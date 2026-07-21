<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Fleet;
use App\Models\StationAgent;
use App\Models\Partner;

class UnifiedAuthController extends Controller
{
    /**
     * Connexion unifiée - Détection automatique du type de compte
     * Supporte les comptes à double rôle (User + Fleet/Agent/Partner)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unifiedLogin(Request $request)
    {
        if ($request->has('mobile')) {
            $request->merge(['mobile' => \App\Helpers\PhoneHelper::normalize($request->mobile)]);
        }
        if ($request->has('username')) {
            $request->merge(['username' => \App\Helpers\PhoneHelper::normalize($request->username)]);
        }

        \Log::info("Unified login attempt: ", $request->all());
        $validator = Validator::make($request->all(), [
            'mobile'       => 'required_without:username',
            'username'     => 'required_without:mobile',
            'password'     => 'required|min:6',
            'device_id'    => 'required',
            'device_type'  => 'required|in:android,ios',
            'device_token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $rawMobile = $request->mobile ?: $request->username;
        $mobile    = \App\Helpers\PhoneHelper::normalize($rawMobile);

        \Log::info("Searching for user with mobile: $mobile (Raw: $rawMobile)");
        $user = User::where('mobile', $mobile)->first();

        if ($user) {
            $passCheck = Hash::check($request->password, $user->password);
            \Log::info("User found ID: {$user->id}. Password check: " . ($passCheck ? "SUCCESS" : "FAILED"));

            if ($passCheck) {
                \Log::info("User authenticated, creating token for user ID: " . $user->id);
                try {
                    $token = $user->createToken('UserToken')->accessToken;
                    \Log::info("Token created successfully");
                } catch (\Exception $e) {
                    \Log::error("Token creation failed: " . $e->getMessage());
                    throw $e;
                }

                $user->update([
                    'device_id'    => $request->device_id,
                    'device_token' => $request->device_token,
                    'device_type'  => $request->device_type,
                ]);

                // ── Auto-liaison legacy Fleet ──────────────────────────────
                if (!$user->fleet_id) {
                    $fleet = Fleet::where('mobile', $mobile)->first();
                    if ($fleet) {
                        $user->update(['fleet_id' => $fleet->id]);
                        try { $fleet->update(['user_id' => $user->id]); } catch (\Exception $e) {}
                    }
                }

                // ── Auto-liaison legacy StationAgent ──────────────────────
                if (!$user->station_agent_id) {
                    $agent = StationAgent::where('user_id', $user->id)->first();
                    if ($agent) {
                        $user->update(['station_agent_id' => $agent->id]);
                    }
                }

                // ── Résolution du Partenaire Unifié ───────────────────────
                $partner = Partner::where('user_id', $user->id)
                    ->with('station')
                    ->first();

                // Détermination du type de compte principal
                $primaryType = 'USER';
                if ($partner) {
                    $primaryType = $partner->type; // FLEET_OWNER | STATION_AGENT | RECRUITER | etc.
                } elseif ($user->fleet_id) {
                    $primaryType = 'FLEET';
                } elseif ($user->station_agent_id) {
                    $primaryType = 'STATION_AGENT';
                } elseif ($user->user_type && $user->user_type !== 'USER') {
                    $primaryType = $user->user_type;
                }

                $user->update(['user_type' => $primaryType]);

                // Construction des rôles disponibles
                $roles = ['USER'];
                if ($partner) {
                    $roles[] = $partner->type;
                    $roles[] = 'PARTNER';
                }
                if ($user->fleet_id && !in_array('FLEET_OWNER', $roles)) {
                    $roles[] = 'FLEET';
                    $roles[] = 'FLEET_OWNER';
                }
                if ($user->station_agent_id && !in_array('STATION_AGENT', $roles)) {
                    $roles[] = 'STATION_AGENT';
                }

                $response = [
                    'access_token'    => $token,
                    'token_type'      => 'Bearer',
                    'account_type'    => $primaryType,
                    'user_type'       => $primaryType,
                    'user'            => $user->fresh(),
                    'available_roles' => array_values(array_unique($roles)),
                ];

                // ── Bloc Partenaire Unifié (prioritaire) ──────────────────
                if ($partner) {
                    $response['partner_data'] = [
                        'id'               => $partner->id,
                        'type'             => $partner->type,
                        'status'           => $partner->status,
                        'commission_rules' => $partner->commission_rules,
                        'wallet_balance'   => $user->wallet_balance ?? 0,
                        'station'          => $partner->station ? [
                            'id'   => $partner->station->id,
                            'name' => $partner->station->name,
                            'type' => $partner->station->type ?? 'arret',
                        ] : null,
                    ];
                }

                // ── Bloc Fleet Owner (legacy ou non-encore migré) ─────────
                if ($user->fleet_id && !$partner) {
                    $fleet = Fleet::find($user->fleet_id);
                    if ($fleet) {
                        $response['fleet_data'] = [
                            'id'      => $fleet->id,
                            'name'    => $fleet->name,
                            'type'    => $fleet->type ?? 'STANDARD',
                            'company' => $fleet->company,
                        ];
                    }
                }

                // ── Bloc Station Agent (legacy ou non-encore migré) ───────
                if ($user->station_agent_id && !$partner) {
                    $agent = StationAgent::find($user->station_agent_id);
                    if ($agent) {
                        $response['agent_data'] = [
                            'id'                       => $agent->id,
                            'name'                     => $agent->name,
                            'wallet_balance'           => $agent->wallet_balance ?? 0,
                            'commission_per_passenger' => $agent->commission_per_passenger ?? 50,
                            'commission_per_parcel'    => $agent->commission_per_parcel ?? 100,
                        ];
                    }
                }

                return response()->json($response, 200);
            }
        }

        // ÉTAPE 2 : Vérifier dans FLEETS (anciens comptes sans compte User lié)
        \Log::info("Searching for fleet with mobile: $mobile");
        $fleet = Fleet::where('mobile', $mobile)->first();

        if ($fleet) {
            $passCheck = Hash::check($request->password, $fleet->password);
            \Log::info("Fleet found ID: {$fleet->id}. Password check: " . ($passCheck ? "SUCCESS" : "FAILED"));

            if ($passCheck) {
                // Création automatique du compte User pour permettre de passer des commandes
                $user = User::create([
                    'first_name'   => $fleet->name,
                    'last_name'    => 'Owner',
                    'mobile'       => $fleet->mobile,
                    'email'        => $fleet->email,
                    'password'     => $fleet->password,
                    'user_type'    => 'FLEET',
                    'fleet_id'     => $fleet->id,
                    'device_id'    => $request->device_id,
                    'device_token' => $request->device_token,
                    'device_type'  => $request->device_type,
                    'payment_mode' => 'CASH',
                ]);

                try {
                    $fleet->user_id = $user->id;
                    $fleet->save();
                } catch (\Exception $e) {}

                $token = $user->createToken('UserToken')->accessToken;

                return response()->json([
                    'access_token'    => $token,
                    'token_type'      => 'Bearer',
                    'account_type'    => 'FLEET',
                    'user'            => $user,
                    'available_roles' => ['USER', 'FLEET', 'FLEET_OWNER'],
                    'fleet_data'      => [
                        'id'      => $fleet->id,
                        'name'    => $fleet->name,
                        'type'    => $fleet->type ?? 'STANDARD',
                        'company' => $fleet->company,
                    ],
                ], 200);
            }
        }

        // AUCUN COMPTE TROUVÉ
        return response()->json([
            'error' => 'Identifiants incorrects ou compte inexistant'
        ], 401);
    }
}
