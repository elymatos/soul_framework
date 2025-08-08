<?php

namespace App\Soul\Services\FOL;

use App\Soul\Models\LogicalAxiom;
use App\Soul\Models\PsychologicalPredicate;
use App\Soul\Contracts\Neo4jService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

/**
 * AxiomParserService - Parse FOL axioms from JSON format
 *
 * This service parses FOL axioms from Gordon & Hobbs' "A Formal Theory of 
 * Commonsense Psychology" from JSON format into structured LogicalAxiom models
 * ready for integration with the SOUL framework.
 */
class AxiomParserService
{
    protected Neo4jService $neo4jService;
    protected AxiomAnalyzerService $analyzer;
    protected PredicateExtractorService $extractor;
    protected array $config;
    
    public function __construct(
        Neo4jService $neo4jService,
        AxiomAnalyzerService $analyzer,
        PredicateExtractorService $extractor
    ) {
        $this->neo4jService = $neo4jService;
        $this->analyzer = $analyzer;
        $this->extractor = $extractor;
        $this->config = Config::get('soul.fol', []);
    }
    
    /**
     * Parse axioms from JSON file
     */
    public function parseAxiomsFromJson(string $jsonPath): Collection
    {
        if (!file_exists($jsonPath)) {
            throw new \InvalidArgumentException("JSON file not found: {$jsonPath}");
        }
        
        Log::info("FOL AxiomParser: Starting to parse axioms", [
            'json_path' => $jsonPath,
            'file_size' => filesize($jsonPath)
        ]);
        
        try {
            $data = json_decode(file_get_contents($jsonPath), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
            }
            
            if (!isset($data['axioms'])) {
                throw new \InvalidArgumentException('Invalid axioms JSON structure: missing "axioms" key');
            }
            
            $axioms = collect($data['axioms'])->map(function ($axiomData, $index) {
                try {
                    return $this->parseAxiom($axiomData, $index);
                } catch (\Exception $e) {
                    Log::warning("FOL AxiomParser: Failed to parse axiom at index {$index}", [
                        'error' => $e->getMessage(),
                        'axiom_data' => $axiomData
                    ]);
                    return null;
                }
            })->filter();
            
            $this->logParsingResults($axioms, $jsonPath);
            
            return $axioms;
            
        } catch (\Exception $e) {
            Log::error("FOL AxiomParser: Failed to parse JSON file", [
                'json_path' => $jsonPath,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Parse individual axiom from JSON data
     */
    protected function parseAxiom(array $axiomData, int $index): LogicalAxiom
    {
        // Validate required fields
        $this->validateAxiomData($axiomData, $index);
        
        // Extract predicates from FOL expression
        $predicates = $this->extractor->extractPredicatesFromFOL($axiomData['fol'] ?? '');
        
        // Analyze axiom complexity and patterns
        $analysis = $this->analyzer->analyzeAxiom($axiomData, $predicates);
        
        // Create LogicalAxiom model
        $axiom = new LogicalAxiom([
            'name' => $this->generateAxiomName($axiomData),
            'axiom_id' => $axiomData['id'] ?? $axiomData['axiom_id'] ?? 'unknown_' . $index,
            'chapter' => $axiomData['chapter'] ?? null,
            'chapter_title' => $axiomData['chapter_title'] ?? null,
            'section' => $axiomData['section'] ?? null,
            'page' => $this->extractPageNumber($axiomData),
            'axiom_number' => $this->extractAxiomNumber($axiomData),
            'title' => $axiomData['title'] ?? "Axiom {$index}",
            'fol' => $axiomData['fol'] ?? '',
            'english' => $axiomData['english'] ?? $axiomData['description'] ?? '',
            'complexity' => $analysis['complexity'],
            'pattern' => $analysis['pattern'],
            'predicates' => $predicates,
            'variables' => $this->extractor->extractVariablesFromFOL($axiomData['fol'] ?? ''),
            'quantifiers' => $this->extractor->extractQuantifiersFromFOL($axiomData['fol'] ?? ''),
            'defeasible' => $analysis['defeasible'],
            'reified' => $analysis['reified'],
            'domain' => $analysis['domain'],
            'confidence' => $this->calculateConfidence($axiomData, $analysis),
            'implementation_status' => 'parsed',
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString()
        ]);
        
        // Validate the parsed axiom
        $validationErrors = $axiom->validate();
        if (!empty($validationErrors)) {
            Log::warning("FOL AxiomParser: Axiom validation warnings", [
                'axiom_id' => $axiom->axiom_id,
                'errors' => $validationErrors
            ]);
        }
        
        return $axiom;
    }
    
    /**
     * Validate axiom data structure
     */
    protected function validateAxiomData(array $axiomData, int $index): void
    {
        $required = ['fol'];
        $recommended = ['id', 'title', 'english', 'chapter'];
        
        // Check required fields
        foreach ($required as $field) {
            if (!isset($axiomData[$field]) || empty($axiomData[$field])) {
                throw new \InvalidArgumentException(
                    "Axiom at index {$index} missing required field: {$field}"
                );
            }
        }
        
        // Warn about missing recommended fields
        foreach ($recommended as $field) {
            if (!isset($axiomData[$field]) || empty($axiomData[$field])) {
                Log::info("FOL AxiomParser: Axiom missing recommended field", [
                    'index' => $index,
                    'field' => $field,
                    'axiom_id' => $axiomData['id'] ?? 'unknown'
                ]);
            }
        }
    }
    
    /**
     * Generate axiom name from data
     */
    protected function generateAxiomName(array $axiomData): string
    {
        if (!empty($axiomData['title']) && !empty($axiomData['id'])) {
            $cleanTitle = str_replace(' ', '_', strtoupper($axiomData['title']));
            $cleanTitle = preg_replace('/[^A-Z0-9_]/', '', $cleanTitle);
            return "{$cleanTitle}_{$axiomData['id']}";
        }
        
        if (!empty($axiomData['id'])) {
            return "AXIOM_{$axiomData['id']}";
        }
        
        if (!empty($axiomData['title'])) {
            $cleanTitle = str_replace(' ', '_', strtoupper($axiomData['title']));
            $cleanTitle = preg_replace('/[^A-Z0-9_]/', '', $cleanTitle);
            return "AXIOM_{$cleanTitle}_" . uniqid();
        }
        
        return 'UNNAMED_AXIOM_' . uniqid();
    }
    
    /**
     * Extract page number from various possible formats
     */
    protected function extractPageNumber(array $axiomData): ?int
    {
        // Try direct page field
        if (isset($axiomData['page']) && is_numeric($axiomData['page'])) {
            return (int) $axiomData['page'];
        }
        
        // Try to extract from page_reference
        if (isset($axiomData['page_reference'])) {
            if (preg_match('/(\d+)/', $axiomData['page_reference'], $matches)) {
                return (int) $matches[1];
            }
        }
        
        // Try to extract from source field
        if (isset($axiomData['source'])) {
            if (preg_match('/p\.?\s*(\d+)/i', $axiomData['source'], $matches)) {
                return (int) $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Extract axiom number from data
     */
    protected function extractAxiomNumber(array $axiomData): ?int
    {
        // Try direct axiom_number field
        if (isset($axiomData['axiom_number']) && is_numeric($axiomData['axiom_number'])) {
            return (int) $axiomData['axiom_number'];
        }
        
        // Try to extract from number field
        if (isset($axiomData['number']) && is_numeric($axiomData['number'])) {
            return (int) $axiomData['number'];
        }
        
        // Try to extract from ID if it contains numbers
        if (isset($axiomData['id'])) {
            if (preg_match('/(\d+)$/', $axiomData['id'], $matches)) {
                return (int) $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Calculate confidence score for axiom
     */
    protected function calculateConfidence(array $axiomData, array $analysis): float
    {
        $baseConfidence = 0.7;
        
        // Increase confidence for simpler axioms
        switch ($analysis['complexity']) {
            case 'simple':
                $baseConfidence += 0.2;
                break;
            case 'complex':
                $baseConfidence -= 0.1;
                break;
        }
        
        // Decrease confidence for defeasible axioms
        if ($analysis['defeasible']) {
            $baseConfidence -= $this->config['defeasible_handling']['default_confidence_reduction'] ?? 0.1;
        }
        
        // Increase confidence if axiom has good documentation
        if (!empty($axiomData['english']) && !empty($axiomData['title'])) {
            $baseConfidence += 0.1;
        }
        
        // Adjust based on domain
        if ($analysis['domain'] === 'psychology') {
            $baseConfidence += 0.05; // Slightly higher confidence for psychology domain
        }
        
        return min(1.0, max(0.1, $baseConfidence));
    }
    
    /**
     * Log parsing results for monitoring
     */
    protected function logParsingResults(Collection $axioms, string $jsonPath): void
    {
        $stats = [
            'source_file' => basename($jsonPath),
            'total_axioms' => $axioms->count(),
            'by_complexity' => $axioms->countBy('complexity'),
            'by_domain' => $axioms->countBy('domain'),
            'by_pattern' => $axioms->countBy('pattern'),
            'defeasible_count' => $axioms->where('defeasible', true)->count(),
            'reified_count' => $axioms->where('reified', true)->count(),
            'avg_confidence' => $axioms->avg('confidence'),
            'can_generate_agents' => $axioms->filter(fn($a) => $a->canGenerateAgent())->count()
        ];
        
        Log::info('FOL AxiomParser: Parsing completed successfully', $stats);
    }
    
    /**
     * Parse axioms from multiple JSON files
     */
    public function parseAxiomsFromJsonFiles(array $jsonPaths): Collection
    {
        $allAxioms = collect();
        $errors = [];
        
        foreach ($jsonPaths as $jsonPath) {
            try {
                $axioms = $this->parseAxiomsFromJson($jsonPath);
                $allAxioms = $allAxioms->merge($axioms);
                
                Log::info("FOL AxiomParser: Successfully parsed file", [
                    'file' => basename($jsonPath),
                    'axioms_count' => $axioms->count()
                ]);
                
            } catch (\Exception $e) {
                $errors[] = [
                    'file' => $jsonPath,
                    'error' => $e->getMessage()
                ];
                
                Log::error("FOL AxiomParser: Failed to parse file", [
                    'file' => $jsonPath,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        if (!empty($errors)) {
            Log::warning("FOL AxiomParser: Some files failed to parse", [
                'total_files' => count($jsonPaths),
                'failed_files' => count($errors),
                'errors' => $errors
            ]);
        }
        
        return $allAxioms;
    }
    
    /**
     * Get parsing statistics
     */
    public function getParsingStatistics(): array
    {
        return [
            'parser_version' => '1.0',
            'supported_formats' => ['json'],
            'validation_enabled' => $this->config['axiom_processing']['enable_validation'] ?? true,
            'max_complexity_level' => $this->config['axiom_processing']['max_complexity_level'] ?? 3,
            'timeout_per_axiom' => $this->config['axiom_processing']['timeout_per_axiom'] ?? 30
        ];
    }
}