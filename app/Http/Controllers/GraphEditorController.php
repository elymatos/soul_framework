<?php

namespace App\Http\Controllers;

use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Post;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

#[Middleware(name: 'web')]
class GraphEditorController extends Controller
{
    private const GRAPH_STORAGE_PATH = 'graphs';

    #[Get(path: '/graph-editor')]
    public function main()
    {
        return view('GraphEditor.main');
    }

    #[Get(path: '/graph-editor/data')]
    public function data(): JsonResponse
    {
        // For now, return empty graph data - this can be enhanced to load from session/database
        return response()->json([
            'nodes' => [],
            'edges' => []
        ]);
    }

    #[Post(path: '/graph-editor/node')]
    public function createNode(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:frame,slot'
        ]);

        // In a real implementation, you would save to database/session
        // For now, just return success response using HTMX pattern
        return $this->renderNotify('success', 'Node "' . $request->label . '" created successfully')
            ->header('HX-Trigger', json_encode([
                'reload-graph-visualization' => true
            ]));
    }

    #[Post(path: '/graph-editor/relation')]
    public function createRelation(Request $request)
    {
        $request->validate([
            'from' => 'required|string',
            'to' => 'required|string',
            'label' => 'nullable|string|max:255'
        ]);

        // In a real implementation, you would save to database/session
        return $this->renderNotify('success', 'Relation created successfully')
            ->header('HX-Trigger', json_encode([
                'reload-graph-visualization' => true
            ]));
    }

    #[Post(path: '/graph-editor/delete-node')]
    public function deleteNode(Request $request): JsonResponse
    {
        $request->validate([
            'nodeId' => 'required|string'
        ]);

        // In a real implementation, you would delete from database/session
        return response()->json([
            'success' => true,
            'message' => 'Node deleted successfully'
        ]);
    }

    #[Post(path: '/graph-editor/save')]
    public function saveGraph(Request $request)
    {
        $graphData = $request->json()->all();

        // Validate the graph data structure
        if (!isset($graphData['nodes']) || !isset($graphData['edges'])) {
            return $this->renderNotify('error', 'Invalid graph data structure');
        }

        try {
            // Create storage directory if it doesn't exist
            if (!Storage::exists(self::GRAPH_STORAGE_PATH)) {
                Storage::makeDirectory(self::GRAPH_STORAGE_PATH);
            }

            // Generate filename with timestamp
            $filename = 'custom_graph_' . date('Y-m-d_H-i-s') . '.json';
            $filePath = self::GRAPH_STORAGE_PATH . '/' . $filename;

            // Add metadata
            $saveData = [
                'metadata' => [
                    'created_at' => now()->toISOString(),
                    'type' => 'custom',
                    'node_count' => count($graphData['nodes']),
                    'edge_count' => count($graphData['edges'])
                ],
                'graph' => [
                    'nodes' => $graphData['nodes'],
                    'links' => $graphData['edges']
                ]
            ];

            Storage::put($filePath, json_encode($saveData, JSON_PRETTY_PRINT));

            return $this->renderNotify('success', "Graph saved as {$filename}");
        } catch (\Exception $e) {
            return $this->renderNotify('error', 'Failed to save graph: ' . $e->getMessage());
        }
    }

    #[Post(path: '/graph-editor/import')]
    public function importGraph(Request $request)
    {
        $jsonData = $request->json()->all();

        // Validate JSON structure
        if (!isset($jsonData['nodes']) || !is_array($jsonData['nodes'])) {
            return $this->renderNotify('error', 'Invalid JSON structure: missing or invalid nodes array');
        }
        if (!isset($jsonData['edges']) || !is_array($jsonData['edges'])) {
            return $this->renderNotify('error', 'Invalid JSON structure: missing or invalid edges array');
        }

        // In a real implementation, you would import into database
        $nodeCount = count($jsonData['nodes']);
        $edgeCount = count($jsonData['edges']);

        return $this->renderNotify('success', "Graph imported successfully: {$nodeCount} nodes and {$edgeCount} edges");
    }

    #[Get(path: '/graph-editor/reset')]
    public function resetGraph()
    {
        // In a real implementation, you would clear database/session
        return $this->renderNotify('success', 'Graph cleared successfully')
            ->header('HX-Trigger', json_encode([
                'reload-graph-visualization' => true
            ]));
    }

    #[Get(path: '/graph-editor/list')]
    public function listGraphs(): JsonResponse
    {
        $graphs = [];

        if (Storage::exists(self::GRAPH_STORAGE_PATH)) {
            $files = Storage::files(self::GRAPH_STORAGE_PATH);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                    $filename = basename($file);
                    $graphs[] = [
                        'filename' => $filename,
                        'name' => pathinfo($filename, PATHINFO_FILENAME),
                        'path' => Storage::url($file),
                        'size' => Storage::size($file),
                        'modified' => Storage::lastModified($file)
                    ];
                }
            }
        }

        return response()->json($graphs);
    }

    #[Get(path: '/graph-editor/load/{filename}')]
    public function loadGraph(string $filename)
    {
        // Sanitize filename to prevent directory traversal
        $filename = basename($filename);
        if (!str_ends_with($filename, '.json')) {
            $filename .= '.json';
        }

        $filePath = self::GRAPH_STORAGE_PATH . '/' . $filename;

        if (!Storage::exists($filePath)) {
            return $this->renderNotify('error', 'Graph file not found: ' . $filename);
        }

        try {
            $jsonContent = Storage::get($filePath);
            $graphData = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->renderNotify('error', 'Invalid JSON in file: ' . $filename);
            }

            // Transform the SOUL Framework graph format to vis-network format
            $visNetworkData = $this->transformSoulGraphToVisNetwork($graphData);

            return response()->json($visNetworkData);

        } catch (\Exception $e) {
            return $this->renderNotify('error', 'Error loading graph: ' . $e->getMessage());
        }
    }


    private function transformSoulGraphToVisNetwork(array $soulGraphData): array
    {
        $visNodes = [];
        $visEdges = [];

        // Check if we have the expected graph structure
        if (!isset($soulGraphData['graph'])) {
            // If no graph key, assume this is already in vis-network format
            return [
                'nodes' => $soulGraphData['nodes'] ?? [],
                'edges' => $soulGraphData['edges'] ?? []
            ];
        }

        $graph = $soulGraphData['graph'];
        $nodes = $graph['nodes'] ?? [];
        $links = $graph['links'] ?? [];

        // Transform nodes
        foreach ($nodes as $node) {
            $visNode = [
                'id' => $node['id'],
                'label' => $node['name'] ?? $node['id'],
                'title' => $this->createNodeTooltip($node), // Tooltip for hover
                'group' => $node['group'] ?? $node['type'] ?? 'default'
            ];

            // Apply styling based on node type
            $this->applyNodeStyling($visNode, $node);

            $visNodes[] = $visNode;
        }

        // Transform links to edges
        foreach ($links as $link) {
            $visEdge = [
                'id' => ($link['source'] ?? '') . '_' . ($link['target'] ?? '') . '_' . ($link['type'] ?? ''),
                'from' => $link['source'],
                'to' => $link['target'],
                'label' => $link['type'] ?? '',
                'title' => $this->createEdgeTooltip($link),
                'width' => $this->getEdgeWidth($link),
                'dashes' => $this->shouldDashEdge($link)
            ];

            $visEdges[] = $visEdge;
        }

        return [
            'nodes' => $visNodes,
            'edges' => $visEdges
        ];
    }

    private function applyNodeStyling(array &$visNode, array $soulNode): void
    {
        $type = $soulNode['type'] ?? 'default';

        switch ($type) {
            case 'axiom':
                $visNode['shape'] = 'box';
                $visNode['color'] = [
                    'background' => '#E3F2FD',
                    'border' => '#1976D2',
                    'highlight' => ['background' => '#BBDEFB', 'border' => '#0D47A1']
                ];
                $visNode['size'] = 25;
                break;

            case 'predicate':
                $importance = $soulNode['importance'] ?? 'medium';
                $visNode['shape'] = 'dot';
                $visNode['size'] = $importance === 'high' ? 20 : ($importance === 'medium' ? 15 : 10);
                $visNode['color'] = [
                    'background' => $importance === 'high' ? '#4CAF50' : ($importance === 'medium' ? '#FF9800' : '#FFC107'),
                    'border' => $importance === 'high' ? '#2E7D32' : ($importance === 'medium' ? '#F57C00' : '#FF8F00'),
                    'highlight' => ['background' => '#81C784', 'border' => '#1B5E20']
                ];
                break;

            case 'variable':
                $visNode['shape'] = 'triangle';
                $visNode['color'] = [
                    'background' => '#9C27B0',
                    'border' => '#4A148C',
                    'highlight' => ['background' => '#CE93D8', 'border' => '#4A148C']
                ];
                $visNode['size'] = 15;
                break;

            case 'slot':
                $visNode['shape'] = 'dot';
                $visNode['size'] = 10;
                $visNode['color'] = [
                    'background' => '#90EE90',
                    'border' => '#32CD32',
                    'highlight' => ['background' => '#98FB98', 'border' => '#228B22']
                ];
                break;

            case 'frame':
            default:
                $visNode['shape'] = 'dot';
                $visNode['size'] = 20;
                $visNode['color'] = [
                    'background' => '#97C2FC',
                    'border' => '#2B7CE9',
                    'highlight' => ['background' => '#FFA500', 'border' => '#FF8C00']
                ];
                break;
        }
    }

    private function createNodeTooltip(array $node): string
    {
        $tooltip = "<b>{$node['name']}</b><br>";
        $tooltip .= "Type: " . ($node['type'] ?? 'unknown') . "<br>";

        if (isset($node['english'])) {
            $tooltip .= "Description: " . htmlspecialchars($node['english']) . "<br>";
        }

        if (isset($node['frequency'])) {
            $tooltip .= "Frequency: " . $node['frequency'] . "<br>";
        }

        if (isset($node['complexity'])) {
            $tooltip .= "Complexity: " . $node['complexity'] . "<br>";
        }

        if (isset($node['pattern'])) {
            $tooltip .= "Pattern: " . $node['pattern'] . "<br>";
        }

        return $tooltip;
    }

    private function createEdgeTooltip(array $link): string
    {
        $tooltip = "Relation: " . ($link['type'] ?? 'unknown') . "<br>";

        if (isset($link['weight'])) {
            $tooltip .= "Weight: " . $link['weight'] . "<br>";
        }

        return $tooltip;
    }

    private function getEdgeWidth(array $link): int
    {
        $weight = $link['weight'] ?? 1;
        return max(1, min(5, (int)($weight * 3)));
    }

    private function shouldDashEdge(array $link): bool
    {
        $type = $link['type'] ?? '';
        return in_array($type, ['co_occurs', 'has_variable']);
    }

    #[Get(path: '/graph-editor/migrate-graphs')]
    public function migrateGraphs()
    {
        try {
            $publicGraphsPath = public_path('graphs');
            $migratedCount = 0;

            // Create storage directory if it doesn't exist
            if (!Storage::exists(self::GRAPH_STORAGE_PATH)) {
                Storage::makeDirectory(self::GRAPH_STORAGE_PATH);
            }

            // Check if public graphs directory exists
            if (is_dir($publicGraphsPath)) {
                $files = glob($publicGraphsPath . '/*.json');

                foreach ($files as $file) {
                    $filename = basename($file);
                    $storageFilePath = self::GRAPH_STORAGE_PATH . '/' . $filename;

                    // Only copy if not already exists in storage
                    if (!Storage::exists($storageFilePath)) {
                        $content = file_get_contents($file);
                        Storage::put($storageFilePath, $content);
                        $migratedCount++;
                    }
                }
            }

            return $this->renderNotify('success', "Migrated {$migratedCount} graph files to storage");
        } catch (\Exception $e) {
            return $this->renderNotify('error', 'Migration failed: ' . $e->getMessage());
        }
    }
}