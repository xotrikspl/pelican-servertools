<?php

return [
    'server_tools_group' => 'サーバーツール',
    'actions' => [
        'create_profile' => '新しいサーバー設定プロファイル',
        'create_translation_category' => '新しい翻訳カテゴリー',
        'create_translation' => '新しい翻訳',
        'export_profiles' => 'プロファイルをエクスポート',
        'import_profiles' => 'プロファイルをインポート',
        'export_translations' => '翻訳をエクスポート',
        'import_translations' => '翻訳をインポート',
        'edit' => '編集',
        'delete' => '削除',
        'save_changes' => '変更を保存',
        'cancel' => 'キャンセル',
    ],
    'pages' => [
        'create_profile' => '新しいサーバー設定プロファイル',
        'edit_profile' => 'サーバー設定プロファイルを編集',
        'create_translation_category' => '新しい翻訳カテゴリー',
        'edit_translation_category' => '翻訳カテゴリーを編集',
    ],
    // Admin Resources
    // Server Tool Configuration (legacy)
    'config' => [
        'label' => 'サーバーツール設定',
        'plural' => 'サーバーツール設定',
        'label_short' => '設定',
        
        'form' => [
            'section_basic' => '基本情報',
            'section_basic_desc' => 'このサーバーツール設定の基本設定を構成します',
            'section_profile' => 'プロファイル情報',
            'section_profile_desc' => '設定プロファイルに関する情報',
            
            'name' => '設定名',
            'slug' => 'スラッグ',
            'slug_help' => '識別子およびプロファイルファイル名として使用されます',
            'description' => '説明',
            'is_active' => '有効',
            'is_active_help' => '無効な設定はサーバーで利用できません',
        ],
        
        'table' => [
            'name' => '名前',
            'slug' => 'スラッグ',
            'description' => '説明',
            'is_active' => '有効',
            'egg_mappings' => 'エッグマッピング',
            'created' => '作成',
        ],
    ],
    
    // Configuration Profiles (DB)
    'profiles' => [
        'label' => 'サーバー設定プロファイル',
        'plural' => 'サーバー設定プロファイル',
        
        'form' => [
            'section_basic' => '基本情報',
            'section_basic_desc' => 'サーバー設定プロファイルの基本情報',
            'section_config' => 'プロファイル設定',
            'section_config_desc' => 'サーバー設定プロファイルの構造を編集',
            'section_translations' => 'プロファイル翻訳',
            'section_translations_desc' => 'このプロファイル用のカスタム翻訳を追加します（既定を上書き）。',
            
            'name' => 'プロファイル名',
            'profile_name' => '技術名',
            'profile_name_help' => 'プロファイル名から自動生成されます',
            'egg_feature_status_select' => '機能の状態を確認するには egg を選択してください。',
            'egg_feature_status_enabled' => 'この egg では「server-tools」機能が有効です。',
            'egg_feature_status_missing' => 'この egg では「server-tools」機能が無効です。保存時に追加されます。',
            'description' => '説明',
            'config' => '設定 (JSON)',
            'config_help' => 'プロファイル構造を編集 (JSONとして `config` 列に保存)。',
            'config_add' => '設定 (JSON) を追加',
            'translations' => '翻訳',
            'translation_locale' => '言語',
            'translation_key' => 'キー',
            'translation_key_help' => 'minecraft.motd_label のようなキーを使用してください。',
            'translation_value' => '値',
            'translation_key_prefix_note' => 'servertools:: プレフィックスは自動的に追加されます。',
        ],

        'builder' => [
            'type' => 'タイプ',
            'sections' => 'セクション',
            'sections_add' => '新しいセクションを追加',
            'section_key' => 'セクションキー',
            'fields' => 'フィールド',
            'field_type' => 'フィールド種別',
            'key' => 'キー',
            'label' => 'ラベル',
            'options' => 'オプション',
            'min' => '最小',
            'max' => '最大',
            'translation_key_prefix_note' => 'servertools:: プレフィックスは自動的に追加されます。',
        ],
        
        'table' => [
            'name' => '名前',
            'profile_name' => '技術名',
            'description' => '説明',
            'egg' => 'Egg',
            'profile_exists' => 'プロファイル存在',
            'server_tools_enabled' => 'Server-tools 有効',
            'created' => '作成日',
        ],
        
    ],

    'translation_categories' => [
        'label' => '翻訳カテゴリ',
        'plural' => '翻訳カテゴリ',

        'form' => [
            'section_basic' => '基本情報',
            'name' => '名前',
            'slug' => 'スラッグ',
            'category' => '翻訳カテゴリ',
        ],

        'table' => [
            'name' => '名前',
            'slug' => 'スラッグ',
            'created' => '作成日',
        ],
    ],

    'import_export' => [
        'file' => 'インポートファイル (JSON)',
    ],

    'notifications' => [
        'import_profiles_success' => 'プロファイルのインポートに成功しました。',
        'import_profiles_failed' => 'プロファイルのインポートに失敗しました。',
        'import_translations_success' => '翻訳のインポートに成功しました。',
        'import_translations_failed' => '翻訳のインポートに失敗しました。',
        'import_invalid_json' => 'インポートファイルの形式が無効です。',
    ],

    'translations' => [
        'table' => [
            'id' => 'ID',
            'updated' => '更新日',
        ],
    ],
];

