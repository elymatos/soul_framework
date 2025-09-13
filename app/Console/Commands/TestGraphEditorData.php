<?php

namespace App\Console\Commands;

use App\Services\SOUL\GraphService;
use Illuminate\Console\Command;

class TestGraphEditorData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'graph-editor:test-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test graph editor data loading from Neo4j';

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
        $this->info('Testing Graph Editor Data Loading');
        $this->info('==================================');
        
        try {
            $data = $this->graphService->loadEditorGraph();
            
            $this->info('Data loaded successfully!');
            $this->info('Nodes: ' . count($data['nodes']));
            $this->info('Edges: ' . count($data['edges']));
            $this->newLine();
            
            if (!empty($data['nodes'])) {
                $this->info('Sample node data:');
                $this->info(json_encode($data['nodes'][0], JSON_PRETTY_PRINT));
            }
            
            if (!empty($data['edges'])) {
                $this->newLine();
                $this->info('Sample edge data:');
                $this->info(json_encode($data['edges'][0], JSON_PRETTY_PRINT));
            }
            
        } catch (\Exception $e) {
            $this->error('Failed to load data: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
