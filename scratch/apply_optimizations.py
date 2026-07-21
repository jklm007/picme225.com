import os

# Configuration des fichiers et remplacements
workspace = r"c:\Users\HP\Documents\Jews-world Backend\picme225.com_backend"

replacements = {
    # 1. UserRideController.php
    os.path.join(workspace, "app", "Http", "Controllers", "UserRideController.php"): [
        # OSRM cache
        (
            '                            // 1. Temps restant pour finir la course actuelle (position du chauffeur → dépose A)\n'
            '                            $driverLat = $activeTrip->track_latitude ?: $activeTrip->s_latitude;\n'
            '                            $driverLng = $activeTrip->track_longitude ?: $activeTrip->s_longitude;\n'
            '                            $routeCurrent = $routing->getRouteEstimate(\n'
            '                                (float) $driverLat, (float) $driverLng,\n'
            '                                (float) $activeTrip->d_latitude,\n'
            '                                (float) $activeTrip->d_longitude\n'
            '                            );\n\n'
            '                            // 2. Temps pour aller de la dépose A au ramassage B\n'
            '                            $routeNext = $routing->getRouteEstimate(\n'
            '                                (float) $activeTrip->d_latitude,\n'
            '                                (float) $activeTrip->d_longitude,\n'
            '                                (float) $request[\'s_latitude\'],\n'
            '                                (float) $request[\'s_longitude\']\n'
            '                            );',
            '                            // 1. Temps restant pour finir la course actuelle (position du chauffeur → dépose A)\n'
            '                            $driverLat = $activeTrip->track_latitude ?: $activeTrip->s_latitude;\n'
            '                            $driverLng = $activeTrip->track_longitude ?: $activeTrip->s_longitude;\n'
            '                            $cacheKeyCurrent = \'osrm_route:\' . md5("{$driverLat},{$driverLng},{$activeTrip->d_latitude},{$activeTrip->d_longitude}");\n'
            '                            $routeCurrent = \Cache::remember($cacheKeyCurrent, 15, function () use ($routing, $driverLat, $driverLng, $activeTrip) {\n'
            '                                return $routing->getRouteEstimate(\n'
            '                                    (float) $driverLat, (float) $driverLng,\n'
            '                                    (float) $activeTrip->d_latitude,\n'
            '                                    (float) $activeTrip->d_longitude\n'
            '                                );\n'
            '                            });\n\n'
            '                            // 2. Temps pour aller de la dépose A au ramassage B\n'
            '                            $cacheKeyNext = \'osrm_route:\' . md5("{$activeTrip->d_latitude},{$activeTrip->d_longitude},{$request[\'s_latitude\']},{$request[\'s_longitude\']}");\n'
            '                            $routeNext = \Cache::remember($cacheKeyNext, 15, function () use ($routing, $activeTrip, $request) {\n'
            '                                return $routing->getRouteEstimate(\n'
            '                                    (float) $activeTrip->d_latitude,\n'
            '                                    (float) $activeTrip->d_longitude,\n'
            '                                    (float) $request[\'s_latitude\'],\n'
            '                                    (float) $request[\'s_longitude\']\n'
            '                                );\n'
            '                            });'
        ),
        # Bounding Box classical dispatch
        (
            '        $Providers = Provider::with(\'service\')\n'
            '            ->select(DB::Raw("(6371 * acos( cos( radians(\'$latitude\') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(\'$longitude\') ) + sin( radians(\'$latitude\') ) * sin( radians(latitude) ) ) ) AS distance"), \'id\', \'eco_wallet_balance\', \'service_type_id\', \'commune\')\n'
            '            ->where(\'status\', \'approved\')\n'
            '            ->whereRaw("(6371 * acos( cos( radians(\'$latitude\') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(\'$longitude\') ) + sin( radians(\'$latitude\') ) * sin( radians(latitude) ) ) ) <= $distance");',
            '        // Bounding Box filter (Pre-filter before Haversine equation to hit composite index)\n'
            '        $lat_deg = $distance / 111.0;\n'
            '        $lng_deg = $distance / (111.0 * cos(deg2rad($latitude)));\n'
            '        $min_lat = $latitude - $lat_deg;\n'
            '        $max_lat = $latitude + $lat_deg;\n'
            '        $min_lng = $longitude - $lng_deg;\n'
            '        $max_lng = $longitude + $lng_deg;\n\n'
            '        $Providers = Provider::with(\'service\')\n'
            '            ->select(DB::Raw("(6371 * acos( cos( radians(\'$latitude\') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(\'$longitude\') ) + sin( radians(\'$latitude\') ) * sin( radians(latitude) ) ) ) AS distance"), \'id\', \'eco_wallet_balance\', \'service_type_id\', \'commune\')\n'
            '            ->where(\'status\', \'approved\')\n'
            '            ->whereBetween(\'latitude\', [$min_lat, $max_lat])\n'
            '            ->whereBetween(\'longitude\', [$min_lng, $max_lng])\n'
            '            ->whereRaw("(6371 * acos( cos( radians(\'$latitude\') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(\'$longitude\') ) + sin( radians(\'$latitude\') ) * sin( radians(latitude) ) ) ) <= $distance");'
        ),
        # Bounding Box show_providers service check
        (
            '                $query = Provider::with(\'service\')->whereIn(\'id\', $ActiveProviders)\n'
            '                    ->where(\'status\', \'approved\')\n'
            '                    ->whereRaw("(1.609344 * 3956 * acos( cos( radians(\'$latitude\') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(\'$longitude\') ) + sin( radians(\'$latitude\') ) * sin( radians(latitude) ) ) ) <= $distance");',
            '                $lat_deg = $distance / 111.0;\n'
            '                $lng_deg = $distance / (111.0 * cos(deg2rad($latitude)));\n'
            '                $min_lat = $latitude - $lat_deg;\n'
            '                $max_lat = $latitude + $lat_deg;\n'
            '                $min_lng = $longitude - $lng_deg;\n'
            '                $max_lng = $longitude + $lng_deg;\n\n'
            '                $query = Provider::with(\'service\')->whereIn(\'id\', $ActiveProviders)\n'
            '                    ->where(\'status\', \'approved\')\n'
            '                    ->whereBetween(\'latitude\', [$min_lat, $max_lat])\n'
            '                    ->whereBetween(\'longitude\', [$min_lng, $max_lng])\n'
            '                    ->whereRaw("(1.609344 * 3956 * acos( cos( radians(\'$latitude\') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(\'$longitude\') ) + sin( radians(\'$latitude\') ) * sin( radians(latitude) ) ) ) <= $distance");'
        ),
        # Bounding Box show_providers else check
        (
            '                $Providers = Provider::with(\'service\')->whereIn(\'id\', $ActiveProviders)\n'
            '                    ->where(\'status\', \'approved\')\n'
            '                    ->whereRaw("(1.609344 * 3956 * acos( cos( radians(\'$latitude\') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(\'$longitude\') ) + sin( radians(\'$latitude\') ) * sin( radians(latitude) ) ) ) <= $distance")\n'
            '                    ->get();',
            '                $lat_deg = $distance / 111.0;\n'
            '                $lng_deg = $distance / (111.0 * cos(deg2rad($latitude)));\n'
            '                $min_lat = $latitude - $lat_deg;\n'
            '                $max_lat = $latitude + $lat_deg;\n'
            '                $min_lng = $longitude - $lng_deg;\n'
            '                $max_lng = $longitude + $lng_deg;\n\n'
            '                $Providers = Provider::with(\'service\')->whereIn(\'id\', $ActiveProviders)\n'
            '                    ->where(\'status\', \'approved\')\n'
            '                    ->whereBetween(\'latitude\', [$min_lat, $max_lat])\n'
            '                    ->whereBetween(\'longitude\', [$min_lng, $max_lng])\n'
            '                    ->whereRaw("(1.609344 * 3956 * acos( cos( radians(\'$latitude\') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(\'$longitude\') ) + sin( radians(\'$latitude\') ) * sin( radians(latitude) ) ) ) <= $distance")\n'
            '                    ->get();'
        )
    ],
    # 2. UserServiceController.php
    os.path.join(workspace, "app", "Http", "Carbon", "Controllers", "UserServiceController.php"): [], # Wait, let's fix path to app/Http/Controllers/UserServiceController.php
    os.path.join(workspace, "app", "Http", "Controllers", "UserServiceController.php"): [
        # Hospital Bounding Box
        (
            '            $hospitals = Cache::remember($cacheKey, 180, function () use ($latitude, $longitude, $distance) {\n'
            '                return Hospital::selectRaw("\n'
            '                    *,\n'
            '                    ( 6371 * acos(\n'
            '                        cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) )\n'
            '                        + sin( radians(?) ) * sin( radians(latitude) )\n'
            '                    )) AS distance_calculated", [$latitude, $longitude, $latitude])\n'
            '                    ->having(\'distance_calculated\', \'<=\', $distance)\n'
            '                    ->orderBy(\'distance_calculated\', \'asc\')\n'
            '                    ->get();\n'
            '            });',
            '            $lat_deg = $distance / 111.0;\n'
            '            $lng_deg = $distance / (111.0 * cos(deg2rad($latitude)));\n'
            '            $min_lat = $latitude - $lat_deg;\n'
            '            $max_lat = $latitude + $lat_deg;\n'
            '            $min_lng = $longitude - $lng_deg;\n'
            '            $max_lng = $longitude + $lng_deg;\n\n'
            '            $hospitals = Cache::remember($cacheKey, 180, function () use ($latitude, $longitude, $distance, $min_lat, $max_lat, $min_lng, $max_lng) {\n'
            '                return Hospital::selectRaw("\n'
            '                    *,\n'
            '                    ( 6371 * acos(\n'
            '                        cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) )\n'
            '                        + sin( radians(?) ) * sin( radians(latitude) )\n'
            '                    )) AS distance_calculated", [$latitude, $longitude, $latitude])\n'
            '                    ->whereBetween(\'latitude\', [$min_lat, $max_lat])\n'
            '                    ->whereBetween(\'longitude\', [$min_lng, $max_lng])\n'
            '                    ->having(\'distance_calculated\', \'<=\', $distance)\n'
            '                    ->orderBy(\'distance_calculated\', \'asc\')\n'
            '                    ->get();\n'
            '            });'
        ),
        # Nearby Stops Bounding Box
        (
            '            $stops = PdpStop::selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$lat, $lng, $lat])\n'
            '                ->where(\'is_active\', true)\n'
            '                ->having(\'distance\', \'<=\', $radius)\n'
            '                ->orderBy(\'distance\')\n'
            '                ->get();',
            '            // Formule Haversine pour les arrêts proches avec Bounding Box\n'
            '            $lat_deg = $radius / 111.0;\n'
            '            $lng_deg = $radius / (111.0 * cos(deg2rad($lat)));\n'
            '            $min_lat = $lat - $lat_deg;\n'
            '            $max_lat = $lat + $lat_deg;\n'
            '            $min_lng = $lng - $lng_deg;\n'
            '            $max_lng = $lng + $lng_deg;\n\n'
            '            $stops = PdpStop::selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$lat, $lng, $lat])\n'
            '                ->where(\'is_active\', true)\n'
            '                ->whereBetween(\'latitude\', [$min_lat, $max_lat])\n'
            '                ->whereBetween(\'longitude\', [$min_lng, $max_lng])\n'
            '                ->having(\'distance\', \'<=\', $radius)\n'
            '                ->orderBy(\'distance\')\n'
            '                ->get();'
        ),
        # Commune coordinates caching in getServiceTypes
        (
            '                    if ($s_lat && $s_lng && $d_lat && $d_lng) {\n'
            '                        $nearStart = \\App\\PdpStop::whereRaw("(1.609344 * 3956 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ) ) <= 5.0", [$s_lat, $s_lng, $s_lat])\n'
            '                            ->selectRaw("commune, (1.609344 * 3956 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ) ) as distance", [$s_lat, $s_lng, $s_lat])\n'
            '                            ->orderBy(\'distance\')->first();\n'
            '                        $start_commune = $nearStart ? $nearStart->commune : null;\n\n'
            '                        $nearEnd = \\App\\PdpStop::whereRaw("(1.609344 * 3956 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ) ) <= 5.0", [$d_lat, $d_lng, $d_lat])\n'
            '                            ->selectRaw("commune, (1.609344 * 3956 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ) ) as distance", [$d_lat, $d_lng, $d_lat])\n'
            '                            ->orderBy(\'distance\')->first();\n'
            '                        $end_commune = $nearEnd ? $nearEnd->commune : null;',
            '                    if ($s_lat && $s_lng && $d_lat && $d_lng) {\n'
            '                        $cacheKeyStart = \'commune_by_coords:\' . round($s_lat, 3) . \':\' . round($s_lng, 3);\n'
            '                        $start_commune = \\Cache::remember($cacheKeyStart, 300, function() use ($s_lat, $s_lng) {\n'
            '                            $nearStart = \\App\\PdpStop::whereRaw("(1.609344 * 3956 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ) ) <= 5.0", [$s_lat, $s_lng, $s_lat])\n'
            '                                ->selectRaw("commune, (1.609344 * 3956 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ) ) as distance", [$s_lat, $s_lng, $s_lat])\n'
            '                                ->orderBy(\'distance\')->first();\n'
            '                            return $nearStart ? $nearStart->commune : null;\n'
            '                        });\n\n'
            '                        $cacheKeyEnd = \'commune_by_coords:\' . round($d_lat, 3) . \':\' . round($d_lng, 3);\n'
            '                        $end_commune = \\Cache::remember($cacheKeyEnd, 300, function() use ($d_lat, $d_lng) {\n'
            '                            $nearEnd = \\App\\PdpStop::whereRaw("(1.609344 * 3956 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ) ) <= 5.0", [$d_lat, $d_lng, $d_lat])\n'
            '                                ->selectRaw("commune, (1.609344 * 3956 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ) ) as distance", [$d_lat, $d_lng, $d_lat])\n'
            '                                ->orderBy(\'distance\')->first();\n'
            '                            return $nearEnd ? $nearEnd->commune : null;\n'
            '                        });'
        )
    ],
    # 3. ProfileController.php (gps_ping & location rate limiting)
    os.path.join(workspace, "app", "Http", "Controllers", "ProviderResources", "ProfileController.php"): [
        # location rate-limit
        (
            '        if ($Provider = Auth::guard(\'providerapi\')->user()) {\n\n'
            '            $Provider->latitude = $request->latitude;\n'
            '            $Provider->longitude = $request->longitude;\n'
            '            $Provider->save();\n\n'
            '            return response()->json([\'message\' => \'Location Updated successfully!\']);',
            '        if ($Provider = Auth::guard(\'providerapi\')->user()) {\n'
            '            $cacheKey = "provider_location_db_write:{$Provider->id}";\n'
            '            $Provider->latitude = $request->latitude;\n'
            '            $Provider->longitude = $request->longitude;\n'
            '            if (!\\Cache::has($cacheKey)) {\n'
            '                $Provider->save();\n'
            '                \\Cache::put($cacheKey, true, now()->addMinutes(2)); // Write at most every 2 min\n'
            '            }\n\n'
            '            return response()->json([\'message\' => \'Location Updated successfully!\']);'
        ),
        # gps_ping rate-limit
        (
            '        // [V2.3] Synchroniser les dernières coordonnées GPS et calculer le geohash du chauffeur\n'
            '        if (!empty($pings)) {\n'
            '            $lastPing = end($pings);\n'
            '            if (isset($lastPing[\'lat\']) && isset($lastPing[\'lng\'])) {\n'
            '                $provider->latitude  = (float) $lastPing[\'lat\'];\n'
            '                $provider->longitude = (float) $lastPing[\'lng\'];\n'
            '                \n'
            '                // Calculer le geohash de la position courante\n'
            '                /** @var \\App\\Services\\DispatchEngine\\GeoService $geoService */\n'
            '                $geoService = app(\\App\\Services\\DispatchEngine\\GeoService::class);\n'
            '                $provider->geohash = $geoService->encode((float)$lastPing[\'lat\'], (float)$lastPing[\'lng\'], 5);\n'
            '                \n'
            '                $provider->save();\n'
            '            }\n'
            '        }',
            '        // [V2.3] Synchroniser les dernières coordonnées GPS et calculer le geohash du chauffeur\n'
            '        if (!empty($pings)) {\n'
            '            $lastPing = end($pings);\n'
            '            if (isset($lastPing[\'lat\']) && isset($lastPing[\'lng\'])) {\n'
            '                $provider->latitude  = (float) $lastPing[\'lat\'];\n'
            '                $provider->longitude = (float) $lastPing[\'lng\'];\n'
            '                \n'
            '                // Calculer le geohash de la position courante\n'
            '                /** @var \\App\\Services\\DispatchEngine\\GeoService $geoService */\n'
            '                $geoService = app(\\App\\Services\\DispatchEngine\\GeoService::class);\n'
            '                $provider->geohash = $geoService->encode((float)$lastPing[\'lat\'], (float)$lastPing[\'lng\'], 5);\n'
            '                \n'
            '                $cacheKey = "provider_location_db_write:{$provider->id}";\n'
            '                if (!\\Cache::has($cacheKey)) {\n'
            '                    $provider->save();\n'
            '                    \\Cache::put($cacheKey, true, now()->addMinutes(2)); // Write at most every 2 min\n'
            '                }\n'
            '            }\n'
            '        }'
        )
    ],
    # 4. SecureChatController.php (ModerateChatMessageJob dispatch)
    os.path.join(workspace, "app", "Http", "Controllers", "SecureChatController.php"): [
        (
            '        $msg->is_blocked     = $isBlocked;\n'
            '        $msg->is_flagged     = $isFlagged;\n'
            '        $msg->lead_score     = $leadScore;\n'
            '        $msg->ai_used        = $regexAnalysis[\'ai_used\'] ?? false;\n'
            '        $msg->save();\n\n'
            '        // 🔔 Diffusion Temps Réel (WebSockets / Soketi)',
            '        $msg->is_blocked     = $isBlocked;\n'
            '        $msg->is_flagged     = $isFlagged;\n'
            '        $msg->lead_score     = $leadScore;\n'
            '        $msg->ai_used        = $regexAnalysis[\'ai_used\'] ?? false;\n'
            '        $msg->save();\n\n'
            '        // Dispatch background AI moderation if not blocked by synchronous Regex shield\n'
            '        if (!$isBlocked) {\n'
            '            try {\n'
            '                \\App\Jobs\\ModerateChatMessageJob::dispatch(\n'
            '                    $msg->id,\n'
            '                    $rawMessage,\n'
            '                    $userId,\n'
            '                    $recipientId,\n'
            '                    $request->listing_id ?? $request->announcement_id,\n'
            '                    $sender->cancellation_strikes ?? 0\n'
            '                );\n'
            '            } catch (\\Exception $e) {\n'
            '                \\Log::error("Failed to dispatch ModerateChatMessageJob: " . $e->getMessage());\n'
            '            }\n'
            '        }\n\n'
            '        // 🔔 Diffusion Temps Réel (WebSockets / Soketi)'
        )
    ],
    # 5. CustomCommand.php
    os.path.join(workspace, "app", "Console", "Commands", "CustomCommand.php"): [
        (
            '        if(!empty($UserRequest)){\n'
            '            foreach($CustomPush as $Push){',
            '        if(!empty($CustomPush)){\n'
            '            foreach($CustomPush as $Push){'
        )
    ],
    # 6. server.js (KEYS to SCAN)
    os.path.join(workspace, "server.js"): [
        # Insert scanKeys helper
        (
            '// ============================================================\n'
            '// ÉTAT EN MÉMOIRE (Local par Socket)\n'
            '// ============================================================',
            '// Helper function to scan keys non-blockingly instead of KEYS\n'
            'function scanKeys(pattern, callback) {\n'
            '    var keys = [];\n'
            '    var cursor = \'0\';\n'
            '    function scan() {\n'
            '        redisClient.scan(cursor, \'MATCH\', pattern, \'COUNT\', 100, function(err, res) {\n'
            '            if (err) return callback(err);\n'
            '            cursor = res[0];\n'
            '            keys = keys.concat(res[1]);\n'
            '            if (cursor === \'0\') {\n'
            '                callback(null, keys);\n'
            '            } else {\n'
            '                scan();\n'
            '            }\n'
            '        });\n'
            '    }\n'
            '    scan();\n'
            '}\n\n'
            '// ============================================================\n'
            '// ÉTAT EN MÉMOIRE (Local par Socket)\n'
            '// ============================================================'
        ),
        # health SCAN
        (
            'app.get(\'/health\', function(req, res) {\n'
            '    redisClient.keys(\'driver:*\', function(err, keys) {',
            'app.get(\'/health\', function(req, res) {\n'
            '    scanKeys(\'driver:*\', function(err, keys) {'
        ),
        # drivers SCAN
        (
            'app.get(\'/drivers\', function(req, res) {\n'
            '    redisClient.keys(\'driver:*\', function(err, keys) {',
            'app.get(\'/drivers\', function(req, res) {\n'
            '    scanKeys(\'driver:*\', function(err, keys) {'
        )
    ],
    # 7. AddSpatialIndexToProviders.php (PostgreSQL spatial index migration)
    os.path.join(workspace, "database", "migrations", "2026_05_15_185130_add_spatial_index_to_providers.php"): [
        (
            '    private function indexExists(string $table, string $index): bool\n'
            '    {\n'
            '        try {\n'
            '            $indexes = \\DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = \'{$index}\'");\n'
            '            return count($indexes) > 0;\n'
            '        } catch (\\Exception $e) {\n'
            '            return false;\n'
            '        }\n'
            '    }',
            '    private function indexExists(string $table, string $index): bool\n'
            '    {\n'
            '        try {\n'
            '            $driver = \\DB::getDriverName();\n'
            '            if ($driver === \'mysql\') {\n'
            '                $indexes = \\DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = \'{$index}\'");\n'
            '                return count($indexes) > 0;\n'
            '            } elseif ($driver === \'pgsql\') {\n'
            '                $indexes = \\DB::select("SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?", [$table, $index]);\n'
            '                return count($indexes) > 0;\n'
            '            }\n'
            '            return false;\n'
            '        } catch (\\Exception $e) {\n'
            '            return false;\n'
            '        }\n'
            '    }'
        )
    ]
}

# Application des remplacements
for filepath, changes in replacements.items():
    if not os.path.exists(filepath):
        print(f"FICHIER INEXISTANT: {filepath}")
        continue
    
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    modified = False
    for target, replacement in changes:
        # Normaliser les retours à la ligne pour Windows
        target_norm = target.replace('\r\n', '\n').replace('\r', '\n')
        replacement_norm = replacement.replace('\r\n', '\n').replace('\r', '\n')
        content_norm = content.replace('\r\n', '\n').replace('\r', '\n')
        
        if target_norm in content_norm:
            content_norm = content_norm.replace(target_norm, replacement_norm)
            content = content_norm.replace('\n', os.linesep)
            modified = True
            print(f"SUCCESS: Modifié dans {os.path.basename(filepath)}")
        else:
            print(f"ERROR: Cible non trouvée dans {os.path.basename(filepath)}")
            # print("CIBLE:")
            # print(repr(target_norm[:100]))
            
    if modified:
        with open(filepath, 'w', encoding='utf-8', newline='') as f:
            f.write(content)
