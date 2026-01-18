<?php

namespace Xotriks\Servertools;

use Filament\Contracts\Plugin;
use Filament\Panel;

class ServerToolsPlugin implements Plugin
{
    /**
     * Plugin identifier used by Pelican to discover resources and translations.
     */
    public function getId(): string
    {
        return 'servertools';
    }

    public function register(Panel $panel): void
    {
        // Filament panels provide an id like "Server" or similar. Convert it
        // to the title-cased namespace segment used by this plugin.
        $id = str($panel->getId())->title();

        // Build discovery path for Pages relative to the plugin installation.
        $pagesPath = plugin_path($this->getId(), "src/Filament/$id/Pages");
        $resourcesPath = plugin_path($this->getId(), "src/Filament/$id/Resources");

        // Discover Filament pages if the plugin provides them for this panel.
        if (is_dir($pagesPath)) {
            $panel->discoverPages($pagesPath, "Xotriks\\Servertools\\Filament\\$id\\Pages");
        }

        // Discover Filament resources if the plugin provides them for this panel.
        if (is_dir($resourcesPath)) {
            $panel->discoverResources($resourcesPath, "Xotriks\\Servertools\\Filament\\$id\\Resources");
        }
    }

    public function boot(Panel $panel): void
    {
        // Register views
        try {
            $pluginViewsPath = plugin_path($this->getId(), 'resources/views');
            if (is_dir($pluginViewsPath)) {
                app('view')->addNamespace('servertools', $pluginViewsPath);
            }
        } catch (\Exception $e) {
            // Namespace already registered
        }
    }

    /**
     * Called when plugin is installed/enabled
     * Creates database tables automatically
     */
    public function onInstall(): void
    {
        try {
            // Use Artisan directly to run migrations
            // Migrations are discovered via loadMigrationsFrom in ServiceProvider
            \Illuminate\Support\Facades\Artisan::call('migrate', [
                '--force' => true,
            ]);
            
            \Illuminate\Support\Facades\Log::debug('ServerTools migrations executed during installation');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ServerTools installation error: ' . $e->getMessage());
        }
    }

    /**
     * Called when plugin is uninstalled/disabled
     * Removes database tables automatically
     */
    public function onUninstall(): void
    {
        try {
            // Use Artisan to rollback migrations
            \Illuminate\Support\Facades\Artisan::call('migrate:rollback', [
                '--force' => true,
            ]);
            
            \Illuminate\Support\Facades\Log::debug('ServerTools plugin uninstalled - migrations rolled back');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ServerTools uninstall error: ' . $e->getMessage());
        } finally {
            $this->dropTables();
        }
    }

    private function dropTables(): void
    {
        try {
            $schema = \Illuminate\Support\Facades\Schema::connection(config('database.default'));
            $schema->dropIfExists('server_tool_translation_categories');
            $schema->dropIfExists('server_tool_profile_translations');
            $schema->dropIfExists('server_tool_configurations');
            \Illuminate\Support\Facades\Log::debug('ServerTools tables dropped via schema');
        } catch (\Exception $fallbackError) {
            \Illuminate\Support\Facades\Log::error('ServerTools fallback drop failed: ' . $fallbackError->getMessage());
        }
    }
}