<?php

namespace App\Http\Controllers\Facts;

use App\Data\Facts\CreateFactData;
use App\Data\Facts\UpdateFactData;
use App\Data\Facts\SearchFactData;
use App\Data\Facts\BrowseFactData;
use App\Http\Controllers\Controller;
use App\Services\Facts\TripletFactService;
use App\Soul\Exceptions\Neo4jException;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Post;
use Collective\Annotations\Routing\Attributes\Attributes\Put;
use Collective\Annotations\Routing\Attributes\Attributes\Delete;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Middleware(name: 'web')]
class FactController extends Controller
{
    public function __construct(
        private TripletFactService $factService
    ) {}

    /**
     * Display the main facts interface
     */
    #[Get(path: '/facts')]
    public function index()
    {
        return view('Facts.index', [
            'page_title' => 'Triplet Fact Network',
            'active_tab' => 'browse'
        ]);
    }

    /**
     * Show fact creation form
     */
    #[Get(path: '/facts/create')]
    public function create()
    {
        return view('Facts.create', [
            'page_title' => 'Create New Fact'
        ]);
    }

    /**
     * Store a new fact
     */
    #[Post(path: '/facts')]
    public function store(CreateFactData $data): JsonResponse
    {
        try {
            $result = $this->factService->createFact($data);

            return response()->json([
                'success' => true,
                'message' => 'Fact created successfully',
                'data' => $result
            ], 201);

        } catch (Neo4jException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating fact: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Show a specific fact
     */
    #[Get(path: '/facts/{fact_id}')]
    public function show(string $fact_id)
    {
        try {
            $fact = $this->factService->getFactWithRelationships($fact_id);
            
            if (!$fact) {
                return redirect('/facts')
                    ->with('error', "Fact with ID '{$fact_id}' not found");
            }

            return view('Facts.show', [
                'fact' => $fact,
                'page_title' => 'Fact: ' . $fact['statement']
            ]);

        } catch (Neo4jException $e) {
            return redirect('/facts')
                ->with('error', 'Database error: ' . $e->getMessage());
        }
    }

    /**
     * Show fact editing form
     */
    #[Get(path: '/facts/{fact_id}/edit')]
    public function edit(string $fact_id)
    {
        try {
            $fact = $this->factService->getFactWithRelationships($fact_id);
            
            if (!$fact) {
                return redirect('/facts')
                    ->with('error', "Fact with ID '{$fact_id}' not found");
            }

            return view('Facts.edit', [
                'fact' => $fact,
                'page_title' => 'Edit Fact'
            ]);

        } catch (Neo4jException $e) {
            return redirect('/facts')
                ->with('error', 'Database error: ' . $e->getMessage());
        }
    }

    /**
     * Update a fact
     */
    #[Put(path: '/facts/{fact_id}')]
    public function update(string $fact_id, UpdateFactData $data): JsonResponse
    {
        try {
            // Ensure the fact_id matches
            $data->fact_id = $fact_id;
            
            $result = $this->factService->updateFact($data);

            return response()->json([
                'success' => true,
                'message' => 'Fact updated successfully',
                'data' => $result
            ]);

        } catch (Neo4jException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating fact: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete a fact
     */
    #[Delete(path: '/facts/{fact_id}')]
    public function destroy(string $fact_id): JsonResponse
    {
        try {
            $result = $this->factService->deleteFact($fact_id);

            return response()->json([
                'success' => true,
                'message' => 'Fact deleted successfully',
                'data' => $result
            ]);

        } catch (Neo4jException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting fact: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Browse facts with filtering
     */
    #[Post(path: '/facts/browse')]
    public function browse(BrowseFactData $data)
    {
        try {
            $results = $this->factService->browseFacts($data);

            return view('Facts.Browse.grid', [
                'facts' => $results['facts'],
                'pagination' => $results['pagination'],
                'statistics' => $results['statistics'] ?? null,
                'search' => $data,
                'has_filters' => $results['has_filters'],
                'filter_summary' => $results['filter_summary']
            ])->fragment('facts-grid');

        } catch (Neo4jException $e) {
            return view('Facts.Browse.error', [
                'error' => 'Database error: ' . $e->getMessage()
            ])->fragment('facts-grid');
        }
    }

    /**
     * Search facts with advanced criteria
     */
    #[Post(path: '/facts/search')]
    public function search(SearchFactData $data): JsonResponse
    {
        try {
            $results = $this->factService->searchFacts($data);

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (Neo4jException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search error: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get fact network for visualization
     */
    #[Get(path: '/facts/{fact_id}/network')]
    public function network(string $fact_id, Request $request): JsonResponse
    {
        try {
            $depth = min(5, max(1, (int) $request->get('depth', 2)));
            $network = $this->factService->getFactNetwork($fact_id, $depth);

            return response()->json([
                'success' => true,
                'data' => $network
            ]);

        } catch (Neo4jException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Network error: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get available concepts for fact creation
     */
    #[Get(path: '/facts/concepts/available')]
    public function availableConcepts(Request $request): JsonResponse
    {
        try {
            $search = $request->get('search', '');
            $limit = min(50, max(5, (int) $request->get('limit', 20)));
            $type = $request->get('type', 'all'); // all, primitive, derived

            // Build concept search query
            $query = "MATCH (c:Concept) ";
            $params = [];

            $conditions = [];
            
            if (!empty($search)) {
                $conditions[] = "c.name CONTAINS \$search";
                $params['search'] = $search;
            }

            if ($type === 'primitive') {
                $conditions[] = "c.is_primitive = true";
            } elseif ($type === 'derived') {
                $conditions[] = "c.is_primitive = false";
            }

            if (!empty($conditions)) {
                $query .= "WHERE " . implode(" AND ", $conditions) . " ";
            }

            $query .= "RETURN c.name as name, c.is_primitive as is_primitive, c.fact_frequency as frequency, c.semantic_category as category ";
            $query .= "ORDER BY c.fact_frequency DESC, c.name ASC ";
            $query .= "LIMIT \$limit";
            $params['limit'] = $limit;

            $result = $this->factService->neo4j->run($query, $params);

            $concepts = [];
            foreach ($result as $record) {
                $concepts[] = [
                    'name' => $record->get('name'),
                    'is_primitive' => $record->get('is_primitive'),
                    'frequency' => $record->get('frequency'),
                    'category' => $record->get('category')
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $concepts
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading concepts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate triplet structure
     */
    #[Post(path: '/facts/validate-triplet')]
    public function validateTriplet(Request $request): JsonResponse
    {
        try {
            $subject = $request->get('subject');
            $predicate = $request->get('predicate');
            $object = $request->get('object');

            $errors = [];

            // Basic validation
            if (empty($subject)) {
                $errors[] = 'Subject concept is required';
            }

            if (empty($predicate)) {
                $errors[] = 'Predicate concept is required';
            }

            if (empty($object)) {
                $errors[] = 'Object concept is required';
            }

            // Check for duplicates
            $concepts = array_filter([$subject, $predicate, $object]);
            if (count($concepts) !== count(array_unique($concepts))) {
                $errors[] = 'All triplet concepts must be unique';
            }

            // Check if concepts exist
            if (empty($errors)) {
                foreach ($concepts as $concept) {
                    $result = $this->factService->neo4j->run(
                        'MATCH (c:Concept {name: $name}) RETURN count(c) as exists',
                        ['name' => $concept]
                    );

                    if ($result->first()->get('exists') === 0) {
                        $errors[] = "Concept '{$concept}' does not exist";
                    }
                }
            }

            return response()->json([
                'valid' => empty($errors),
                'errors' => $errors,
                'concepts_checked' => count($concepts)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'errors' => ['Validation error: ' . $e->getMessage()],
                'concepts_checked' => 0
            ], 500);
        }
    }

    /**
     * Get fact statistics
     */
    #[Get(path: '/facts/statistics')]
    public function statistics(): JsonResponse
    {
        try {
            $stats_query = "
                MATCH (f:FactNode)
                OPTIONAL MATCH (f)-[:INVOLVES_CONCEPT]->(c:Concept)
                RETURN 
                    count(DISTINCT f) as total_facts,
                    count(DISTINCT c) as concepts_involved,
                    avg(f.confidence) as avg_confidence,
                    count(CASE WHEN f.verified = true THEN 1 END) as verified_facts,
                    count(CASE WHEN f.has_modifiers = true THEN 1 END) as facts_with_modifiers,
                    collect(DISTINCT f.domain) as domains,
                    collect(DISTINCT f.fact_type) as fact_types
            ";

            $result = $this->factService->neo4j->run($stats_query);
            $record = $result->first();

            $statistics = [
                'total_facts' => $record->get('total_facts'),
                'concepts_involved' => $record->get('concepts_involved'),
                'avg_confidence' => round($record->get('avg_confidence'), 3),
                'verified_facts' => $record->get('verified_facts'),
                'facts_with_modifiers' => $record->get('facts_with_modifiers'),
                'domains' => array_filter($record->get('domains')),
                'fact_types' => $record->get('fact_types'),
                'verification_rate' => $record->get('total_facts') > 0 
                    ? round($record->get('verified_facts') / $record->get('total_facts') * 100, 1)
                    : 0
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export facts data
     */
    #[Get(path: '/facts/export')]
    public function export(Request $request)
    {
        try {
            $format = $request->get('format', 'json'); // json, csv, cypher
            $filters = $request->only([
                'domain', 'fact_type', 'verified', 'min_confidence'
            ]);

            // Build export query based on filters
            $conditions = [];
            $params = [];

            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    if ($key === 'min_confidence') {
                        $conditions[] = "f.confidence >= \$min_confidence";
                        $params['min_confidence'] = (float) $value;
                    } elseif ($key === 'verified') {
                        $conditions[] = "f.verified = \$verified";
                        $params['verified'] = $value === 'true';
                    } else {
                        $conditions[] = "f.{$key} = \${$key}";
                        $params[$key] = $value;
                    }
                }
            }

            $where_clause = "";
            if (!empty($conditions)) {
                $where_clause = "WHERE " . implode(" AND ", $conditions);
            }

            $query = "
                MATCH (f:FactNode)
                {$where_clause}
                OPTIONAL MATCH (f)-[r:INVOLVES_CONCEPT]->(c:Concept)
                RETURN f, collect({concept: c.name, role: r.role, sequence: r.sequence}) as concepts
                ORDER BY f.created_at DESC
            ";

            $result = $this->factService->neo4j->run($query, $params);

            $facts = [];
            foreach ($result as $record) {
                $fact_node = $record->get('f');
                $concepts = $record->get('concepts');

                $fact_data = [
                    'id' => $fact_node->getProperty('id'),
                    'statement' => $fact_node->getProperty('statement'),
                    'confidence' => $fact_node->getProperty('confidence'),
                    'domain' => $fact_node->getProperty('domain'),
                    'source' => $fact_node->getProperty('source'),
                    'verified' => $fact_node->getProperty('verified'),
                    'fact_type' => $fact_node->getProperty('fact_type'),
                    'created_at' => $fact_node->getProperty('created_at'),
                    'concepts' => $concepts
                ];

                $facts[] = $fact_data;
            }

            // Format response based on requested format
            switch ($format) {
                case 'csv':
                    return $this->exportAsCsv($facts);
                
                case 'cypher':
                    return $this->exportAsCypher($facts);
                
                default:
                    return response()->json([
                        'success' => true,
                        'data' => $facts,
                        'total' => count($facts),
                        'exported_at' => now()->toISOString()
                    ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export facts as CSV
     */
    private function exportAsCsv(array $facts)
    {
        $filename = 'facts_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($facts) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Statement', 'Subject', 'Predicate', 'Object', 
                'Confidence', 'Domain', 'Verified', 'Type', 'Created'
            ]);

            foreach ($facts as $fact) {
                $concepts = collect($fact['concepts'] ?? []);
                $subject = $concepts->firstWhere('role', 'subject')['concept'] ?? '';
                $predicate = $concepts->firstWhere('role', 'predicate')['concept'] ?? '';
                $object = $concepts->firstWhere('role', 'object')['concept'] ?? '';

                fputcsv($file, [
                    $fact['id'],
                    $fact['statement'],
                    $subject,
                    $predicate,
                    $object,
                    $fact['confidence'],
                    $fact['domain'],
                    $fact['verified'] ? 'Yes' : 'No',
                    $fact['fact_type'],
                    $fact['created_at']
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export facts as Cypher statements
     */
    private function exportAsCypher(array $facts)
    {
        $filename = 'facts_export_' . date('Y-m-d_H-i-s') . '.cypher';
        
        $headers = [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $cypher = "// Fact Network Export - " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($facts as $fact) {
            $cypher .= "// Fact: {$fact['statement']}\n";
            $cypher .= "CREATE (f:FactNode {\n";
            $cypher .= "  id: '{$fact['id']}',\n";
            $cypher .= "  statement: " . json_encode($fact['statement']) . ",\n";
            $cypher .= "  confidence: {$fact['confidence']},\n";
            $cypher .= "  verified: " . ($fact['verified'] ? 'true' : 'false') . "\n";
            $cypher .= "});\n\n";

            foreach ($fact['concepts'] as $concept) {
                $cypher .= "MERGE (c_{$concept['sequence']}:Concept {name: " . json_encode($concept['concept']) . "});\n";
                $cypher .= "MATCH (f:FactNode {id: '{$fact['id']}'}), (c:Concept {name: " . json_encode($concept['concept']) . "})\n";
                $cypher .= "CREATE (f)-[:INVOLVES_CONCEPT {role: '{$concept['role']}', sequence: {$concept['sequence']}}]->(c);\n\n";
            }
        }

        return response($cypher, 200, $headers);
    }
}