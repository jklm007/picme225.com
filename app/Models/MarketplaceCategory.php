<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketplaceCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['total_listings_count', 'image_url'];

    public function children()
    {
        return $this->hasMany(MarketplaceCategory::class, 'parent_id')->orderBy('order_index');
    }

    public function parent()
    {
        return $this->belongsTo(MarketplaceCategory::class, 'parent_id');
    }

    public function listings()
    {
        return $this->hasMany(\App\Models\MarketplaceListing::class, 'category', 'name')->where('status', 'ACTIVE');
    }

    /**
     * Nombre total d'annonces : propres + celles des sous-catégories.
     */
    public function getTotalListingsCountAttribute(): int
    {
        $own = \App\Models\MarketplaceListing::where('category', $this->name)
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->count();

        // Ajouter les annonces des sous-catégories
        $childNames = self::where('parent_id', $this->id)->pluck('name')->toArray();
        if (!empty($childNames)) {
            $own += \App\Models\MarketplaceListing::whereIn('category', $childNames)
                ->where('status', 'ACTIVE')
                ->whereNull('deleted_at')
                ->count();
        }

        return $own;
    }

    /**
     * Returns the full public URL for the category image.
     * Handles S3/CDN URLs, local storage paths, and missing images gracefully.
     */
    public function getImageUrlAttribute(): ?string
    {
        $image = $this->image ?? null;

        if (empty($image)) {
            return null;
        }

        // Already a full URL (S3, R2, CDN) — return as-is
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            $parsed = parse_url($image);
            if (isset($parsed['host']) && in_array($parsed['host'], ['127.0.0.1', 'localhost'])) {
                $image = ltrim($parsed['path'], '/');
            } else {
                return $image;
            }
        }

        $path = ltrim($image, '/');

        if (str_starts_with($path, 'storage/') || str_starts_with($path, 'uploads/')) {
            return url($path);
        }

        return img($path);
    }
}
