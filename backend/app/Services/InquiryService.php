<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Inquiry;

class InquiryService
{
    public function __construct(private readonly Inquiry $model) {}

    public function create(array $data, string $ip): int
    {
        return $this->model->create(array_merge($data, ['ip_address' => $ip]));
    }
}
