<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\MarketplaceListing;
use App\Models\MarketplaceCategory;

class UserMarketplaceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the form to create a new listing.
     */
    public function create()
    {
        $categories = MarketplaceCategory::whereNull('parent_id')
            ->with('children')
            ->orderBy('order_index')
            ->get();
            
        $agents = \App\Models\User::whereHas('partner', function ($query) {
            $query->where('type', 'STATION_AGENT');
        })->orWhereHas('stationAgent')->get();

        return view('user.marketplace.create', compact('categories', 'agents'));
    }

    /**
     * Store a new listing (status: PENDING for admin review).
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category'    => 'required|string|max:100',
            'price'       => 'required|numeric|min:0',
            'phone'       => 'required|string|max:30',
            'location_city' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();

        // Handle photo uploads (up to 6)
        $images = [];
        if ($request->hasFile('photos')) {
            foreach (array_slice($request->file('photos'), 0, 6) as $photo) {
                $path = $photo->store('marketplace/' . $user->id, 's3');
                $images[] = $path;
            }
        }

        $isDigital = $request->has('is_digital') || $request->is_digital == '1' || str_contains(strtoupper($request->category), 'DIGITAL');
        $digitalFilePath = null;
        if ($request->hasFile('digital_file')) {
            // Fichier numérique stocké en local de manière sécurisée (ou S3 si souhaité)
            $digitalFilePath = $request->file('digital_file')->store('marketplace_digital/' . $user->id, 'local');
            $isDigital = true;
        }

        $listing = MarketplaceListing::create([
            'user_id'      => $user->id,
            'title'        => $request->title,
            'description'  => $request->description,
            'category'     => $request->category,
            'sub_category' => $request->sub_category,
            'price'        => $request->price,
            'price_unit'   => $request->price_unit ?? 'FCFA',
            'owner_name'   => $user->first_name . ' ' . $user->last_name,
            'owner_phone'  => $request->phone,
            'location_city' => $request->location_city,
            'cover_image'  => $images[0] ?? null,
            'images'       => $images,
            'status'       => 'PENDING', // Must be approved by admin
            'type'         => strtoupper($request->category),
            'is_digital'   => $isDigital,
            'digital_file_path' => $digitalFilePath,
            'metadata'     => [
                'condition'    => $request->condition ?? 'used',
                'extra_info'   => $request->extra_info,
                'price_unit'   => $request->price_unit,
                'brand'        => $request->brand,
                'model'        => $request->model,
                'year'         => $request->year,
                'color'        => $request->color,
                'plate_number' => $request->plate_number,
                'stock_quantity'=> $request->stock_quantity ?? 1,
                'rooms'        => $request->rooms,
                'bathrooms'    => $request->bathrooms,
            ],
        ]);

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

        return redirect()->route('user.marketplace.my')
            ->with('success', 'Votre annonce a été soumise et sera publiée après validation par notre équipe.');
    }

    /**
     * Show the user's own listings.
     */
    public function myListings()
    {
        $listingsQuery = MarketplaceListing::where('user_id', Auth::id())
            ->withCount(['bookings as sales_count' => function($q) {
                $q->where('status', 'COMPLETED');
            }])
            ->orderBy('created_at', 'desc');
            
        // Statistiques globales
        $totalListings = $listingsQuery->count();
        $activeListings = (clone $listingsQuery)->whereIn('status', ['ACTIVE', 'APPROVED'])->count();
        
        $allListings = $listingsQuery->get();
        $totalSales = $allListings->sum('sales_count');
        $totalRevenue = $allListings->sum(function($listing) {
            return $listing->sales_count * $listing->price;
        });

        $listings = $listingsQuery->paginate(15);

        return view('user.marketplace.my_listings', compact('listings', 'totalListings', 'activeListings', 'totalSales', 'totalRevenue'));
    }

    /**
     * Delete own listing.
     */
    public function destroy($id)
    {
        $listing = MarketplaceListing::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $listing->delete();

        return back()->with('flash_success', 'Annonce supprimée avec succès.');
    }

    /**
     * Show the marketplace for authenticated users.
     */
    public function explore(Request $request)
    {
        $categories = \App\Models\MarketplaceCategory::whereNull('parent_id')->with('children')->orderBy('order_index')->get();
        
        $query = MarketplaceListing::whereIn('status', ['ACTIVE', 'APPROVED'])->whereNull('deleted_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('city')) {
            $query->where('location_city', 'like', "%{$request->city}%");
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('category')) {
            $cat = $request->category;
            $query->where(function($q) use ($cat) {
                $q->where('category', 'like', "%{$cat}%")
                  ->orWhere('sub_category', 'like', "%{$cat}%")
                  ->orWhere('type', 'like', "%{$cat}%");
            });
        }

        $sort = $request->get('sort', 'newest');
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $listings = $query->paginate(30);

        return view('user.marketplace.explore', compact('listings', 'categories'));
    }

    /**
     * Show the form to edit the listing.
     */
    public function edit($id)
    {
        $listing = MarketplaceListing::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $categories = MarketplaceCategory::whereNull('parent_id')
            ->with('children')
            ->orderBy('order_index')
            ->get();

        return view('user.marketplace.edit', compact('listing', 'categories'));
    }

    /**
     * Update the listing in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category'    => 'required|string|max:100',
            'price'       => 'required|numeric|min:0',
            'phone'       => 'required|string|max:30',
            'location_city' => 'nullable|string|max:100',
        ]);

        $listing = MarketplaceListing::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Handle photo uploads
        $images = $listing->images ?? [];
        if ($request->hasFile('photos')) {
            $images = []; // overwrite existing images if new ones are uploaded
            foreach (array_slice($request->file('photos'), 0, 6) as $photo) {
                $path = $photo->store('marketplace/' . Auth::id(), 's3');
                $images[] = $path;
            }
        }

        $meta = $listing->metadata ?? [];
        $meta['condition'] = $request->condition ?? 'used';
        $meta['extra_info'] = $request->extra_info;
        $meta['stock_quantity'] = $request->stock_quantity ?? 1;
        if($request->has('brand')) $meta['brand'] = $request->brand;
        if($request->has('model')) $meta['model'] = $request->model;
        if($request->has('year')) $meta['year'] = $request->year;
        if($request->has('color')) $meta['color'] = $request->color;
        if($request->has('rooms')) $meta['rooms'] = $request->rooms;
        if($request->has('bathrooms')) $meta['bathrooms'] = $request->bathrooms;

        $isDigital = $listing->is_digital;
        if ($request->has('is_digital') || $request->is_digital == '1') {
            $isDigital = true;
        }

        $digitalFilePath = $listing->digital_file_path;
        if ($request->hasFile('digital_file')) {
            $digitalFilePath = $request->file('digital_file')->store('marketplace_digital/' . Auth::id(), 'local');
            $isDigital = true;
        }

        $listing->update([
            'title'        => $request->title,
            'description'  => $request->description,
            'category'     => $request->category,
            'sub_category' => $request->sub_category,
            'price'        => $request->price,
            'price_unit'   => $request->price_unit ?? 'FCFA',
            'owner_phone'  => $request->phone,
            'location_city' => $request->location_city,
            'cover_image'  => $images[0] ?? $listing->cover_image,
            'images'       => $images,
            'metadata'     => $meta,
            'is_digital'   => $isDigital,
            'digital_file_path' => $digitalFilePath,
        ]);

        // --- GESTION DES PASSES (TICKETS & TRAVEL) ---
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

        return redirect()->route('user.marketplace.my')
            ->with('success', 'Annonce mise à jour avec succès.');
    }

    /**
     * Show the detailed marketplace product page under authenticated User middleware.
     */
    public function detail($id)
    {
        $listing = MarketplaceListing::where('id', $id)->firstOrFail();
        
        // Ensure active state
        if ($listing->status !== 'ACTIVE' && $listing->status !== 'APPROVED') {
            abort(404);
        }

        // Related listings
        $related = MarketplaceListing::where('status', 'ACTIVE')
            ->where('id', '!=', $id)
            ->when($listing->category, fn($q) => $q->where('category', $listing->category))
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        $isPurchased = $listing->isPurchasedBy(Auth::id());

        return view('user.marketplace.detail', compact('listing', 'related', 'isPurchased'));
    }

    /**
     * Procède à l'achat d'un produit digital par Wallet
     */
    public function purchaseDigitalProduct(Request $request, $id)
    {
        $listing = MarketplaceListing::findOrFail($id);
        
        if (!$listing->is_digital) {
            return response()->json(['error' => 'Ce produit n\'est pas un produit digital.'], 400);
        }

        $user = Auth::user();

        // 1. Vérification si déjà acheté
        if ($listing->isPurchasedBy($user->id)) {
            return response()->json([
                'success' => true,
                'message' => 'Vous avez déjà acheté ce produit.',
                'download_url' => route('user.marketplace.download_digital', $listing->id)
            ]);
        }

        // 2. Vérification du solde du portefeuille
        if ($user->wallet_balance >= $listing->price) {
            \Illuminate\Support\Facades\DB::transaction(function() use ($user, $listing) {
                // Débiter l'acheteur
                $user->wallet_balance -= $listing->price;
                $user->save();

                // Enregistrer la transaction pour l'acheteur (Passbook)
                \App\Models\WalletPassbook::create([
                    'user_id' => $user->id,
                    'amount' => $listing->price,
                    'status' => 'DEBIT',
                    'via' => 'WALLET',
                    'transaction_id' => 'DIGITAL_BUY_' . time() . '_' . $listing->id,
                    'description' => 'Achat produit digital : ' . $listing->title,
                ]);

                // Créditer le vendeur (s'il existe)
                if ($listing->user) {
                    $seller = $listing->user;
                    $seller->wallet_balance += $listing->price;
                    $seller->save();

                    \App\Models\WalletPassbook::create([
                        'user_id' => $seller->id,
                        'amount' => $listing->price,
                        'status' => 'CREDIT',
                        'via' => 'WALLET',
                        'transaction_id' => 'DIGITAL_SELL_' . time() . '_' . $listing->id,
                        'description' => 'Vente produit digital : ' . $listing->title,
                    ]);
                }

                // Créer le booking d'achat complété
                \App\Models\RentalBooking::create([
                    'listing_id' => $listing->id,
                    'user_id' => $user->id,
                    'total_price' => $listing->price,
                    'status' => 'COMPLETED',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Achat effectué avec succès !',
                'download_url' => route('user.marketplace.download_digital', $listing->id)
            ]);
        }

        // 3. Si solde insuffisant, proposer le rechargement
        return response()->json([
            'error' => 'SOLDE_INSUFFISANT',
            'message' => 'Votre solde est insuffisant pour acheter ce produit.',
            'balance' => $user->wallet_balance,
            'price' => $listing->price,
            'recharge_url' => url('/wallet')
        ], 402);
    }

    /**
     * Téléchargement sécurisé du fichier digital
     */
    public function downloadDigitalProduct($id)
    {
        $listing = MarketplaceListing::findOrFail($id);
        
        if (!$listing->is_digital || !$listing->digital_file_path) {
            abort(404, 'Fichier introuvable.');
        }

        $user = Auth::user();

        // Vérifier l'achat
        if (!$listing->isPurchasedBy($user->id)) {
            abort(403, 'Vous devez acheter ce produit pour le télécharger.');
        }

        $filePath = $listing->digital_file_path;

        // Si le fichier est stocké sur S3
        if (Storage::disk('s3')->exists($filePath)) {
            return Storage::disk('s3')->download($filePath, basename($filePath));
        }

        // Si le fichier est en local
        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->download($filePath, basename($filePath));
        }

        abort(404, 'Le fichier physique n\'existe pas sur le serveur de stockage.');
    }

    /**
     * Procède à l'achat d'un ticket ou réservation (PWA)
     */
    public function buyTicket(Request $request, $id)
    {
        try {
            $paymentMode = $request->input('payment_mode', 'WALLET');
            $passId = $request->input('pass_type_id');
            $userLat = $request->input('user_lat', 5.3484);
            $userLng = $request->input('user_lng', -4.0244);
            
            $service = new \App\Services\TicketPurchaseService();
            $ticket = $service->purchase($id, Auth::user(), $paymentMode, $passId, $userLat, $userLng);

            return response()->json([
                'success' => true,
                'message' => 'Opération validée !',
                'ticket_url' => route('ticket.view', ['booking_id' => $ticket->qr_code ?? 'RENT-'.$ticket->id])
            ]);
        } catch (\Exception $e) {
            \Log::error("Erreur achat ticket PWA : " . $e->getMessage());
            
            if (str_contains($e->getMessage(), 'Solde insuffisant')) {
                return response()->json([
                    'error' => 'SOLDE_INSUFFISANT',
                    'message' => $e->getMessage(),
                    'recharge_url' => url('/wallet')
                ], 402);
            }
            
            return response()->json([
                'error' => 'Erreur technique lors de l\'achat : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Liste les achats et tickets
     */
    public function myPurchases(Request $request)
    {
        $user = Auth::user();
        
        $purchases = \App\Models\TransportTicket::where('user_id', $user->id)
            ->with(['listing.user', 'pass'])
            ->latest()
            ->get();
            
        $bookings = \App\Models\RentalBooking::where('user_id', $user->id)
            ->with(['listing.user'])
            ->latest()
            ->get();

        return view('user.marketplace.purchases', compact('purchases', 'bookings'));
    }

    /**
     * Get a paginated feed of active marketplace listings.
     */
    public function feed(Request $request)
    {
        $query = MarketplaceListing::whereIn('status', ['ACTIVE', 'APPROVED'])
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc');

        $listings = $query->paginate(10);

        $listings->getCollection()->transform(function($listing) {
            return [
                'id' => $listing->id,
                'title' => $listing->title,
                'price' => number_format($listing->price, 0, ',', ' '),
                'price_unit' => $listing->price_unit ?? 'FCFA',
                'category' => $listing->category,
                'location_city' => $listing->location_city ?? 'Abidjan',
                'cover_image' => $listing->cover_image ? img($listing->cover_image) : asset('images/default_product.png'),
                'is_sponsored' => $listing->is_sponsored ?? false,
                'detail_url' => route('user.marketplace.detail', $listing->id),
            ];
        });

        return response()->json($listings);
    }
}
