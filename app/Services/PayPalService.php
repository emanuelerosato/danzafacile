<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\School;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Log;
use Exception;

class PayPalService
{
    private $client;
    private $school;
    private $settings;

    public function __construct(School $school)
    {
        $this->school = $school;
        $this->loadSchoolSettings();
        $this->initializeClient();
    }

    /**
     * Carica le impostazioni PayPal specifiche della scuola
     */
    private function loadSchoolSettings()
    {
        $schoolId = $this->school->id;

        $this->settings = [
            'enabled' => Setting::get("school.{$schoolId}.paypal.enabled", false),
            'mode' => Setting::get("school.{$schoolId}.paypal.mode", 'sandbox'),
            'currency' => Setting::get("school.{$schoolId}.paypal.currency", 'EUR'),
            'client_id' => Setting::get("school.{$schoolId}.paypal.client_id", ''),
            'client_secret' => Setting::get("school.{$schoolId}.paypal.client_secret", ''),
            'fee_percentage' => (float) Setting::get("school.{$schoolId}.paypal.fee_percentage", '3.4'),
            'fixed_fee' => (float) Setting::get("school.{$schoolId}.paypal.fixed_fee", '0.35'),
        ];
    }

    /**
     * Inizializza il client PayPal con le credenziali della scuola
     */
    private function initializeClient()
    {
        if (!$this->settings['enabled']) {
            throw new Exception('PayPal non è abilitato per questa scuola');
        }

        if (empty($this->settings['client_id']) || empty($this->settings['client_secret'])) {
            throw new Exception('Credenziali PayPal non configurate per questa scuola');
        }

        $config = [
            'mode' => $this->settings['mode'], // sandbox | live
            'sandbox' => [
                'client_id' => $this->settings['client_id'],
                'client_secret' => $this->settings['client_secret'],
                'app_id' => 'APP-80W284485P519543T', // Default PayPal sandbox app ID
            ],
            'live' => [
                'client_id' => $this->settings['client_id'],
                'client_secret' => $this->settings['client_secret'],
                'app_id' => '', // Live app ID (se necessario)
            ],
            'payment_action' => 'Sale', // Capture | Authorize | Order
            'currency' => $this->settings['currency'],
            'notify_url' => route('paypal.webhook'),
            'locale' => 'it_IT',
            'validate_ssl' => true,
        ];

        $this->client = new PayPalClient($config);
        $this->client->setApiCredentials($config);
    }

    /**
     * Verifica se PayPal è abilitato per questa scuola
     */
    public function isEnabled(): bool
    {
        return $this->settings['enabled'] &&
               !empty($this->settings['client_id']) &&
               !empty($this->settings['client_secret']);
    }

    /**
     * Calcola le commissioni PayPal per un importo
     */
    public function calculateFees(float $amount): array
    {
        $percentageFee = ($amount * $this->settings['fee_percentage']) / 100;
        $fixedFee = $this->settings['fixed_fee'];
        $totalFees = $percentageFee + $fixedFee;

        return [
            'percentage_fee' => round($percentageFee, 2),
            'fixed_fee' => round($fixedFee, 2),
            'total_fees' => round($totalFees, 2),
            'net_amount' => round($amount - $totalFees, 2),
        ];
    }

