<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\NewsletterSubscription;
use App\Services\NewsletterSubscriptionService;
use PDOException;
use Tests\TestCase;

class NewsletterSubscriptionServiceTest extends TestCase
{
    private NewsletterSubscription $model;
    private NewsletterSubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = $this->createMock(NewsletterSubscription::class);
        $this->service = new NewsletterSubscriptionService($this->model);
    }

    public function test_subscribe_creates_normalized_subscription(): void
    {
        $this->model
            ->expects($this->once())
            ->method('findByNormalizedEmail')
            ->with('alice@example.com')
            ->willReturn(null);

        $this->model
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (array $payload): bool {
                return $payload['email'] === 'alice@example.com'
                    && $payload['email_normalized'] === 'alice@example.com'
                    && $payload['source'] === 'footer'
                    && $payload['ip_address'] === '203.0.113.10'
                    && $payload['user_agent'] === 'Unit Test Browser';
            }))
            ->willReturn(12);

        $result = $this->service->subscribe(
            ' Alice@Example.COM ',
            '203.0.113.10',
            'Unit Test Browser'
        );

        $this->assertSame(12, $result['id']);
        $this->assertSame('subscribed', $result['status']);
    }

    public function test_subscribe_does_not_duplicate_active_subscription(): void
    {
        $this->model
            ->method('findByNormalizedEmail')
            ->with('alice@example.com')
            ->willReturn([
                'id' => 7,
                'email' => 'alice@example.com',
                'email_normalized' => 'alice@example.com',
                'status' => 'active',
            ]);

        $this->model->expects($this->never())->method('create');
        $this->model->expects($this->never())->method('reactivate');

        $result = $this->service->subscribe('alice@example.com', '127.0.0.1', '');

        $this->assertSame(7, $result['id']);
        $this->assertSame('already_subscribed', $result['status']);
    }

    public function test_subscribe_reactivates_existing_unsubscribed_record(): void
    {
        $this->model
            ->method('findByNormalizedEmail')
            ->with('alice@example.com')
            ->willReturn([
                'id' => 9,
                'email' => 'alice@example.com',
                'email_normalized' => 'alice@example.com',
                'status' => 'unsubscribed',
            ]);

        $this->model
            ->expects($this->once())
            ->method('reactivate')
            ->with(9, $this->arrayHasKey('email_normalized'));
        $this->model->expects($this->never())->method('create');

        $result = $this->service->subscribe('alice@example.com', '127.0.0.1', '');

        $this->assertSame(9, $result['id']);
        $this->assertSame('reactivated', $result['status']);
    }

    public function test_subscribe_recovers_from_duplicate_insert_race(): void
    {
        $exception = new PDOException('SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry');
        $exception->errorInfo = ['23000', 1062, 'Duplicate entry'];

        $this->model
            ->expects($this->exactly(2))
            ->method('findByNormalizedEmail')
            ->with('alice@example.com')
            ->willReturnOnConsecutiveCalls(null, [
                'id' => 15,
                'email' => 'alice@example.com',
                'email_normalized' => 'alice@example.com',
                'status' => 'active',
            ]);

        $this->model
            ->expects($this->once())
            ->method('create')
            ->willThrowException($exception);

        $result = $this->service->subscribe('alice@example.com', '127.0.0.1', '');

        $this->assertSame(15, $result['id']);
        $this->assertSame('already_subscribed', $result['status']);
    }
}
