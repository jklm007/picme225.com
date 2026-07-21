<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebRTCController extends Controller
{
    /**
     * Verifies if a user is authorized to call another user in a given context.
     * This endpoint is called by the external NestJS Signaling Server.
     */
    public function verifyRelation(Request $request)
    {
        $callerId = $request->input('callerId');
        $receiverId = $request->input('receiverId');
        $context = $request->input('context', 'default');

        Log::info("WebRTC Verification Request: Caller {$callerId} -> Receiver {$receiverId} (Context: {$context})");

        // Basic verification structure based on context
        $isValid = false;
        
        try {
            switch ($context) {
                case 'taxi':
                case 'delivery':
                    // Check if there is an active ride between this user and this provider
                    // Example (pseudo-code):
                    // $ride = UserRequests::whereIn('user_id', [$callerId, $receiverId])
                    //                     ->whereIn('provider_id', [$callerId, $receiverId])
                    //                     ->whereIn('status', ['ACCEPTED', 'ARRIVED', 'PICKEDUP', 'STARTED'])
                    //                     ->first();
                    // if ($ride) $isValid = true;
                    
                    // For development, allow all
                    $isValid = true; 
                    break;

                case 'marketplace':
                    // Check if there is an active chat/interest between these users
                    // For development, allow all
                    $isValid = true;
                    break;

                case 'support':
                    // Check if one is a user and the other is an active support agent handling their ticket
                    // For development, allow all
                    $isValid = true;
                    break;

                default:
                    // Unknown context, block by default
                    $isValid = false;
                    break;
            }

            return response()->json([
                'valid' => $isValid,
                'message' => $isValid ? 'Authorized' : 'Not authorized'
            ], 200);

        } catch (\Exception $e) {
            Log::error("WebRTC Verification Error: " . $e->getMessage());
            return response()->json(['valid' => false, 'message' => 'Internal error'], 500);
        }
    }

    /**
     * Trigger a Push Notification to wake up the recipient device.
     */
    public function triggerPush(Request $request)
    {
        $callerId = $request->input('callerId');
        $receiverId = $request->input('receiverId');
        $type = $request->input('type');
        $roomId = $request->input('roomId');

        if (!$receiverId) {
            return response()->json(['status' => 'error', 'message' => 'receiverId missing'], 400);
        }

        try {
            $caller = \App\Models\User::find($callerId);
            $callerName = $caller ? $caller->first_name : "Un utilisateur";

            $pushData = [
                'type' => 'WEBRTC_CALL',
                'call_type' => $type,
                'roomId' => $roomId,
                'callerId' => $callerId,
                'callerName' => $callerName,
                'title' => 'Appel entrant de ' . $callerName,
                'message' => 'Appel ' . ($type == 'video' ? 'vidéo' : 'audio') . ' en cours...',
            ];

            // In our system, providers and users are in the same User model or separate?
            // If SendPushNotification::sendPushToUser expects the id from users table:
            $pushService = new \App\Http\Controllers\SendPushNotification();
            $pushService->sendPushToUser($receiverId, $pushData);

            return response()->json(['status' => 'success', 'message' => 'Push notification sent']);
        } catch (\Exception $e) {
            Log::error("WebRTC Push Trigger Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to trigger push'], 500);
        }
    }

    /**
     * Trigger a Push Notification for a missed call.
     */
    public function missedCall(Request $request)
    {
        $callerId = $request->input('callerId');
        $receiverId = $request->input('receiverId');

        if (!$receiverId || !$callerId) {
            return response()->json(['status' => 'error', 'message' => 'Missing callerId or receiverId'], 400);
        }

        try {
            $caller = \App\Models\User::find($callerId);
            $callerName = $caller ? $caller->first_name : "Un utilisateur";

            $pushData = [
                'type' => 'MISSED_CALL',
                'callerId' => $callerId,
                'title' => 'Appel manqué',
                'message' => 'Vous avez manqué un appel de ' . $callerName,
            ];

            $pushService = new \App\Http\Controllers\SendPushNotification();
            $pushService->sendPushToUser($receiverId, $pushData);

            return response()->json(['status' => 'success', 'message' => 'Missed call notification sent']);
        } catch (\Exception $e) {
            Log::error("WebRTC Missed Call Push Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to trigger missed call push'], 500);
        }
    }
}
