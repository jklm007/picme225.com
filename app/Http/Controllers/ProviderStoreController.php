<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MarketplaceListing;
use App\Models\MarketplaceCategory;

class ProviderStoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:provider');
    }

    public function index()
    {
        $provider = Auth::guard('provider')->user();
        
        $listings = MarketplaceListing::where('user_id', $provider->id)
            ->where('metadata->owner_type', 'provider')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Stats
        $total = MarketplaceListing::where('user_id', $provider->id)->where('metadata->owner_type', 'provider')->count();
        $active = MarketplaceListing::where('user_id', $provider->id)->where('metadata->owner_type', 'provider')->where('status', 'ACTIVE')->count();
        $pending = MarketplaceListing::where('user_id', $provider->id)->where('metadata->owner_type', 'provider')->where('status', 'PENDING')->count();

        return view('provider.store.index', compact('listings', 'total', 'active', 'pending'));
    }

    public function create()
    {
        $categories = MarketplaceCategory::whereNull('parent_id')
            ->with('children')
            ->orderBy('order_index')
            ->get();

        return view('provider.store.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category'    => 'required|string|max:100',
            'price'       => 'required|numeric|min:0',
            'phone'       => 'required|string|max:30',
            'location_city' => 'nullable|string|max:100',
        ]);

        $provider = Auth::guard('provider')->user();

        // Handle photo uploads (up to 6)
        $images = [];
        if ($request->hasFile('photos')) {
            foreach (array_slice($request->file('photos'), 0, 6) as $photo) {
                $path = $photo->store('marketplace/provider/' . $provider->id, 's3');
                $images[] = $path;
            }
        }

        MarketplaceListing::create([
            'user_id'      => $provider->id,
            'title'        => $request->title,
            'description'  => $request->description,
            'category'     => $request->category,
            'sub_category' => $request->sub_category,
            'price'        => $request->price,
            'price_unit'   => $request->price_unit ?? 'FCFA',
            'owner_name'   => $provider->first_name . ' ' . $provider->last_name,
            'owner_phone'  => $request->phone,
            'location_city' => $request->location_city,
            'cover_image'  => $images[0] ?? null,
            'images'       => $images,
            'status'       => 'PENDING', // approved by admin
            'type'         => 'RENTAL', // or SALE depending on category
            'metadata'     => [
                'owner_type'   => 'provider',
                'condition'    => $request->condition ?? 'used',
                'extra_info'   => $request->extra_info,
                'stock_quantity' => $request->stock_quantity ?? 1,
            ],
        ]);

        return redirect()->route('provider.store.index')
            ->with('flash_success', 'Votre annonce a été soumise et sera publiée après validation.');
    }

    public function edit($id)
    {
        $provider = Auth::guard('provider')->user();
        $listing = MarketplaceListing::where('id', $id)
            ->where('user_id', $provider->id)
            ->where('metadata->owner_type', 'provider')
            ->firstOrFail();

        $categories = MarketplaceCategory::whereNull('parent_id')
            ->with('children')
            ->orderBy('order_index')
            ->get();

        return view('provider.store.edit', compact('listing', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category'    => 'required|string|max:100',
            'price'       => 'required|numeric|min:0',
            'phone'       => 'required|string|max:30',
            'location_city' => 'nullable|string|max:100',
        ]);

        $provider = Auth::guard('provider')->user();
        $listing = MarketplaceListing::where('id', $id)
            ->where('user_id', $provider->id)
            ->where('metadata->owner_type', 'provider')
            ->firstOrFail();

        // Handle photo uploads
        $images = $listing->images ?? [];
        if ($request->hasFile('photos')) {
            $images = []; // overwrite existing images if new ones are uploaded
            foreach (array_slice($request->file('photos'), 0, 6) as $photo) {
                $path = $photo->store('marketplace/provider/' . $provider->id, 's3');
                $images[] = $path;
            }
        }

        $meta = $listing->metadata ?? [];
        $meta['condition'] = $request->condition ?? 'used';
        $meta['extra_info'] = $request->extra_info;
        $meta['stock_quantity'] = $request->stock_quantity ?? 1;

        $listing->update([
            'title'        => $request->title,
            'description'  => $request->description,
            'category'     => $request->category,
            'sub_category' => $request->sub_category,
            'price'        => $request->price,
            'price_unit'   => $request->price_unit ?? 'FCFA',
            'owner_phone'  => $request->phone,
            'location_city' => $request->location_city,
            'cover_image'  => $images[0] ?? $listing->cover_image,
            'images'       => $images,
            'metadata'     => $meta,
        ]);

        return redirect()->route('provider.store.index')
            ->with('flash_success', 'Annonce mise à jour avec succès.');
    }

    public function destroy($id)
    {
        $provider = Auth::guard('provider')->user();
        $listing = MarketplaceListing::where('id', $id)
            ->where('user_id', $provider->id)
            ->where('metadata->owner_type', 'provider')
            ->firstOrFail();

        $listing->delete();

        return redirect()->route('provider.store.index')
            ->with('flash_success', 'Annonce supprimée avec succès.');
    }
}
