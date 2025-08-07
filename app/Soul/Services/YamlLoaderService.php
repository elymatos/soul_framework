<?php

namespace App\Soul\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use App\Soul\Contracts\GraphServiceInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * YamlLoaderService - YAML-based data loading with basic validation
 *
 * This service handles loading and processing of YAML files for the SOUL framework,
 * including primitive definitions, concept hierarchies, and procedural agent definitions.
 */
class YamlLoaderService
{
    protected GraphServiceInterface $graphService;
    protected array $config;
    protected array $loadedFiles;
    protected array $validationRules;

    public function __construct(GraphServiceInterface $graphService)
    {
        $this->graphService = $graphService;
        $this->config = Config::get('soul.yaml', []);
        $this->loadedFiles = [];
        $this->initializeValidationRules();
    }

    /**
     * Load all YAML files from the configured directory
     */
    public function loadAllYamlFiles(): array
    {
        $baseDirectory = $this->config['base_directory'] ?? storage_path('soul/yaml');
        
        if (!File::exists($baseDirectory)) {
            Log::warning("YamlLoaderService: Base directory does not exist", [
                'directory' => $baseDirectory
            ]);
            return [];
        }

        $results = [
            'loaded_files' => [],
            'errors' => [],
            'concepts_created' => 0,
            'agents_created' => 0
        ];

        try {
            $yamlFiles = File::glob($baseDirectory . '/*.{yml,yaml}', GLOB_BRACE);
            
            Log::info("YamlLoaderService: Loading YAML files", [
                'base_directory' => $baseDirectory,
                'files_found' => count($yamlFiles)
            ]);

            foreach ($yamlFiles as $filePath) {
                try {
                    $result = $this->loadYamlFile($filePath);
                    $results['loaded_files'][] = $result;
                    $results['concepts_created'] += $result['concepts_created'] ?? 0;
                    $results['agents_created'] += $result['agents_created'] ?? 0;
                    
                } catch (\Exception $e) {
                    $error = [
                        'file' => $filePath,
                        'error' => $e->getMessage()
                    ];
                    $results['errors'][] = $error;
                    
                    Log::error("YamlLoaderService: Failed to load file", $error);
                }
            }

            Log::info("YamlLoaderService: Batch loading completed", [
                'total_files' => count($yamlFiles),
                'successful' => count($results['loaded_files']),
                'errors' => count($results['errors']),
                'concepts_created' => $results['concepts_created'],
                'agents_created' => $results['agents_created']
            ]);

        } catch (\Exception $e) {
            Log::error("YamlLoaderService: Batch loading failed", [
                'base_directory' => $baseDirectory,
                'error' => $e->getMessage()
            ]);
            
            $results['errors'][] = [
                'file' => 'batch_operation',
                'error' => $e->getMessage()
            ];
        }

        return $results;
    }

