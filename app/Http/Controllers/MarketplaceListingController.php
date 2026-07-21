<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceListing;
use App\Models\EventPassType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MarketplaceListingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = MarketplaceListing::with(['user:id,first_name,last_name,display_name,picture', 'corridor', 'passes'])
                ->where('status', 'ACTIVE')
                ->whereNull('deleted_at');

            // Filter by type
            if ($request->has('type') && !empty($request->type)) {
                $query->where('type', $request->type);
            }

            // Filter by category
            if ($request->has('category') && $request->category !== 'Tout' && !empty($request->category)) {
                $category = $request->category;
                
                // Include subcategories
                $childNames = \App\Models\MarketplaceCategory::whereHas('parent', function($q) use ($category) {
                    $q->where('name', $category);
                })->pluck('name')->toArray();

                if (!empty($childNames)) {
                    $query->where(function($q) use ($category, $childNames) {
                        $q->whereIn('category', array_merge([$category], $childNames))
                          ->orWhereIn('sub_category', array_merge([$category], $childNames));
                    });
                } else {
                    $query->where(function($q) use ($category) {
                        $q->where('category', $category)
                          ->orWhere('sub_category', $category);
                    });
                }
            }

            // Search query
            if ($request->has('q') && !empty($request->q)) {
                $search = $request->q;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Latitude & Longitude radius search (if provided)
            if ($request->has('latitude') && $request->has('longitude')) {
                $latitude = floatval($request->latitude);
                $longitude = floatval($request->longitude);
                $radius = floatval($request->input('radius', 50)); // Default 50 km
                
                if ($latitude && $longitude) {
                    $lat_deg = $radius / 111.0;
                    $cosLat = cos(deg2rad($latitude));
                    $lng_deg = $cosLat != 0 ? $radius / (111.0 * $cosLat) : $radius / 111.0;
                    
                    $query->whereBetween('metadata->location_latitude', [$latitude - $lat_deg, $latitude + $lat_deg])
                          ->whereBetween('metadata->location_longitude', [$longitude - $lng_deg, $longitude + $lng_deg]);
                }
            }

            $listings = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data'    => $listings
            ]);
        } catch (\Exception $e) {
            \Log::error("Erreur de récupération des annonces marketplace : " . $e->getMessage());
            return response()->json(['error' => 'Erreur technique lors du chargement des annonces.'], 500);
        }
    }

    public function my_listings(): JsonResponse
    {
        try {
            $user = \Illuminate\Support\Facades\Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $listings = \App\Models\MarketplaceListing::with(['corridor', 'passes'])
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $listings
            ]);
        } catch (\Exception $e) {
            \Log::error("Erreur de récupération de mes annonces marketplace : " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Internal Server Error'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type'            => 'nullable|string|max:50',
            'title'           => 'required|string|max:200',
            'description'     => 'required|string|max:5000',
            'price'           => 'required|numeric|min:0',
            'location_latitude'  => 'nullable|numeric',
            'location_longitude' => 'nullable|numeric',
            'pickup_address'  => 'nullable|string|max:255',
            'cover_image'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'with_driver'     => 'nullable|boolean',
            'driver_price'    => 'nullable|numeric|min:0',
            'driving_policy'  => 'nullable|string',
            'category'        => 'nullable|string',
            'price_unit'      => 'nullable|string|in:total,day,month',
            'is_digital'      => 'nullable|boolean',
            'digital_file'    => 'nullable|file|max:512000',
        ]);

        // Defaults pour les champs manquants envoyés par l'app mobile
        if (empty($request->type)) $request->merge(['type' => 'ARTICLE']);
        if (empty($request->location_latitude)) $request->merge(['location_latitude' => 5.34]);
        if (empty($request->location_longitude)) $request->merge(['location_longitude' => -4.0]);
        if (empty($request->pickup_address)) $request->merge(['pickup_address' => 'Abidjan, Côte d\'Ivoire']);

        $data = $request->all();

        // 🔧 FIX ANDROID ENCODING VOLLEY BUG
        $cleanString = function($str) {
            if (!$str) return $str;
            return mb_convert_encoding($str, 'UTF-8', mb_detect_encoding($str, 'UTF-8, ISO-8859-1', true) ?: 'UTF-8');
        };

        if (isset($data['category'])) $data['category'] = $cleanString($data['category']);
        if (isset($data['title'])) $data['title'] = $cleanString($data['title']);
        if (isset($data['description'])) $data['description'] = $cleanString($data['description']);

        $data['user_id'] = Auth::id();
        $data['status']  = 'ACTIVE';
        
        $data['is_digital'] = filter_var($request->input('is_digital', false), FILTER_VALIDATE_BOOLEAN);

        if ($request->hasFile('digital_file') && $data['is_digital']) {
            try {
                $data['digital_file_path'] = $request->file('digital_file')->store('digital_products', 's3');
            } catch (\Exception $e) {
                \Log::error('S3 Upload Error: ' . $e->getMessage());
                return response()->json(['error' => 'Erreur d\'upload du fichier numérique. Vérifiez la configuration S3.'], 500);
            }
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('marketplace', 's3');
        }

        if ($request->hasFile('images')) {
            $extraImages = [];
            foreach ($request->file('images') as $file) {
                $extraImages[] = $file->store('marketplace', 's3');
            }
            $data['images'] = $extraImages;
        }

        if (isset($data['metadata']) && is_string($data['metadata'])) {
            $metaString = $data['metadata'];
            $metaString = mb_convert_encoding($metaString, 'UTF-8', mb_detect_encoding($metaString, 'UTF-8, ISO-8859-1', true) ?: 'UTF-8');
            $data['metadata'] = json_decode($metaString, true) ?: [];
        }

        $listing = MarketplaceListing::create($data);

        // --- UNIFICATION BILLETTERIE (Si catégorie TICKETS) ---
        if ($request->category === 'TICKETS' || $request->has('passes')) {
            $passes = $request->input('passes');
            if (is_string($passes)) $passes = json_decode($passes, true);

            if ($passes && is_array($passes)) {
                foreach ($passes as $p) {
                    EventPassType::create([
                        'listing_id'  => $listing->id,
                        'name'        => $cleanString($p['name']),
                        'price'       => $p['price'] ?? $listing->price,
                        'valid_from'  => $p['valid_from'],
                        'valid_until' => $p['valid_until'],
                        'quantity'    => $p['quantity'] ?? 100,
                        'persons_per_pass' => $p['persons_per_pass'] ?? 1,
                    ]);
                }
            } else {
                EventPassType::create([
                    'listing_id'  => $listing->id,
                    'name'        => 'Accès Standard',
                    'price'       => $listing->price,
                    'valid_from'  => '00:00:00',
                    'valid_until' => '23:59:59',
                    'quantity'    => 100,
                    'persons_per_pass' => 1,
                ]);
            }

            try {
                \App\Models\Post::create([
                    'user_id'   => Auth::id(),
                    'type'      => 'SALE',
                    'source'    => 'INTERNAL',
                    'category'  => 'MARKETPLACE',
                    'content'   => "🎫 ÉVÉNEMENT : " . $listing->title . "\n" . \Illuminate\Support\Str::limit($listing->description, 150) . "\n\nRéservez vos places dès maintenant sur PicMe !",
                    'media_url' => $listing->cover_image ? url('storage/' . $listing->cover_image) : null,
                    'price'     => $listing->price,
                    'latitude'  => $listing->location_latitude,
                    'longitude' => $listing->location_longitude,
                    'status'    => 'ACTIVE',
                    'published_at' => \Carbon\Carbon::now(),
                ]);
            } catch (\Exception $e) {
                \Log::error("Social Sync failed: " . $e->getMessage());
            }
        }

        if ($request->has('metadata')) {
            $metadata = $request->input('metadata');
            if (is_string($metadata)) {
                $metadata = json_decode($metadata, true);
            }
            if ($metadata) {
                $listing->metadata = $metadata;
                $listing->save();
            }
        }

        try {
            broadcast(new \App\Events\NewSocialTripPosted(
                $listing->id,
                'MARKETPLACE',
                null,
                ['title' => $listing->title ?? 'Nouvelle annonce', 'price' => $listing->price ?? 0]
            ))->toOthers();
        } catch (\Exception $e) {
            \Log::warning("Erreur Push Marketplace: " . $e->getMessage());
        }

        // --- ASSIGNATION DES AGENTS ---
        if ($request->has('assigned_agents_ids')) {
            $agentIds = $request->input('assigned_agents_ids');
            if (is_string($agentIds)) {
                $agentIds = json_decode($agentIds, true);
            }
            if (is_array($agentIds)) {
                foreach ($agentIds as $agentId) {
                    \App\Models\MarketplaceAgent::create([
                        'listing_id' => $listing->id,
                        'user_id' => $agentId
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Annonce publiée avec succès !',
            'data'    => $listing
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $listing = MarketplaceListing::findOrFail($id);

            if ($listing->user_id !== Auth::id()) {
                return response()->json(['error' => 'Action non autorisée. Vous n\'êtes pas le propriétaire.'], 403);
            }

            $request->validate([
                'title'              => 'nullable|string|max:100',
                'description'        => 'nullable|string|max:1000',
                'price'              => 'nullable|numeric|min:0',
                'category'           => 'nullable|string',
                'location_latitude'  => 'nullable|numeric',
                'location_longitude' => 'nullable|numeric',
                'pickup_address'     => 'required|string|max:255',
            ]);

            $data = $request->only(['title', 'description', 'price', 'category', 'location_latitude', 'location_longitude', 'pickup_address']);
            
            $cleanString = function($str) {
                if (!$str) return $str;
                return mb_convert_encoding($str, 'UTF-8', mb_detect_encoding($str, 'UTF-8, ISO-8859-1', true) ?: 'UTF-8');
            };

            if (isset($data['title'])) $data['title'] = $cleanString($data['title']);
            if (isset($data['description'])) $data['description'] = $cleanString($data['description']);
            if (isset($data['category'])) $data['category'] = $cleanString($data['category']);

            if ($request->has('metadata')) {
                $meta = $request->input('metadata');
                if (is_string($meta)) {
                    $meta = mb_convert_encoding($meta, 'UTF-8', mb_detect_encoding($meta, 'UTF-8, ISO-8859-1', true) ?: 'UTF-8');
                    $data['metadata'] = json_decode($meta, true) ?: [];
                } elseif (is_array($meta)) {
                    $data['metadata'] = $meta;
                }
            }

            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $request->file('cover_image')->store('marketplace', 's3');
            }

            $listing->update($data);

            // --- ASSIGNATION DES AGENTS ---
            if ($request->has('assigned_agents_ids')) {
                $listing->agents()->delete(); // Clear existing
                
                $agentIds = $request->input('assigned_agents_ids');
                if (is_string($agentIds)) {
                    $agentIds = json_decode($agentIds, true);
                }
                if (is_array($agentIds)) {
                    foreach ($agentIds as $agentId) {
                        \App\Models\MarketplaceAgent::create([
                            'listing_id' => $listing->id,
                            'user_id' => $agentId
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Annonce mise à jour avec succès.',
                'data'    => $listing
            ]);

        } catch (\Exception $e) {
            \Log::error("Erreur de mise à jour marketplace : " . $e->getMessage());
            return response()->json(['error' => 'Erreur technique lors de la mise à jour.'], 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        $listing = MarketplaceListing::with(['user:id,first_name,last_name,display_name,picture', 'corridor', 'passes'])->findOrFail($id);
        
        $is_favorite = false;
        // if (Auth::guard('api')->check()) {
        //     $is_favorite = \App\Models\FavoriteAnnouncement::where('user_id', Auth::guard('api')->id())
        //                        ->where('listing_id', $listing->id)
        //                        ->exists();
        // }

        return response()->json([
            'success'     => true,
            'data'        => $listing,
            'is_favorite' => $is_favorite
        ]);
    }

    public function destroy($id): JsonResponse
    {
        try {
            $listing = MarketplaceListing::findOrFail($id);

            if ($listing->user_id !== Auth::id()) {
                return response()->json(['error' => 'Action non autorisée. Vous n\'êtes pas le propriétaire.'], 403);
            }

            $listing->delete();

            return response()->json([
                'success' => true,
                'message' => 'Annonce supprimée avec succès.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur technique lors de la suppression.'], 500);
        }
    }

    public function categories(): JsonResponse
    {
        try {
            $categories = \App\Models\MarketplaceCategory::whereNull('parent_id')
                ->with('children')
                ->orderBy('order_index')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $categories
            ]);
        } catch (\Exception $e) {
            \Log::error("Erreur de récupération des catégories marketplace : " . $e->getMessage());
            return response()->json(['error' => 'Erreur technique lors du chargement des catégories.'], 500);
        }
    }
}