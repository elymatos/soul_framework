<?php

namespace App\Http\Controllers;

use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Middleware(name: 'web')]
class GraphEditorController extends Controller
{
    private const GRAPH_STORAGE_PATH = 'graphs/graph_data.json';

    #[Get(path: '/graph-editor')]
    public function index()
    {
        return view('GraphEditor.main');
    }

    #[Get(path: '/graph-editor/data')]
    public function getData(): JsonResponse
    {
        try {
            if (! Storage::exists(self::GRAPH_STORAGE_PATH)) {
                $this->initializeGraphFile();
            }

            $jsonContent = Storage::get(self::GRAPH_STORAGE_PATH);
            $graphData = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'nodes' => [],
                    'edges' => [],
                    'error' => 'Invalid JSON data',
                ], 400);
            }

            return response()->json([
                'nodes' => $graphData['nodes'] ?? [],
                'edges' => $graphData['edges'] ?? [],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'nodes' => [],
                'edges' => [],
                'error' => 'Failed to load graph data',
            ], 500);
        }
    }

    #[Post(path: '/graph-editor/node')]
    public function saveNode(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|in:frame,slot,lu',
        ]);

        try {
            $graphData = $this->loadGraphData();

            $newNode = [
                'id' => Str::uuid()->toString(),
                'label' => $request->input('label'),
                'type' => $request->input('type'),
            ];

            $graphData['nodes'][] = $newNode;
            $graphData['metadata']['modified'] = now()->toISOString();

            $this->saveGraphData($graphData);

            return $this->renderNotify('success', 'Node added successfully')
                ->header('HX-Trigger', json_encode(['reload-graph-visualization' => true]));

        } catch (\Exception $e) {
            return $this->renderNotify('error', 'Failed to add node: '.$e->getMessage());
        }
    }

    #[Post(path: '/graph-editor/relation')]
    public function saveRelation(Request $request)
    {
        $request->validate([
            'from' => 'required|string',
            'to' => 'required|string',
            'label' => 'nullable|string|max:255',
        ]);

        try {
            $graphData = $this->loadGraphData();

            // Verify that both nodes exist
            $fromExists = collect($graphData['nodes'])->contains('id', $request->input('from'));
            $toExists = collect($graphData['nodes'])->contains('id', $request->input('to'));

            if (! $fromExists || ! $toExists) {
                return $this->renderNotify('error', 'One or both nodes do not exist');
            }

            $newEdge = [
                'id' => Str::uuid()->toString(),
                'from' => $request->input('from'),
                'to' => $request->input('to'),
                'label' => $request->input('label', ''),
            ];

            $graphData['edges'][] = $newEdge;
            $graphData['metadata']['modified'] = now()->toISOString();

            $this->saveGraphData($graphData);

            return $this->renderNotify('success', 'Relation added successfully')
                ->header('HX-Trigger', json_encode(['reload-graph-visualization' => true]));

        } catch (\Exception $e) {
            return $this->renderNotify('error', 'Failed to add relation: '.$e->getMessage());
        }
    }

    #[Post(path: '/graph-editor/delete-node')]
    public function deleteNode(Request $request)
    {
        $request->validate([
            'nodeId' => 'required|string',
        ]);

        try {
            $graphData = $this->loadGraphData();
            $nodeId = $request->input('nodeId');

            // Remove the node
            $graphData['nodes'] = array_values(array_filter($graphData['nodes'], function ($node) use ($nodeId) {
                return $node['id'] !== $nodeId;
            }));

            // Remove all edges connected to this node
            $graphData['edges'] = array_values(array_filter($graphData['edges'], function ($edge) use ($nodeId) {
                return $edge['from'] !== $nodeId && $edge['to'] !== $nodeId;
            }));

            $graphData['metadata']['modified'] = now()->toISOString();

            $this->saveGraphData($graphData);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[Post(path: '/graph-editor/delete-edge')]
    public function deleteEdge(Request $request)
    {
        $request->validate([
            'edgeId' => 'required|string',
        ]);

        try {
            $graphData = $this->loadGraphData();
            $edgeId = $request->input('edgeId');

            // Remove the edge
            $graphData['edges'] = array_values(array_filter($graphData['edges'], function ($edge) use ($edgeId) {
                return $edge['id'] !== $edgeId;
            }));

            $graphData['metadata']['modified'] = now()->toISOString();

            $this->saveGraphData($graphData);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[Post(path: '/graph-editor/save')]
    public function saveGraph(Request $request)
    {
        try {
            $jsonData = $request->getContent();
            $graphData = json_decode($jsonData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->renderNotify('error', 'Invalid JSON data');
            }

            // Ensure metadata exists
            if (! isset($graphData['metadata'])) {
                $graphData['metadata'] = [
                    'created' => now()->toISOString(),
                    'modified' => now()->toISOString(),
                    'version' => '1.0',
                ];
            } else {
                $graphData['metadata']['modified'] = now()->toISOString();
            }

            $this->saveGraphData($graphData);

            return $this->renderNotify('success', 'Graph saved to database successfully');

        } catch (\Exception $e) {
            return $this->renderNotify('error', 'Failed to save graph: '.$e->getMessage());
        }
    }

    #[Post(path: '/graph-editor/import')]
    public function importGraph(Request $request)
    {
        try {
            $jsonData = $request->getContent();
            $graphData = json_decode($jsonData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->renderNotify('error', 'Invalid JSON data');
            }

            // Validate structure
            if (! isset($graphData['nodes']) || ! is_array($graphData['nodes'])) {
                return $this->renderNotify('error', 'Invalid graph structure: missing nodes array');
            }

            if (! isset($graphData['edges']) || ! is_array($graphData['edges'])) {
                return $this->renderNotify('error', 'Invalid graph structure: missing edges array');
            }

            // Ensure metadata exists
            if (! isset($graphData['metadata'])) {
                $graphData['metadata'] = [
                    'created' => now()->toISOString(),
                    'modified' => now()->toISOString(),
                    'version' => '1.0',
                ];
            } else {
                $graphData['metadata']['modified'] = now()->toISOString();
            }

            $this->saveGraphData($graphData);

            return $this->renderNotify('success', 'Graph imported successfully from JSON file');

        } catch (\Exception $e) {
            return $this->renderNotify('error', 'Failed to import graph: '.$e->getMessage());
        }
    }

    #[Get(path: '/graph-editor/reset')]
    public function resetGraph()
    {
        try {
            $this->initializeGraphFile();

            return $this->renderNotify('success', 'Graph cleared successfully')
                ->header('HX-Trigger', json_encode(['reload-graph-visualization' => true]));

        } catch (\Exception $e) {
            return $this->renderNotify('error', 'Failed to clear graph: '.$e->getMessage());
        }
    }

    private function loadGraphData(): array
    {
        if (! Storage::exists(self::GRAPH_STORAGE_PATH)) {
            $this->initializeGraphFile();
        }

        $jsonContent = Storage::get(self::GRAPH_STORAGE_PATH);
        $graphData = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON in graph data file');
        }

        return $graphData;
    }

    private function saveGraphData(array $graphData): void
    {
        $jsonContent = json_encode($graphData, JSON_PRETTY_PRINT);
        Storage::put(self::GRAPH_STORAGE_PATH, $jsonContent);
    }

    private function initializeGraphFile(): void
    {
        $initialData = [
            'nodes' => [],
            'edges' => [],
            'metadata' => [
                'created' => now()->toISOString(),
                'modified' => now()->toISOString(),
                'version' => '1.0',
            ],
        ];

        $this->saveGraphData($initialData);
    }
}
