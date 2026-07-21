<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Post;

/**
 * Controller pour agréger et livrer les news RSS réelles
 */
class NewsAggregatorController extends Controller
{
    private array $sources = [
        [
            'name'      => 'Abidjan.net',
            'url'       => 'https://news.abidjan.net/rss/',
            'logo'      => 'https://news.abidjan.net/favicon.ico',
            'color'     => '#1565C0',
            'theme'     => 'general'
        ],
        [
            'name'      => 'KOACI',
            'url'       => 'https://koaci.com/rss.xml',
            'logo'      => 'https://koaci.com/favicon.ico',
            'color'     => '#E65100',
            'theme'     => 'buzz'
        ],
        [
            'name'      => 'FratMat',
            'url'       => 'https://www.fratmat.info/index.php/feed',
            'logo'      => 'https://www.fratmat.info/favicon.ico',
            'color'     => '#2E7D32',
            'theme'     => 'officiel'
        ],
        [
            'name'      => 'L\'Infodrome',
            'url'       => 'https://www.linfodrome.com/fils-rss/actualite.xml',
            'logo'      => 'https://www.linfodrome.com/favicon.ico',
            'color'     => '#D32F2F',
            'theme'     => 'general'
        ],
        [
            'name'      => 'AIP',
            'url'       => 'https://www.aip.ci/feed/',
            'logo'      => 'https://www.aip.ci/favicon.ico',
            'color'     => '#00695C',
            'theme'     => 'officiel'
        ],
        [
            'name'      => 'RTI',
            'url'       => 'https://www.rti.ci/feed/',
            'logo'      => 'https://www.rti.ci/favicon.ico',
            'color'     => '#6A1B9A',
            'theme'     => 'officiel'
        ]
    ];

    private array $transportKeywords = [
        'trafic', 'route', 'embouteillage', 'accident', 'Yamoussoukro', 'Abidjan',
        'péage', 'carrefour', 'pont', 'vitesse', 'radar', 'circulation', 'bouchon',
        'travaux', 'bitume', 'transport', 'taxi', 'bus', 'corridor', 'gbaka', 'woro-woro'
    ];

    /**
     * GET /api/user/news-feed
     * Livrer les news depuis la base de données (remplie par news:fetch)
     */
    public function index(Request $request): JsonResponse
    {
        $limit     = $request->input('limit', 30);
        $publisher = $request->input('publisher');
        $userId    = \Auth::id();

        $query = Post::with(['user', 'provider'])
                     ->where('status', 'ACTIVE')
                     ->where(function($q) {
                         $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                     })
                     ->latest();

        if ($publisher) {
            if ($publisher === 'Info Trafic') {
                $query->where('type', 'ROAD_INFO');
            } else {
                $query->whereIn('type', ['NEWS', 'RSS_NEWS'])->where('source', 'LIKE', "%$publisher%");
            }
        } else {
            $query->whereIn('type', ['NEWS', 'RSS_NEWS']);
        }

        $cacheKey = 'news_feed_posts_' . ($publisher ?? 'all') . '_' . $limit;
        $posts = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(5), function() use ($query, $limit) {
            return $query->limit($limit)->get();
        });

        $postIds = $posts->pluck('id')->toArray();

        // Bulk fetch interactions to prevent N+1 queries (OUTSIDE CACHE)
        $userLikes = [];
        $userDislikes = [];
        $userFavorites = [];
        
        if ($userId && !empty($postIds)) {
            $userLikes = \App\Models\PostLike::whereIn('post_id', $postIds)->where('user_id', $userId)->where('type', 'LIKE')->pluck('post_id')->toArray();
            $userDislikes = \App\Models\PostDislike::whereIn('post_id', $postIds)->where('user_id', $userId)->pluck('post_id')->toArray();
            $userFavorites = \App\Models\PostLike::whereIn('post_id', $postIds)->where('user_id', $userId)->where('type', 'FAVORITE')->pluck('post_id')->toArray();
        }

