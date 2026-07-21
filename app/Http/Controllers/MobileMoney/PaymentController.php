<?php

namespace App\Http\Controllers\MobileMoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\MobileMoneyTransaction;
use App\Services\MobileMoneyService;

class PaymentController extends Controller
{
    /**
     * Initier un paiement Mobile Money
     * POST /api/mobile-money/payment/initiate
     */
    public function initiatePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:100',
            'phone_number' => 'required|string|regex:/^[0-9]{10}$/',
            'provider' => 'required|in:orange,mtn,moov',
            'reference' => 'required|string',
            'type' => 'required|in:WALLET_RECHARGE,RIDE_PAYMENT',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $user = Auth::guard('api')->user();
            $mobileMoneyService = new MobileMoneyService($request->provider);

            // Initier le paiement
            $result = $mobileMoneyService->initiatePayment(
                $request->amount,
                $request->phone_number,
                $request->reference
            );

            // Enregistrer la transaction
            $transaction = MobileMoneyTransaction::create([
                'user_id' => $user->id,
                'provider' => $request->provider,
                'amount' => $request->amount,
                'phone_number' => $request->phone_number,
                'transaction_id' => $result['transaction_id'],
                'reference' => $request->reference,
                'type' => $request->type,
                'status' => 'PENDING',
                'provider_response' => $result,
            ]);

            return response()->json([
                'message' => 'Paiement initié avec succès',
                'transaction' => $transaction
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error initiating mobile money payment: ' . $e->getMessage());
            return response()->json([
                'error' => 'Une erreur est survenue lors de l\'initiation du paiement'
            ], 500);
        }
    }

    /**
     * Vérifier le statut d'une transaction
     * GET /api/mobile-money/payment/verify/{transactionId}
     */
    public function verifyTransaction($transactionId)
    {
        try {
            $transaction = MobileMoneyTransaction::where('transaction_id', $transactionId)->firstOrFail();
            $mobileMoneyService = new MobileMoneyService($transaction->provider);

            $status = $mobileMoneyService->verifyTransaction($transactionId);

            $transaction->update([
                'status' => $status,
                'processed_at' => $status === 'SUCCESS' ? now() : null,
            ]);

            return response()->json([
                'transaction' => $transaction,
                'status' => $status
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error verifying transaction: ' . $e->getMessage());
            return response()->json([
                'error' => 'Transaction introuvable'
            ], 404);
        }
    }

    /**
     * Historique des transactions
     * GET /api/mobile-money/transactions
     */
    public function transactions(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            $query = MobileMoneyTransaction::where('user_id', $user->id);

            if ($request->provider) {
                $query->where('provider', $request->provider);
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

            return response()->json($transactions, 200);

        } catch (\Exception $e) {
            Log::error('Error getting transactions: ' . $e->getMessage());
            return response()->json([
                'error' => 'Une erreur est survenue'
            ], 500);
        }
    }

    /**
     * Webhook pour les notifications des fournisseurs
     * POST /api/mobile-money/webhook/{provider}
     */
    public function webhook(Request $request, $provider)
    {
        try {
            $mobileMoneyService = new MobileMoneyService($provider);
            $payload = $request->getContent();
            $signature = $request->header('X-Signature');

            // Vérifier la signature
            if (!$mobileMoneyService->verifyWebhookSignature($payload, $signature)) {
                Log::warning('Invalid webhook signature', ['provider' => $provider]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $data = json_decode($payload, true);

            // Traiter la notification
            $transaction = MobileMoneyTransaction::where('transaction_id', $data['transaction_id'])->first();

            if ($transaction) {
                $transaction->update([
                    'status' => $data['status'],
                    'provider_response' => $data,
                    'processed_at' => $data['status'] === 'SUCCESS' ? now() : null,
                ]);

                if ($data['status'] === 'SUCCESS') {
                    if ($transaction->type === 'RIDE_PAYMENT') {
                        $this->handleRidePayment($transaction);
                    } elseif ($transaction->type === 'PACKAGE_PAYMENT') {
                        $this->handlePackagePayment($transaction);
                    }
                }
            }

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('Error processing webhook: ' . $e->getMessage());
            return response()->json(['error' => 'Error processing webhook'], 500);
        }
    }

    /**
     * Handle logic for successful Ride Payment.
     */
    private function handleRidePayment($transaction)
    {
        try {
            // Assuming reference is formatted as "RIDE_ID" or just ID
            // Safe parsing: if strictly numeric, assume ID. If prefix, strip it.
            $requestId = filter_var($transaction->reference, FILTER_SANITIZE_NUMBER_INT);

            $userRequest = \App\Models\UserRequests::find($requestId);
            if (!$userRequest)
                return;

            // Find Payment Record
            $paymentEntry = \App\Models\UserRequestPayment::where('request_id', $userRequest->id)->first();

            if ($paymentEntry) {
                $paymentEntry->payment_mode = 'MOBILE_MONEY';
                $paymentEntry->payment_id = $transaction->transaction_id;
                $paymentEntry->save();
            }

            $userRequest->paid = 1;
            $userRequest->status = 'COMPLETED';
            $userRequest->save();

            // Trigger DAO Distribution (Revenue Split)
            try {
                if ($paymentEntry) {
                    $daoService = new \App\Services\DaoDistributionService();
                    $daoService->applyDaoFees($paymentEntry);
                }
            } catch (\Exception $e) {
                Log::error("DAO Distribution failed after Mobile Money payment: " . $e->getMessage());
            }

        } catch (\Exception $e) {
            Log::error("Error handling Ride Payment: " . $e->getMessage());
        }
    }

    /**
     * Handle logic for successful Package Payment.
     */
    private function handlePackagePayment($transaction)
    {
        try {
            $requestId = filter_var($transaction->reference, FILTER_SANITIZE_NUMBER_INT);
            $package = \App\Models\PackageRequest::find($requestId);

            if ($package) {
                $package->paid = 1;
                $package->payment_mode = 'MOBILE_MONEY';
                $package->save();

                // Trigger Logistics Distribution
                try {
                    $daoService = new \App\Services\DaoDistributionService();
                    $daoService->distributeLogisticsRevenue($package);
                } catch (\Exception $e) {
                    Log::error("Logistics Distribution failed: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error("Error handling Package Payment: " . $e->getMessage());
        }
    }
}

