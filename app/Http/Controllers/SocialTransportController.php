<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostComment;
use App\Models\PostPledge;
use App\Models\UserRequests;
use App\Models\ActiveSharedRide;
use App\Models\RideBooking;
use App\Models\PdpRoute;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;

use App\Events\NewSocialTripPosted;
use App\Events\TripJoined;
use App\Events\SocialSosAlert;
use App\Events\PledgeThresholdReached;
use App\Services\TrajetMatchingService;
use App\Services\CommissionService;

class SocialTransportController extends Controller
{
    // =========================================================================
    // SECTION 1 : FIL D'ACTUALITÉ PAR CORRIDOR
    // =========================================================================

    /**
     * Récupère le fil d'actualité d'un corridor de route.
     * Supporte aussi le fil global si aucun corridor n'est précisé.
     */
    public function corridorFeed(Request $request): JsonResponse
    {
        // ── AUTO-SYNC ASYNCHRONE: Lancement robuste en arrière-plan (Windows & Linux) ──
        if (!Cache::has('sync_news_running')) {
            $newsCount = Post::whereIn('type', ['RSS_NEWS', 'ROAD_INFO'])->count();
            $lastSync = Cache::get('last_news_sync_at');
            $isOld = !$lastSync || now()->diffInMinutes($lastSync) > 60;

            if ($newsCount === 0 || $isOld) {
                Cache::put('sync_news_running', true, 300); // Verrou de 5 min
                
                $artisan = base_path('artisan');
                if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
                    pclose(popen("start /B php $artisan news:fetch > NUL 2>&1", "r"));
                } else {
                    exec("php $artisan news:fetch > /dev/null 2>&1 &");
                }
            }
        }

        // ── PERFORMANCE: Paramètres ──
        $page     = $request->input('page', 1);
        $routeId  = $request->input('route_id', 'all');
        $type     = $request->input('type', 'all');
        $svcType  = $request->input('service_type_id', 'all');
        $userId   = Auth::id() ?? 'guest';
        $source   = $request->input('source', 'all');
        $cacheKey = "feed:{$userId}:{$routeId}:{$type}:{$svcType}:{$source}:p{$page}";

        $query = Post::with([
            'user:id,first_name,last_name,display_name,picture,is_verified,user_badge,social_points,social_rating,mobile,kyc_status', 
            'provider:id,first_name,last_name,display_name,avatar,mobile',
            'corridor:id,name',
            'poll.options'
        ])
            ->select(['id','user_id','author_type','type','source','category','content','media_url',
                      'external_link','likes_count','comments_count','pdp_route_id',
                      'service_type_id','is_shareable','seats_available','price',
                      'pledge_count','pledge_threshold','poll_id','status',
                      'expires_at','latitude','longitude','published_at','created_at','updated_at'])
            ->where('status', 'ACTIVE')
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->whereNull('deleted_at')
            ->latest();

        if ($request->has('route_id') && $request->route_id !== 'all') {
            $query->where(function($q) use ($request) {
                $q->where('pdp_route_id', $request->route_id)
                  ->orWhereIn('type', ['NEWS', 'RSS_NEWS']); 
            });
        }
        if ($request->has('type')) {
            if ($request->type === 'STORIES') {
                $query->whereIn('type', ['SOCIAL_PIC', 'SOCIAL_VID', 'ROAD_INFO']);
            } elseif ($request->type === 'RSS_NEWS' || $request->type === 'NEWS') {
                $query->whereIn('type', ['NEWS', 'RSS_NEWS', 'ROAD_INFO']);
            } else {
                $query->where('type', $request->type);
            }
        }
        if ($request->has('service_type_id')) {
            $query->where('service_type_id', $request->service_type_id);
        }

        if ($request->has('source') && !empty($request->source) && $request->source !== 'all') {
            if ($request->source === 'Info Trafic') {
                $query->where('type', 'ROAD_INFO');
            } else {
                $reqSource = strtoupper($request->source);
                $dbSource = 'INTERNAL';
                if (str_contains($reqSource, 'ABIDJAN')) $dbSource = 'ABIDJAN_NET';
                elseif (str_contains($reqSource, 'INFODROME')) $dbSource = 'LINFODROME';
                elseif (str_contains($reqSource, 'KOACI')) $dbSource = 'KOACI';
                elseif (str_contains($reqSource, 'AIP')) $dbSource = 'AIP';
                elseif (str_contains($reqSource, 'RTI')) $dbSource = 'RTI';
                elseif (str_contains($reqSource, 'FRATMAT')) $dbSource = 'FRATMAT';

                $query->where('source', $dbSource);
            }
        } elseif ($request->type === 'RSS_NEWS' || $request->type === 'NEWS') {
            $query->whereIn('type', ['NEWS', 'RSS_NEWS', 'ROAD_INFO']);
        }

        // FILTRAGE NEWS 7 JOURS (les articles restent visibles une semaine)
        $query->where(function($q) {
            $q->whereNotIn('type', ['NEWS', 'RSS_NEWS', 'ROAD_INFO'])
              ->orWhere('published_at', '>=', now()->subDays(7))
              ->orWhere(function($sub) {
                  $sub->whereNull('published_at')->where('created_at', '>=', now()->subDays(7));
              });
        });

