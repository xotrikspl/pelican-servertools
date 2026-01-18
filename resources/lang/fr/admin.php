<?php

return [
    'server_tools_group' => 'Outils Serveur',
    'actions' => [
        'create_profile' => 'Nouveau profil de configuration du serveur',
        'create_translation_category' => 'Nouvelle catégorie de traductions',
        'create_translation' => 'Nouvelle traduction',
        'export_profiles' => 'Exporter les profils',
        'import_profiles' => 'Importer les profils',
        'export_translations' => 'Exporter les traductions',
        'import_translations' => 'Importer les traductions',
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
        'save_changes' => 'Enregistrer les modifications',
        'cancel' => 'Annuler',
    ],
    'pages' => [
        'create_profile' => 'Nouveau profil de configuration du serveur',
        'edit_profile' => 'Modifier le profil de configuration du serveur',
        'create_translation_category' => 'Nouvelle catégorie de traductions',
        'edit_translation_category' => 'Modifier la catégorie de traductions',
    ],
    // Admin Resources
    // Server Tool Configuration (legacy)
    'config' => [
        'label' => 'Configuration des Outils Serveur',
        'plural' => 'Configurations des Outils Serveur',
        'label_short' => 'Configuration',
        'form' => [
            'section_basic' => 'Informations de Base',
            'section_basic_desc' => 'Configurez les paramètres de base pour cette configuration d\'outils serveur',
            'section_profile' => 'Informations sur le Profil',
            'section_profile_desc' => 'Informations sur le profil de configuration',
            'name' => 'Nom de Configuration',
            'slug' => 'Slug',
            'slug_help' => 'Utilisé comme identifiant et nom de fichier de profil',
            'description' => 'Description',
            'is_active' => 'Actif',
            'is_active_help' => 'Les configurations inactives ne seront pas disponibles pour les serveurs',
        ],
        'table' => [
            'name' => 'Nom',
            'slug' => 'Slug',
            'description' => 'Description',
            'is_active' => 'Actif',
            'egg_mappings' => 'Mappages d\'Œufs',
            'created' => 'Créé',
        ],
    ],
    
    // Configuration Profiles (DB)
    'profiles' => [
        'label' => 'Profil de Configuration du Serveur',
        'plural' => 'Profils de Configuration du Serveur',
        
        'form' => [
            'section_basic' => 'Informations de Base',
            'section_basic_desc' => 'Informations de base sur le profil de configuration du serveur',
            'section_config' => 'Configuration du Profil',
            'section_config_desc' => 'Modifier la structure du profil de configuration du serveur',
            'section_translations' => 'Traductions du profil',
            'section_translations_desc' => 'Ajoutez des traductions personnalisées pour ce profil (écrase les valeurs par défaut).',
            
            'name' => 'Nom du Profil',
            'profile_name' => 'Nom Technique',
            'profile_name_help' => 'Généré automatiquement à partir du nom du profil',
            'egg_feature_status_select' => 'Sélectionnez un egg pour vérifier l’état de la fonctionnalité.',
            'egg_feature_status_enabled' => 'La fonctionnalité « server-tools » est activée pour cet egg.',
            'egg_feature_status_missing' => 'La fonctionnalité « server-tools » n’est pas activée pour cet egg ; elle sera ajoutée à l’enregistrement.',
            'description' => 'Description',
            'config' => 'Configuration (JSON)',
            'config_help' => 'Modifier la structure du profil (enregistré en JSON dans la colonne `config`).',
            'config_add' => 'Ajouter une configuration (JSON)',
            'translations' => 'Traductions',
            'translation_locale' => 'Langue',
            'translation_key' => 'Clé',
            'translation_key_help' => 'Utilisez des clés comme common.section_basic ou minecraft.motd_label.',
            'translation_value' => 'Valeur',
        ],
        'builder' => [
            'type' => 'Type',
            'sections' => 'Sections',
            'sections_add' => 'Ajouter une nouvelle section',
            'section_key' => 'Clé de section',
            'fields' => 'Champs',
            'field_type' => 'Type de champ',
            'key' => 'Clé',
            'label' => 'Étiquette',
            'options' => 'Options',
            'min' => 'Min',
            'max' => 'Max',
        ],
        
        'table' => [
            'name' => 'Nom',
            'profile_name' => 'Nom Technique',
            'description' => 'Description',
            'egg' => 'Egg',
            'profile_exists' => 'Profil Existe',
            'server_tools_enabled' => 'Server-tools activé',
            'created' => 'Créé',
        ],
        
    ],
    
    'translation_categories' => [
        'label' => 'Catégorie de traduction',
        'plural' => 'Catégories de traduction',

        'form' => [
            'section_basic' => 'Informations de base',
            'name' => 'Nom',
            'slug' => 'Slug',
            'category' => 'Catégorie de traduction',
        ],

        'table' => [
            'name' => 'Nom',
            'slug' => 'Slug',
            'created' => 'Créé',
        ],
    ],

    'import_export' => [
        'file' => "Fichier d'import (JSON)",
    ],

    'notifications' => [
        'import_profiles_success' => 'Profils importés avec succès.',
        'import_profiles_failed' => "Échec de l'import des profils.",
        'import_translations_success' => 'Traductions importées avec succès.',
        'import_translations_failed' => "Échec de l'import des traductions.",
        'import_invalid_json' => "Format de fichier d'import invalide.",
    ],

    'translations' => [
        'table' => [
            'id' => 'ID',
            'updated' => 'Mis à jour',
        ],
    ],
];

