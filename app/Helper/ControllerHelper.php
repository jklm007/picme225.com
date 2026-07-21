<?php

namespace App\Helpers;

use File;
use Setting;
use Illuminate\Support\Facades\Mail;

class Helper
{

    public static function upload_picture($picture)
    {
        $file_name = time();
        $file_name .= rand();
        $file_name = sha1($file_name);
        if ($picture) {
            $ext = $picture->getClientOriginalExtension();
            $path = 'uploads/' . $file_name . "." . $ext;
            
            $disk = env('FILESYSTEM_DISK', config('filesystems.default', 's3'));
            try {
                \Illuminate\Support\Facades\Storage::disk($disk)->put($path, file_get_contents($picture), 'public');
                return $path;
            } catch (\Exception $e) {
                \Log::error('Cloud Storage Upload Failed: ' . $e->getMessage());
            }
        }
        return "";
    }

    public static function delete_picture($picture)
    {
        if (empty($picture)) {
            return true;
        }

        $disk = env('FILESYSTEM_DISK', config('filesystems.default', 's3'));
        
        try {
            // Si c'est un lien complet
            if (strpos($picture, 'http') !== false) {
                $parsedUrl = parse_url($picture);
                if (isset($parsedUrl['path'])) {
                    $path = ltrim($parsedUrl['path'], '/');
                    if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($path)) {
                        \Illuminate\Support\Facades\Storage::disk($disk)->delete($path);
                    }
                }
            } else {
                if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($picture)) {
                    \Illuminate\Support\Facades\Storage::disk($disk)->delete($picture);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Cloud Storage Delete Failed: ' . $e->getMessage());
        }
        
        return true;
    }

    public static function generate_booking_id()
    {
        return Setting::get('booking_prefix') . mt_rand(100000, 999999);
    }

    public static function site_sendmail($user)
    {

        $site_details = Setting::all();



        Mail::send('emails.invoice', ['user' => $user, 'site_details' => $site_details], function ($mail) use ($user, $site_details) {
            // $mail->from('harapriya@appoets.com', 'Your Application');

            $mail->to($user->user->email, $user->user->first_name . ' ' . $user->user->last_name)->subject('Invoice');
        });

        return true;
    }

    public static function getBearing(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $lngDelta = deg2rad($lng2 - $lng1);
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);

        $y = sin($lngDelta) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($lngDelta);

        $bearing = rad2deg(atan2($y, $x));
        return fmod(($bearing + 360), 360);
    }

    public static function haversineGreatCircleDistance(
        float $latitudeFrom,
        float $longitudeFrom,
        float $latitudeTo,
        float $longitudeTo
    ): float {
        $earthRadius = 6371000;

        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    public static function secondsToWords($seconds)
    {
        if ($seconds == 0)
            return "Immédit";

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        $parts = [];
        if ($hours > 0)
            $parts[] = $hours . "h";
        if ($minutes > 0)
            $parts[] = $minutes . "min";
        if (empty($parts))
            $parts[] = $remainingSeconds . "s";

        return implode(' ', $parts);
    }

    public static function formatMoney($value)
    {
        return number_format($value, 0, ',', ' ') . ' ' . Setting::get('currency', 'CFA');
    }
}
