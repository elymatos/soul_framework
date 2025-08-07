<?php

namespace App\Console\Commands\Soul;

use Illuminate\Console\Command;
use Laudis\Neo4j\Contracts\ClientInterface;
use Illuminate\Support\Facades\Log;

class ApplyNeo4jConstraints extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'soul:neo4j-constraints 
                            {--drop : Drop existing constraints before creating new ones}
                            {--check : Only check existing constraints and indexes}';

    /**
     * The console command description.
     */
    protected $description = 'Apply Neo4j constraints and indexes for SOUL Framework';

    protected ClientInterface $neo4j;

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
        $this->info('SOUL Framework - Neo4j Constraints Management');
        $this->info('=============================================');

        try {
            // Check if we should only verify existing constraints
            if ($this->option('check')) {
                $this->checkConstraintsAndIndexes();
                return Command::SUCCESS;
            }

            // Drop existing constraints if requested
            if ($this->option('drop')) {
                $this->warn('Dropping existing constraints...');
                $this->dropExistingConstraints();
            }

            // Apply new constraints and indexes
            $this->info('Applying Neo4j constraints and indexes...');
            $this->applyConstraintsAndIndexes();

            $this->info('✅ Neo4j constraints and indexes applied successfully!');
            
            // Verify the results
            $this->checkConstraintsAndIndexes();

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to apply Neo4j constraints: ' . $e->getMessage());
            Log::error('Neo4j constraints application failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Apply all constraints and indexes
     */
    protected function applyConstraintsAndIndexes(): void
    {
        $constraintsFile = database_path('migrations/neo4j_soul_constraints.cypher');
        
        if (!file_exists($constraintsFile)) {
            throw new \Exception("Constraints file not found: {$constraintsFile}");
        }

        $cypherContent = file_get_contents($constraintsFile);
        $statements = $this->parseCypherStatements($cypherContent);

        $this->info("Found " . count($statements) . " Cypher statements to execute");

        $successCount = 0;
        $skipCount = 0;
        $errorCount = 0;

        foreach ($statements as $index => $statement) {
            $trimmedStatement = trim($statement);
            
            // Skip empty statements and comments
            if (empty($trimmedStatement) || str_starts_with($trimmedStatement, '//')) {
                continue;
            }

            $this->line("Executing statement " . ($index + 1) . "...");
            
            try {
                $result = $this->neo4j->run($statement);
                $successCount++;
                
                // Show success with constraint/index name if available
                if (preg_match('/(?:CREATE\s+(?:CONSTRAINT|INDEX)\s+)(\w+)/i', $statement, $matches)) {
                    $this->info("  ✅ Created: {$matches[1]}");
                }
                
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'already exists') || 
                    str_contains($e->getMessage(), 'An equivalent')) {
                    $skipCount++;
                    $this->comment("  ⏭️  Already exists, skipping");
                } else {
                    $errorCount++;
                    $this->error("  ❌ Error: " . $e->getMessage());
                    Log::warning('Neo4j constraint creation failed', [
                        'statement' => $statement,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        $this->info("\nExecution Summary:");
        $this->info("  ✅ Successful: {$successCount}");
        $this->comment("  ⏭️  Skipped: {$skipCount}");
        if ($errorCount > 0) {
            $this->error("  ❌ Errors: {$errorCount}");
        }
    }

    /**
     * Check existing constraints and indexes
     */
    protected function checkConstraintsAndIndexes(): void
    {
        $this->info("\nChecking existing constraints and indexes...\n");

        try {
            // Check constraints
            $this->info("📋 Constraints:");
            $constraintResult = $this->neo4j->run('SHOW CONSTRAINTS');
            $constraintCount = 0;
            
            foreach ($constraintResult as $record) {
                $name = $record->get('name') ?? 'unnamed';
                $type = $record->get('type') ?? 'unknown';
                $this->line("  • {$name} ({$type})");
                $constraintCount++;
            }
            
            if ($constraintCount === 0) {
                $this->warn("  No constraints found");
            } else {
                $this->info("  Total: {$constraintCount} constraints");
            }

            // Check indexes
            $this->info("\n📊 Indexes:");
            $indexResult = $this->neo4j->run('SHOW INDEXES');
            $indexCount = 0;
            
            foreach ($indexResult as $record) {
                $name = $record->get('name') ?? 'unnamed';
                $type = $record->get('type') ?? 'unknown';
                $state = $record->get('state') ?? 'unknown';
                $this->line("  • {$name} ({$type}) - {$state}");
                $indexCount++;
            }
            
            if ($indexCount === 0) {
                $this->warn("  No indexes found");
            } else {
                $this->info("  Total: {$indexCount} indexes");
            }

            // Overall assessment
            $this->info("\n🔍 Assessment:");
            if ($constraintCount >= 10 && $indexCount >= 20) {
                $this->info("  ✅ Schema looks well-configured for SOUL Framework");
            } elseif ($constraintCount >= 5 && $indexCount >= 10) {
                $this->comment("  ⚠️  Basic schema in place, some optimizations may be missing");
            } else {
                $this->warn("  ⚠️  Schema may need more constraints and indexes for optimal performance");
            }

        } catch (\Exception $e) {
            $this->error("Failed to check constraints and indexes: " . $e->getMessage());
        }
    }

    /**
     * Drop existing constraints (use with caution)
     */
    protected function dropExistingConstraints(): void
    {
        $this->warn("⚠️  This will drop existing constraints - use with caution!");
        
        if (!$this->confirm('Are you sure you want to drop existing constraints?', false)) {
            $this->info('Skipping constraint dropping');
            return;
        }

        try {
            // Get list of existing constraints
            $constraintResult = $this->neo4j->run('SHOW CONSTRAINTS');
            $droppedCount = 0;
            
            foreach ($constraintResult as $record) {
                $constraintName = $record->get('name');
                if ($constraintName) {
                    try {
                        $this->neo4j->run("DROP CONSTRAINT {$constraintName}");
                        $this->line("  🗑️  Dropped constraint: {$constraintName}");
                        $droppedCount++;
                    } catch (\Exception $e) {
                        $this->error("  ❌ Failed to drop {$constraintName}: " . $e->getMessage());
                    }
                }
            }

            $this->info("Dropped {$droppedCount} constraints");

        } catch (\Exception $e) {
            $this->error("Failed to drop constraints: " . $e->getMessage());
        }
    }

    /**
     * Parse Cypher file into individual statements
     */
    protected function parseCypherStatements(string $content): array
    {
        // Remove comments and split by semicolons
        $lines = explode("\n", $content);
        $cleanedLines = [];
        
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            // Skip full-line comments and empty lines
            if (!str_starts_with($trimmedLine, '//') && !empty($trimmedLine)) {
                $cleanedLines[] = $line;
            }
        }
        
        $cleanContent = implode("\n", $cleanedLines);
        
        // Split by semicolons, but be careful about semicolons in strings
        $statements = preg_split('/;(?=(?:[^\']*\'[^\']*\')*[^\']*$)/', $cleanContent);
        
        return array_filter($statements, function($stmt) {
            return !empty(trim($stmt));
        });
    }
}