<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon\Carbon;
use App\Http\Controllers\SendPushNotification;
use App\Models\AppNotification;
use App\Models\Provider;
use App\Models\UserRequests;
use Illuminate\Support\Facades\Http;
use Log;

class ScheduledRideReminderCommand extends Command
{
    protected $signature   = 'rides:remind-scheduled';
    protected $description = 'Rappels progressifs FCM (30/15/5 min) + AppNotification Centre Activites, et reassignation (Fallback T-30).';

    const WINDOW_SEC = 55;
    const OSRM_BASE = 'http://router.project-osrm.org/route/v1/driving';

    public function handle(): void
    {
        $now = Carbon::now();

        $rides = DB::table('user_requests')
            ->whereNotNull('provider_id')
            ->where('status', 'SCHEDULED')
            ->whereNotNull('schedule_at')
            ->get();

        if ($rides->isEmpty()) return;

        $pushController = new SendPushNotification();

        foreach ($rides as $ride) {
            $scheduleAt  = Carbon::parse($ride->schedule_at);
            $secondsLeft = $now->diffInSeconds($scheduleAt, false);

            if ($secondsLeft < 0) continue;

            $checkpoint  = null;
            $minutesLeft = null;

            if (abs($secondsLeft - 1800) <= self::WINDOW_SEC) {
                $checkpoint  = '30min';
                $minutesLeft = 30;
            } elseif (abs($secondsLeft - 900) <= self::WINDOW_SEC) {
                $checkpoint  = '15min';
                $minutesLeft = 15;
            } elseif (abs($secondsLeft - 300) <= self::WINDOW_SEC) {
                $checkpoint  = '5min';
                $minutesLeft = 5;
            }

            if (!$checkpoint) continue;

            Log::info("[ScheduledReminder] Ride #{$ride->id} checkpoint={$checkpoint} provider={$ride->provider_id}");

            $provider = Provider::find($ride->provider_id);

            if (!$provider || !$provider->latitude || !$provider->longitude) {
                if ($minutesLeft === 30) {
                    $this->reassignRideToNearest($ride, $provider, $pushController, 9999, $secondsLeft);
                } else {
                    $pushController->ScheduledRideReminder(
                        $ride->provider_id, $minutesLeft, $ride->id,
                        $ride->pick_address ?? 'Point de depart',
                        $scheduleAt->toIso8601String()
                    );
                    // Persister rappel dans le Centre d'Activites
                    $this->persistReminderNotification($ride, $scheduleAt, $minutesLeft);
                }
                continue;
            }

            $eta = $this->getOsrmEta(
                $provider->latitude, $provider->longitude,
                $ride->s_latitude, $ride->s_longitude
            );

            if ($eta === null) {
                $pushController->ScheduledRideReminder(
                    $ride->provider_id, $minutesLeft, $ride->id,
                    $ride->pick_address ?? 'Point de depart',
                    $scheduleAt->toIso8601String()
                );
                $this->persistReminderNotification($ride, $scheduleAt, $minutesLeft);
                continue;
            }

            // T-30 Fallback : trop loin pour garantir la prise en charge
            if ($checkpoint === '30min' && $eta > 1500) {
                $this->reassignRideToNearest($ride, $provider, $pushController, $eta, $secondsLeft);
            } else {
                $pushController->ScheduledRideReminder(
                    $ride->provider_id, $minutesLeft, $ride->id,
                    $ride->pick_address ?? 'Point de depart',
                    $scheduleAt->toIso8601String()
                );
                // Persister rappel dans le Centre d'Activites
                $this->persistReminderNotification($ride, $scheduleAt, $minutesLeft);
            }
        }
    }

    /**
     * Persiste le rappel dans la table app_notifications (Centre d'Activites du chauffeur).
     */
    private function persistReminderNotification($ride, Carbon $scheduleAt, int $minutesLeft): void
    {
        $provider = Provider::find($ride->provider_id);
        if (!$provider) return;

        AppNotification::send(
            $provider,
            'Rappel course dans ' . $minutesLeft . ' min',
            'Votre course vers ' . ($ride->d_address ?? 'destination') . ' est prevue a ' . $scheduleAt->format('H:i') . '. Partez des maintenant !',
            'TRIP_REMINDER',
            (string) $ride->id,
            'TRIP'
        );
    }

