<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WhatsappGroup;

class WhatsappGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $groups = WhatsappGroup::latest()->get();
        return view('admin.whatsapp_groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $evoApiUrl = config('services.evolution.url');
        $evoApiKey = config('services.evolution.key');
        $instanceName = config('services.evolution.instance', 'picme225');

        $whatsappGroups = [];
        $apiError = null;

        if ($evoApiUrl && $evoApiKey) {
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders(['apikey' => $evoApiKey])
                    ->timeout(30)
                    ->get("{$evoApiUrl}/group/fetchAllGroups/{$instanceName}?getParticipants=false");

                if ($response->successful()) {
                    $whatsappGroups = $response->json();
                } else {
                    $apiError = "Erreur de l'API Evolution (HTTP " . $response->status() . ")";
                }
            } catch (\Exception $e) {
                $apiError = "Impossible de se connecter à Evolution API (Timeout ou lenteur de réponse). Vous pouvez saisir les informations manuellement.";
            }
        } else {
            $apiError = "L'URL ou la clé API d'Evolution n'est pas configurée.";
        }

        return view('admin.whatsapp_groups.create', compact('whatsappGroups', 'apiError'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'group_id' => 'required|string|unique:whatsapp_groups',
            'name' => 'nullable|string',
            'default_category' => 'required|string',
            'insert_mode' => 'required|in:PENDING_VALIDATION,APPROVED',
            'is_active' => 'boolean',
        ]);

        WhatsappGroup::create($request->all());

        return redirect()->route('admin.whatsapp-groups.index')->with('success', 'Groupe ajouté avec succès.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $group = WhatsappGroup::findOrFail($id);
        return view('admin.whatsapp_groups.edit', compact('group'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $group = WhatsappGroup::findOrFail($id);

        $request->validate([
            'group_id' => 'required|string|unique:whatsapp_groups,group_id,' . $group->id,
            'name' => 'nullable|string',
            'default_category' => 'required|string',
            'insert_mode' => 'required|in:PENDING_VALIDATION,APPROVED',
            'is_active' => 'boolean',
        ]);

        $group->update($request->all());

        return redirect()->route('admin.whatsapp-groups.index')->with('success', 'Groupe mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $group = WhatsappGroup::findOrFail($id);
        $group->delete();

        return redirect()->route('admin.whatsapp-groups.index')->with('success', 'Groupe supprimé avec succès.');
    }
}
