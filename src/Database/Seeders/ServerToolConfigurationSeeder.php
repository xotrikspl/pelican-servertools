<?php

namespace Xotriks\Servertools\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Egg;
use Xotriks\Servertools\Models\ServerToolConfiguration;
use Xotriks\Servertools\Models\ServerToolProfileTranslation;
use Xotriks\Servertools\Models\ServerToolTranslationCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ServerToolConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
		// Clear existing data before seeding (resets auto-increment)
		DB::table('server_tool_profile_translations')->truncate();
		DB::table('server_tool_translation_categories')->truncate();
		DB::table('server_tool_configurations')->truncate();

		$eggs = Egg::all();
		$missingProfiles = [];

        $profiles = [
            'minecraft-paper' => $this->minecraftServerProperties(),
            'minecraft-spigot' => $this->minecraftServerProperties(),
            'minecraft-purpur' => $this->minecraftServerProperties(),
        ];

        $minecraftCategory = $this->getOrCreateCategory('Minecraft');

        foreach ($eggs as $egg) {
            $profileName = $this->findProfileByEggName($egg->name);
			if (!$profileName || !isset($profiles[$profileName])) {
				$missingProfiles[] = $egg->name;
				// Log::warning('ServerTools: missing profile mapping for egg.', [
				// 	'egg_id' => $egg->id,
				// 	'egg_name' => $egg->name,
				// ]);
				$this->command?->warn("⚠️  Missing profile for egg: {$egg->name}");
                continue;
            }

			$profileConfig = $this->normalizeProfileConfigOrder($profiles[$profileName]);
			$profileConfig = $this->prefixTranslationKeysInConfig($profileConfig);

            $mapping = ServerToolConfiguration::updateOrCreate(
                ['egg_id' => $egg->id],
                [
                    'name' => Str::title(str_replace(['-', '_'], ' ', $profileName)),
                    'profile_name' => $profileName,
                    'description' => "Profile for {$egg->name}",
                    'config' => $profileConfig,
                    'server_tools_enabled' => false,
                    'translation_category_id' => $minecraftCategory?->id,
                ]
            );

			// Force config overwrite to preserve ordering
            $mapping->update(['config' => $profileConfig]);

			// Add the server-tools feature to the egg and update the flag
            $enabled = $this->ensureServerToolsFeature($egg);
            $mapping->update(['server_tools_enabled' => $enabled]);

            if ($minecraftCategory) {
                $mapping->update(['translation_category_id' => $minecraftCategory->id]);
            }

			// Seed profile translations into the database
            $this->seedProfileTranslations($minecraftCategory?->id);

            $this->command->info("✅ Saved profile '{$profileName}' in egg profile mappings.config for eggs '{$egg->name}'");
        }

		$this->command?->newLine();
		if (!empty($missingProfiles)) {
			$uniqueMissing = array_values(array_unique($missingProfiles));
			$this->command?->warn('⚠️  Missing profiles for eggs: ' . implode(', ', $uniqueMissing));
		}
        $this->command->info('🎉 Profile mapping seeding completed!');
    }

    private function minecraftServerProperties(): array
    {
        return [
            'files' => [
                'server.properties' => [
                    'type' => 'ini',
                    'sections' => [
                        'minecraft.section_basic' => [
                            ['type' => 'text', 'key' => 'motd', 'label' => 'minecraft.motd_label'],
                            ['type' => 'text', 'key' => 'server-name', 'label' => 'minecraft.server_name_label'],
                            ['type' => 'text', 'key' => 'level-name', 'label' => 'minecraft.level_name_label'],
                            ['type' => 'text', 'key' => 'server-ip', 'label' => 'minecraft.server_ip_label'],
                            ['type' => 'number', 'key' => 'server-port', 'label' => 'minecraft.server_port_label', 'min' => 1024, 'max' => 65535],
                            ['type' => 'number', 'key' => 'port', 'label' => 'minecraft.port_label', 'min' => 1024, 'max' => 65535],
                            ['type' => 'number', 'key' => 'max-players', 'label' => 'minecraft.max_players_label', 'min' => 1, 'max' => 999],
                            ['type' => 'toggle', 'key' => 'online-mode', 'label' => 'minecraft.online_mode_label'],
                        ],
                        'minecraft.section_gameplay' => [
                            ['type' => 'select', 'key' => 'gamemode', 'label' => 'minecraft.gamemode_label', 'options' => ['survival' => 'minecraft.gamemode_survival','creative' => 'minecraft.gamemode_creative','adventure' => 'minecraft.gamemode_adventure','spectator' => 'minecraft.gamemode_spectator']],
                            ['type' => 'select', 'key' => 'difficulty', 'label' => 'minecraft.difficulty_label', 'options' => ['peaceful' => 'minecraft.difficulty_peaceful','easy' => 'minecraft.difficulty_easy','normal' => 'minecraft.difficulty_normal','hard' => 'minecraft.difficulty_hard']],
                            ['type' => 'toggle', 'key' => 'pvp', 'label' => 'minecraft.pvp_label'],
                            ['type' => 'toggle', 'key' => 'force-gamemode', 'label' => 'minecraft.force_gamemode_label'],
                            ['type' => 'toggle', 'key' => 'hardcore', 'label' => 'minecraft.hardcore_label'],
                        ],
                        'minecraft.section_spawning' => [
                            ['type' => 'toggle', 'key' => 'spawn-animals', 'label' => 'minecraft.spawn_animals_label'],
                            ['type' => 'toggle', 'key' => 'spawn-monsters', 'label' => 'minecraft.spawn_monsters_label'],
                            ['type' => 'toggle', 'key' => 'spawn-npcs', 'label' => 'minecraft.spawn_npcs_label'],
                            ['type' => 'number', 'key' => 'spawn-protection', 'label' => 'minecraft.spawn_protection_label', 'min' => 0, 'max' => 100],
                        ],
                        'minecraft.section_world' => [
                            ['type' => 'text', 'key' => 'level-seed', 'label' => 'minecraft.level_seed_label'],
                            ['type' => 'select', 'key' => 'level-type', 'label' => 'minecraft.level_type_label', 'options' => ['minecraft:normal' => 'minecraft.level_type_default','minecraft:flat' => 'minecraft.level_type_flat','minecraft:large_biomes' => 'minecraft.level_type_large_biomes']],
                            ['type' => 'textarea', 'key' => 'generator-settings', 'label' => 'minecraft.generator_settings_label'],
                            ['type' => 'toggle', 'key' => 'generate-structures', 'label' => 'minecraft.generate_structures_label'],
                            ['type' => 'number', 'key' => 'max-world-size', 'label' => 'minecraft.max_world_size_label', 'min' => 1, 'max' => 29999984],
                        ],
                        'minecraft.section_performance' => [
                            ['type' => 'number', 'key' => 'view-distance', 'label' => 'minecraft.view_distance_label', 'min' => 3, 'max' => 32],
                            ['type' => 'number', 'key' => 'simulation-distance', 'label' => 'minecraft.simulation_distance_label', 'min' => 5, 'max' => 32],
                            ['type' => 'number', 'key' => 'network-compression-threshold', 'label' => 'minecraft.network_compression_threshold_label', 'min' => -1, 'max' => 65535],
                            ['type' => 'number', 'key' => 'max-tick-time', 'label' => 'minecraft.max_tick_time_label', 'min' => 1],
                            ['type' => 'toggle', 'key' => 'sync-chunk-writes', 'label' => 'minecraft.sync_chunk_writes_label'],
                            ['type' => 'select', 'key' => 'region-file-compression', 'label' => 'minecraft.region_file_compression_label', 'options' => ['deflate' => 'minecraft.compression_deflate','none' => 'minecraft.compression_none']],
                        ],
                        'minecraft.section_security' => [
                            ['type' => 'toggle', 'key' => 'enable-command-block', 'label' => 'minecraft.enable_command_block_label'],
                            ['type' => 'number', 'key' => 'op-permission-level', 'label' => 'minecraft.op_permission_level_label', 'min' => 0, 'max' => 4],
                            ['type' => 'number', 'key' => 'function-permission-level', 'label' => 'minecraft.function_permission_level_label', 'min' => 0, 'max' => 4],
                            ['type' => 'toggle', 'key' => 'enforce-whitelist', 'label' => 'minecraft.enforce_whitelist_label'],
                            ['type' => 'toggle', 'key' => 'white-list', 'label' => 'minecraft.white_list_label'],
                            ['type' => 'toggle', 'key' => 'broadcast-console-to-ops', 'label' => 'minecraft.broadcast_console_to_ops_label'],
                            ['type' => 'toggle', 'key' => 'broadcast-rcon-to-ops', 'label' => 'minecraft.broadcast_rcon_to_ops_label'],
                            ['type' => 'toggle', 'key' => 'enforce-secure-profile', 'label' => 'minecraft.enforce_secure_profile_label'],
                        ],
                        'minecraft.section_query' => [
                            ['type' => 'toggle', 'key' => 'enable-query', 'label' => 'minecraft.enable_query_label'],
                            ['type' => 'number', 'key' => 'query.port', 'label' => 'minecraft.query_port_label', 'min' => 1024, 'max' => 65535],
                        ],
                        'minecraft.section_rcon' => [
                            ['type' => 'toggle', 'key' => 'enable-rcon', 'label' => 'minecraft.enable_rcon_label'],
                            ['type' => 'number', 'key' => 'rcon.port', 'label' => 'minecraft.rcon_port_label', 'min' => 1024, 'max' => 65535],
                            ['type' => 'text', 'key' => 'rcon.password', 'label' => 'minecraft.rcon_password_label'],
                        ],
                        'minecraft.section_advanced' => [
                            ['type' => 'toggle', 'key' => 'enable-jmx-monitoring', 'label' => 'minecraft.enable_jmx_monitoring_label'],
                            ['type' => 'toggle', 'key' => 'enable-code-of-conduct', 'label' => 'minecraft.enable_code_of_conduct_label'],
                            ['type' => 'text', 'key' => 'initial-enabled-packs', 'label' => 'minecraft.initial_enabled_packs_label'],
                            ['type' => 'text', 'key' => 'initial-disabled-packs', 'label' => 'minecraft.initial_disabled_packs_label'],
                            ['type' => 'text', 'key' => 'management-server-host', 'label' => 'minecraft.management_server_host_label'],
                            ['type' => 'number', 'key' => 'management-server-port', 'label' => 'minecraft.management_server_port_label', 'min' => 0, 'max' => 65535],
                            ['type' => 'toggle', 'key' => 'management-server-enabled', 'label' => 'minecraft.management_server_enabled_label'],
                            ['type' => 'toggle', 'key' => 'management-server-tls-enabled', 'label' => 'minecraft.management_server_tls_enabled_label'],
                            ['type' => 'text', 'key' => 'management-server-allowed-origins', 'label' => 'minecraft.management_server_allowed_origins_label'],
                            ['type' => 'text', 'key' => 'management-server-tls-keystore', 'label' => 'minecraft.management_server_tls_keystore_label'],
                            ['type' => 'text', 'key' => 'management-server-tls-keystore-password', 'label' => 'minecraft.management_server_tls_keystore_password_label'],
                            ['type' => 'text', 'key' => 'management-server-secret', 'label' => 'minecraft.management_server_secret_label'],
                        ],
                        'minecraft.section_other' => [
                            ['type' => 'toggle', 'key' => 'accepts-transfers', 'label' => 'minecraft.accepts_transfers_label'],
                            ['type' => 'toggle', 'key' => 'allow-flight', 'label' => 'minecraft.allow_flight_label'],
                            ['type' => 'toggle', 'key' => 'debug', 'label' => 'minecraft.debug_label'],
                            ['type' => 'toggle', 'key' => 'enable-status', 'label' => 'minecraft.enable_status_label'],
                            ['type' => 'toggle', 'key' => 'log-ips', 'label' => 'minecraft.log_ips_label'],
                            ['type' => 'number', 'key' => 'player-idle-timeout', 'label' => 'minecraft.player_idle_timeout_label', 'min' => 0],
                            ['type' => 'number', 'key' => 'rate-limit', 'label' => 'minecraft.rate_limit_label', 'min' => 0],
                            ['type' => 'toggle', 'key' => 'prevent-proxy-connections', 'label' => 'minecraft.prevent_proxy_connections_label'],
                            ['type' => 'toggle', 'key' => 'use-native-transport', 'label' => 'minecraft.use_native_transport_label'],
                            ['type' => 'toggle', 'key' => 'hide-online-players', 'label' => 'minecraft.hide_online_players_label'],
                            ['type' => 'number', 'key' => 'entity-broadcast-range-percentage', 'label' => 'minecraft.entity_broadcast_range_percentage_label', 'min' => 1, 'max' => 1000],
                            ['type' => 'number', 'key' => 'max-chained-neighbor-updates', 'label' => 'minecraft.max_chained_neighbor_updates_label', 'min' => 1],
                            ['type' => 'number', 'key' => 'pause-when-empty-seconds', 'label' => 'minecraft.pause_when_empty_seconds_label'],
                            ['type' => 'number', 'key' => 'status-heartbeat-interval', 'label' => 'minecraft.status_heartbeat_interval_label', 'min' => 0],
                            ['type' => 'text', 'key' => 'bug-report-link', 'label' => 'minecraft.bug_report_link_label'],
                            ['type' => 'text', 'key' => 'require-resource-pack', 'label' => 'minecraft.require_resource_pack_label'],
                            ['type' => 'text', 'key' => 'resource-pack', 'label' => 'minecraft.resource_pack_label'],
                            ['type' => 'text', 'key' => 'resource-pack-id', 'label' => 'minecraft.resource_pack_id_label'],
                            ['type' => 'text', 'key' => 'resource-pack-prompt', 'label' => 'minecraft.resource_pack_prompt_label'],
                            ['type' => 'text', 'key' => 'resource-pack-sha1', 'label' => 'minecraft.resource_pack_sha1_label'],
                            ['type' => 'text', 'key' => 'text-filtering-config', 'label' => 'minecraft.text_filtering_config_label'],
                            ['type' => 'number', 'key' => 'text-filtering-version', 'label' => 'minecraft.text_filtering_version_label', 'min' => 0],
                        ],
                    ],
                ],
            ],
        ];
    }

	private function normalizeProfileConfigOrder(array $config): array
	{
		$files = $config['files'] ?? null;
		if (!is_array($files)) {
			return $config;
		}

		foreach ($files as $filename => $fileConfig) {
			if (!is_array($fileConfig)) {
				continue;
			}

			$sections = $fileConfig['sections'] ?? null;
			if (!is_array($sections)) {
				continue;
			}

			if (isset($fileConfig['sections_order']) && is_array($fileConfig['sections_order'])) {
				continue;
			}

			if (isset($sections[0]) && is_array($sections[0]) && array_key_exists('section_key', $sections[0])) {
				$config['files'][$filename]['sections_order'] = array_values(array_filter(array_map(
					fn (array $section) => $section['section_key'] ?? null,
					$sections
				)));
				continue;
			}

			$config['files'][$filename]['sections_order'] = array_values(array_filter(array_keys($sections), 'is_string'));
		}

		return $config;
	}

	private function prefixTranslationKeysInConfig(array $config): array
	{
		$files = $config['files'] ?? null;
		if (!is_array($files)) {
			return $config;
		}

		foreach ($files as $filename => $fileConfig) {
			if (!is_array($fileConfig)) {
				continue;
			}

			$sections = $fileConfig['sections'] ?? null;
			if (!is_array($sections)) {
				continue;
			}

			$sectionsOrder = $fileConfig['sections_order'] ?? null;
			if (is_array($sectionsOrder)) {
				$config['files'][$filename]['sections_order'] = array_values(array_map(
					fn ($key) => $this->prefixKey($key),
					$sectionsOrder
				));
			}

			if (isset($sections[0]) && is_array($sections[0]) && array_key_exists('section_key', $sections[0])) {
				foreach ($sections as $index => $section) {
					if (!is_array($section)) {
						continue;
					}

					$sectionKey = $section['section_key'] ?? null;
					if (is_string($sectionKey)) {
						$config['files'][$filename]['sections'][$index]['section_key'] = $this->prefixKey($sectionKey);
					}

					$fields = $section['fields'] ?? null;
					if (!is_array($fields)) {
						continue;
					}

					foreach ($fields as $fieldIndex => $field) {
						if (!is_array($field)) {
							continue;
						}

						$label = $field['label'] ?? null;
						if (is_string($label)) {
							$config['files'][$filename]['sections'][$index]['fields'][$fieldIndex]['label'] = $this->prefixKey($label);
						}

						$options = $field['options'] ?? null;
						if (is_array($options)) {
							foreach ($options as $optionKey => $optionLabel) {
								if (is_string($optionLabel)) {
									$config['files'][$filename]['sections'][$index]['fields'][$fieldIndex]['options'][$optionKey] = $this->prefixKey($optionLabel);
								}
							}
						}
					}
				}
			} else {
				$prefixedSections = [];
				foreach ($sections as $sectionKey => $fields) {
					$prefixedKey = $this->prefixKey($sectionKey);
					if (!is_array($fields)) {
						$prefixedSections[$prefixedKey] = $fields;
						continue;
					}

					$prefixedFields = [];
					foreach ($fields as $field) {
						if (!is_array($field)) {
							$prefixedFields[] = $field;
							continue;
						}

						$label = $field['label'] ?? null;
						if (is_string($label)) {
							$field['label'] = $this->prefixKey($label);
						}

						$options = $field['options'] ?? null;
						if (is_array($options)) {
							foreach ($options as $optionKey => $optionLabel) {
								if (is_string($optionLabel)) {
									$options[$optionKey] = $this->prefixKey($optionLabel);
								}
							}
							$field['options'] = $options;
						}

						$prefixedFields[] = $field;
					}

					$prefixedSections[$prefixedKey] = $prefixedFields;
				}

				$config['files'][$filename]['sections'] = $prefixedSections;
			}
		}

		return $config;
	}

	private function prefixKey(mixed $key): mixed
	{
		if (!is_string($key)) {
			return $key;
		}

		if (!str_contains($key, '.')) {
			return $key;
		}

		if (str_starts_with($key, 'servertools::')) {
			return $key;
		}

		return 'servertools::' . $key;
	}

    /**
     * Automatic profile matching based on egg name
     */
    private function findProfileByEggName(string $eggName): ?string
    {
        $eggNameLower = Str::lower($eggName);

        // Exact matches for known egg names
        $exactMatches = [
            'paper' => 'minecraft-paper',
            'spigot' => 'minecraft-spigot',
            'purpur' => 'minecraft-purpur',
        ];

        return $exactMatches[$eggNameLower] ?? null;
    }

    private function ensureServerToolsFeature(Egg $egg): bool
    {
        $features = $egg->features ?? [];
        if (is_string($features)) {
            $decoded = json_decode($features, true);
            $features = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($features)) {
            $features = [];
        }

        if (!in_array('server-tools', $features, true)) {
            $features[] = 'server-tools';
            $egg->features = array_values(array_unique($features));
            $egg->save();
        }

        return in_array('server-tools', $egg->features ?? [], true);
    }

    private function minecraftTranslations(): array
    {
        return [
            'de' => [
						// Basic Section
						'section_basic' => 'Grundlagen',
						'motd_label' => 'Servernachricht (MOTD)',
						'server_name_label' => 'Servername',
						'level_name_label' => 'Weltname',
						'server_ip_label' => 'Server-IP',
						'server_port_label' => 'Serverport',
						'port_label' => 'Port',
						'max_players_label' => 'Maximale Spieleranzahl',
						'online_mode_label' => 'Online-Modus',

						// Gameplay Section
						'section_gameplay' => 'Spielmechanik',
						'gamemode_label' => 'Spielmodus',
						'gamemode_survival' => 'Überleben',
						'gamemode_creative' => 'Kreativ',
						'gamemode_adventure' => 'Abenteuer',
						'gamemode_spectator' => 'Zuschauer',
						'difficulty_label' => 'Schwierigkeit',
						'difficulty_peaceful' => 'Friedlich',
						'difficulty_easy' => 'Einfach',
						'difficulty_normal' => 'Normal',
						'difficulty_hard' => 'Schwer',
						'pvp_label' => 'PvP',
						'force_gamemode_label' => 'Spielmodus erzwingen',
						'hardcore_label' => 'Hardcore',

						// Spawning Section
						'section_spawning' => 'Spawn-Einstellungen',
						'spawn_animals_label' => 'Tiere spawnen',
						'spawn_monsters_label' => 'Monster spawnen',
						'spawn_npcs_label' => 'NPCs spawnen',
						'spawn_protection_label' => 'Spawn-Schutz',

						// World Section
						'section_world' => 'Welt',
						'level_seed_label' => 'Welt-Seed',
						'level_type_label' => 'Welttyp',
						'level_type_default' => 'Standard',
						'level_type_flat' => 'Flach',
						'level_type_large_biomes' => 'Große Biome',
						'generator_settings_label' => 'Generator-Einstellungen',
						'generate_structures_label' => 'Strukturen generieren',
						'max_world_size_label' => 'Max. Weltgröße',

						// Performance Section
						'section_performance' => 'Leistung',
						'view_distance_label' => 'Sichtweite',
						'simulation_distance_label' => 'Simulationsweite',
						'network_compression_threshold_label' => 'Netzwerk-Kompressionsschwelle',
						'max_tick_time_label' => 'Max. Tick-Zeit',
						'sync_chunk_writes_label' => 'Chunk-Schreibvorgänge synchronisieren',
						'region_file_compression_label' => 'Regiondatei-Kompression',
						'compression_deflate' => 'Deflate',
						'compression_none' => 'Keine',

						// Security Section
						'section_security' => 'Sicherheit',
						'enable_command_block_label' => 'Befehlsblöcke aktivieren',
						'op_permission_level_label' => 'OP-Berechtigungsstufe',
						'function_permission_level_label' => 'Funktionsberechtigungsstufe',
						'enforce_whitelist_label' => 'Whitelist erzwingen',
						'white_list_label' => 'Whitelist',
						'broadcast_console_to_ops_label' => 'Konsole an OPs übertragen',
						'broadcast_rcon_to_ops_label' => 'RCON an OPs übertragen',
						'enforce_secure_profile_label' => 'Sicheres Profil erzwingen',

						// Query Section
						'section_query' => 'Abfrage',
						'enable_query_label' => 'Abfrage aktivieren',
						'query_port_label' => 'Abfrageport',

						// RCON Section
						'section_rcon' => 'RCON',
						'enable_rcon_label' => 'RCON aktivieren',
						'rcon_port_label' => 'RCON-Port',
						'rcon_password_label' => 'RCON-Passwort',

						// Other Section
						'section_other' => 'Sonstige',
						'accepts_transfers_label' => 'Akzeptiert Transfers',
						'allow_flight_label' => 'Flug erlauben',
						'debug_label' => 'Debug',
						'enable_status_label' => 'Status aktivieren',
						'log_ips_label' => 'IPs protokollieren',
						'player_idle_timeout_label' => 'Spieler-Untätigkeitszeitüberschreitung',
						'rate_limit_label' => 'Ratenbegrenzung',
						'prevent_proxy_connections_label' => 'Proxy-Verbindungen verhindern',
						'use_native_transport_label' => 'Nativen Transport verwenden',
						'hide_online_players_label' => 'Online-Spieler ausblenden',
						'entity_broadcast_range_percentage_label' => 'Entity-Broadcast-Bereich in Prozent',
						'max_chained_neighbor_updates_label' => 'Max. verkettete Nachbaraktualisierungen',
						'pause_when_empty_seconds_label' => 'Pause bei leer (Sekunden)',
						'status_heartbeat_interval_label' => 'Status-Heartbeat-Intervall',
						'bug_report_link_label' => 'Bug-Report-Link',
						'require_resource_pack_label' => 'Ressourcen-Pack erforderlich',
						'resource_pack_label' => 'Ressourcen-Pack-URL',
						'resource_pack_id_label' => 'Ressourcen-Pack-ID',
						'resource_pack_prompt_label' => 'Ressourcen-Pack-Eingabeaufforderung',
						'resource_pack_sha1_label' => 'Ressourcen-Pack-SHA1',
						'text_filtering_config_label' => 'Textfilter-Konfiguration',
						'text_filtering_version_label' => 'Textfilter-Version',

						// Advanced Section
						'section_advanced' => 'Erweitert',
						'enable_jmx_monitoring_label' => 'JMX-Überwachung aktivieren',
						'enable_code_of_conduct_label' => 'Verhaltenskodex aktivieren',
						'initial_enabled_packs_label' => 'Initial aktivierte Pakete',
						'initial_disabled_packs_label' => 'Initial deaktivierte Pakete',
						'management_server_host_label' => 'Management-Server-Host',
						'management_server_port_label' => 'Management-Server-Port',
						'management_server_enabled_label' => 'Management-Server aktiviert',
						'management_server_tls_enabled_label' => 'Management-Server-TLS aktiviert',
						'management_server_allowed_origins_label' => 'Zulässige Ursprünge des Management-Servers',
						'management_server_tls_keystore_label' => 'Management-Server-TLS-Keystore',
						'management_server_tls_keystore_password_label' => 'Management-Server-TLS-Keystore-Passwort',
						'management_server_secret_label' => 'Management-Server-Geheimnis',
            ],
            'en' => [
						// Basic Section
						'section_basic' => 'Basic',
						'motd_label' => 'Server Message (MOTD)',
						'server_name_label' => 'Server Name',
						'level_name_label' => 'World Name',
						'server_ip_label' => 'Server IP',
						'server_port_label' => 'Server Port',
						'port_label' => 'Port',
						'max_players_label' => 'Max Players',
						'online_mode_label' => 'Online Mode',

						// Gameplay Section
						'section_gameplay' => 'Gameplay',
						'gamemode_label' => 'Game Mode',
						'gamemode_survival' => 'Survival',
						'gamemode_creative' => 'Creative',
						'gamemode_adventure' => 'Adventure',
						'gamemode_spectator' => 'Spectator',
						'difficulty_label' => 'Difficulty',
						'difficulty_peaceful' => 'Peaceful',
						'difficulty_easy' => 'Easy',
						'difficulty_normal' => 'Normal',
						'difficulty_hard' => 'Hard',
						'pvp_label' => 'PVP',
						'force_gamemode_label' => 'Force Gamemode',
						'hardcore_label' => 'Hardcore',

						// Spawning Section
						'section_spawning' => 'Spawning',
						'spawn_animals_label' => 'Spawn Animals',
						'spawn_monsters_label' => 'Spawn Monsters',
						'spawn_npcs_label' => 'Spawn NPCs',
						'spawn_protection_label' => 'Spawn Protection',

						// World Section
						'section_world' => 'World',
						'level_seed_label' => 'World Seed',
						'level_type_label' => 'World Type',
						'level_type_default' => 'Default',
						'level_type_flat' => 'Flat',
						'level_type_large_biomes' => 'Large Biomes',
						'generator_settings_label' => 'Generator Settings',
						'generate_structures_label' => 'Generate Structures',
						'max_world_size_label' => 'Max World Size',

						// Performance Section
						'section_performance' => 'Performance',
						'view_distance_label' => 'View Distance',
						'simulation_distance_label' => 'Simulation Distance',
						'network_compression_threshold_label' => 'Network Compression Threshold',
						'max_tick_time_label' => 'Max Tick Time',
						'sync_chunk_writes_label' => 'Sync Chunk Writes',
						'region_file_compression_label' => 'Region File Compression',
						'compression_deflate' => 'Deflate',
						'compression_none' => 'None',

						// Security Section
						'section_security' => 'Security',
						'enable_command_block_label' => 'Enable Command Blocks',
						'op_permission_level_label' => 'OP Permission Level',
						'function_permission_level_label' => 'Function Permission Level',
						'enforce_whitelist_label' => 'Enforce Whitelist',
						'white_list_label' => 'White List',
						'broadcast_console_to_ops_label' => 'Broadcast Console to OPs',
						'broadcast_rcon_to_ops_label' => 'Broadcast RCON to OPs',
						'enforce_secure_profile_label' => 'Enforce Secure Profile',

						// Query Section
						'section_query' => 'Query',
						'enable_query_label' => 'Enable Query',
						'query_port_label' => 'Query Port',

						// RCON Section
						'section_rcon' => 'RCON',
						'enable_rcon_label' => 'Enable RCON',
						'rcon_port_label' => 'RCON Port',
						'rcon_password_label' => 'RCON Password',

						// Other Section
						'section_other' => 'Other',
						'accepts_transfers_label' => 'Accepts Transfers',
						'allow_flight_label' => 'Allow Flight',
						'debug_label' => 'Debug',
						'enable_status_label' => 'Enable Status',
						'log_ips_label' => 'Log IPs',
						'player_idle_timeout_label' => 'Player Idle Timeout',
						'rate_limit_label' => 'Rate Limit',
						'prevent_proxy_connections_label' => 'Prevent Proxy Connections',
						'use_native_transport_label' => 'Use Native Transport',
						'hide_online_players_label' => 'Hide Online Players',
						'entity_broadcast_range_percentage_label' => 'Entity Broadcast Range Percentage',
						'max_chained_neighbor_updates_label' => 'Max Chained Neighbor Updates',
						'pause_when_empty_seconds_label' => 'Pause When Empty (seconds)',
						'status_heartbeat_interval_label' => 'Status Heartbeat Interval',
						'bug_report_link_label' => 'Bug Report Link',
						'require_resource_pack_label' => 'Require Resource Pack',
						'resource_pack_label' => 'Resource Pack URL',
						'resource_pack_id_label' => 'Resource Pack ID',
						'resource_pack_prompt_label' => 'Resource Pack Prompt',
						'resource_pack_sha1_label' => 'Resource Pack SHA1',
						'text_filtering_config_label' => 'Text Filtering Config',
						'text_filtering_version_label' => 'Text Filtering Version',

						// Advanced Section
						'section_advanced' => 'Advanced',
						'enable_jmx_monitoring_label' => 'Enable JMX Monitoring',
						'enable_code_of_conduct_label' => 'Enable Code of Conduct',
						'initial_enabled_packs_label' => 'Initial Enabled Packs',
						'initial_disabled_packs_label' => 'Initial Disabled Packs',
						'management_server_host_label' => 'Management Server Host',
						'management_server_port_label' => 'Management Server Port',
						'management_server_enabled_label' => 'Management Server Enabled',
						'management_server_tls_enabled_label' => 'Management Server TLS Enabled',
						'management_server_allowed_origins_label' => 'Management Server Allowed Origins',
						'management_server_tls_keystore_label' => 'Management Server TLS Keystore',
						'management_server_tls_keystore_password_label' => 'Management Server TLS Keystore Password',
						'management_server_secret_label' => 'Management Server Secret',
            ],
            'es' => [
						// Basic Section
						'section_basic' => 'Básico',
						'motd_label' => 'Mensaje del Servidor (MOTD)',
						'server_name_label' => 'Nombre del Servidor',
						'level_name_label' => 'Nombre del Mundo',
						'server_ip_label' => 'IP del Servidor',
						'server_port_label' => 'Puerto del Servidor',
						'port_label' => 'Puerto',
						'max_players_label' => 'Máximo de Jugadores',
						'online_mode_label' => 'Modo En Línea',

						// Gameplay Section
						'section_gameplay' => 'Juego',
						'gamemode_label' => 'Modo de Juego',
						'gamemode_survival' => 'Supervivencia',
						'gamemode_creative' => 'Creativo',
						'gamemode_adventure' => 'Aventura',
						'gamemode_spectator' => 'Espectador',
						'difficulty_label' => 'Dificultad',
						'difficulty_peaceful' => 'Pacífico',
						'difficulty_easy' => 'Fácil',
						'difficulty_normal' => 'Normal',
						'difficulty_hard' => 'Difícil',
						'pvp_label' => 'PvP',
						'force_gamemode_label' => 'Forzar Modo de Juego',
						'hardcore_label' => 'Hardcore',

						// Spawning Section
						'section_spawning' => 'Generación',
						'spawn_animals_label' => 'Spawn de Animales',
						'spawn_monsters_label' => 'Spawn de Monstruos',
						'spawn_npcs_label' => 'Spawn de NPCs',
						'spawn_protection_label' => 'Protección de Spawn',

						// World Section
						'section_world' => 'Mundo',
						'level_seed_label' => 'Semilla del Mundo',
						'level_type_label' => 'Tipo de Mundo',
						'level_type_default' => 'Por Defecto',
						'level_type_flat' => 'Plano',
						'level_type_large_biomes' => 'Biomas Grandes',
						'generator_settings_label' => 'Configuración del Generador',
						'generate_structures_label' => 'Generar Estructuras',
						'max_world_size_label' => 'Tamaño Máximo del Mundo',

						// Performance Section
						'section_performance' => 'Rendimiento',
						'view_distance_label' => 'Distancia de Vista',
						'simulation_distance_label' => 'Distancia de Simulación',
						'network_compression_threshold_label' => 'Umbral de Compresión de Red',
						'max_tick_time_label' => 'Tiempo Máximo de Tick',
						'sync_chunk_writes_label' => 'Sincronizar Escrituras de Chunks',
						'region_file_compression_label' => 'Compresión de Archivo de Región',
						'compression_deflate' => 'Deflate',
						'compression_none' => 'Ninguno',

						// Security Section
						'section_security' => 'Seguridad',
						'enable_command_block_label' => 'Habilitar Bloques de Comando',
						'op_permission_level_label' => 'Nivel de Permiso OP',
						'function_permission_level_label' => 'Nivel de Permiso de Función',
						'enforce_whitelist_label' => 'Aplicar Lista Blanca',
						'white_list_label' => 'Lista Blanca',
						'broadcast_console_to_ops_label' => 'Transmitir Consola a OPs',
						'broadcast_rcon_to_ops_label' => 'Transmitir RCON a OPs',
						'enforce_secure_profile_label' => 'Aplicar Perfil Seguro',

						// Query Section
						'section_query' => 'Consulta',
						'enable_query_label' => 'Habilitar Consulta',
						'query_port_label' => 'Puerto de Consulta',

						// RCON Section
						'section_rcon' => 'RCON',
						'enable_rcon_label' => 'Habilitar RCON',
						'rcon_port_label' => 'Puerto RCON',
						'rcon_password_label' => 'Contraseña RCON',

						// Other Section
						'section_other' => 'Otros',
						'accepts_transfers_label' => 'Acepta Transferencias',
						'allow_flight_label' => 'Permitir Vuelo',
						'debug_label' => 'Depuración',
						'enable_status_label' => 'Habilitar Estado',
						'log_ips_label' => 'Registrar IPs',
						'player_idle_timeout_label' => 'Tiempo de Espera de Inactividad del Jugador',
						'rate_limit_label' => 'Límite de Velocidad',
						'prevent_proxy_connections_label' => 'Prevenir Conexiones Proxy',
						'use_native_transport_label' => 'Usar Transporte Nativo',
						'hide_online_players_label' => 'Ocultar Jugadores En Línea',
						'entity_broadcast_range_percentage_label' => 'Porcentaje de Rango de Transmisión de Entidad',
						'max_chained_neighbor_updates_label' => 'Máximo de Actualizaciones de Vecino Encadenado',
						'pause_when_empty_seconds_label' => 'Pausa Cuando Está Vacío (segundos)',
						'status_heartbeat_interval_label' => 'Intervalo de Latido del Estado',
						'bug_report_link_label' => 'Enlace de Informe de Errores',
						'require_resource_pack_label' => 'Requerir Pack de Recursos',
						'resource_pack_label' => 'URL del Pack de Recursos',
						'resource_pack_id_label' => 'ID del Pack de Recursos',
						'resource_pack_prompt_label' => 'Aviso del Pack de Recursos',
						'resource_pack_sha1_label' => 'SHA1 del Pack de Recursos',
						'text_filtering_config_label' => 'Configuración de Filtrado de Texto',
						'text_filtering_version_label' => 'Versión de Filtrado de Texto',

						// Advanced Section
						'section_advanced' => 'Avanzado',
						'enable_jmx_monitoring_label' => 'Habilitar Monitoreo JMX',
						'enable_code_of_conduct_label' => 'Habilitar Código de Conducta',
						'initial_enabled_packs_label' => 'Packs Inicialmente Habilitados',
						'initial_disabled_packs_label' => 'Packs Inicialmente Deshabilitados',
						'management_server_host_label' => 'Host del Servidor de Gestión',
						'management_server_port_label' => 'Puerto del Servidor de Gestión',
						'management_server_enabled_label' => 'Servidor de Gestión Habilitado',
						'management_server_tls_enabled_label' => 'TLS del Servidor de Gestión Habilitado',
						'management_server_allowed_origins_label' => 'Orígenes Permitidos del Servidor de Gestión',
						'management_server_tls_keystore_label' => 'Almacén de Claves TLS del Servidor de Gestión',
						'management_server_tls_keystore_password_label' => 'Contraseña del Almacén de Claves TLS del Servidor de Gestión',
						'management_server_secret_label' => 'Secreto del Servidor de Gestión',
            ],
            'fr' => [
						// Basic Section
						'section_basic' => 'Basique',
						'motd_label' => 'Message du Serveur (MOTD)',
						'server_name_label' => 'Nom du Serveur',
						'level_name_label' => 'Nom du Monde',
						'server_ip_label' => 'IP du Serveur',
						'server_port_label' => 'Port du Serveur',
						'port_label' => 'Port',
						'max_players_label' => 'Nombre Max de Joueurs',
						'online_mode_label' => 'Mode En Ligne',

						// Gameplay Section
						'section_gameplay' => 'Jeu',
						'gamemode_label' => 'Mode de Jeu',
						'gamemode_survival' => 'Survie',
						'gamemode_creative' => 'Créatif',
						'gamemode_adventure' => 'Aventure',
						'gamemode_spectator' => 'Spectateur',
						'difficulty_label' => 'Difficulté',
						'difficulty_peaceful' => 'Paisible',
						'difficulty_easy' => 'Facile',
						'difficulty_normal' => 'Normal',
						'difficulty_hard' => 'Difficile',
						'pvp_label' => 'PvP',
						'force_gamemode_label' => 'Forcer le Mode de Jeu',
						'hardcore_label' => 'Hardcore',

						// Spawning Section
						'section_spawning' => 'Apparition',
						'spawn_animals_label' => 'Spawn Animaux',
						'spawn_monsters_label' => 'Spawn Monstres',
						'spawn_npcs_label' => 'Spawn PNJ',
						'spawn_protection_label' => 'Protection du Spawn',

						// World Section
						'section_world' => 'Monde',
						'level_seed_label' => 'Graine du Monde',
						'level_type_label' => 'Type de Monde',
						'level_type_default' => 'Par Défaut',
						'level_type_flat' => 'Plat',
						'level_type_large_biomes' => 'Grands Biomes',
						'generator_settings_label' => 'Paramètres du Générateur',
						'generate_structures_label' => 'Générer des Structures',
						'max_world_size_label' => 'Taille Max du Monde',

						// Performance Section
						'section_performance' => 'Performance',
						'view_distance_label' => 'Distance de Vue',
						'simulation_distance_label' => 'Distance de Simulation',
						'network_compression_threshold_label' => 'Seuil de Compression Réseau',
						'max_tick_time_label' => 'Temps Max du Tick',
						'sync_chunk_writes_label' => 'Synchroniser les Écritures de Chunks',
						'region_file_compression_label' => 'Compression du Fichier de Région',
						'compression_deflate' => 'Deflate',
						'compression_none' => 'Aucune',

						// Security Section
						'section_security' => 'Sécurité',
						'enable_command_block_label' => 'Activer les Blocs de Commande',
						'op_permission_level_label' => 'Niveau de Permission OP',
						'function_permission_level_label' => 'Niveau de Permission Fonction',
						'enforce_whitelist_label' => 'Appliquer la Liste Blanche',
						'white_list_label' => 'Liste Blanche',
						'broadcast_console_to_ops_label' => 'Diffuser la Console aux OPs',
						'broadcast_rcon_to_ops_label' => 'Diffuser RCON aux OPs',
						'enforce_secure_profile_label' => 'Appliquer le Profil Sécurisé',

						// Query Section
						'section_query' => 'Requête',
						'enable_query_label' => 'Activer les Requêtes',
						'query_port_label' => 'Port de Requête',

						// RCON Section
						'section_rcon' => 'RCON',
						'enable_rcon_label' => 'Activer RCON',
						'rcon_port_label' => 'Port RCON',
						'rcon_password_label' => 'Mot de passe RCON',

						// Other Section
						'section_other' => 'Autres',
						'accepts_transfers_label' => 'Accepte les Transferts',
						'allow_flight_label' => 'Autoriser le Vol',
						'debug_label' => 'Débogage',
						'enable_status_label' => 'Activer le Statut',
						'log_ips_label' => 'Journaliser les IPs',
						'player_idle_timeout_label' => 'Délai d\'Expiration du Joueur Inactif',
						'rate_limit_label' => 'Limite de Débit',
						'prevent_proxy_connections_label' => 'Empêcher les Connexions Proxy',
						'use_native_transport_label' => 'Utiliser le Transport Natif',
						'hide_online_players_label' => 'Masquer les Joueurs En Ligne',
						'entity_broadcast_range_percentage_label' => 'Pourcentage de Plage de Diffusion d\'Entité',
						'max_chained_neighbor_updates_label' => 'Max Mises à Jour Voisin Chaîné',
						'pause_when_empty_seconds_label' => 'Pause Quand Vide (secondes)',
						'status_heartbeat_interval_label' => 'Intervalle de Battement de Cœur du Statut',
						'bug_report_link_label' => 'Lien de Signalement de Bug',
						'require_resource_pack_label' => 'Exiger un Pack de Ressources',
						'resource_pack_label' => 'URL du Pack de Ressources',
						'resource_pack_id_label' => 'ID du Pack de Ressources',
						'resource_pack_prompt_label' => 'Invite du Pack de Ressources',
						'resource_pack_sha1_label' => 'SHA1 du Pack de Ressources',
						'text_filtering_config_label' => 'Config de Filtrage de Texte',
						'text_filtering_version_label' => 'Version de Filtrage de Texte',

						// Advanced Section
						'section_advanced' => 'Avancé',
						'enable_jmx_monitoring_label' => 'Activer la Surveillance JMX',
						'enable_code_of_conduct_label' => 'Activer le Code de Conduite',
						'initial_enabled_packs_label' => 'Packs Initialement Activés',
						'initial_disabled_packs_label' => 'Packs Initialement Désactivés',
						'management_server_host_label' => 'Hôte du Serveur de Gestion',
						'management_server_port_label' => 'Port du Serveur de Gestion',
						'management_server_enabled_label' => 'Serveur de Gestion Activé',
						'management_server_tls_enabled_label' => 'TLS du Serveur de Gestion Activé',
						'management_server_allowed_origins_label' => 'Origines Autorisées du Serveur de Gestion',
						'management_server_tls_keystore_label' => 'Magasin de Clés TLS du Serveur de Gestion',
						'management_server_tls_keystore_password_label' => 'Mot de passe du Magasin de Clés TLS du Serveur de Gestion',
						'management_server_secret_label' => 'Secret du Serveur de Gestion',
            ],
            'it' => [
						// Basic Section
						'section_basic' => 'Base',
						'motd_label' => 'Messaggio del Server (MOTD)',
						'server_name_label' => 'Nome Server',
						'level_name_label' => 'Nome Mondo',
						'server_ip_label' => 'IP Server',
						'server_port_label' => 'Porta Server',
						'port_label' => 'Porta',
						'max_players_label' => 'Max Giocatori',
						'online_mode_label' => 'Modalità Online',

						// Gameplay Section
						'section_gameplay' => 'Gameplay',
						'gamemode_label' => 'Modalità Gioco',
						'gamemode_survival' => 'Sopravvivenza',
						'gamemode_creative' => 'Creativa',
						'gamemode_adventure' => 'Avventura',
						'gamemode_spectator' => 'Spettatore',
						'difficulty_label' => 'Difficoltà',
						'difficulty_peaceful' => 'Pacifico',
						'difficulty_easy' => 'Facile',
						'difficulty_normal' => 'Normale',
						'difficulty_hard' => 'Difficile',
						'pvp_label' => 'PvP',
						'force_gamemode_label' => 'Forza Modalità Gioco',
						'hardcore_label' => 'Hardcore',

						// Spawning Section
						'section_spawning' => 'Generazione',
						'spawn_animals_label' => 'Spawn Animali',
						'spawn_monsters_label' => 'Spawn Mostri',
						'spawn_npcs_label' => 'Spawn NPC',
						'spawn_protection_label' => 'Protezione Spawn',

						// World Section
						'section_world' => 'Mondo',
						'level_seed_label' => 'Seed Mondo',
						'level_type_label' => 'Tipo Mondo',
						'level_type_default' => 'Predefinito',
						'level_type_flat' => 'Piatto',
						'level_type_large_biomes' => 'Biomi Grandi',
						'generator_settings_label' => 'Impostazioni Generatore',
						'generate_structures_label' => 'Genera Strutture',
						'max_world_size_label' => 'Max Dimensione Mondo',

						// Performance Section
						'section_performance' => 'Prestazioni',
						'view_distance_label' => 'Distanza di Visualizzazione',
						'simulation_distance_label' => 'Distanza di Simulazione',
						'network_compression_threshold_label' => 'Soglia Compressione Rete',
						'max_tick_time_label' => 'Max Tempo Tick',
						'sync_chunk_writes_label' => 'Sincronizza Scritture Chunk',
						'region_file_compression_label' => 'Compressione File Regione',
						'compression_deflate' => 'Deflate',
						'compression_none' => 'Nessuna',

						// Security Section
						'section_security' => 'Sicurezza',
						'enable_command_block_label' => 'Abilita Blocchi Comando',
						'op_permission_level_label' => 'Livello Permesso OP',
						'function_permission_level_label' => 'Livello Permesso Funzione',
						'enforce_whitelist_label' => 'Applica Lista Bianca',
						'white_list_label' => 'Lista Bianca',
						'broadcast_console_to_ops_label' => 'Trasmetti Console a OP',
						'broadcast_rcon_to_ops_label' => 'Trasmetti RCON a OP',
						'enforce_secure_profile_label' => 'Applica Profilo Sicuro',

						// Query Section
						'section_query' => 'Query',
						'enable_query_label' => 'Abilita Query',
						'query_port_label' => 'Porta Query',

						// RCON Section
						'section_rcon' => 'RCON',
						'enable_rcon_label' => 'Abilita RCON',
						'rcon_port_label' => 'Porta RCON',
						'rcon_password_label' => 'Password RCON',

						// Other Section
						'section_other' => 'Altro',
						'accepts_transfers_label' => 'Accetta Trasferimenti',
						'allow_flight_label' => 'Consenti Volo',
						'debug_label' => 'Debug',
						'enable_status_label' => 'Abilita Stato',
						'log_ips_label' => 'Registra IP',
						'player_idle_timeout_label' => 'Timeout Inattività Giocatore',
						'rate_limit_label' => 'Limite Velocità',
						'prevent_proxy_connections_label' => 'Previeni Connessioni Proxy',
						'use_native_transport_label' => 'Usa Trasporto Nativo',
						'hide_online_players_label' => 'Nascondi Giocatori Online',
						'entity_broadcast_range_percentage_label' => 'Percentuale Range Broadcast Entità',
						'max_chained_neighbor_updates_label' => 'Max Aggiornamenti Vicino Concatenati',
						'pause_when_empty_seconds_label' => 'Pausa Quando Vuoto (secondi)',
						'status_heartbeat_interval_label' => 'Intervallo Heartbeat Stato',
						'bug_report_link_label' => 'Link Segnalazione Bug',
						'require_resource_pack_label' => 'Richiedi Resource Pack',
						'resource_pack_label' => 'URL Resource Pack',
						'resource_pack_id_label' => 'ID Resource Pack',
						'resource_pack_prompt_label' => 'Prompt Resource Pack',
						'resource_pack_sha1_label' => 'SHA1 Resource Pack',
						'text_filtering_config_label' => 'Config Filtro Testo',
						'text_filtering_version_label' => 'Versione Filtro Testo',

						// Advanced Section
						'section_advanced' => 'Avanzate',
						'enable_jmx_monitoring_label' => 'Abilita Monitoraggio JMX',
						'enable_code_of_conduct_label' => 'Abilita Codice Condotta',
						'initial_enabled_packs_label' => 'Pack Inizialmente Abilitati',
						'initial_disabled_packs_label' => 'Pack Inizialmente Disabilitati',
						'management_server_host_label' => 'Host Server Gestione',
						'management_server_port_label' => 'Porta Server Gestione',
						'management_server_enabled_label' => 'Server Gestione Abilitato',
						'management_server_tls_enabled_label' => 'TLS Server Gestione Abilitato',
						'management_server_allowed_origins_label' => 'Origini Consentite Server Gestione',
						'management_server_tls_keystore_label' => 'Keystore TLS Server Gestione',
						'management_server_tls_keystore_password_label' => 'Password Keystore TLS Server Gestione',
						'management_server_secret_label' => 'Segreto Server Gestione',
            ],
            'ja' => [
						// Basic Section
						'section_basic' => '基本',
						'motd_label' => 'サーバーメッセージ (MOTD)',
						'server_name_label' => 'サーバー名',
						'level_name_label' => 'ワールド名',
						'server_ip_label' => 'サーバーIP',
						'server_port_label' => 'サーバーポート',
						'port_label' => 'ポート',
						'max_players_label' => '最大プレイヤー数',
						'online_mode_label' => 'オンラインモード',

						// Gameplay Section
						'section_gameplay' => 'ゲームプレイ',
						'gamemode_label' => 'ゲームモード',
						'gamemode_survival' => 'サバイバル',
						'gamemode_creative' => 'クリエイティブ',
						'gamemode_adventure' => 'アドベンチャー',
						'gamemode_spectator' => 'スペクテーター',
						'difficulty_label' => '難易度',
						'difficulty_peaceful' => 'ピース',
						'difficulty_easy' => '簡単',
						'difficulty_normal' => '普通',
						'difficulty_hard' => '難しい',
						'pvp_label' => 'PVP',
						'force_gamemode_label' => 'ゲームモードを強制',
						'hardcore_label' => 'ハードコア',

						// Spawning Section
						'section_spawning' => 'スポーン',
						'spawn_animals_label' => '動物スポーン',
						'spawn_monsters_label' => 'モンスタースポーン',
						'spawn_npcs_label' => 'NPCスポーン',
						'spawn_protection_label' => 'スポーン保護',

						// World Section
						'section_world' => 'ワールド',
						'level_seed_label' => 'ワールドシード',
						'level_type_label' => 'ワールドタイプ',
						'level_type_default' => 'デフォルト',
						'level_type_flat' => 'フラット',
						'level_type_large_biomes' => '大きなバイオーム',
						'generator_settings_label' => 'ジェネレーター設定',
						'generate_structures_label' => '構造物を生成',
						'max_world_size_label' => 'ワールド最大サイズ',

						// Performance Section
						'section_performance' => 'パフォーマンス',
						'view_distance_label' => 'ビュー距離',
						'simulation_distance_label' => 'シミュレーション距離',
						'network_compression_threshold_label' => 'ネットワーク圧縮閾値',
						'max_tick_time_label' => '最大ティック時間',
						'sync_chunk_writes_label' => 'チャンク書き込みを同期',
						'region_file_compression_label' => 'リージョンファイル圧縮',
						'compression_deflate' => 'Deflate',
						'compression_none' => 'なし',

						// Security Section
						'section_security' => 'セキュリティ',
						'enable_command_block_label' => 'コマンドブロックを有効',
						'op_permission_level_label' => 'OP権限レベル',
						'function_permission_level_label' => '関数権限レベル',
						'enforce_whitelist_label' => 'ホワイトリストを強制',
						'white_list_label' => 'ホワイトリスト',
						'broadcast_console_to_ops_label' => 'コンソールをOPに配信',
						'broadcast_rcon_to_ops_label' => 'RCONをOPに配信',
						'enforce_secure_profile_label' => 'セキュアプロフィールを強制',

						// Query Section
						'section_query' => 'クエリ',
						'enable_query_label' => 'クエリを有効',
						'query_port_label' => 'クエリポート',

						// RCON Section
						'section_rcon' => 'RCON',
						'enable_rcon_label' => 'RCONを有効',
						'rcon_port_label' => 'RCONポート',
						'rcon_password_label' => 'RCONパスワード',

						// Other Section
						'section_other' => 'その他',
						'accepts_transfers_label' => '転送を受け入れ',
						'allow_flight_label' => '飛行を許可',
						'debug_label' => 'デバッグ',
						'enable_status_label' => 'ステータスを有効',
						'log_ips_label' => 'IPをログ',
						'player_idle_timeout_label' => 'プレイヤーアイドルタイムアウト',
						'rate_limit_label' => 'レート制限',
						'prevent_proxy_connections_label' => 'プロキシ接続を防止',
						'use_native_transport_label' => 'ネイティブトランスポート使用',
						'hide_online_players_label' => 'オンラインプレイヤーを非表示',
						'entity_broadcast_range_percentage_label' => 'エンティティブロードキャスト範囲パーセンテージ',
						'max_chained_neighbor_updates_label' => '最大チェーン隣接更新',
						'pause_when_empty_seconds_label' => '空の場合に一時停止(秒)',
						'status_heartbeat_interval_label' => 'ステータスハートビート間隔',
						'bug_report_link_label' => 'バグ報告リンク',
						'require_resource_pack_label' => 'リソースパックが必須',
						'resource_pack_label' => 'リソースパックURL',
						'resource_pack_id_label' => 'リソースパックID',
						'resource_pack_prompt_label' => 'リソースパックプロンプト',
						'resource_pack_sha1_label' => 'リソースパックSHA1',
						'text_filtering_config_label' => 'テキストフィルター設定',
						'text_filtering_version_label' => 'テキストフィルターバージョン',

						// Advanced Section
						'section_advanced' => '詳細',
						'enable_jmx_monitoring_label' => 'JMX監視を有効',
						'enable_code_of_conduct_label' => '行動規範を有効',
						'initial_enabled_packs_label' => '最初から有効なパック',
						'initial_disabled_packs_label' => '最初から無効なパック',
						'management_server_host_label' => '管理サーバーホスト',
						'management_server_port_label' => '管理サーバーポート',
						'management_server_enabled_label' => '管理サーバーを有効',
						'management_server_tls_enabled_label' => '管理サーバーTLSを有効',
						'management_server_allowed_origins_label' => '管理サーバー許可オリジン',
						'management_server_tls_keystore_label' => '管理サーバーTLSキーストア',
						'management_server_tls_keystore_password_label' => '管理サーバーTLSキーストアパスワード',
						'management_server_secret_label' => '管理サーバーシークレット',
            ],
            'ko' => [
						// Basic Section
						'section_basic' => '기본',
						'motd_label' => '서버 메시지 (MOTD)',
						'server_name_label' => '서버 이름',
						'level_name_label' => '월드 이름',
						'server_ip_label' => '서버 IP',
						'server_port_label' => '서버 포트',
						'port_label' => '포트',
						'max_players_label' => '최대 플레이어 수',
						'online_mode_label' => '온라인 모드',

						// Gameplay Section
						'section_gameplay' => '게임플레이',
						'gamemode_label' => '게임 모드',
						'gamemode_survival' => '생존',
						'gamemode_creative' => '창의',
						'gamemode_adventure' => '모험',
						'gamemode_spectator' => '관전',
						'difficulty_label' => '난이도',
						'difficulty_peaceful' => '평화',
						'difficulty_easy' => '쉬움',
						'difficulty_normal' => '보통',
						'difficulty_hard' => '어려움',
						'pvp_label' => 'PVP',
						'force_gamemode_label' => '게임 모드 강제',
						'hardcore_label' => '하드코어',

						// Spawning Section
						'section_spawning' => '생성',
						'spawn_animals_label' => '동물 생성',
						'spawn_monsters_label' => '몬스터 생성',
						'spawn_npcs_label' => 'NPC 생성',
						'spawn_protection_label' => '스폰 보호',

						// World Section
						'section_world' => '월드',
						'level_seed_label' => '월드 시드',
						'level_type_label' => '월드 유형',
						'level_type_default' => '기본값',
						'level_type_flat' => '평탄',
						'level_type_large_biomes' => '큰 생물 군계',
						'generator_settings_label' => '생성기 설정',
						'generate_structures_label' => '구조 생성',
						'max_world_size_label' => '최대 월드 크기',

						// Performance Section
						'section_performance' => '성능',
						'view_distance_label' => '렌더 거리',
						'simulation_distance_label' => '시뮬레이션 거리',
						'network_compression_threshold_label' => '네트워크 압축 임계값',
						'max_tick_time_label' => '최대 틱 시간',
						'sync_chunk_writes_label' => '청크 쓰기 동기화',
						'region_file_compression_label' => '지역 파일 압축',
						'compression_deflate' => 'Deflate',
						'compression_none' => '없음',

						// Security Section
						'section_security' => '보안',
						'enable_command_block_label' => '명령어 블록 활성화',
						'op_permission_level_label' => 'OP 권한 수준',
						'function_permission_level_label' => '함수 권한 수준',
						'enforce_whitelist_label' => '화이트리스트 강제',
						'white_list_label' => '화이트리스트',
						'broadcast_console_to_ops_label' => 'OP에 콘솔 브로드캐스트',
						'broadcast_rcon_to_ops_label' => 'OP에 RCON 브로드캐스트',
						'enforce_secure_profile_label' => '보안 프로필 강제',

						// Query Section
						'section_query' => '쿼리',
						'enable_query_label' => '쿼리 활성화',
						'query_port_label' => '쿼리 포트',

						// RCON Section
						'section_rcon' => 'RCON',
						'enable_rcon_label' => 'RCON 활성화',
						'rcon_port_label' => 'RCON 포트',
						'rcon_password_label' => 'RCON 비밀번호',

						// Other Section
						'section_other' => '기타',
						'accepts_transfers_label' => '전송 수락',
						'allow_flight_label' => '비행 허용',
						'debug_label' => '디버그',
						'enable_status_label' => '상태 활성화',
						'log_ips_label' => 'IP 기록',
						'player_idle_timeout_label' => '플레이어 유휴 시간 초과',
						'rate_limit_label' => '속도 제한',
						'prevent_proxy_connections_label' => '프록시 연결 방지',
						'use_native_transport_label' => '기본 전송 사용',
						'hide_online_players_label' => '온라인 플레이어 숨기기',
						'entity_broadcast_range_percentage_label' => '엔티티 브로드캐스트 범위 백분율',
						'max_chained_neighbor_updates_label' => '최대 연쇄 이웃 업데이트',
						'pause_when_empty_seconds_label' => '비어있을 때 일시 중지 (초)',
						'status_heartbeat_interval_label' => '상태 하트비트 간격',
						'bug_report_link_label' => '버그 보고 링크',
						'require_resource_pack_label' => '리소스 팩 필요',
						'resource_pack_label' => '리소스 팩 URL',
						'resource_pack_id_label' => '리소스 팩 ID',
						'resource_pack_prompt_label' => '리소스 팩 프롬프트',
						'resource_pack_sha1_label' => '리소스 팩 SHA1',
						'text_filtering_config_label' => '텍스트 필터링 설정',
						'text_filtering_version_label' => '텍스트 필터링 버전',

						// Advanced Section
						'section_advanced' => '고급',
						'enable_jmx_monitoring_label' => 'JMX 모니터링 활성화',
						'enable_code_of_conduct_label' => '행동 강령 활성화',
						'initial_enabled_packs_label' => '초기 활성화된 팩',
						'initial_disabled_packs_label' => '초기 비활성화된 팩',
						'management_server_host_label' => '관리 서버 호스트',
						'management_server_port_label' => '관리 서버 포트',
						'management_server_enabled_label' => '관리 서버 활성화',
						'management_server_tls_enabled_label' => '관리 서버 TLS 활성화',
						'management_server_allowed_origins_label' => '관리 서버 허용 출처',
						'management_server_tls_keystore_label' => '관리 서버 TLS 키 저장소',
						'management_server_tls_keystore_password_label' => '관리 서버 TLS 키 저장소 비밀번호',
						'management_server_secret_label' => '관리 서버 시크릿',
            ],
            'nl' => [
						// Basic Section
						'section_basic' => 'Basis',
						'motd_label' => 'Serverbericht (MOTD)',
						'server_name_label' => 'Servernaam',
						'level_name_label' => 'Wereldnaam',
						'server_ip_label' => 'Server-IP',
						'server_port_label' => 'Serverpoort',
						'port_label' => 'Poort',
						'max_players_label' => 'Maximum aantal spelers',
						'online_mode_label' => 'Online modus',

						// Gameplay Section
						'section_gameplay' => 'Gameplay',
						'gamemode_label' => 'Speelmodus',
						'gamemode_survival' => 'Overleving',
						'gamemode_creative' => 'Creatief',
						'gamemode_adventure' => 'Avontuur',
						'gamemode_spectator' => 'Toeschouwer',
						'difficulty_label' => 'Moeilijkheidsgraad',
						'difficulty_peaceful' => 'Vredig',
						'difficulty_easy' => 'Gemakkelijk',
						'difficulty_normal' => 'Normaal',
						'difficulty_hard' => 'Moeilijk',
						'pvp_label' => 'PVP',
						'force_gamemode_label' => 'Speelmodus afdwingen',
						'hardcore_label' => 'Hardcore',

						// Spawning Section
						'section_spawning' => 'Spawning',
						'spawn_animals_label' => 'Diersspawning',
						'spawn_monsters_label' => 'Monsterspawning',
						'spawn_npcs_label' => 'NPC-vorming',
						'spawn_protection_label' => 'Spawn-bescherming',

						// World Section
						'section_world' => 'Wereld',
						'level_seed_label' => 'Wereld Seed',
						'level_type_label' => 'Wereldtype',
						'level_type_default' => 'Standaard',
						'level_type_flat' => 'Plat',
						'level_type_large_biomes' => 'Grote Biomen',
						'generator_settings_label' => 'Generatorinstellingen',
						'generate_structures_label' => 'Genereer Structuren',
						'max_world_size_label' => 'Max. Wereldgrootte',

						// Performance Section
						'section_performance' => 'Prestaties',
						'view_distance_label' => 'Zichtafstand',
						'simulation_distance_label' => 'Simulatieafstand',
						'network_compression_threshold_label' => 'Drempel Netwerkcompressie',
						'max_tick_time_label' => 'Max. Tick-tijd',
						'sync_chunk_writes_label' => 'Chunk-schrijfbewerkingen synchroniseren',
						'region_file_compression_label' => 'Regio-bestandscompressie',
						'compression_deflate' => 'Deflate',
						'compression_none' => 'Geen',

						// Security Section
						'section_security' => 'Beveiliging',
						'enable_command_block_label' => 'Opdrachtblokken inschakelen',
						'op_permission_level_label' => 'OP-Toestemmingsniveau',
						'function_permission_level_label' => 'Functietoestemmingsniveau',
						'enforce_whitelist_label' => 'Whitelist afdwingen',
						'white_list_label' => 'Whitelist',
						'broadcast_console_to_ops_label' => 'Console naar OPs uitzenden',
						'broadcast_rcon_to_ops_label' => 'RCON naar OPs uitzenden',
						'enforce_secure_profile_label' => 'Veilig profiel afdwingen',

						// Query Section
						'section_query' => 'Query',
						'enable_query_label' => 'Query inschakelen',
						'query_port_label' => 'Query-poort',

						// RCON Section
						'section_rcon' => 'RCON',
						'enable_rcon_label' => 'RCON inschakelen',
						'rcon_port_label' => 'RCON-poort',
						'rcon_password_label' => 'RCON-wachtwoord',

						// Other Section
						'section_other' => 'Overige',
						'accepts_transfers_label' => 'Accepteert Overdrachten',
						'allow_flight_label' => 'Vlucht toestaan',
						'debug_label' => 'Foutopsporing',
						'enable_status_label' => 'Status inschakelen',
						'log_ips_label' => 'IP\'s registreren',
						'player_idle_timeout_label' => 'Speler Idle Timeout',
						'rate_limit_label' => 'Snelheidslimiet',
						'prevent_proxy_connections_label' => 'Proxyverbindingen voorkomen',
						'use_native_transport_label' => 'Gebruik Inheemse Transport',
						'hide_online_players_label' => 'Online spelers verbergen',
						'entity_broadcast_range_percentage_label' => 'Entity-uitzendingsbereik Percentage',
						'max_chained_neighbor_updates_label' => 'Max. Gekoppelde Nabuur Updates',
						'pause_when_empty_seconds_label' => 'Pauze Wanneer Leeg (seconden)',
						'status_heartbeat_interval_label' => 'Status Hartslag Interval',
						'bug_report_link_label' => 'Bug Rapport Link',
						'require_resource_pack_label' => 'Resourcepack Vereist',
						'resource_pack_label' => 'Resourcepack URL',
						'resource_pack_id_label' => 'Resourcepack ID',
						'resource_pack_prompt_label' => 'Resourcepack Prompt',
						'resource_pack_sha1_label' => 'Resourcepack SHA1',
						'text_filtering_config_label' => 'Tekstfilter Configuratie',
						'text_filtering_version_label' => 'Tekstfilter Versie',

						// Advanced Section
						'section_advanced' => 'Geavanceerd',
						'enable_jmx_monitoring_label' => 'JMX-monitoring inschakelen',
						'enable_code_of_conduct_label' => 'Gedragscode inschakelen',
						'initial_enabled_packs_label' => 'Aanvankelijk Ingeschakelde Pakketten',
						'initial_disabled_packs_label' => 'Aanvankelijk Uitgeschakelde Pakketten',
						'management_server_host_label' => 'Management Server Host',
						'management_server_port_label' => 'Management Server Poort',
						'management_server_enabled_label' => 'Management Server Ingeschakeld',
						'management_server_tls_enabled_label' => 'Management Server TLS Ingeschakeld',
						'management_server_allowed_origins_label' => 'Management Server Toegestane Oorsprongen',
						'management_server_tls_keystore_label' => 'Management Server TLS Keystore',
						'management_server_tls_keystore_password_label' => 'Management Server TLS Keystore Wachtwoord',
						'management_server_secret_label' => 'Management Server Geheim',
            ],
            'pl' => [
						// Basic Section
						'section_basic' => 'Podstawy',
						'motd_label' => 'Wiadomość serwera (MOTD)',
						'server_name_label' => 'Nazwa serwera',
						'level_name_label' => 'Nazwa świata',
						'server_ip_label' => 'IP serwera',
						'server_port_label' => 'Port serwera',
						'port_label' => 'Port',
						'max_players_label' => 'Maksymalna liczba graczy',
						'online_mode_label' => 'Tryb online',

						// Gameplay Section
						'section_gameplay' => 'Rozgrywka',
						'gamemode_label' => 'Tryb gry',
						'gamemode_survival' => 'Przetrwanie',
						'gamemode_creative' => 'Kreatywny',
						'gamemode_adventure' => 'Przygoda',
						'gamemode_spectator' => 'Obserwator',
						'difficulty_label' => 'Poziom trudności',
						'difficulty_peaceful' => 'Spokojny',
						'difficulty_easy' => 'Łatwy',
						'difficulty_normal' => 'Normalny',
						'difficulty_hard' => 'Trudny',
						'pvp_label' => 'PVP',
						'force_gamemode_label' => 'Wymuś tryb gry',
						'hardcore_label' => 'Hardcore',

						// Spawning Section
						'section_spawning' => 'Pojawianie się',
						'spawn_animals_label' => 'Pojawianie się zwierząt',
						'spawn_monsters_label' => 'Pojawianie się potworów',
						'spawn_npcs_label' => 'Pojawianie się NPC',
						'spawn_protection_label' => 'Ochrona spawnu',

						// World Section
						'section_world' => 'Świat',
						'level_seed_label' => 'Seed świata',
						'level_type_label' => 'Typ świata',
						'level_type_default' => 'Domyślny',
						'level_type_flat' => 'Płaski',
						'level_type_large_biomes' => 'Duże biomy',
						'generator_settings_label' => 'Ustawienia generatora',
						'generate_structures_label' => 'Generuj struktury',
						'max_world_size_label' => 'Maksymalny rozmiar świata',

						// Performance Section
						'section_performance' => 'Wydajność',
						'view_distance_label' => 'Zasięg widoczności',
						'simulation_distance_label' => 'Zasięg symulacji',
						'network_compression_threshold_label' => 'Próg kompresji sieciowej',
						'max_tick_time_label' => 'Maksymalny czas ticku',
						'sync_chunk_writes_label' => 'Zsynchronizuj zapis chunków',
						'region_file_compression_label' => 'Kompresja pliku regionu',
						'compression_deflate' => 'Deflate',
						'compression_none' => 'Brak',

						// Security Section
						'section_security' => 'Bezpieczeństwo',
						'enable_command_block_label' => 'Włącz bloki poleceń',
						'op_permission_level_label' => 'Poziom uprawnień OP',
						'function_permission_level_label' => 'Poziom uprawnień funkcji',
						'enforce_whitelist_label' => 'Wymuś whitelist',
						'white_list_label' => 'Whitelist',
						'broadcast_console_to_ops_label' => 'Transmituj konsolę do OP',
						'broadcast_rcon_to_ops_label' => 'Transmituj RCON do OP',
						'enforce_secure_profile_label' => 'Wymuś bezpieczny profil',

						// Query Section
						'section_query' => 'Query',
						'enable_query_label' => 'Włącz Query',
						'query_port_label' => 'Port Query',

						// RCON Section
						'section_rcon' => 'RCON',
						'enable_rcon_label' => 'Włącz RCON',
						'rcon_port_label' => 'Port RCON',
						'rcon_password_label' => 'Hasło RCON',

						// Other Section
						'section_other' => 'Inne',
						'accepts_transfers_label' => 'Akceptuj transfery',
						'allow_flight_label' => 'Zezwól na lot',
						'debug_label' => 'Debugowanie',
						'enable_status_label' => 'Włącz status',
						'log_ips_label' => 'Loguj IP',
						'player_idle_timeout_label' => 'Limit czasu bezczynności gracza',
						'rate_limit_label' => 'Limit szybkości',
						'prevent_proxy_connections_label' => 'Zapobiegaj połączeniom proxy',
						'use_native_transport_label' => 'Użyj transportu natywnego',
						'hide_online_players_label' => 'Ukryj graczy online',
						'entity_broadcast_range_percentage_label' => 'Procent zasięgu transmisji jednostek',
						'max_chained_neighbor_updates_label' => 'Maksymalne połączone aktualizacje sąsiadów',
						'pause_when_empty_seconds_label' => 'Pauza gdy pusty (sekundy)',
						'status_heartbeat_interval_label' => 'Interwał heartbeatu statusu',
						'bug_report_link_label' => 'Link do raportu błędu',
						'require_resource_pack_label' => 'Wymagaj pakietu zasobów',
						'resource_pack_label' => 'URL pakietu zasobów',
						'resource_pack_id_label' => 'ID pakietu zasobów',
						'resource_pack_prompt_label' => 'Monit pakietu zasobów',
						'resource_pack_sha1_label' => 'SHA1 pakietu zasobów',
						'text_filtering_config_label' => 'Konfiguracja filtrowania tekstu',
						'text_filtering_version_label' => 'Wersja filtrowania tekstu',

						// Advanced Section
						'section_advanced' => 'Zaawansowane',
						'enable_jmx_monitoring_label' => 'Włącz monitorowanie JMX',
						'enable_code_of_conduct_label' => 'Włącz kodeks postępowania',
						'initial_enabled_packs_label' => 'Wstępnie włączone pakiety',
						'initial_disabled_packs_label' => 'Wstępnie wyłączone pakiety',
						'management_server_host_label' => 'Host serwera zarządzania',
						'management_server_port_label' => 'Port serwera zarządzania',
						'management_server_enabled_label' => 'Serwer zarządzania włączony',
						'management_server_tls_enabled_label' => 'TLS serwera zarządzania włączony',
						'management_server_allowed_origins_label' => 'Dozwolone pochodzenia serwera zarządzania',
						'management_server_tls_keystore_label' => 'Keystore TLS serwera zarządzania',
						'management_server_tls_keystore_password_label' => 'Hasło keystore TLS serwera zarządzania',
						'management_server_secret_label' => 'Tajemnica serwera zarządzania',
            ],
            'pt' => [
						// Basic Section
						'section_basic' => 'Básico',
						'motd_label' => 'Mensagem do Servidor (MOTD)',
						'server_name_label' => 'Nome do Servidor',
						'level_name_label' => 'Nome do Mundo',
						'server_ip_label' => 'IP do Servidor',
						'server_port_label' => 'Porta do Servidor',
						'port_label' => 'Porta',
						'max_players_label' => 'Número Máximo de Jogadores',
						'online_mode_label' => 'Modo Online',

						// Gameplay Section
						'section_gameplay' => 'Jogabilidade',
						'gamemode_label' => 'Modo de Jogo',
						'gamemode_survival' => 'Sobrevivência',
						'gamemode_creative' => 'Criativo',
						'gamemode_adventure' => 'Aventura',
						'gamemode_spectator' => 'Espectador',
						'difficulty_label' => 'Dificuldade',
						'difficulty_peaceful' => 'Pacífico',
						'difficulty_easy' => 'Fácil',
						'difficulty_normal' => 'Normal',
						'difficulty_hard' => 'Difícil',
						'pvp_label' => 'PVP',
						'force_gamemode_label' => 'Forçar Modo de Jogo',
						'hardcore_label' => 'Hardcore',

						// Spawning Section
						'section_spawning' => 'Desova',
						'spawn_animals_label' => 'Spawn de Animais',
						'spawn_monsters_label' => 'Spawn de Monstros',
						'spawn_npcs_label' => 'Spawn de NPCs',
						'spawn_protection_label' => 'Proteção de Spawn',

						// World Section
						'section_world' => 'Mundo',
						'level_seed_label' => 'Seed do Mundo',
						'level_type_label' => 'Tipo de Mundo',
						'level_type_default' => 'Padrão',
						'level_type_flat' => 'Plano',
						'level_type_large_biomes' => 'Biomas Grandes',
						'generator_settings_label' => 'Configurações do Gerador',
						'generate_structures_label' => 'Gerar Estruturas',
						'max_world_size_label' => 'Tamanho Máximo do Mundo',

						// Performance Section
						'section_performance' => 'Desempenho',
						'view_distance_label' => 'Distância de Visão',
						'simulation_distance_label' => 'Distância de Simulação',
						'network_compression_threshold_label' => 'Limite de Compressão de Rede',
						'max_tick_time_label' => 'Tempo Máximo do Tick',
						'sync_chunk_writes_label' => 'Sincronizar Escritas de Chunks',
						'region_file_compression_label' => 'Compressão de Arquivo de Região',
						'compression_deflate' => 'Deflate',
						'compression_none' => 'Nenhuma',

						// Security Section
						'section_security' => 'Segurança',
						'enable_command_block_label' => 'Ativar Blocos de Comando',
						'op_permission_level_label' => 'Nível de Permissão do OP',
						'function_permission_level_label' => 'Nível de Permissão da Função',
						'enforce_whitelist_label' => 'Aplicar Lista Branca',
						'white_list_label' => 'Lista Branca',
						'broadcast_console_to_ops_label' => 'Transmitir Console para OPs',
						'broadcast_rcon_to_ops_label' => 'Transmitir RCON para OPs',
						'enforce_secure_profile_label' => 'Aplicar Perfil Seguro',

						// Query Section
						'section_query' => 'Consulta',
						'enable_query_label' => 'Ativar Consulta',
						'query_port_label' => 'Porta de Consulta',

						// RCON Section
						'section_rcon' => 'RCON',
						'enable_rcon_label' => 'Ativar RCON',
						'rcon_port_label' => 'Porta RCON',
						'rcon_password_label' => 'Senha RCON',

						// Other Section
						'section_other' => 'Outro',
						'accepts_transfers_label' => 'Aceita Transferências',
						'allow_flight_label' => 'Permitir Voo',
						'debug_label' => 'Depuração',
						'enable_status_label' => 'Ativar Status',
						'log_ips_label' => 'Registrar IPs',
						'player_idle_timeout_label' => 'Tempo Limite de Inatividade do Jogador',
						'rate_limit_label' => 'Limite de Taxa',
						'prevent_proxy_connections_label' => 'Evitar Conexões Proxy',
						'use_native_transport_label' => 'Usar Transporte Nativo',
						'hide_online_players_label' => 'Ocultar Jogadores Online',
						'entity_broadcast_range_percentage_label' => 'Percentual de Alcance de Transmissão de Entidade',
						'max_chained_neighbor_updates_label' => 'Máximo de Atualizações de Vizinho Encadeado',
						'pause_when_empty_seconds_label' => 'Pausar Quando Vazio (segundos)',
						'status_heartbeat_interval_label' => 'Intervalo de Batida do Coração de Status',
						'bug_report_link_label' => 'Link de Relatório de Bug',
						'require_resource_pack_label' => 'Exigir Pacote de Recursos',
						'resource_pack_label' => 'URL do Pacote de Recursos',
						'resource_pack_id_label' => 'ID do Pacote de Recursos',
						'resource_pack_prompt_label' => 'Prompt do Pacote de Recursos',
						'resource_pack_sha1_label' => 'SHA1 do Pacote de Recursos',
						'text_filtering_config_label' => 'Configuração de Filtro de Texto',
						'text_filtering_version_label' => 'Versão de Filtro de Texto',

						// Advanced Section
						'section_advanced' => 'Avançado',
						'enable_jmx_monitoring_label' => 'Ativar Monitoramento JMX',
						'enable_code_of_conduct_label' => 'Ativar Código de Conduta',
						'initial_enabled_packs_label' => 'Pacotes Inicialmente Ativados',
						'initial_disabled_packs_label' => 'Pacotes Inicialmente Desativados',
						'management_server_host_label' => 'Host do Servidor de Gerenciamento',
						'management_server_port_label' => 'Porta do Servidor de Gerenciamento',
						'management_server_enabled_label' => 'Servidor de Gerenciamento Ativado',
						'management_server_tls_enabled_label' => 'TLS do Servidor de Gerenciamento Ativado',
						'management_server_allowed_origins_label' => 'Origens Permitidas do Servidor de Gerenciamento',
						'management_server_tls_keystore_label' => 'Keystore TLS do Servidor de Gerenciamento',
						'management_server_tls_keystore_password_label' => 'Senha do Keystore TLS do Servidor de Gerenciamento',
						'management_server_secret_label' => 'Segredo do Servidor de Gerenciamento',
            ],
            'ru' => [
						// Basic Section
						'section_basic' => 'Основное',
						'motd_label' => 'Сообщение Сервера (MOTD)',
						'server_name_label' => 'Имя Сервера',
						'level_name_label' => 'Имя Мира',
						'server_ip_label' => 'IP Сервера',
						'server_port_label' => 'Порт Сервера',
						'port_label' => 'Порт',
						'max_players_label' => 'Макс. Игроков',
						'online_mode_label' => 'Режим Онлайн',

						// Gameplay Section
						'section_gameplay' => 'Геймплей',
						'gamemode_label' => 'Режим Игры',
						'gamemode_survival' => 'Выживание',
						'gamemode_creative' => 'Творчество',
						'gamemode_adventure' => 'Приключение',
						'gamemode_spectator' => 'Наблюдатель',
						'difficulty_label' => 'Сложность',
						'difficulty_peaceful' => 'Мирный',
						'difficulty_easy' => 'Легкий',
						'difficulty_normal' => 'Нормальный',
						'difficulty_hard' => 'Сложный',
						'pvp_label' => 'PvP',
						'force_gamemode_label' => 'Принудить Режим Игры',
						'hardcore_label' => 'Хардкор',

						// Spawning Section
						'section_spawning' => 'Спаун',
						'spawn_animals_label' => 'Спаун Животных',
						'spawn_monsters_label' => 'Спаун Монстров',
						'spawn_npcs_label' => 'Спаун NPC',
						'spawn_protection_label' => 'Защита Спауна',

						// World Section
						'section_world' => 'Мир',
						'level_seed_label' => 'Сид Мира',
						'level_type_label' => 'Тип Мира',
						'level_type_default' => 'По Умолчанию',
						'level_type_flat' => 'Плоский',
						'level_type_large_biomes' => 'Большие Биомы',
						'generator_settings_label' => 'Настройки Генератора',
						'generate_structures_label' => 'Генерировать Структуры',
						'max_world_size_label' => 'Макс. Размер Мира',

						// Performance Section
						'section_performance' => 'Производительность',
						'view_distance_label' => 'Расстояние Видимости',
						'simulation_distance_label' => 'Расстояние Симуляции',
						'network_compression_threshold_label' => 'Порог Сжатия Сети',
						'max_tick_time_label' => 'Макс. Время Тика',
						'sync_chunk_writes_label' => 'Синхронизировать Запись Чанков',
						'region_file_compression_label' => 'Сжатие Файла Региона',
						'compression_deflate' => 'Deflate',
						'compression_none' => 'Нет',

						// Security Section
						'section_security' => 'Безопасность',
						'enable_command_block_label' => 'Включить Блоки Команд',
						'op_permission_level_label' => 'Уровень Прав OP',
						'function_permission_level_label' => 'Уровень Прав Функции',
						'enforce_whitelist_label' => 'Применить Белый Список',
						'white_list_label' => 'Белый Список',
						'broadcast_console_to_ops_label' => 'Транслировать Консоль OP',
						'broadcast_rcon_to_ops_label' => 'Транслировать RCON OP',
						'enforce_secure_profile_label' => 'Применить Безопасный Профиль',

						// Query Section
						'section_query' => 'Запрос',
						'enable_query_label' => 'Включить Запрос',
						'query_port_label' => 'Порт Запроса',

						// RCON Section
						'section_rcon' => 'RCON',
						'enable_rcon_label' => 'Включить RCON',
						'rcon_port_label' => 'Порт RCON',
						'rcon_password_label' => 'Пароль RCON',

						// Other Section
						'section_other' => 'Прочие',
						'accepts_transfers_label' => 'Принимает Передачи',
						'allow_flight_label' => 'Разрешить Полет',
						'debug_label' => 'Отладка',
						'enable_status_label' => 'Включить Статус',
						'log_ips_label' => 'Логировать IP',
						'player_idle_timeout_label' => 'Таймаут Неактивности Игрока',
						'rate_limit_label' => 'Ограничение Скорости',
						'prevent_proxy_connections_label' => 'Предотвратить Подключения Прокси',
						'use_native_transport_label' => 'Использовать Нативный Транспорт',
						'hide_online_players_label' => 'Скрыть Игроков Онлайн',
						'entity_broadcast_range_percentage_label' => 'Процент Диапазона Трансляции Сущности',
						'max_chained_neighbor_updates_label' => 'Макс. Цепные Обновления Соседей',
						'pause_when_empty_seconds_label' => 'Пауза Когда Пусто (сек)',
						'status_heartbeat_interval_label' => 'Интервал Сердцебиения Статуса',
						'bug_report_link_label' => 'Ссылка на Отчет об Ошибке',
						'require_resource_pack_label' => 'Требовать Пак Ресурсов',
						'resource_pack_label' => 'URL Пака Ресурсов',
						'resource_pack_id_label' => 'ID Пака Ресурсов',
						'resource_pack_prompt_label' => 'Приглашение Пака Ресурсов',
						'resource_pack_sha1_label' => 'SHA1 Пака Ресурсов',
						'text_filtering_config_label' => 'Конфиг Фильтрации Текста',
						'text_filtering_version_label' => 'Версия Фильтрации Текста',

						// Advanced Section
						'section_advanced' => 'Дополнительно',
						'enable_jmx_monitoring_label' => 'Включить Мониторинг JMX',
						'enable_code_of_conduct_label' => 'Включить Кодекс Поведения',
						'initial_enabled_packs_label' => 'Изначально Включенные Паки',
						'initial_disabled_packs_label' => 'Изначально Отключенные Паки',
						'management_server_host_label' => 'Хост Сервера Управления',
						'management_server_port_label' => 'Порт Сервера Управления',
						'management_server_enabled_label' => 'Сервер Управления Включен',
						'management_server_tls_enabled_label' => 'TLS Сервера Управления Включен',
						'management_server_allowed_origins_label' => 'Разрешенные Источники Сервера Управления',
						'management_server_tls_keystore_label' => 'Хранилище Ключей TLS Сервера Управления',
						'management_server_tls_keystore_password_label' => 'Пароль Хранилища Ключей TLS Сервера Управления',
						'management_server_secret_label' => 'Секрет Сервера Управления',
            ],
            'zh' => [
						// Basic Section
						'section_basic' => '基本',
						'motd_label' => '服务器消息 (MOTD)',
						'server_name_label' => '服务器名称',
						'level_name_label' => '世界名称',
						'server_ip_label' => '服务器IP',
						'server_port_label' => '服务器端口',
						'port_label' => '端口',
						'max_players_label' => '最大玩家数',
						'online_mode_label' => '在线模式',

						// Gameplay Section
						'section_gameplay' => '游戏玩法',
						'gamemode_label' => '游戏模式',
						'gamemode_survival' => '生存',
						'gamemode_creative' => '创意',
						'gamemode_adventure' => '冒险',
						'gamemode_spectator' => '旁观者',
						'difficulty_label' => '难度',
						'difficulty_peaceful' => '和平',
						'difficulty_easy' => '简单',
						'difficulty_normal' => '普通',
						'difficulty_hard' => '困难',
						'pvp_label' => 'PVP',
						'force_gamemode_label' => '强制游戏模式',
						'hardcore_label' => '极限',

						// Spawning Section
						'section_spawning' => '生成',
						'spawn_animals_label' => '动物生成',
						'spawn_monsters_label' => '怪物生成',
						'spawn_npcs_label' => '生成NPC',
						'spawn_protection_label' => '出生点保护',

						// World Section
						'section_world' => '世界',
						'level_seed_label' => '世界种子',
						'level_type_label' => '世界类型',
						'level_type_default' => '默认',
						'level_type_flat' => '平坦',
						'level_type_large_biomes' => '大生物群落',
						'generator_settings_label' => '生成器设置',
						'generate_structures_label' => '生成结构',
						'max_world_size_label' => '最大世界大小',

						// Performance Section
						'section_performance' => '性能',
						'view_distance_label' => '视距',
						'simulation_distance_label' => '模拟距离',
						'network_compression_threshold_label' => '网络压缩阈值',
						'max_tick_time_label' => '最大刻时间',
						'sync_chunk_writes_label' => '同步区块写入',
						'region_file_compression_label' => '区域文件压缩',
						'compression_deflate' => 'Deflate',
						'compression_none' => '无',

						// Security Section
						'section_security' => '安全',
						'enable_command_block_label' => '启用命令方块',
						'op_permission_level_label' => 'OP权限等级',
						'function_permission_level_label' => '函数权限等级',
						'enforce_whitelist_label' => '强制白名单',
						'white_list_label' => '白名单',
						'broadcast_console_to_ops_label' => '向OP广播控制台',
						'broadcast_rcon_to_ops_label' => '向OP广播RCON',
						'enforce_secure_profile_label' => '强制安全配置文件',

						// Query Section
						'section_query' => '查询',
						'enable_query_label' => '启用查询',
						'query_port_label' => '查询端口',

						// RCON Section
						'section_rcon' => '远程控制',
						'enable_rcon_label' => '启用远程控制',
						'rcon_port_label' => '远程控制端口',
						'rcon_password_label' => '远程控制密码',

						// Other Section
						'section_other' => '其他',
						'accepts_transfers_label' => '接受转移',
						'allow_flight_label' => '允许飞行',
						'debug_label' => '调试',
						'enable_status_label' => '启用状态',
						'log_ips_label' => '记录IP',
						'player_idle_timeout_label' => '玩家空闲超时',
						'rate_limit_label' => '速率限制',
						'prevent_proxy_connections_label' => '防止代理连接',
						'use_native_transport_label' => '使用本机传输',
						'hide_online_players_label' => '隐藏在线玩家',
						'entity_broadcast_range_percentage_label' => '实体广播范围百分比',
						'max_chained_neighbor_updates_label' => '最大链式邻居更新',
						'pause_when_empty_seconds_label' => '为空时暂停（秒）',
						'status_heartbeat_interval_label' => '状态心跳间隔',
						'bug_report_link_label' => '错误报告链接',
						'require_resource_pack_label' => '需要资源包',
						'resource_pack_label' => '资源包URL',
						'resource_pack_id_label' => '资源包ID',
						'resource_pack_prompt_label' => '资源包提示',
						'resource_pack_sha1_label' => '资源包SHA1',
						'text_filtering_config_label' => '文本过滤配置',
						'text_filtering_version_label' => '文本过滤版本',

						// Advanced Section
						'section_advanced' => '高级',
						'enable_jmx_monitoring_label' => '启用JMX监控',
						'enable_code_of_conduct_label' => '启用行为准则',
						'initial_enabled_packs_label' => '初始启用的包',
						'initial_disabled_packs_label' => '初始禁用的包',
						'management_server_host_label' => '管理服务器主机',
						'management_server_port_label' => '管理服务器端口',
						'management_server_enabled_label' => '管理服务器已启用',
						'management_server_tls_enabled_label' => '管理服务器TLS已启用',
						'management_server_allowed_origins_label' => '管理服务器允许的来源',
						'management_server_tls_keystore_label' => '管理服务器TLS密钥库',
						'management_server_tls_keystore_password_label' => '管理服务器TLS密钥库密码',
						'management_server_secret_label' => '管理服务器密钥',
            ],
        ];
    }

    private function seedProfileTranslations(?int $categoryId): void
    {
        if (!$categoryId) {
            return;
        }

        foreach ($this->minecraftTranslations() as $locale => $translations) {
            foreach ($translations as $key => $value) {
                if (!is_string($key) || !is_string($value)) {
                    continue;
                }

                ServerToolProfileTranslation::updateOrCreate(
                    [
                        'translation_category_id' => $categoryId,
                        'locale' => $locale,
						'key' => 'servertools::minecraft.' . $key,
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }
        }
    }

    private function getOrCreateCategory(string $name): ?ServerToolTranslationCategory
    {
        $slug = Str::slug($name);

        return ServerToolTranslationCategory::updateOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        );
    }
}
