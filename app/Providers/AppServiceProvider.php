<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Soul\Contracts\FrameDefinitionRegistry;
use App\Soul\Contracts\Neo4jService;
use App\Soul\Contracts\GraphServiceInterface;
use App\Soul\Services\FrameDefinitionRegistryService;
use App\Soul\Services\Neo4jFrameService;
use App\Soul\Services\GraphService;
use App\Soul\Services\FrameService;
use App\Soul\Services\ImageSchemaService;
use App\Soul\Services\YamlLoaderService;
use App\Soul\Services\MindService;
use App\Console\Commands\Soul\ApplyNeo4jConstraints;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (env('LOG_SQL') == 'debug') {
            DB::enableQueryLog();
            DB::listen(function ($query) {
                debugQuery($query->sql, $query->bindings);
            });
        }

        // Register SOUL Framework contract implementations
        $this->app->singleton(FrameDefinitionRegistry::class, FrameDefinitionRegistryService::class);
        $this->app->singleton(Neo4jService::class, Neo4jFrameService::class);
        $this->app->singleton(GraphServiceInterface::class, GraphService::class);
        
        // Register SOUL agent services
        $this->app->singleton(FrameService::class);
        $this->app->singleton(ImageSchemaService::class);
        $this->app->singleton(YamlLoaderService::class);
        $this->app->singleton(MindService::class);
        
        // Register SOUL commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ApplyNeo4jConstraints::class,
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::addExtension('js', 'php');
        Blade::anonymousComponentPath(app_path('UI/layouts'), 'layout');
        Blade::anonymousComponentPath(app_path('UI/components'), 'ui');
        Blade::anonymousComponentPath(app_path('UI/forms'), 'form');
    }
}
