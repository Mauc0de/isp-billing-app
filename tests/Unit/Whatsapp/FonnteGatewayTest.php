<?php

namespace Tests\Unit\Whatsapp;

use App\Whatsapp\FonnteGateway;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FonnteGatewayTest extends TestCase
{
    public function test_successful_send_returns_the_first_message_id(): void
    {
        Http::fake([
            'api.fonnte.com/send' => Http::response([
                'status' => true,
                'id' => ['80367170'],
                'detail' => 'success! message in queue',
                'requestid' => 2937124,
            ]),
        ]);

        $result = $this->gateway()->send('6281234567890', 'Halo, tagihan Anda nearing due.');

        $this->assertTrue($result->success);
        $this->assertFalse($result->skipped);
        $this->assertSame('80367170', $result->messageId);
        $this->assertNull($result->error);
    }

    public function test_request_uses_form_encoding_and_bare_token_authorization(): void
    {
        Http::fake(['api.fonnte.com/send' => Http::response(['status' => true, 'id' => ['1']])]);

        $this->gateway(token: 'TOKEN-ABC')->send('6281234567890', 'Halo');

        Http::assertSent(function (Request $request): bool {
            $body = urldecode($request->body());

            return $request->method() === 'POST'
                && $request->url() === 'https://api.fonnte.com/send'
                && $request->hasHeader('Authorization', 'TOKEN-ABC')
                && str_contains($body, 'target=6281234567890')
                && str_contains($body, 'message=Halo');
        });
    }

    public function test_rejection_is_reported_as_a_failure_with_its_reason(): void
    {
        Http::fake([
            'api.fonnte.com/send' => Http::response([
                'status' => false,
                'reason' => 'token invalid',
            ]),
        ]);

        $result = $this->gateway()->send('6281234567890', 'Halo');

        $this->assertFalse($result->success);
        $this->assertFalse($result->skipped);
        $this->assertSame('token invalid', $result->error);
    }

    /**
     * Fonnte tidak konsisten mengapitalisasi key pada respons error, jadi
     * parser harus tahan terhadap "Status" versus "status".
     */
    public function test_capitalised_status_true_is_still_read_as_success(): void
    {
        Http::fake([
            'api.fonnte.com/send' => Http::response([
                'Status' => true,
                'id' => ['99'],
            ]),
        ]);

        $result = $this->gateway()->send('6281234567890', 'Halo');

        $this->assertTrue($result->success);
        $this->assertSame('99', $result->messageId);
    }

    public function test_capitalised_status_false_is_still_read_as_failure(): void
    {
        Http::fake([
            'api.fonnte.com/send' => Http::response([
                'Status' => false,
                'reason' => 'token invalid',
            ]),
        ]);

        $this->assertFalse($this->gateway()->send('6281234567890', 'Halo')->success);
    }

    public function test_http_error_status_is_surfaced_with_the_http_code(): void
    {
        Http::fake(['api.fonnte.com/send' => Http::response('', 500)]);

        $result = $this->gateway()->send('6281234567890', 'Halo');

        $this->assertFalse($result->success);
        $this->assertStringContainsString('500', (string) $result->error);
    }

    public function test_unparseable_response_is_treated_as_a_failure(): void
    {
        Http::fake(['api.fonnte.com/send' => Http::response('<html>gateway error</html>')]);

        $result = $this->gateway()->send('6281234567890', 'Halo');

        $this->assertFalse($result->success);
        $this->assertFalse($result->skipped);
    }

    public function test_request_id_is_used_when_no_message_id_is_returned(): void
    {
        Http::fake([
            'api.fonnte.com/send' => Http::response([
                'status' => true,
                'requestid' => 55501,
            ]),
        ]);

        $this->assertSame('55501', $this->gateway()->send('6281234567890', 'Halo')->messageId);
    }

    private function gateway(string $token = 'TOKEN'): FonnteGateway
    {
        return new FonnteGateway(
            token: $token,
            baseUrl: 'https://api.fonnte.com',
            countryCode: '0',
        );
    }
}
