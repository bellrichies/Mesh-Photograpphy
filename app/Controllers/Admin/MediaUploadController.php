<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Services\MediaArchiveService;
use App\Services\MediaUploadService;
use RuntimeException;
use Throwable;

class MediaUploadController
{
    public function upload(Request $request, Response $response): Response
    {
        $tokenKey = (string) config('app.csrf_token_name', '_token');
        if (! app_csrf()->verify((string) $request->post($tokenKey, ''))) {
            return $response->json([
                'ok' => false,
                'message' => 'Invalid CSRF token.',
            ], 419);
        }

        $file = $request->file('file');
        if (! is_array($file)) {
            return $response->json([
                'ok' => false,
                'message' => 'No file uploaded.',
            ], 422);
        }

        try {
            $service = new MediaUploadService(app_database(), dirname(__DIR__, 3));
            $result = $service->upload($file, app_auth()->id());
            app_security_logger()->log('media.uploaded', $request, 'media', (int) ($result['id'] ?? 0), 'Uploaded media asset.', [
                'original_name' => (string) ($result['original_name'] ?? ''),
                'stored_name' => (string) ($result['stored_name'] ?? ''),
                'file_type' => (string) ($result['file_type'] ?? ''),
            ]);

            return $response->json([
                'ok' => true,
                'message' => 'Upload successful.',
                'media' => $result,
            ], 201);
        } catch (RuntimeException $exception) {
            return $response->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            return $response->json([
                'ok' => false,
                'message' => 'Unexpected upload error.',
            ], 500);
        }
    }

    public function archive(Request $request, Response $response): Response
    {
        $tokenKey = (string) config('app.csrf_token_name', '_token');
        if (! app_csrf()->verify((string) $request->post($tokenKey, ''))) {
            return $response->json(['ok' => false, 'message' => 'Invalid CSRF token.'], 419);
        }

        $mediaId = (int) $request->post('media_id', 0);
        if ($mediaId <= 0) {
            return $response->json(['ok' => false, 'message' => 'Invalid media identifier.'], 422);
        }

        (new MediaArchiveService(app_database()))->archive($mediaId);
        app_security_logger()->log('media.archived', $request, 'media', $mediaId, 'Archived media asset.');

        return $response->json(['ok' => true, 'message' => 'Media archived.']);
    }
}
