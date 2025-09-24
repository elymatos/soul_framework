<?php

namespace App\Http\Controllers;

use App\Repositories\ActivationStateRepository;
use App\Repositories\ColumnConnectionRepository;
use App\Repositories\CorticalColumnRepository;
use App\Services\CorticalNetwork\NetworkService;
use Collective\Annotations\Routing\Attributes\Attributes\Delete;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Middleware(name: 'web')]
class CorticalNetworkController extends Controller
{
    public function __construct(
        private CorticalColumnRepository $columnRepo,
        private ColumnConnectionRepository $connectionRepo,
        private ActivationStateRepository $activationRepo,
        private NetworkService $networkService
    ) {}

    #[Get(path: '/cortical-network')]
    public function index()
    {
        return view('CorticalNetwork.editor');
    }

    #[Get(path: '/cortical-network/data')]
    public function getData(): JsonResponse
    {
        try {
            $columns = $this->columnRepo->getAll();

            $nodes = [];
            $edges = [];

            foreach ($columns as $column) {
                $nodes[] = [
                    'id' => "col_{$column->id}",
                    'label' => $column->name,
                    'type' => 'column',
                    'network_id' => $column->id,
                    'status' => $column->status,
                    'group' => 'column',
                ];

                $neurons = $this->columnRepo->getAllNeurons($column->id);

                foreach ($neurons as $layerName => $layerNeurons) {
                    foreach ($layerNeurons as $neuron) {
                        $nodes[] = [
                            'id' => "neuron_{$neuron->neuron_id}",
                            'label' => $neuron->n->getProperty('name'),
                            'type' => 'neuron',
                            'neuron_id' => $neuron->neuron_id,
                            'layer' => $neuron->n->getProperty('layer'),
                            'column_id' => $column->id,
                            'activation_level' => $neuron->n->getProperty('activation_level', 0.0),
                            'neuron_type' => $neuron->n->getProperty('neuron_type'),
                            'group' => 'layer_'.$neuron->n->getProperty('layer'),
                        ];

                        $edges[] = [
                            'id' => "col_{$column->id}_neuron_{$neuron->neuron_id}",
                            'from' => "col_{$column->id}",
                            'to' => "neuron_{$neuron->neuron_id}",
                            'label' => 'HAS_NEURON',
                            'type' => 'HAS_NEURON',
                            'arrows' => 'to',
                        ];
                    }
                }
            }

            foreach ($columns as $column) {
                $neurons = $this->columnRepo->getAllNeurons($column->id);
                foreach ($neurons as $layerNeurons) {
                    foreach ($layerNeurons as $neuron) {
                        $connections = $this->connectionRepo->getConnectionsForNeuron($neuron->neuron_id, 'outgoing');

                        foreach ($connections as $conn) {
                            $edges[] = [
                                'id' => "edge_{$conn->id}",
                                'from' => "neuron_{$conn->start_node_id}",
                                'to' => "neuron_{$conn->end_node_id}",
                                'label' => $conn->type,
                                'type' => $conn->type,
                                'weight' => $conn->properties['weight'] ?? 0.5,
                                'arrows' => 'to',
                            ];
                        }
                    }
                }
            }

            return response()->json([
                'nodes' => $nodes,
                'edges' => $edges,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'nodes' => [],
                'edges' => [],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    #[Post(path: '/cortical-network/column')]
    public function createColumn(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'column_type' => 'nullable|string|in:concept,frame,schema',
            'layer_4_count' => 'nullable|integer|min:1|max:50',
            'layer_23_count' => 'nullable|integer|min:1|max:100',
            'layer_5_count' => 'nullable|integer|min:1|max:20',
        ]);

        try {
            $layerConfig = [
                'layer_4' => [
                    'count' => $validated['layer_4_count'] ?? 10,
                    'activation_level' => 0.0,
                    'threshold' => 0.5,
                ],
                'layer_23' => [
                    'count' => $validated['layer_23_count'] ?? 20,
                    'activation_level' => 0.0,
                    'threshold' => 0.6,
                ],
                'layer_5' => [
                    'count' => $validated['layer_5_count'] ?? 5,
                    'activation_level' => 0.0,
                    'threshold' => 0.7,
                ],
            ];

            $result = $this->networkService->createCorticalColumn([
                'name' => $validated['name'],
                'configuration' => [
                    'column_type' => $validated['column_type'] ?? 'concept',
                ],
                'layer_config' => $layerConfig,
            ]);

            return $this->renderNotify('success', "Column '{$validated['name']}' created with {$result['configuration']['layer_config']['layer_4']['count']} + {$result['configuration']['layer_config']['layer_23']['count']} + {$result['configuration']['layer_config']['layer_5']['count']} neurons")
                ->header('HX-Trigger', json_encode(['reload-cortical-network' => true]));

        } catch (\Exception $e) {
            return $this->renderNotify('error', 'Failed to create column: '.$e->getMessage());
        }
    }

    #[Post(path: '/cortical-network/neuron')]
    public function createNeuron(Request $request)
    {
        $validated = $request->validate([
            'column_id' => 'required|integer|exists:cortical_networks,id',
            'name' => 'required|string|max:255',
            'layer' => 'required|integer|in:4,23,5',
            'neuron_type' => 'required|string|in:input,processing,output,cardinal',
            'activation_level' => 'nullable|numeric|min:0|max:1',
            'threshold' => 'nullable|numeric|min:0|max:1',
        ]);

        try {
            $neuron = $this->networkService->createNeuron(
                $validated['name'],
                $validated['layer'],
                [
                    'column_id' => $validated['column_id'],
                    'neuron_type' => $validated['neuron_type'],
                    'activation_level' => $validated['activation_level'] ?? 0.0,
                    'threshold' => $validated['threshold'] ?? 0.5,
                ]
            );

            return $this->renderNotify('success', "Neuron '{$validated['name']}' created in Layer {$validated['layer']}")
                ->header('HX-Trigger', json_encode(['reload-cortical-network' => true]));

        } catch (\Exception $e) {
            return $this->renderNotify('error', 'Failed to create neuron: '.$e->getMessage());
        }
    }

    #[Post(path: '/cortical-network/connection')]
    public function createConnection(Request $request)
    {
        $validated = $request->validate([
            'from_neuron_id' => 'required|integer',
            'to_neuron_id' => 'required|integer',
            'connection_type' => 'required|string|in:CONNECTS_TO,ACTIVATES,INHIBITS',
            'weight' => 'nullable|numeric|min:0|max:1',
            'strength' => 'nullable|numeric|min:0|max:1',
        ]);

        try {
            $connection = $this->connectionRepo->createConnection(
                $validated['from_neuron_id'],
                $validated['to_neuron_id'],
                $validated['connection_type'],
                [
                    'weight' => $validated['weight'] ?? 0.5,
                    'strength' => $validated['strength'] ?? 1.0,
                ]
            );

            return $this->renderNotify('success', "Connection created: {$validated['connection_type']}")
                ->header('HX-Trigger', json_encode(['reload-cortical-network' => true]));

        } catch (\Exception $e) {
            return $this->renderNotify('error', 'Failed to create connection: '.$e->getMessage());
        }
    }

    #[Delete(path: '/cortical-network/column/{id}')]
    public function deleteColumn(int $id)
    {
        try {
            $column = $this->columnRepo->find($id);

            if (! $column) {
                return response()->json(['success' => false, 'error' => 'Column not found'], 404);
            }

            $this->columnRepo->delete($id, hard: true);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[Delete(path: '/cortical-network/neuron/{id}')]
    public function deleteNeuron(int $id)
    {
        try {
            $this->networkService->deleteNeuron($id);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[Get(path: '/cortical-network/stats')]
    public function getStats()
    {
        try {
            $columns = $this->columnRepo->getActiveColumns();

            $totalNeurons = 0;
            $totalConnections = 0;
            $activeNeurons = 0;

            foreach ($columns as $column) {
                $stats = $this->columnRepo->getStatistics($column->id);
                $totalNeurons += $stats['neuron_count'];
                $totalConnections += $stats['connection_count'];
                $activeNeurons += $stats['active_neurons'];
            }

            return view('CorticalNetwork.partials.stats', [
                'total_columns' => $columns->count(),
                'total_neurons' => $totalNeurons,
                'total_connections' => $totalConnections,
                'active_neurons' => $activeNeurons,
            ]);

        } catch (\Exception $e) {
            return '<div class="stat-row"><span class="stat-label">Error:</span><span class="stat-value">'.e($e->getMessage()).'</span></div>';
        }
    }

    #[Get(path: '/cortical-network/columns-list')]
    public function getColumnsList()
    {
        try {
            $columns = $this->columnRepo->getAll();

            $html = '<option value="">Select a column...</option>';
            foreach ($columns as $column) {
                $html .= "<option value=\"{$column->id}\">{$column->name} (ID: {$column->id})</option>";
            }

            return $html;

        } catch (\Exception $e) {
            return '<option value="">Error loading columns</option>';
        }
    }
}