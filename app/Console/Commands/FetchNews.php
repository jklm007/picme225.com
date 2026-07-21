<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;

class FetchNews extends Command
{
    protected $signature = 'news:fetch';
    protected $description = 'Collecte les news réelles via RSS (Multi-Source Verified) et les injecte dans le fil social.';

    public function handle()
    {
        $this->info("Début Sync News (Verified Multi-Source Mode)...");

        $sources = [
            ['name' => 'AIP', 'url' => 'https://www.aip.ci/feed/'],
            ['name' => 'L\'Infodrome', 'url' => 'https://www.linfodrome.com/rss'],
            ['name' => 'RTI', 'url' => 'https://www.rti.info/rss'],
            ['name' => 'FratMat', 'url' => 'https://www.fratmat.info/index.php?format=feed&type=rss'],
            ['name' => 'Abidjan.net', 'url' => 'https://news.abidjan.net/rss/societe.xml'],
            ['name' => 'KOACI', 'url' => 'https://www.koaci.com/feed'],
        ];

        $admin = User::where('id', 1)->first() ?? User::first();

        foreach ($sources as $source) {
            $this->line("Source: " . $source['name']);
            try {
                $content = $this->fetchUrl($source['url']);
                if (!$content) {
                    $this->error("  Échec CURL (404 ou Timeout)");
                    continue;
                }

                $encoding = mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true);
                if ($encoding && $encoding != 'UTF-8') {
                    $content = mb_convert_encoding($content, 'UTF-8', $encoding);
                }

                // Parser Regex Global
                preg_match_all('/<(item|entry)>(.*?)<\/(item|entry)>/s', $content, $items);
                
                if (empty($items[2])) {
                    $this->error("  Aucun item trouvé.");
                    continue;
                }

                $added = 0;
                foreach ($items[2] as $itemXml) {
                    try {
                        if ($added >= 10) break;

                        // Extraction robuste du Titre
                        if (preg_match('/<title[^>]*>(.*?)<\/title>/s', $itemXml, $tMatch)) {
                            $title = str_replace(['<![CDATA[', ']]>'], '', $tMatch[1]);
                            $title = trim($title);
                        } else {
                            $title = 'Actualité';
                        }

                        // Extraction du Lien
                        preg_match('/<link[^>]*>(?:<!\[CDATA\[)?(.*?)(?:\]\]>)?<\/link>/s', $itemXml, $lMatch);
                        if (empty($lMatch)) preg_match('/href=["\']([^"\']+)["\']/', $itemXml, $lMatch);
                        $link = !empty($lMatch[1]) ? trim($lMatch[1]) : '';

                        // Extraction de la Description
                        if (preg_match('/<(description|summary|content:encoded)[^>]*>(.*?)<\/\1>/s', $itemXml, $dMatch)) {
                            $descContent = str_replace(['<![CDATA[', ']]>'], '', $dMatch[2]);
                            $descContent = trim($descContent);
                            $desc = strip_tags($descContent);
                        } else {
                            $descContent = '';
                            $desc = '';
                        }

                        // --- EXTRACTION DATE ---
                        $publishedAt = null;
                        if (preg_match('/<(pubDate|dc:date)>(.*?)<\/\1>/s', $itemXml, $dateMatch)) {
                            try {
                                $publishedAt = Carbon::parse($dateMatch[2]);
                            } catch (\Exception $e) {
                                $publishedAt = Carbon::now();
                            }
                        } else {
                            $publishedAt = Carbon::now();
                        }

                        // FILTRAGE 7 JOURS - articles restent visibles une semaine
                        if ($publishedAt->lt(Carbon::now()->subDays(7))) {
                            continue;
                        }

                        // --- EXTRACTION IMAGE ---
                        $imageUrl = null;
                        // 1. media:content or media:thumbnail
                        if (preg_match('/<media:(?:content|thumbnail)[^>]*url=["\']([^"\']+)["\']/', $itemXml, $mMatch)) {
                            $imageUrl = $mMatch[1];
                        }
                        // 2. enclosure
                        if (!$imageUrl && preg_match('/<enclosure[^>]*url=["\']([^"\']+)["\']/', $itemXml, $eMatch)) {
                            $imageUrl = $eMatch[1];
                        }
                        // 3. img tag in description
                        if (!$imageUrl && !empty($descContent) && preg_match('/<img[^>]*src=["\']([^"\']+)["\']/', $descContent, $iMatch)) {
                            $imageUrl = $iMatch[1];
                        }

                        // Nettoyage UTF-8 robuste pour MySQL
                        $title = mb_convert_encoding($title, 'UTF-8', 'UTF-8');
                        $desc  = mb_convert_encoding($desc, 'UTF-8', 'UTF-8');
                        $title = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $title);
                        $desc  = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $desc);
                        $link  = trim($link);

                        if ($link && !Post::where('external_link', $link)->exists()) {
                            // Utiliser l'admin (user_id=1) car la table exige un user_id non-NULL
                            $adminId = $admin ? $admin->id : 1;
                            // PostgreSQL CHECK constraint mapping for 'source'
                            $dbSource = 'INTERNAL';
                            if (str_contains(strtoupper($source['name']), 'ABIDJAN')) $dbSource = 'ABIDJAN_NET';
                            elseif (str_contains(strtoupper($source['name']), 'INFODROME')) $dbSource = 'LINFODROME';
                            elseif (str_contains(strtoupper($source['name']), 'KOACI')) $dbSource = 'KOACI';
                            elseif (str_contains(strtoupper($source['name']), 'AIP')) $dbSource = 'AIP';
                            elseif (str_contains(strtoupper($source['name']), 'RTI')) $dbSource = 'RTI';
                            elseif (str_contains(strtoupper($source['name']), 'FRATMAT')) $dbSource = 'FRATMAT';

                            Post::create([
                                'user_id'          => $adminId,
                                'author_type'      => 'RSS',
                                'type'             => 'RSS_NEWS',
                                'category'         => 'INFO_REEL',
                                'content'          => $title . "\n\n" . substr($desc, 0, 500),
                                'external_link'    => $link,
                                'media_url'        => $imageUrl,
                                'source'           => $dbSource,
                                'published_at'     => $publishedAt,
                                'publication_date' => $publishedAt->toDateString(),
                                'publication_time' => $publishedAt->toTimeString(),
                                'status'           => 'ACTIVE',
                                'is_shareable'     => true,
                                'expires_at'       => $publishedAt->copy()->addDays(7)
                            ]);
                            $added++;
                        }
                    } catch (\Exception $itemE) {
                        $this->warn("    Item sauté : " . substr($itemE->getMessage(), 0, 50));
                    }
                }
                $this->info("  Ajoutés: " . $added);
            } catch (\Exception $e) {
                $this->error("  Erreur: " . $e->getMessage());
            }
        }

        $this->info("Sync terminée.");
        \Cache::put('last_news_sync_at', now(), 1440); // 24h
        \Cache::forget('active_news_sources_data');
    }

    private function fetchUrl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        $result = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code >= 400) return null;
        return $result;
    }
}
