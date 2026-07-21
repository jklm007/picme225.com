<?php

namespace App\Services;

use App\Models\DaoProposal;
use App\Models\ServiceType;
use Illuminate\Support\Facades\Log;
use Exception;

class ProposalExecutionService
{
    /**
     * Exécuter une proposition qui a été votée "PASSED"
     */
    public function execute(DaoProposal $proposal)
    {
        if ($proposal->status !== 'PASSED' || $proposal->executed) {
            throw new Exception("La proposition ne peut pas être exécutée.");
        }

        try {
            switch ($proposal->type) {
                case 'PRICE_CHANGE':
                    $this->executePriceChange($proposal->execution_data);
                    break;
                case 'PARAMETER_CHANGE':
                    $this->executeParameterChange($proposal->execution_data);
                    break;
                // Autres types à implémenter...
                default:
                    Log::warning("Type de proposition non géré pour l'exécution automatique : " . $proposal->type);
                    break;
            }

            $proposal->update([
                'executed' => true,
                'executed_at' => now(),
                'status' => 'EXECUTED'
            ]);

            return true;
        } catch (Exception $e) {
            Log::error("Erreur lors de l'exécution de la proposition {$proposal->id}: " . $e->getMessage());
            throw $e;
        }
    }

    private function executePriceChange($data)
    {
        if (!isset($data['service_type_id']) || !isset($data['new_price'])) {
            throw new Exception("Données d'exécution invalides pour PRICE_CHANGE");
        }

        $serviceType = ServiceType::findOrFail($data['service_type_id']);
        $serviceType->update([
            'price' => $data['new_price']
        ]);

        Log::info("Le prix du service {$serviceType->name} a été mis à jour via DAO : {$data['new_price']}");
    }

    private function executeParameterChange($data)
    {
        // TODO: Mettre à jour les paramètres globaux (Settings)
        Log::info("Changement de paramètre via DAO simulé.");
    }
}
