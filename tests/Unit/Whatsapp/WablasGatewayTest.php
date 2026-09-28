<?php

namespace Tests\Unit\Whatsapp;

use App\Whatsapp\WablasGateway;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WablasGatewayTest extends TestCase
{
    public function test_successful_send_returns_the_message_id(): void
    {
        Http::fake([
            '*/send-message' => Http::response([
                'status' => true,
                'message' => 'Message is pending and waiting to be processed',
                'data' => [
                    'messages' => [
                        ['id' => '5be4ca7f-6b1e-4c7c', 'status' => 'pending'],
                    ],
                ],
            ]),
        ]);

        $result = $this->gateway()->send('6281234567890', 'Halo');

        $this->assertTrue($result->success);
        $this->assertSame('5be4ca7f-6b1e-4c7c', $result->messageId);
    }

    public function test_authorization_header_joins_token_and_secret_with_a_dot(): void
    {
        Http::fake(['*/send-message' => Http::response(['status' => true, 'data' => ['id' => 'x']])]);

        $this->gateway()->send('6281234567890', 'Halo');

        Http::assertSent(fn (Request $request): bool => $request->hasHeader(
            'Authorization',
            'TOKEN-123.SECRET-456',
        ));
    }

    /**
     * Bentuk `data` berbeda antara satu perangkat dan banyak perangkat, dan
     * keduanya muncul di dokumentasi Wablas.
     */
    public function test_multi_device_response_shape_is_handled(): void
    {
        Http::fake([
            '*/send-message' => Http::response([
                'status' => true,
                'data' => [
                    [
                        'messages' => [
                            ['id' => 'multi-device-id'],
                        ],
                    ],
                ],
            ]),
        ]);

        $this->assertSame('multi-device-id', $this->gateway()->send('6281234567890', 'Halo')->messageId);
    }

    public function test_flat_message_object_without_messages_wrapper_is_handled(): void
    {
        Http::fake([
            '*/send-message' => Http::response([
                'status' => true,
                'data' => ['message' => ['id' => 'flat-id']],
            ]),
        ]);

        $this->assertSame('flat-id', $this->gateway()->send('6281234567890', 'Halo')->messageId);
    }

    public function test_rejection_is_reported_as_a_failure(): void
    {
        Http::fake([
            '*/send-message' => Http::response([
                'status' => false,
                'message' => 'token invalid',
            ]),
        ]);

        $result = $this->gateway()->send('6281234567890', 'Halo');

        $this->assertFalse($result->success);
        $this->assertFalse($result->skipped);
        $this->assertSame('token invalid', $result->error);
    }

    public function test_network_failure_is_reported_as_a_failure_not_an_exception(): void
    {
        Http::fake(fn () => throw new ConnectionException('Connection timed out'));

        $result = $this->gateway()->send('6281234567890', 'Halo');

        $this->assertFalse($result->success);
        $this->assertStringContainsString('Wablas', (string) $result->error);
    }

    public function test_missing_status_field_is_treated_as_a_failure(): void
    {
        Http::fake(['*/send-message' => Http::response(['data' => ['id' => 'x']])]);

        $this->assertFalse($this->gateway()->send('6281234567890', 'Halo')->success);
    }

    private function gateway(): WablasGateway
    {
        return new WablasGateway(
            token: 'TOKEN-123',
            secretKey: 'SECRET-456',
            baseUrl: 'https://console.wablas.com/api',
        );
    }
}
