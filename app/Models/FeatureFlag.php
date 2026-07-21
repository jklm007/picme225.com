<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FeatureFlag extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'feature_flags';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'label',
        'description',
        'is_enabled',
        'service_id',
        'zone',
        'activation_conditions',
        'category',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_enabled'            => 'boolean',
        'activation_conditions' => 'array',
        'service_id'            => 'integer',
        'updated_by'            => 'integer',
    ];

    /**
     * Generate a consistent cache key for a given flag key and optional service ID.
     *
     * @param  string   $key
     * @param  int|null $serviceId
     * @return string
     */
    public static function cacheKey(string $key, ?int $serviceId = null): string
    {
        return 'feature_flag:' . $key . ($serviceId !== null ? ':service_' . $serviceId : '');
    }

    /**
     * Check whether a feature flag is enabled, with a 60-second cache.
     *
     * @param  string   $key
     * @param  int|null $serviceId
     * @return bool
     */
    public static function isEnabled(string $key, ?int $serviceId = null): bool
    {
        $cacheKey = static::cacheKey($key, $serviceId);

        return (bool) Cache::remember($cacheKey, 60, function () use ($key, $serviceId) {
            $query = static::where('key', $key);

            if ($serviceId !== null) {
                $query->where('service_id', $serviceId);
            }

            $flag = $query->first();

            return $flag ? $flag->is_enabled : false;
        });
    }
}
