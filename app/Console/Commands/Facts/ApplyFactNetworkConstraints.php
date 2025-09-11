<?php

namespace App\Console\Commands\Facts;

use Illuminate\Console\Command;
use Laudis\Neo4j\Contracts\ClientInterface;
use Exception;

class ApplyFactNetworkConstraints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facts:neo4j-constraints 
                            {--check : Check existing constraints and indexes}
                            {--drop : Drop existing constraints (use with caution)}
                            {--force : Force application without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply Neo4j constraints and indexes for the Fact Network system';

    /**
     * Neo4j client instance
     */
    private ClientInterface $neo4j;

    /**
     * Create a new command instance.
     */
    public function __construct(ClientInterface $neo4j)
    {
        parent::__construct();
        $this->neo4j = $neo4j;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔗 Fact Network Neo4j Constraints Manager');
        $this->newLine();

        if ($this->option('check')) {
            return $this->checkConstraints();
        }

        if ($this->option('drop')) {
            return $this->dropConstraints();
        }

        return $this->applyConstraints();
    }

    /**
     * Apply all fact network constraints and indexes
     */
    private function applyConstraints(): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will create constraints and indexes for the Fact Network. Continue?')) {
                $this->warn('Operation cancelled.');
                return Command::FAILURE;
            }
        }

        $this->info('📝 Reading constraint definitions...');
        $constraintsFile = database_path('migrations/neo4j_fact_network_constraints.cypher');
        
        if (!file_exists($constraintsFile)) {
            $this->error("Constraints file not found: {$constraintsFile}");
            return Command::FAILURE;
        }

        $cypher = file_get_contents($constraintsFile);
        $statements = $this->parseCypherStatements($cypher);

        $this->info("Found {count($statements)} statements to execute");
        $this->newLine();

        $successCount = 0;
        $errorCount = 0;

        foreach ($statements as $index => $statement) {
            $this->info("Executing statement " . ($index + 1) . "/" . count($statements));
            
            try {
                $result = $this->neo4j->run($statement);
                $this->line("✅ Success");
                $successCount++;
            } catch (Exception $e) {
                $this->line("❌ Error: " . $e->getMessage());
                $errorCount++;
                
                // Continue with other statements even if one fails
                if ($this->option('verbose')) {
                    $this->warn("Statement: " . substr($statement, 0, 100) . "...");
                }
            }
        }

        $this->newLine();
        $this->info("📊 Results:");
        $this->line("✅ Successful: {$successCount}");
        $this->line("❌ Failed: {$errorCount}");

        if ($errorCount === 0) {
            $this->info('🎉 All constraints and indexes applied successfully!');
            return Command::SUCCESS;
        } else {
            $this->warn('⚠️  Some constraints failed to apply. Check the output above.');
            return Command::FAILURE;
        }
    }

    /**
     * Check existing constraints and indexes
     */
    private function checkConstraints(): int
    {
        $this->info('🔍 Checking existing Neo4j constraints and indexes...');
        $this->newLine();

        try {
            // Check constraints
            $this->info('📋 Constraints:');
            $constraintsResult = $this->neo4j->run('SHOW CONSTRAINTS');
            
            $constraintCount = 0;
            foreach ($constraintsResult as $record) {
                $name = $record->get('name');
                $type = $record->get('type');
                $entityType = $record->get('entityType');
                $labelsOrTypes = $record->get('labelsOrTypes');
                $properties = $record->get('properties');
                
                $this->line("  ✓ {$name} ({$type}) on {$entityType}({$labelsOrTypes}) properties: " . implode(', ', $properties));
                $constraintCount++;
            }
            
            if ($constraintCount === 0) {
                $this->warn('  No constraints found');
            } else {
                $this->info("  Total constraints: {$constraintCount}");
            }

            $this->newLine();

            // Check indexes
            $this->info('📇 Indexes:');
            $indexesResult = $this->neo4j->run('SHOW INDEXES');
            
            $indexCount = 0;
            foreach ($indexesResult as $record) {
                $name = $record->get('name');
                $type = $record->get('type');
                $entityType = $record->get('entityType');
                $labelsOrTypes = $record->get('labelsOrTypes');
                $properties = $record->get('properties');
                
                $this->line("  ✓ {$name} ({$type}) on {$entityType}({$labelsOrTypes}) properties: " . implode(', ', $properties));
                $indexCount++;
            }
            
            if ($indexCount === 0) {
                $this->warn('  No indexes found');
            } else {
                $this->info("  Total indexes: {$indexCount}");
            }

            $this->newLine();
            $this->info("📊 Summary: {$constraintCount} constraints, {$indexCount} indexes");

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('Failed to check constraints: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Drop existing fact network constraints
     */
    private function dropConstraints(): int
    {
        $this->warn('⚠️  WARNING: This will drop ALL existing constraints and indexes!');
        $this->warn('This is a destructive operation and cannot be undone.');
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Are you absolutely sure you want to drop all constraints?')) {
                $this->warn('Operation cancelled.');
                return Command::FAILURE;
            }

            if (!$this->confirm('Type "DELETE EVERYTHING" to confirm', false)) {
                $this->warn('Operation cancelled. You must type exactly "DELETE EVERYTHING" to confirm.');
                return Command::FAILURE;
            }
        }

        try {
            $this->info('🗑️  Dropping all constraints...');

            // Get all constraint names
            $constraintsResult = $this->neo4j->run('SHOW CONSTRAINTS');
            $constraintNames = [];
            
            foreach ($constraintsResult as $record) {
                $constraintNames[] = $record->get('name');
            }

            // Drop each constraint
            $droppedCount = 0;
            foreach ($constraintNames as $name) {
                try {
                    $this->neo4j->run("DROP CONSTRAINT `{$name}` IF EXISTS");
                    $this->line("✅ Dropped constraint: {$name}");
                    $droppedCount++;
                } catch (Exception $e) {
                    $this->line("❌ Failed to drop constraint {$name}: " . $e->getMessage());
                }
            }

            $this->newLine();
            $this->info('🗑️  Dropping non-constraint indexes...');

            // Get all index names (excluding constraint indexes)
            $indexesResult = $this->neo4j->run('SHOW INDEXES WHERE type <> "RANGE"');
            $indexNames = [];
            
            foreach ($indexesResult as $record) {
                $indexNames[] = $record->get('name');
            }

            // Drop each index
            foreach ($indexNames as $name) {
                try {
                    $this->neo4j->run("DROP INDEX `{$name}` IF EXISTS");
                    $this->line("✅ Dropped index: {$name}");
                    $droppedCount++;
                } catch (Exception $e) {
                    $this->line("❌ Failed to drop index {$name}: " . $e->getMessage());
                }
            }

            $this->newLine();
            $this->info("🎯 Dropped {$droppedCount} constraints and indexes");

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('Failed to drop constraints: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Parse Cypher statements from file content
     */
    private function parseCypherStatements(string $cypher): array
    {
        $statements = [];
        $lines = explode("\n", $cypher);
        $currentStatement = '';

        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines and comments
            if (empty($line) || str_starts_with($line, '//')) {
                continue;
            }

            $currentStatement .= $line . "\n";

            // Check if statement is complete (ends with semicolon)
            if (str_ends_with($line, ';')) {
                $statement = trim($currentStatement);
                if (!empty($statement)) {
                    $statements[] = rtrim($statement, ';');
                }
                $currentStatement = '';
            }
        }

        // Add final statement if it doesn't end with semicolon
        if (!empty(trim($currentStatement))) {
            $statements[] = trim($currentStatement);
        }

        return array_filter($statements);
    }
}
