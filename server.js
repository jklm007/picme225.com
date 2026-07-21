/**
 * PicMe PRO - Serveur Temps Réel Intelligent (Redis & PM2 Cluster Ready)
 * ============================================
 * Version : 3.0 (Cluster, Redis, Anti-Fake GPS)
 * Compatible : socket.io 1.7.x, Node.js 10+, PM2 Cluster Mode
 *
 * NOUVELLES FONCTIONNALITÉS :
 * - Architecture Clusterisée : Utilisation de socket.io-redis pour synchroniser les web sockets entre les cœurs CPU
 * - Mémoire Externalisée : Les chauffeurs sont stockés dans ioredis au lieu de la mémoire RAM
 * - Résilience PM2 : Le redémarrage d'un worker n'impacte pas l'état global du système
 * - Anti-Fake GPS et Rate-Limiting conservés.
 */

var app    = require('express')();
var server = require('http').Server(app);
var io     = require('socket.io')(server);
var Redis  = require('ioredis');
var socketRedis = require('socket.io-redis');

var debug  = require('debug')('PicMe:RealTime');

var port = process.env.PORT || '3000';
var redisHost = process.env.REDIS_HOST || '127.0.0.1';
var redisPort = process.env.REDIS_PORT || 6379;

// ============================================================
// CONFIGURATION REDIS & SOCKET.IO
// ============================================================

// Adapters pour la synchronisation multi-coeurs via Pub/Sub Redis
var pubClient = new Redis({ host: redisHost, port: redisPort });
var subClient = new Redis({ host: redisHost, port: redisPort });
io.adapter(socketRedis({ pubClient: pubClient, subClient: subClient }));

// Client Redis standard pour le stockage (KV)
var redisClient = new Redis({ host: redisHost, port: redisPort });

redisClient.on('connect', function() {
    console.log('[Redis] Connecté à ' + redisHost + ':' + redisPort);
});

server.listen(port, function() {
    console.log('[PicMe] Serveur temps réel (Cluster) démarré sur le port ' + port);
});

// Helper function to scan keys non-blockingly instead of KEYS
function scanKeys(pattern, callback) {
    var keys = [];
    var cursor = '0';
    function scan() {
        redisClient.scan(cursor, 'MATCH', pattern, 'COUNT', 100, function(err, res) {
            if (err) return callback(err);
            cursor = res[0];
            keys = keys.concat(res[1]);
            if (cursor === '0') {
                callback(null, keys);
            } else {
                scan();
            }
        });
    }
    scan();
}

// ============================================================
// ÉTAT EN MÉMOIRE (Local par Socket)
// ============================================================

/**
 * Rate limiter anti-spam (reste en mémoire car lié au socket TCP local)
 * clé   : socket.id
 * valeur: { count: int, resetAt: timestamp }
 */
var rateLimiter = new Map();

// Limites anti-spam
var RATE_LIMIT_MAX      = 30;  // max événements par fenêtre
var RATE_LIMIT_WINDOW   = 10000; // fenêtre en ms (10 secondes)
var GPS_MAX_SPEED_KMH   = 300; // vitesse max acceptable (Fake GPS)

// ============================================================
// UTILITAIRES
// ============================================================

/**
 * Calcul Haversine (distance GPS)
 */
