<?php

namespace App\Console\Commands;

use Laudis\Neo4j\Contracts\ClientInterface;
use Illuminate\Console\Command;

class TestNeo4jConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'neo4j:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Neo4j connection and show environment details';

    public function __construct(
        private ClientInterface $neo4j
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Neo4j Connection Test');
        $this->info('===================');
        
        // Show connection details
        $host = env('NEO4J_HOST');
        $port = env('NEO4J_PORT');
        $username = env('NEO4J_USERNAME');
        $password = env('NEO4J_PASSWORD');
        
        $this->info("Host: {$host}");
        $this->info("Port: {$port}");
        $this->info("Username: {$username}");
        $this->info("Password: " . str_repeat('*', strlen($password)));
        $this->newLine();
        
        try {
            $this->info('Testing basic connection...');
            $result = $this->neo4j->run('RETURN "Hello Neo4j" as message, datetime() as timestamp');
            
            $record = $result->first();
            $this->info('✓ Connection successful!');
            $this->info("Message: " . $record->get('message'));
            $this->newLine();
            
            $this->info('Testing graph editor queries...');
            
            // Test loading editor data
            $loadQuery = '
                MATCH (n:GraphEditorNode)
                RETURN count(n) as nodeCount
            ';
            $loadResult = $this->neo4j->run($loadQuery);
            $nodeCount = $loadResult->first()->get('nodeCount');
            $this->info("✓ Found {$nodeCount} existing graph editor nodes");
            
            // Test edge count
            $edgeQuery = '
                MATCH ()-[r:EDITOR_RELATION]->()
                RETURN count(r) as edgeCount
            ';
            $edgeResult = $this->neo4j->run($edgeQuery);
            $edgeCount = $edgeResult->first()->get('edgeCount');
            $this->info("✓ Found {$edgeCount} existing graph editor edges");
            
            $this->newLine();
            $this->info('All tests passed! Neo4j connection is working.');
            
        } catch (\Exception $e) {
            $this->error('Connection failed: ' . $e->getMessage());
            $this->error('Exception type: ' . get_class($e));
            return 1;
        }
        
        return 0;
    }
}
