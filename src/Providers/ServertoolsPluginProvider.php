<?php

namespace Xotriks\Servertools\Providers;

use Illuminate\Support\ServiceProvider;

class ServertoolsPluginProvider extends ServiceProvider
{
    public function register(): void
    {
        // No config to merge (DB-only)
    }

    public function boot(): void
    {
        // Load migrations using absolute path
        $migrationPath = __DIR__ . '/../../database/migrations';
        
        // Ensure the path exists
        if (is_dir($migrationPath)) {
            // Use loadMigrationsFrom for Laravel discovery
            $this->loadMigrationsFrom($migrationPath);
        } else {
            // Fallback to plugin_path
            $pluginMigrationPath = plugin_path('servertools/database/migrations');
            if (is_dir($pluginMigrationPath)) {
                $this->loadMigrationsFrom($pluginMigrationPath);
            }
        }

        // No config to publish (DB-only)
        $this->loadViewsFrom(plugin_path('servertools') . '/resources/views', 'servertools');
        $this->loadTranslationsFrom(plugin_path('servertools') . '/resources/lang', 'servertools');

        // Register Artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Xotriks\Servertools\Commands\SeedServerToolConfigurationsCommand::class,
            ]);
        }
    }
}