<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\MarketplaceListing;
use App\Models\RentalBooking;
use App\Models\UserRequests;
use App\Models\EventPassType;
use App\Models\TransportTicket;
use App\Models\WalletPassbook;
use App\Helpers\Helper;
use App\Models\ServiceType;
use App\Models\Post;
use App\Models\MarketplaceAgent;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MarketplaceCartController extends Controller
{
        public function getCart(Request $request): JsonResponse
        {
            $user = $request->user();
            $cart = json_decode($user->cart_data ?? '[]', true) ?? [];
    
            // Hydrater chaque item avec les données fraîches du produit
            $hydrated = [];
            foreach ($cart as $item) {
                $listing = MarketplaceListing::with('user:id,first_name,last_name,display_name,picture')
                    ->whereIn('status', ['ACTIVE', 'RENTED'])
                    ->find($item['listing_id'] ?? null);
    
                if (!$listing) continue; // Article supprimé ou vendu
    
                $coverUrl = $listing->cover_image;
                if ($coverUrl && !str_starts_with($coverUrl, 'http')) {
                    $coverUrl = \Storage::disk('s3')->url( $coverUrl);
                }
                if (!$coverUrl && $listing->images) {
                    $images = is_string($listing->images) ? json_decode($listing->images, true) : $listing->images;
                    if (is_array($images) && count($images) > 0) {
                        $coverUrl = $images[0];
                        if (!str_starts_with($coverUrl, 'http')) {
                            $coverUrl = \Storage::disk('s3')->url( $coverUrl);
                        }
                    }
                }
                
                $realPrice = (float) $listing->price;
                if ($realPrice == 0 && $listing->metadata) {
                    $meta = is_string($listing->metadata) ? json_decode($listing->metadata, true) : $listing->metadata;
                    if (is_array($meta)) {
                        if (isset($meta['price_per_day'])) {
                            $realPrice = (float) $meta['price_per_day'];
                        } elseif (isset($meta['price_per_month'])) {
                            $realPrice = (float) $meta['price_per_month'];
                        } elseif (isset($meta['price'])) {
                            $realPrice = (float) $meta['price'];
                        }
                    }
                }
    
                $sellerName = $listing->user
                    ? ($listing->user->display_name ?: $listing->user->first_name . ' ' . $listing->user->last_name)
                    : 'Vendeur';
    
                $hydrated[] = [
                    'cart_item_id'   => $item['cart_item_id'] ?? Str::uuid(),
                    'listing_id'     => $listing->id,
                    'title'          => $listing->title,
                    'price'          => $realPrice,
                    'original_price' => (float) ($listing->original_price ?? $realPrice),
                    'discount_pct'   => $listing->discount_percent ?? 0,
                    'cover_image'    => $coverUrl,
                    'quantity'       => max(1, $item['quantity'] ?? 1),
                    'seller_id'      => $listing->user_id,
                    'seller_name'    => $sellerName,
                    'seller_lat'     => (float) $listing->location_latitude,
                    'seller_lng'     => (float) $listing->location_longitude,
                    'category'       => $listing->category,
                    'is_digital'     => (bool)($listing->is_digital || strtolower($listing->category ?? '') === 'tickets' || str_contains(strtolower($listing->category ?? ''), 'ticket')),
                    'price_unit'     => $listing->price_unit ?? 'total',
                    'status'         => $listing->status,
                ];
            }
    
            $subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $hydrated));
            $savings  = array_sum(array_map(fn($i) => ($i['original_price'] - $i['price']) * $i['quantity'], $hydrated));
    
            return response()->json([
                'success'     => true,
                'data'        => $hydrated,
                'summary'     => [
                    'item_count'    => count($hydrated),
                    'subtotal'      => $subtotal,
                    'savings'       => max(0, $savings),
                    'wallet_balance'=> (float) ($user->wallet_balance ?? 0),
                ],
            ]);
        }

        public function addToCart(Request $request, $listingId): JsonResponse
        {
            Log::info("Marketplace: Adding to cart", ['user_id' => Auth::id(), 'listing_id' => $listingId]);
            $request->validate(['quantity' => 'nullable|integer|min:1|max:10']);
            $user  = $request->user();
            $listing = MarketplaceListing::findOrFail($listingId);
    
            if ($listing->user_id === $user->id) {
                return response()->json(['error' => 'Vous ne pouvez pas ajouter votre propre article au panier.'], 403);
            }
    
            $cart = json_decode($user->cart_data ?? '[]', true) ?? [];
    
            // Chercher si l'article est déjà dans le panier
            $found = false;
            foreach ($cart as &$item) {
                if (($item['listing_id'] ?? null) == $listingId) {
                    $item['quantity'] = min(10, ($item['quantity'] ?? 1) + ($request->quantity ?? 1));
                    $found = true;
                    break;
                }
            }
            unset($item);
    
            if (!$found) {
                $cart[] = [
                    'cart_item_id' => (string) Str::uuid(),
                    'listing_id'   => (int) $listingId,
                    'quantity'     => $request->quantity ?? 1,
                ];
            }
    
            $user->update(['cart_data' => json_encode($cart)]);
    
            return response()->json([
                'success'    => true,
                'message'    => '✅ Article ajouté au panier !',
                'cart_count' => count($cart),
            ]);
        }

        public function updateCartItem(Request $request, $listingId): JsonResponse
        {
            $request->validate(['quantity' => 'required|integer|min:0|max:10']);
            $user = $request->user();
            $cart = json_decode($user->cart_data ?? '[]', true) ?? [];
    
            if ($request->quantity == 0) {
                // Supprimer l'article
                $cart = array_filter($cart, fn($i) => ($i['listing_id'] ?? null) != $listingId);
            } else {
                foreach ($cart as &$item) {
                    if (($item['listing_id'] ?? null) == $listingId) {
                        $item['quantity'] = $request->quantity;
                        break;
                    }
                }
                unset($item);
            }
    
            $user->update(['cart_data' => json_encode(array_values($cart))]);
            return response()->json(['success' => true, 'message' => 'Panier mis à jour.', 'cart_count' => count($cart)]);
        }

        public function removeFromCart(Request $request, $listingId): JsonResponse
        {
            $user = $request->user();
            $cart = json_decode($user->cart_data ?? '[]', true) ?? [];
            $cart = array_values(array_filter($cart, fn($i) => ($i['listing_id'] ?? null) != $listingId));
            $user->update(['cart_data' => json_encode($cart)]);
            return response()->json(['success' => true, 'message' => 'Article retiré du panier.', 'cart_count' => count($cart)]);
        }

        public function clearCart(Request $request): JsonResponse
        {
            $request->user()->update(['cart_data' => '[]']);
            return response()->json(['success' => true, 'message' => 'Panier vidé.']);
        }

        public function calculateDelivery(Request $request): JsonResponse
        {
            Log::info("Marketplace: Calculating delivery", $request->all());
            $request->validate([
                'buyer_lat'   => 'required|numeric',
                'buyer_lng'   => 'required|numeric',
                'listing_ids' => 'required|array|min:1',
            ]);
    
            $buyerLat = (float) $request->buyer_lat;
            $buyerLng = (float) $request->buyer_lng;
    
            // On ne filtre plus par coordonnées non nulles pour accepter l'immobilier et les véhicules
            $listings = MarketplaceListing::with('user:id,first_name,display_name')
                ->whereIn('id', $request->listing_ids)
                ->get();
    
            if ($listings->isEmpty()) {
                return response()->json(['error' => 'Aucun article valide trouvé.'], 404);
            }
    
            $grouped = $listings->groupBy('user_id');
    
            $specialVendors = [];
            $vendorDistances = [];
            
            // Extraire les quantités du panier pour les frais par véhicule
            $user = $request->user();
            $cart = json_decode($user->cart_data ?? '[]', true) ?? [];
            $qtyMap = [];
            foreach ($cart as $c) {
                $qtyMap[$c['listing_id'] ?? 0] = $c['quantity'] ?? 1;
            }
    
            foreach ($grouped as $sellerId => $items) {
                $first = $items->first();
                $isVehicle = false;
                $isRealEstate = false;
                $isAllDigital = true;
                $vehicleFee = 0;
                $realEstateFee = 0;
    
                foreach ($items as $item) {
                    $cat = strtolower($item->category ?? '');
                    $qty = $qtyMap[$item->id] ?? 1;
                    $isRental = ($item->price_unit === 'day' || $item->price_unit === 'month');
    
                    // Check if it's a digital product or ticket
                    $isItemDigital = ($item->is_digital || $cat === 'tickets' || str_contains($cat, 'ticket'));
                    if (!$isItemDigital) {
                        $isAllDigital = false;
                    }
    
                    if (str_contains($cat, 'véhicule') || str_contains($cat, 'vehicule') || str_contains($cat, 'auto') || str_contains($cat, 'vehicles')) {
                        $isVehicle = true;
                        // Location = Frais payés 1 fois. Achat = Frais x Quantité.
                        $vehicleFee += $isRental ? 5000 : (5000 * $qty);
                    } elseif (str_contains($cat, 'immobili') || str_contains($cat, 'maison') || str_contains($cat, 'real_estate')) {
                        $isRealEstate = true;
                        if ($isRental) {
                            // Location : Frais de visite uniquement si mensuel OU > 1 an (jour)
                            $isLongTerm = ($item->price_unit === 'month') || ($item->price_unit === 'day' && $qty > 365);
                            if ($isLongTerm) {
                                $realEstateFee += 5000;
                            }
                        } else {
                            // Achat immobilier : Frais de visite standard
                            $realEstateFee += 5000;
                        }
                    }
                }
    
                if ($isAllDigital) {
                    // Digital or Tickets: 0 delivery fee, no coordinates check
                    $specialVendors[$sellerId] = [
                        'listing'  => $first,
                        'dist_km'  => 0,
                        'base_fee' => 0,
                        'reason'   => 'Produit numérique / Billet (Aucun frais de livraison)',
                    ];
                } elseif ($isVehicle || $isRealEstate) {
                    $fee = 0;
                    $reason = '';
                    if ($isVehicle && $isRealEstate) {
                        $fee = $vehicleFee + $realEstateFee;
                        $reason = 'Frais logistique véhicule + visite immobilier';
                    } elseif ($isVehicle) {
                        $fee = $vehicleFee;
                        $reason = 'Frais logistique véhicule (obligatoire)';
                    } else {
                        $fee = $realEstateFee;
                        $reason = $realEstateFee > 0 ? 'Frais de visite (obligatoire)' : 'Frais de visite offerts (court séjour)';
                    }
    
                    $specialVendors[$sellerId] = [
                        'listing'  => $first,
                        'dist_km'  => 0,
                        'base_fee' => $fee,
                        'reason'   => $reason,
                    ];
                } else {
                    $lat = (float) $first->location_latitude;
                    $lng = (float) $first->location_longitude;
                    if ($lat == 0 || $lng == 0) {
                        $dist = 0;
                        $baseFee = 1000;
                    } else {
                        $dist  = $this->haversineDistance($lat, $lng, $buyerLat, $buyerLng);
                        $baseFee = $this->deliveryFeeFromDistance($dist);
                    }
                    $vendorDistances[$sellerId] = [
                        'listing'  => $first,
                        'dist_km'  => $dist,
                        'base_fee' => $baseFee,
                    ];
                }
            }
    
            uasort($vendorDistances, fn($a, $b) => $b['dist_km'] <=> $a['dist_km']);
    
            $breakdown       = [];
            $totalDeliveryFee = 0;
            $paidCount       = 0;
            $maxPaidVendors  = 3;
            $savedAmount     = 0;
    
            foreach ($specialVendors as $sellerId => $data) {
                $first = $data['listing'];
                $sellerName = $first->user ? ($first->user->display_name ?: $first->user->first_name) : 'Vendeur';
                $fee = $data['base_fee'];
                $reason = $data['reason'] ?? 'Frais de service (obligatoire)';
    
                $breakdown[] = [
                    'seller_id'    => $sellerId,
                    'seller_name'  => $sellerName,
                    'distance_km'  => 0,
                    'duration_min' => 0,
                    'base_fee'     => $fee,
                    'fee'          => $fee,
                    'free'         => false,
                    'free_reason'  => $reason,
                ];
                $totalDeliveryFee += $fee;
            }
    
            foreach ($vendorDistances as $sellerId => $data) {
                $first      = $data['listing'];
                $dist       = $data['dist_km'];
                $baseFee    = $data['base_fee'];
                $sellerName = $first->user ? ($first->user->display_name ?: $first->user->first_name) : 'Vendeur';
    
                if ($paidCount < $maxPaidVendors) {
                    $fee  = $baseFee;
                    $free = false;
                    $paidCount++;
                } else {
                    $fee        = 0;
                    $free       = true;
                    $savedAmount += $baseFee;
                }
    
                $breakdown[] = [
                    'seller_id'    => $sellerId,
                    'seller_name'  => $sellerName,
                    'distance_km'  => round($dist, 1),
                    'duration_min' => max(10, (int) ($dist * 4)),
                    'base_fee'     => $baseFee,
                    'fee'          => $fee,
                    'free'         => $free,
                    'free_reason'  => $free ? '🎁 Offert à partir du 4ème vendeur' : null,
                ];
                $totalDeliveryFee += $fee;
            }
    
            usort($breakdown, fn($a, $b) => $a['distance_km'] <=> $b['distance_km']);
    
            return response()->json([
                'success'            => true,
                'breakdown'          => $breakdown,
                'total_delivery_fee' => $totalDeliveryFee,
                'vendor_count'       => count($breakdown),
                'paid_vendors'       => $paidCount + count($specialVendors),
                'free_vendors'       => count($vendorDistances) - $paidCount,
                'saved_amount'       => $savedAmount,
                'cap_rule'           => "Max {$maxPaidVendors} livraisons standard facturées. Frais de visite/véhicule obligatoires.",
            ]);
        }

        public function checkout(Request $request): JsonResponse
        {
            $request->validate([
                'buyer_lat'      => 'required|numeric',
                'buyer_lng'      => 'required|numeric',
                'buyer_address'  => 'nullable|string',
                'payment_mode'   => 'required|in:WALLET,CASH',
            ]);
    
            $user   = $request->user();
            $cart   = json_decode($user->cart_data ?? '[]', true) ?? [];
    
            if (empty($cart)) {
                return response()->json(['error' => 'Votre panier est vide.'], 400);
            }
    
            $listingIds = array_column($cart, 'listing_id');
            $listings   = MarketplaceListing::with('user')
                ->whereIn('id', $listingIds)
                ->whereIn('status', ['ACTIVE', 'RENTED'])
                ->get()
                ->keyBy('id');
    
            if ($listings->isEmpty()) {
                return response()->json(['error' => 'Aucun article disponible dans le panier.'], 400);
            }
    
            foreach ($listings as $l) {
                if (strtolower($l->category ?? '') === 'tickets') {
                    return response()->json(['error' => 'Les tickets événementiels doivent être achetés via le bouton Acheter (Achat direct), et non dans le panier.'], 400);
                }
            }
    
            // Calculer le total
            $subtotal = 0;
            foreach ($cart as $item) {
                $l = $listings->get($item['listing_id'] ?? null);
                if ($l) {
                    $realPrice = (float) $l->price;
                    if ($realPrice == 0 && $l->metadata) {
                        $meta = is_string($l->metadata) ? json_decode($l->metadata, true) : $l->metadata;
                        if (is_array($meta)) {
                            if (isset($meta['price_per_day'])) $realPrice = (float) $meta['price_per_day'];
                            elseif (isset($meta['price_per_month'])) $realPrice = (float) $meta['price_per_month'];
                            elseif (isset($meta['price'])) $realPrice = (float) $meta['price'];
                        }
                    }
                    $subtotal += $realPrice * ($item['quantity'] ?? 1);
                }
            }
    
            $sellerGroups = $listings->groupBy('user_id');
    
            $vendorFeeMap = [];
            $vendorDistMap = [];
            $specialVendors = [];
            $digitalVendors = [];
    
            foreach ($sellerGroups as $sellerId => $sellerListings) {
                $first = $sellerListings->first();
                $isVehicle = false;
                $isRealEstate = false;
                $isAllDigital = true;
                $vehicleFee = 0;
    
                foreach ($sellerListings as $item) {
                    $cat = strtolower($item->category ?? '');
                    
                    $qty = 1;
                    foreach ($cart as $c) {
                        if (($c['listing_id'] ?? null) == $item->id) {
                            $qty = $c['quantity'] ?? 1;
                            break;
                        }
                    }
    
                    $isItemDigital = ($item->is_digital || $cat === 'tickets' || str_contains($cat, 'ticket'));
                    if (!$isItemDigital) {
                        $isAllDigital = false;
                    }
    
                    if (str_contains($cat, 'véhicule') || str_contains($cat, 'vehicule') || str_contains($cat, 'auto') || str_contains($cat, 'vehicles')) {
                        $isVehicle = true;
                        $vehicleFee += (5000 * $qty);
                    } elseif (str_contains($cat, 'immobili') || str_contains($cat, 'maison') || str_contains($cat, 'real_estate')) {
                        $isRealEstate = true;
                    }
                }
    
                if ($isAllDigital) {
                    $vendorFeeMap[$sellerId] = 0;
                    $digitalVendors[] = $sellerId;
                } elseif ($isVehicle || $isRealEstate) {
                    $specialVendors[] = $sellerId;
                    if ($isVehicle && $isRealEstate) {
                        $vendorFeeMap[$sellerId] = $vehicleFee + 5000;
                    } elseif ($isVehicle) {
                        $vendorFeeMap[$sellerId] = $vehicleFee;
                    } else {
                        $vendorFeeMap[$sellerId] = 5000;
                    }
                } else {
                    $lat = (float) $first->location_latitude;
                    $lng = (float) $first->location_longitude;
                    if ($lat == 0 || $lng == 0) {
                        $dist = 0;
                        $baseFee = 1000;
                    } else {
                        $dist  = $this->haversineDistance($lat, $lng, (float) $request->buyer_lat, (float) $request->buyer_lng);
                        $baseFee = $this->deliveryFeeFromDistance($dist);
                    }
                    $vendorDistMap[$sellerId] = ['dist' => $dist, 'base_fee' => $baseFee];
                }
            }
    
            uasort($vendorDistMap, fn($a, $b) => $b['dist'] <=> $a['dist']);
    
            $paidCount = 0;
            foreach ($vendorDistMap as $sellerId => $info) {
                $vendorFeeMap[$sellerId] = ($paidCount < 3) ? $info['base_fee'] : 0;
                if ($paidCount < 3) $paidCount++;
            }
    
            $totalDelivery = array_sum($vendorFeeMap);
            $grandTotal = $subtotal + $totalDelivery;
    
            $requiredWallet = 0;
            if ($request->payment_mode === 'WALLET') {
                $requiredWallet = $grandTotal;
            } else {
                $requiredWallet = $totalDelivery;
            }
    
            if ($requiredWallet > 0 && $user->wallet_balance < $requiredWallet) {
                return response()->json([
                    'error'          => 'Solde insuffisant dans votre portefeuille pour couvrir les frais obligatoires.',
                    'required'       => $requiredWallet,
                    'wallet_balance' => $user->wallet_balance,
                ], 402);
            }
    
            $orderRefs = [];
    
            DB::transaction(function () use ($user, $cart, $listings, $sellerGroups, $request, $subtotal, $totalDelivery, $grandTotal, $requiredWallet, $vendorFeeMap, $digitalVendors, &$orderRefs) {
    
                if ($requiredWallet > 0) {
                    $user->decrement('wallet_balance', $requiredWallet);
                    WalletPassbook::create([
                        'user_id' => $user->id,
                        'amount'  => -$requiredWallet,
                        'status'  => 'DEBITED',
                        'via'     => 'CART_CHECKOUT',
                        'description' => ($request->payment_mode === 'WALLET') ? 'Commande panier ' . count($cart) . ' article(s)' : 'Frais de livraison/visite obligatoires',
                    ]);
                }
    
                foreach ($sellerGroups as $sellerId => $sellerListings) {
                    $first = $sellerListings->first();
    
                    $vendorSubtotal = 0;
                    foreach ($cart as $item) {
                        $l = $listings->get($item['listing_id'] ?? null);
                        if ($l && $l->user_id == $sellerId) {
                            $realPrice = (float) $l->price;
                            if ($realPrice == 0 && $l->metadata) {
                                $meta = is_string($l->metadata) ? json_decode($l->metadata, true) : $l->metadata;
                                if (is_array($meta)) {
                                    if (isset($meta['price_per_day'])) $realPrice = (float) $meta['price_per_day'];
                                    elseif (isset($meta['price_per_month'])) $realPrice = (float) $meta['price_per_month'];
                                    elseif (isset($meta['price'])) $realPrice = (float) $meta['price'];
                                }
                            }
                            $vendorSubtotal += $realPrice * ($item['quantity'] ?? 1);
                        }
                    }
    
                    $delFee = $vendorFeeMap[$sellerId] ?? 0;
                    $isAllDigital = in_array($sellerId, $digitalVendors);
    
                    if ($request->payment_mode === 'WALLET') {
                        $commission   = $vendorSubtotal * 0.10;
                        $sellerCredit = $vendorSubtotal - $commission;
                        $first->user->increment('wallet_balance', $sellerCredit);
                        WalletPassbook::create([
                            'user_id' => $sellerId,
                            'amount'  => $sellerCredit,
                            'status'  => 'CREDITED',
                            'via'     => 'CART_SALE',
                            'description' => 'Vente panier (hors commission 10%)',
                        ]);
    
                        $admin = \App\Models\User::where('user_type', 'admin')->first() ?: \App\Models\User::find(1);
                        if ($admin) {
                            $admin->increment('wallet_balance', $commission);
                            WalletPassbook::create([
                                'user_id' => $admin->id,
                                'amount'  => $commission,
                                'status'  => 'CREDITED',
                                'via'     => 'COMMISSION',
                                'description' => 'Commission de 10% sur vente produit par ' . ($first->user->display_name ?: $first->user->first_name),
                            ]);
                        }
                    }
    
                    foreach ($sellerListings as $l) {
                        $qty = 1;
                        foreach ($cart as $c) {
                            if (($c['listing_id'] ?? null) == $l->id) {
                                $qty = $c['quantity'] ?? 1;
                                break;
                            }
                        }
                        $isRental = in_array(strtoupper($l->type ?? ''), ['RENTAL', 'RENT', 'LOCATION']);
                        
                        if ($isRental) {
                            $l->update(['status' => 'RENTED']);
                        } else {
                            $l->decrement('stock_quantity', $qty);
                            $currentStock = MarketplaceListing::where('id', $l->id)->value('stock_quantity');
                            if ($currentStock <= 0) {
                                $l->update(['status' => 'SOLD']);
                            }
                        }
                    }
    
                    $deliveryId = 0;
                    if (!$isAllDigital) {
                        $delivery = new UserRequests();
                        $delivery->booking_id      = Helper::generate_booking_id();
                        $delivery->user_id         = $user->id;
                        $delivery->service_type_id = 1;
                        $delivery->status          = 'SEARCHING';
                        $delivery->s_latitude      = $first->location_latitude;
                        $delivery->s_longitude     = $first->location_longitude;
                        $delivery->s_address       = 'Vendeur : ' . ($first->user->display_name ?: $first->user->first_name);
                        $delivery->d_latitude      = $request->buyer_lat;
                        $delivery->d_longitude     = $request->buyer_lng;
                        $delivery->d_address       = $request->buyer_address ?? 'Adresse acheteur';
                        $delivery->payment_mode    = $request->payment_mode;
                        $delivery->method          = 'delivery';
                        $delivery->save();
                        
                        $deliveryId = $delivery->id;
                    }
    
                    $ticketRef  = Str::upper(Str::random(8));
                    $qrCode     = 'PKM-' . $ticketRef . '-' . substr(hash_hmac('sha256', $ticketRef . $user->id, config('app.key')), 0, 8);
                    $ticket     = TransportTicket::create([
                        'listing_id'         => $first->id,
                        'event_pass_type_id' => 0,
                        'transport_event_id' => 0,
                        'user_id'            => $user->id,
                        'qr_code'            => $qrCode,
                        'total_price'        => $vendorSubtotal + $delFee,
                        'payment_mode'       => $request->payment_mode,
                        'payment_status'     => ($request->payment_mode === 'WALLET') ? 'PAID' : 'PENDING_CASH',
                        'status'             => 'BOOKED',
                        'metadata'           => json_encode([
                            'cart_items'     => array_values(array_filter($cart, fn($i) => ($listings->get($i['listing_id'] ?? null)?->user_id ?? null) == $sellerId)),
                            'delivery_fee'   => $delFee,
                            'request_id'     => $deliveryId,
                            'buyer_address'  => $request->buyer_address ?? '',
                        ]),
                    ]);
    
                    $orderRefs[] = [
                        'order_ref'    => $qrCode,
                        'seller_name'  => $first->user->display_name ?: $first->user->first_name,
                        'items_count'  => $sellerListings->count(),
                        'subtotal'     => $vendorSubtotal,
                        'delivery_fee' => $delFee,
                        'request_id'   => $deliveryId,
                        'ticket_id'    => $ticket->id,
                        'track_url'    => route('ticket.view', ['booking_id' => $qrCode]),
                    ];
    
                    try {
                        (new SendPushNotification())->MarketplaceOrderReceived($sellerId, "🛍 Nouvelle commande ! {$sellerListings->count()} article(s) commandé(s).");
                    } catch (\Exception $e) {}
                }
    
                // Vider le panier
                $user->update(['cart_data' => '[]']);
            });
    
            return response()->json([
                'success'    => true,
                'message'    => '🎉 Commande passée avec succès !',
                'orders'     => $orderRefs,
                'grand_total'=> $grandTotal,
            ], 201);
        }

        public function trackOrder(Request $request, $ref): JsonResponse
        {
            // Chercher le ticket par QR code ou par ID
            $ticket = TransportTicket::with(['listing.user', 'pass'])
                ->where('qr_code', $ref)
                ->orWhere('id', is_numeric($ref) ? (int) $ref : 0)
                ->firstOrFail();
    
            // Vérification d'appartenance
            if ($ticket->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Accès refusé.'], 403);
            }
    
            $meta      = json_decode($ticket->metadata ?? '{}', true);
            $requestId = $meta['request_id'] ?? null;
    
            // Infos coursier depuis le UserRequest associé
            $courierInfo = null;
            $deliveryStatus = 'PROCESSING';
    
            if ($requestId) {
                $deliveryReq = UserRequests::with('provider.user')
                    ->find($requestId);
    
                if ($deliveryReq) {
                    $deliveryStatus = $deliveryReq->status;
                    if ($deliveryReq->provider) {
                        $provider = $deliveryReq->provider;
                        $courierInfo = [
                            'name'     => $provider->user->first_name . ' ' . $provider->user->last_name,
                            'phone'    => $provider->user->mobile ?? '',
                            'picture'  => $provider->user->picture ? \Storage::disk('s3')->url( $provider->user->picture) : null,
                            'rating'   => number_format($provider->rating ?? 4.5, 1),
                            'trips'    => $provider->total_trips ?? 0,
                            'vehicle'  => $provider->car_model ?? 'Moto',
                            'plate'    => $provider->car_number ?? '',
                        ];
                    }
                }
            }
    
            // Mapper les statuts sur les étapes de la timeline
            $steps = [
                ['key' => 'ORDER_RECEIVED',     'label' => '✅ Commande reçue',            'done' => true,                                        'icon' => 'receipt'],
                ['key' => 'SELLER_CONFIRMED',   'label' => '✅ Confirmée par le vendeur',   'done' => !in_array($deliveryStatus, ['PROCESSING']),   'icon' => 'check_circle'],
                ['key' => 'PREPARING',          'label' => '📦 En préparation',             'done' => in_array($deliveryStatus, ['STARTED', 'REACHED', 'PICKEDUP', 'SEARCHING', 'ARRIVED', 'COMPLETED']), 'icon' => 'inventory'],
                ['key' => 'COURIER_ASSIGNED',   'label' => '🏍 Coursier assigné',           'done' => $courierInfo !== null,                        'icon' => 'delivery_dining'],
                ['key' => 'IN_DELIVERY',        'label' => '🚀 En cours de livraison',      'done' => in_array($deliveryStatus, ['STARTED', 'PICKEDUP', 'ARRIVED', 'COMPLETED']), 'icon' => 'local_shipping'],
                ['key' => 'DELIVERED',          'label' => '🎉 Livré',                      'done' => in_array($deliveryStatus, ['COMPLETED']),     'icon' => 'home'],
            ];
    
            $listing = $ticket->listing;
            $coverUrl = $listing ? ($listing->cover_image && !str_starts_with($listing->cover_image, 'http') ? \Storage::disk('s3')->url( $listing->cover_image) : $listing->cover_image) : null;
    
            return response()->json([
                'success'       => true,
                'data'          => [
                    'order_ref'      => $ticket->qr_code,
                    'status'         => $ticket->status,
                    'delivery_status'=> $deliveryStatus,
                    'total_price'    => (float) $ticket->total_price,
                    'delivery_fee'   => (float) ($meta['delivery_fee'] ?? 0),
                    'buyer_address'  => $meta['buyer_address'] ?? '',
                    'created_at'     => $ticket->created_at ? $ticket->created_at->toDateTimeString() : null,
                    'listing'        => $listing ? [
                        'id' => $listing->id, 
                        'title' => $listing->title, 
                        'cover' => $coverUrl,
                        'metadata' => is_string($listing->metadata) ? json_decode($listing->metadata, true) : $listing->metadata
                    ] : null,
                    'pass'           => $ticket->pass,
                    'persons_per_pass' => (int)($meta['persons_per_pass'] ?? ($ticket->pass ? $ticket->pass->persons_per_pass : 1)),
                    'seller'         => $listing && $listing->user ? ['name' => $listing->user->first_name, 'phone' => $listing->user->mobile ?? ''] : null,
                    'courier'        => $courierInfo,
                    'steps'          => $steps,
                    'cart_items'     => $meta['cart_items'] ?? [],
                ],
            ]);
        }

}