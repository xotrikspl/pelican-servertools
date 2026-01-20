<?php

return [
    'server_tools_group' => 'Server Tools',
    'actions' => [
        'create_profile' => 'New Server Configuration Profile',
        'create_translation_category' => 'New Translation Category',
        'create_translation' => 'New Translation',
        'export_profiles' => 'Export profiles',
        'import_profiles' => 'Import profiles',
        'export_translations' => 'Export translations',
        'import_translations' => 'Import translations',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'save_changes' => 'Save changes',
        'cancel' => 'Cancel',
    ],
    'pages' => [
        'create_profile' => 'New Server Configuration Profile',
        'edit_profile' => 'Edit Server Configuration Profile',
        'create_translation_category' => 'New Translation Category',
        'edit_translation_category' => 'Edit Translation Category',
    ],
    // Admin Resources
    // Server Tool Configuration (legacy)
    'config' => [
        'label' => 'Server Tool Configuration',
        'plural' => 'Server Tool Configurations',
        'label_short' => 'Config',
        
        'form' => [
            'section_basic' => 'Basic Information',
            'section_basic_desc' => 'Configure the basic settings for this server tool configuration',
            'section_profile' => 'Profile Information',
            'section_profile_desc' => 'Information about the configuration profile',
            
            'name' => 'Configuration Name',
            'slug' => 'Slug',
            'slug_help' => 'Used as the identifier and profile filename',
            'description' => 'Description',
            'is_active' => 'Active',
            'is_active_help' => 'Inactive configurations will not be available for servers',
        ],
        
        'table' => [
            'name' => 'Name',
            'slug' => 'Slug',
            'description' => 'Description',
            'is_active' => 'Active',
            'egg_mappings' => 'Egg Mappings',
            'created' => 'Created',
        ],
    ],
    
    // Configuration Profiles (DB)
    'profiles' => [
        'label' => 'Server Configuration Profile',
        'plural' => 'Server Configuration Profiles',
        
        'form' => [
            'section_basic' => 'Basic Information',
            'section_basic_desc' => 'Basic information about the server configuration profile',
            'section_config' => 'Profile Configuration',
            'section_config_desc' => 'Edit the server configuration profile structure',
            'section_translations' => 'Profile Translations',
            'section_translations_desc' => 'Add custom translations for this profile (overrides defaults).',
            
            'name' => 'Profile Name',
            'profile_name' => 'Technical Name',
            'profile_name_help' => 'Auto-generated from Profile Name',
            'egg_feature_status_select' => 'Select an egg to check the feature status.',
            'egg_feature_status_enabled' => 'The "server-tools" feature is enabled for this egg.',
            'egg_feature_status_missing' => 'The "server-tools" feature is not enabled for this egg; it will be added on save.',
            'description' => 'Description',
            'config' => 'Configuration (JSON)',
            'config_help' => 'Edit the profile structure (saved as JSON in the `config` column).',
            'config_add' => 'Add configuration (JSON)',
            'translations' => 'Translations',
            'translation_locale' => 'Locale',
            'translation_key' => 'Key',
            'translation_key_help' => 'Use keys like minecraft.motd_label.',
            'translation_key_prefix_note' => 'The servertools:: prefix is added automatically.',
            'translation_value' => 'Value',
        ],

        'builder' => [
            'type' => 'Type',
            'sections' => 'Sections',
            'sections_add' => 'Add new section',
            'section_key' => 'Section key',
            'fields' => 'Fields',
            'field_type' => 'Field type',
            'key' => 'Key',
            'label' => 'Label',
            'options' => 'Options',
            'min' => 'Min',
            'max' => 'Max',
            'translation_key_prefix_note' => 'The servertools:: prefix is added automatically.',
        ],
        
        'table' => [
            'name' => 'Name',
            'profile_name' => 'Technical Name',
            'description' => 'Description',
            'egg' => 'Egg',
            'profile_exists' => 'Profile Exists',
            'server_tools_enabled' => 'Server-tools enabled',
            'created' => 'Created',
        ],
        
    ],

    'translation_categories' => [
        'label' => 'Translation Category',
        'plural' => 'Translation Categories',

        'form' => [
            'section_basic' => 'Basic Information',
            'name' => 'Name',
            'slug' => 'Slug',
            'category' => 'Translation Category',
        ],

        'table' => [
            'name' => 'Name',
            'slug' => 'Slug',
            'created' => 'Created',
        ],
    ],

    'import_export' => [
        'file' => 'Import file (JSON)',
    ],

    'notifications' => [
        'import_profiles_success' => 'Profiles imported successfully.',
        'import_profiles_failed' => 'Failed to import profiles.',
        'import_translations_success' => 'Translations imported successfully.',
        'import_translations_failed' => 'Failed to import translations.',
        'import_invalid_json' => 'Invalid import file format.',
    ],

    'translations' => [
        'table' => [
            'id' => 'ID',
            'updated' => 'Updated',
        ],
    ],
];
