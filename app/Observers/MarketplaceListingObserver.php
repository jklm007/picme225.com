<?php

namespace App\Observers;

use App\Models\MarketplaceListing;
use App\Models\MktRealEstate;
use App\Models\MktVehicle;
use App\Models\MktLogistic;
use App\Models\MktEvent;
use App\Models\MktService;
use App\Models\MktProduct;
use Illuminate\Support\Facades\DB;

class MarketplaceListingObserver
{
    /**
     * Handle the MarketplaceListing "saving" event.
     * Extract virtual attributes to prevent SQL errors when they are dropped from DB.
     */
    public function saving(MarketplaceListing $listing)
    {
        $virtualKeys = [
            'brand', 'model', 'year', 'color', 'plate_number', 'with_driver', 'driver_price', 'driving_policy',
            'location_city', 'location_latitude', 'location_longitude', 'price_unit',
            'stock_quantity', 'home_delivery', 'delivery_price', 'is_digital', 'digital_file_path', 'pdp_route_id'
        ];
        
        $listing->_virtual_attributes = $listing->_virtual_attributes ?? [];
        
        // Define which attributes are NOT supported by the target polymorphic table and should go to metadata
        $cat = strtoupper((string) $listing->category);
        $metadataKeys = [];
        if (strpos($cat, 'REAL_ESTATE') === 0 || strpos($cat, 'IMMOBILIER') !== false) {
            $metadataKeys = ['brand', 'model', 'year', 'color', 'plate_number', 'with_driver', 'driver_price', 'driving_policy', 'stock_quantity', 'home_delivery', 'delivery_price', 'is_digital', 'digital_file_path', 'pdp_route_id'];
        } elseif (strpos($cat, 'VEHICLES') === 0 || strpos($cat, 'VÉHICULE') !== false || strpos($cat, 'VEHICULE') !== false) {
            $metadataKeys = ['location_city', 'location_latitude', 'location_longitude', 'price_unit', 'stock_quantity', 'home_delivery', 'delivery_price', 'is_digital', 'digital_file_path', 'pdp_route_id'];
        } elseif (strpos($cat, 'TICKETS') === 0 || strpos($cat, 'EVENT') !== false || strpos($cat, 'BILLET') !== false) {
            $metadataKeys = ['brand', 'model', 'year', 'color', 'plate_number', 'with_driver', 'driver_price', 'driving_policy', 'location_city', 'location_latitude', 'location_longitude', 'price_unit', 'stock_quantity', 'home_delivery', 'delivery_price', 'is_digital', 'digital_file_path', 'pdp_route_id'];
        } elseif (strpos($cat, 'SERVICES') === 0) {
            $metadataKeys = ['brand', 'model', 'year', 'color', 'plate_number', 'with_driver', 'driver_price', 'driving_policy', 'location_city', 'location_latitude', 'location_longitude', 'stock_quantity', 'home_delivery', 'delivery_price', 'is_digital', 'digital_file_path', 'pdp_route_id'];
        } elseif (strpos($cat, 'CONVOY') === 0) {
            $metadataKeys = ['brand', 'model', 'year', 'color', 'plate_number', 'with_driver', 'driver_price', 'driving_policy', 'location_city', 'location_latitude', 'location_longitude', 'price_unit', 'stock_quantity', 'home_delivery', 'delivery_price', 'is_digital', 'digital_file_path'];
        } else {
            // Product by default
            $metadataKeys = ['brand', 'model', 'year', 'color', 'plate_number', 'with_driver', 'driver_price', 'driving_policy', 'location_city', 'location_latitude', 'location_longitude', 'price_unit', 'pdp_route_id'];
        }

        $metadata = $listing->metadata ?? [];
        
        foreach ($virtualKeys as $key) {
            if (array_key_exists($key, $listing->getAttributes())) {
                $val = $listing->getAttributes()[$key];
                $listing->_virtual_attributes[$key] = $val;
                
                if (in_array($key, $metadataKeys) && !is_null($val)) {
                    $metadata[$key] = $val;
                }
                
                unset($listing->$key); // Remove from Eloquent attributes to prevent DB insert/update
            }
        }
        
        $listing->metadata = $metadata;
    }

