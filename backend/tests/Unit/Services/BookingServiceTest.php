<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\BookingRequest;
use App\Services\BookingService;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    private BookingRequest $model;
    private BookingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model   = $this->createMock(BookingRequest::class);
        $this->service = new BookingService($this->model);
    }

    public function test_create_persists_booking_with_ip(): void
    {
        $data = [
            'name'             => 'Carol Jones',
            'email'            => 'carol@example.com',
            'service_type'     => 'wedding',
            'preferred_date'   => '2026-09-15',
            'message'          => 'Looking forward to working with you.',
        ];

        $this->model
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (array $arg) use ($data): bool {
                return $arg['name']         === $data['name']
                    && $arg['email']        === $data['email']
                    && $arg['service_type'] === $data['service_type']
                    && $arg['ip_address']   === '203.0.113.5';
            }))
            ->willReturn(7);

        $id = $this->service->create($data, '203.0.113.5');

        $this->assertSame(7, $id);
    }

    public function test_create_merges_ip_into_data(): void
    {
        $data = ['name' => 'Dan', 'email' => 'dan@example.com', 'message' => 'Hi'];

        $this->model
            ->expects($this->once())
            ->method('create')
            ->with($this->arrayHasKey('ip_address'))
            ->willReturn(1);

        $this->service->create($data, '10.0.0.2');
    }
}