function haversine(lat1, lng1, lat2, lng2) {
    var R = 6371;
    var dLat = (lat2 - lat1) * Math.PI / 180;
    var dLng = (lng2 - lng1) * Math.PI / 180;
    var a = Math.sin(dLat/2) * Math.sin(dLat/2)
          + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180)
          * Math.sin(dLng/2) * Math.sin(dLng/2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

/**
 * Génère un Geohash simple (5 chars ≈ 4km²).
 */
function geohash(lat, lng, precision) {
    precision = precision || 5;
    var BASE32 = '0123456789bcdefghjkmnpqrstuvwxyz';
    var isEven = true, hash = '', bits = 0, bitsTotal = 0, hashVal = 0;
    var minLat = -90, maxLat = 90, minLng = -180, maxLng = 180, mid;
    while (hash.length < precision) {
        if (isEven) {
            mid = (minLng + maxLng) / 2;
            if (lng >= mid) { hashVal = (hashVal << 1) + 1; minLng = mid; }
            else            { hashVal = (hashVal << 1) + 0; maxLng = mid; }
        } else {
            mid = (minLat + maxLat) / 2;
            if (lat >= mid) { hashVal = (hashVal << 1) + 1; minLat = mid; }
            else            { hashVal = (hashVal << 1) + 0; maxLat = mid; }
        }
        isEven = !isEven;
        if (++bits === 5) { hash += BASE32[hashVal]; bits = 0; hashVal = 0; }
    }
    return hash;
}

/**
 * Rate limiter local.
 */
function isRateLimited(socketId) {
    var now = Date.now();
    var state = rateLimiter.get(socketId) || { count: 0, resetAt: now + RATE_LIMIT_WINDOW };
    if (now > state.resetAt) {
        state = { count: 0, resetAt: now + RATE_LIMIT_WINDOW };
    }
    state.count++;
    rateLimiter.set(socketId, state);
    return state.count > RATE_LIMIT_MAX;
}

/**
 * Vérifie si une mise à jour GPS est plausible.
 */
function isFakeGPS(driverState, newLat, newLng) {
    if (!driverState || !driverState.lastLat) return false;
    var elapsed = (Date.now() - driverState.lastUpdate) / 1000;
    if (elapsed <= 0) return false;
    var dist    = haversine(driverState.lastLat, driverState.lastLng, newLat, newLng);
    var speedKmh = (dist / elapsed) * 3600;
    return speedKmh > GPS_MAX_SPEED_KMH;
}

// ============================================================
// ENDPOINT API EXPRESS
// ============================================================

app.get('/health', function(req, res) {
    scanKeys('driver:*', function(err, keys) {
        res.json({
            status: 'ok',
            drivers_online: keys ? keys.length : 0,
            uptime_seconds: Math.round(process.uptime()),
            redis_status: pubClient.status
        });
    });
});

app.get('/drivers', function(req, res) {
    scanKeys('driver:*', function(err, keys) {
        if (!keys || keys.length === 0) return res.json({ count: 0, drivers: [] });
        redisClient.mget(keys, function(err, results) {
            var list = [];
            if (results) {
                results.forEach(function(val) {
                    if (val) list.push(JSON.parse(val));
                });
            }
            res.json({ count: list.length, drivers: list });
        });
    });
});

/**
 * POST /dispatch - Dispatch ciblé
 * Diffuse sur le cluster via io.to('driver_X') grâce à socket.io-redis
 */
app.use(require('express').json());
app.post('/dispatch', function(req, res) {
    var body        = req.body || {};
    var providerIds = body.provider_ids || [];
    var tripData    = body.request || {};
    var notified    = [];
    var offline     = [];

    if (!providerIds.length) {
        return res.json({ notified_via_socket: [], need_firebase_push: [] });
    }

    var redisKeys = providerIds.map(function(pid) { return 'driver:' + pid; });
    
    redisClient.mget(redisKeys, function(err, results) {
        if (err || !results) {
            return res.json({ error: 'Redis error' });
        }
        
        providerIds.forEach(function(pid, index) {
            var driverData = results[index];
            if (driverData) {
                // Le driver existe en Redis, il est donc en ligne
                io.to('driver_' + pid).emit('new_trip_request', tripData);
                notified.push(pid);
                console.log('[Dispatch] Course #' + tripData.id + ' → Driver #' + pid + ' (via Cluster)');
            } else {
                offline.push(pid);
                console.log('[Dispatch] Driver #' + pid + ' hors ligne → Firebase fallback nécessaire');
            }
        });

        res.json({
            notified_via_socket: notified,
            need_firebase_push:  offline
        });
    });
});

// ============================================================
// EVENTS SOCKET.IO
// ============================================================

io.on('connection', function(socket) {
    var requestId  = 'unassigned';
    var providerId = null;
    var currentGeohash = null;

    console.log('[Socket] Nouvelle connexion: ' + socket.id);
    socket.emit('connected', { status: 'ok', message: 'Connexion PicMe établie !' });

    socket.on('update sender', function(data) {
        if (isRateLimited(socket.id)) return;
        requestId = data.request_id;
        socket.join(requestId);
        socket.emit('sender updated', 'Sender Updated ID:' + requestId);
        console.log('[Tracking] Socket joint room: ' + requestId);
    });

    socket.on('driver_online', function(data) {
        if (isRateLimited(socket.id)) {
            socket.emit('error', { code: 429, message: 'Trop de requêtes, ralentissez.' });
            return;
        }
        if (!data || !data.provider_id || !data.latitude || !data.longitude) return;

        providerId = String(data.provider_id);
        var hash   = geohash(data.latitude, data.longitude);
        currentGeohash = hash;

        var geoRoom = 'geo_' + hash;
        socket.join(geoRoom);
        var personalRoom = 'driver_' + providerId;
        socket.join(personalRoom);

        var state = {
            provider_id: providerId,
            socketId:    socket.id,
            lat:         data.latitude,
            lng:         data.longitude,
            geohash:     hash,
            lastUpdate:  Date.now(),
            lastLat:     data.latitude,
            lastLng:     data.longitude
        };

        // Sauvegarde dans Redis avec expiration 10 minutes (600s)
        redisClient.set('driver:' + providerId, JSON.stringify(state), 'EX', 600);

        socket.emit('driver_registered', { geohash: hash, room: geoRoom });
        console.log('[Driver] #' + providerId + ' en ligne → Room ' + geoRoom);
    });

    socket.on('update location', function(data) {
        if (isRateLimited(socket.id)) return;
        if (!data || !data.latitude || !data.longitude) return;

        data.timestamp = new Date();

        if (providerId) {
            redisClient.get('driver:' + providerId, function(err, val) {
                if (val) {
                    var driverState = JSON.parse(val);

                    if (isFakeGPS(driverState, data.latitude, data.longitude)) {
                        console.warn('[SECURITY] Fake GPS détecté pour Driver #' + providerId);
                        socket.emit('location_rejected', { reason: 'FAKE_GPS_DETECTED' });
                        return;
                    }

                    var newHash = geohash(data.latitude, data.longitude);

                    if (newHash !== driverState.geohash) {
                        socket.leave('geo_' + driverState.geohash);
                        socket.join('geo_' + newHash);
                        currentGeohash = newHash;
                    }

                    var newState = {
                        provider_id: providerId,
                        socketId:    socket.id,
                        lat:         data.latitude,
                        lng:         data.longitude,
                        geohash:     newHash,
                        lastUpdate:  Date.now(),
                        lastLat:     driverState.lat,
                        lastLng:     driverState.lng
                    };

                    redisClient.set('driver:' + providerId, JSON.stringify(newState), 'EX', 600);
                }
            });
        }

        socket.broadcast.to(requestId).emit('location update', data);
    });

    socket.on('send message', function(data) {
        if (isRateLimited(socket.id)) return;
        if (!data) return;
        var receiver = (data.type === 'up')
            ? 'pu' + data.provider_id
            : 'up' + data.user_id;
        socket.broadcast.to(receiver).emit('message', data);
    });

    socket.on('trip_status_update', function(data) {
        if (isRateLimited(socket.id)) return;
        if (!data || !data.request_id) return;
        io.to(String(data.request_id)).emit('trip_status_changed', data);
    });

    socket.on('ping', function() {
        socket.emit('pong', { ts: Date.now() });
        // Rafraîchir l'expiration en Redis
        if (providerId) {
            redisClient.expire('driver:' + providerId, 600);
        }
    });

    socket.on('disconnect', function(reason) {
        if (providerId) {
            redisClient.del('driver:' + providerId);
            console.log('[Driver] #' + providerId + ' hors ligne (raison: ' + reason + ')');
        }
        rateLimiter.delete(socket.id);
        console.log('[Socket] Déconnexion: ' + socket.id + ' (' + reason + ')');
    });
});