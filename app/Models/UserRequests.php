<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRequests extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider_id',
        'user_id',
        'current_provider_id',
        'service_type_id',
        'rental_hours',
        'is_recurring',
        'subscription_plan_id',
        'status',
        'cancelled_by',
        'is_track',
        'otp',
        'travel_time',
        'distance',
        's_latitude',
        'd_latitude',
        's_longitude',
        'd_longitude',
        'track_distance',
        'track_latitude',
        'track_longitude',
        'paid',
        's_address',
        'd_address',
        'assigned_at',
        'schedule_at',
        'started_at',
        'finished_at',
        'use_wallet',
        'user_rated',
        'provider_rated',
        'surge',
        'package_id',
        'round_trip',
        'total_capacity',
        'seats_booked',
        'is_pool_dynamic',
        'is_pdp_route',
        'linked_request_id',
        'sender_name',
        'sender_phone',
        'recipient_name',
        'recipient_phone',
        'package_description',
        'segments',
        'grouping_point_id',
        'luggage_count',
        'selected_seats',
        'interurban_company_id',
        'pdp_route_id',
        'pickup_stop_id',
        'dropoff_stop_id',
        'ride_variant',
        'method',
        'ac',
        'delivery_image',
        'delivery_meta',
        'with_driver',
        'rental_start_date',
        'rental_end_date',
        'rental_with_driver',
        'hospital_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        // 'created_at', 'updated_at'
    ];

    protected $casts = [
        'segments' => 'array',
        'is_recurring' => 'boolean',
        'is_pool_dynamic' => 'boolean',
        'is_pdp_route' => 'boolean',
        'use_wallet' => 'boolean',
        'surge' => 'boolean',
        'interurban_company_id' => 'integer',
        'pdp_route_id' => 'integer',
        'pickup_stop_id' => 'integer',
        'dropoff_stop_id' => 'integer',
        'luggage_count' => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'assigned_at',
        'schedule_at',
        'started_at',
        'finished_at',
    ];

    public function package()
    {
        return $this->belongsTo('App\Models\KmHour', 'package_id');
    }

    public function package_ren()
    {
        return $this->hasMany('App\Models\ServiceTypeRental', 'km_hour_id', 'package_id');
    }

    public function packagePrice()
    {
        return $this->hasOne(KmHourServiceTypePrice::class, 'km_hour_id', 'package_id');
    }
    /**
     * ServiceType Model Linked
     */
    public function service_type()
    {
        return $this->belongsTo('App\Models\ServiceType');
    }

    /**
     * UserRequestPayment Model Linked
     */
    public function payment()
    {
        return $this->hasOne('App\Models\UserRequestPayment', 'request_id');
    }

    /**
     * UserRequestRating Model Linked
     */
    public function rating()
    {
        return $this->hasOne('App\Models\UserRequestRating', 'request_id');
    }

    /**
     * UserRequestRating Model Linked
     */
    public function filter()
    {
        return $this->hasMany('App\Models\RequestFilter', 'request_id');
    }

    /**
     * The user who created the request.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * The provider assigned to the request.
     */
    public function provider()
    {
        return $this->belongsTo('App\Models\Provider');
    }

    public function provider_service()
    {
        return $this->belongsTo('App\Models\ProviderService', 'provider_id', 'provider_id');
    }

    /**
     * Passagers liés à la requête (Partage).
     */
    public function passengers()
    {
        return $this->hasMany(UserRequestPassenger::class , 'request_id');
    }

    public function groupingPoint()
    {
        return $this->belongsTo(PdpStop::class , 'grouping_point_id');
    }

    /**
     * Requête principale associée (si course liée).
     */
    public function linkedRequest()
    {
        return $this->belongsTo(self::class , 'linked_request_id');
    }

    public function interurbanCompany()
    {
        return $this->belongsTo(InterurbanCompany::class , 'interurban_company_id');
    }

    public function route()
    {
        return $this->belongsTo(PdpRoute::class , 'pdp_route_id');
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }

    public function scopePendingRequest($query, $user_id)
    {
        return $query->where('user_id', $user_id)
            ->whereNotIn('status', ['CANCELLED', 'COMPLETED', 'SCHEDULED']);
    }

    public function scopeRequestHistory($query)
    {
        return $query->orderBy('user_requests.created_at', 'desc')
            ->with('user', 'payment', 'provider');
    }

    public function scopeUserTrips($query, $user_id)
    {
        return $query->where('user_requests.user_id', $user_id)
            ->where('user_requests.status', 'COMPLETED')
            ->orderBy('user_requests.created_at', 'desc')
            ->select('user_requests.*')
            ->with('payment', 'service_type');
    }

    public function scopeUserUpcomingTrips($query, $user_id)
    {
        return $query->where('user_requests.user_id', $user_id)
            ->where('user_requests.status', 'SCHEDULED')
            ->orderBy('user_requests.created_at', 'desc')
            ->select('user_requests.*')
            ->with('service_type', 'provider');
    }

    public function scopeProviderUpcomingRequest($query, $user_id)
    {
        return $query->where('user_requests.provider_id', $user_id)
            ->where('user_requests.status', 'SCHEDULED')
            ->select('user_requests.*')
            ->with('service_type', 'user', 'provider');
    }

    public function scopeUserTripDetails($query, $user_id, $request_id)
    {
        return $query->where('user_requests.user_id', $user_id)
            ->where('user_requests.id', $request_id)
            ->where('user_requests.status', 'COMPLETED')
            ->select('user_requests.*')
            ->with('payment', 'service_type', 'user', 'provider', 'rating');
    }

    public function scopeUserUpcomingTripDetails($query, $user_id, $request_id)
    {
        return $query->where('user_requests.user_id', $user_id)
            ->where('user_requests.id', $request_id)
            ->where('user_requests.status', 'SCHEDULED')
            ->select('user_requests.*')
            ->with('service_type', 'user', 'provider');
    }

    public function scopeUserRequestStatusCheck($query, $user_id, $check_status)
    {
        // ANTIGRAVITY FIX: Limit to latest 1 request and only eager-load critical relations.
        // Loading 8 relations on every 3-second poll was causing "Maximum execution time of 60s" errors.
        return $query->where('user_requests.user_id', $user_id)
            ->where('user_requests.user_rated', 0)
            ->whereNotIn('user_requests.status', $check_status)
            ->orderBy('user_requests.id', 'desc')
            ->limit(1)
            ->select('user_requests.*')
            ->with('provider', 'service_type', 'provider_service', 'payment');
    }

    public function scopeUserRequestAssignProvider($query, $user_id, $check_status)
    {
        return $query->where('user_requests.user_id', $user_id)
            ->where('user_requests.user_rated', 0)
            ->where('user_requests.provider_id', 0)
            ->whereIn('user_requests.status', $check_status)
            ->select('user_requests.*')
            ->with('filter');
    }
}
