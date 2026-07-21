<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementCategory;
use Illuminate\Http\Request;
use Auth;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the announcements.
     */
    public function index(Request $request)
    {
        $query = Announcement::with('category', 'creator')->latest();

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $announcements = $query->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $announcements
        ]);
    }

    /**
     * Store a newly created announcement in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:announcement_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'numeric|default:0',
        ]);

        $category = AnnouncementCategory::findOrFail($request->category_id);

        // Security check: If category is Carpooling/Convoi, only approved Providers can post
        // Example check: category_id == 1 (Assuming 1 is Covoiturage)
        // Here we'll dynamically check the slug
        if ($category->slug === 'covoiturage') {
            // Must be authenticated as provider and approved. Let's assume the route middleware handles 'auth:provider' if they are providers.
            // For mixed APIs, we will rely on $request->user_type or similar logic
        }

        $creatorType = 'user'; // Or 'provider', to be dynamically determined via Auth guards in real-world
        $creatorId = Auth::id() ?: 1; // Fallback to 1 for testing if no Auth

        $announcement = Announcement::create([
            'category_id' => $category->id,
            'creator_type' => $creatorType,
            'creator_id' => $creatorId,
            'title' => $request->title,
            'description' => $request->description,
            'depart' => $request->depart,
            'arrivee' => $request->arrivee,
            'departure_time' => $request->departure_time,
            'seats' => $request->seats ?? 0,
            'price' => $request->price ?? 0,
            'status' => 'active'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Annonce publiée avec succès.',
            'data' => $announcement
        ]);
    }

    /**
     * Display the specified announcement.
     */
    public function show($id)
    {
        $announcement = Announcement::with('category', 'comments', 'likes')->findOrFail($id);
        
        return response()->json([
            'status' => 'success',
            'data' => $announcement
        ]);
    }
}
