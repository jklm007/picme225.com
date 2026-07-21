<?php

namespace App\Http\Controllers\Resource;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;

class MainCategoryResource extends Controller
{
    private function getAvailableImages()
    {
        return collect(\Illuminate\Support\Facades\Storage::disk('s3')->files('service'))
            ->filter(function($file) {
                return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['webp', 'png', 'jpg', 'jpeg']);
            })
            ->values();
    }

    public function index()
    {
        $services = Service::orderBy('created_at', 'desc')->get();
        return view('admin.main_category.index', compact('services'));
    }

    public function create()
    {
        $images = $this->getAvailableImages();
        return view('admin.main_category.create', compact('images'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'image' => 'nullable|mimes:ico,png,jpg,jpeg|max:5048'
        ]);

        try {
            $service = new Service;
            $service->name = $request->name;

            if($request->hasFile('image')) {
                $service->image = Helper::upload_picture($request->image);
            } elseif($request->filled('image_select')) {
                $service->image = $request->image_select;
            }
            
            $service->status = $request->has('status') ? 1 : 0;
            
            $service->save();

            return redirect()->route('admin.main-category.index')->with('flash_success', 'Catégorie Principale Enregistrée');
        } catch (\Exception $e) {
            return back()->with('flash_error', 'Erreur lors de la création');
        }
    }

    public function edit($id)
    {
        $service = Service::findOrFail($id);
        $images = $this->getAvailableImages();
        return view('admin.main_category.edit', compact('service', 'images'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'image' => 'nullable|mimes:ico,png,jpg,jpeg|max:5048'
        ]);

        try {
            $service = Service::findOrFail($id);
            $service->name = $request->name;

            if($request->hasFile('image')) {
                $service->image = Helper::upload_picture($request->image);
            } elseif($request->filled('image_select')) {
                $service->image = $request->image_select;
            }
            
            $service->status = $request->has('status') ? 1 : 0;
            
            $service->save();

            return redirect()->route('admin.main-category.index')->with('flash_success', 'Catégorie Mise à jour');
        } catch (\Exception $e) {
            return back()->with('flash_error', 'Erreur lors de la modification');
        }
    }
    
    public function destroy($id)
    {
        try {
            Service::find($id)->delete();
            return back()->with('flash_success', 'Catégorie supprimée');
        } catch (\Exception $e) {
            return back()->with('flash_error', 'Impossible de supprimer cette catégorie');
        }
    }
}
