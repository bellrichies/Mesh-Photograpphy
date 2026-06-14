<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BookingRequest;

class BookingService
{
    public function __construct(private readonly BookingRequest $model) {}

    public function create(array $data, string $ip): int
    {
        return $this->model->create(array_merge($data, ['ip_address' => $ip]));
    }
}
