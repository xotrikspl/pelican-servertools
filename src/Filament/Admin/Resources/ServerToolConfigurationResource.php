<?php

namespace Xotriks\Servertools\Filament\Admin\Resources;

use Xotriks\Servertools\Models\ServerToolConfiguration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use App\Models\Egg;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema as DbSchema;
use UnitEnum;
use BackedEnum;
use Xotriks\Servertools\Services\ServerToolTranslationService;
use Xotriks\Servertools\Models\ServerToolTranslationCategory;

class ServerToolConfigurationResource extends Resource
{
    protected static ?string $model = ServerToolConfiguration::class;

    protected static BackedEnum|string|null $navigationIcon = 'tabler-layout-list';
    
    protected static ?int $navigationSort = 0;

    public static function canViewAny(): bool
    {
        return DbSchema::hasTable('server_tool_configurations');
    }

    public static function getNavigationLabel(): string
    {
        return self::t('admin.profiles.plural');
    }

    public static function getNavigationGroup(): string
    {
        return self::t('admin.server_tools_group');
    }

    public static function getLabel(): string
    {
        return self::t('admin.profiles.label');
    }

    public static function getPluralLabel(): string
    {
        return self::t('admin.profiles.plural');
    }

    protected static function t(string $key): string
    {
        return ServerToolTranslationService::translate($key);
    }

    protected static function translationKeySearchOptions(array $configBuilderState, string $search): array
    {
        $keys = self::translationKeysFromBuilder($configBuilderState);

        $search = trim(mb_strtolower($search));
        if ($search !== '') {
            $keys = array_values(array_filter($keys, function (string $key) use ($search) {
                return str_contains(mb_strtolower($key), $search);
            }));
        }

        $keys = array_slice($keys, 0, 50);

        return empty($keys) ? [] : array_combine($keys, $keys);
    }

    protected static function translationKeysFromBuilder(array $configBuilderState): array
    {
        $keys = [];

        foreach ($configBuilderState as $file) {
            if (!is_array($file)) {
                continue;
            }
            $sections = $file['sections'] ?? [];
            if (!is_array($sections)) {
                continue;
            }

            foreach ($sections as $section) {
                if (!is_array($section)) {
                    continue;
                }

                $sectionKey = $section['section_key'] ?? null;
                if (is_string($sectionKey) && str_contains($sectionKey, '.')) {
                    $keys[] = $sectionKey;
                }

                $fields = $section['fields'] ?? [];
                if (!is_array($fields)) {
                    continue;
                }

                foreach ($fields as $field) {
                    if (!is_array($field)) {
                        continue;
                    }

                    $label = $field['label'] ?? null;
                    if (is_string($label) && str_contains($label, '.')) {
                        $keys[] = $label;
                    }

                    $options = $field['options'] ?? null;
                    if (is_array($options)) {
                        foreach ($options as $optionKey => $optionLabel) {
                            if (is_string($optionKey) && str_contains($optionKey, '.')) {
                                $keys[] = $optionKey;
                            }
                            if (is_string($optionLabel) && str_contains($optionLabel, '.')) {
                                $keys[] = $optionLabel;
                            }
                        }
                    }
                }
            }
        }

        $keys = array_values(array_unique($keys));
        sort($keys);

        return $keys;
    }

