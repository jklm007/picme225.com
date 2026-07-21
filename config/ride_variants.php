<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ride Variant Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for different ride variants including pricing discounts,
    | constraints, and validation rules.
    |
    */

    'variants' => [
        'prive' => [
            'name' => 'Private',
            'discount_percentage' => 0,
            'description' => 'Direct ride, no sharing',
        ],
        'dynamique' => [
            'name' => 'Dynamic',
            'discount_percentage' => 15,
            'description' => 'Allows detours for ride-sharing',
            'constraints' => [
                'max_detour_distance_km' => 3.0,      // Maximum additional distance (km)
                'max_detour_time_minutes' => 10,      // Maximum additional time (minutes)
                'max_detour_percentage' => 30,        // Max % increase from direct route
                'min_direct_distance_km' => 2.0,      // Minimum trip distance to allow detours
            ],
        ],
        'arret' => [
            'name' => 'Stop-based',
            'discount_percentage' => 30,
            'description' => 'Pickup and dropoff at predefined public stops only',
            'constraints' => [
                'max_distance_from_stop_meters' => 200,  // Max distance from stop coordinates
                'require_public_stops' => true,          // Only approved public stops
                'require_active_stops' => true,          // Only active stops
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Stop Validation Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for validating public stops and stop-based rides.
    |
    */

    'stops' => [
        'validation' => [
            'max_distance_meters' => 200,           // Max distance to consider a match
            'require_approved' => true,            // Only use approved stops
            'require_active' => true,              // Only use active stops
            'require_public' => true,              // Only use public stops
        ],
        'search' => [
            'radius_meters' => 5000,                // Search radius for nearby stops
            'max_results' => 10,                   // Max stops to return in search
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dynamic Ride Matching Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for matching riders in dynamic (shared) rides.
    |
    */

    'dynamic_matching' => [
        'enabled' => true,
        'max_riders_per_vehicle' => 4,
        'max_waiting_time_minutes' => 5,
        'detour_calculation' => [
            'use_osrm' => true,                    // Use OSRM for accurate detour calculation
            'fallback_to_haversine' => true,       // Fallback if OSRM fails
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pricing Configuration
    |--------------------------------------------------------------------------
    |
    | Base pricing rules for different variants.
    |
    */

    'pricing' => [
        'apply_discount_before_tax' => true,
        'apply_discount_before_surge' => true,
        'round_to_nearest' => 10,                  // Round final price to nearest 10 FCFA
    ],

];
