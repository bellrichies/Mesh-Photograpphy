<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function json(array $data, int $status = 200): Response
    {
        return (new Response())->json($data, $status);
    }

    protected function success(mixed $data = null, string $message = 'Success', int $status = 200, array $meta = []): Response
    {
        return (new Response())->json([
            'ok'      => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => (object) [],
            'meta'    => $meta,
        ], $status);
    }

    protected function created(mixed $data = null, string $message = 'Created successfully.'): Response
    {
        return $this->success($data, $message, 201);
    }

    protected function noContent(): Response
    {
        return (new Response())->json([
            'ok'      => true,
            'message' => 'Deleted successfully.',
            'data'    => null,
            'errors'  => (object) [],
            'meta'    => (object) [],
        ], 200);
    }

    protected function error(string $message, int $status = 400, array $errors = []): Response
    {
        return (new Response())->json([
            'ok'      => false,
            'message' => $message,
            'data'    => null,
            'errors'  => empty($errors) ? (object) [] : $errors,
            'meta'    => ['status' => $status],
        ], $status);
    }

    protected function validationError(array $errors, string $message = 'Validation failed.'): Response
    {
        return $this->error($message, 422, $errors);
    }

    protected function validate(array $data, array $rules): ?Response
    {
        $validator = new Validator($data, $rules);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }
        return null;
    }

    protected function paginate(array $items, int $total, int $page, int $perPage): array
    {
        return [
            'current_page' => $page,
            'per_page'     => $perPage,
            'total'        => $total,
            'last_page'    => (int) ceil($total / max(1, $perPage)),
            'from'         => $total > 0 ? (($page - 1) * $perPage) + 1 : 0,
            'to'           => min($page * $perPage, $total),
        ];
    }
}
