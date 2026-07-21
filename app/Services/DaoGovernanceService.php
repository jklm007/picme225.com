<?php

namespace App\Services;

use App\Models\DaoProposal;
use App\Models\PdpStop;
use App\Models\PdpRoute;
use App\Models\ServiceType;
use Setting;
use Log;
use Exception;
use Carbon\Carbon;

class DaoGovernanceService
{
    /**
     * Exécuter une proposition si elle est approuvée
     *
     * @param DaoProposal $proposal
     * @return array
     */
    public function execute(DaoProposal $proposal)
    {
        if ($proposal->executed) {
            return ['status' => 'error', 'message' => 'Proposition déjà exécutée'];
        }

        $quorum = Setting::get('dao_quorum', 100);
        $totalVotes = $proposal->votes_for + $proposal->votes_against + $proposal->votes_abstain;

        if ($totalVotes < $quorum) {
            $proposal->status = 'FAILED_QUORUM';
            $proposal->save();
            return ['status' => 'failed', 'message' => 'Quorum non atteint'];
        }

        $isPassed = $proposal->votes_for > $proposal->votes_against;

        if (!$isPassed) {
            $proposal->status = 'REJECTED';
            $proposal->save();
            return ['status' => 'rejected', 'message' => 'Proposition rejetée par les votes'];
        }

        // Exécution de la proposition
        $executionData = $proposal->execution_data;
        $success = false;

        try {
            switch ($proposal->type) {
                case 'STOP_ADDITION':
                    $success = $this->handleStopAddition($executionData);
                    break;
                case 'PRICE_CHANGE':
                    $success = $this->handlePriceChange($executionData);
                    break;
                case 'PARAMETER_CHANGE':
                    $success = $this->handleParameterChange($executionData);
                    break;
                case 'ROUTE_ADDITION':
                    $success = $this->handleRouteAddition($executionData);
                    break;
                case 'ROUTE_MODIFICATION':
                    $success = $this->handleRouteModification($executionData);
                    break;
                default:
                    Log::warning("Unknown proposal type: " . $proposal->type);
                    break;
            }

            if ($success) {
                $proposal->executed = true;
                $proposal->executed_at = Carbon::now();
                $proposal->status = 'EXECUTED';
                $proposal->save();

                // Synchroniser avec la blockchain (Simulation)
                (new Web3Service())->executeProposal($proposal->blockchain_proposal_id, $executionData);

                return ['status' => 'success', 'message' => 'Proposition exécutée avec succès'];
            } else {
                return ['status' => 'error', 'message' => 'Échec de l\'exécution technique'];
            }

        } catch (Exception $e) {
            Log::error("DAO Execution Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function handleStopAddition($data)
    {
        if (isset($data['stop_id'])) {
            $stop = PdpStop::find($data['stop_id']);
            if ($stop) {
                $stop->status = 'APPROVED';
                $stop->is_public = true;
                return $stop->save();
            }
        }
        return false;
    }

    private function handlePriceChange($data)
    {
        if (isset($data['service_type_id']) && isset($data['new_price'])) {
            $service = ServiceType::find($data['service_type_id']);
            if ($service) {
                $service->price = $data['new_price'];
                return $service->save();
            }
        }
        return false;
    }

    private function handleParameterChange($data)
    {
        if (isset($data['key']) && isset($data['value'])) {
            Setting::set($data['key'], $data['value']);
            Setting::save();
            return true;
        }
        return false;
    }

    private function handleRouteAddition($data)
    {
        if (isset($data['route_id'])) {
            $route = PdpRoute::find($data['route_id']);
            if ($route) {
                $route->status = 'ACTIVE';
                return $route->save();
            }
        }
        return false;
    }

    private function handleRouteModification($data)
    {
        if (isset($data['route_id'])) {
            $route = PdpRoute::find($data['route_id']);
            if ($route) {
                if (isset($data['base_price_per_segment'])) {
                    $route->base_price_per_segment = $data['base_price_per_segment'];
                }
                if (isset($data['detour_price_per_km'])) {
                    $route->detour_price_per_km = $data['detour_price_per_km'];
                }
                if (isset($data['max_detour_communal'])) {
                    $route->max_detour_communal = $data['max_detour_communal'];
                }
                if (isset($data['max_detour_intercommunal'])) {
                    $route->max_detour_intercommunal = $data['max_detour_intercommunal'];
                }
                if (isset($data['is_active'])) {
                    $route->is_active = $data['is_active'];
                }
                return $route->save();
            }
        }
        return false;
    }
}
