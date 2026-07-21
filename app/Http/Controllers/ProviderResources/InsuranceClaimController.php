<?php

namespace App\Http\Controllers\ProviderResources;

use App\Models\InsuranceClaim;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;

class InsuranceClaimController extends Controller
{
    /**
     * Récupérer l'historique des demandes d'aide du chauffeur
     */
    public function index()
    {
        $provider = Auth::user();
        
        $claims = InsuranceClaim::where('provider_id', $provider->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'claims' => $claims,
            'total_requested' => $claims->sum('amount_requested'),
            'total_approved' => $claims->where('status', 'approved')->sum('amount_approved'),
        ]);
    }

    /**
     * Soumettre une nouvelle demande d'aide (sinistre)
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'amount_requested' => 'required|numeric|min:1000',
            'incident_description' => 'required|string|min:20',
            'incident_date' => 'required|date|before_or_equal:today',
            'supporting_documents' => 'nullable|string', // URLs séparées par virgule
        ]);

        $provider = Auth::user();

        // Vérifier si le chauffeur a une assurance active
        if (!$provider->subscriptionPlan || !$provider->subscriptionPlan->insurance_included) {
            return response()->json([
                'error' => 'Vous devez avoir un abonnement avec assurance pour soumettre une demande.'
            ], 403);
        }

        $claim = InsuranceClaim::create([
            'provider_id' => $provider->id,
            'amount_requested' => $request->amount_requested,
            'incident_description' => $request->incident_description,
            'incident_date' => $request->incident_date,
            'supporting_documents' => $request->supporting_documents,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Demande d\'aide soumise avec succès. Elle sera examinée par l\'administration.',
            'claim' => $claim
        ], 201);
    }

    /**
     * Détails d'une demande spécifique
     */
    public function show($id)
    {
        $provider = Auth::user();
        
        $claim = InsuranceClaim::where('provider_id', $provider->id)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json(['claim' => $claim]);
    }
}
