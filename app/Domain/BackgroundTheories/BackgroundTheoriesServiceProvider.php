<?php

namespace App\Domain\BackgroundTheories;

use Illuminate\Support\ServiceProvider;

/**
 * Laravel Service Provider for Background Theories
 * 
 * Registers all services and dependencies for the FOL axiom system
 * integration with the Laravel application.
 */
class BackgroundTheoriesServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        // Register repository as singleton
        $this->app->singleton(BackgroundRepository::class, function ($app) {
            return new BackgroundRepository();
        });

        // Register reasoning context as singleton
        $this->app->singleton(BackgroundReasoningContext::class, function ($app) {
            $repository = $app->make(BackgroundRepository::class);
            return new BackgroundReasoningContext($repository);
        });

        // Register main service as singleton
        $this->app->singleton(BackgroundTheoriesService::class, function ($app) {
            $context = $app->make(BackgroundReasoningContext::class);
            $repository = $app->make(BackgroundRepository::class);
            return new BackgroundTheoriesService($context, $repository);
        });

        // Register configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/background_theories.php',
            'background_theories'
        );
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Publish configuration file
        $this->publishes([
            __DIR__ . '/../../../config/background_theories.php' => config_path('background_theories.php'),
        ], 'config');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../../database/migrations');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\GenerateBackgroundTheories::class,
                \App\Console\Commands\BackgroundTheoriesStatus::class,
                \App\Console\Commands\ExecuteAxiom::class,
                \App\Console\Commands\ClearBackgroundTheories::class,
            ]);
        }

        // Auto-register axiom executors if configured
        $this->autoRegisterAxiomExecutors();

        // Auto-load YAML files if configured
        $this->autoLoadYamlFiles();
    }

    /**
     * Auto-register axiom executors from configuration
     */
    private function autoRegisterAxiomExecutors(): void
    {
        $executors = config('background_theories.axiom_executors', []);
        
        if (empty($executors)) {
            return;
        }

        $service = $this->app->make(BackgroundTheoriesService::class);
        
        foreach ($executors as $axiomId => $executorClass) {
            if (class_exists($executorClass)) {
                $executor = new $executorClass();
                $service->registerAxiomExecutor($axiomId, $executor);
            }
        }
    }

    /**
     * Auto-load YAML files if configured
     */
    private function autoLoadYamlFiles(): void
    {
        if (!config('background_theories.auto_load_yaml', false)) {
            return;
        }

        // This would trigger YAML loading on boot
        // For now, just log that it's available
        \Illuminate\Support\Facades\Log::info('Background Theories: Auto-load YAML is enabled');
    }

    /**
     * Get the services provided by the provider
     */
    public function provides(): array
    {
        return [
            BackgroundRepository::class,
            BackgroundReasoningContext::class,
            BackgroundTheoriesService::class,
        ];
    }
}