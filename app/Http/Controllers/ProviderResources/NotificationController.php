<?php

namespace App\Http\Controllers\ProviderResources;

use App\Http\Controllers\Controller;
use Auth;
use DB;

class NotificationController extends Controller
{
    /**
     * Liste des notifications du chauffeur connecté.
     */
    public function index()
    {
        $provider = Auth::guard('provider')->user();

        // Tentative d'utilisation de la table notifications standard de Laravel
        // (si elle n'existe pas, on renvoie une liste vide sans planter)
        try {
            $notifications = DB::table('notifications')
                ->where('notifiable_type', 'App\\Models\\Provider')
                ->where('notifiable_id', $provider->id)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($n) {
                    $data = json_decode($n->data, true) ?? [];
                    return [
                        'id'         => $n->id,
                        'title'      => $data['title'] ?? 'Notification',
                        'message'    => $data['message'] ?? ($data['body'] ?? ''),
                        'read'       => !is_null($n->read_at),
                        'read_at'    => $n->read_at,
                        'created_at' => $n->created_at,
                    ];
                });
        } catch (\Exception $e) {
            $notifications = collect([]);
        }

        return view('provider.notifications.index', compact('notifications'));
    }

    /**
     * Marquer une notification comme lue.
     */
    public function markRead($id)
    {
        try {
            $provider = Auth::guard('provider')->user();
            DB::table('notifications')
                ->where('id', $id)
                ->where('notifiable_type', 'App\\Models\\Provider')
                ->where('notifiable_id', $provider->id)
                ->update(['read_at' => now()]);
        } catch (\Exception $e) { }

        return response()->json(['success' => true]);
    }

    /**
     * Marquer toutes les notifications comme lues.
     */
    public function markAllRead()
    {
        try {
            $provider = Auth::guard('provider')->user();
            DB::table('notifications')
                ->where('notifiable_type', 'App\\Models\\Provider')
                ->where('notifiable_id', $provider->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        } catch (\Exception $e) { }

        return redirect()->back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Nombre de notifications non lues — appelé par le badge AJAX.
     */
    public function unreadCount()
    {
        try {
            $provider = Auth::guard('provider')->user();
            $count = DB::table('notifications')
                ->where('notifiable_type', 'App\\Models\\Provider')
                ->where('notifiable_id', $provider->id)
                ->whereNull('read_at')
                ->count();
        } catch (\Exception $e) {
            $count = 0;
        }

        return response()->json(['count' => $count]);
    }
}
