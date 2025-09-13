<?php

namespace App\Http\Controllers;

use App\Services\SOUL\GraphService;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Post;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Exception;
use Illuminate\Http\Request;

#[Middleware(name: 'web')]
class GraphEditorController extends Controller
{
    public function __construct(
        private GraphService $graphService
    ) {}

    #[Get(path: '/graph-editor')]
    public function index()
    {
        return view('GraphEditor.main');
    }

    #[Get(path: '/graph-editor/data')]
    public function getData()
    {
        $data = $this->graphService->loadEditorGraph();
        return response()->json($data);
    }

    #[Post(path: '/graph-editor/save')]
    public function saveGraph(Request $request)
    {
        $graphData = $request->json()->all();
        
        $result = $this->graphService->saveEditorGraph($graphData);
        
        if ($result['success']) {
            $stats = $result['stats'];
            $message = "Graph saved successfully! ({$stats['nodes']} nodes, {$stats['edges']} edges)";
            if (!empty($stats['errors'])) {
                $message .= " with " . count($stats['errors']) . " errors.";
            }
            return $this->renderNotify("success", $message);
        } else {
            return $this->renderNotify("error", "Failed to save graph: " . $result['error']);
        }
    }

    #[Post(path: '/graph-editor/node')]
    public function addNode(Request $request)
    {
        try {
            $request->validate([
                'label' => 'required|string|max:255',
                'type' => 'required|string|in:frame,slot'
            ]);
            
            $nodeId = uniqid('node_');
            $label = $request->input('label');
            $type = $request->input('type');
            $nodeData = [
                'id' => $nodeId,
                'label' => $label,
                'name' => $label, // Auto-generate name from label
                'type' => $type
            ];

            $result = $this->graphService->addEditorNode($nodeData);
            
            if ($result['success']) {
                $this->trigger('reload-graph-visualization');
                return $this->renderNotify("success", "Node '{$label}' added successfully!");
            } else {
                return $this->renderNotify("error", "Failed to add node: " . $result['error']);
            }
        } catch (\Exception $e) {
            return $this->renderNotify("error", "Error adding node: " . $e->getMessage());
        }
    }

    #[Post(path: '/graph-editor/relation')]
    public function addRelation(Request $request)
    {
        try {
            $request->validate([
                'from' => 'required|string',
                'to' => 'required|string',
                'label' => 'nullable|string|max:255'
            ]);
            
            $relationId = uniqid('edge_');
            $relationData = [
                'id' => $relationId,
                'from' => $request->input('from'),
                'to' => $request->input('to'),
                'label' => $request->input('label', '')
            ];

            $result = $this->graphService->addEditorRelation($relationData);
            
            if ($result['success']) {
                $label = $relationData['label'] ?: 'unlabeled';
                $this->trigger('reload-graph-visualization');
                return $this->renderNotify("success", "Relation '$label' added successfully!");
            } else {
                return $this->renderNotify("error", "Failed to add relation: " . $result['error']);
            }
        } catch (\Exception $e) {
            return $this->renderNotify("error", "Error adding relation: " . $e->getMessage());
        }
    }

    #[Post(path: '/graph-editor/delete-node')]
    public function deleteNode(Request $request)
    {
        try {
            $request->validate([
                'nodeId' => 'required|string'
            ]);

            $nodeId = $request->input('nodeId');
            $result = $this->graphService->deleteEditorNode($nodeId);
            
            if ($result['success']) {
                $this->trigger('reload-graph-visualization');
                return $this->renderNotify("success", "Node and its connections deleted successfully!");
            } else {
                return $this->renderNotify("error", "Failed to delete node: " . $result['error']);
            }
        } catch (\Exception $e) {
            return $this->renderNotify("error", "Error deleting node: " . $e->getMessage());
        }
    }

    #[Get(path: '/graph-editor/reset')]
    public function resetGraph()
    {
        try {
            $result = $this->graphService->resetEditorGraph();
            
            if ($result['success']) {
                $this->trigger('reload-graph-visualization');
                return $this->renderNotify("success", "Graph cleared successfully!");
            } else {
                return $this->renderNotify("error", "Failed to clear graph: " . $result['error']);
            }
        } catch (\Exception $e) {
            return $this->renderNotify("error", "Error clearing graph: " . $e->getMessage());
        }
    }
}