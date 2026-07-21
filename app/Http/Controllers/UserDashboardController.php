<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserRequests;
use App\Models\ServiceType;

class UserDashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Résoudre dynamiquement le UserApiController.
     */
    protected function api()
    {
        return app(UserApiController::class);
    }

    /**
     * Show the user dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        try {
            $recentTrips = UserRequests::where('user_id', $user->id)
                ->with(['provider', 'payment'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $totalTrips = UserRequests::where('user_id', $user->id)
                ->where('status', 'COMPLETED')
                ->count();

            $upcomingTrips = UserRequests::where('user_id', $user->id)
                ->where('status', 'SCHEDULED')
                ->orderBy('schedule_at', 'asc')
                ->take(3)
                ->get();
        } catch (\Exception $e) {
            $recentTrips   = collect();
            $totalTrips    = 0;
            $upcomingTrips = collect();
        }

        // Include categories with status=1 OR status=NULL (backward compat before status field was added)
        $categories   = \App\Models\Service::where(function($q) {
            $q->where('status', 1)
              ->orWhere('status', 'ACTIVE')
              ->orWhere('status', 'active')
              ->orWhereNull('status');
        })->with('serviceTypes')->get();
        $package      = \App\Models\KmHour::all();
        // Fetch ads
        $ads = collect();
        if (class_exists('\App\Models\AdCampaign')) {
            try {
                $ads = \App\Models\AdCampaign::where('status', 'ACTIVE')
                    ->whereDate('start_date', '<=', now())
                    ->where(function($q) {
                        $q->whereDate('end_date', '>=', now())
                          ->orWhereNull('end_date');
                    })
                    ->with('contents')
                    ->take(5)
                    ->get();
            } catch (\Exception $e) {
                // Ignore
            }
        }

        return view('user.dashboard', compact(
            'user',
            'recentTrips',
            'totalTrips',
            'upcomingTrips',
            'categories',
            'package',
            'ads'
        ));
    }

    public function home()
    {
        $user = Auth::user();
        
        // Fetch categories (active)
        $categories = \App\Models\Service::where(function($q) {
            $q->where('status', 1)
              ->orWhere('status', 'ACTIVE')
              ->orWhere('status', 'active')
              ->orWhereNull('status');
        })->get();
        
        // Fetch recent marketplace listings
        $recentProducts = collect();
        if (class_exists('\App\Models\MarketplaceListing')) {
            $recentProducts = \App\Models\MarketplaceListing::where('status', 'ACTIVE')
                ->whereNull('deleted_at')
                ->latest()
                ->take(8)
                ->get();
        }
        
        // Fetch ads
        $ads = collect();
        if (class_exists('\App\Models\AdCampaign')) {
            try {
                $ads = \App\Models\AdCampaign::where('status', 'ACTIVE')
                    ->whereDate('start_date', '<=', now())
                    ->where(function($q) {
                        $q->whereDate('end_date', '>=', now())
                          ->orWhereNull('end_date');
                    })
                    ->with('contents')
                    ->take(5)
                    ->get();
            } catch (\Exception $e) {
                // Ignore
            }
        }
        
        return view('user.home', compact('user', 'categories', 'recentProducts', 'ads'));
    }

    public function check_dash()
    {
        try {
            $response = $this->api()->request_status_check()->getData();
            return response()->json(['status' => empty($response->data) ? 0 : 1]);
        } catch (\Exception $e) {
            return response()->json(['status' => 0]);
        }
    }

    public function profile()
    {
        return view('user.account.profile');
    }

    public function edit_profile()
    {
        return view('user.account.edit_profile');
    }

    public function update_profile(Request $request)
    {
        return $this->api()->update_profile($request);
    }

    public function change_password()
    {
        return view('user.account.change_password');
    }

    public function update_password(Request $request)
    {
        return $this->api()->change_password($request);
    }

    public function trips()
    {
        try {
            $trips = \App\Models\UserRequests::where('user_id', Auth::id())
                ->where('status', 'COMPLETED')
                ->with(['provider', 'payment', 'service_type', 'rating'])
                ->orderBy('assigned_at', 'desc')
                ->paginate(15);
        } catch (\Exception $e) {
            \Log::error('Trips query failed: ' . $e->getMessage());
            $trips = collect();
        }
        return view('user.ride.trips', compact('trips'));
    }

    public function payment()
    {
        $cards = (new Resource\CardResource)->index();
        return view('user.account.payment', compact('cards'));
    }

    public function wallet(Request $request)
    {
        $cards = (new Resource\CardResource)->index();
        return view('user.account.wallet', compact('cards'));
    }

    public function promotions_index()
    {
        $promocodes = $this->api()->promocodes();
        return view('user.account.promotions', compact('promocodes'));
    }

    public function promotions_store(Request $request)
    {
        return $this->api()->add_promocode($request);
    }

    public function upcoming_trips()
    {
        $trips = $this->api()->upcoming_trips();
        return view('user.ride.upcoming', compact('trips'));
    }

    public function notifications()
    {
        return view('user.notifications');
    }
}
