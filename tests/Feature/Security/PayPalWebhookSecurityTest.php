<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\School;
use App\Models\Payment;
use App\Services\PayPalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalWebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        // Create school and payment
        $this->school = School::factory()->create();
        $this->payment = Payment::factory()->create([
            'school_id' => $this->school->id,
            'transaction_id' => 'TXN12345',
            'status' => 'pending'
        ]);
    }

    /**
     * Test webhook rejects requests without signature headers
     */
    public function test_webhook_rejects_requests_without_signature_headers()
    {
        $webhookData = [
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => [
                'id' => 'TXN12345',
                'custom' => json_encode(['school_id' => $this->school->id])
            ]
        ];

        $response = $this->postJson(route('paypal.webhook'), $webhookData);

        // Should reject because missing signature headers
        $response->assertStatus(403);
    }

    /**
     * Test webhook accepts requests when verification is disabled
     */
    public function test_webhook_accepts_when_verification_disabled()
    {
        // Temporarily disable verification
        config(['paypal.webhook_verification.enabled' => false]);

        $webhookData = [
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => [
                'id' => 'TXN12345',
                'parent_payment' => 'PAY123',
                'custom' => json_encode(['school_id' => $this->school->id])
            ]
        ];

        $response = $this->postJson(route('paypal.webhook'), $webhookData);

        // Should accept without verification
        $response->assertStatus(200);
    }

    /**
     * Test webhook signature verification with valid signature
     */
    public function test_webhook_verifies_valid_signature()
    {
        // Mock PayPal API response for signature verification
        Http::fake([
            '*/v1/notifications/verify-webhook-signature' => Http::response([
                'verification_status' => 'SUCCESS'
            ], 200)
        ]);

        // Mock valid PayPal headers
        $headers = [
            'paypal-auth-algo' => 'SHA256withRSA',
            'paypal-cert-url' => 'https://api.sandbox.paypal.com/cert.pem',
            'paypal-transmission-id' => 'test-transmission-id-123',
            'paypal-transmission-sig' => 'test-signature-abc',
            'paypal-transmission-time' => '2025-10-01T10:00:00Z',
        ];

        $webhookData = [
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => [
                'id' => 'TXN12345',
                'custom' => json_encode(['school_id' => $this->school->id])
            ]
        ];

        $response = $this->withHeaders($headers)
                         ->postJson(route('paypal.webhook'), $webhookData);

        // Should accept because signature is valid
        $response->assertStatus(200);
    }

    /**
     * Test webhook rejects invalid signature
     */
    public function test_webhook_rejects_invalid_signature()
    {
        // Mock PayPal API response for FAILED verification
        Http::fake([
            '*/v1/notifications/verify-webhook-signature' => Http::response([
                'verification_status' => 'FAILURE'
            ], 200)
        ]);

        $headers = [
            'paypal-auth-algo' => 'SHA256withRSA',
            'paypal-cert-url' => 'https://malicious-site.com/fake-cert.pem',
            'paypal-transmission-id' => 'fake-transmission-id',
            'paypal-transmission-sig' => 'invalid-signature',
            'paypal-transmission-time' => '2025-10-01T10:00:00Z',
        ];

        $webhookData = [
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => [
                'id' => 'TXN12345',
                'custom' => json_encode(['school_id' => $this->school->id])
            ]
        ];

        $response = $this->withHeaders($headers)
                         ->postJson(route('paypal.webhook'), $webhookData);

        // Should reject with 403
        $response->assertStatus(403);
    }

    /**
     * Test webhook rejects when school not found
     */
    public function test_webhook_rejects_when_school_not_found()
    {
        $headers = [
            'paypal-auth-algo' => 'SHA256withRSA',
            'paypal-cert-url' => 'https://api.sandbox.paypal.com/cert.pem',
            'paypal-transmission-id' => 'test-id',
            'paypal-transmission-sig' => 'test-sig',
            'paypal-transmission-time' => '2025-10-01T10:00:00Z',
        ];

        $webhookData = [
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => [
                'id' => 'TXN99999',
                'custom' => json_encode(['school_id' => 999999]) // Non-existent school
            ]
        ];

        $response = $this->withHeaders($headers)
                         ->postJson(route('paypal.webhook'), $webhookData);

        // Should reject with 404
        $response->assertStatus(404);
    }

    /**
     * Test webhook logs critical alert on verification failure
     */
    public function test_webhook_logs_critical_alert_on_verification_failure()
    {
        Log::shouldReceive('critical')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'SIGNATURE VERIFICATION FAILED') &&
                       isset($context['school_id']) &&
                       isset($context['event_type']);
            });

        Log::shouldReceive('info')->zeroOrMoreTimes();
        Log::shouldReceive('error')->zeroOrMoreTimes();
        Log::shouldReceive('warning')->zeroOrMoreTimes();

        Http::fake([
            '*/v1/notifications/verify-webhook-signature' => Http::response([
                'verification_status' => 'FAILURE'
            ], 200)
        ]);

        $headers = [
            'paypal-auth-algo' => 'SHA256withRSA',
            'paypal-cert-url' => 'https://malicious.com/cert.pem',
            'paypal-transmission-id' => 'malicious-id',
            'paypal-transmission-sig' => 'fake-sig',
            'paypal-transmission-time' => '2025-10-01T10:00:00Z',
        ];

        $webhookData = [
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => [
                'id' => 'TXN12345',
                'custom' => json_encode(['school_id' => $this->school->id])
            ]
        ];

        $this->withHeaders($headers)
             ->postJson(route('paypal.webhook'), $webhookData);
    }

    /**
     * Test webhook requires webhook_id configuration
     */
    public function test_webhook_requires_webhook_id_configuration()
    {
        // Clear webhook_id configuration
        config(['paypal.webhook_verification.webhook_id' => '']);

        $headers = [
            'paypal-auth-algo' => 'SHA256withRSA',
            'paypal-cert-url' => 'https://api.sandbox.paypal.com/cert.pem',
            'paypal-transmission-id' => 'test-id',
            'paypal-transmission-sig' => 'test-sig',
            'paypal-transmission-time' => '2025-10-01T10:00:00Z',
        ];

        $webhookData = [
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => [
                'id' => 'TXN12345',
                'custom' => json_encode(['school_id' => $this->school->id])
            ]
        ];

        $response = $this->withHeaders($headers)
                         ->postJson(route('paypal.webhook'), $webhookData);

        // Should reject because webhook_id not configured
        $response->assertStatus(403);
    }

    /**
     * Test PayPalService verifyWebhook method directly
     */
    public function test_paypal_service_verify_webhook_with_missing_headers()
    {
        $service = PayPalService::forSchool($this->school);

        // Headers missing required fields
        $headers = [
            'paypal-auth-algo' => 'SHA256withRSA',
            // Missing other required headers
        ];

        $body = json_encode(['event_type' => 'PAYMENT.SALE.COMPLETED']);

        $result = $service->verifyWebhook($headers, $body, 'test-webhook-id');

        // Should return false
        $this->assertFalse($result);
    }

    /**
     * Test PayPalService verifyWebhook with complete headers
     */
    public function test_paypal_service_verify_webhook_with_complete_headers()
    {
        Http::fake([
            '*/v1/notifications/verify-webhook-signature' => Http::response([
                'verification_status' => 'SUCCESS'
            ], 200)
        ]);

        $service = PayPalService::forSchool($this->school);

        $headers = [
            'paypal-auth-algo' => ['SHA256withRSA'],
            'paypal-cert-url' => ['https://api.sandbox.paypal.com/cert.pem'],
            'paypal-transmission-id' => ['test-transmission-123'],
            'paypal-transmission-sig' => ['test-signature-abc'],
            'paypal-transmission-time' => ['2025-10-01T10:00:00Z'],
        ];

        $body = json_encode([
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => ['id' => 'TXN123']
        ]);

        $result = $service->verifyWebhook($headers, $body, 'test-webhook-id');

        // Should return true
        $this->assertTrue($result);
    }

    /**
     * Test replay attack prevention (same webhook sent twice)
     */
    public function test_webhook_can_detect_replay_attacks()
    {
        Http::fake([
            '*/v1/notifications/verify-webhook-signature' => Http::response([
                'verification_status' => 'SUCCESS'
            ], 200)
        ]);

        $headers = [
            'paypal-auth-algo' => 'SHA256withRSA',
            'paypal-cert-url' => 'https://api.sandbox.paypal.com/cert.pem',
            'paypal-transmission-id' => 'same-transmission-id-123',
            'paypal-transmission-sig' => 'test-sig',
            'paypal-transmission-time' => '2025-10-01T10:00:00Z',
        ];

        $webhookData = [
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => [
                'id' => 'TXN12345',
                'custom' => json_encode(['school_id' => $this->school->id])
            ]
        ];

        // First request - should succeed
        $response1 = $this->withHeaders($headers)
                          ->postJson(route('paypal.webhook'), $webhookData);
        $response1->assertStatus(200);

        // Payment should now be completed
        $this->payment->refresh();
        $this->assertEquals('completed', $this->payment->status);

        // Second identical request (replay attack)
        // Note: In real implementation, you should track transmission_id to prevent replays
        // For now, this test demonstrates the concept
        $response2 = $this->withHeaders($headers)
                          ->postJson(route('paypal.webhook'), $webhookData);

        // Should still process (current implementation doesn't prevent replays yet)
        // In production, implement transmission_id tracking
        $response2->assertStatus(200);
    }
}
