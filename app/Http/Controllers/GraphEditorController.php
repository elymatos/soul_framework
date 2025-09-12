<?php

namespace App\Http\Controllers;

use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Post;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

#[Middleware(name: 'web')]
class GraphEditorController extends Controller
{
    private const GRAPH_FILE = 'graph_editor_data.json';

    #[Get(path: '/graph-editor')]
    public function index()
    {
        return view('GraphEditor.main');
    }

    #[Get(path: '/graph-editor/data')]
    public function getData()
    {
        $data = $this->loadGraphData();
        return response()->json($data);
    }

    #[Post(path: '/graph-editor/save')]
    public function saveGraph(Request $request)
    {
        $graphData = $request->json()->all();
        
        try {
            Storage::put(self::GRAPH_FILE, json_encode($graphData, JSON_PRETTY_PRINT));
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[Post(path: '/graph-editor/node')]
    public function addNode(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'label' => 'nullable|string|max:255'
        ]);

        $graphData = $this->loadGraphData();
        
        $nodeId = uniqid('node_');
        $newNode = [
            'id' => $nodeId,
            'label' => $request->input('label', $request->input('name')),
            'name' => $request->input('name')
        ];

        $graphData['nodes'][] = $newNode;
        
        try {
            Storage::put(self::GRAPH_FILE, json_encode($graphData, JSON_PRETTY_PRINT));
            return response()->json(['success' => true, 'node' => $newNode]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[Post(path: '/graph-editor/relation')]
    public function addRelation(Request $request)
    {
        $request->validate([
            'from' => 'required|string',
            'to' => 'required|string',
            'label' => 'nullable|string|max:255'
        ]);

        $graphData = $this->loadGraphData();
        
        $relationId = uniqid('edge_');
        $newRelation = [
            'id' => $relationId,
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'label' => $request->input('label', '')
        ];

        $graphData['edges'][] = $newRelation;
        
        try {
            Storage::put(self::GRAPH_FILE, json_encode($graphData, JSON_PRETTY_PRINT));
            return response()->json(['success' => true, 'relation' => $newRelation]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[Post(path: '/graph-editor/delete-node')]
    public function deleteNode(Request $request)
    {
        $request->validate([
            'nodeId' => 'required|string'
        ]);

        $nodeId = $request->input('nodeId');
        $graphData = $this->loadGraphData();
        
        // Remove the node
        $graphData['nodes'] = array_filter($graphData['nodes'], function($node) use ($nodeId) {
            return $node['id'] !== $nodeId;
        });
        
        // Remove related edges
        $graphData['edges'] = array_filter($graphData['edges'], function($edge) use ($nodeId) {
            return $edge['from'] !== $nodeId && $edge['to'] !== $nodeId;
        });
        
        // Reindex arrays
        $graphData['nodes'] = array_values($graphData['nodes']);
        $graphData['edges'] = array_values($graphData['edges']);
        
        try {
            Storage::put(self::GRAPH_FILE, json_encode($graphData, JSON_PRETTY_PRINT));
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[Get(path: '/graph-editor/reset')]
    public function resetGraph()
    {
        $emptyData = ['nodes' => [], 'edges' => []];
        
        try {
            Storage::put(self::GRAPH_FILE, json_encode($emptyData, JSON_PRETTY_PRINT));
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function loadGraphData(): array
    {
        if (Storage::exists(self::GRAPH_FILE)) {
            $content = Storage::get(self::GRAPH_FILE);
            return json_decode($content, true) ?: ['nodes' => [], 'edges' => []];
        }
        
        return ['nodes' => [], 'edges' => []];
    }
}