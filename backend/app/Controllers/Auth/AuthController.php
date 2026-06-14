<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Core\Exceptions\HttpException;
use App\Models\User;
use App\Models\UserRefreshToken;
use App\Services\JwtService;
use App\Services\AuthorizationService;

class AuthController extends Controller
{
    private JwtService $jwtService;
    private User $userModel;

    public function __construct()
    {
        $db              = app_database();
        $userModel       = new User($db);
        $tokenModel      = new UserRefreshToken($db);
        $authz           = new AuthorizationService($db);
        $this->userModel = $userModel;
        $this->jwtService = new JwtService(app_jwt(), $userModel, $tokenModel, $authz);
    }

    public function login(Request $request, Response $response): Response
    {
        $data      = $request->json();
        $validator = new Validator($data, [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $user = $this->userModel->findByEmail($data['email']);

        if (!$user || !$this->userModel->verifyPassword($data['password'], $user['password'])) {
            throw new HttpException(401, 'Invalid email or password');
        }

        if (($user['status'] ?? '') !== 'active') {
            throw new HttpException(401, 'Your account has been deactivated');
        }

        $tokens = $this->jwtService->issueTokens($user);
        $this->jwtService->setRefreshCookie($tokens['refresh_token']);

        $this->userModel->updateLastLogin($user['id']);

        return $this->success([
            'access_token' => $tokens['access_token'],
            'expires_in'   => $tokens['expires_in'],
            'user'         => $this->jwtService->buildUserPayload($user),
        ], 'Login successful');
    }

    public function refresh(Request $request, Response $response): Response
    {
        $rawToken = $this->jwtService->getRefreshTokenFromCookie();

        if (!$rawToken) {
            throw new HttpException(401, 'No refresh token provided');
        }

        $tokens = $this->jwtService->refreshAccessToken($rawToken);
        $this->jwtService->setRefreshCookie($tokens['refresh_token']);

        // Resolve user from new tokens
        $jwt     = app_jwt();
        $payload = $jwt->decode($tokens['access_token']);
        $user    = $this->userModel->findById($payload['sub']);

        return $this->success([
            'access_token' => $tokens['access_token'],
            'expires_in'   => $tokens['expires_in'],
            'user'         => $this->jwtService->buildUserPayload($user),
        ]);
    }

    public function logout(Request $request, Response $response): Response
    {
        $rawToken = $this->jwtService->getRefreshTokenFromCookie();

        if ($rawToken) {
            $this->jwtService->revokeRefreshToken($rawToken);
        }

        $this->jwtService->clearRefreshCookie();

        return $this->success(null, 'Logged out successfully');
    }

    public function me(Request $request, Response $response): Response
    {
        $payload = $request->authPayload();
        $user    = $this->userModel->findById($payload['sub']);

        if (!$user) {
            throw new HttpException(401, 'User not found');
        }

        return $this->success($this->jwtService->buildUserPayload($user));
    }
}