        $articles = $posts->map(function($post) use ($userLikes, $userDislikes, $userFavorites) {
                $parts = explode("\n\n", $post->content);
                
                $type = ($post->type === 'ROAD_INFO') ? 'ROAD_INFO' : 'RSS_NEWS';
                
                $authorName = $post->source;
                $authorLogo = '';
                if ($type === 'ROAD_INFO') {
                    $author = $post->user ?: $post->provider;
                    if ($author) {
                        $pseudo = $author->display_name;
                        if ($pseudo && $pseudo !== 'null' && trim($pseudo) !== '') {
                            $authorName = $pseudo;
                        } else if ($author) {
                            $authorName = trim($author->first_name . ' ' . $author->last_name);
                        } else {
                            $authorName = 'Membre Picme';
                        }
                        $authorLogo = $post->author_type === 'PROVIDER' ? $author->avatar : $author->picture;
                        if ($authorLogo && !str_starts_with($authorLogo, 'http')) {
                            $authorLogo = \Storage::disk('s3')->url( $authorLogo);
                        }
                    } else {
                        $authorName = 'Info Trafic';
                        $authorLogo = asset('img/logo.png');
                    }
                }

                return [
                    'id'                   => $post->id,
                    'type'                 => $type,
                    'is_transport_related' => true,
                    'title'                => $parts[0] ?? 'Actualité',
                    'content'              => $parts[1] ?? (isset($parts[0]) ? '' : $post->content),
                    'external_url'         => $post->external_link,
                    'cover_image'          => $post->media_url,
                    'published_at'         => $post->created_at->toIso8601String(),
                    'likes_count'          => $post->likes_count,
                    'comments_count'       => $post->comments_count,
                    'is_liked'             => in_array($post->id, $userLikes),
                    'is_disliked'          => in_array($post->id, $userDislikes),
                    'is_favorited'         => in_array($post->id, $userFavorites),
                    'source'               => [
                        'name'  => $authorName,
                        'logo'  => $authorLogo,
                        'color' => ($post->type === 'ROAD_INFO') ? '#E64A19' : '#2E7D32',
                        'theme' => 'all'
                    ]
                ];
            });

        return response()->json([
            'success'      => true,
            'count'        => count($articles),
            'cached_until' => now()->addMinutes(5)->toIso8601String(),
            'data'         => $articles
        ]);
    }

    /**
     * GET /api/user/news-sources
     * Retourne dynamiquement la liste des sources qui ont des articles actifs
     */
    public function sources()
    {
        // Cache réduit à 1 minute pour plus de réactivité lors des tests
        $dynamicSources = \Cache::remember('active_news_sources_data', now()->addMinutes(1), function() {
            $activeDbSources = Post::whereIn('type', ['NEWS', 'RSS_NEWS'])
                                 ->where('status', 'ACTIVE')
                                 ->where('created_at', '>=', now()->subDays(7)) // Uniquement sources avec posts récents
                                 ->where(function($q) {
                                     $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                                 })
                                 ->distinct()
                                 ->pluck('source')
                                 ->filter()
                                 ->toArray();

            $list = [];
            foreach ($this->sources as $source) {
                $dbEnum = 'INTERNAL';
                $reqSource = strtoupper($source['name']);
                if (str_contains($reqSource, 'ABIDJAN')) $dbEnum = 'ABIDJAN_NET';
                elseif (str_contains($reqSource, 'INFODROME')) $dbEnum = 'LINFODROME';
                elseif (str_contains($reqSource, 'KOACI')) $dbEnum = 'KOACI';
                elseif (str_contains($reqSource, 'AIP')) $dbEnum = 'AIP';
                elseif (str_contains($reqSource, 'RTI')) $dbEnum = 'RTI';
                elseif (str_contains($reqSource, 'FRATMAT')) $dbEnum = 'FRATMAT';

                if (in_array($dbEnum, $activeDbSources)) {
                    $list[] = $source;
                }
            }

            // Info Trafic
            $hasTraffic = Post::where('type', 'ROAD_INFO')
                              ->where('status', 'ACTIVE')
                              ->where('created_at', '>=', now()->subDays(7))
                              ->where(function($q) {
                                  $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                              })
                              ->exists();

            if ($hasTraffic) {
                $list[] = [
                    'name'  => 'Info Trafic',
                    'url'   => '',
                    'logo'  => '',
                    'color' => '#E64A19',
                    'theme' => 'traffic'
                ];
            }
            return $list;
        });

        return response()->json([
            'success' => true,
            'data'    => $dynamicSources
        ]);
    }

    // Méthodes internes conservées pour le FetchNews Command si besoin (ou héritage)
    public function fetchAllFeedsParallel(string $theme = 'all', ?string $corridor = null, ?string $publisher = null): array
    {
        // ... Logique conservée dans FetchNews ...
        return [];
    }
}
