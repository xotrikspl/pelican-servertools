<?php

return [
    'server_tools_group' => 'Serverhulpmiddelen',
    'actions' => [
        'create_profile' => 'Nieuw serverconfiguratieprofiel',
        'create_translation_category' => 'Nieuwe vertaalcategorie',
        'create_translation' => 'Nieuwe vertaling',
        'export_profiles' => 'Profielen exporteren',
        'import_profiles' => 'Profielen importeren',
        'export_translations' => 'Vertalingen exporteren',
        'import_translations' => 'Vertalingen importeren',
        'edit' => 'Bewerken',
        'delete' => 'Verwijderen',
        'save_changes' => 'Wijzigingen opslaan',
        'cancel' => 'Annuleren',
    ],
    'pages' => [
        'create_profile' => 'Nieuw serverconfiguratieprofiel',
        'edit_profile' => 'Serverconfiguratieprofiel bewerken',
        'create_translation_category' => 'Nieuwe vertaalcategorie',
        'edit_translation_category' => 'Vertaalcategorie bewerken',
    ],
    // Admin Resources
    // Server Tool Configuration (legacy)
    'config' => [
        'label' => 'Serverconfiguratie',
        'plural' => 'Serverconfiguraties',
        'label_short' => 'Configuratie',
        'form' => [
            'section_basic' => 'Basisinformatie',
            'section_basic_desc' => 'Configureer de basisinstellingen voor deze serverconfiguratie',
            'section_profile' => 'Profielinformatie',
            'section_profile_desc' => 'Informatie over het configuratieprofiel',
            'name' => 'Configuratienaam',
            'slug' => 'Slug',
            'slug_help' => 'Gebruikt als identificatie en profielbestandsnaam',
            'description' => 'Beschrijving',
            'is_active' => 'Actief',
            'is_active_help' => 'Inactieve configuraties zijn niet beschikbaar voor servers',
        ],
        'table' => [
            'name' => 'Naam',
            'slug' => 'Slug',
            'description' => 'Beschrijving',
            'is_active' => 'Actief',
            'egg_mappings' => 'Egg-koppelingen',
            'created' => 'Gemaakt',
        ],
    ],
    
    // Configuration Profiles (DB)
    'profiles' => [
        'label' => 'Server Configuratie Profiel',
        'plural' => 'Server Configuratie Profielen',
        
        'form' => [
            'section_basic' => 'Basisinformatie',
            'section_basic_desc' => 'Basisinformatie over het server configuratie profiel',
            'section_config' => 'Profielconfiguratie',
            'section_config_desc' => 'Bewerk de server configuratie profiel structuur',
            'section_translations' => 'Profielvertalingen',
            'section_translations_desc' => 'Voeg aangepaste vertalingen toe voor dit profiel (overschrijft standaard).',
            
            'name' => 'Profielnaam',
            'profile_name' => 'Technische Naam',
            'profile_name_help' => 'Wordt automatisch gegenereerd uit de profielnaam',
            'egg_feature_status_select' => 'Selecteer een egg om de functiestatus te controleren.',
            'egg_feature_status_enabled' => 'De functie "server-tools" is ingeschakeld voor dit egg.',
            'egg_feature_status_missing' => 'De functie "server-tools" is niet ingeschakeld voor dit egg; deze wordt toegevoegd bij opslaan.',
            'description' => 'Beschrijving',
            'config' => 'Configuratie (JSON)',
            'config_help' => 'Bewerk de profiel structuur (opgeslagen als JSON in de `config` kolom).',
            'config_add' => 'Configuratie (JSON) toevoegen',
            'translations' => 'Vertalingen',
            'translation_locale' => 'Taal',
            'translation_key' => 'Sleutel',
            'translation_key_help' => 'Gebruik sleutels zoals common.section_basic of minecraft.motd_label.',
            'translation_value' => 'Waarde',
        ],

        'builder' => [
            'type' => 'Type',
            'sections' => 'Secties',
            'sections_add' => 'Nieuwe sectie toevoegen',
            'section_key' => 'Sectiesleutel',
            'fields' => 'Velden',
            'field_type' => 'Veldtype',
            'key' => 'Sleutel',
            'label' => 'Label',
            'options' => 'Opties',
            'min' => 'Min',
            'max' => 'Max',
        ],
        
        'table' => [
            'name' => 'Naam',
            'profile_name' => 'Technische Naam',
            'description' => 'Beschrijving',
            'egg' => 'Egg',
            'profile_exists' => 'Profiel Bestaat',
            'server_tools_enabled' => 'Server-tools ingeschakeld',
            'created' => 'Aangemaakt',
        ],
        
    ],
    'translation_categories' => [
        'label' => 'Vertaalcategorie',
        'plural' => 'Vertaalcategorieën',

        'form' => [
            'section_basic' => 'Basisinformatie',
            'name' => 'Naam',
            'slug' => 'Slug',
            'category' => 'Vertaalcategorie',
        ],

        'table' => [
            'name' => 'Naam',
            'slug' => 'Slug',
            'created' => 'Aangemaakt',
        ],
    ],

    'import_export' => [
        'file' => 'Importbestand (JSON)',
    ],

    'notifications' => [
        'import_profiles_success' => 'Profielen succesvol geïmporteerd.',
        'import_profiles_failed' => 'Profielen konden niet worden geïmporteerd.',
        'import_translations_success' => 'Vertalingen succesvol geïmporteerd.',
        'import_translations_failed' => 'Vertalingen konden niet worden geïmporteerd.',
        'import_invalid_json' => 'Ongeldig importbestandformaat.',
    ],

    'translations' => [
        'table' => [
            'id' => 'ID',
            'updated' => 'Bijgewerkt',
        ],
    ],
];

