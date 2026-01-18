<?php

return [
    'server_tools_group' => 'Herramientas del Servidor',
    'actions' => [
        'create_profile' => 'Nuevo perfil de configuración del servidor',
        'create_translation_category' => 'Nueva categoría de traducciones',
        'create_translation' => 'Nueva traducción',
        'export_profiles' => 'Exportar perfiles',
        'import_profiles' => 'Importar perfiles',
        'export_translations' => 'Exportar traducciones',
        'import_translations' => 'Importar traducciones',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'save_changes' => 'Guardar cambios',
        'cancel' => 'Cancelar',
    ],
    'pages' => [
        'create_profile' => 'Nuevo perfil de configuración del servidor',
        'edit_profile' => 'Editar perfil de configuración del servidor',
        'create_translation_category' => 'Nueva categoría de traducciones',
        'edit_translation_category' => 'Editar categoría de traducciones',
    ],
    // Admin Resources
    // Server Tool Configuration (legacy)
    'config' => [
        'label' => 'Configuración de Herramientas del Servidor',
        'plural' => 'Configuraciones de Herramientas del Servidor',
        'label_short' => 'Configuración',
        'form' => [
            'section_basic' => 'Información Básica',
            'section_basic_desc' => 'Configure los ajustes básicos para esta configuración de herramientas del servidor',
            'section_profile' => 'Información del Perfil',
            'section_profile_desc' => 'Información sobre el perfil de configuración',
            'name' => 'Nombre de Configuración',
            'slug' => 'Slug',
            'slug_help' => 'Se utiliza como identificador y nombre de archivo de perfil',
            'description' => 'Descripción',
            'is_active' => 'Activo',
            'is_active_help' => 'Las configuraciones inactivas no estarán disponibles para los servidores',
        ],
        'table' => [
            'name' => 'Nombre',
            'slug' => 'Slug',
            'description' => 'Descripción',
            'is_active' => 'Activo',
            'egg_mappings' => 'Asignaciones de Huevos',
            'created' => 'Creado',
        ],
    ],
    
    // Configuration Profiles (DB)
    'profiles' => [
        'label' => 'Perfil de Configuración del Servidor',
        'plural' => 'Perfiles de Configuración del Servidor',
        
        'form' => [
            'section_basic' => 'Información Básica',
            'section_basic_desc' => 'Información básica sobre el perfil de configuración del servidor',
            'section_config' => 'Configuración del Perfil',
            'section_config_desc' => 'Editar la estructura del perfil de configuración del servidor',
            'section_translations' => 'Traducciones del perfil',
            'section_translations_desc' => 'Agrega traducciones personalizadas para este perfil (sobrescribe las predeterminadas).',
            
            'name' => 'Nombre del Perfil',
            'profile_name' => 'Nombre Técnico',
            'profile_name_help' => 'Generado automáticamente a partir del nombre del perfil',
            'egg_feature_status_select' => 'Selecciona un egg para comprobar el estado de la función.',
            'egg_feature_status_enabled' => 'La función "server-tools" está habilitada para este egg.',
            'egg_feature_status_missing' => 'La función "server-tools" no está habilitada para este egg; se añadirá al guardar.',
            'description' => 'Descripción',
            'config' => 'Configuración (JSON)',
            'config_help' => 'Editar la estructura del perfil (guardado como JSON en la columna `config`).',
            'config_add' => 'Agregar configuración (JSON)',
            'translations' => 'Traducciones',
            'translation_locale' => 'Idioma',
            'translation_key' => 'Clave',
            'translation_key_help' => 'Usa claves como common.section_basic o minecraft.motd_label.',
            'translation_value' => 'Valor',
        ],

        'builder' => [
            'type' => 'Tipo',
            'sections' => 'Secciones',
            'sections_add' => 'Agregar nueva sección',
            'section_key' => 'Clave de sección',
            'fields' => 'Campos',
            'field_type' => 'Tipo de campo',
            'key' => 'Clave',
            'label' => 'Etiqueta',
            'options' => 'Opciones',
            'min' => 'Mín',
            'max' => 'Máx',
        ],
        
        'table' => [
            'name' => 'Nombre',
            'profile_name' => 'Nombre Técnico',
            'description' => 'Descripción',
            'egg' => 'Egg',
            'profile_exists' => 'Perfil Existe',
            'server_tools_enabled' => 'Server-tools activado',
            'created' => 'Creado',
        ],
        
    ],

    'translation_categories' => [
        'label' => 'Categoría de traducción',
        'plural' => 'Categorías de traducción',

        'form' => [
            'section_basic' => 'Información básica',
            'name' => 'Nombre',
            'slug' => 'Slug',
            'category' => 'Categoría de traducción',
        ],

        'table' => [
            'name' => 'Nombre',
            'slug' => 'Slug',
            'created' => 'Creado',
        ],
    ],

    'import_export' => [
        'file' => 'Archivo de importación (JSON)',
    ],

    'notifications' => [
        'import_profiles_success' => 'Perfiles importados correctamente.',
        'import_profiles_failed' => 'No se pudieron importar los perfiles.',
        'import_translations_success' => 'Traducciones importadas correctamente.',
        'import_translations_failed' => 'No se pudieron importar las traducciones.',
        'import_invalid_json' => 'Formato de archivo de importación inválido.',
    ],

    'translations' => [
        'table' => [
            'id' => 'ID',
            'updated' => 'Actualizado',
        ],
    ],
];

