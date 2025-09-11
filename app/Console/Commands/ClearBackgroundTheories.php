<?php

namespace App\Console\Commands;

use App\Domain\BackgroundTheories\BackgroundTheoriesService;
use Illuminate\Console\Command;

/**
 * Command to clear Background Theories data
 */
class ClearBackgroundTheories extends Command
{
    protected $signature = 'background:clear 
                           {--force : Skip confirmation}';
    
    protected $description = 'Clear all Background Theories data (entities, predicates, execution history)';

    public function handle(): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will delete ALL Background Theories data. Are you sure?')) {
                $this->info('Operation cancelled.');
                return self::SUCCESS;
            }
        }

        try {
            $service = app(BackgroundTheoriesService::class);
            
            $this->info('Clearing all Background Theories data...');
            
            $service->clearAll();
            
            $this->info('Successfully cleared all data.');
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to clear data: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}