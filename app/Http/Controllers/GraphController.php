<?php

namespace App\Http\Controllers;

use App\Database\GraphCriteria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GraphController extends Controller
{
    public function index(): View
    {
        return view('graph.index');
    }

    public function getNeurons(): JsonResponse
    {
        $neurons = GraphCriteria::node('Neuron')
            ->returnClause('n, ID(n) as node_id')
            ->orderBy('n.created_at', 'DESC')
            ->limit(50)
            ->get();

        return response()->json($neurons);
    }

    public function createNeuron(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'layer' => 'required|integer|min:1|max:6',
            'activation_level' => 'nullable|numeric|between:0,1',
            'threshold' => 'nullable|numeric|between:0,1',
        ]);

        $neuron = GraphCriteria::createNode('Neuron', $validated);

        if (! $neuron) {
            return response()->json(['error' => 'Failed to create neuron'], 500);
        }

        return response()->json($neuron, 201);
    }

    public function updateNeuron(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'layer' => 'sometimes|integer|min:1|max:6',
            'activation_level' => 'sometimes|numeric|between:0,1',
            'threshold' => 'sometimes|numeric|between:0,1',
        ]);

        $updated = GraphCriteria::node('Neuron')
            ->where('ID(n)', '=', $id)
            ->update($validated);

        if ($updated === 0) {
            return response()->json(['error' => 'Neuron not found'], 404);
        }

        return response()->json(['message' => 'Neuron updated successfully']);
    }

    public function deleteNeuron(int $id): JsonResponse
    {
        $deleted = GraphCriteria::node('Neuron')
            ->where('ID(n)', '=', $id)
            ->delete();

        if ($deleted === 0) {
            return response()->json(['error' => 'Neuron not found'], 404);
        }

        return response()->json(['message' => 'Neuron deleted successfully']);
    }

    public function createRelationship(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_node_id' => 'required|integer',
            'to_node_id' => 'required|integer',
            'relationship_type' => 'required|string|in:CONNECTS_TO,ACTIVATES,INHIBITS',
            'weight' => 'nullable|numeric|between:0,1',
            'strength' => 'nullable|numeric|between:0,1',
        ]);

        $properties = array_filter([
            'weight' => $validated['weight'] ?? null,
            'strength' => $validated['strength'] ?? null,
        ]);

        $relationship = GraphCriteria::createRelation(
            $validated['from_node_id'],
            $validated['to_node_id'],
            $validated['relationship_type'],
            $properties
        );

        if (! $relationship) {
            return response()->json(['error' => 'Failed to create relationship'], 500);
        }

        return response()->json($relationship, 201);
    }

    public function getRelationships(int $nodeId): JsonResponse
    {
        $relationships = GraphCriteria::match('(n:Neuron)-[r]-(connected)')
            ->where('ID(n)', '=', $nodeId)
            ->returnClause('r, ID(startNode(r)) as start_id, ID(endNode(r)) as end_id, connected')
            ->get();

        return response()->json($relationships);
    }

    public function deleteRelationship(int $relationshipId): JsonResponse
    {
        $result = GraphCriteria::match('()-[r]->()')
            ->where('ID(r)', '=', $relationshipId)
            ->getQueryBuilder()
            ->delete('r');

        $executed = GraphCriteria::match('()-[r]->()')
            ->where('ID(r)', '=', $relationshipId)
            ->getClient()
            ->run('MATCH ()-[r]->() WHERE ID(r) = $id DELETE r', ['id' => $relationshipId]);

        $deletedCount = $executed->getSummary()->getCounters()->relationshipsDeleted();

        if ($deletedCount === 0) {
            return response()->json(['error' => 'Relationship not found'], 404);
        }

        return response()->json(['message' => 'Relationship deleted successfully']);
    }
}
