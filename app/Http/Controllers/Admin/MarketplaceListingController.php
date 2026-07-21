<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MarketplaceListing;
use App\Models\MarketplaceCategory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MarketplaceListingController extends Controller
{
    public function index()
    {
        $pendingListings = MarketplaceListing::with('user')
            ->whereNull('deleted_at')
            ->where('status', 'PENDING_VALIDATION')
            ->latest()
            ->get();

        $activeListings = MarketplaceListing::with('user')
            ->whereNull('deleted_at')
            ->where('status', '!=', 'PENDING_VALIDATION')
            ->latest()
            ->get();

        return view('admin.marketplace.listings.index', compact('pendingListings', 'activeListings'));
    }

    public function approve($id)
    {
        $listing = MarketplaceListing::findOrFail($id);
        $listing->status = 'ACTIVE';
        $listing->save();

        // Notify the author
        if (!empty($listing->owner_phone)) {
            $this->notifyAuthor(
                $listing->owner_phone,
                "✅ *Votre annonce a été validée*\n\nFélicitations ! Votre annonce a été validée et est maintenant visible par tous les utilisateurs sur PickMe225.\n\n🔗 Voir mon annonce : " . url('/marketplace/' . $listing->id) . "\n🔄 Partager mon annonce : " . url('/marketplace/' . $listing->id)
            );
        }

        return redirect()->route('admin.marketplace-listings.index')->with('flash_success', '✅ Annonce approuvée et publiée sur la marketplace !');
    }

    public function reject($id)
    {
        $listing = MarketplaceListing::findOrFail($id);
        $listing->status = 'REJECTED';
        $listing->save();

        // Notify the author
        if (!empty($listing->owner_phone)) {
            $this->notifyAuthor(
                $listing->owner_phone,
                "❌ Votre annonce *{$listing->title}* n'a malheureusement pas pu être validée. Si vous avez des questions, contactez notre équipe."
            );
        }

        return redirect()->route('admin.marketplace-listings.index')->with('flash_success', '❌ Annonce rejetée.');
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('selected_ids', []);

        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('flash_error', 'Aucune annonce sélectionnée.');
        }

        $count = count($ids);

        if ($action === 'approve') {
            MarketplaceListing::whereIn('id', $ids)->update(['status' => 'ACTIVE']);
            // NOTE: We skip individual WhatsApp notifications for bulk approve to avoid spamming the API/rate limits
            return redirect()->back()->with('flash_success', $count . ' annonce(s) approuvée(s) avec succès.');
        } 
        elseif ($action === 'reject') {
            MarketplaceListing::whereIn('id', $ids)->update(['status' => 'REJECTED']);
            return redirect()->back()->with('flash_success', $count . ' annonce(s) rejetée(s) avec succès.');
        }
        elseif ($action === 'delete') {
            MarketplaceListing::whereIn('id', $ids)->delete();
            return redirect()->back()->with('flash_success', $count . ' annonce(s) supprimée(s) avec succès.');
        }

        return redirect()->back()->with('flash_error', 'Action invalide.');
    }


    public function create()
    {
        $categories = MarketplaceCategory::whereNull('parent_id')->with('children')->orderBy('order_index')->get();
        $agents = \App\Models\User::whereHas('partner', function ($query) {
            $query->where('type', 'STATION_AGENT');
        })->orWhereHas('stationAgent')->get();
        
        return view('admin.marketplace.listings.create', compact('categories', 'agents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:ARTICLE,VEHICLE',
            'category' => 'required|string',
            'sub_category' => 'nullable|string',
        ]);

        $listing = new MarketplaceListing($request->all());
        $listing->user_id = \Auth::guard('admin')->id() ?? 1;
        $listing->location_latitude = 5.345317;
        $listing->location_longitude = -4.024429;
        $listing->status = 'ACTIVE';

        // Déterminer le type automatiquement pour la compatibilité DB
        if ($request->category == 'VEHICLES') {
            $listing->type = 'VEHICLE';
        } else {
            $listing->type = 'ARTICLE';
        }
        
        if ($request->hasFile('cover_image')) {
            $listing->cover_image = $request->file('cover_image')->store('marketplace', 's3');
        }

        // Fusionner toutes les métadonnées dynamiques
        $metadata = $request->input('metadata', []);
        if ($request->has('sub_category')) {
            $metadata['sub_category'] = $request->sub_category;
        }
        $listing->metadata = $metadata;

        $listing->save();

        // --- GESTION DES PASSES (TICKETS & TRAVEL) ---
        if (in_array($request->category, ['TICKETS', 'TRAVEL']) && $request->has('passes')) {
            foreach ($request->passes as $passData) {
                if (!empty($passData['name'])) {
                    \App\Models\EventPassType::create([
                        'listing_id' => $listing->id,
                        'name' => $passData['name'],
                        'price' => $passData['price'] ?? $listing->price,
                        'valid_from' => $passData['valid_from'] ?? '00:00:00',
                        'valid_until' => $passData['valid_until'] ?? '23:59:59',
                        'quantity' => $passData['quantity'] ?? 100,
                        'persons_per_pass' => $passData['persons_per_pass'] ?? 1,
                    ]);
                }
            }
        }
        
        // --- ASSIGNATION DES AGENTS ---
        if ($request->has('assigned_agents')) {
            $listing->agents()->delete(); // Clear existing
            foreach ($request->assigned_agents as $agentId) {
                \App\Models\MarketplaceAgent::create([
                    'listing_id' => $listing->id,
                    'user_id' => $agentId
                ]);
            }
        }

        return redirect()->route('admin.marketplace-listings.index')->with('flash_success', 'Annonce publiée avec succès');
    }

    public function edit($id)
    {
        $listing = MarketplaceListing::findOrFail($id);
        $categories = MarketplaceCategory::whereNull('parent_id')->with('children')->orderBy('order_index')->get();
        $agents = \App\Models\User::whereHas('partner', function ($query) {
            $query->where('type', 'STATION_AGENT');
        })->orWhereHas('stationAgent')->get();
        return view('admin.marketplace.listings.edit', compact('listing', 'categories', 'agents'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string',
            'sub_category' => 'nullable|string',
        ]);

        $listing = MarketplaceListing::findOrFail($id);
        $listing->update($request->except(['cover_image', 'metadata']));

        // Déterminer le type automatiquement
        if ($request->category == 'VEHICLES') {
            $listing->type = 'VEHICLE';
        } else {
            $listing->type = 'ARTICLE';
        }

        if ($request->hasFile('cover_image')) {
            $listing->cover_image = $request->file('cover_image')->store('marketplace', 's3');
        }

        // Mettre à jour toutes les métadonnées dynamiques
        $metadata = $request->input('metadata', []);
        if ($request->has('sub_category')) {
            $metadata['sub_category'] = $request->sub_category;
        }
        $listing->metadata = $metadata;
        
        $listing->save();

        // --- GESTION DES PASSES ---
        if (in_array($request->category, ['TICKETS', 'TRAVEL']) && $request->has('passes')) {
            \App\Models\EventPassType::where('listing_id', $listing->id)->delete();
            foreach ($request->passes as $passData) {
                if (!empty($passData['name'])) {
                    \App\Models\EventPassType::create([
                        'listing_id' => $listing->id,
                        'name' => $passData['name'],
                        'price' => $passData['price'] ?? $listing->price,
                        'valid_from' => $passData['valid_from'] ?? '00:00:00',
                        'valid_until' => $passData['valid_until'] ?? '23:59:59',
                        'quantity' => $passData['quantity'] ?? 100,
                        'persons_per_pass' => $passData['persons_per_pass'] ?? 1,
                    ]);
                }
            }
        }
        
        // --- ASSIGNATION DES AGENTS ---
        if ($request->has('assigned_agents')) {
            $listing->agents()->delete(); // Clear existing
            foreach ($request->assigned_agents as $agentId) {
                \App\Models\MarketplaceAgent::create([
                    'listing_id' => $listing->id,
                    'user_id' => $agentId
                ]);
            }
        }

        return redirect()->route('admin.marketplace-listings.index')->with('flash_success', 'Annonce mise à jour avec succès');
    }

    public function destroy($id)
    {
        $listing = MarketplaceListing::findOrFail($id);
        $listing->delete();
        return redirect()->route('admin.marketplace-listings.index')->with('flash_success', 'Annonce supprimée avec succès');
    }

    // ----------------------------------------------------------------
    // PHOTO CRUD
    // ----------------------------------------------------------------

    public function photos($id)
    {
        $listing = MarketplaceListing::findOrFail($id);
        return view('admin.marketplace.listings.photos', compact('listing'));
    }

    public function storePhoto(Request $request, $id)
    {
        $request->validate(['photo' => 'required|image|max:10240']);
        $listing = MarketplaceListing::findOrFail($id);

        $disk = env('FILESYSTEM_DISK', config('filesystems.default', 's3'));
        $path = $request->file('photo')->store('listings', $disk);

        $images = is_array($listing->images) ? $listing->images : [];
        $images[] = $path;
        $listing->images = $images;

        // If no cover image, set this as cover
        if (empty($listing->cover_image)) {
            $listing->cover_image = $path;
        }

        $listing->save();

        return response()->json(['success' => true, 'path' => $path]);
    }

    public function reorderPhotos(Request $request, $id)
    {
        $listing = MarketplaceListing::findOrFail($id);
        $order = $request->input('order', []);

        if (!is_array($order)) {
            return response()->json(['success' => false, 'message' => 'Invalid order']);
        }

        $listing->images = $order;
        // Set first image as cover
        if (!empty($order)) {
            $listing->cover_image = $order[0];
        }
        $listing->save();

        return response()->json(['success' => true]);
    }

    public function setMainPhoto(Request $request, $id)
    {
        $listing = MarketplaceListing::findOrFail($id);
        $path = $request->input('path');

        $images = is_array($listing->images) ? $listing->images : [];
        if (!in_array($path, $images)) {
            return response()->json(['success' => false, 'message' => 'Image non trouvée dans cette annonce']);
        }

        $listing->cover_image = $path;
        // Move this image to front of array
        $images = array_values(array_filter($images, fn($i) => $i !== $path));
        array_unshift($images, $path);
        $listing->images = $images;
        $listing->save();

        return response()->json(['success' => true]);
    }

    public function deletePhoto(Request $request, $id)
    {
        $listing = MarketplaceListing::findOrFail($id);
        $path = $request->input('path');

        $images = is_array($listing->images) ? $listing->images : [];
        $images = array_values(array_filter($images, fn($i) => $i !== $path));
        $listing->images = $images;

        // If deleted image was cover, update cover to first remaining image
        if ($listing->cover_image === $path) {
            $listing->cover_image = $images[0] ?? null;
        }

        $listing->save();

        // Optionally delete from storage
        try {
            $disk = env('FILESYSTEM_DISK', config('filesystems.default', 's3'));
            \Illuminate\Support\Facades\Storage::disk($disk)->delete($path);
        } catch (\Exception $e) {
            Log::warning('deletePhoto: could not delete file from storage: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    private function notifyAuthor(string $phone, string $message): void
    {
        $evoUrl      = config('services.evolution.url')  ?: env('EVOLUTION_API_URL',  'http://evolution-api-service:8080');
        $evoKey      = config('services.evolution.key')  ?: env('EVOLUTION_API_KEY',  'picme225-evolution-secret-key');
        $evoInstance = config('services.evolution.instance') ?: env('EVOLUTION_INSTANCE', 'picme_whatsapp');

        if (empty($evoUrl) || empty($evoKey)) {
            Log::warning('MarketplaceListingController: Evolution API not configured, notification skipped.');
            return;
        }

        // Always use phone_number@s.whatsapp.net for private messages — reliable format.
        $whatsappUser = \App\Models\WhatsappUser::where('phone_number', $phone)->first();
        $rawPhone = preg_replace('/[^0-9]/', '', $whatsappUser ? $whatsappUser->phone_number : $phone);
        $whatsappId = $rawPhone . '@s.whatsapp.net';

        try {
            $resp = Http::withHeaders(['apikey' => $evoKey])
                ->timeout(10)
                ->post("{$evoUrl}/message/sendText/{$evoInstance}", [
                    'number'  => $whatsappId,
                    'text'    => $message,
                    'options' => [
                        'delay'    => 1200,
                        'presence' => 'composing',
                    ],
                ]);

            Log::info('notifyAuthor (Marketplace) envoyé', [
                'phone'  => $phone,
                'to'     => $whatsappId,
                'status' => $resp->status(),
            ]);

            if ($resp->failed()) {
                Log::warning('notifyAuthor (Marketplace) failed', [
                    'status' => $resp->status(),
                    'body'   => $resp->body(),
                    'phone'  => $phone,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('notifyAuthor (Marketplace) exception: ' . $e->getMessage());
        }
    }
}

