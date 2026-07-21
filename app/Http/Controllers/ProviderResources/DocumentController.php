<?php

namespace App\Http\Controllers\ProviderResources;

use App\Models\Document;
use App\Http\Controllers\Controller;
use App\Models\ProviderDocument;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    /**
     * Affiche la liste des documents requis, en incluant les documents
     * déjà soumis par le fournisseur.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Étape 1 : Récupérer l'utilisateur (fournisseur) actuellement authentifié via l'API.
            $provider = Auth::guard('providerapi')->user() ?: Auth::guard('provider')->user();

            // Étape 2 : Récupérer la liste de tous les types de documents possibles pour les chauffeurs.
            $driverDocumentTypes = Document::driver()->get();

            // Étape 3 : Récupérer la liste de tous les types de documents possibles pour les véhicules.
            $vehicleDocumentTypes = Document::vehicle()->get();
            
            // API Response
            if (request()->wantsJson() || request()->is('api/*') || request()->ajax()) {
                // Étape 4 : Récupérer les documents que ce fournisseur spécifique a déjà téléchargés.
                // La méthode `keyBy('document_id')` organise les résultats par l'identifiant du document
                // pour un accès facile et rapide à l'étape suivante.
                $submittedProviderDocuments = ProviderDocument::where('provider_id', $provider->id)
                                    ->get()
                                    ->keyBy('document_id');

                // Étape 5 : Parcourir la liste des types de documents pour chauffeurs et y ajouter les URLs existantes.
                // La méthode `map` permet de transformer chaque élément de la collection.
                $driverDocumentTypes->map(function ($documentType) use ($submittedProviderDocuments) {
                    // Vérifier si un document soumis correspond à ce type de document.
                    if (isset($submittedProviderDocuments[$documentType->id])) {
                        // Si oui, ajouter les propriétés 'url' et 'status' à l'objet.
                        // L'URL est récupérée depuis le stockage pour être accessible publiquement.
                        $documentType->url = \Storage::disk('s3')->url( $submittedProviderDocuments[$documentType->id]->url);
                        $documentType->status = $submittedProviderDocuments[$documentType->id]->status;
                        $expiresAtVal = $submittedProviderDocuments[$documentType->id]->expires_at;
                        if ($expiresAtVal) {
                            $documentType->expires_at = is_string($expiresAtVal) ? substr($expiresAtVal, 0, 10) : (is_a($expiresAtVal, 'Carbon\Carbon') || is_a($expiresAtVal, 'DateTime') ? $expiresAtVal->format('Y-m-d') : $expiresAtVal);
                        } else {
                            $documentType->expires_at = null;
                        }
                    } else {
                        $documentType->url = null;
                        $documentType->status = 'NOT_SUBMITTED';
                        $documentType->expires_at = null;
                    }
                    return $documentType;
                });

                // Étape 6 : Faire la même chose pour les types de documents pour véhicules.
                $vehicleDocumentTypes->map(function ($documentType) use ($submittedProviderDocuments) {
                    // Vérifier si un document soumis correspond à ce type de document.
                    if (isset($submittedProviderDocuments[$documentType->id])) {
                        // Si oui, ajouter les propriétés 'url' et 'status'.
                        $documentType->url = \Storage::disk('s3')->url( $submittedProviderDocuments[$documentType->id]->url);
                        $documentType->status = $submittedProviderDocuments[$documentType->id]->status;
                        $expiresAtVal = $submittedProviderDocuments[$documentType->id]->expires_at;
                        if ($expiresAtVal) {
                            $documentType->expires_at = is_string($expiresAtVal) ? substr($expiresAtVal, 0, 10) : (is_a($expiresAtVal, 'Carbon\Carbon') || is_a($expiresAtVal, 'DateTime') ? $expiresAtVal->format('Y-m-d') : $expiresAtVal);
                        } else {
                            $documentType->expires_at = null;
                        }
                    } else {
                        $documentType->url = null;
                        $documentType->status = 'NOT_SUBMITTED';
                        $documentType->expires_at = null;
                    }
                    return $documentType;
                });
                
                // Étape 7 : Retourner la réponse JSON finale à l'application Android.
                // Les listes de documents contiennent maintenant les URLs des fichiers déjà téléchargés.
                return response()->json([
                    'driver_documents' => $driverDocumentTypes,
                    'vehicle_documents' => $vehicleDocumentTypes,
                    'provider' => $provider,
                ]);
            }

            // Web Response
            $DriverDocuments = $driverDocumentTypes;
            $VehicleDocuments = $vehicleDocumentTypes;
            $Provider = $provider;
            return view('provider.document.index', compact('DriverDocuments', 'VehicleDocuments', 'Provider'));

        } catch (\Exception $exception) {
            // En cas d'erreur (par exemple, si l'utilisateur n'est pas authentifié),
            // renvoyer une réponse d'erreur générique.
            if (request()->wantsJson() || request()->is('api/*') || request()->ajax()) {
                return response()->json(['error' => 'Une erreur est survenue lors de la récupération des documents.' . $exception->getMessage()], 500);
            }
            return back()->with('flash_error', 'Une erreur est survenue lors de la récupération des documents.');
        }
    }


    /**
     * Stocke un nouveau document téléchargé par le fournisseur (API Android - retourne JSON).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document.1' => 'required|file|max:2048|mimes:jpg,jpeg,png,pdf',
            'document_id.1' => 'required|exists:documents,id',
            'document.2' => 'nullable|file|max:2048|mimes:jpg,jpeg,png,pdf',
            'document_id.2' => 'nullable|exists:documents,id',
            'document.3' => 'nullable|file|max:2048|mimes:jpg,jpeg,png,pdf',
            'document_id.3' => 'nullable|exists:documents,id',
            'document.4' => 'nullable|file|max:2048|mimes:jpg,jpeg,png,pdf',
            'document_id.4' => 'nullable|exists:documents,id',
        ], [
            'document.1.required' => 'Le Driving Licence est requis.',
            'document.1.file' => 'Le Driving Licence doit être un fichier.',
            'document.1.mimes' => 'Le Driving Licence doit être de type: jpg, jpeg, png, pdf.',
            'document.1.max' => 'Le Driving Licence ne doit pas dépasser 2Mo.',
            'document_id.1.required' => 'Le champ Driving Licence ID est obligatoire.',
            'document_id.1.exists' => 'Le Driving Licence ID sélectionné est invalide.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $provider = Auth::guard('providerapi')->user() ?: Auth::guard('provider')->user();
        if (!$provider) {
            return response()->json(['error' => 'Utilisateur non authentifié.'], 401);
        }

        $providerId = $provider->id;

        for ($i = 1; $i <= count($request->file('document')); $i++) {
            $documentFile = $request->file('document')[$i] ?? null;
            $documentId = $request->input('document_id')[$i] ?? null;
            $expiresAt = null;
            if ($request->has('expires_at') && isset($request->input('expires_at')[$i])) {
                $expiresAt = $request->input('expires_at')[$i];
            }

            if ($documentFile && $documentId) {
                try {
                    $documentPath = $documentFile->store("provider/documents/provider_{$providerId}");

                    ProviderDocument::updateOrCreate(
                        ['provider_id' => $providerId, 'document_id' => $documentId],
                        [
                            'url' => $documentPath,
                            'status' => 'ASSESSING',
                            'expires_at' => $expiresAt,
                        ]
                    );
                } catch (\Exception $exception) {
                    return response()->json(['error' => 'Erreur lors de l\'ajout du document.'], 500);
                }
            }
        }

        return response()->json(['message' => 'Documents ajoutés/mis à jour avec succès.'], 200);
    }


    /**
     * Affiche un document spécifique (pour l'API Android - retourne JSON).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $provider = Auth::guard('providerapi')->user() ?: Auth::guard('provider')->user();
        $document = ProviderDocument::where('provider_id', $provider->id)
            ->where('document_id', $id)
            ->first();

        if (!$document) {
            return response()->json(['error' => 'Document introuvable.'], 404);
        }

        return response()->json(['document' => $document]);
    }

    /**
     * Met à jour un document du fournisseur (pour l'API Android - retourne JSON).
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'expires_at' => 'nullable|date_format:Y-m-d',
        ]);

        $provider = Auth::guard('providerapi')->user() ?: Auth::guard('provider')->user();
        if (!$provider) {
            return response()->json(['error' => 'Utilisateur non authentifié.'], 401);
        }

        try {
            $document = ProviderDocument::where('provider_id', $provider->id)
                ->where('document_id', $id)
                ->firstOrFail();

            if (Storage::exists($document->url)) {
                Storage::delete($document->url);
            }

            $documentPath = $request->file('document')->store('provider/documents');
            $document->update([
                'url' => $documentPath,
                'status' => 'ASSESSING',
                'expires_at' => $request->input('expires_at'),
            ]);

            return response()->json(['message' => 'Document mis à jour avec succès.', 'document' => $document], 200);

        } catch (ModelNotFoundException $exception) {
            $documentPath = $request->file('document')->store('provider/documents');

            $document = ProviderDocument::create([
                'provider_id' => $provider->id,
                'document_id' => $id,
                'url' => $documentPath,
                'status' => 'ASSESSING',
                'expires_at' => $request->input('expires_at'),
            ]);

            return response()->json(['message' => 'Document ajouté avec succès.', 'document' => $document], 201);
        }
    }

    /**
     * Supprime un document du fournisseur (pour l'API Android - retourne JSON).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $provider = Auth::guard('providerapi')->user() ?: Auth::guard('provider')->user();
        if (!$provider) {
            return response()->json(['error' => 'Utilisateur non authentifié.'], 401);
        }

        try {
            $document = ProviderDocument::where('provider_id', $provider->id)
                ->where('document_id', $id)
                ->firstOrFail();

            if (Storage::exists($document->url)) {
                Storage::delete($document->url);
            }

            $document->delete();

            return response()->json(['message' => 'Document supprimé avec succès.'], 200);

        } catch (ModelNotFoundException $exception) {
            return response()->json(['error' => 'Document introuvable.'], 404);
        } catch (\Exception $exception) {
            return response()->json(['error' => 'Erreur lors de la suppression du document.'], 500);
        }
    }
}
