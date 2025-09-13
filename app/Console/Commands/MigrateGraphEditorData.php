<?php

namespace App\Console\Commands;

use App\Services\SOUL\GraphService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateGraphEditorData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'graph-editor:migrate 
                            {--force : Force migration even if Neo4j already contains data}
                            {--backup : Create backup of existing Neo4j data before migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate graph editor data from JSON file to Neo4j database';

    public function __construct(
        private GraphService $graphService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Graph Editor Data Migration Tool');
        $this->info('====================================');

        // Check if JSON file exists
        $jsonFile = 'graph_editor_data.json';
        if (!Storage::exists($jsonFile)) {
            $this->error("JSON file '{$jsonFile}' not found in storage/app/");
            return 1;
        }

        // Load JSON data
        $this->info('Loading JSON data...');
        $jsonContent = Storage::get($jsonFile);
        $graphData = json_decode($jsonContent, true);

        if (!$graphData) {
            $this->error('Failed to parse JSON data or file is empty');
            return 1;
        }

        $nodeCount = count($graphData['nodes'] ?? []);
        $edgeCount = count($graphData['edges'] ?? []);
        
        $this->info("Found {$nodeCount} nodes and {$edgeCount} edges in JSON file");

        if ($nodeCount === 0 && $edgeCount === 0) {
            $this->warn('No data to migrate');
            return 0;
        }

        // Check existing Neo4j data
        $this->info('Checking existing Neo4j data...');
        $existingData = $this->graphService->loadEditorGraph();
        $existingNodes = count($existingData['nodes']);
        $existingEdges = count($existingData['edges']);

        if (($existingNodes > 0 || $existingEdges > 0) && !$this->option('force')) {
            $this->warn("Neo4j already contains {$existingNodes} nodes and {$existingEdges} edges");
            if (!$this->confirm('Do you want to replace existing data?')) {
                $this->info('Migration cancelled');
                return 0;
            }
        }

        // Create backup if requested
        if ($this->option('backup') && ($existingNodes > 0 || $existingEdges > 0)) {
            $this->info('Creating backup of existing Neo4j data...');
            $backupFile = 'graph_editor_backup_' . date('Y-m-d_H-i-s') . '.json';
            Storage::put($backupFile, json_encode($existingData, JSON_PRETTY_PRINT));
            $this->info("Backup saved as: {$backupFile}");
        }

        // Migrate data
        $this->info('Migrating data to Neo4j...');
        $this->newLine();
        
        // Ensure nodes have proper type field for backward compatibility
        if (isset($graphData['nodes'])) {
            foreach ($graphData['nodes'] as &$node) {
                if (!isset($node['type'])) {
                    $node['type'] = 'frame'; // Default to frame type
                }
            }
        }

        // Perform migration
        $result = $this->graphService->saveEditorGraph($graphData);

        if ($result['success']) {
            $stats = $result['stats'];
            $this->info('Migration completed successfully!');
            $this->info("- Nodes migrated: {$stats['nodes']}");
            $this->info("- Edges migrated: {$stats['edges']}");
            
            if (!empty($stats['errors'])) {
                $this->warn('Migration completed with some errors:');
                foreach ($stats['errors'] as $error) {
                    $this->error("  - {$error}");
                }
            }

            // Verify migration
            $this->info('Verifying migration...');
            $verifyData = $this->graphService->loadEditorGraph();
            $verifyNodeCount = count($verifyData['nodes']);
            $verifyEdgeCount = count($verifyData['edges']);
            $this->info("Neo4j now contains: {$verifyNodeCount} nodes and {$verifyEdgeCount} edges");

            // Offer to archive JSON file
            if ($this->confirm('Archive original JSON file? (rename with .migrated extension)')) {
                $archiveFile = $jsonFile . '.migrated.' . date('Y-m-d_H-i-s');
                Storage::move($jsonFile, $archiveFile);
                $this->info("JSON file archived as: {$archiveFile}");
            }

            $this->info('Migration process completed!');
            return 0;
        } else {
            $this->error('Migration failed: ' . $result['error']);
            return 1;
        }
    }
}
