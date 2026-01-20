<?php

return [
    'server_tools_group' => 'Ferramentas do Servidor',
    'actions' => [
        'create_profile' => 'Novo perfil de configuração do servidor',
        'create_translation_category' => 'Nova categoria de traduções',
        'create_translation' => 'Nova tradução',
        'export_profiles' => 'Exportar perfis',
        'import_profiles' => 'Importar perfis',
        'export_translations' => 'Exportar traduções',
        'import_translations' => 'Importar traduções',
        'edit' => 'Editar',
        'delete' => 'Excluir',
        'save_changes' => 'Salvar alterações',
        'cancel' => 'Cancelar',
    ],
    'pages' => [
        'create_profile' => 'Novo perfil de configuração do servidor',
        'edit_profile' => 'Editar perfil de configuração do servidor',
        'create_translation_category' => 'Nova categoria de traduções',
        'edit_translation_category' => 'Editar categoria de traduções',
    ],
    // Admin Resources
    // Server Tool Configuration (legacy)
    'config' => [
        'label' => 'Configuração das Ferramentas do Servidor',
        'plural' => 'Configurações das Ferramentas do Servidor',
        'label_short' => 'Configuração',
        'form' => [
            'section_basic' => 'Informações Básicas',
            'section_basic_desc' => 'Configure as configurações básicas para essa configuração de ferramentas do servidor',
            'section_profile' => 'Informações do Perfil',
            'section_profile_desc' => 'Informações sobre o perfil de configuração',
            'name' => 'Nome da Configuração',
            'slug' => 'Slug',
            'slug_help' => 'Usado como identificador e nome de arquivo de perfil',
            'description' => 'Descrição',
            'is_active' => 'Ativo',
            'is_active_help' => 'Configurações inativas não estarão disponíveis para servidores',
        ],
        'table' => [
            'name' => 'Nome',
            'slug' => 'Slug',
            'description' => 'Descrição',
            'is_active' => 'Ativo',
            'egg_mappings' => 'Mapeamentos de Ovo',
            'created' => 'Criado',
        ],
    ],
    
    // Configuration Profiles (DB)
    'profiles' => [
        'label' => 'Perfil de Configuração do Servidor',
        'plural' => 'Perfis de Configuração do Servidor',
        
        'form' => [
            'section_basic' => 'Informações Básicas',
            'section_basic_desc' => 'Informações básicas sobre o perfil de configuração do servidor',
            'section_config' => 'Configuração do Perfil',
            'section_config_desc' => 'Editar a estrutura do perfil de configuração do servidor',
            'section_translations' => 'Traduções do perfil',
            'section_translations_desc' => 'Adicione traduções personalizadas para este perfil (substitui o padrão).',
            
            'name' => 'Nome do Perfil',
            'profile_name' => 'Nome Técnico',
            'profile_name_help' => 'Gerado automaticamente a partir do nome do perfil',
            'egg_feature_status_select' => 'Selecione um egg para verificar o status do recurso.',
            'egg_feature_status_enabled' => 'O recurso "server-tools" está ativado para este egg.',
            'egg_feature_status_missing' => 'O recurso "server-tools" não está ativado para este egg; será adicionado ao salvar.',
            'description' => 'Descrição',
            'config' => 'Configuração (JSON)',
            'config_help' => 'Editar a estrutura do perfil (salvo como JSON na coluna `config`).',
            'config_add' => 'Adicionar configuração (JSON)',
            'translations' => 'Traduções',
            'translation_locale' => 'Idioma',
            'translation_key' => 'Chave',
            'translation_key_help' => 'Use chaves como minecraft.motd_label.',
            'translation_key_prefix_note' => 'O prefixo servertools:: é adicionado automaticamente.',
            'translation_value' => 'Valor',
        ],

        'builder' => [
            'type' => 'Tipo',
            'sections' => 'Seções',
            'sections_add' => 'Adicionar nova seção',
            'section_key' => 'Chave da seção',
            'fields' => 'Campos',
            'field_type' => 'Tipo de campo',
            'key' => 'Chave',
            'label' => 'Rótulo',
            'options' => 'Opções',
            'min' => 'Mín',
            'max' => 'Máx',
            'translation_key_prefix_note' => 'O prefixo servertools:: é adicionado automaticamente.',
        ],
        
        'table' => [
            'name' => 'Nome',
            'profile_name' => 'Nome Técnico',
            'description' => 'Descrição',
            'egg' => 'Egg',
            'profile_exists' => 'Perfil Existe',
            'server_tools_enabled' => 'Server-tools ativado',
            'created' => 'Criado',
        ],
        
    ],

    'translation_categories' => [
        'label' => 'Categoria de tradução',
        'plural' => 'Categorias de tradução',

        'form' => [
            'section_basic' => 'Informações básicas',
            'name' => 'Nome',
            'slug' => 'Slug',
            'category' => 'Categoria de tradução',
        ],

        'table' => [
            'name' => 'Nome',
            'slug' => 'Slug',
            'created' => 'Criado',
        ],
    ],

    'import_export' => [
        'file' => 'Arquivo de importação (JSON)',
    ],

    'notifications' => [
        'import_profiles_success' => 'Perfis importados com sucesso.',
        'import_profiles_failed' => 'Não foi possível importar os perfis.',
        'import_translations_success' => 'Traduções importadas com sucesso.',
        'import_translations_failed' => 'Não foi possível importar as traduções.',
        'import_invalid_json' => 'Formato de arquivo de importação inválido.',
    ],

    'translations' => [
        'table' => [
            'id' => 'ID',
            'updated' => 'Atualizado',
        ],
    ],
];

