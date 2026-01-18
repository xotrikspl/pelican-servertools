<?php

return [
    'server_tools_group' => 'Server-Tools',
    'actions' => [
        'create_profile' => 'Neues Server-Konfigurationsprofil',
        'create_translation_category' => 'Neue Übersetzungskategorie',
        'create_translation' => 'Neue Übersetzung',
        'export_profiles' => 'Profile exportieren',
        'import_profiles' => 'Profile importieren',
        'export_translations' => 'Übersetzungen exportieren',
        'import_translations' => 'Übersetzungen importieren',
        'edit' => 'Bearbeiten',
        'delete' => 'Löschen',
        'save_changes' => 'Änderungen speichern',
        'cancel' => 'Abbrechen',
    ],
    'pages' => [
        'create_profile' => 'Neues Server-Konfigurationsprofil',
        'edit_profile' => 'Server-Konfigurationsprofil bearbeiten',
        'create_translation_category' => 'Neue Übersetzungskategorie',
        'edit_translation_category' => 'Übersetzungskategorie bearbeiten',
    ],
    // Admin Resources
    // Server Tool Configuration (legacy)
    'config' => [
        'label' => 'Serverkonfiguration',
        'plural' => 'Serverkonfigurationen',
        'label_short' => 'Konfiguration',
        
        'form' => [
            'section_basic' => 'Grundlegende Informationen',
            'section_basic_desc' => 'Konfigurieren Sie die grundlegenden Einstellungen für diese Serverkonfiguration',
            'section_profile' => 'Profilinformationen',
            'section_profile_desc' => 'Informationen zum Konfigurationsprofil',
            
            'name' => 'Konfigurationsname',
            'slug' => 'Slug',
            'slug_help' => 'Wird als Bezeichner und Profildateiname verwendet',
            'description' => 'Beschreibung',
            'is_active' => 'Aktiv',
            'is_active_help' => 'Inaktive Konfigurationen sind für Server nicht verfügbar',
        ],
        
        'table' => [
            'name' => 'Name',
            'slug' => 'Slug',
            'description' => 'Beschreibung',
            'is_active' => 'Aktiv',
            'egg_mappings' => 'Egg-Zuordnungen',
            'created' => 'Erstellt',
        ],
    ],
    
    // Configuration Profiles (DB)
    'profiles' => [
        'label' => 'Server-Konfigurationsprofil',
        'plural' => 'Server-Konfigurationsprofile',
        
        'form' => [
            'section_basic' => 'Grundinformationen',
            'section_basic_desc' => 'Grundinformationen über das Server-Konfigurationsprofil',
            'section_config' => 'Profilkonfiguration',
            'section_config_desc' => 'Bearbeiten Sie die Struktur des Server-Konfigurationsprofils',
            'section_translations' => 'Profil-Übersetzungen',
            'section_translations_desc' => 'Eigene Übersetzungen für dieses Profil hinzufügen (überschreibt Standard).',
            
            'name' => 'Profilname',
            'profile_name' => 'Technischer Name',
            'profile_name_help' => 'Wird automatisch aus dem Profilnamen generiert',
            'egg_feature_status_select' => 'Wähle ein Egg aus, um den Feature-Status zu prüfen.',
            'egg_feature_status_enabled' => 'Das Feature „server-tools“ ist für dieses Egg aktiviert.',
            'egg_feature_status_missing' => 'Das Feature „server-tools“ ist für dieses Egg nicht aktiviert – es wird beim Speichern hinzugefügt.',
            'description' => 'Beschreibung',
            'config' => 'Konfiguration (JSON)',
            'config_help' => 'Bearbeiten Sie die Profilstruktur (gespeichert als JSON in der `config` Spalte).',
            'config_add' => 'Konfiguration (JSON) hinzufügen',
            'translations' => 'Übersetzungen',
            'translation_locale' => 'Sprache',
            'translation_key' => 'Schlüssel',
            'translation_key_help' => 'Schlüssel wie common.section_basic oder minecraft.motd_label.',
            'translation_value' => 'Wert',
        ],

        'builder' => [
            'type' => 'Typ',
            'sections' => 'Sektionen',
            'sections_add' => 'Neue Sektion hinzufügen',
            'section_key' => 'Schlüssel der Sektion',
            'fields' => 'Felder',
            'field_type' => 'Feldtyp',
            'key' => 'Schlüssel',
            'label' => 'Bezeichnung',
            'options' => 'Optionen',
            'min' => 'Min',
            'max' => 'Max',
        ],
        
        'table' => [
            'name' => 'Name',
            'profile_name' => 'Technischer Name',
            'description' => 'Beschreibung',
            'egg' => 'Egg',
            'profile_exists' => 'Profil Existiert',
            'server_tools_enabled' => 'Server-tools aktiviert',
            'created' => 'Erstellt',
        ],
        
    ],

    'translation_categories' => [
        'label' => 'Übersetzungskategorie',
        'plural' => 'Übersetzungskategorien',

        'form' => [
            'section_basic' => 'Grundinformationen',
            'name' => 'Name',
            'slug' => 'Slug',
            'category' => 'Übersetzungskategorie',
        ],

        'table' => [
            'name' => 'Name',
            'slug' => 'Slug',
            'created' => 'Erstellt',
        ],
    ],

    'import_export' => [
        'file' => 'Importdatei (JSON)',
    ],

    'notifications' => [
        'import_profiles_success' => 'Profile erfolgreich importiert.',
        'import_profiles_failed' => 'Profile konnten nicht importiert werden.',
        'import_translations_success' => 'Übersetzungen erfolgreich importiert.',
        'import_translations_failed' => 'Übersetzungen konnten nicht importiert werden.',
        'import_invalid_json' => 'Ungültiges Importdateiformat.',
    ],

    'translations' => [
        'table' => [
            'id' => 'ID',
            'updated' => 'Aktualisiert',
        ],
    ],

];

