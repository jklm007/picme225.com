<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'image', 'status'];

    /**
     * Antigravity: Append full image_url to JSON output.
     */
    protected $appends = ['image_url'];

    /**
     * Antigravity: Accessor — returns the full public URL for the image field.
     */
    public function getImageUrlAttribute()
    {
        $image = $this->image;

        // No image — return a CDN placeholder
        if (empty($image)) {
            return asset('images/default_category.png');
        }

        // Already a full URL (R2, Cloudflare, CDN, etc.) — return as-is
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            // Strip localhost/127 URLs to relative path
            $parsed = parse_url($image);
            if (isset($parsed['host']) && ($parsed['host'] === '127.0.0.1' || $parsed['host'] === 'localhost')) {
                $image = ltrim($parsed['path'], '/');
            } else {
                return $image; // External / R2 URL — return directly
            }
        }

        $path = ltrim($image, '/');

        // Known prefixes that include the folder already
        if (str_starts_with($path, 'storage/') || str_starts_with($path, 'uploads/')) {
            return url($path);
        }

        // Relative path like "service/taxi.png" — prepend storage/ or use img()
        return img($path);
    }

    // Relation many-to-many avec ServiceType via la table pivot
    public function serviceTypes()
    {
        return $this->belongsToMany(ServiceType::class, 'service_service_type', 'service_id', 'service_type_id')
                    ->withPivot('fixed', 'price', 'minute', 'hour', 'distance', 'day', 'calculator', 'description', 'status', 'ambulance', 'rental_amount', 'outstation_price');
    }
}





