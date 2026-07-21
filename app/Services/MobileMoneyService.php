<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Exception;

/**
 * Service unifié pour les paiements Mobile Money
 * Supporte: Orange Money, MTN Mobile Money, Moov Money
 */
class MobileMoneyService
{
    private $provider;
    private $config;

    public function __construct($providerName = 'orange')
    {
        $this->provider = $providerName;
        $this->config = config("mobile_money.providers.{$providerName}");

        if (!$this->config) {
            throw new Exception("Provider non configuré: {$providerName}");
        }
    }

    /**
     * Initier un paiement
     */
    public function initiatePayment($amount, $phoneNumber, $reference)
    {
        try {
            switch ($this->provider) {
                case 'orange':
                    return $this->orangeMoneyPayment($amount, $phoneNumber, $reference);
                case 'mtn':
                    return $this->mtnMobileMoneyPayment($amount, $phoneNumber, $reference);
                case 'moov':
                    return $this->moovMoneyPayment($amount, $phoneNumber, $reference);
                case 'cinetpay':
                    return $this->cinetPayPayment($amount, $phoneNumber, $reference);
                default:
                    throw new Exception("Provider non supporté: {$this->provider}");
            }
        } catch (Exception $e) {
            Log::error("Error initiating {$this->provider} payment: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Vérifier le statut d'une transaction
     */
    public function verifyTransaction($transactionId)
    {
        try {
            switch ($this->provider) {
                case 'orange':
                    return $this->orangeMoneyVerify($transactionId);
                case 'mtn':
                    return $this->mtnMobileMoneyVerify($transactionId);
                case 'moov':
                    return $this->moovMoneyVerify($transactionId);
                case 'cinetpay':
                    return $this->cinetPayVerify($transactionId);
                default:
                    throw new Exception("Provider non supporté: {$this->provider}");
            }
        } catch (Exception $e) {
            Log::error("Error verifying {$this->provider} transaction: " . $e->getMessage());
            throw $e;
        }
    }

    // =========================================================================
    // ORANGE MONEY
    // =========================================================================

    /**
     * Orange Money - Obtenir un token d'accès
     */
    private function getOrangeAccessToken()
    {
        return Cache::remember('orange_money_token', 3500, function () {
            try {
                $response = Http::asForm()->post($this->config['api_url'] . '/oauth/v3/token', [
                    'grant_type' => 'client_credentials',
                ])->withBasicAuth(
                        $this->config['client_id'],
                        $this->config['client_secret']
                    );

                if ($response->failed()) {
                    throw new Exception('Failed to get Orange Money access token');
                }

                return $response->json()['access_token'];
            } catch (Exception $e) {
                Log::error('Orange Money token error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Orange Money - Initier un paiement
     */
    private function orangeMoneyPayment($amount, $phoneNumber, $reference)
    {
        try {
            $token = $this->getOrangeAccessToken();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->config['api_url'] . '/omcoreapis/1.0.2/mp/pay', [
                        'merchant_key' => $this->config['merchant_key'],
                        'currency' => 'XOF',
                        'order_id' => $reference,
                        'amount' => $amount,
                        'return_url' => url('/api/mobile-money/callback'),
                        'cancel_url' => url('/api/mobile-money/cancel'),
                        'notif_url' => url('/api/mobile-money/webhook/orange'),
                        'lang' => 'fr',
                        'reference' => $reference,
                    ]);

            if ($response->failed()) {
                throw new Exception('Orange Money payment failed: ' . $response->body());
            }

            $data = $response->json();

            return [
                'transaction_id' => $data['pay_token'] ?? 'OM' . time() . rand(1000, 9999),
                'status' => 'PENDING',
                'message' => 'Paiement initié avec succès',
                'payment_url' => $data['payment_url'] ?? null,
                'raw_response' => $data,
            ];
        } catch (Exception $e) {
            Log::error('Orange Money payment error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Orange Money - Vérifier une transaction
     */
    private function orangeMoneyVerify($transactionId)
    {
        try {
            $token = $this->getOrangeAccessToken();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->get($this->config['api_url'] . '/omcoreapis/1.0.2/mp/paymentstatus/' . $transactionId);

            if ($response->failed()) {
                return 'FAILED';
            }

            $data = $response->json();
            $status = $data['status'] ?? 'PENDING';

            // Mapper les statuts Orange Money vers nos statuts
            $statusMap = [
                'SUCCESS' => 'SUCCESS',
                'PENDING' => 'PENDING',
                'FAILED' => 'FAILED',
                'EXPIRED' => 'CANCELLED',
            ];

            return $statusMap[$status] ?? 'PENDING';
        } catch (Exception $e) {
            Log::error('Orange Money verify error: ' . $e->getMessage());
            return 'FAILED';
        }
    }

    // =========================================================================
    // MTN MOBILE MONEY
    // =========================================================================

    /**
     * MTN MoMo - Obtenir un token d'accès
     */
    private function getMTNAccessToken()
    {
        return Cache::remember('mtn_momo_token', 3500, function () {
            try {
                $response = Http::withHeaders([
                    'Ocp-Apim-Subscription-Key' => $this->config['subscription_key'],
                ])->withBasicAuth(
                        $this->config['api_user'],
                        $this->config['api_key']
                    )->post($this->config['api_url'] . '/collection/token/');

                if ($response->failed()) {
                    throw new Exception('Failed to get MTN MoMo access token');
                }

                return $response->json()['access_token'];
            } catch (Exception $e) {
                Log::error('MTN MoMo token error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * MTN Mobile Money - Initier un paiement
     */
    private function mtnMobileMoneyPayment($amount, $phoneNumber, $reference)
    {
        try {
            $token = $this->getMTNAccessToken();
            $referenceId = Str::uuid()->toString();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Reference-Id' => $referenceId,
                'X-Target-Environment' => env('MTN_MOMO_ENVIRONMENT', 'sandbox'),
                'Ocp-Apim-Subscription-Key' => $this->config['subscription_key'],
                'Content-Type' => 'application/json',
            ])->post($this->config['api_url'] . '/collection/v1_0/requesttopay', [
                        'amount' => (string) $amount,
                        'currency' => 'XOF',
                        'externalId' => $reference,
                        'payer' => [
                            'partyIdType' => 'MSISDN',
                            'partyId' => $phoneNumber,
                        ],
                        'payerMessage' => 'Paiement Picme225',
                        'payeeNote' => 'Référence: ' . $reference,
                    ]);

            if ($response->failed()) {
                throw new Exception('MTN MoMo payment failed: ' . $response->body());
            }

            return [
                'transaction_id' => $referenceId,
                'status' => 'PENDING',
                'message' => 'Paiement initié avec succès',
                'raw_response' => $response->json(),
            ];
        } catch (Exception $e) {
            Log::error('MTN MoMo payment error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * MTN Mobile Money - Vérifier une transaction
     */
    private function mtnMobileMoneyVerify($transactionId)
    {
        try {
            $token = $this->getMTNAccessToken();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Target-Environment' => env('MTN_MOMO_ENVIRONMENT', 'sandbox'),
                'Ocp-Apim-Subscription-Key' => $this->config['subscription_key'],
            ])->get($this->config['api_url'] . '/collection/v1_0/requesttopay/' . $transactionId);

            if ($response->failed()) {
                return 'FAILED';
            }

            $data = $response->json();
            $status = $data['status'] ?? 'PENDING';

            // Mapper les statuts MTN vers nos statuts
            $statusMap = [
                'SUCCESSFUL' => 'SUCCESS',
                'PENDING' => 'PENDING',
                'FAILED' => 'FAILED',
                'TIMEOUT' => 'CANCELLED',
            ];

            return $statusMap[$status] ?? 'PENDING';
        } catch (Exception $e) {
            Log::error('MTN MoMo verify error: ' . $e->getMessage());
            return 'FAILED';
        }
    }

    // =========================================================================
    // MOOV MONEY
    // =========================================================================

    /**
     * Moov Money - Obtenir un token d'accès
     */
    private function getMoovAccessToken()
    {
        return Cache::remember('moov_money_token', 3500, function () {
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post($this->config['api_url'] . '/api/v1/auth/token', [
                            'api_key' => $this->config['api_key'],
                            'merchant_id' => $this->config['merchant_id'],
                        ]);

                if ($response->failed()) {
                    throw new Exception('Failed to get Moov Money access token');
                }

                return $response->json()['access_token'];
            } catch (Exception $e) {
                Log::error('Moov Money token error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Moov Money - Initier un paiement
     */
    private function moovMoneyPayment($amount, $phoneNumber, $reference)
    {
        try {
            $token = $this->getMoovAccessToken();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post($this->config['api_url'] . '/api/v1/payment/request', [
                        'merchant_id' => $this->config['merchant_id'],
                        'amount' => $amount,
                        'currency' => 'XOF',
                        'phone_number' => $phoneNumber,
                        'reference' => $reference,
                        'description' => 'Paiement Picme225 - Ref: ' . $reference,
                        'callback_url' => url('/api/mobile-money/webhook/moov'),
                    ]);

            if ($response->failed()) {
                throw new Exception('Moov Money payment failed: ' . $response->body());
            }

            $data = $response->json();

            return [
                'transaction_id' => $data['transaction_id'] ?? 'MOOV' . time() . rand(1000, 9999),
                'status' => 'PENDING',
                'message' => 'Paiement initié avec succès',
                'ussd_code' => $data['ussd_code'] ?? null,
                'raw_response' => $data,
            ];
        } catch (Exception $e) {
            Log::error('Moov Money payment error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Moov Money - Vérifier une transaction
     */
    private function moovMoneyVerify($transactionId)
    {
        try {
            $token = $this->getMoovAccessToken();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->get($this->config['api_url'] . '/api/v1/payment/status/' . $transactionId);

            if ($response->failed()) {
                return 'FAILED';
            }

            $data = $response->json();
            $status = $data['status'] ?? 'PENDING';

            // Mapper les statuts Moov vers nos statuts
            $statusMap = [
                'SUCCESS' => 'SUCCESS',
                'COMPLETED' => 'SUCCESS',
                'PENDING' => 'PENDING',
                'FAILED' => 'FAILED',
                'CANCELLED' => 'CANCELLED',
                'EXPIRED' => 'CANCELLED',
            ];

            return $statusMap[$status] ?? 'PENDING';
        } catch (Exception $e) {
            Log::error('Moov Money verify error: ' . $e->getMessage());
            return 'FAILED';
        }
    }

    // =========================================================================
    // CINETPAY
    // =========================================================================

    /**
     * CinetPay - Initier un paiement (Supporte Orange, MTN, Moov, Wave, Carte Bancaire)
     */
    private function cinetPayPayment($amount, $phoneNumber, $reference)
    {
        try {
            $response = Http::post($this->config['api_url'], [
                'apikey' => $this->config['api_key'],
                'site_id' => $this->config['site_id'],
                'transaction_id' => $reference,
                'amount' => $amount,
                'currency' => 'XOF',
                'description' => 'Recharge ECO Wallet - Ref: ' . $reference,
                'notify_url' => url('/api/provider/wallet/callback'),
                'return_url' => url('/api/provider/wallet/status?ref=' . $reference),
                'channels' => 'ALL', // ALL, MOBILE_MONEY, CREDIT_CARD, WALLET
                'metadata' => 'wallet_recharge',
                'customer_mobile' => $this->formatPhoneNumber($phoneNumber),
            ]);

            if ($response->failed()) {
                throw new Exception('CinetPay payment initiation failed: ' . $response->body());
            }

            $data = $response->json();

            if ($data['code'] == '201') {
                return [
                    'transaction_id' => $reference,
                    'status' => 'PENDING',
                    'message' => 'Paiement initié',
                    'payment_url' => $data['data']['payment_url'] ?? null,
                    'raw_response' => $data,
                ];
            } else {
                throw new Exception('CinetPay Error: ' . ($data['message'] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            Log::error('CinetPay payment error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * CinetPay - Vérifier une transaction
     */
    private function cinetPayVerify($transactionId)
    {
        try {
            $checkUrl = str_replace('/v2/payment', '/v2/payment/check', $this->config['api_url']);

            $response = Http::post($checkUrl, [
                'apikey' => $this->config['api_key'],
                'site_id' => $this->config['site_id'],
                'transaction_id' => $transactionId,
            ]);

            if ($response->failed()) {
                return 'PENDING';
            }

            $data = $response->json();

            if (isset($data['code']) && $data['code'] == '00') {
                return 'SUCCESS';
            }

            return 'PENDING';
        } catch (Exception $e) {
            Log::error('CinetPay verify error: ' . $e->getMessage());
            return 'PENDING';
        }
    }

    // =========================================================================
    // UTILITAIRES
    // =========================================================================

    /**
     * Vérifier la signature d'un webhook
     */
    public function verifyWebhookSignature($payload, $signature)
    {
        if (!$signature) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $this->config['webhook_secret']);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Formater un numéro de téléphone
     */
    private function formatPhoneNumber($phoneNumber)
    {
        // Supprimer les espaces et caractères spéciaux
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Ajouter le code pays si nécessaire (225 pour Côte d'Ivoire)
        if (strlen($phone) == 10 && !str_starts_with($phone, '225')) {
            $phone = '225' . $phone;
        }

        return $phone;
    }
}