    /**
     * Crea un pagamento PayPal
     */
    public function createPayment(array $paymentData): array
    {
        try {
            $this->client->setAccessToken($this->client->getAccessToken());

            $payment = [
                'intent' => 'sale',
                'application_context' => [
                    'return_url' => route('paypal.success'),
                    'cancel_url' => route('paypal.cancel'),
                    'brand_name' => $this->school->name,
                    'locale' => 'it-IT',
                    'landing_page' => 'BILLING',
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                ],
                'redirect_urls' => [
                    'return_url' => route('paypal.success'),
                    'cancel_url' => route('paypal.cancel'),
                ],
                'payer' => [
                    'payment_method' => 'paypal',
                    'payer_info' => [
                        'email' => $paymentData['payer_email'] ?? '',
                        'first_name' => $paymentData['payer_first_name'] ?? '',
                        'last_name' => $paymentData['payer_last_name'] ?? '',
                    ],
                ],
                'transactions' => [
                    [
                        'amount' => [
                            'total' => number_format($paymentData['amount'], 2, '.', ''),
                            'currency' => $this->settings['currency'],
                            'details' => [
                                'subtotal' => number_format($paymentData['amount'], 2, '.', ''),
                            ],
                        ],
                        'description' => $paymentData['description'] ?? 'Pagamento corso',
                        'custom' => json_encode([
                            'school_id' => $this->school->id,
                            'user_id' => $paymentData['user_id'] ?? null,
                            'course_id' => $paymentData['course_id'] ?? null,
                            'payment_id' => $paymentData['payment_id'] ?? null,
                        ]),
                        'item_list' => [
                            'items' => [
                                [
                                    'name' => $paymentData['item_name'] ?? 'Corso di danza',
                                    'description' => $paymentData['description'] ?? 'Pagamento corso',
                                    'quantity' => 1,
                                    'price' => number_format($paymentData['amount'], 2, '.', ''),
                                    'currency' => $this->settings['currency'],
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            $response = $this->client->createPayment($payment);

            if (isset($response['error'])) {
                Log::error('PayPal createPayment error:', $response);
                throw new Exception('Errore nella creazione del pagamento PayPal: ' . ($response['error']['message'] ?? 'Errore sconosciuto'));
            }

            Log::info('PayPal payment created successfully', [
                'payment_id' => $response['id'] ?? null,
                'school_id' => $this->school->id,
            ]);

            return $response;

        } catch (Exception $e) {
            Log::error('PayPal createPayment exception:', [
                'error' => $e->getMessage(),
                'school_id' => $this->school->id,
            ]);
            throw $e;
        }
    }

    /**
     * Esegue un pagamento PayPal dopo l'approvazione
     */
    public function executePayment(string $paymentId, string $payerId): array
    {
        try {
            $this->client->setAccessToken($this->client->getAccessToken());

            $response = $this->client->executePayment($paymentId, $payerId);

            if (isset($response['error'])) {
                Log::error('PayPal executePayment error:', $response);
                throw new Exception('Errore nell\'esecuzione del pagamento PayPal: ' . ($response['error']['message'] ?? 'Errore sconosciuto'));
            }

            Log::info('PayPal payment executed successfully', [
                'payment_id' => $paymentId,
                'payer_id' => $payerId,
                'school_id' => $this->school->id,
            ]);

            return $response;

        } catch (Exception $e) {
            Log::error('PayPal executePayment exception:', [
                'error' => $e->getMessage(),
                'payment_id' => $paymentId,
                'school_id' => $this->school->id,
            ]);
            throw $e;
        }
    }

    /**
     * Ottiene i dettagli di un pagamento PayPal
     */
    public function getPaymentDetails(string $paymentId): array
    {
        try {
            $this->client->setAccessToken($this->client->getAccessToken());

            $response = $this->client->showPaymentDetails($paymentId);

            if (isset($response['error'])) {
                Log::error('PayPal getPaymentDetails error:', $response);
                throw new Exception('Errore nel recupero dettagli pagamento PayPal: ' . ($response['error']['message'] ?? 'Errore sconosciuto'));
            }

            return $response;

        } catch (Exception $e) {
            Log::error('PayPal getPaymentDetails exception:', [
                'error' => $e->getMessage(),
                'payment_id' => $paymentId,
                'school_id' => $this->school->id,
            ]);
            throw $e;
        }
    }

    /**
     * Ottiene le impostazioni PayPal della scuola
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * Ottiene la scuola associata
     */
    public function getSchool(): School
    {
        return $this->school;
    }

    /**
     * Factory method per creare istanza basata su scuola
     */
    public static function forSchool(School $school): self
    {
        return new self($school);
    }

    /**
     * Ottiene l'URL di approvazione dal response di createPayment
     */
    public function getApprovalUrl(array $paymentResponse): ?string
    {
        if (!isset($paymentResponse['links'])) {
            return null;
        }

        foreach ($paymentResponse['links'] as $link) {
            if ($link['rel'] === 'approval_url') {
                return $link['href'];
            }
        }

        return null;
    }

    /**
     * Verifica se un webhook PayPal è valido
     */
    public function verifyWebhook(array $headers, string $body): bool
    {
        try {
            // Implementazione verifica signature webhook PayPal
            // Questo richiede la configurazione del webhook ID nelle impostazioni
            return true; // Placeholder - implementare verifica reale
        } catch (Exception $e) {
            Log::error('PayPal webhook verification failed:', [
                'error' => $e->getMessage(),
                'school_id' => $this->school->id,
            ]);
            return false;
        }
    }
}