    /**
     * Load a single YAML file
     */
    public function loadYamlFile(string $filePath): array
    {
        if (in_array($filePath, $this->loadedFiles)) {
            Log::debug("YamlLoaderService: File already loaded", ['file' => $filePath]);
            return ['status' => 'already_loaded', 'file' => $filePath];
        }

        Log::debug("YamlLoaderService: Loading YAML file", ['file' => $filePath]);

        try {
            // Parse YAML file
            $content = File::get($filePath);
            $data = Yaml::parse($content);

            // Validate structure
            if ($this->config['validation_strict'] ?? true) {
                $validationResult = $this->validateYamlStructure($data, $filePath);
                if (!$validationResult['valid']) {
                    throw new \InvalidArgumentException(
                        "YAML validation failed: " . implode(', ', $validationResult['errors'])
                    );
                }
            }

            // Process the data
            $result = $this->processYamlData($data, $filePath);
            $this->loadedFiles[] = $filePath;

            Log::info("YamlLoaderService: File loaded successfully", [
                'file' => basename($filePath),
                'concepts_created' => $result['concepts_created'] ?? 0,
                'agents_created' => $result['agents_created'] ?? 0
            ]);

            return $result;

        } catch (ParseException $e) {
            throw new \InvalidArgumentException(
                "YAML parse error in {$filePath}: " . $e->getMessage()
            );
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "Failed to load {$filePath}: " . $e->getMessage()
            );
        }
    }

    /**
     * Process parsed YAML data
     */
    protected function processYamlData(array $data, string $filePath): array
    {
        $result = [
            'file' => $filePath,
            'concepts_created' => 0,
            'agents_created' => 0,
            'relationships_created' => 0,
            'processing_details' => []
        ];

        // Process concepts
        if (isset($data['concepts']) && is_array($data['concepts'])) {
            $conceptResult = $this->processConcepts($data['concepts']);
            $result['concepts_created'] = $conceptResult['created'];
            $result['processing_details']['concepts'] = $conceptResult['details'];
        }

        // Process procedural agents
        if (isset($data['procedural_agents']) && is_array($data['procedural_agents'])) {
            $agentResult = $this->processProceduralAgents($data['procedural_agents']);
            $result['agents_created'] = $agentResult['created'];
            $result['processing_details']['agents'] = $agentResult['details'];
        }

        // Process relationships
        if (isset($data['relationships']) && is_array($data['relationships'])) {
            $relationshipResult = $this->processRelationships($data['relationships']);
            $result['relationships_created'] = $relationshipResult['created'];
            $result['processing_details']['relationships'] = $relationshipResult['details'];
        }

        return $result;
    }

    /**
     * Process concept definitions
     */
    protected function processConcepts(array $concepts): array
    {
        $created = 0;
        $details = [];

        foreach ($concepts as $conceptData) {
            try {
                // Basic validation
                if (!isset($conceptData['name'])) {
                    throw new \InvalidArgumentException("Concept missing required 'name' field");
                }

                // Prepare concept data for graph
                $graphData = [
                    'name' => $conceptData['name'],
                    'labels' => $conceptData['labels'] ?? ['Concept'],
                    'properties' => array_merge(
                        $conceptData['properties'] ?? [],
                        [
                            'source' => 'yaml_loader',
                            'loaded_at' => now()->toISOString()
                        ]
                    )
                ];

                // Create concept in graph
                $nodeId = $this->graphService->createConcept($graphData);
                $created++;

                $details[] = [
                    'name' => $conceptData['name'],
                    'node_id' => $nodeId,
                    'labels' => $graphData['labels']
                ];

                Log::debug("YamlLoaderService: Concept created", [
                    'name' => $conceptData['name'],
                    'node_id' => $nodeId
                ]);

            } catch (\Exception $e) {
                $details[] = [
                    'name' => $conceptData['name'] ?? 'unknown',
                    'error' => $e->getMessage()
                ];

                Log::warning("YamlLoaderService: Failed to create concept", [
                    'concept' => $conceptData['name'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }
        }

        return ['created' => $created, 'details' => $details];
    }

    /**
     * Process procedural agent definitions
     */
    protected function processProceduralAgents(array $agents): array
    {
        $created = 0;
        $details = [];

        foreach ($agents as $agentData) {
            try {
                // Basic validation
                if (!isset($agentData['name']) || !isset($agentData['code_reference'])) {
                    throw new \InvalidArgumentException(
                        "Procedural agent missing required 'name' or 'code_reference' field"
                    );
                }

                // Create procedural agent in graph
                $nodeId = $this->graphService->createProceduralAgent($agentData);
                $created++;

                $details[] = [
                    'name' => $agentData['name'],
                    'code_reference' => $agentData['code_reference'],
                    'node_id' => $nodeId
                ];

                Log::debug("YamlLoaderService: Procedural agent created", [
                    'name' => $agentData['name'],
                    'code_reference' => $agentData['code_reference'],
                    'node_id' => $nodeId
                ]);

            } catch (\Exception $e) {
                $details[] = [
                    'name' => $agentData['name'] ?? 'unknown',
                    'code_reference' => $agentData['code_reference'] ?? 'unknown',
                    'error' => $e->getMessage()
                ];

                Log::warning("YamlLoaderService: Failed to create procedural agent", [
                    'agent' => $agentData['name'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }
        }

        return ['created' => $created, 'details' => $details];
    }

    /**
     * Process relationship definitions
     */
    protected function processRelationships(array $relationships): array
    {
        $created = 0;
        $details = [];

        foreach ($relationships as $relationData) {
            try {
                // Basic validation
                if (!isset($relationData['from']) || !isset($relationData['to']) || !isset($relationData['type'])) {
                    throw new \InvalidArgumentException(
                        "Relationship missing required 'from', 'to', or 'type' field"
                    );
                }

                // Create relationship in graph
                $success = $this->graphService->createRelationship(
                    $relationData['from'],
                    $relationData['to'],
                    $relationData['type'],
                    $relationData['properties'] ?? []
                );

                if ($success) {
                    $created++;
                    $details[] = [
                        'from' => $relationData['from'],
                        'to' => $relationData['to'],
                        'type' => $relationData['type'],
                        'status' => 'created'
                    ];
                } else {
                    $details[] = [
                        'from' => $relationData['from'],
                        'to' => $relationData['to'],
                        'type' => $relationData['type'],
                        'status' => 'failed'
                    ];
                }

            } catch (\Exception $e) {
                $details[] = [
                    'from' => $relationData['from'] ?? 'unknown',
                    'to' => $relationData['to'] ?? 'unknown',
                    'type' => $relationData['type'] ?? 'unknown',
                    'error' => $e->getMessage()
                ];

                Log::warning("YamlLoaderService: Failed to create relationship", [
                    'relationship' => $relationData,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return ['created' => $created, 'details' => $details];
    }

    /**
     * Validate YAML structure
     */
    protected function validateYamlStructure(array $data, string $filePath): array
    {
        $errors = [];

        // Check for recognized top-level keys
        $validKeys = ['concepts', 'procedural_agents', 'relationships', 'metadata'];
        $invalidKeys = array_diff(array_keys($data), $validKeys);
        
        if (!empty($invalidKeys)) {
            $errors[] = "Unknown top-level keys: " . implode(', ', $invalidKeys);
        }

        // Validate concepts structure
        if (isset($data['concepts'])) {
            if (!is_array($data['concepts'])) {
                $errors[] = "'concepts' must be an array";
            } else {
                foreach ($data['concepts'] as $index => $concept) {
                    if (!is_array($concept)) {
                        $errors[] = "concepts[{$index}] must be an object";
                        continue;
                    }
                    
                    if (!isset($concept['name'])) {
                        $errors[] = "concepts[{$index}] missing required 'name' field";
                    }
                }
            }
        }

        // Validate procedural agents structure
        if (isset($data['procedural_agents'])) {
            if (!is_array($data['procedural_agents'])) {
                $errors[] = "'procedural_agents' must be an array";
            } else {
                foreach ($data['procedural_agents'] as $index => $agent) {
                    if (!is_array($agent)) {
                        $errors[] = "procedural_agents[{$index}] must be an object";
                        continue;
                    }
                    
                    if (!isset($agent['name'])) {
                        $errors[] = "procedural_agents[{$index}] missing required 'name' field";
                    }
                    
                    if (!isset($agent['code_reference'])) {
                        $errors[] = "procedural_agents[{$index}] missing required 'code_reference' field";
                    }
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Initialize validation rules (placeholder for future extension)
     */
    protected function initializeValidationRules(): void
    {
        $this->validationRules = [
            'concepts' => [
                'required' => ['name'],
                'optional' => ['labels', 'properties', 'description']
            ],
            'procedural_agents' => [
                'required' => ['name', 'code_reference'],
                'optional' => ['description', 'priority']
            ],
            'relationships' => [
                'required' => ['from', 'to', 'type'],
                'optional' => ['properties']
            ]
        ];
    }

    /**
     * Get loading statistics
     */
    public function getLoadingStatistics(): array
    {
        return [
            'loaded_files_count' => count($this->loadedFiles),
            'loaded_files' => $this->loadedFiles,
            'base_directory' => $this->config['base_directory'] ?? storage_path('soul/yaml'),
            'validation_strict' => $this->config['validation_strict'] ?? true,
            'auto_load_on_boot' => $this->config['auto_load_on_boot'] ?? false
        ];
    }

    /**
     * Check if a file has already been loaded
     */
    public function isFileLoaded(string $filePath): bool
    {
        return in_array($filePath, $this->loadedFiles);
    }

    /**
     * Clear loaded files cache
     */
    public function clearLoadedFilesCache(): void
    {
        $this->loadedFiles = [];
        Log::debug("YamlLoaderService: Loaded files cache cleared");
    }
}