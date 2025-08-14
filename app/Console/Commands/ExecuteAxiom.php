<?php

namespace App\Console\Commands;

use App\Domain\BackgroundTheories\BackgroundTheoriesService;
use Illuminate\Console\Command;

/**
 * Command to execute specific axioms
 */
class ExecuteAxiom extends Command
{
    protected $signature = 'background:execute-axiom 
                           {axiom_id? : Specific axiom ID to execute}
                           {--all : Execute all registered axioms}
                           {--list : List available axiom executors}';
    
    protected $description = 'Execute Background Theory axioms';

    public function handle(): int
    {
        try {
            $service = app(BackgroundTheoriesService::class);

            if ($this->option('list')) {
                return $this->listAxiomExecutors($service);
            }

            if ($this->option('all')) {
                return $this->executeAllAxioms($service);
            }

            $axiomId = $this->argument('axiom_id');
            if (!$axiomId) {
                $this->error("Please provide an axiom ID or use --all or --list");
                return self::FAILURE;
            }

            return $this->executeSpecificAxiom($service, $axiomId);

        } catch (\Exception $e) {
            $this->error("Execution failed: " . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function listAxiomExecutors(BackgroundTheoriesService $service): int
    {
        $executors = $service->getAxiomExecutors();

        if (empty($executors)) {
            $this->info("No axiom executors registered.");
            return self::SUCCESS;
        }

        $this->info("=== Registered Axiom Executors ===\n");

        $tableData = [];
        foreach ($executors as $axiomId => $executor) {
            $tableData[] = [
                $axiomId,
                $executor->getDescription(),
                $executor->getComplexity(),
                implode(', ', $executor->getPredicatesUsed()),
            ];
        }

        $this->table(
            ['Axiom ID', 'Description', 'Complexity', 'Predicates Used'],
            $tableData
        );

        return self::SUCCESS;
    }

    private function executeAllAxioms(BackgroundTheoriesService $service): int
    {
        $this->info("Executing all registered axioms...\n");

        $results = $service->executeAllTheories();

        $this->info("=== Execution Results ===");
        $this->info("Total results: " . $results->count());
        $this->info("New predicates: " . $results->whereInstanceOf(\App\Domain\BackgroundTheories\BackgroundPredicate::class)->count());
        $this->info("New entities: " . $results->whereInstanceOf(\App\Domain\BackgroundTheories\BackgroundEntity::class)->count());

        return self::SUCCESS;
    }

    private function executeSpecificAxiom(BackgroundTheoriesService $service, string $axiomId): int
    {
        $this->info("Executing axiom: {$axiomId}");

        $results = $service->executeAxiom($axiomId);

        $this->info("=== Execution Results ===");
        $this->info("Results count: " . $results->count());

        if ($results->isNotEmpty()) {
            $this->info("\nCreated items:");
            foreach ($results as $result) {
                if ($result instanceof \App\Domain\BackgroundTheories\BackgroundPredicate) {
                    $this->line("  Predicate: " . $result->describe());
                } elseif ($result instanceof \App\Domain\BackgroundTheories\BackgroundEntity) {
                    $this->line("  Entity: " . $result->describe());
                }
            }
        }

        return self::SUCCESS;
    }
}