<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MarketplaceCategory;

class MarketplaceCategoryController extends Controller
{
    public function index()
    {
        $categories = MarketplaceCategory::whereNull('parent_id')->with('children')->orderBy('order_index')->get();
        return view('admin.marketplace.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = MarketplaceCategory::whereNull('parent_id')->get();
        return view('admin.marketplace.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'label' => 'required|string|max:50',
            'icon' => 'nullable|string|max:50',
            'order_index' => 'required|integer',
            'parent_id' => 'nullable|exists:marketplace_categories,id',
        ]);

        MarketplaceCategory::create($request->all());

        return redirect()->route('admin.marketplace-categories.index')->with('flash_success', 'Catégorie créée avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function edit($id)
    {
        $category = MarketplaceCategory::findOrFail($id);
        $parents = MarketplaceCategory::whereNull('parent_id')->where('id', '!=', $id)->get();
        return view('admin.marketplace.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'label' => 'required|string|max:50',
            'icon' => 'nullable|string|max:50',
            'order_index' => 'required|integer',
            'parent_id' => 'nullable|exists:marketplace_categories,id',
        ]);

        $category = MarketplaceCategory::findOrFail($id);
        $category->update($request->all());

        return redirect()->route('admin.marketplace-categories.index')->with('flash_success', 'Catégorie mise à jour avec succès');
    }

    public function destroy($id)
    {
        $category = MarketplaceCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.marketplace-categories.index')->with('flash_success', 'Catégorie supprimée avec succès');
    }
}
