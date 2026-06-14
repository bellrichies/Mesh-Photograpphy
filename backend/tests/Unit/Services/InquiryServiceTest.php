<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Inquiry;
use App\Services\InquiryService;
use Tests\TestCase;

class InquiryServiceTest extends TestCase
{
    private Inquiry $model;
    private InquiryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model   = $this->createMock(Inquiry::class);
        $this->service = new InquiryService($this->model);
    }

    public function test_create_persists_inquiry_with_ip(): void
    {
        $data = [
            'name'    => 'Alice Smith',
            'email'   => 'alice@example.com',
            'message' => 'Hello, I would like to book a session.',
        ];

        $this->model
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (array $arg) use ($data): bool {
                return $arg['name']       === $data['name']
                    && $arg['email']      === $data['email']
                    && $arg['message']    === $data['message']
                    && $arg['ip_address'] === '127.0.0.1';
            }))
            ->willReturn(42);

        $id = $this->service->create($data, '127.0.0.1');

        $this->assertSame(42, $id);
    }

    public function test_create_does_not_override_existing_ip_address(): void
    {
        // ip_address supplied in $data should be overwritten by the $ip param
        $data = [
            'name'       => 'Bob',
            'email'      => 'bob@example.com',
            'message'    => 'Test',
            'ip_address' => '10.0.0.1', // attacker-supplied value
        ];

        $this->model
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(fn (array $arg): bool => $arg['ip_address'] === '192.168.1.1'))
            ->willReturn(1);

        $this->service->create($data, '192.168.1.1');
    }
}