    /**
     * Handle the MarketplaceListing "saved" event.
     * We use 'saved' to catch both creates and updates, keeping the shadowed table in sync.
     */
    public function saved(MarketplaceListing $listing)
    {
        $cat = strtoupper((string) $listing->category);
        $listable = null;

        $getAttr = function($key) use ($listing) {
            if (isset($listing->_virtual_attributes) && array_key_exists($key, $listing->_virtual_attributes)) {
                return $listing->_virtual_attributes[$key];
            }
            return $listing->$key ?? null;
        };

        $savePolymorphic = function($modelClass, $attributes) use ($listing) {
            \Illuminate\Support\Facades\Log::info("savePolymorphic", [
                'modelClass'   => $modelClass,
                'listing_id'   => $listing->id,
                'listable_id'  => $listing->listable_id,
                'wasRecently'  => $listing->wasRecentlyCreated,
            ]);

            // Only re-use the existing sub-record when we are UPDATING an already-saved listing.
            // For brand-new listings always create a fresh sub-record to avoid overwriting
            // the sub-records that belong to a different, older listing.
            if (!$listing->wasRecentlyCreated && $listing->listable_id) {
                \Illuminate\Support\Facades\Log::info("savePolymorphic: updateOrCreate (existing listing)");
                return $modelClass::updateOrCreate(['id' => $listing->listable_id], $attributes);
            } else {
                \Illuminate\Support\Facades\Log::info("savePolymorphic: create (new listing)");
                return $modelClass::create($attributes);
            }
        };

        if (strpos($cat, 'REAL_ESTATE') === 0 || strpos($cat, 'IMMOBILIER') !== false) {
            $listable = $savePolymorphic(MktRealEstate::class, [
                'location_city' => $getAttr('location_city'),
                'location_latitude' => $getAttr('location_latitude'),
                'location_longitude' => $getAttr('location_longitude'),
                'price_unit' => $getAttr('price_unit'),
            ]);
        } elseif (strpos($cat, 'VEHICLES') === 0 || strpos($cat, 'VÉHICULE') !== false || strpos($cat, 'VEHICULE') !== false) {
            $listable = $savePolymorphic(MktVehicle::class, [
                'brand' => $getAttr('brand'),
                'model' => $getAttr('model'),
                'year' => $getAttr('year'),
                'color' => $getAttr('color'),
                'plate_number' => $getAttr('plate_number'),
                'with_driver' => $getAttr('with_driver') ?? false,
                'driver_price' => $getAttr('driver_price'),
                'driving_policy' => $getAttr('driving_policy'),
            ]);
        } elseif (strpos($cat, 'TICKETS') === 0 || strpos($cat, 'EVENT') !== false || strpos($cat, 'BILLET') !== false) {
            $listable = $savePolymorphic(MktEvent::class, []);
        } elseif (strpos($cat, 'SERVICES') === 0) {
            $listable = $savePolymorphic(MktService::class, [
                'price_unit' => $getAttr('price_unit')
            ]);
        } elseif (strpos($cat, 'CONVOY') === 0) {
            $listable = $savePolymorphic(MktLogistic::class, [
                'pdp_route_id' => $getAttr('pdp_route_id')
            ]);
        } else {
            // Product by default (SALE, ELECTRONICS, FASHION, FOOD, etc)
            $listable = $savePolymorphic(MktProduct::class, [
                'stock_quantity' => $getAttr('stock_quantity') ?? 1,
                'home_delivery' => $getAttr('home_delivery') ?? false,
                'delivery_price' => $getAttr('delivery_price'),
                'is_digital' => $getAttr('is_digital') ?? false,
                'digital_file_path' => $getAttr('digital_file_path'),
            ]);
        }

        // Update polymorphic link in the parent table silently without triggering loop
        if ($listable && ($listing->listable_id !== $listable->id || $listing->listable_type !== get_class($listable))) {
            DB::table('marketplace_listings')
                ->where('id', $listing->id)
                ->update([
                    'listable_id' => $listable->id,
                    'listable_type' => get_class($listable)
                ]);
        }
    }
}
