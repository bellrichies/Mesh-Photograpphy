<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\NewsletterSubscription;
use App\Services\NewsletterSubscriptionService;

class NewsletterSubscriptionController extends Controller
{
    private NewsletterSubscriptionService $service;

    public function __construct()
    {
        $this->service = new NewsletterSubscriptionService(new NewsletterSubscription(app_database()));
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->json();
        $data['email'] = trim((string) ($data['email'] ?? ''));

        $validator = new Validator($data, [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $result = $this->service->subscribe(
            $data['email'],
            $request->ip(),
            $request->userAgent(),
            'footer'
        );

        $message = match ($result['status']) {
            'already_subscribed' => 'You are already subscribed.',
            'reactivated' => 'Welcome back. Your subscription is active again.',
            default => 'Thanks for subscribing.',
        };

        return $this->success(
            ['status' => $result['status']],
            $message,
            $result['status'] === 'subscribed' ? 201 : 200
        );
    }
}
