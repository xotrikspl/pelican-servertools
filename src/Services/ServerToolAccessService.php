<?php

namespace Xotriks\Servertools\Services;

use App\Models\Server;

class ServerToolAccessService
{
    /**
     * Get all available configurations for a server
     */
    public static function getAvailableConfigs(Server $server): array
    {
        try {
            // Check features on the egg
            $eggFeatures = $server->egg?->features ?? [];
            if (!in_array('server-tools', $eggFeatures)) {
                return [];
            }

            // Check whether a mapping exists for this egg
            $mapping = \Xotriks\Servertools\Models\ServerToolConfiguration::where('egg_id', $server->egg_id)->first();

            if (!$mapping) {
                \Log::debug('[ServerTools] No ServerToolConfiguration found for egg: ' . ($server->egg?->name ?? 'unknown'));
                return [];
            }

            // Check whether the config is available (not empty)
            if (empty($mapping->config) || !is_array($mapping->config)) {
                \Log::debug('[ServerTools] ServerToolConfiguration config is empty or invalid for egg: ' . ($server->egg?->name ?? 'unknown'));
                return [];
            }

            // Return the config as a single array element
            return [$mapping->config];

        } catch (\Throwable $e) {
            // Handle cases where tables do not exist (e.g., during migrations)
            \Log::debug('[ServerTools] getAvailableConfigs error: ' . $e->getMessage());
            return [];
        }
    }
}
