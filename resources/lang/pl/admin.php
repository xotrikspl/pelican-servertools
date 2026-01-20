<?php

return [
    'server_tools_group' => 'Narzędzia Serwera',
    'actions' => [
        'create_profile' => 'Nowy Profil Konfiguracji Serwera',
        'create_translation_category' => 'Nowa Kategoria tłumaczeń',
        'create_translation' => 'Nowe Tłumaczenie',
        'export_profiles' => 'Eksportuj profile',
        'import_profiles' => 'Importuj profile',
        'export_translations' => 'Eksportuj tłumaczenia',
        'import_translations' => 'Importuj tłumaczenia',
        'edit' => 'Edytuj',
        'delete' => 'Usuń',
        'save_changes' => 'Zapisz zmiany',
        'cancel' => 'Anuluj',
    ],
    'pages' => [
        'create_profile' => 'Nowy profil konfiguracji serwera',
        'edit_profile' => 'Edycja profilu konfiguracji serwera',
        'create_translation_category' => 'Nowa kategoria tłumaczeń',
        'edit_translation_category' => 'Edycja kategorii tłumaczeń',
    ],
    // Admin Resources
    // Server Tool Configuration (legacy)
    'config' => [
        'label' => 'Konfiguracja Narzędzia Serwera',
        'plural' => 'Konfiguracje Narzędzi Serwera',
        'label_short' => 'Konfiguracja',
        
        'form' => [
            'section_basic' => 'Informacje Podstawowe',
            'section_basic_desc' => 'Skonfiguruj podstawowe ustawienia dla tej konfiguracji narzędzia serwera',
            'section_profile' => 'Informacje o Profilu',
            'section_profile_desc' => 'Informacje o profilu konfiguracji',
            
            'name' => 'Nazwa Konfiguracji',
            'slug' => 'Slug',
            'slug_help' => 'Używany jako identyfikator i nazwa pliku profilu',
            'description' => 'Opis',
            'is_active' => 'Aktywna',
            'is_active_help' => 'Nieaktywne konfiguracje nie będą dostępne dla serwerów',
        ],
        
        'table' => [
            'name' => 'Nazwa',
            'slug' => 'Slug',
            'description' => 'Opis',
            'is_active' => 'Aktywna',
            'egg_mappings' => 'Mapowania Eggów',
            'created' => 'Utworzono',
        ],
    ],
    
    // Configuration Profiles (DB)
    'profiles' => [
        'label' => 'Profil Konfiguracji Serwera',
        'plural' => 'Profile Konfiguracji Serwerów',
        
        'form' => [
            'section_basic' => 'Informacje Podstawowe',
            'section_basic_desc' => 'Podstawowe informacje o profilu konfiguracji',
            'section_config' => 'Konfiguracja Profilu',
            'section_config_desc' => 'Edytuj strukturę profilu konfiguracji serwera',
            'section_translations' => 'Tłumaczenia profilu',
            'section_translations_desc' => 'Dodaj własne tłumaczenia dla profilu (nadpisują domyślne).',
            
            'name' => 'Nazwa Profilu',
            'profile_name' => 'Nazwa Techniczna',
            'profile_name_help' => 'Automatycznie generowana na podstawie nazwy profilu',
            'egg_feature_status_select' => 'Wybierz egg, aby sprawdzić status funkcji.',
            'egg_feature_status_enabled' => '"server-tools" jest włączone dla tego egga.',
            'egg_feature_status_missing' => '"server-tools" nie jest włączone dla tego egga — zostanie dodane po zapisie.',
            'description' => 'Opis',
            'config' => 'Konfiguracja (JSON)',
            'config_help' => 'Edytuj strukturę profilu (zapisana jako JSON w kolumnie `config`).',
            'config_add' => 'Dodaj konfigurację (JSON)',
            'translations' => 'Tłumaczenia',
            'translation_locale' => 'Język',
            'translation_key' => 'Klucz',
            'translation_key_help' => 'Użyj kluczy typu minecraft.motd_label.',
            'translation_key_prefix_note' => 'Prefiks servertools:: jest dodawany automatycznie.',
            'translation_value' => 'Wartość',
        ],

        'builder' => [
            'type' => 'Typ',
            'sections' => 'Sekcje',
            'sections_add' => 'Dodaj nową sekcję',
            'section_key' => 'Klucz sekcji',
            'fields' => 'Pola',
            'field_type' => 'Typ pola',
            'key' => 'Klucz',
            'label' => 'Etykieta',
            'options' => 'Opcje',
            'min' => 'Min',
            'max' => 'Max',
            'translation_key_prefix_note' => 'Prefiks servertools:: jest dodawany automatycznie.',
        ],
        
        'table' => [
            'name' => 'Nazwa',
            'profile_name' => 'Nazwa Techniczna',
            'description' => 'Opis',
            'egg' => 'Egg',
            'profile_exists' => 'Profil Istnieje',
            'server_tools_enabled' => 'Server-tools włączone',
            'created' => 'Utworzono',
        ],
    
    ],
    
    'translation_categories' => [
        'label' => 'Kategoria tłumaczeń',
        'plural' => 'Kategorie tłumaczeń',

        'form' => [
            'section_basic' => 'Informacje podstawowe',
            'name' => 'Nazwa',
            'slug' => 'Slug',
            'category' => 'Kategoria tłumaczeń',
        ],

        'table' => [
            'name' => 'Nazwa',
            'slug' => 'Slug',
            'created' => 'Utworzono',
        ],
    ],

    'import_export' => [
        'file' => 'Plik importu (JSON)',
    ],

    'notifications' => [
        'import_profiles_success' => 'Profile zaimportowane pomyślnie.',
        'import_profiles_failed' => 'Nie udało się zaimportować profili.',
        'import_translations_success' => 'Tłumaczenia zaimportowane pomyślnie.',
        'import_translations_failed' => 'Nie udało się zaimportować tłumaczeń.',
        'import_invalid_json' => 'Nieprawidłowy format pliku importu.',
    ],

    'translations' => [
        'table' => [
            'id' => 'ID',
            'updated' => 'Zaktualizowano',
        ],
    ],
];