    protected static function localeOptions(): array
    {
        $langPath = plugin_path('servertools', 'resources/lang');
        if (!is_dir($langPath)) {
            return ['en' => 'en'];
        }

        $locales = collect(File::directories($langPath))
            ->map(fn (string $dir) => basename($dir))
            ->sort()
            ->values()
            ->all();

        return array_combine($locales, $locales);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                Section::make(self::t('admin.profiles.form.section_basic'))
                    ->description(self::t('admin.profiles.form.section_basic_desc'))
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('name')
                            ->label(self::t('admin.profiles.form.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('profile_name', \Illuminate\Support\Str::slug($state));
                            }),

                        TextInput::make('profile_name')
                            ->label(self::t('admin.profiles.form.profile_name'))
                            ->required()
                            ->maxLength(255)
                            ->readOnly()
                            ->helperText(self::t('admin.profiles.form.profile_name_help')),

                        Textarea::make('description')
                            ->label(self::t('admin.profiles.form.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make(self::t('admin.profiles.table.egg'))
                    ->columnSpan(1)
                    ->schema([
                        \Filament\Forms\Components\Select::make('egg_id')
                            ->label(self::t('admin.profiles.table.egg'))
                            ->options(fn () => Egg::query()->orderBy('name')->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText(function ($get) {
                                $eggId = $get('egg_id');

                                if (!$eggId) {
                                    return self::t('admin.profiles.form.egg_feature_status_select');
                                }

                                $egg = Egg::find($eggId);
                                if (!$egg) {
                                    return self::t('admin.profiles.form.egg_feature_status_select');
                                }

                                $features = $egg->features ?? [];
                                if (is_string($features)) {
                                    $decoded = json_decode($features, true);
                                    $features = is_array($decoded) ? $decoded : [];
                                }
                                if (!is_array($features)) {
                                    $features = [];
                                }

                                return in_array('server-tools', $features, true)
                                    ? self::t('admin.profiles.form.egg_feature_status_enabled')
                                    : self::t('admin.profiles.form.egg_feature_status_missing');
                            }),

                        \Filament\Forms\Components\Select::make('translation_category_id')
                            ->label(self::t('admin.translation_categories.form.category'))
                            ->options(fn () => ServerToolTranslationCategory::query()->orderBy('name')->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),
            ]),

            Section::make(self::t('admin.profiles.form.section_config'))
                ->description(self::t('admin.profiles.form.section_config_desc'))
                ->collapsible()
                ->collapsed()
                ->columnSpanFull()
                ->schema([
                        Repeater::make('config_builder')
                            ->label(self::t('admin.profiles.form.config'))
                            ->helperText(self::t('admin.profiles.form.config_help'))
                            ->addActionLabel(self::t('admin.profiles.form.config_add'))
                            ->reorderable()
                            ->columns(2)
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['filename'] ?? null)
                            ->defaultItems(0)
                            ->schema([
                                TextInput::make('filename')
                                    ->label(self::t('common.select_file_label'))
                                    ->required()
                                    ->maxLength(255),

                                \Filament\Forms\Components\Select::make('type')
                                    ->label(self::t('admin.profiles.builder.type'))
                                    ->options([
                                        'yaml' => 'YAML',
                                        'json' => 'JSON',
                                        'ini' => 'INI',
                                        'cfg' => 'CFG',
                                        'txt' => 'TXT',
                                        'conf' => 'CONF',
                                    ])
                                    ->required(),

                                Repeater::make('sections')
                                    ->label(self::t('admin.profiles.builder.sections'))
                                        ->addActionLabel(self::t('admin.profiles.builder.sections_add'))
                                    ->collapsible()
                                    ->collapsed()
                                    ->reorderable()
                                    ->itemLabel(fn (array $state): ?string => $state['section_key'] ?? null)
                                    ->columnSpanFull()
                                    ->defaultItems(0)
                                    ->schema([
                                        TextInput::make('section_key')
                                            ->label(self::t('admin.profiles.builder.section_key'))
                                            ->required()
                                            ->maxLength(255),

                                        Repeater::make('fields')
                                            ->label(self::t('admin.profiles.builder.fields'))
                                            ->collapsible()
                                            ->collapsed()
                                            ->reorderable()
                                            ->itemLabel(fn (array $state): ?string => $state['key'] ?? null)
                                            ->columns(2)
                                            ->defaultItems(0)
                                            ->schema([
                                                \Filament\Forms\Components\Select::make('type')
                                                    ->label(self::t('admin.profiles.builder.field_type'))
                                                    ->options([
                                                        'text' => 'Text',
                                                        'textarea' => 'Textarea',
                                                        'number' => 'Number',
                                                        'toggle' => 'Toggle',
                                                        'select' => 'Select',
                                                    ])
                                                    ->required(),

                                                TextInput::make('key')
                                                    ->label(self::t('admin.profiles.builder.key'))
                                                    ->required()
                                                    ->maxLength(255),

                                                TextInput::make('label')
                                                    ->label(self::t('admin.profiles.builder.label'))
                                                    ->required()
                                                    ->maxLength(255),

                                                KeyValue::make('options')
                                                    ->label(self::t('admin.profiles.builder.options'))
                                                    ->columnSpanFull()
                                                    ->visible(fn ($get) => $get('type') === 'select'),

                                                TextInput::make('min')
                                                    ->label(self::t('admin.profiles.builder.min'))
                                                    ->numeric()
                                                    ->visible(fn ($get) => $get('type') === 'number'),

                                                TextInput::make('max')
                                                    ->label(self::t('admin.profiles.builder.max'))
                                                    ->numeric()
                                                    ->visible(fn ($get) => $get('type') === 'number'),
                                            ]),
                                    ]),
                            ]),
                ]),
        ]);
    }

