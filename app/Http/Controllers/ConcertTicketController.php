<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConcertTicketController extends Controller
{
    /**
     * Organisateur : Diffuser l'événement sur le réseau social Picme
     */
    public function broadcastEventToSocial(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'date' => 'required|string',
            'price' => 'required|numeric',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'address' => 'nullable|string'
        ]);

        $postContent = "🎉 ÉVÉNEMENT : {$request->title}\n";
        $postContent .= "📅 Date : {$request->date}";
        if ($request->address) {
            $postContent .= " | 📍 Lieu : {$request->address}";
        }
        $postContent .= "\n💰 Ticket prévente : {$request->price} FCFA\n";
        $postContent .= "📝 {$request->description}\n";

        $post = \App\Models\Post::create([
            'user_id'      => \Illuminate\Support\Facades\Auth::id(),
            'type'         => 'SOCIAL',
            'source'       => 'INTERNAL',
            'category'     => 'EVENT_TICKET',
            'content'      => $postContent,
            'price'        => $request->price,
            'status'       => 'ACTIVE'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'L\'événement a été poussé sur le réseau social passagers et chauffeurs !',
            'post'    => $post
        ], 201);
    }

    public function store(Request $request)
    {
        $request->validate([
            'price' => 'required|numeric'
        ]);

        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        $price = (float)$request->price;

        if ($request->paymentMethod === 'WALLET' || $request->paymentMethod === 'wallet') {
            if ($user->wallet_balance < $price) {
                return response()->json(['error' => 'Solde insuffisant dans le portefeuille'], 402);
            }
            
            $user->decrement('wallet_balance', $price);
            \App\Models\WalletPassbook::create([
                'user_id' => $user->id,
                'amount'  => -$price,
                'status'  => 'DEBITED',
                'via'     => 'CONCERT_TICKET'
            ]);
            $status = 'paid';
        } else {
            $status = 'pending_payment';
        }

        $id = (string) Str::uuid();

        DB::table('concert_tickets')->insert([
            'id' => $id,
            'name' => $request->name,
            'phone' => $request->phone,
            'type' => $request->type,
            'price' => $price,
            'payment_method' => $request->paymentMethod,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'id' => $id,
            'name' => $request->name,
            'phone' => $request->phone,
            'type' => $request->type,
            'price' => $price,
            'paymentMethod' => $request->paymentMethod,
            'status' => $status
        ]);
    }

    public function show($id)
    {
        $ticket = DB::table('concert_tickets')->where('id', $id)->first();

        if ($ticket) {
            return response()->json($ticket);
        } else {
            return response()->json(['error' => 'Ticket not found'], 404);
        }
    }

    public function validateTicket($id)
    {
        $ticket = DB::table('concert_tickets')->where('id', $id)->first();

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        if ($ticket->status === 'used') {
            return response()->json(['error' => 'Ticket already used', 'ticket' => $ticket], 400);
        }

        DB::table('concert_tickets')->where('id', $id)->update([
            'status' => 'used',
            'updated_at' => now(),
        ]);

        $ticket->status = 'used';
        return response()->json([
            'message' => 'Ticket validated successfully',
            'ticket' => $ticket
        ]);
    }
}
