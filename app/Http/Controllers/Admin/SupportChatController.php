<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Log;
use App\Models\Provider;

class SupportChatController extends Controller
{
    /**
     * Affiche la vue principale du tableau de bord de support.
     */
    public function index()
    {
        return view('admin.support.chat');
    }

    /**
     * Retourne la liste JSON des canaux de discussion d'assistance actifs.
     */
    public function rooms()
    {
        try {
            // Grouper les messages par provider_id pour lister les salons
            $latestMessages = \App\Models\SupportMessage::select('provider_id', \DB::raw('MAX(created_at) as last_message_date'))
                ->groupBy('provider_id')
                ->get();

            $supportRooms = [];
            foreach ($latestMessages as $room) {
                $providerId = $room->provider_id;
                $provider = Provider::find($providerId);
                
                $lastMsgRecord = \App\Models\SupportMessage::where('provider_id', $providerId)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $lastMessage = $lastMsgRecord ? $lastMsgRecord->message : '';
                $lastTimestamp = $lastMsgRecord ? strtotime($lastMsgRecord->created_at) * 1000 : 0;

                $supportRooms[] = [
                    'room_id' => 'support_driver_' . $providerId,
                    'provider_id' => $providerId,
                    'provider_name' => $provider ? ($provider->first_name . ' ' . $provider->last_name) : "Chauffeur #{$providerId}",
                    'provider_avatar' => $provider ? ($provider->avatar ? (str_starts_with($provider->avatar, 'http') ? $provider->avatar : \Storage::disk('s3')->url( $provider->avatar)) : null) : null,
                    'last_message' => $lastMessage,
                    'last_timestamp' => $lastTimestamp,
                ];
            }

            // Trier les salons par date du dernier message décroissant
            usort($supportRooms, fn($a, $b) => $b['last_timestamp'] <=> $a['last_timestamp']);

            return response()->json([
                'success' => true,
                'rooms' => $supportRooms
            ]);

        } catch (\Exception $e) {
            Log::error('Admin Support Rooms Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve support rooms: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Retourne la liste JSON des messages pour un canal (chauffeur) spécifique.
     */
    public function messages($roomId)
    {
        try {
            $providerId = str_replace('support_driver_', '', $roomId);
            
            $dbMessages = \App\Models\SupportMessage::where('provider_id', $providerId)
                ->orderBy('created_at', 'asc')
                ->get();

            $messages = [];
            foreach ($dbMessages as $msg) {
                // Formater pour que le front end react/vue ou JS existant s'y retrouve
                $senderId = "driver_" . $providerId;
                if ($msg->sender == 'agent_picme_ai') $senderId = 'agent_picme_ai';
                if ($msg->sender == 'agent_admin') $senderId = 'agent_admin';

                $messages[] = [
                    'senderId' => $senderId,
                    'message' => $msg->message,
                    'timestamp' => strtotime($msg->created_at) * 1000
                ];
            }

            return response()->json([
                'success' => true,
                'messages' => $messages
            ]);

        } catch (\Exception $e) {
            Log::error('Admin Support Messages Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve messages: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Enregistre une réponse d'assistance et la diffuse via WebSockets.
     */
    public function reply(Request $request, $roomId)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        try {
            $providerId = str_replace('support_driver_', '', $roomId);
            
            $msg = \App\Models\SupportMessage::create([
                'provider_id' => $providerId,
                'sender' => 'agent_admin',
                'message' => $request->message,
            ]);

            try {
                broadcast(new \App\Events\NewSupportMessage($msg));
            } catch (\Exception $e) {}

            return response()->json([
                'success' => true,
                'message' => 'Reply sent successfully.',
                'data' => [
                    'senderId' => 'agent_admin',
                    'message' => $msg->message,
                    'timestamp' => strtotime($msg->created_at) * 1000
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Admin Support Reply Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send reply: ' . $e->getMessage()], 500);
        }
    }
}
