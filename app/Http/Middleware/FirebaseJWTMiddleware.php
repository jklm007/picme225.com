<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\Auth\Token\Verifier;
use Firebase\Auth\Token\Exception\InvalidToken;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class FirebaseJWTMiddleware
{
    public function handle($request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        // Vérifiez que le token existe
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Authorization token not found'], 401);
        }

        $idToken = substr($authHeader, 7);

        try {
            // Vérifiez le token Firebase
            $verifier = new Verifier(config('services.firebase.project_id'));
            $verifiedIdToken = $verifier->verifyIdToken($idToken);
            $firebaseUid = $verifiedIdToken->claims()->get('sub');

            // Synchroniser l'utilisateur avec la base de données Laravel
            $user = User::firstOrCreate(
                ['firebase_uid' => $firebaseUid],
                [
                    'name' => $verifiedIdToken->claims()->get('name') ?? 'Unknown',
                    'email' => $verifiedIdToken->claims()->get('email'),
                    'email_verified_at' => now(),
                ]
            );

            // Authentifier l'utilisateur dans Laravel
            Auth::login($user);

        } catch (InvalidToken $e) {
            return response()->json(['error' => 'Invalid Firebase token'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to authenticate token', 'message' => $e->getMessage()], 500);
        }

        return $next($request);
    }
}