    public static function mapConfigToFiles($config): array
    {
        if (!is_array($config)) {
            return [];
        }

        // If the state is already in builder format (list of files), return it as-is
        if (isset($config[0]) && is_array($config[0]) && array_key_exists('filename', $config[0])) {
            return $config;
        }

        $files = [];
        $configFiles = $config['files'] ?? [];

        if (!is_array($configFiles)) {
            return [];
        }

        foreach ($configFiles as $filename => $fileConfig) {
            if (!is_array($fileConfig)) {
                continue;
            }
            $sections = [];

            $sectionsRaw = $fileConfig['sections'] ?? [];
            if (!is_array($sectionsRaw)) {
                $sectionsRaw = [];
            }

            if (isset($sectionsRaw[0]) && is_array($sectionsRaw[0]) && array_key_exists('section_key', $sectionsRaw[0])) {
                foreach ($sectionsRaw as $section) {
                    if (!is_array($section)) {
                        continue;
                    }

                    $sectionKey = $section['section_key'] ?? null;
                    if (!is_string($sectionKey) || $sectionKey === '') {
                        continue;
                    }

                    $fields = $section['fields'] ?? [];
                    if (!is_array($fields)) {
                        $fields = [];
                    }

                    $mappedFields = [];
                    foreach ($fields as $field) {
                        if (!is_array($field)) {
                            continue;
                        }
                        $mappedFields[] = [
                            'type' => $field['type'] ?? null,
                            'key' => $field['key'] ?? null,
                            'label' => $field['label'] ?? null,
                            'options' => $field['options'] ?? null,
                            'min' => $field['min'] ?? null,
                            'max' => $field['max'] ?? null,
                        ];
                    }

                    $sections[] = [
                        'section_key' => $sectionKey,
                        'fields' => $mappedFields,
                    ];
                }
            } else {
                $sectionsOrder = $fileConfig['sections_order'] ?? null;
                if (is_array($sectionsOrder) && !empty($sectionsOrder)) {
                    foreach ($sectionsOrder as $sectionKey) {
                        if (!is_string($sectionKey) || $sectionKey === '') {
                            continue;
                        }
                        $fields = $sectionsRaw[$sectionKey] ?? null;
                        if (!is_array($fields)) {
                            continue;
                        }

                        $mappedFields = [];
                        foreach ($fields as $field) {
                            if (!is_array($field)) {
                                continue;
                            }
                            $mappedFields[] = [
                                'type' => $field['type'] ?? null,
                                'key' => $field['key'] ?? null,
                                'label' => $field['label'] ?? null,
                                'options' => $field['options'] ?? null,
                                'min' => $field['min'] ?? null,
                                'max' => $field['max'] ?? null,
                            ];
                        }

                        $sections[] = [
                            'section_key' => $sectionKey,
                            'fields' => $mappedFields,
                        ];
                    }

                    foreach ($sectionsRaw as $sectionKey => $fields) {
                        if (in_array($sectionKey, $sectionsOrder, true)) {
                            continue;
                        }
                        if (!is_array($fields)) {
                            continue;
                        }
                        $mappedFields = [];

                        foreach ($fields as $field) {
                            if (!is_array($field)) {
                                continue;
                            }
                            $mappedFields[] = [
                                'type' => $field['type'] ?? null,
                                'key' => $field['key'] ?? null,
                                'label' => $field['label'] ?? null,
                                'options' => $field['options'] ?? null,
                                'min' => $field['min'] ?? null,
                                'max' => $field['max'] ?? null,
                            ];
                        }

                        $sections[] = [
                            'section_key' => $sectionKey,
                            'fields' => $mappedFields,
                        ];
                    }
                } else {
                foreach ($sectionsRaw as $sectionKey => $fields) {
                    if (!is_array($fields)) {
                        continue;
                    }
                    $mappedFields = [];

                    foreach ($fields as $field) {
                        if (!is_array($field)) {
                            continue;
                        }
                        $mappedFields[] = [
                            'type' => $field['type'] ?? null,
                            'key' => $field['key'] ?? null,
                            'label' => $field['label'] ?? null,
                            'options' => $field['options'] ?? null,
                            'min' => $field['min'] ?? null,
                            'max' => $field['max'] ?? null,
                        ];
                    }

                    $sections[] = [
                        'section_key' => $sectionKey,
                        'fields' => $mappedFields,
                    ];
                }
                }
            }

            $files[] = [
                'filename' => $filename,
                'type' => $fileConfig['type'] ?? 'ini',
                'sections' => $sections,
            ];
        }

        return $files;
    }

