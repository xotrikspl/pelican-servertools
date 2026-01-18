<?php

return [
    'server_tools_group' => 'Инструменты Сервера',
    'actions' => [
        'create_profile' => 'Новый профиль конфигурации сервера',
        'create_translation_category' => 'Новая категория переводов',
        'create_translation' => 'Новый перевод',
        'export_profiles' => 'Экспорт профилей',
        'import_profiles' => 'Импорт профилей',
        'export_translations' => 'Экспорт переводов',
        'import_translations' => 'Импорт переводов',
        'edit' => 'Изменить',
        'delete' => 'Удалить',
        'save_changes' => 'Сохранить изменения',
        'cancel' => 'Отмена',
    ],
    'pages' => [
        'create_profile' => 'Новый профиль конфигурации сервера',
        'edit_profile' => 'Редактирование профиля конфигурации сервера',
        'create_translation_category' => 'Новая категория переводов',
        'edit_translation_category' => 'Редактирование категории переводов',
    ],
    // Admin Resources
    // Server Tool Configuration (legacy)
    'config' => [
        'label' => 'Конфигурация Инструментов Сервера',
        'plural' => 'Конфигурации Инструментов Сервера',
        'label_short' => 'Конфигурация',
        'form' => [
            'section_basic' => 'Основная Информация',
            'section_basic_desc' => 'Настройте основные параметры для этой конфигурации инструментов сервера',
            'section_profile' => 'Информация о Профиле',
            'section_profile_desc' => 'Информация о профиле конфигурации',
            'name' => 'Имя Конфигурации',
            'slug' => 'Slug',
            'slug_help' => 'Используется как идентификатор и имя файла профиля',
            'description' => 'Описание',
            'is_active' => 'Активно',
            'is_active_help' => 'Неактивные конфигурации не будут доступны для серверов',
        ],
        'table' => [
            'name' => 'Имя',
            'slug' => 'Slug',
            'description' => 'Описание',
            'is_active' => 'Активно',
            'egg_mappings' => 'Сопоставления Яиц',
            'created' => 'Создано',
        ],
    ],
    
    // Configuration Profiles (DB)
    'profiles' => [
        'label' => 'Профиль Конфигурации Сервера',
        'plural' => 'Профили Конфигурации Сервера',
        
        'form' => [
            'section_basic' => 'Основная Информация',
            'section_basic_desc' => 'Основная информация о профиле конфигурации сервера',
            'section_config' => 'Конфигурация Профиля',
            'section_config_desc' => 'Редактировать структуру профиля конфигурации сервера',
            'section_translations' => 'Переводы профиля',
            'section_translations_desc' => 'Добавьте пользовательские переводы для этого профиля (перекрывают значения по умолчанию).',
            
            'name' => 'Имя Профиля',
            'profile_name' => 'Техническое Имя',
            'profile_name_help' => 'Автоматически создаётся из названия профиля',
            'egg_feature_status_select' => 'Выберите egg, чтобы проверить статус функции.',
            'egg_feature_status_enabled' => 'Функция "server-tools" включена для этого egg.',
            'egg_feature_status_missing' => 'Функция "server-tools" не включена для этого egg; будет добавлена при сохранении.',
            'description' => 'Описание',
            'config' => 'Конфигурация (JSON)',
            'config_help' => 'Редактировать структуру профиля (сохранено как JSON в столбце `config`).',
            'config_add' => 'Добавить конфигурацию (JSON)',
            'translations' => 'Переводы',
            'translation_locale' => 'Язык',
            'translation_key' => 'Ключ',
            'translation_key_help' => 'Используйте ключи типа common.section_basic или minecraft.motd_label.',
            'translation_value' => 'Значение',
        ],

        'builder' => [
            'type' => 'Тип',
            'sections' => 'Разделы',
            'sections_add' => 'Добавить новый раздел',
            'section_key' => 'Ключ раздела',
            'fields' => 'Поля',
            'field_type' => 'Тип поля',
            'key' => 'Ключ',
            'label' => 'Метка',
            'options' => 'Опции',
            'min' => 'Мин',
            'max' => 'Макс',
        ],
        
        'table' => [
            'name' => 'Имя',
            'profile_name' => 'Техническое Имя',
            'description' => 'Описание',
            'egg' => 'Egg',
            'profile_exists' => 'Профиль Существует',
            'server_tools_enabled' => 'Server-tools включен',
            'created' => 'Создано',
        ],

    ],

    'translation_categories' => [
        'label' => 'Категория переводов',
        'plural' => 'Категории переводов',

        'form' => [
            'section_basic' => 'Основная информация',
            'name' => 'Название',
            'slug' => 'Slug',
            'category' => 'Категория переводов',
        ],

        'table' => [
            'name' => 'Название',
            'slug' => 'Slug',
            'created' => 'Создано',
        ],
    ],

        'import_export' => [
            'file' => 'Файл импорта (JSON)',
        ],

        'notifications' => [
            'import_profiles_success' => 'Профили успешно импортированы.',
            'import_profiles_failed' => 'Не удалось импортировать профили.',
            'import_translations_success' => 'Переводы успешно импортированы.',
            'import_translations_failed' => 'Не удалось импортировать переводы.',
            'import_invalid_json' => 'Неверный формат файла импорта.',
        ],

    'translations' => [
        'table' => [
            'id' => 'ID',
            'updated' => 'Обновлено',
        ],
    ],
];

