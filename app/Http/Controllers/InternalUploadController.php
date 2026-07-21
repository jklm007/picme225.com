<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Internal controller for receiving image uploads FROM the worker pod.
 * This endpoint is NOT meant to be called from the public internet.
 * It accepts base64-encoded image data and stores it on the web pod's public disk,
 * returning the accessible public URL. This is a temporary workaround until
 * Cloudflare R2 (or another shared storage) is configured.
 */
class InternalUploadController extends Controller
{
    /**
     * Accepts a base64-encoded image and stores it locally.
     * No authentication required - must only be called from within the cluster.
     *
     * POST /internal/upload-image
     * Body: { "data": "data:image/jpeg;base64,..." }
     */
    public function uploadImage(Request $request)
    {
        // Basic cluster-internal security: check for internal secret header
        $internalSecret = env('INTERNAL_API_SECRET', 'picme225-internal-secret');
        if ($request->header('X-Internal-Secret') !== $internalSecret) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $base64 = $request->input('data', '');

        if (empty($base64)) {
            return response()->json(['error' => 'No image data provided'], 422);
        }

        try {
            $ext = 'webp'; // Default fallback
            if (preg_match('/^data:(\w+)\/(\w+);base64,/', $base64, $matches)) {
                $type = $matches[1]; // e.g. "image" or "video"
                $mimeExt = $matches[2]; // e.g. "jpeg", "mp4"
                if ($type === 'video') {
                    $ext = ($mimeExt === 'quicktime') ? 'mov' : $mimeExt;
                }
                $base64Data = substr($base64, strpos($base64, ',') + 1);
            } else {
                $base64Data = $base64;
            }

            $imageData = base64_decode($base64Data);

            if ($imageData === false || strlen($imageData) < 100) {
                return response()->json(['error' => 'Invalid base64 data'], 422);
            }

            // Save with resolved extension
            $filename = 'listings/' . Str::uuid() . '_' . time() . '.' . $ext;
            Storage::disk('s3')->put($filename, $imageData);

            // Return the full public URL
            try {
                $url = Storage::disk('s3')->url($filename);
            } catch (\Throwable $e) {
                $url = Storage::url($filename);
            }

            if (str_starts_with($url, 'http')) {
                $publicUrl = $url;
            } else {
                $appPublicUrl = rtrim(env('APP_PUBLIC_URL', 'https://www.picme225.site'), '/');
                $publicUrl = $appPublicUrl . $url;
            }

            Log::info('InternalUpload: image saved', ['filename' => $filename, 'url' => $publicUrl]);

            return response()->json(['url' => $publicUrl], 201);
        } catch (\Exception $e) {
            Log::error('InternalUpload error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
