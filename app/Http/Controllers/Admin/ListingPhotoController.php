<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MarketplaceListing;
use App\Models\WhatsappMessage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ListingPhotoController extends Controller
{
    /**
     * Display the photo manager for a listing.
     */
    public function index($id)
    {
        $listing = MarketplaceListing::findOrFail($id);
        return view('admin.marketplace.listings.photos', compact('listing'));
    }

    /**
     * Upload a new photo to the listing.
     */
    public function upload(Request $request, $id)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $listing = MarketplaceListing::findOrFail($id);
        
        try {
            $disk = env('FILESYSTEM_DISK', config('filesystems.default', 's3'));
            $path = $request->file('photo')->store('marketplace/listings', $disk);
            
            $images = is_array($listing->images) ? $listing->images : [];
            $images[] = $path;
            
            $listing->images = $images;
            
            // If it's the first image, set as cover
            if (empty($listing->cover_image) || count($images) == 1) {
                $listing->cover_image = $path;
            }
            
            $listing->save();
            $this->syncWithWhatsapp($listing);
            
            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => Storage::disk($disk)->url($path),
                'message' => 'Image uploadée avec succès.'
            ]);
        } catch (\Exception $e) {
            Log::error('Photo upload failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de l\'upload.'], 500);
        }
    }

    /**
     * Reorder photos via drag & drop.
     */
    public function reorder(Request $request, $id)
    {
        $request->validate([
            'order' => 'required|array',
        ]);

        $listing = MarketplaceListing::findOrFail($id);
        $order = $request->input('order'); // array of paths
        
        $currentImages = is_array($listing->images) ? $listing->images : [];
        
        // Ensure we only keep valid paths that currently exist for this listing
        $newImages = [];
        foreach ($order as $path) {
            if (in_array($path, $currentImages)) {
                $newImages[] = $path;
            }
        }
        
        $listing->images = $newImages;
        
        // Automatically make the first one the cover image if none explicitly set
        // Or if we just reordered and want the first to be the main
        if (count($newImages) > 0) {
            $listing->cover_image = $newImages[0];
        } else {
            $listing->cover_image = null;
        }

        $listing->save();
        $this->syncWithWhatsapp($listing);

        return response()->json(['success' => true, 'message' => 'Ordre mis à jour.']);
    }

    /**
     * Delete a photo.
     */
    public function destroy(Request $request, $id)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $listing = MarketplaceListing::findOrFail($id);
        $path = $request->input('path');
        
        $images = is_array($listing->images) ? $listing->images : [];
        $images = array_values(array_filter($images, function($img) use ($path) {
            return $img !== $path;
        }));
        
        $listing->images = $images;
        
        // Remove file from disk
        $disk = env('FILESYSTEM_DISK', config('filesystems.default', 's3'));
        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
        
        // Update cover image if the deleted one was the cover
        if ($listing->cover_image === $path) {
            $listing->cover_image = count($images) > 0 ? $images[0] : null;
        }
        
        $listing->save();
        $this->syncWithWhatsapp($listing);

        return response()->json(['success' => true, 'message' => 'Image supprimée.']);
    }

    /**
     * Set a photo as main cover image.
     */
    public function setMain(Request $request, $id)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $listing = MarketplaceListing::findOrFail($id);
        $path = $request->input('path');
        
        $images = is_array($listing->images) ? $listing->images : [];
        
        if (in_array($path, $images)) {
            $listing->cover_image = $path;
            
            // Optionally, move it to the beginning of the array
            $images = array_values(array_filter($images, function($img) use ($path) {
                return $img !== $path;
            }));
            array_unshift($images, $path);
            $listing->images = $images;
            
            $listing->save();
            $this->syncWithWhatsapp($listing);
            
            return response()->json(['success' => true, 'message' => 'Image principale mise à jour.']);
        }

        return response()->json(['success' => false, 'message' => 'Image introuvable.'], 404);
    }
    
    /**
     * Sync images with WhatsappMessage model if the listing originates from WhatsApp.
     */
    private function syncWithWhatsapp(MarketplaceListing $listing)
    {
        if ($listing->whatsapp_message_id) {
            $message = WhatsappMessage::find($listing->whatsapp_message_id);
            if ($message) {
                // Ensure media array format is correct for WhatsApp messages 
                $message->medias = $listing->images;
                $message->save();
            }
        }
    }
}
