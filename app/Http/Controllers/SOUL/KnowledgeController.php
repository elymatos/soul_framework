<?php

namespace App\Http\Controllers\SOUL;

use App\Http\Controllers\Controller;
use App\Soul\Services\YamlLoaderService;
use App\Soul\Contracts\GraphServiceInterface;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Post;
use Collective\Annotations\Routing\Attributes\Attributes\Put;
use Collective\Annotations\Routing\Attributes\Attributes\Delete;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

#[Middleware(name: 'web')]
class KnowledgeController extends Controller
{
    public function __construct(
        private YamlLoaderService $yamlLoader,
        private GraphServiceInterface $graphService
    ) {}

    #[Get(path: '/soul/knowledge')]
    public function index()
    {
        try {
            $fileTree = $this->getFileTreeStructure();
            $statistics = $this->getKnowledgeStatistics();
            
            return view('SOUL.Knowledge.main', [
                'fileTree' => $fileTree,
                'statistics' => $statistics,
            ]);
        } catch (\Exception $e) {
            return redirect('/soul/browse')
                ->with('error', 'Failed to load knowledge manager: ' . $e->getMessage());
        }
    }

    #[Get(path: '/soul/knowledge/scripts/{file}')]
    public function scripts(string $file)
    {
        return response()
            ->view("SOUL.Knowledge.{$file}")
            ->header('Content-type', 'text/javascript');
    }

