<?php

namespace App\Http\Controllers;

use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

#[Middleware(name: 'web')]
class GraphViewerController extends Controller
{
    private const GRAPH_STORAGE_PATH = 'graphs';

    #[Get(path: '/graph-viewer')]
    public function main()
    {
        return view('GraphViewer.main');
    }

    #[Get(path: '/graph-viewer/list')]
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

    #[Get(path: '/graph-viewer/view/{filename}')]
    public function viewGraph(string $filename)
    {
        // Sanitize filename
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

            // Transform and pass to view
            $visNetworkData = $this->transformSoulGraphToVisNetwork($graphData);

            return view('GraphViewer.viewer', [
                'filename' => $filename,
                'graphData' => $visNetworkData,
                'metadata' => $graphData['metadata'] ?? []
            ]);

        } catch (\Exception $e) {
            return $this->renderNotify('error', 'Error viewing graph: ' . $e->getMessage());
        }
    }

    #[Get(path: '/graph-viewer/load/{filename}')]
    public function loadGraph(string $filename): JsonResponse
    {
        // Sanitize filename to prevent directory traversal
        $filename = basename($filename);
        if (!str_ends_with($filename, '.json')) {
            $filename .= '.json';
        }

        $filePath = self::GRAPH_STORAGE_PATH . '/' . $filename;

        if (!Storage::exists($filePath)) {
            return response()->json(['error' => 'Graph file not found: ' . $filename], 404);
        }

        try {
            $jsonContent = Storage::get($filePath);
            $graphData = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['error' => 'Invalid JSON in file: ' . $filename], 400);
            }

            // Transform the SOUL Framework graph format to vis-network format
            $visNetworkData = $this->transformSoulGraphToVisNetwork($graphData);

            return response()->json($visNetworkData);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error loading graph: ' . $e->getMessage()], 500);
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
}