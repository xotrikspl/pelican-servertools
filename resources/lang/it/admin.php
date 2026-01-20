<?php

return [
    'server_tools_group' => 'Strumenti del Server',
    'actions' => [
        'create_profile' => 'Nuovo profilo di configurazione del server',
        'create_translation_category' => 'Nuova categoria di traduzioni',
        'create_translation' => 'Nuova traduzione',
        'export_profiles' => 'Esporta profili',
        'import_profiles' => 'Importa profili',
        'export_translations' => 'Esporta traduzioni',
        'import_translations' => 'Importa traduzioni',
        'edit' => 'Modifica',
        'delete' => 'Elimina',
        'save_changes' => 'Salva modifiche',
        'cancel' => 'Annulla',
    ],
    'pages' => [
        'create_profile' => 'Nuovo profilo di configurazione del server',
        'edit_profile' => 'Modifica profilo di configurazione del server',
        'create_translation_category' => 'Nuova categoria di traduzioni',
        'edit_translation_category' => 'Modifica categoria di traduzioni',
    ],
    // Admin Resources
    // Server Tool Configuration (legacy)
    'config' => [
        'label' => 'Configurazione Strumenti Server',
        'plural' => 'Configurazioni Strumenti Server',
        'label_short' => 'Configurazione',
        'form' => [
            'section_basic' => 'Informazioni di Base',
            'section_basic_desc' => 'Configura le impostazioni di base per questa configurazione strumenti server',
            'section_profile' => 'Informazioni Profilo',
            'section_profile_desc' => 'Informazioni sul profilo di configurazione',
            'name' => 'Nome Configurazione',
            'slug' => 'Slug',
            'slug_help' => 'Usato come identificatore e nome file profilo',
            'description' => 'Descrizione',
            'is_active' => 'Attivo',
            'is_active_help' => 'Le configurazioni inattive non saranno disponibili per i server',
        ],
        'table' => [
            'name' => 'Nome',
            'slug' => 'Slug',
            'description' => 'Descrizione',
            'is_active' => 'Attivo',
            'egg_mappings' => 'Mappature Egg',
            'created' => 'Creato',
        ],
    ],
    
    // Configuration Profiles (DB)
    'profiles' => [
        'label' => 'Profilo di Configurazione del Server',
        'plural' => 'Profili di Configurazione del Server',
        
        'form' => [
            'section_basic' => 'Informazioni di Base',
            'section_basic_desc' => 'Informazioni di base sul profilo di configurazione del server',
            'section_config' => 'Configurazione del Profilo',
            'section_config_desc' => 'Modifica la struttura del profilo di configurazione del server',
            'section_translations' => 'Traduzioni del profilo',
            'section_translations_desc' => 'Aggiungi traduzioni personalizzate per questo profilo (sovrascrive le predefinite).',
            
            'name' => 'Nome del Profilo',
            'profile_name' => 'Nome Tecnico',
            'profile_name_help' => 'Generato automaticamente dal nome del profilo',
            'egg_feature_status_select' => 'Seleziona un egg per verificare lo stato della funzionalità.',
            'egg_feature_status_enabled' => 'La funzionalità "server-tools" è abilitata per questo egg.',
            'egg_feature_status_missing' => 'La funzionalità "server-tools" non è abilitata per questo egg; verrà aggiunta al salvataggio.',
            'description' => 'Descrizione',
            'config' => 'Configurazione (JSON)',
            'config_help' => 'Modifica la struttura del profilo (salvato come JSON nella colonna `config`).',
            'config_add' => 'Aggiungi configurazione (JSON)',
            'translations' => 'Traduzioni',
            'translation_locale' => 'Lingua',
            'translation_key' => 'Chiave',
            'translation_key_help' => 'Usa chiavi come minecraft.motd_label.',
            'translation_key_prefix_note' => 'Il prefisso servertools:: viene aggiunto automaticamente.',
            'translation_value' => 'Valore',
        ],

        'builder' => [
            'type' => 'Tipo',
            'sections' => 'Sezioni',
            'sections_add' => 'Aggiungi nuova sezione',
            'section_key' => 'Chiave sezione',
            'fields' => 'Campi',
            'field_type' => 'Tipo campo',
            'key' => 'Chiave',
            'label' => 'Etichetta',
            'options' => 'Opzioni',
            'min' => 'Min',
            'max' => 'Max',
            'translation_key_prefix_note' => 'Il prefisso servertools:: viene aggiunto automaticamente.',
        ],

        'table' => [
            'name' => 'Nome',
            'profile_name' => 'Nome Tecnico',
            'description' => 'Descrizione',
            'egg' => 'Egg',
            'profile_exists' => 'Profilo Esiste',
            'server_tools_enabled' => 'Server-tools attivato',
            'created' => 'Creato',
        ],
        
    ],

    'translation_categories' => [
        'label' => 'Categoria di traduzione',
        'plural' => 'Categorie di traduzione',

        'form' => [
            'section_basic' => 'Informazioni di base',
            'name' => 'Nome',
            'slug' => 'Slug',
            'category' => 'Categoria di traduzione',
        ],

        'table' => [
            'name' => 'Nome',
            'slug' => 'Slug',
            'created' => 'Creato',
        ],
    ],

        'import_export' => [
            'file' => 'File di importazione (JSON)',
        ],

        'notifications' => [
            'import_profiles_success' => 'Profili importati correttamente.',
            'import_profiles_failed' => 'Impossibile importare i profili.',
            'import_translations_success' => 'Traduzioni importate correttamente.',
            'import_translations_failed' => 'Impossibile importare le traduzioni.',
            'import_invalid_json' => 'Formato file di importazione non valido.',
        ],

    'translations' => [
        'table' => [
            'id' => 'ID',
            'updated' => 'Aggiornato',
        ],
    ],
];