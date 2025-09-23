<?php

namespace App\Services\CorticalNetwork;

use App\Database\Criteria;
use App\Database\GraphCriteria;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ActivationService
{
    private Criteria $criteria;

    private ?int $currentSessionId = null;

    public function __construct()
    {
        $this->criteria = new Criteria;
    }

    /**
     * Activate a single neuron with specified activation level
     */
    public function activateNeuron(int $neuronId, float $level): bool
    {
        if ($level < 0.0 || $level > 1.0) {
            throw new RuntimeException("Activation level must be between 0.0 and 1.0, got: {$level}");
        }

        $updated = GraphCriteria::node('Neuron')
            ->where('ID(n)', '=', $neuronId)
            ->update(['activation_level' => $level]);

        if ($updated > 0 && $this->currentSessionId) {
            $this->incrementSessionActivationCount();
        }

        return $updated > 0;
    }

    /**
     * Implement spread activation algorithm
     */
    public function spreadActivation(int $sourceId, array $params = []): array
    {
        $startTime = microtime(true);
        $maxSteps = $params['max_steps'] ?? 10;
        $decayFactor = $params['decay_factor'] ?? 0.8;
        $threshold = $params['threshold'] ?? 0.1;
        $activationHistory = [];

        Log::info('Starting spread activation', [
            'source_id' => $sourceId,
            'params' => $params,
            'session_id' => $this->currentSessionId,
        ]);

        for ($step = 0; $step < $maxSteps; $step++) {
            $stepStart = microtime(true);

            // Get currently active neurons
            $activeNeurons = GraphCriteria::node('Neuron')
                ->where('n.activation_level', '>', $threshold)
                ->returnClause('n, ID(n) as node_id')
                ->get();

            if ($activeNeurons->isEmpty()) {
                Log::info('No active neurons found, stopping spread', ['step' => $step]);
                break;
            }

            $activationChanges = [];

            // For each active neuron, spread activation to connected neurons
            foreach ($activeNeurons as $activeNeuron) {
                $connections = $this->getOutgoingConnections($activeNeuron->node_id);

                foreach ($connections as $connection) {
                    $targetId = $connection->end_node_id;
                    $weight = $connection->properties->weight ?? 0.5;
                    $currentActivation = $activeNeuron->n->properties->activation_level;

                    // Calculate activation spread
                    $spreadAmount = $currentActivation * $weight * $decayFactor;

                    if ($spreadAmount > $threshold) {
                        $activationChanges[$targetId] = ($activationChanges[$targetId] ?? 0) + $spreadAmount;
                    }
                }
            }

            // Apply activation changes
            $neuronsActivated = 0;
            foreach ($activationChanges as $neuronId => $newActivation) {
                // Cap activation at 1.0
                $finalActivation = min(1.0, $newActivation);

                if ($this->activateNeuron($neuronId, $finalActivation)) {
                    $neuronsActivated++;
                }
            }

            $stepDuration = microtime(true) - $stepStart;

            $stepData = [
                'step' => $step,
                'active_neurons' => $activeNeurons->count(),
                'neurons_activated' => $neuronsActivated,
                'duration_ms' => round($stepDuration * 1000, 2),
                'activation_changes' => count($activationChanges),
            ];

            $activationHistory[] = $stepData;

            Log::debug('Spread activation step completed', $stepData);

            // Stop if no new activations
            if ($neuronsActivated === 0) {
                Log::info('No new activations, stopping spread', ['step' => $step]);
                break;
            }
        }

        $totalDuration = microtime(true) - $startTime;

        $result = [
            'source_id' => $sourceId,
            'total_steps' => count($activationHistory),
            'total_duration_seconds' => round($totalDuration, 3),
            'final_active_count' => $this->countActiveNeurons($threshold),
            'activation_history' => $activationHistory,
            'parameters' => $params,
        ];

        if ($this->currentSessionId) {
            $this->updateSessionStepCount(count($activationHistory));
        }

        Log::info('Spread activation completed', [
            'result_summary' => array_diff_key($result, ['activation_history' => null]),
        ]);

        return $result;
    }

    /**
     * Calculate dynamic threshold for a neuron based on its connections
     */
    public function calculateThreshold(int $neuronId): float
    {
        // Get neuron's current properties
        $neuron = GraphCriteria::node('Neuron')
            ->where('ID(n)', '=', $neuronId)
            ->returnClause('n')
            ->first();

        if (! $neuron) {
            throw new RuntimeException("Neuron not found: {$neuronId}");
        }

        $baseThreshold = $neuron->n->properties->threshold ?? 0.5;

        // Get incoming connections
        $incomingConnections = $this->getIncomingConnections($neuronId);

        // Adjust threshold based on connection count and types
        $inhibitoryCount = 0;
        $excitatoryCount = 0;
        $totalWeight = 0;

        foreach ($incomingConnections as $connection) {
            $weight = $connection->properties->weight ?? 0.5;
            $totalWeight += $weight;

            if ($connection->type === 'INHIBITS') {
                $inhibitoryCount++;
            } else {
                $excitatoryCount++;
            }
        }

        // Calculate adaptive threshold
        $connectionFactor = $incomingConnections->count() > 0 ?
            ($totalWeight / $incomingConnections->count()) : 1.0;

        $inhibitionFactor = $inhibitoryCount > 0 ?
            1 + ($inhibitoryCount * 0.1) : 1.0;

        $adaptiveThreshold = $baseThreshold * $connectionFactor * $inhibitionFactor;

        // Keep within bounds
        return max(0.1, min(1.0, $adaptiveThreshold));
    }

    /**
     * Start a new activation session for tracking
     */
    public function startActivationSession(string $sessionName, int $networkId, array $parameters = []): int
    {
        $sessionData = [
            'cortical_network_id' => $networkId,
            'session_name' => $sessionName,
            'description' => $parameters['description'] ?? "Activation session: {$sessionName}",
            'status' => 'active',
            'parameters' => json_encode($parameters),
            'triggered_by' => $parameters['triggered_by'] ?? 'system',
            'started_at' => now(),
        ];

        $this->currentSessionId = Criteria::create('activation_sessions', $sessionData);

        Log::info('Started activation session', [
            'session_id' => $this->currentSessionId,
            'session_name' => $sessionName,
            'network_id' => $networkId,
        ]);

        return $this->currentSessionId;
    }

    /**
     * Complete the current activation session
     */
    public function completeActivationSession(array $finalMetrics = []): void
    {
        if (! $this->currentSessionId) {
            throw new RuntimeException('No active session to complete');
        }

        $updateData = [
            'status' => 'completed',
            'completed_at' => now(),
            'final_state' => json_encode($this->getCurrentActivationState()),
            'performance_metrics' => json_encode($finalMetrics),
        ];

        // Calculate session duration if not provided
        $session = $this->getSessionData($this->currentSessionId);
        if ($session && $session->started_at) {
            $duration = now()->diffInSeconds($session->started_at);
            $updateData['duration_seconds'] = $duration;
        }

        Criteria::table('activation_sessions')
            ->where('id', $this->currentSessionId)
            ->update($updateData);

        Log::info('Completed activation session', [
            'session_id' => $this->currentSessionId,
            'duration_seconds' => $updateData['duration_seconds'] ?? null,
        ]);

        $this->currentSessionId = null;
    }

    /**
     * Get current activation state of all neurons
     */
    public function getActivationState(array $neuronIds = []): Collection
    {
        $query = GraphCriteria::node('Neuron');

        if (! empty($neuronIds)) {
            $query->where('ID(n) IN ['.implode(',', $neuronIds).']');
        }

        return $query
            ->returnClause('ID(n) as neuron_id, n.activation_level, n.threshold, n.layer')
            ->orderBy('n.layer', 'ASC')
            ->get();
    }

    /**
     * Reset all neuron activations to zero
     */
    public function resetNetwork(): int
    {
        $updated = GraphCriteria::node('Neuron')
            ->update(['activation_level' => 0.0]);

        Log::info('Reset network activations', ['neurons_reset' => $updated]);

        return $updated;
    }

    /**
     * Get activation history for a specific neuron
     */
    public function getActivationHistory(int $neuronId, int $steps = 10): array
    {
        // For now, return current state - in a full implementation,
        // we would store activation history in the database
        $neuron = GraphCriteria::node('Neuron')
            ->where('ID(n)', '=', $neuronId)
            ->returnClause('n')
            ->first();

        return [
            'neuron_id' => $neuronId,
            'current_activation' => $neuron ? $neuron->n->properties->activation_level : 0.0,
            'current_threshold' => $neuron ? $neuron->n->properties->threshold : 0.5,
            'note' => 'Historical tracking not yet implemented',
        ];
    }

    /**
     * Save current activation state as snapshot
     */
    public function saveActivationSnapshot(string $sessionId): void
    {
        if (! $this->currentSessionId) {
            throw new RuntimeException('No active session for snapshot');
        }

        $activationState = $this->getCurrentActivationState();

        $this->criteria
            ->update('activation_sessions')
            ->set(['final_state' => json_encode($activationState)])
            ->where('id', $this->currentSessionId)
            ->execute();

        Log::info('Saved activation snapshot', [
            'session_id' => $this->currentSessionId,
            'active_neurons' => count(array_filter($activationState, fn ($n) => $n['activation_level'] > 0)),
        ]);
    }

    /**
     * Get outgoing connections for a neuron
     */
    private function getOutgoingConnections(int $neuronId): Collection
    {
        return GraphCriteria::match('(n:Neuron)-[r]->(target:Neuron)')
            ->where('ID(n)', '=', $neuronId)
            ->returnClause('r, ID(startNode(r)) as start_node_id, ID(endNode(r)) as end_node_id')
            ->get();
    }

    /**
     * Get incoming connections for a neuron
     */
    private function getIncomingConnections(int $neuronId): Collection
    {
        return GraphCriteria::match('(source:Neuron)-[r]->(n:Neuron)')
            ->where('ID(n)', '=', $neuronId)
            ->returnClause('r, ID(startNode(r)) as start_node_id, ID(endNode(r)) as end_node_id')
            ->get();
    }

    /**
     * Count currently active neurons above threshold
     */
    private function countActiveNeurons(float $threshold = 0.1): int
    {
        return GraphCriteria::node('Neuron')
            ->where('n.activation_level', '>', $threshold)
            ->count();
    }

    /**
     * Get current activation state for session tracking
     */
    private function getCurrentActivationState(): array
    {
        $activeNeurons = GraphCriteria::node('Neuron')
            ->where('n.activation_level', '>', 0)
            ->returnClause('ID(n) as neuron_id, n.activation_level, n.layer, n.name')
            ->get();

        $state = [];
        foreach ($activeNeurons as $neuron) {
            $state[] = [
                'neuron_id' => $neuron->neuron_id,
                'name' => $neuron->name,
                'layer' => $neuron->layer,
                'activation_level' => $neuron->activation_level,
            ];
        }

        return $state;
    }

    /**
     * Increment activation count for current session
     */
    private function incrementSessionActivationCount(): void
    {
        if (! $this->currentSessionId) {
            return;
        }

        $this->criteria
            ->update('activation_sessions')
            ->set(['activation_count = activation_count + 1'])
            ->where('id', $this->currentSessionId)
            ->execute();
    }

    /**
     * Update step count for current session
     */
    private function updateSessionStepCount(int $steps): void
    {
        if (! $this->currentSessionId) {
            return;
        }

        $this->criteria
            ->update('activation_sessions')
            ->set(['step_count' => $steps])
            ->where('id', $this->currentSessionId)
            ->execute();
    }

    /**
     * Get session data from database
     */
    private function getSessionData(int $sessionId): ?object
    {
        return $this->criteria
            ->select()
            ->from('activation_sessions')
            ->where('id', $sessionId)
            ->first();
    }
}