    private function getOsrmEta(float $driverLat, float $driverLng, float $pickupLat, float $pickupLng): ?int
    {
        try {
            $url = sprintf('%s/%s,%s;%s,%s?overview=false',
                self::OSRM_BASE, $driverLng, $driverLat, $pickupLng, $pickupLat);
            $response = Http::timeout(5)->get($url);
            if (!$response->ok()) return null;
            $body = $response->json();
            return !empty($body['routes']) ? (int) ceil($body['routes'][0]['duration'] ?? 0) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function reassignRideToNearest($ride, $provider, SendPushNotification $push, int $eta, int $secondsLeft): void
    {
        Log::info("[ScheduledReminder] FALLBACK T-30 active pour ride #{$ride->id}. ETA={$eta}s.");

        if ($provider) {
            $push->sendPushToProvider(
                $ride->provider_id,
                "Course #{$ride->id} retiree : Vous etiez trop loin pour garantir la prise en charge."
            );
            // Notifier dans le Centre d'Activites
            AppNotification::send(
                $provider,
                'Course retiree - Reassignation',
                "La course #{$ride->id} vous a ete retiree car vous etiez trop loin du point de depart. Un autre chauffeur a ete contacte.",
                'TRIP_CANCELLED',
                (string) $ride->id,
                'TRIP'
            );
            DB::table('providers')->where('id', $ride->provider_id)->update(['status' => 'approved']);
            DB::table('request_filters')->where('request_id', $ride->id)->where('provider_id', $ride->provider_id)->delete();
        }

        $nearestProvider = Provider::where('status', 'approved')
            ->where('is_online', 1)
            ->where('is_available', 1)
            ->whereHas('service', function ($query) use ($ride) {
                $query->where('service_type_id', $ride->service_type_id);
            })
            ->select(DB::raw("*, (6371 * acos(cos(radians($ride->s_latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians($ride->s_longitude)) + sin(radians($ride->s_latitude)) * sin(radians(latitude)))) AS distance"))
            ->having('distance', '<', 5)
            ->orderBy('distance', 'asc')
            ->first();

        if ($nearestProvider) {
            DB::table('user_requests')->where('id', $ride->id)->update([
                'provider_id'         => $nearestProvider->id,
                'current_provider_id' => $nearestProvider->id,
            ]);
            $push->sendPushToProvider(
                $nearestProvider->id,
                "URGENT : Course #{$ride->id} assignee en mode SOS. Vous etes le plus proche !"
            );
            // Notifier le nouveau chauffeur dans son Centre d'Activites
            AppNotification::send(
                $nearestProvider,
                'Course SOS assignee',
                "La course #{$ride->id} vous a ete assignee en urgence. Depart prevu a " . Carbon::parse($ride->schedule_at)->format('H:i') . ".",
                'SCHEDULED_TRIP',
                (string) $ride->id,
                'TRIP'
            );
            Log::info("[ScheduledReminder] SOS Assignation reussie : Provider #{$nearestProvider->id} prend la course #{$ride->id}.");

            $push->sendPushToUser(
                $ride->user_id,
                "Ajustement de derniere minute : Votre chauffeur habituel etant trop eloigne, nous avons reassigne votre trajet au vehicule le plus proche."
            );
        } else {
            DB::table('user_requests')->where('id', $ride->id)->update([
                'provider_id'         => null,
                'current_provider_id' => null,
                'status'              => 'SEARCHING',
                'assigned_at'         => null,
            ]);
            Log::warning("[ScheduledReminder] SOS Echec : Aucun chauffeur proche trouve pour course #{$ride->id}.");
            Log::warning("[DISPATCHER_ALERT] Echec reassignation automatique (SOS T-30) pour course #{$ride->id} : aucun chauffeur dispo.");

            $push->sendPushToUser(
                $ride->user_id,
                "Le chauffeur de votre course planifiée n'est plus disponible. Nous recherchons activement un remplaçant."
            );
        }
    }
}