    public static function buildConfigFromFiles($files): array
    {
        if (!is_array($files)) {
            return ['files' => []];
        }

        $config = ['files' => []];

        foreach ($files as $file) {
            if (!is_array($file)) {
                continue;
            }
            $filename = $file['filename'] ?? null;
            if (! $filename) {
                continue;
            }

            $sections = [];
            $sectionsRaw = $file['sections'] ?? [];
            if (!is_array($sectionsRaw)) {
                $sectionsRaw = [];
            }

            foreach ($sectionsRaw as $section) {
                if (!is_array($section)) {
                    continue;
                }
                $sectionKey = $section['section_key'] ?? null;
                if (! $sectionKey) {
                    continue;
                }

                $fields = [];
                $fieldsRaw = $section['fields'] ?? [];
                if (!is_array($fieldsRaw)) {
                    $fieldsRaw = [];
                }

                foreach ($fieldsRaw as $field) {
                    if (!is_array($field)) {
                        continue;
                    }
                    $fields[] = array_filter(
                        Arr::only($field, ['type', 'key', 'label', 'options', 'min', 'max']),
                        fn ($value) => !is_null($value) && $value !== ''
                    );
                }

                $sections[] = [
                    'section_key' => $sectionKey,
                    'fields' => $fields,
                ];
            }

            $config['files'][$filename] = [
                'type' => $file['type'] ?? 'ini',
                'sections' => $sections,
            ];
        }

        return $config;
    }

    public static function ensureEggHasServerToolsFeature(?int $eggId): void
    {
        if (!$eggId) {
            return;
        }

        $egg = Egg::find($eggId);
        if (!$egg) {
            return;
        }

        $features = $egg->features ?? [];
        if (is_string($features)) {
            $decoded = json_decode($features, true);
            $features = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($features)) {
            $features = [];
        }

        if (!in_array('server-tools', $features, true)) {
            $features[] = 'server-tools';
            $egg->features = array_values(array_unique($features));
            $egg->save();
        }
    }

    public static function table(Table $table): Table
    {
        
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(self::t('admin.profiles.table.name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('profile_name')
                    ->label(self::t('admin.profiles.table.profile_name'))
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('description')
                    ->label(self::t('admin.profiles.table.description'))
                    ->limit(60)
                    ->searchable(),

                TextColumn::make('egg.name')
                    ->label(self::t('admin.profiles.table.egg'))
                    ->sortable()
                    ->searchable()
                    ->placeholder('-'),

                IconColumn::make('profile_exists')
                    ->label(self::t('admin.profiles.table.profile_exists'))
                    ->getStateUsing(function ($record) {
                        return !empty($record->config);
                    })
                    ->boolean()
                    ->trueIcon('tabler-check')
                    ->falseIcon('tabler-x')
                    ->trueColor('success')
                    ->falseColor('danger'),

                IconColumn::make('server_tools_enabled')
                    ->label(self::t('admin.profiles.table.server_tools_enabled'))
                    ->boolean()
                    ->trueIcon('tabler-check')
                    ->falseIcon('tabler-x')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label(self::t('admin.profiles.table.created'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->label(self::t('admin.actions.edit')),
                DeleteAction::make()
                    ->label(self::t('admin.actions.delete'))
                    ->after(function (ServerToolConfiguration $record): void {
                        \Log::debug('[ServerTools] profile deleted', [
                            'profile_id' => $record->id,
                            'profile_name' => $record->profile_name,
                            'egg_id' => $record->egg_id,
                        ]);
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \Xotriks\Servertools\Filament\Admin\Resources\ServerToolConfigurationResource\Pages\ListServerToolConfigurations::route('/'),
            'create' => \Xotriks\Servertools\Filament\Admin\Resources\ServerToolConfigurationResource\Pages\CreateServerToolConfiguration::route('/create'),
            'edit' => \Xotriks\Servertools\Filament\Admin\Resources\ServerToolConfigurationResource\Pages\EditServerToolConfiguration::route('/{record}/edit'),
        ];
    }
}