    #[Get(path: '/soul/knowledge/file-tree')]
    public function getFileTree()
    {
        try {
            $tree = $this->getFileTreeStructure();
            return response()->json($tree);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Get(path: '/soul/knowledge/file/{filename}')]
    public function loadFile(string $filename)
    {
        try {
            $yamlDirectory = config('soul.yaml.base_directory');
            $filePath = $yamlDirectory . '/' . $filename;
            
            if (!File::exists($filePath)) {
                return response()->json(['error' => 'File not found'], 404);
            }
            
            $content = File::get($filePath);
            $parsed = Yaml::parse($content);
            
            // Validate the YAML structure
            $validation = $this->validateYamlStructure($parsed);
            
            return response()->json([
                'filename' => $filename,
                'content' => $content,
                'parsed' => $parsed,
                'validation' => $validation,
                'lastModified' => File::lastModified($filePath),
                'size' => File::size($filePath)
            ]);
        } catch (ParseException $e) {
            return response()->json([
                'error' => 'YAML parsing error',
                'message' => $e->getMessage(),
                'line' => $e->getParsedLine()
            ], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Post(path: '/soul/knowledge/file')]
    public function saveFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filename' => 'required|string',
            'content' => 'required|string',
            'createBackup' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $yamlDirectory = config('soul.yaml.base_directory');
            $filePath = $yamlDirectory . '/' . $request->filename;
            
            // Create backup if requested
            if ($request->createBackup && File::exists($filePath)) {
                $backupPath = $filePath . '.backup.' . now()->format('Y-m-d_H-i-s');
                File::copy($filePath, $backupPath);
            }
            
            // Validate YAML before saving
            try {
                $parsed = Yaml::parse($request->content);
                $validation = $this->validateYamlStructure($parsed);
                
                if (!$validation['valid']) {
                    return response()->json([
                        'error' => 'YAML validation failed',
                        'validation' => $validation
                    ], 422);
                }
            } catch (ParseException $e) {
                return response()->json([
                    'error' => 'Invalid YAML syntax',
                    'message' => $e->getMessage(),
                    'line' => $e->getParsedLine()
                ], 422);
            }
            
            // Ensure directory exists
            $directory = dirname($filePath);
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            
            File::put($filePath, $request->content);
            
            return response()->json([
                'success' => true,
                'message' => 'File saved successfully',
                'validation' => $validation,
                'lastModified' => File::lastModified($filePath)
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Post(path: '/soul/knowledge/validate')]
    public function validateFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'strict' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $parsed = Yaml::parse($request->content);
            $validation = $this->validateYamlStructure($parsed, $request->boolean('strict', true));
            
            return response()->json($validation);
        } catch (ParseException $e) {
            return response()->json([
                'valid' => false,
                'error' => 'YAML parsing error',
                'message' => $e->getMessage(),
                'line' => $e->getParsedLine()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'error' => 'Validation error',
                'message' => $e->getMessage()
            ]);
        }
    }

    #[Get(path: '/soul/knowledge/dependency-graph')]
    public function getDependencyGraph()
    {
        try {
            $yamlDirectory = config('soul.yaml.base_directory');
            $files = File::allFiles($yamlDirectory);
            
            $nodes = [];
            $links = [];
            $fileGroups = [];
            
            foreach ($files as $file) {
                if ($file->getExtension() !== 'yml' && $file->getExtension() !== 'yaml') {
                    continue;
                }
                
                $relativePath = str_replace($yamlDirectory . '/', '', $file->getPathname());
                $content = File::get($file->getPathname());
                
                try {
                    $parsed = Yaml::parse($content);
                    $this->processFileForDependencyGraph($parsed, $relativePath, $nodes, $links, $fileGroups);
                } catch (ParseException $e) {
                    // Skip invalid YAML files but log them
                    continue;
                }
            }
            
            // Detect circular dependencies
            $circularDeps = $this->detectCircularDependencies($links);
            
            return response()->json([
                'nodes' => array_values($nodes),
                'links' => $links,
                'fileGroups' => $fileGroups,
                'circularDependencies' => $circularDeps,
                'statistics' => [
                    'totalConcepts' => count($nodes),
                    'totalRelationships' => count($links),
                    'totalFiles' => count($fileGroups)
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Post(path: '/soul/knowledge/preview-changes')]
    public function previewChanges(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filename' => 'required|string',
            'newContent' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $yamlDirectory = config('soul.yaml.base_directory');
            $filePath = $yamlDirectory . '/' . $request->filename;
            
            $changes = [
                'concepts' => ['added' => [], 'modified' => [], 'removed' => []],
                'relationships' => ['added' => [], 'modified' => [], 'removed' => []],
                'agents' => ['added' => [], 'modified' => [], 'removed' => []]
            ];
            
            // Get current content if file exists
            $currentContent = File::exists($filePath) ? File::get($filePath) : '';
            
            if ($currentContent) {
                $currentParsed = Yaml::parse($currentContent);
                $newParsed = Yaml::parse($request->newContent);
                
                $changes = $this->calculateYamlDiff($currentParsed, $newParsed);
            } else {
                // New file - everything is added
                $newParsed = Yaml::parse($request->newContent);
                if (isset($newParsed['concepts'])) {
                    $changes['concepts']['added'] = $newParsed['concepts'];
                }
                if (isset($newParsed['relationships'])) {
                    $changes['relationships']['added'] = $newParsed['relationships'];
                }
                if (isset($newParsed['procedural_agents'])) {
                    $changes['agents']['added'] = $newParsed['procedural_agents'];
                }
            }
            
            return response()->json($changes);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Post(path: '/soul/knowledge/upload')]
    public function uploadFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'files.*' => 'required|file|mimes:yml,yaml',
            'overwrite' => 'boolean',
            'validate' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $yamlDirectory = config('soul.yaml.base_directory');
            $results = [];
            
            foreach ($request->file('files', []) as $uploadedFile) {
                $filename = $uploadedFile->getClientOriginalName();
                $filePath = $yamlDirectory . '/' . $filename;
                
                // Check if file exists and overwrite is not allowed
                if (File::exists($filePath) && !$request->boolean('overwrite', false)) {
                    $results[] = [
                        'filename' => $filename,
                        'success' => false,
                        'error' => 'File already exists and overwrite is disabled'
                    ];
                    continue;
                }
                
                $content = $uploadedFile->getContent();
                
                // Validate if requested
                if ($request->boolean('validate', true)) {
                    try {
                        $parsed = Yaml::parse($content);
                        $validation = $this->validateYamlStructure($parsed);
                        
                        if (!$validation['valid']) {
                            $results[] = [
                                'filename' => $filename,
                                'success' => false,
                                'error' => 'YAML validation failed',
                                'validation' => $validation
                            ];
                            continue;
                        }
                    } catch (ParseException $e) {
                        $results[] = [
                            'filename' => $filename,
                            'success' => false,
                            'error' => 'Invalid YAML syntax: ' . $e->getMessage()
                        ];
                        continue;
                    }
                }
                
                File::put($filePath, $content);
                
                $results[] = [
                    'filename' => $filename,
                    'success' => true,
                    'size' => File::size($filePath)
                ];
            }
            
            return response()->json(['results' => $results]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Get(path: '/soul/knowledge/export')]
    public function exportFiles(Request $request)
    {
        try {
            $files = $request->input('files', []);
            $yamlDirectory = config('soul.yaml.base_directory');
            
            if (empty($files)) {
                // Export all files
                $allFiles = File::allFiles($yamlDirectory);
                $files = array_map(function ($file) use ($yamlDirectory) {
                    return str_replace($yamlDirectory . '/', '', $file->getPathname());
                }, $allFiles);
            }
            
            $zipPath = storage_path('app/soul_knowledge_export_' . now()->format('Y-m-d_H-i-s') . '.zip');
            $zip = new \ZipArchive();
            
            if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
                foreach ($files as $file) {
                    $filePath = $yamlDirectory . '/' . $file;
                    if (File::exists($filePath)) {
                        $zip->addFile($filePath, $file);
                    }
                }
                $zip->close();
                
                return response()->download($zipPath, 'soul_knowledge_export.zip')->deleteFileAfterSend();
            } else {
                return response()->json(['error' => 'Failed to create export archive'], 500);
            }
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Delete(path: '/soul/knowledge/file/{filename}')]
    public function deleteFile(string $filename)
    {
        try {
            $yamlDirectory = config('soul.yaml.base_directory');
            $filePath = $yamlDirectory . '/' . $filename;
            
            if (!File::exists($filePath)) {
                return response()->json(['error' => 'File not found'], 404);
            }
            
            // Create backup before deletion
            $backupPath = $filePath . '.deleted.' . now()->format('Y-m-d_H-i-s');
            File::copy($filePath, $backupPath);
            
            File::delete($filePath);
            
            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully',
                'backup' => basename($backupPath)
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Post(path: '/soul/knowledge/load-yaml')]
    public function loadYaml(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'files' => 'array',
            'files.*' => 'string',
            'force' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $files = $request->input('files', []);
            $force = $request->boolean('force', false);
            
            if (empty($files)) {
                $results = $this->yamlLoader->loadAllYamlFiles();
            } else {
                $results = [];
                foreach ($files as $file) {
                    $result = $this->yamlLoader->loadYamlFile(
                        config('soul.yaml.base_directory') . '/' . $file,
                        $force
                    );
                    $results[] = array_merge($result, ['filename' => $file]);
                }
            }
            
            return response()->json([
                'success' => true,
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Private helper methods

    private function getFileTreeStructure(): array
    {
        $yamlDirectory = config('soul.yaml.base_directory');
        
        if (!File::exists($yamlDirectory)) {
            File::makeDirectory($yamlDirectory, 0755, true);
        }
        
        return $this->buildFileTree($yamlDirectory, $yamlDirectory);
    }

    private function buildFileTree(string $path, string $basePath): array
    {
        $tree = [];
        $items = File::glob($path . '/*');
        
        foreach ($items as $item) {
            $name = basename($item);
            $relativePath = str_replace($basePath . '/', '', $item);
            
            if (File::isDirectory($item)) {
                $tree[] = [
                    'name' => $name,
                    'type' => 'directory',
                    'path' => $relativePath,
                    'children' => $this->buildFileTree($item, $basePath)
                ];
            } elseif (in_array(File::extension($item), ['yml', 'yaml'])) {
                $tree[] = [
                    'name' => $name,
                    'type' => 'file',
                    'path' => $relativePath,
                    'size' => File::size($item),
                    'modified' => File::lastModified($item),
                    'extension' => File::extension($item)
                ];
            }
        }
        
        return $tree;
    }

    private function getKnowledgeStatistics(): array
    {
        try {
            $yamlDirectory = config('soul.yaml.base_directory');
            $files = File::allFiles($yamlDirectory);
            
            $stats = [
                'totalFiles' => 0,
                'totalConcepts' => 0,
                'totalRelationships' => 0,
                'totalAgents' => 0,
                'filesByType' => [],
                'conceptsByType' => [],
                'lastModified' => null
            ];
            
            $latestModification = 0;
            
            foreach ($files as $file) {
                if (!in_array($file->getExtension(), ['yml', 'yaml'])) {
                    continue;
                }
                
                $stats['totalFiles']++;
                $modified = File::lastModified($file->getPathname());
                
                if ($modified > $latestModification) {
                    $latestModification = $modified;
                    $stats['lastModified'] = [
                        'timestamp' => $modified,
                        'file' => str_replace($yamlDirectory . '/', '', $file->getPathname())
                    ];
                }
                
                try {
                    $content = File::get($file->getPathname());
                    $parsed = Yaml::parse($content);
                    
                    if (isset($parsed['concepts'])) {
                        $stats['totalConcepts'] += count($parsed['concepts']);
                        
                        foreach ($parsed['concepts'] as $concept) {
                            $type = $concept['properties']['type'] ?? 'unknown';
                            $stats['conceptsByType'][$type] = ($stats['conceptsByType'][$type] ?? 0) + 1;
                        }
                    }
                    
                    if (isset($parsed['relationships'])) {
                        $stats['totalRelationships'] += count($parsed['relationships']);
                    }
                    
                    if (isset($parsed['procedural_agents'])) {
                        $stats['totalAgents'] += count($parsed['procedural_agents']);
                    }
                    
                } catch (ParseException $e) {
                    // Skip invalid files but count them
                    continue;
                }
            }
            
            return $stats;
            
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'totalFiles' => 0,
                'totalConcepts' => 0,
                'totalRelationships' => 0,
                'totalAgents' => 0
            ];
        }
    }

    private function validateYamlStructure(array $data, bool $strict = true): array
    {
        $validation = [
            'valid' => true,
            'errors' => [],
            'warnings' => []
        ];

        // Check required top-level keys
        $requiredKeys = ['metadata'];
        foreach ($requiredKeys as $key) {
            if (!isset($data[$key])) {
                $validation['errors'][] = "Missing required key: {$key}";
                $validation['valid'] = false;
            }
        }

        // Validate metadata
        if (isset($data['metadata'])) {
            $metadataRequired = ['title', 'version', 'description'];
            foreach ($metadataRequired as $key) {
                if (!isset($data['metadata'][$key])) {
                    $validation['errors'][] = "Missing metadata key: {$key}";
                    if ($strict) {
                        $validation['valid'] = false;
                    }
                }
            }
        }

        // Validate concepts
        if (isset($data['concepts'])) {
            if (!is_array($data['concepts'])) {
                $validation['errors'][] = "Concepts must be an array";
                $validation['valid'] = false;
            } else {
                foreach ($data['concepts'] as $index => $concept) {
                    $this->validateConcept($concept, $index, $validation, $strict);
                }
            }
        }

        // Validate procedural agents
        if (isset($data['procedural_agents'])) {
            if (!is_array($data['procedural_agents'])) {
                $validation['errors'][] = "Procedural agents must be an array";
                $validation['valid'] = false;
            } else {
                foreach ($data['procedural_agents'] as $index => $agent) {
                    $this->validateProceduralAgent($agent, $index, $validation, $strict);
                }
            }
        }

        // Validate relationships
        if (isset($data['relationships'])) {
            if (!is_array($data['relationships'])) {
                $validation['errors'][] = "Relationships must be an array";
                $validation['valid'] = false;
            } else {
                foreach ($data['relationships'] as $index => $relationship) {
                    $this->validateRelationship($relationship, $index, $validation, $strict);
                }
            }
        }

        return $validation;
    }

    private function validateConcept(array $concept, int $index, array &$validation, bool $strict): void
    {
        $required = ['name', 'labels', 'properties'];
        
        foreach ($required as $field) {
            if (!isset($concept[$field])) {
                $validation['errors'][] = "Concept {$index}: Missing required field '{$field}'";
                $validation['valid'] = false;
            }
        }
        
        if (isset($concept['labels']) && !is_array($concept['labels'])) {
            $validation['errors'][] = "Concept {$index}: Labels must be an array";
            $validation['valid'] = false;
        }
        
        if (isset($concept['properties']) && !is_array($concept['properties'])) {
            $validation['errors'][] = "Concept {$index}: Properties must be an array";
            $validation['valid'] = false;
        }
    }

    private function validateProceduralAgent(array $agent, int $index, array &$validation, bool $strict): void
    {
        $required = ['name', 'code_reference', 'description'];
        
        foreach ($required as $field) {
            if (!isset($agent[$field])) {
                $validation['errors'][] = "Procedural agent {$index}: Missing required field '{$field}'";
                $validation['valid'] = false;
            }
        }
        
        if (isset($agent['priority']) && !is_numeric($agent['priority'])) {
            $validation['warnings'][] = "Procedural agent {$index}: Priority should be numeric";
        }
    }

    private function validateRelationship(array $relationship, int $index, array &$validation, bool $strict): void
    {
        $required = ['from', 'to', 'type'];
        
        foreach ($required as $field) {
            if (!isset($relationship[$field])) {
                $validation['errors'][] = "Relationship {$index}: Missing required field '{$field}'";
                $validation['valid'] = false;
            }
        }
        
        if (isset($relationship['properties']) && !is_array($relationship['properties'])) {
            $validation['errors'][] = "Relationship {$index}: Properties must be an array";
            $validation['valid'] = false;
        }
    }

    private function processFileForDependencyGraph(array $data, string $filename, array &$nodes, array &$links, array &$fileGroups): void
    {
        $fileGroup = pathinfo($filename, PATHINFO_DIRNAME);
        if (!isset($fileGroups[$fileGroup])) {
            $fileGroups[$fileGroup] = [
                'name' => $fileGroup,
                'files' => [],
                'conceptCount' => 0
            ];
        }
        
        $fileGroups[$fileGroup]['files'][] = $filename;
        
        // Process concepts
        if (isset($data['concepts'])) {
            foreach ($data['concepts'] as $concept) {
                $conceptName = $concept['name'];
                
                if (!isset($nodes[$conceptName])) {
                    $nodes[$conceptName] = [
                        'id' => $conceptName,
                        'name' => $conceptName,
                        'type' => $concept['properties']['type'] ?? 'unknown',
                        'domain' => $concept['properties']['domain'] ?? 'general',
                        'file' => $filename,
                        'fileGroup' => $fileGroup,
                        'description' => $concept['properties']['description'] ?? '',
                        'labels' => $concept['labels'] ?? []
                    ];
                    
                    $fileGroups[$fileGroup]['conceptCount']++;
                }
            }
        }
        
        // Process relationships
        if (isset($data['relationships'])) {
            foreach ($data['relationships'] as $relationship) {
                $links[] = [
                    'source' => $relationship['from'],
                    'target' => $relationship['to'],
                    'type' => $relationship['type'],
                    'strength' => $relationship['properties']['strength'] ?? 1.0,
                    'file' => $filename
                ];
            }
        }
    }

    private function detectCircularDependencies(array $links): array
    {
        $graph = [];
        
        // Build adjacency list
        foreach ($links as $link) {
            if (!isset($graph[$link['source']])) {
                $graph[$link['source']] = [];
            }
            $graph[$link['source']][] = $link['target'];
        }
        
        $visited = [];
        $recursionStack = [];
        $cycles = [];
        
        foreach (array_keys($graph) as $node) {
            if (!isset($visited[$node])) {
                $this->detectCycleDFS($node, $graph, $visited, $recursionStack, $cycles, []);
            }
        }
        
        return $cycles;
    }

    private function detectCycleDFS(string $node, array $graph, array &$visited, array &$recursionStack, array &$cycles, array $path): void
    {
        $visited[$node] = true;
        $recursionStack[$node] = true;
        $path[] = $node;
        
        if (isset($graph[$node])) {
            foreach ($graph[$node] as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $this->detectCycleDFS($neighbor, $graph, $visited, $recursionStack, $cycles, $path);
                } elseif (isset($recursionStack[$neighbor]) && $recursionStack[$neighbor]) {
                    // Found a cycle
                    $cycleStart = array_search($neighbor, $path);
                    $cycle = array_slice($path, $cycleStart);
                    $cycle[] = $neighbor; // Complete the cycle
                    $cycles[] = $cycle;
                }
            }
        }
        
        $recursionStack[$node] = false;
    }

    private function calculateYamlDiff(array $current, array $new): array
    {
        $diff = [
            'concepts' => ['added' => [], 'modified' => [], 'removed' => []],
            'relationships' => ['added' => [], 'modified' => [], 'removed' => []],
            'agents' => ['added' => [], 'modified' => [], 'removed' => []]
        ];

        // Compare concepts
        $currentConcepts = $current['concepts'] ?? [];
        $newConcepts = $new['concepts'] ?? [];
        
        $currentConceptNames = array_column($currentConcepts, 'name');
        $newConceptNames = array_column($newConcepts, 'name');
        
        // Find added concepts
        foreach ($newConcepts as $concept) {
            if (!in_array($concept['name'], $currentConceptNames)) {
                $diff['concepts']['added'][] = $concept;
            }
        }
        
        // Find removed concepts
        foreach ($currentConcepts as $concept) {
            if (!in_array($concept['name'], $newConceptNames)) {
                $diff['concepts']['removed'][] = $concept;
            }
        }
        
        // Find modified concepts
        foreach ($newConcepts as $newConcept) {
            if (in_array($newConcept['name'], $currentConceptNames)) {
                $currentConcept = collect($currentConcepts)->firstWhere('name', $newConcept['name']);
                if (json_encode($currentConcept) !== json_encode($newConcept)) {
                    $diff['concepts']['modified'][] = [
                        'name' => $newConcept['name'],
                        'current' => $currentConcept,
                        'new' => $newConcept
                    ];
                }
            }
        }
        
        // Similar logic for relationships and agents
        $this->compareArraySection($current, $new, 'relationships', $diff, 'from', 'to', 'type');
        $this->compareArraySection($current, $new, 'procedural_agents', $diff, 'name', null, null, 'agents');
        
        return $diff;
    }

    private function compareArraySection(array $current, array $new, string $section, array &$diff, string $keyField, ?string $keyField2 = null, ?string $keyField3 = null, ?string $diffSection = null): void
    {
        $diffSection = $diffSection ?? $section;
        $currentItems = $current[$section] ?? [];
        $newItems = $new[$section] ?? [];
        
        // Create unique keys for comparison
        $createKey = function($item) use ($keyField, $keyField2, $keyField3) {
            $key = $item[$keyField] ?? '';
            if ($keyField2) $key .= '|' . ($item[$keyField2] ?? '');
            if ($keyField3) $key .= '|' . ($item[$keyField3] ?? '');
            return $key;
        };
        
        $currentKeys = array_map($createKey, $currentItems);
        $newKeys = array_map($createKey, $newItems);
        
        // Find added items
        foreach ($newItems as $item) {
            $key = $createKey($item);
            if (!in_array($key, $currentKeys)) {
                $diff[$diffSection]['added'][] = $item;
            }
        }
        
        // Find removed items
        foreach ($currentItems as $item) {
            $key = $createKey($item);
            if (!in_array($key, $newKeys)) {
                $diff[$diffSection]['removed'][] = $item;
            }
        }
        
        // Find modified items
        foreach ($newItems as $newItem) {
            $key = $createKey($newItem);
            if (in_array($key, $currentKeys)) {
                $currentIndex = array_search($key, $currentKeys);
                $currentItem = $currentItems[$currentIndex];
                if (json_encode($currentItem) !== json_encode($newItem)) {
                    $diff[$diffSection]['modified'][] = [
                        'key' => $key,
                        'current' => $currentItem,
                        'new' => $newItem
                    ];
                }
            }
        }
    }
}