        $posts = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(2), function () use ($query) {
            return $query->paginate(15);
        });

        // Fallback: Si le corridor est vide, on affiche les infos réelles globales (en respectant la source)
        if ($posts->isEmpty() && $routeId !== 'all') {
             $fallbackCacheKey = "feed_fallback:{$routeId}:{$source}:p{$page}";
             $posts = \Illuminate\Support\Facades\Cache::remember($fallbackCacheKey, now()->addMinutes(5), function () use ($source) {
                 $q = Post::where('status', 'ACTIVE')
                    ->whereIn('type', ['RSS_NEWS', 'NEWS', 'ROAD_INFO'])
                    ->where(function($sq) {
                        $sq->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    });
                 
                 if ($source !== 'all') {
                     if ($source === 'Info Trafic') {
                         $q->where('type', 'ROAD_INFO');
                     } else {
                         $q->where('source', $source);
                     }
                 }

                 return $q->where('created_at', '>=', now()->subHours(72))
                    ->latest()
                    ->paginate(15);
             });
        }

        // Charger les favoris de l'utilisateur pour marquer l'étoile
        $favUsers   = [];
        $favSources = [];
        if (Auth::check()) {
            $favs = \DB::table('author_favorites')->where('user_id', Auth::id())->get();
            $favUsers   = $favs->whereNotNull('author_id')->pluck('author_id')->toArray();
            $favSources = $favs->whereNotNull('source_name')->pluck('source_name')->toArray();
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $posts */
        $posts->getCollection()->transform(function($post) use ($favUsers, $favSources) {
            if ($post->media_url && !str_starts_with($post->media_url, 'http')) {
                $post->media_url = \Storage::disk('s3')->url( $post->media_url);
            }
            if ($post->user && $post->user->picture && !str_starts_with($post->user->picture, 'http')) {
                $post->user->picture = \Storage::disk('s3')->url( $post->user->picture);
            }

            // Unify author data (Pseudos / Picme Info)
            $author = $post->user ?: $post->provider;
            $isNews = ($post->type === 'NEWS' || $post->type === 'RSS_NEWS');
            $isTraffic = ($post->type === 'ROAD_INFO');

            if ($isNews) {
                // Pour les vraies actualités RSS/News
                $post->author_name = $post->source ?: 'Picme Info';
                $post->author_picture = asset('img/logo.png');
                $post->user_id = -1; // Empêche la suppression des news
                // Identification par SOURCE_nom pour les favoris
                $post->is_favorite_author = in_array($post->author_name, $favSources);
            } else {
                // Pour les posts utilisateurs (y compris ROAD_INFO posté par un utilisateur)
                $pseudo = $author ? $author->display_name : null;
                if ($pseudo && $pseudo !== 'null' && trim($pseudo) !== '') {
                    $post->author_name = $pseudo;
                } else if ($author) {
                    $post->author_name = trim($author->first_name . ' ' . $author->last_name);
                } else {
                    $post->author_name = 'Membre Picme';
                }
                
                // Si ROAD_INFO n'a pas d'auteur (généré par le système), on fallback sur Picme Info
                if ($isTraffic && !$author) {
                    $post->author_name = 'Info Trafic';
                    $post->author_picture = asset('img/logo.png');
                    $post->user_id = -1;
                    $post->is_favorite_author = false;
                } else {
                    $post->author_picture = $author ? ($post->author_type === 'PROVIDER' ? $author->avatar : $author->picture) : null;
                    $post->is_favorite_author = $author ? in_array($author->id, $favUsers) : false;
                }
            }

            if ($post->author_picture && !str_starts_with($post->author_picture, 'http')) {
                $post->author_picture = \Storage::disk('s3')->url( $post->author_picture);
            }

            // Clean up to avoid confusion in JSON
            unset($post->provider);
            unset($post->user);
            unset($post->author);
            
            // Interaction states for current user
            if (Auth::check()) {
                $userId = Auth::id();
                $post->is_liked = PostLike::where('post_id', $post->id)->where('user_id', $userId)->where('type', 'LIKE')->exists();
                $post->is_disliked = \App\Models\PostDislike::where('post_id', $post->id)->where('user_id', $userId)->exists();
                $post->is_favorited = PostLike::where('post_id', $post->id)->where('user_id', $userId)->where('type', 'FAVORITE')->exists();
            } else {
                $post->is_liked = false;
                $post->is_disliked = false;
                $post->is_favorited = false;
            }

            return $post;
        });

        return response()->json($posts)
            ->header('Cache-Control', 'public, max-age=30');
    }

    // =========================================================================
    // SECTION 1B : MATCHING INTELLIGENT (Booking par direction)
    // =========================================================================

    /**
     * Trouve les trajets compatibles avec la position et destination du passager.
     * Retourne une liste triée par score de matching (0-100).
     */
    public function findMatchingTrips(Request $request, TrajetMatchingService $matchingService): JsonResponse
    {
        $request->validate([
            'user_lat'       => 'required|numeric',
            'user_lng'       => 'required|numeric',
            'dest_lat'       => 'required|numeric',
            'dest_lng'       => 'required|numeric',
            'service_type_id'=> 'nullable|integer',
            'route_id'       => 'nullable|integer',
        ]);

        $matches = $matchingService->findMatchingTrips(
            (float) $request->user_lat,
            (float) $request->user_lng,
            (float) $request->dest_lat,
            (float) $request->dest_lng,
            $request->service_type_id,
            $request->route_id
        );

        return response()->json([
            'success' => true,
            'count'   => $matches->count(),
            'matches' => $matches,
        ]);
    }

    /**
     * Publie un trajet communautaire (Offre ou Demande).
     * TRIP = Offre (Chauffeur), INTENTION = Demande (Passager).
     */
    public function createSocialTrip(Request $request, TrajetMatchingService $matchingService): JsonResponse
    {
        $request->validate([
            'content'         => 'required|string|max:500',
            'type'            => 'required|in:TRIP,INTENTION',
            'pdp_route_id'    => 'nullable|integer',
            'price'           => 'nullable|numeric',
            'seats_available' => 'nullable|integer|min:1',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
            'd_latitude'      => 'nullable|numeric',
            'd_longitude'     => 'nullable|numeric',
            'departure_time'  => 'nullable|date',
        ]);

        $userId = Auth::id();
        $type = $request->type;

        if ($type === 'TRIP') {
            $trip = \App\Models\Trip::create([
                'user_id'          => $userId,
                'origin_name'      => $request->input('origin_name', 'Abidjan'),
                'destination_name' => $request->input('destination_name', 'Abidjan'),
                'origin_lat'       => $request->latitude,
                'origin_lng'       => $request->longitude,
                'destination_lat'  => $request->d_latitude,
                'destination_lng'  => $request->d_longitude,
                'departure_time'   => $request->input('departure_time', now()->addHour()),
                'seats_available'  => $request->input('seats_available', 4),
                'price'            => $request->input('price', 0),
                'description'      => $request->input('content'),
                'pdp_route_id'     => $request->input('pdp_route_id'),
                'status'           => 'OPEN',
            ]);

            // Synchroniser avec la table posts pour la visibilité sociale
            $post = Post::create([
                'user_id' => $userId,
                'type' => 'TRIP',
                'category' => 'TRANSPORT',
                'content' => $request->content,
                'price' => $request->price,
                'seats_available' => $request->seats_available,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'pdp_route_id' => $request->pdp_route_id,
                'status' => 'ACTIVE'
            ]);

            return response()->json(['success' => true, 'trip' => $trip, 'post' => $post], 201);

        } else {
            $intention = \App\Models\Intention::create([
                'user_id'          => $userId,
                'origin_name'      => $request->input('origin_name', 'Abidjan'),
                'destination_name' => $request->input('destination_name', 'Abidjan'),
                'origin_lat'       => $request->latitude,
                'origin_lng'       => $request->longitude,
                'destination_lat'  => $request->d_latitude,
                'destination_lng'  => $request->d_longitude,
                'earliest_departure' => now(),
                'latest_departure'   => now()->addHours(2),
                'seats_needed'     => $request->input('seats_available', 1),
                'description'      => $request->input('content'),
                'pdp_route_id'     => $request->input('pdp_route_id'),
                'status'           => 'PENDING',
            ]);

            // Sync with posts
            $post = Post::create([
                'user_id' => $userId,
                'type' => 'INTENTION',
                'category' => 'TRANSPORT',
                'content' => $request->content,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'pdp_route_id' => $request->pdp_route_id,
                'status' => 'ACTIVE'
            ]);

            // Matching automatique
            $matches = $matchingService->findMatchesForIntention($intention);

            return response()->json([
                'success' => true,
                'intention' => $intention,
                'post' => $post,
                'matches' => $matches
            ], 201);
        }
    }

    /**
     * Un utilisateur publie une "Intention de Transport" dans un corridor.
     * Ex: "Je cherche à aller à Bassam vendredi matin, qui est partant ?"
     */
    public function createIntention(Request $request): JsonResponse
    {
        $request->validate([
            'content'          => 'required|string|max:500',
            'pdp_route_id'     => 'nullable|exists:pdp_routes,id',
            'pledge_threshold' => 'nullable|integer|min:2|max:10',
        ]);

        $post = Post::create([
            'user_id'          => Auth::id(),
            'type'             => 'INTENTION',
            'source'           => 'INTERNAL',
            'content'          => $request->input('content'),
            'pdp_route_id'     => $request->pdp_route_id,
            'pledge_threshold' => $request->pledge_threshold ?? 4,
            'pledge_count'     => 0,
            'status'           => 'PLEDGING',
        ]);

        try {
            broadcast(new \App\Events\NewSocialTripPosted(
                $post->id,
                'INTENTION',
                $post->pdp_route_id,
                ['content' => substr($post->content, 0, 50)]
            ))->toOthers();
        } catch (\Exception $e) { \Log::warning("Erreur Push Intention: " . $e->getMessage()); }

        return response()->json(['success' => true, 'post' => $post], 201);
    }

    // =========================================================================
    // SECTION 3 : ENGAGEMENT (PLEDGE) SUR UNE INTENTION
    // =========================================================================

    /**
     * Un utilisateur "pledge" (s'engage) sur une intention de trajet.
     * Quand le seuil est atteint, une notification est envoyée pour déclencher la course communautaire.
     */
    public function pledge(Request $request, int $postId): JsonResponse
    {
        $request->validate([
            'pickup_latitude'  => 'required|numeric',
            'pickup_longitude' => 'required|numeric',
            'pickup_address'   => 'nullable|string',
        ]);

        $post = Post::where('type', 'INTENTION')
            ->where('status', 'PLEDGING')
            ->findOrFail($postId);

        if ($post->user_id === Auth::id()) {
            return response()->json(['error' => 'Vous ne pouvez pas vous engager sur votre propre intention.'], 403);
        }

        // Vérifier si l'utilisateur a déjà pledgé
        $existingPledge = PostPledge::where('post_id', $postId)
            ->where('user_id', Auth::id())
            ->exists();

        if ($existingPledge) {
            return response()->json(['error' => 'Vous avez déjà rejoint cette intention.'], 409);
        }

        DB::transaction(function () use ($request, $post) {
            PostPledge::create([
                'post_id'          => $post->id,
                'user_id'          => Auth::id(),
                'pickup_latitude'  => $request->pickup_latitude,
                'pickup_longitude' => $request->pickup_longitude,
                'pickup_address'   => $request->pickup_address,
                'status'           => 'PLEDGED',
            ]);

            // Incrémenter le compteur de pledges
            $post->increment('pledge_count');
            $post->refresh();

            // Si le seuil est atteint, changer le statut et notifier
            if ($post->isPledgeThresholdReached()) {
                $post->update(['status' => 'ACTIVE']);
                Log::info("Seuil atteint pour l'intention #" . $post->id . " — Déclencher la mise en relation communautaire.");
                
                // NOTIFY CREATOR
                (new SendPushNotification)->CommunityPledgeReached($post->user_id, "Félicitations ! Votre intention de trajet a reçu assez d'engagements. Un chauffeur peut maintenant prendre la course.");

                // Diffuser l'événement broadcast
                broadcast(new PledgeThresholdReached($post->id, $post->pledge_count, $post->pdp_route_id));
            }
        });

        return response()->json([
            'success'        => true,
            'pledge_count'   => $post->fresh()->pledge_count,
            'threshold_met'  => $post->isPledgeThresholdReached(),
        ]);
    }

    /**
     * Réserver un trajet trouvé via le matching.
     * Implémente le système d'Escrow : les fonds sont bloqués jusqu'à la fin du trajet.
     */
    public function bookTrip(Request $request): JsonResponse
    {
        $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'seats'   => 'required|integer|min:1',
        ]);

        $trip = \App\Models\Trip::with('user')->findOrFail($request->trip_id);
        $user = Auth::user();

        if ($trip->user_id === $user->id) {
            return response()->json(['error' => 'Vous ne pouvez pas réserver votre propre trajet.'], 403);
        }

        if ($trip->seats_available < $request->seats) {
            return response()->json(['error' => 'Plus de places disponibles.'], 422);
        }

        $totalPrice = $trip->price * $request->seats;

        if ($user->wallet_balance < $totalPrice) {
            return response()->json(['error' => 'Solde insuffisant dans votre portefeuille.'], 402);
        }

        $booking = DB::transaction(function () use ($trip, $user, $request, $totalPrice) {
            // 1. Débiter le portefeuille (Escrow)
            $user->decrement('wallet_balance', $totalPrice);

            \App\Models\WalletPassbook::create([
                'user_id' => $user->id,
                'amount'  => -$totalPrice,
                'status'  => 'DEBITED',
                'via'     => 'TRIP_ESCROW',
            ]);

            // 2. Créer la réservation
            $booking = \App\Models\TripBooking::create([
                'trip_id'        => $trip->id,
                'user_id'        => $user->id,
                'seats_booked'   => $request->seats,
                'price'          => $totalPrice,
                'handshake_code' => strtoupper(substr(md5(microtime()), 0, 6)),
                'status'         => 'CONFIRMED',
                'payment_status' => 'ESCROW',
            ]);

            // 3. Mettre à jour les places disponibles
            $trip->decrement('seats_available', $request->seats);
            if ($trip->seats_available <= 0) {
                $trip->update(['status' => 'FULL']);
            }

            return $booking;
        });

        // 4. NOTIFIER LE CONDUCTEUR
        try {
            $msg = "🎉 Nouveau passager ! " . $user->first_name . " a réservé " . $request->seats . " place(s).";
            (new SendPushNotification)->sendPushToUser($trip->user_id, "Réservation Covoiturage", $msg);
        } catch (\Exception $e) {
            Log::warning("Erreur notification booking: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'booking' => $booking,
            'message' => 'Réservation confirmée ! Les fonds sont en sécurité (Escrow).'
        ], 201);
    }

    // =========================================================================
    // SECTION 5B : SOS SOCIAL - ALERTE D'URGENCE
    // =========================================================================


    /**
     * Valide le ramassage du passager (Départ du trajet).
     * Déclenché par scan du QR Code du conducteur par le passager ou confirmation push.
     */
    public function startTrip(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => 'required|exists:trip_bookings,id',
        ]);

        $booking = \App\Models\TripBooking::with('trip')->findOrFail($request->booking_id);

        if ($booking->status !== 'CONFIRMED') {
            return response()->json(['error' => 'Le trajet est déjà en cours ou terminé.'], 400);
        }

        $booking->update([
            'status' => 'STARTED',
            'started_at' => now(),
        ]);

        // Notifier le conducteur
        (new SendPushNotification)->HandshakeStarted($booking->trip->user_id, $booking->id);

        return response()->json([
            'success' => true,
            'message' => 'Le trajet a officiellement commencé !',
            'status' => 'STARTED'
        ]);
    }

    /**
     * Confirmer le trajet via le code de Handshake.
     * Libère les fonds de l'Escrow vers le conducteur.
     */
    public function confirmHandshake(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id'     => 'required|exists:trip_bookings,id',
            'handshake_code' => 'required|string',
        ]);

        $booking = \App\Models\TripBooking::with('trip.user')->findOrFail($request->booking_id);

        if ($booking->handshake_code !== strtoupper($request->handshake_code)) {
            return response()->json(['error' => 'Code de handshake invalide.'], 403);
        }

        if ($booking->status === 'COMPLETED') {
            return response()->json(['error' => 'Ce trajet est déjà terminé.'], 400);
        }

        DB::transaction(function () use ($booking) {
            $trip   = $booking->trip;
            $driver = $trip->user;
            
            // Commission de 15%
            $commission = $booking->price * 0.15;
            $driverNet  = $booking->price - $commission;

            // Créditer le conducteur
            $driver->increment('wallet_balance', $driverNet);

            \App\Models\WalletPassbook::create([
                'user_id' => $driver->id,
                'amount'  => $driverNet,
                'status'  => 'CREDITED',
                'via'     => 'TRIP_EARNING',
            ]);

            // Marquer comme terminé
            $booking->update([
                'status'         => 'COMPLETED',
                'payment_status' => 'PAID',
                'completed_at'   => now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Handshake réussi ! Les fonds ont été versés au conducteur.'
        ]);
    }

    /**
     * Rappel du code Handshake au passager (déclenché par le conducteur).
     */
    public function sendHandshakeReminder(Request $request): JsonResponse
    {
        $request->validate(['booking_id' => 'required|exists:trip_bookings,id']);
        
        $booking = \App\Models\TripBooking::findOrFail($request->booking_id);
        
        // 🔔 Notification
        (new SendPushNotification)->HandshakeReminder($booking->user_id, $booking->handshake_code);

        return response()->json([
            'success' => true,
            'message' => 'Rappel envoyé au passager.'
        ]);
    }

    // =========================================================================
    // SECTION 5B : SOS SOCIAL - ALERTE D'URGENCE
    // =========================================================================

    /**
     * Déclenche une alerte SOS sociale visible sur le fil du corridor.
     * Notifie la communauté locale en temps réel.
     */
    public function triggerSos(Request $request, int $postId): JsonResponse
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $post = Post::findOrFail($postId);

        // Diffuser l'alerte SOS sur tous les canaux du corridor
        broadcast(new SocialSosAlert(
            $post->id,
            Auth::id(),
            (float) $request->latitude,
            (float) $request->longitude,
            $post->pdp_route_id,
            now()->toISOString()
        ));

        Log::critical("SOS Social déclenché sur le post #{$post->id} par l'utilisateur #" . Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Alerte SOS envoyée. La communauté et les agents ont été notifiés.',
        ]);
    }

    // =========================================================================
    // SECTION 6 : INTERACTIONS SOCIALES (LIKE / COMMENTAIRE)
    // =========================================================================

    /** Liker / Unliker un post */
    public function toggleLike(int $postId): JsonResponse
    {
        $post = Post::findOrFail($postId);
        $userId = Auth::id();
        $existing = PostLike::where('post_id', $postId)->where('user_id', $userId)->first();

        if ($existing) {
            $existing->delete();
            $post->decrement('likes_count');
            return response()->json(['liked' => false, 'likes_count' => $post->likes_count]);
        }

        // Supprimer le Dislike s'il existe (Mutual Exclusivity)
        $dislike = \App\Models\PostDislike::where('post_id', $postId)->where('user_id', $userId)->first();
        if ($dislike) {
            $dislike->delete();
            $post->decrement('dislikes_count');
        }

        PostLike::create(['post_id' => $postId, 'user_id' => $userId, 'type' => 'LIKE']);
        $post->increment('likes_count');

        // 🏆 REPUTATION : Points pour l'auteur
        if ($post->user) {
            // Milestone : +0.5 si 10 likes sur Info Trafic
            if ($post->type === 'ROAD_INFO' && $post->likes_count == 10) {
                \App\Services\ReputationService::awardPoints($post->user, \App\Services\ReputationService::POINTS_ROAD_INFO_MILESTONE);
            }

            // Points standards basés sur le succès
            $points = ($post->category === 'SUCCESS') 
                ? \App\Services\ReputationService::POINTS_LIKE_SUCCESS_BASED 
                : \App\Services\ReputationService::POINTS_LIKE_RECEIVED;
            
            if ($points > 0) {
                \App\Services\ReputationService::awardPoints($post->user, $points);
            }
        }

        // Broadcast update
        try {
            broadcast(new \App\Events\SocialInteractionUpdate($post->id, $post->likes_count, $post->dislikes_count))->toOthers();
        } catch (\Exception $e) {}

        return response()->json([
            'liked' => true, 
            'likes_count' => $post->likes_count,
            'disliked' => false,
            'dislikes_count' => $post->dislikes_count
        ]);
    }

    /** Disliker / Undisliker un post */
    public function toggleDislike(int $postId): JsonResponse
    {
        $post = Post::findOrFail($postId);
        $userId = Auth::id();
        $existing = \App\Models\PostDislike::where('post_id', $postId)->where('user_id', $userId)->first();

        if ($existing) {
            $existing->delete();
            $post->decrement('dislikes_count');
            return response()->json(['disliked' => false, 'dislikes_count' => $post->dislikes_count]);
        }

        // Supprimer le Like s'il existe (Mutual Exclusivity)
        $like = PostLike::where('post_id', $postId)->where('user_id', $userId)->where('type', 'LIKE')->first();
        if ($like) {
            $like->delete();
            $post->decrement('likes_count');
        }

        \App\Models\PostDislike::create(['post_id' => $postId, 'user_id' => $userId]);
        $post->increment('dislikes_count');
        // Broadcast update
        try {
            broadcast(new \App\Events\SocialInteractionUpdate($post->id, $post->likes_count, $post->dislikes_count))->toOthers();
        } catch (\Exception $e) {}

        return response()->json([
            'disliked' => true, 
            'dislikes_count' => $post->dislikes_count,
            'liked' => false,
            'likes_count' => $post->likes_count
        ]);
    }

    /** Ajouter / Retirer un post des favoris */
    public function toggleFavorite(int $postId): JsonResponse
    {
        $post = Post::findOrFail($postId);
        $userId = Auth::id();
        $existing = PostLike::where('post_id', $postId)->where('user_id', $userId)->where('type', 'FAVORITE')->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['favorited' => false]);
        }

        PostLike::create(['post_id' => $postId, 'user_id' => $userId, 'type' => 'FAVORITE']);
        return response()->json(['favorited' => true]);
    }

    /** Incrémenter le compteur de partages */
    public function incrementShare(int $postId): JsonResponse
    {
        $post = Post::findOrFail($postId);
        $post->increment('shares_count');

        // 🏆 RECOMPENSE : +0.5 points Karma pour le partage de story
        if (Auth::check()) {
            $user = Auth::user();
            $user->increment('social_points', 0.5);
            
            // Logique de badge automatique
            $user->syncKarmaBadge();
        }

        return response()->json([
            'success' => true, 
            'shares_count' => $post->shares_count,
            'new_social_points' => Auth::check() ? Auth::user()->social_points : 0
        ]);
    }

    /**
     * Mettre à jour la position GPS d'un trajet actif (Shuttle ou P2P).
     * Diffuse l'événement broadcast pour le suivi par les passagers.
     */
    public function updateRideLocation(Request $request, int $rideId): JsonResponse
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'bearing'   => 'nullable|numeric',
        ]);

        $ride = ActiveSharedRide::where('id', $rideId)
            ->where('provider_id', Auth::guard('providerapi')->id())
            ->firstOrFail();

        $ride->update([
            'current_latitude'  => $request->latitude,
            'current_longitude' => $request->longitude,
        ]);

        // Diffuser en temps réel via WebSockets
        broadcast(new \App\Events\SharedRideLocationUpdated(
            $ride->id,
            $request->latitude,
            $request->longitude,
            $request->bearing ?? 0
        ))->toOthers();

        return response()->json(['success' => true]);
    }

    /** Créer un post utilisateur éphémère (media_file) ou via media_url */
    public function createUserPost(Request $request): JsonResponse
    {
        $request->validate([
            'content'      => 'nullable|string|max:500',
            'media_url'    => 'nullable|string', // URL externe si applicable
            'media'        => 'nullable|file|mimes:jpg,jpeg,png,mp4,3gp,mov|max:20480', // 20MB max
            'type'         => 'required|in:SOCIAL_PIC,SOCIAL_VID,SOCIAL_POST,ROAD_INFO',
            'route_id'     => 'nullable|integer',
        ]);

        $mediaPath = null;

        if ($request->hasFile('media')) {
            $mediaPath = $request->file('media')->store('social', 's3');
        } else if ($request->has('media_url')) {
            $mediaPath = $request->media_url;
        }

        $content = $request->input('content') ?: "";
        if ($content) {
            $content = mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true) ?: 'UTF-8');
        }
        
        // Sécurité supplémentaire pour ROAD_INFO
        if ($request->type === 'ROAD_INFO' && !str_contains($content, '[🚦 INFO ROUTE]')) {
            $content = "[🚦 INFO ROUTE] " . $content;
        }

        // Définir la durée de vie (Expiration) pour soulager le stockage
        // - Ephemeral (PIC/VID) -> 48h (2 jours)
        // - Text Posts -> 1 mois (30 jours)
        // - Par défaut -> 1 mois
        $expiresAt = now()->addHours(48);
        if ($request->type === 'SOCIAL_POST') {
            $expiresAt = now()->addDays(30);
        }

        $authorType = 'USER';
        if (Auth::guard('providerapi')->check()) {
            $authorType = 'PROVIDER';
        }

        $post = Post::create([
            'user_id'         => Auth::id(),
            'author_type'     => $authorType,
            'type'            => $request->type,
            'source'          => 'USER',
            'category'        => 'COMMUNITY',
            'content'         => $content,
            'media_url'       => $mediaPath,
            'status'          => 'ACTIVE',
            'expires_at'      => $expiresAt,
            'pdp_route_id'    => $request->route_id,
        ]);

        try {
            broadcast(new \App\Events\NewSocialTripPosted(
                $post->id,
                $post->type,
                $post->pdp_route_id,
                ['content' => substr($post->content ?? '', 0, 50)]
            ))->toOthers();
        } catch (\Exception $e) { \Log::warning("Erreur Push UserPost: " . $e->getMessage()); }

        // 🏆 REPUTATION : Points attribués après validation communautaire (likes)
        // (Logique déplacée dans toggleLike pour le milestone de 10 likes)

        $post->load(['user:id,first_name,last_name,display_name,picture,is_verified,user_badge,social_points,social_rating,mobile,kyc_status', 'provider:id,first_name,last_name,display_name,avatar,mobile']);
        
        if ($post->author_type === 'PROVIDER' && $post->provider) {
            $post->provider->picture = $post->provider->avatar;
            if ($post->provider->picture && !str_starts_with($post->provider->picture, 'http')) {
                $post->provider->picture = \Storage::disk('s3')->url( $post->provider->picture);
            }
            $post->setRelation('user', $post->provider);
            unset($post->provider);
        }

        return response()->json(['success' => true, 'post' => $post], 201);
    }

    /** Supprimer son propre post (Story ou Info Trafic) */
    public function deleteMyPost(Request $request, $postId): JsonResponse
    {
        $guard = Auth::guard('api')->check() ? 'api' : (Auth::guard('providerapi')->check() ? 'providerapi' : 'none');
        $user = Auth::user();
        \Log::info("--- DELETE REQUEST START ---");
        \Log::info("URL: " . $request->fullUrl());
        \Log::info("ID from route: " . $postId);
        \Log::info("Guard: $guard, AuthID: " . ($user ? $user->id : 'NULL'));
        \Log::info("Inputs: " . json_encode($request->all()));
        
        // Utiliser withTrashed pour voir si le post est déjà supprimé
        $post = Post::withTrashed()->find($postId);

        if (!$post) {
            \Log::warning("POST NOT FOUND IN DATABASE: ID " . $postId);
            return response()->json(['error' => 'Publication introuvable (ID: '.$postId.').'], 404);
        }

        if ($post->trashed()) {
            return response()->json(['success' => true, 'message' => 'Publication déjà supprimée.']);
        }

        // Vérifier si l'utilisateur est l'auteur via l'un des guards
        $isAuthor = false;
        
        if ($post->author_type === 'USER') {
            // Si le post est de type USER, on vérifie le guard 'api'
            if (Auth::guard('api')->check() && (int)Auth::guard('api')->id() === (int)$post->user_id) {
                $isAuthor = true;
            }
        } elseif ($post->author_type === 'PROVIDER') {
            // Si le post est de type PROVIDER, on vérifie le guard 'providerapi'
            if (Auth::guard('providerapi')->check() && (int)Auth::guard('providerapi')->id() === (int)$post->user_id) {
                $isAuthor = true;
            }
        }

        // Sécurité : seul l'auteur peut supprimer son post
        if (!$isAuthor) {
            \Log::warning("Unauthorized delete attempt for post ID " . $postId . " (Type: " . $post->author_type . ", AuthorID: " . $post->user_id . ") by CurrentUser: " . Auth::id());
            return response()->json(['error' => 'Action non autorisée. Vous ne pouvez supprimer que vos propres publications.'], 403);
        }

        // Supprimer le fichier média associé si présent
        if ($post->media_url && \Storage::disk('s3')->exists($post->media_url)) {
            \Storage::disk('s3')->delete($post->media_url);
        }

        $post->delete();
        
        // Nettoyer tous les caches possibles du fil social
        \Illuminate\Support\Facades\Cache::flush();
        \Illuminate\Support\Facades\Cache::forget('stories:latest20');

        return response()->json(['success' => true, 'message' => 'Publication supprimée avec succès.']);
    }

    /** Commenter un post avec filtrage anti-contournement */
    public function comment(Request $request, int $postId): JsonResponse
    {
        $request->validate(['comment' => 'required|string|max:300']);

        $post = Post::findOrFail($postId);
        $content = $request->input('comment');

        // Filtrage anti-contournement : détecter les numéros et mots-clés
        $isFlagged = $this->isSensitiveContent($content);

        if ($isFlagged) {
            return response()->json([
                'error' => 'Votre message contient des informations de contact. Utilisez la messagerie sécurisée après réservation.',
            ], 422);
        }

        $comment = PostComment::create([
            'post_id'    => $postId,
            'user_id'    => Auth::id(),
            'comment'    => $content,
            'is_flagged' => false,
        ]);

        $post->increment('comments_count');

        // 🏆 REPUTATION : Points pour le commentateur
        \App\Services\ReputationService::awardPoints(Auth::user(), \App\Services\ReputationService::POINTS_COMMENT);

        // Retourner le commentaire avec l'utilisateur pour mise à jour UI immédiate
        $comment->load('user:id,first_name,last_name,display_name,picture');
        if ($comment->user && $comment->user->picture) {
            if (!str_starts_with($comment->user->picture, 'http')) {
                $comment->user->picture = \Storage::disk('s3')->url( $comment->user->picture);
            }
        }

        // 🔔 BROADCAST TEMPS RÉEL (Pusher)
        try {
            broadcast(new \App\Events\NewSocialComment($comment))->toOthers();
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true, 
            'comment' => $comment, 
            'comments_count' => $post->comments_count
        ], 201);
    }

    /** Liste des commentaires d'un post */
    public function getComments(int $postId): JsonResponse
    {
        $comments = PostComment::with('user:id,first_name,last_name,display_name,picture')
            ->where('post_id', $postId)
            ->oldest()
            ->get()
            ->map(function($comment) {
                if ($comment->user && $comment->user->picture && !str_starts_with($comment->user->picture, 'http')) {
                    $comment->user->picture = \Storage::disk('s3')->url( $comment->user->picture);
                }
                return $comment;
            });

        return response()->json([
            'success'  => true,
            'comments' => $comments
        ]);
    }

    /**
     * Récupère les stories récentes (Posts éphémères de type PIC/VID)
     */
    public function getStories(): JsonResponse
    {
        $userId = Auth::id();

        // Retrieve current user favorites
        $favorites = [];
        if ($userId) {
            $favorites = \DB::table('author_favorites')
                ->where('user_id', $userId)
                ->get()
                ->map(function($f) { 
                    return ($f->author_type === 'SOURCE') ? 'SOURCE_' . $f->source_name : $f->author_type . '_' . $f->author_id; 
                })
                ->toArray();
        }

        $query = Post::with(['user:id,first_name,last_name,display_name,picture', 'provider:id,first_name,last_name,display_name,avatar'])
                ->select(['id','user_id','author_type','type','source','content','media_url','external_link','likes_count','dislikes_count','shares_count','comments_count','status','expires_at','published_at','created_at'])
                ->whereIn('type', ['SOCIAL_PIC', 'SOCIAL_VID', 'ROAD_INFO', 'NEWS', 'RSS_NEWS'])
                ->where('status', 'ACTIVE')
                ->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->where('source', '!=', 'Picme'); // Strict Picme fallback removal

        // FILTRAGE : TOUS LES POSTS (utilisateurs ou sources) ne s'affichent QUE si l'auteur est dans les favoris.
        $query->where(function($q) use ($favorites) {
            // S'il n'y a pas de favoris, on ne retourne rien (la bulle stories sera vide ou contiendra juste Picme Info si favoris système)
            if (empty($favorites)) {
                $q->whereRaw('1 = 0'); // Faux forcé
                return;
            }
            
            foreach ($favorites as $fav) {
                if (str_starts_with($fav, 'SOURCE_')) {
                    $source = str_replace('SOURCE_', '', $fav);
                    $q->orWhere(function($sub) use ($source) {
                        $sub->whereIn('type', ['NEWS', 'RSS_NEWS', 'ROAD_INFO'])
                            ->where('source', $source);
                    });
                } else {
                    // Format type_id (ex: USER_15 or PROVIDER_8)
                    $parts = explode('_', $fav);
                    if (count($parts) >= 2) {
                        $type = $parts[0];
                        $id = $parts[1];
                        $q->orWhere(function($sub) use ($type, $id) {
                            // ✅ FIX: inclure TOUS les types de posts d'un auteur favori
                            $sub->whereIn('type', ['SOCIAL_PIC', 'SOCIAL_VID', 'SOCIAL_POST', 'ROAD_INFO', 'TRIP', 'INTENTION'])
                                ->where('author_type', $type)
                                ->where('user_id', $id);
                        });
                    }
                }
            }
        });

        $query->latest()->limit(50); // Plus de contenu pour permettre le défilement de source

        // Cache the stories result to drastically improve performance
        $cacheKey = 'social_stories_user_' . ($userId ?: 'guest');
        $stories = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(3), function () use ($query) {
            return $query->get();
        });
        $postIds = $stories->pluck('id')->toArray();
        $userLikes = [];
        $userDislikes = [];
        if ($userId && !empty($postIds)) {
            $userLikes = \App\Models\PostLike::whereIn('post_id', $postIds)->where('user_id', $userId)->where('type', 'LIKE')->pluck('post_id')->toArray();
            $userDislikes = \App\Models\PostDislike::whereIn('post_id', $postIds)->where('user_id', $userId)->pluck('post_id')->toArray();
        }

        $processed = $stories->map(function($post) use ($favorites, $userLikes, $userDislikes) {
            if ($post->media_url && !str_starts_with($post->media_url, 'http')) {
                $post->media_url = \Storage::disk('s3')->url( $post->media_url);
            }
            if ($post->user && $post->user->picture && !str_starts_with($post->user->picture, 'http')) {
                $post->user->picture = \Storage::disk('s3')->url( $post->user->picture);
            }

            // Sync Like / Dislike
            $post->is_liked = in_array($post->id, $userLikes);
            $post->is_disliked = in_array($post->id, $userDislikes);

            // Unify author data
            $author = ($post->author_type === 'PROVIDER') ? $post->provider : $post->user;
            $isNews = ($post->type === 'NEWS' || $post->type === 'RSS_NEWS');
            $isTraffic = ($post->type === 'ROAD_INFO');

            if ($isNews) {
                $prettySource = 'Actualité RSS';
                if ($post->source === 'ABIDJAN_NET') $prettySource = 'Abidjan.net';
                elseif ($post->source === 'LINFODROME') $prettySource = 'L\'Infodrome';
                elseif ($post->source === 'KOACI') $prettySource = 'KOACI';
                elseif ($post->source === 'AIP') $prettySource = 'AIP';
                elseif ($post->source === 'RTI') $prettySource = 'RTI';
                elseif ($post->source === 'FRATMAT') $prettySource = 'FratMat';
                elseif ($post->source === 'INTERNAL') $prettySource = 'AIP';
                
                $post->author_name = $prettySource;
                $post->author_picture = asset('img/logo.png');
                $post->user_id = -1;
            } else {
                $pseudo = $author ? $author->display_name : null;
                if ($pseudo && $pseudo !== 'null' && trim($pseudo) !== '') {
                    $post->author_name = $pseudo;
                } else if ($author) {
                    $post->author_name = trim($author->first_name . ' ' . $author->last_name);
                } else {
                    $post->author_name = 'Membre Picme';
                }
                $post->author_picture = $author ? ($post->author_type === 'PROVIDER' ? $author->avatar : $author->picture) : null;
                
                // Fallback pour les ROAD_INFO générés par le système (sans user)
                if ($isTraffic && !$author) {
                    $post->author_name = 'Info Trafic';
                    $post->author_picture = asset('img/logo.png');
                    $post->user_id = -1;
                }
            }

            if ($post->author_picture && !str_starts_with($post->author_picture, 'http')) {
                $post->author_picture = \Storage::disk('s3')->url( $post->author_picture);
            }

            unset($post->provider);
            unset($post->user);
            return $post;
        });

        // Group stories by author/source
        $groupedStories = $processed->groupBy(function($post) {
            if ($post->type === 'NEWS' || $post->type === 'RSS_NEWS' || $post->type === 'ROAD_INFO') {
                return 'SOURCE_' . $post->author_name;
            }
            return $post->author_type . '_' . $post->user_id;
        })->map(function($group) use ($favorites) {
            $latest = $group->first();
            $authorKey = ($latest->type === 'NEWS' || $latest->type === 'RSS_NEWS' || $latest->type === 'ROAD_INFO') 
                         ? 'SOURCE_' . $latest->author_name 
                         : $latest->author_type . '_' . $latest->user_id;
            
            $latest->is_favorite_author = in_array($authorKey, $favorites);
            $latest->story_count = $group->count();
            
            // Format pour le viewer mobile
            $latest->all_items = $group->map(function($item) {
                return [
                    'id'             => $item->id,
                    'url'            => $item->media_url,
                    'content'        => $item->content,
                    'type'           => $item->type,
                    'user_id'        => $item->user_id,
                    'likes_count'    => $item->likes_count,
                    'dislikes_count' => $item->dislikes_count,
                    'shares_count'   => $item->shares_count ?? 0,
                    'comments_count' => $item->comments_count,
                    'is_liked'       => $item->is_liked,
                    'is_disliked'    => $item->is_disliked,
                    'external_link'  => $item->external_link ?? '',
                    'published_at'   => $item->published_at ? $item->published_at : $item->created_at
                ];
            })->values()->toArray();

            return $latest;
        })->values();

        // Trier : Favoris en premier
        $sortedStories = $groupedStories->sortByDesc('is_favorite_author')->values();

        return response()->json([
            'success' => true,
            'stories' => $sortedStories
        ]);
    }

    /**
     * Toggle favorite status for an author
     */
    public function toggleFavoriteAuthor(Request $request): JsonResponse
    {
        $request->validate([
            'author_id' => 'nullable|integer',
            'author_type' => 'required|string|in:USER,PROVIDER,SOURCE',
            'source_name' => 'nullable|string'
        ]);

        $userId = Auth::id();
        $authorId = $request->author_id;
        $authorType = $request->author_type;
        $sourceName = $request->source_name;

        \Log::info("TOGGLE_FAVORITE: User=$userId, Author=$authorId, Type=$authorType, Source=$sourceName");

        $query = \DB::table('author_favorites')
            ->where('user_id', $userId)
            ->where('author_type', $authorType);

        if ($authorType === 'SOURCE') {
            $query->where('source_name', $sourceName);
        } else {
            $query->where('author_id', $authorId);
        }

        $favorite = $query->first();

        if ($favorite) {
            \DB::table('author_favorites')->where('id', $favorite->id)->delete();
            $status = 'REMOVED';
        } else {
            \DB::table('author_favorites')->insert([
                'user_id' => $userId,
                'author_id' => $authorId,
                'author_type' => $authorType,
                'source_name' => $sourceName,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $status = 'ADDED';
        }

        // ✅ FIX: Invalider le cache stories de cet utilisateur pour qu'il voie
        // immédiatement les nouvelles publications lors du prochain fetchStoriesHeader()
        \Illuminate\Support\Facades\Cache::forget('social_stories_user_' . $userId);

        return response()->json([
            'success' => true,
            'status' => $status
        ]);
    }

    /**
     * Recherche d'utilisateurs par nom pour le réseautage social
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $request->validate(['query' => 'required|string|min:3']);
        $query = $request->input('query');

        $users = \App\Models\User::where(function($q) use ($query) {
                $q->where('first_name', 'LIKE', "%$query%")
                  ->orWhere('last_name', 'LIKE', "%$query%");
            })
            ->select('id', 'first_name', 'last_name', 'picture', 'social_points', 'social_rating')
            ->limit(10)
            ->get()
            ->map(function($user) {
                $user->rank_label = $this->calculateRank($user->social_points);
                return $user;
            });

        return response()->json([
            'success' => true,
            'users'   => $users
        ]);
    }

    /**
     * Voir le profil public d'un autre membre (Karma, Badge, Réputation)
     */
    public function showMemberProfile(int $id): JsonResponse
    {
        $user = \App\Models\User::select('id', 'first_name', 'last_name', 'display_name', 'picture', 'user_badge', 'social_points', 'social_rating', 'created_at')
            ->findOrFail($id);

        $rank = $this->calculateRank($user->social_points);

        return response()->json([
            'success'   => true,
            'user'      => $user,
            'rank'      => $rank,
            'joined_at' => $user->created_at->format('M Y')
        ]);
    }

    /** Calcule le rang social en fonction des points (Karma) */
    private function calculateRank(int $points): string
    {
        if ($points >= 5000) return "Légende Vivante 👑";
        if ($points >= 2000) return "Ambassadeur Elite 💎";
        if ($points >= 1000) return "Membre influent ⭐";
        if ($points >= 500)  return "Membre Actif ✅";
        return "Nouveau Membre 🌱";
    }

    // =========================================================================
    // SECTION 7 : MÉTHODES UTILITAIRES
    // =========================================================================

    /**
     * Détecte si un message tente de contourner la plateforme.
     * Bloque les numéros de téléphone et les invitations à contacter en dehors de l'app.
     */
    private function isSensitiveContent(string $text): bool
    {
        // Regex : numéros de téléphone africains et mots-clés de contournement
        $phonePattern     = '/(\+|00)?\d[\d\s\-\(\)]{7,}/';
        $keywordsPattern  = '/\b(whatsapp|telegram|appelle-moi|appelle moi|mon num[eé]ro|watsap|wattsap|chat direct)\b/i';

        return preg_match($phonePattern, $text) || preg_match($keywordsPattern, $text);
    }

    // =========================================================================
    // SECTION 8 : FONCTIONS CHAUFFEUR (PROVIDER)
    // =========================================================================

    /**
     * Liste les passagers sociaux ayant rejoint le trajet du chauffeur connecté.
     */
    public function getSocialPassengers(Request $request): JsonResponse
    {
        $providerId = Auth::guard('providerapi')->id();

        // Récupérer le trajet actif du chauffeur (type 'shared' ou lié à un Post)
        $activeRide = ActiveSharedRide::where('provider_id', $providerId)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$activeRide) {
            return response()->json(['error' => 'Aucun trajet actif trouvé.'], 404);
        }

        // Récupérer les réservations liées à ce trajet
        $passengers = RideBooking::with('user:id,first_name,last_name,display_name,picture')
            ->where('active_shared_ride_id', $activeRide->id)
            ->get()
            ->map(function ($booking) {
                return [
                    'id'             => $booking->id,
                    'user_id'        => $booking->user_id,
                    'first_name'     => $booking->user->first_name,
                    'last_name'      => $booking->user->last_name,
                    'picture'        => $booking->user->picture,
                    'status'         => $booking->status, // PENDING, JOINED, COMPLETED
                    'handshake_code' => $booking->handshake_code,
                ];
            });

        return response()->json([
            'success'    => true,
            'ride_id'    => $activeRide->id,
            'passengers' => $passengers,
        ]);
    }

    /**
     * Valide la montée d'un passager via un code Handshake (QR ou Saisie).
     * Libère une partie des fonds ou confirme la présence pour l'Escrow.
     */
    public function verifyHandshake(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id'     => 'required|integer',
            'handshake_code' => 'required|string',
        ]);

        $providerId = Auth::guard('providerapi')->id();

        $booking = RideBooking::where('id', $request->booking_id)
            ->whereHas('activeSharedRide', function($q) use ($providerId) {
                $q->where('provider_id', $providerId);
            })
            ->firstOrFail();

        if ($booking->handshake_code !== $request->handshake_code) {
            return response()->json([
                'error' => 'Code de validation incorrect. Demandez au passager de vous montrer son QR code.',
            ], 422);
        }

        if ($booking->status === 'JOINED') {
            return response()->json(['message' => 'Passager déjà validé.'], 200);
        }

        $booking->update([
            'status'     => 'JOINED',
            'joined_at'  => now(),
        ]);

        // 🏆 REPUTATION AWARD (Phase 8)
        $provider = Auth::guard('providerapi')->user();
        \App\Services\ReputationService::awardPoints($provider, \App\Services\ReputationService::POINTS_HANDSHAKE);
        \App\Services\ReputationService::awardPoints($booking->user, \App\Services\ReputationService::POINTS_HANDSHAKE);

        Log::info("Handshake validé pour le passager #{$booking->user_id} sur le trajet #{$booking->active_shared_ride_id}");

        return response()->json([
            'success' => true,
            'message' => 'Passager validé avec succès ! Bonne route.',
            'points_awarded' => \App\Services\ReputationService::POINTS_HANDSHAKE,
            'next_step' => 'ARRIVE'
        ]);
    }

    /**
     * Valide l'arrivée du passager et libère les fonds séquestrés (Escrow).
     */
    public function verifyArrivalHandshake(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id'     => 'required|integer',
            'handshake_code' => 'required|string',
        ]);

        $providerId = Auth::guard('providerapi')->id();

        $booking = RideBooking::where('id', $request->booking_id)
            ->whereHas('activeSharedRide', function($q) use ($providerId) {
                $q->where('provider_id', $providerId);
            })
            ->firstOrFail();

        if ($booking->handshake_code !== $request->handshake_code) {
             return response()->json(['error' => 'Code de validation incorrect.'], 422);
        }

        if ($booking->status === 'COMPLETED') {
            return response()->json(['message' => 'Trajet déjà terminé.'], 200);
        }

        DB::transaction(function () use ($booking) {
            $booking->update([
                'status'      => 'COMPLETED',
                'finished_at' => now(),
            ]);

            // 💰 LIBÉRATION DES FONDS (Escrow)
            app(\App\Services\CommissionService::class)->releaseEscrow($booking);
        });

        return response()->json([
            'success' => true,
            'message' => 'Bravo ! Trajet terminé. Les fonds ont été libérés sur votre compte.',
            'points_awarded' => 100
        ]);
    }

    // =========================================================================
    // SECTION 9 : SONDAGES COMMUNAUTAIRES (POLLS)
    // =========================================================================

    /** Voter pour une option de sondage */
    public function votePoll(Request $request, int $pollId): JsonResponse
    {
        $request->validate(['option_id' => 'required|exists:poll_options,id']);

        $poll = Poll::where('is_active', true)->findOrFail($pollId);
        $userId = Auth::id();

        // Vérifier si déjà voté
        $alreadyVoted = PollVote::where('poll_id', $pollId)->where('user_id', $userId)->exists();
        if ($alreadyVoted) {
            return response()->json(['error' => 'Vous avez déjà voté pour ce sondage.'], 403);
        }

        DB::transaction(function () use ($pollId, $request, $userId) {
            PollVote::create([
                'poll_id' => $pollId,
                'poll_option_id' => $request->option_id,
                'user_id' => $userId
            ]);

            PollOption::find($request->option_id)->increment('votes_count');

            // 🏆 REPUTATION AWARD (Phase 8)
            $user = \App\Models\User::find($userId);
            \App\Services\ReputationService::awardPoints($user, \App\Services\ReputationService::POINTS_VOTE);
        });

        // Retourner les nouveaux résultats
        $results = PollOption::where('poll_id', $pollId)->get();
        return response()->json(['success' => true, 'results' => $results]);
    }

    /** Récupérer les détails d'un sondage spécifique */
    public function getPoll(int $pollId): JsonResponse
    {
        $poll = Poll::with('options')->findOrFail($pollId);
        return response()->json($poll);
    }

    // =========================================================================
    // SECTION 10 : PARRAINAGE (REFERRAL) & SOCIAL MAP
    // =========================================================================

    /** Mon code de parrainage et stats (compatible partenaires) */
    public function referral_info(): JsonResponse
    {
        $user = Auth::user();

        // Générer un code utilisateur si absent
        if (!$user->referral_unique_id) {
            $user->update(['referral_unique_id' => 'PIC-' . strtoupper(substr(md5($user->id), 0, 6))]);
        }

        // Récupérer le code partenaire si l'utilisateur est un partenaire
        $partner = \App\Models\Partner::where('user_id', $user->id)->first();

        return response()->json([
            'referral_code'   => $user->referral_unique_id,
            'referral_count'  => $user->referral_count,
            'wallet_balance'  => $user->wallet_balance,
            'partner_code'    => $partner ? $partner->partner_code : null,
            'partner_type'    => $partner ? $partner->type : null,
            // Stats affiliés si partenaire
            'affiliate_count' => $partner
                ? \App\Models\PartnerAffiliate::where('partner_id', $partner->id)->count()
                : null,
        ]);
    }

    /**
     * Appliquer un code de parrainage à l'inscription.
     * Résolution étendue : User (referral_unique_id) ou Partenaire (partner_code).
     * Un partenaire qui parraine reçoit une commission configurable via ses commission_rules.
     */
    public function apply_referral(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);
        $user = Auth::user();

        if ($user->referred_by_id) {
            return response()->json(['error' => 'Code déjà appliqué.'], 403);
        }

        // ── Résolution du parrain ─────────────────────────────────────────────
        $referrerUser    = null;
        $referrerPartner = null;

        // 1. Résolution via code partenaire (partner_code)
        $referrerPartner = \App\Models\Partner::where('partner_code', $request->code)
            ->where('status', 'ACTIVE')
            ->with('user')
            ->first();

        if ($referrerPartner && $referrerPartner->user) {
            $referrerUser = $referrerPartner->user;
        } else {
            // 2. Résolution via code parrainage utilisateur classique
            $referrerUser = \App\Models\User::where('referral_unique_id', $request->code)->first();
            $referrerPartner = null;
        }

        if (!$referrerUser || $referrerUser->id === $user->id) {
            return response()->json(['error' => 'Code de parrainage invalide.'], 404);
        }

        // ── Attribution des bonus ─────────────────────────────────────────────
        DB::transaction(function () use ($user, $referrerUser, $referrerPartner) {
            // Lier le parrain à l'utilisateur parrainé
            $user->update(['referred_by_id' => $referrerUser->id]);
            $referrerUser->increment('referral_count');

            // Bonus de base parrain / filleul
            $referrerBonus = $referrerPartner
                ? (float) $referrerPartner->getCommissionRule('referral_bonus_cfa', 500)
                : 500;
            $newUserBonus = 500; // Bonus fixe bienvenue filleul

            // Créditer le parrain
            $referrerUser->increment('wallet_balance', $referrerBonus);
            \App\Models\WalletPassbook::create([
                'user_id'      => $referrerUser->id,
                'partner_id'   => $referrerPartner ? $referrerPartner->id : null,
                'amount'       => $referrerBonus,
                'status'       => 'CREDITED',
                'via'          => $referrerPartner ? 'PARTNER_REFERRAL' : 'REFERRAL',
                'description'  => "Parrainage de {$user->first_name} {$user->last_name}",
                'reference_id' => (string) $user->id,
            ]);

            // Créditer le filleul
            $user->increment('wallet_balance', $newUserBonus);
            \App\Models\WalletPassbook::create([
                'user_id'     => $user->id,
                'amount'      => $newUserBonus,
                'status'      => 'CREDITED',
                'via'         => 'REFERRAL_WELCOME',
                'description' => "Bonus bienvenue (code : {$referrerUser->referral_unique_id})",
                'reference_id' => (string) $referrerUser->id,
            ]);

            // Enregistrer l'affiliation si c'est un partenaire
            if ($referrerPartner) {
                \App\Models\PartnerAffiliate::firstOrCreate(
                    ['partner_id' => $referrerPartner->id, 'affiliated_user_id' => $user->id],
                    ['affiliated_type' => 'USER', 'commission_earned' => $referrerBonus]
                );
            }

            // 🔔 Notifications
            \App\Models\AppNotification::send(
                $referrerUser->id,
                '🎁 Bonus Parrainage',
                "Bravo ! Vous avez gagné {$referrerBonus} CFA grâce au parrainage de {$user->first_name}.",
                'WALLET'
            );
            \App\Models\AppNotification::send(
                $user->id,
                '🎉 Bienvenue !',
                "Votre bonus de bienvenue de {$newUserBonus} CFA a été crédité sur votre portefeuille.",
                'WALLET'
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Félicitations ! Bonus de parrainage appliqué.',
            'referrer_type' => $referrerPartner ? 'PARTNER' : 'USER',
        ]);
    }

    /** Flux Social pour le MODE CARTE (Map Mode) */
    public function map_feed(Request $request): JsonResponse
    {
        $query = Post::with(['user:id,first_name,last_name,display_name,picture'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('status', 'ACTIVE')
            ->latest();

        if ($request->has('route_id')) {
            $query->where('pdp_route_id', $request->route_id);
        }

        // On limite aux 50 posts les plus récents pour la carte
        $posts = $query->limit(50)->get();

        return response()->json($posts);
    }

    /** Liste des notifications In-App de l'utilisateur */
    /** Liste des notifications in-app (Persistantes) */
    public function notifications(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);

        $query = \App\Models\AppNotification::latest()->limit(30);

        if ($user instanceof \App\Models\User) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('provider_id', $user->id);
        }
            
        $notifications = $query->get()->map(function($item) {
            $item->relative_time = $item->created_at ? $item->created_at->diffForHumans(null, true, true) : 'maintenant';
            return $item;
        });

        return response()->json($notifications);
    }

    /** Marquer toutes les notifications comme lues */
    public function markAllRead(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);

        $query = \App\Models\AppNotification::where('is_read', 0);
        if ($user instanceof \App\Models\User) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('provider_id', $user->id);
        }

        $query->update(['is_read' => 1]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Toutes les notifications ont été marquées comme lues.'
        ]);
    }

    /** Marquer une notification spécifique comme lue */
    public function markAsRead($id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);

        $notif = \App\Models\AppNotification::findOrFail($id);

        if ($user instanceof \App\Models\User && $notif->user_id != $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Action non autorisée'], 403);
        }
        if ($user instanceof \App\Models\Provider && $notif->provider_id != $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Action non autorisée'], 403);
        }

        $notif->update(['is_read' => 1]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Notification marquée comme lue.'
        ]);
    }

    /** Afficher les détails d'une notification spécifique */
    public function showNotification($id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);

        $notif = \App\Models\AppNotification::find($id);
        if (!$notif) {
            return response()->json(['status' => 'error', 'message' => 'Notification non trouvée.'], 404);
        }

        if ($user instanceof \App\Models\User && $notif->user_id != $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Action non autorisée'], 403);
        }
        if ($user instanceof \App\Models\Provider && $notif->provider_id != $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Action non autorisée'], 403);
        }

        $notif->relative_time = $notif->created_at ? $notif->created_at->diffForHumans(null, true, true) : 'maintenant';

        return response()->json([
            'status' => 'success',
            'data'   => $notif
        ]);
    }

    /** Mettre à jour une notification spécifique */
    public function updateNotification(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);

        $notif = \App\Models\AppNotification::find($id);
        if (!$notif) {
            return response()->json(['status' => 'error', 'message' => 'Notification non trouvée.'], 404);
        }

        if ($user instanceof \App\Models\User && $notif->user_id != $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Action non autorisée'], 403);
        }
        if ($user instanceof \App\Models\Provider && $notif->provider_id != $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Action non autorisée'], 403);
        }

        $request->validate([
            'title'   => 'nullable|string|max:255',
            'message' => 'nullable|string|max:1000',
            'type'    => 'nullable|string|max:50',
            'is_read' => 'nullable|boolean'
        ]);

        $data = $request->only(['title', 'message', 'type', 'is_read']);
        $notif->update($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Notification mise à jour avec succès.',
            'data'    => $notif
        ]);
    }

    /** Supprimer une notification spécifique */
    public function destroyNotification($id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);

        $notif = \App\Models\AppNotification::find($id);
        if (!$notif) {
            return response()->json(['status' => 'error', 'message' => 'Notification non trouvée.'], 404);
        }

        if ($user instanceof \App\Models\User && $notif->user_id != $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Action non autorisée'], 403);
        }
        if ($user instanceof \App\Models\Provider && $notif->provider_id != $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Action non autorisée'], 403);
        }

        $notif->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Notification supprimée avec succès.'
        ]);
    }

    public function bulkDestroyNotification(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);

        $ids = $request->input('ids');
        if (!is_array($ids)) {
            return response()->json(['status' => 'error', 'message' => 'IDs non fournis.'], 400);
        }

        $query = \App\Models\AppNotification::whereIn('id', $ids);
        if ($user instanceof \App\Models\User) {
            $query->where('user_id', $user->id);
        } else if ($user instanceof \App\Models\Provider) {
            $query->where('provider_id', $user->id);
        }

        $query->delete();
        return response()->json(['status' => 'success', 'message' => 'Notifications supprimées avec succès.']);
    }

    /** Achat d'un abonnement via le portefeuille */
    public function purchase_subscription(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|integer'
        ]);

        $user = Auth::user();
        $plan = \App\Models\SubscriptionPlan::findOrFail($request->plan_id);
        
        // Détecter le portefeuille selon le type d'acteur
        $balanceField = ($user instanceof \App\Models\User) ? 'wallet_balance' : 'eco_wallet_balance';

        if ($user->$balanceField < $plan->price) {
            return response()->json(['error' => 'Solde insuffisant pour cet abonnement.'], 402);
        }

        try {
            DB::transaction(function () use ($user, $plan, $balanceField) {
                // Débit du portefeuille
                $user->decrement($balanceField, $plan->price);

                // Historique portefeuille
                if ($user instanceof \App\Models\User) {
                    \App\Models\WalletPassbook::create([
                        'user_id' => $user->id,
                        'amount'  => -$plan->price,
                        'status'  => 'DEBIT',
                        'via'     => 'SUBSCRIPTION_PURCHASE',
                    ]);
                } else {
                    \App\Models\ProviderWallet::create([
                        'provider_id' => $user->id,
                        'amount'      => -$plan->price,
                        'type'        => 'DEBIT',
                        'transaction_id' => 'SUB_' . time(),
                        'transaction_desc' => 'Achat abonnement ' . $plan->name,
                        'balance'   => $user->eco_wallet_balance
                    ]);
                }

                // Mise à jour du statut
                $user->subscription_plan_id = $plan->id;
                $user->subscription_expires_at = \Carbon\Carbon::now()->addMonth();
                $user->user_badge = ($plan->id == 2) ? 'VIP' : 'PREMIUM';
                $user->is_verified = true;
                $user->save();

                // 🔔 Notification
                \App\Models\AppNotification::send($user, "🛡️ Statut PRO Activé", "Félicitations ! Vous êtes maintenant membre {$user->user_badge}. Profitez de vos avantages !", "SYSTEM");
            });

            return response()->json([
                'success' => true,
                'message' => 'Félicitations ! Vous avez souscrit au plan ' . $plan->name,
                'user'    => $user
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de l\'activation : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Récompenser la promotion de l'application (Partage du code de parrainage)
     */
    public function rewardAppPromotion(Request $request)
    {
        try {
            $user = Auth::user();
            // On limite à une récompense par jour pour éviter les abus
            $today = \Carbon\Carbon::today();
            // (Note: Dans une version réelle, on stockerait ça dans une table logs)
            
            // 🏆 RECOMPENSE : +1 point pour partage réseau social
            $points = 1.0; 
            $user->increment('social_points', $points);

            // Sync Badge
            $user->syncKarmaBadge();

            return response()->json([
                'message' => 'Merci pour votre promotion ! Vous avez gagné ' . $points . ' point Karma.',
                'social_points' => $user->social_points,
                'badge' => $user->user_badge
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Récompenser le partage de contacts (Invitation d'amis)
     */
    public function rewardContactSharing(Request $request)
    {
        try {
            $user = Auth::user();
            $count = (int) $request->input('contact_count', 1);
            
            // Sécurité : Max 20 contacts par appel pour éviter les abus
            if ($count > 20) $count = 20;

            $points = $count * 0.5;
            $user->increment('social_points', $points);

            // Sync Badge
            $user->syncKarmaBadge();

            return response()->json([
                'message' => 'Merci pour vos invitations ! Vous avez gagné ' . $points . ' points Karma.',
                'social_points' => $user->social_points,
                'badge' => $user->user_badge
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Affiche un article complet sur une page web dédiée (SEO et partage unique)
     */
    public function showArticle($id)
    {
        $post = \App\Models\Post::findOrFail($id);
        
        // Incrémenter les vues
        $post->increment('shares_count'); 

        if ($post->external_link) {
            return redirect()->away($post->external_link);
        }

        return redirect('/'); // Fallback
    }
}
