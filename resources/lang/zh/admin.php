<?php

return [
    'server_tools_group' => '服务器工具',
    'actions' => [
        'create_profile' => '新建服务器配置档案',
        'create_translation_category' => '新建翻译分类',
        'create_translation' => '新建翻译',
        'export_profiles' => '导出配置档案',
        'import_profiles' => '导入配置档案',
        'export_translations' => '导出翻译',
        'import_translations' => '导入翻译',
        'edit' => '编辑',
        'delete' => '删除',
        'save_changes' => '保存更改',
        'cancel' => '取消',
    ],
    'pages' => [
        'create_profile' => '新建服务器配置档案',
        'edit_profile' => '编辑服务器配置档案',
        'create_translation_category' => '新建翻译分类',
        'edit_translation_category' => '编辑翻译分类',
    ],
    // Admin Resources
    // Server Tool Configuration (legacy)
    'config' => [
        'label' => '服务器工具配置',
        'plural' => '服务器工具配置',
        'label_short' => '配置',
        'form' => [
            'section_basic' => '基本信息',
            'section_basic_desc' => '为此服务器工具配置配置基本设置',
            'section_profile' => '配置文件信息',
            'section_profile_desc' => '有关配置配置文件的信息',
            'name' => '配置名称',
            'slug' => '老鼠',
            'slug_help' => '用作标识符和配置文件名',
            'description' => '描述',
            'is_active' => '活跃',
            'is_active_help' => '服务器将无法使用不活跃的配置',
        ],
        'table' => [
            'name' => '名称',
            'slug' => '老鼠',
            'description' => '描述',
            'is_active' => '活跃',
            'egg_mappings' => '鸡蛋映射',
            'created' => '创建',
        ],
    ],
   
    // Configuration Profiles (DB)
    'profiles' => [
        'label' => '服务器配置配置文件',
        'plural' => '服务器配置配置文件',
        
        'form' => [
            'section_basic' => '基本信息',
            'section_basic_desc' => '服务器配置配置文件的基本信息',
            'section_config' => '配置文件配置',
            'section_config_desc' => '编辑服务器配置配置文件结构',
            'section_translations' => '配置文件翻译',
            'section_translations_desc' => '为此配置文件添加自定义翻译（覆盖默认值）。',
            
            'name' => '配置文件名称',
            'profile_name' => '技术名称',
            'profile_name_help' => '根据配置文件名称自动生成',
            'egg_feature_status_select' => '请选择一个 egg 以检查功能状态。',
            'egg_feature_status_enabled' => '此 egg 已启用“server-tools”功能。',
            'egg_feature_status_missing' => '此 egg 未启用“server-tools”功能；保存时将自动添加。',
            'description' => '描述',
            'config' => '配置 (JSON)',
            'config_help' => '编辑配置文件结构 (以 JSON 格式保存在 `config` 列中)。',
            'config_add' => '添加配置 (JSON)',
            'translations' => '翻译',
            'translation_locale' => '语言',
            'translation_key' => '键',
            'translation_key_help' => '使用如 minecraft.motd_label 的键。',
            'translation_key_prefix_note' => 'servertools:: 前缀会自动添加。',
            'translation_value' => '值',
        ],

        'builder' => [
            'type' => '类型',
            'sections' => '分区',
            'sections_add' => '添加新分区',
            'section_key' => '分区键',
            'fields' => '字段',
            'field_type' => '字段类型',
            'key' => '键',
            'label' => '标签',
            'options' => '选项',
            'min' => '最小',
            'max' => '最大',
            'translation_key_prefix_note' => 'servertools:: 前缀会自动添加。',
        ],
        
        'table' => [
            'name' => '名称',
            'profile_name' => '技术名称',
            'description' => '描述',
            'egg' => 'Egg',
            'profile_exists' => '配置文件存在',
            'server_tools_enabled' => 'Server-tools 已启用',
            'created' => '创建时间',
        ],
        
    ],

    'translation_categories' => [
        'label' => '翻译类别',
        'plural' => '翻译类别',

        'form' => [
            'section_basic' => '基本信息',
            'name' => '名称',
            'slug' => 'Slug',
            'category' => '翻译类别',
        ],

        'table' => [
            'name' => '名称',
            'slug' => 'Slug',
            'created' => '创建时间',
        ],
    ],

    'import_export' => [
        'file' => '导入文件 (JSON)',
    ],

    'notifications' => [
        'import_profiles_success' => '配置档案导入成功。',
        'import_profiles_failed' => '配置档案导入失败。',
        'import_translations_success' => '翻译导入成功。',
        'import_translations_failed' => '翻译导入失败。',
        'import_invalid_json' => '导入文件格式无效。',
    ],

    'translations' => [
        'table' => [
            'id' => 'ID',
            'updated' => '更新时间',
        ],
    ],
];