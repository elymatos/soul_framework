<?php

namespace App\Console\Commands;

use App\Domain\BackgroundTheories\BackgroundTheoriesService;
use Illuminate\Console\Command;

/**
 * Command to show Background Theories system status
 */
class BackgroundTheoriesStatus extends Command
{
    protected $signature = 'background:status {--detailed : Show detailed statistics}';
    protected $description = 'Show Background Theories system status and statistics';

    public function handle(): int
    {
        try {
            $service = app(BackgroundTheoriesService::class);
            $state = $service->getSystemState();
            $stats = $service->getStatistics();

            $this->info("=== Background Theories System Status ===\n");

            // Basic counts
            $this->table(['Metric', 'Count'], [
                ['Entities', $state['entities_count']],
                ['Real Entities', $state['real_entities_count']],
                ['Predicates', $state['predicates_count']],
                ['Axiom Executors', $state['axiom_executors_count']],
            ]);

            if ($this->option('detailed')) {
                $this->showDetailedStatistics($stats);
            }

            // Recent executions
            $executions = $service->getAxiomExecutionHistory()->take(5);
            if ($executions->isNotEmpty()) {
                $this->info("\n=== Recent Axiom Executions ===");
                $this->table(
                    ['Axiom ID', 'Predicates Created', 'Entities Created', 'Execution Time (ms)', 'Date'],
                    $executions->map(function ($execution) {
                        return [
                            $execution->axiom_id,
                            $execution->predicates_created,
                            $execution->entities_created,
                            $execution->execution_time_ms,
                            $execution->created_at,
                        ];
                    })->toArray()
                );
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to get system status: " . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function showDetailedStatistics(array $stats): void
    {
        $this->info("\n=== Detailed Statistics ===");

        // Entity breakdown
        if (!empty($stats['entities']['by_type'])) {
            $this->info("\nEntities by Type:");
            foreach ($stats['entities']['by_type'] as $type => $count) {
                $this->line("  {$type}: {$count}");
            }
        }

        // Predicate breakdown
        if (!empty($stats['predicates']['by_name'])) {
            $this->info("\nPredicates by Name:");
            foreach ($stats['predicates']['by_name'] as $name => $count) {
                $this->line("  {$name}: {$count}");
            }
        }

        // Execution statistics
        if (isset($stats['axiom_executions'])) {
            $this->info("\nAxiom Executions:");
            $this->line("  Total: {$stats['axiom_executions']['total']}");
            $this->line("  Last 24h: {$stats['axiom_executions']['last_24h']}");
        }
    }
}