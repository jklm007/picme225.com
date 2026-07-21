<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceType;
use App\Models\MarketplaceListing;
use App\Helpers\Helper;
use Setting;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => ['estimate_fare', 'index', 'marketplace', 'marketplace_detail', 'airport']]);
    }

    /**
     * New mobile-app focused landing page.
     */
    public function index()
    {
        $categories = \App\Models\Service::where(function($q) {
            $q->where('status', 1)
              ->orWhere('status', 'ACTIVE')
              ->orWhere('status', 'active')
              ->orWhereNull('status');
        })->with('serviceTypes')->get();
        
        return view('home', compact('categories'));
    }

    /**
     * Public marketplace product catalogue.
     * Loads listings server-side for SEO; the JS layer also fetches via API for filtering.
     */
    public function marketplace(Request $request)
    {
        $categories = \App\Models\MarketplaceCategory::whereNull('parent_id')->with('children')->orderBy('order_index')->get();
        
        $query = MarketplaceListing::where('status', 'ACTIVE')->whereNull('deleted_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('city')) {
            $query->where('location_city', 'like', "%{$request->city}%");
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('category')) {
            $cat = $request->category;
            $query->where(function($q) use ($cat) {
                $q->where('category', 'like', "%{$cat}%")
                  ->orWhere('sub_category', 'like', "%{$cat}%")
                  ->orWhere('type', 'like', "%{$cat}%");
            });
        }

        $sort = $request->get('sort', 'newest');
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $listings = $query->paginate(30);

        return view('marketplace.index', compact('listings', 'categories'));
    }

    /**
     * Detail view for a specific marketplace listing.
     */
    public function marketplace_detail($id)
    {
        $listing = MarketplaceListing::where('id', $id)->firstOrFail();
        
        // Ensure we don't show deleted or unapproved listings unless admin
        if ($listing->status !== 'ACTIVE' && $listing->status !== 'APPROVED') {
            abort(404);
        }

        // Related listings (same category, excluding current)
        $related = MarketplaceListing::where('status', 'ACTIVE')
            ->where('id', '!=', $id)
            ->when($listing->category, fn($q) => $q->where('category', $listing->category))
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        return view('marketplace.detail', compact('listing', 'related'));
    }

    /**
     * Airport shuttle booking page — WhatsApp redirect, no server POST.
     */
    public function airport()
    {
        return redirect('/airport');
    }


    public function estimate_fare(Request $request)
    {
        $this->validate($request, [
            's_latitude' => 'required|numeric',
            's_longitude' => 'required|numeric',
            'd_latitude' => 'required|numeric',
            'd_longitude' => 'required|numeric',
        ]);

        try {
            $routing = get_osrm_routing($request->s_latitude, $request->s_longitude, $request->d_latitude, $request->d_longitude);
            
            if (!$routing) {
                return response()->json(['error' => 'Impossible de calculer l\'itinéraire'], 500);
            }

            $meter = $routing['distance'];
            $seconds = $routing['duration'];
            
            $minutes = round($seconds/60);
            $time = $minutes . " mins"; // Basic text representation

            $kilometer = round($meter/1000);

            $services = ServiceType::all();
            $estimates = [];

            foreach($services as $service) {
                $price = $service->fixed + ($service->price * $kilometer) + ($service->minute * $minutes);
                
                // Apply Tokenomics Discount if applicable (just for display logic, though user is anon here)
                // $price = $price - ($price * ($service->eco_discount_percent / 100));

                $estimates[] = [
                    'id' => $service->id,
                    'name' => $service->name,
                    'image' => image($service->image),
                    'estimated_fare' => currency($price),
                    'time' => $time
                ];
            }

            return response()->json([
                'services' => $estimates,
                'distance' => $kilometer,
                'time' => $time
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Something Went Wrong'], 500);
        }
    }
}
