<?php

return [
    'server_tools_group' => '서버 도구',
    'actions' => [
        'create_profile' => '새 서버 구성 프로필',
        'create_translation_category' => '새 번역 카테고리',
        'create_translation' => '새 번역',
        'export_profiles' => '프로필 내보내기',
        'import_profiles' => '프로필 가져오기',
        'export_translations' => '번역 내보내기',
        'import_translations' => '번역 가져오기',
        'edit' => '편집',
        'delete' => '삭제',
        'save_changes' => '변경 사항 저장',
        'cancel' => '취소',
    ],
    'pages' => [
        'create_profile' => '새 서버 구성 프로필',
        'edit_profile' => '서버 구성 프로필 편집',
        'create_translation_category' => '새 번역 카테고리',
        'edit_translation_category' => '번역 카테고리 편집',
    ],
    // Admin Resources
    // Server Tool Configuration (legacy)
    'config' => [
        'label' => '서버 도구 구성',
        'plural' => '서버 도구 구성',
        'label_short' => '구성',
        
        'form' => [
            'section_basic' => '기본 정보',
            'section_basic_desc' => '이 서버 도구 구성에 대한 기본 설정을 구성합니다',
            'section_profile' => '프로필 정보',
            'section_profile_desc' => '구성 프로필에 대한 정보',
            
            'name' => '구성 이름',
            'slug' => '슬러그',
            'slug_help' => '식별자 및 프로필 파일 이름으로 사용됩니다',
            'description' => '설명',
            'is_active' => '활성',
            'is_active_help' => '비활성 구성은 서버에서 사용할 수 없습니다',
        ],
        
        'table' => [
            'name' => '이름',
            'slug' => '슬러그',
            'description' => '설명',
            'is_active' => '활성',
            'egg_mappings' => 'Egg 매핑',
            'created' => '생성됨',
        ],
    ],

    // Configuration Profiles (DB)
    'profiles' => [
        'label' => '서버 구성 프로필',
        'plural' => '서버 구성 프로필',
        
        'form' => [
            'section_basic' => '기본 정보',
            'section_basic_desc' => '서버 구성 프로필의 기본 정보',
            'section_config' => '프로필 구성',
            'section_config_desc' => '서버 구성 프로필 구조 편집',
            'section_translations' => '프로필 번역',
            'section_translations_desc' => '이 프로필에 대한 사용자 정의 번역을 추가합니다 (기본값 덮어쓰기).',
            
            'name' => '프로필 이름',
            'profile_name' => '기술 이름',
            'profile_name_help' => '프로필 이름에서 자동으로 생성됩니다',
            'egg_feature_status_select' => '기능 상태를 확인하려면 egg를 선택하세요.',
            'egg_feature_status_enabled' => '이 egg에 "server-tools" 기능이 활성화되어 있습니다.',
            'egg_feature_status_missing' => '이 egg에 "server-tools" 기능이 비활성화되어 있습니다. 저장 시 추가됩니다.',
            'description' => '설명',
            'config' => '구성 (JSON)',
            'config_help' => '프로필 구조 편집 (`config` 열에 JSON으로 저장).',
            'config_add' => '구성 (JSON) 추가',
            'translations' => '번역',
            'translation_locale' => '언어',
            'translation_key' => '키',
            'translation_key_help' => 'minecraft.motd_label 같은 키를 사용하세요.',
            'translation_key_prefix_note' => 'servertools:: 접두사는 자동으로 추가됩니다.',
            'translation_value' => '값',
        ],

        'builder' => [
            'type' => '유형',
            'sections' => '섹션',
            'sections_add' => '새 섹션 추가',
            'section_key' => '섹션 키',
            'fields' => '필드',
            'field_type' => '필드 유형',
            'key' => '키',
            'label' => '라벨',
            'options' => '옵션',
            'min' => '최소',
            'max' => '최대',
            'translation_key_prefix_note' => 'servertools:: 접두사는 자동으로 추가됩니다.',
        ],
        
        'table' => [
            'name' => '이름',
            'profile_name' => '기술 이름',
            'description' => '설명',
            'egg' => 'Egg',
            'profile_exists' => '프로필 존재',
            'server_tools_enabled' => 'Server-tools 활성화',
            'created' => '생성됨',
        ],
        
    ],

    'translation_categories' => [
        'label' => '번역 카테고리',
        'plural' => '번역 카테고리',

        'form' => [
            'section_basic' => '기본 정보',
            'name' => '이름',
            'slug' => '슬러그',
            'category' => '번역 카테고리',
        ],

        'table' => [
            'name' => '이름',
            'slug' => '슬러그',
            'created' => '생성됨',
        ],
    ],

    'import_export' => [
        'file' => '가져오기 파일 (JSON)',
    ],

    'notifications' => [
        'import_profiles_success' => '프로필을 성공적으로 가져왔습니다.',
        'import_profiles_failed' => '프로필을 가져오지 못했습니다.',
        'import_translations_success' => '번역을 성공적으로 가져왔습니다.',
        'import_translations_failed' => '번역을 가져오지 못했습니다.',
        'import_invalid_json' => '가져오기 파일 형식이 올바르지 않습니다.',
    ],

    'translations' => [
        'table' => [
            'id' => 'ID',
            'updated' => '업데이트됨',
        ],
    ],
];

