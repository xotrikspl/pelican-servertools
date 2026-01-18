<?php

namespace Xotriks\Servertools\Filament\Server\Pages;

use App\Models\Server;
use App\Repositories\Daemon\DaemonFileRepository;
use Filament\Facades\Filament;
use App\Filament\Server\Pages\ServerFormPage;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Support\Arr;
use Xotriks\Servertools\Models\ServerToolConfiguration;
use Xotriks\Servertools\Models\ServerToolConfig;
use Xotriks\Servertools\Services\ServerToolAccessService;
use Xotriks\Servertools\Services\ServerToolTranslationService;

final class ServerToolsPage extends ServerFormPage
{
    protected static ?string $navigationLabel = null;
    protected static string|\BackedEnum|null $navigationIcon = 'tabler-cog';
    protected static ?int $navigationSort = 3;

    public ?Server $server = null;
    public ?array $profile = null;
    public ?int $profileId = null;
    public ?string $selectedFile = null;
    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return ServerToolTranslationService::translate('common.server_tools_label');
    }

    public static function canAccess(): bool
    {
        /** @var Server|null $server */
        $server = Filament::getTenant();
        if (! $server instanceof Server) {
            \Log::debug('[ServerTools] No server tenant');
            return false;
        }

        // Hybrid approach: the feature must be present on the egg (gatekeeper)
        $eggFeatures = $server->egg?->features ?? [];
        if (!in_array('server-tools', $eggFeatures)) {
            \Log::debug('[ServerTools] Missing feature. Features: ' . json_encode($eggFeatures) . ', Egg: ' . ($server->egg?->name ?? 'unknown'));
            return false;
        }

        // Check whether configurations are available for this egg
        $availableConfigs = ServerToolAccessService::getAvailableConfigs($server);
        if (empty($availableConfigs)) {
            \Log::debug('[ServerTools] No available configurations for egg: ' . ($server->egg?->name ?? 'unknown'));
            return false;
        }

        return true;
    }

    public function getHeading(): ?string
    {
        return ServerToolTranslationService::translate('common.server_tools_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(ServerToolTranslationService::translate('common.save_changes'))
                ->color('primary')
                ->icon('tabler-device-floppy')
                ->action('save')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    public function mount(): void
    {
        $this->server = Filament::getTenant();
        
        if (!$this->server instanceof Server) {
            return;
        }

        $mapping = ServerToolConfiguration::where('egg_id', $this->server->egg_id)->first();
        $this->profileId = $mapping?->id;
        $this->profile = $mapping?->config;
        
        if (!$this->profile) {
            return;
        }

        // Load the first file by default
        $this->selectedFile = array_key_first($this->profile['files']);

        // Initialize Filament first
        parent::mount();

        // Then load data and populate the form
        $this->loadFileData($this->selectedFile);

        \Log::debug('[ServerTools] mount: finished, data count=' . count($this->data ?? []));
    }

    #[\Livewire\Attributes\On('updatedSelectedFile')]
    public function updatedSelectedFile(): void
    {
        if ($this->selectedFile) {
            $this->loadFileData($this->selectedFile);
            // Reset the form so Filament reloads the data
            $this->resetFormExcept('selectedFile');
            $this->fillFormFromData();
        }
    }

    private function resetFormExcept(string $except = ''): void
    {
        // Tell Filament to rebuild the form
        $this->cacheForms = [];
    }

    private function loadFileData(string $filename): void
    {
        if (!$this->server instanceof Server) {
            \Log::debug('[ServerTools] loadFileData: no server instance');
            return;
        }

        if (!isset($this->profile['files'][$filename])) {
            \Log::debug('[ServerTools] loadFileData: file not in profile');
            return;
        }

        try {
            $repo = app(DaemonFileRepository::class)->setServer($this->server);
            $content = $repo->getContent($filename);
            
            $fileConfig = $this->profile['files'][$filename];
            $parser = $this->getParser($fileConfig['type']);
            $data = $parser::parseContent($content);

            $flatData = [];
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    // If this is an associative array (section), prefix the keys
                    if (array_keys($value) !== range(0, count($value) - 1)) {
                        foreach ($value as $subKey => $subValue) {
                            $subValue = $this->normalizeValue($subValue);
                            // Keep the original keys with dashes
                            $flatData["{$key}.{$subKey}"] = $subValue;
                        }
                    } else {
                        // Indexed array — keep as a whole
                        $flatData[$key] = $value;
                    }
                } else {
                    $value = $this->normalizeValue($value);
                    // Keep the original keys
                    $flatData[$key] = $value;
                }
            }

            // Convert boolean-like strings for toggles
            foreach ($this->getSectionsList($fileConfig) as $section) {
                $fields = $section['fields'] ?? [];
                foreach ($fields as $field) {
                    // Use the original key
                    $key = $field['key'];
                    if ($field['type'] === 'toggle' && isset($flatData[$key])) {
                        $value = $flatData[$key];
                        $flatData[$key] = in_array($value, [true, '1', 1, 'true'], true);
                    }
                }
            }

            // Set data directly for Livewire binding
            // getFormStatePath() returns 'data', so Livewire reads from $this->data
            $flatData['selectedFile'] = $filename;
            $this->data = $flatData;

            // If the form already exists, fill it immediately
            $this->fillFormFromData();

            \Log::debug('[ServerTools] loadFileData: data set to ' . count($this->data) . ' keys');
        } catch (\Throwable $e) {
            \Log::error('[ServerTools] File load failed: ' . $filename . ' - ' . $e->getMessage());
            Notification::make()
                ->danger()
                ->title(ServerToolTranslationService::translate('common.notification_error_title'))
                ->body($e->getMessage())
                ->send();
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath($this->getFormStatePath())
            ->schema($this->getFormSchema());
    }

    private function fillFormFromData(): void
    {
        // Filament exposes the form instance on $this->form
        // We fill it when it is available and we have data
        if ($this->data !== null) {
            $this->form->fill($this->data);
            try {
                $state = $this->form->getState();
                $sample = $state['motd'] ?? 'null';
                \Log::debug('[ServerTools] fillFormFromData: filled form state keys=' . count($state) . ', sample motd=' . (is_scalar($sample) ? $sample : json_encode($sample)));
            } catch (\Throwable $e) {
                \Log::warning('[ServerTools] fillFormFromData: cannot read form state - ' . $e->getMessage());
            }
        }
    }

    protected function getFormSchema(): array
    {
        if (!$this->profile || empty($this->profile['files'])) {
            return [];
        }

        $fileOptions = [];
        foreach ($this->profile['files'] as $filename => $config) {
            $fileOptions[$filename] = $filename . ' (' . strtoupper($config['type']) . ')';
        }

        $sections = [
            Section::make(ServerToolTranslationService::translate('common.select_file_section'))
                ->schema([
                    Select::make('selectedFile')
                        ->label(ServerToolTranslationService::translate('common.select_file_label'))
                        ->options($fileOptions)
                        ->default($this->selectedFile)
                        ->live()
                        ->afterStateUpdated(function ($state) {
                            $this->selectedFile = $state;
                            $this->updatedSelectedFile();
                        }),
                ]),
        ];

        if (!$this->selectedFile) {
            return $sections;
        }

        $fileConfig = $this->profile['files'][$this->selectedFile];

        foreach ($this->getSectionsList($fileConfig) as $section) {
            $sectionKey = $section['section_key'] ?? null;
            if (!is_string($sectionKey) || $sectionKey === '') {
                continue;
            }

            $fields = $section['fields'] ?? [];
            $components = [];

            foreach ($fields as $field) {
                $component = $this->buildComponent($field);
                if ($component) {
                    $components[] = $component;
                }
            }

            if (!empty($components)) {
                $sections[] = Section::make($this->translateSection($sectionKey))
                    ->schema($components)
                    ->columns(2);
            }
        }

        return $sections;
    }

    private function buildComponent(array $field)
    {
        $key = $field['key'];
        
        $label = $this->getTranslation($field['label'] ?? '');

        return match ($field['type']) {
            'toggle' => Toggle::make($key)
                ->label($label)
                ->inline(false),
            
            'select' => Select::make($key)
                ->label($label)
                ->options($this->translateOptions($field['options'] ?? []))
                ->searchable(),
            
            'textarea' => Textarea::make($key)
                ->label($label)
                ->rows(4)
                ->columnSpanFull(),
            
            'number' => TextInput::make($key)
                ->label($label)
                ->numeric()
                ->minValue($field['min'] ?? null)
                ->maxValue($field['max'] ?? null),
            
            'text' => TextInput::make($key)
                ->label($label),
            
            default => null,
        };
    }

    /**
     * Translate option keys like 'minecraft.difficulty_peaceful' to actual translations
     */
    private function translateOptions(array $options): array
    {
        $translated = [];
        foreach ($options as $key => $value) {
            if (is_string($value) && strpos($value, '.') !== false) {
                $translated[$key] = $this->getTranslation($value);
            } else {
                $translated[$key] = $value;
            }
        }
        return $translated;
    }

    private function translateSection(string $sectionKey): string
    {
        $translated = $this->getTranslation($sectionKey);
        return $translated !== $sectionKey
            ? $translated
            : ucfirst(str_replace('_', ' ', $sectionKey));
    }

    private function getTranslation(string $key): string
    {
        return ServerToolTranslationService::translate($key, null, null, $this->profileId);
    }

    /**
     * Normalizes values loaded from files (e.g., removes escaped colons from server.properties)
     */
    private function normalizeValue(mixed $value): mixed
    {
        if (is_string($value)) {
            // server.properties stores "minecraft\:normal" — remove the backslash
            return str_replace('\\:', ':', $value);
        }

        return $value;
    }


    private function getParser(string $type): string
    {
        return match ($type) {
            'yaml' => \Xotriks\Servertools\Parsers\YamlParser::class,
            'json' => \Xotriks\Servertools\Parsers\JsonParser::class,
            'ini' => \Xotriks\Servertools\Parsers\IniParser::class,
            'cfg' => \Xotriks\Servertools\Parsers\CfgParser::class,
            'txt' => \Xotriks\Servertools\Parsers\TxtParser::class,
            'conf' => \Xotriks\Servertools\Parsers\ConfParser::class,
            default => throw new \InvalidArgumentException("Parser for type {$type} not supported.")
        };
    }

    public function save(): void
    {
        \Log::debug('[ServerTools] save: invoked');
        if (!$this->server instanceof Server) {
            Notification::make()
                ->danger()
                ->title(ServerToolTranslationService::translate('common.notification_error_invalid_server'))
                ->send();
            return;
        }

        if (!$this->selectedFile || !isset($this->profile['files'][$this->selectedFile])) {
            Notification::make()
                ->danger()
                ->title(ServerToolTranslationService::translate('common.notification_error_no_profile'))
                ->send();
            return;
        }

        try {
            // Fetch the latest form data (may be changed by the user)
            // Livewire updates $formData automatically when the value changes
            
            $fileConfig = $this->profile['files'][$this->selectedFile];
            $repo = app(DaemonFileRepository::class)->setServer($this->server);
            $content = $repo->getContent($this->selectedFile);
            $parser = $this->getParser($fileConfig['type']);

            // Parse the current file
            $data = $parser::parseContent($content);
            $originalData = $data;

            // Check structure — flat or hierarchical
            $isFlat = true;
            foreach ($data as $key => $value) {
                if (is_array($value) && !empty($value) && array_keys($value) !== range(0, count($value) - 1)) {
                    $isFlat = false;
                    break;
                }
            }

            // Get form data (use Filament instance, fallback to $this->data)
            try {
                $formData = $this->form->getState();
            } catch (\Throwable $e) {
                $formData = $this->data ?? [];
                \Log::warning('[ServerTools] save: fallback to data, reason=' . $e->getMessage());
            }

            // Update data
            if ($isFlat) {
                foreach ($formData as $key => $value) {
                    if (isset($data[$key])) {
                        // Convert booleans for toggles
                        if ($this->isToggleField($fileConfig, $key) && is_bool($value)) {
                            $value = $value ? 'true' : 'false';
                        }
                        $data[$key] = $value;
                    }
                }
            } else {
                foreach ($this->getSectionsList($fileConfig) as $section) {
                    $fields = $section['fields'] ?? [];
                    foreach ($fields as $field) {
                        $key = $field['key'];
                        if (isset($formData[$key])) {
                            $value = $formData[$key];
                            
                            // Convert booleans for toggles
                            if ($field['type'] === 'toggle' && is_bool($value)) {
                                $value = $value ? 'true' : 'false';
                            }

                            // Search in sections
                            $found = false;

                            // If the key contains a dot, treat it as `section.subKey`
                            if (strpos($key, '.') !== false) {
                                [$sectionName, $subKey] = explode('.', $key, 2);
                                if (isset($data[$sectionName]) && is_array($data[$sectionName])) {
                                    $data[$sectionName][$subKey] = $value;
                                    $found = true;
                                }
                            }

                            // If still not found, try any section
                            if (!$found) {
                                foreach ($data as $dataSection => &$sectionData) {
                                    if (is_array($sectionData) && isset($sectionData[$key])) {
                                        $sectionData[$key] = $value;
                                        $found = true;
                                        break;
                                    }
                                }
                            }

                            // If not found, set at top level
                            if (!$found && isset($data[$key])) {
                                $data[$key] = $value;
                            }
                        }
                    }
                }
            }

            // Serialize and save
            $newContent = $parser::writeContent($data);
            $repo->putContent($this->selectedFile, $newContent);
            $changedKeys = $this->getChangedKeys($originalData, $data);
            \Log::debug('[ServerTools] config saved', [
                'server_id' => $this->server?->id,
                'egg_id' => $this->server?->egg_id,
                'profile_id' => $this->profileId,
                'file' => $this->selectedFile,
                'changed_keys' => $changedKeys,
                'old_hash' => hash('sha256', $content),
                'new_hash' => hash('sha256', $newContent),
            ]);

            Notification::make()
                ->success()
                ->title(ServerToolTranslationService::translate('common.notification_success_title'))
                ->body(ServerToolTranslationService::translate('common.notification_success_message'))
                ->send();

            // Reload data
            $this->loadFileData($this->selectedFile);
        } catch (\Throwable $e) {
            \Log::error('[ServerTools] Save failed: ' . $e->getMessage());
            
            Notification::make()
                ->danger()
                ->title(ServerToolTranslationService::translate('common.notification_error_title'))
                ->body($e->getMessage())
                ->send();
        }
    }

    private function isToggleField(array $fileConfig, string $key): bool
    {
        foreach ($this->getSectionsList($fileConfig) as $section) {
            $fields = $section['fields'] ?? [];
            foreach ($fields as $field) {
                if ($field['key'] === $key && $field['type'] === 'toggle') {
                    return true;
                }
            }
        }
        return false;
    }

    private function getSectionsList(array $fileConfig): array
    {
        $sections = $fileConfig['sections'] ?? [];
        if (!is_array($sections)) {
            return [];
        }

        if (isset($sections[0]) && is_array($sections[0]) && array_key_exists('section_key', $sections[0])) {
            return $sections;
        }

        $order = $fileConfig['sections_order'] ?? [];
        $list = [];

        if (is_array($order) && !empty($order)) {
            foreach ($order as $sectionKey) {
                if (!is_string($sectionKey) || $sectionKey === '') {
                    continue;
                }
                $fields = $sections[$sectionKey] ?? null;
                if (!is_array($fields)) {
                    continue;
                }
                $list[] = [
                    'section_key' => $sectionKey,
                    'fields' => $fields,
                ];
            }
        }

        foreach ($sections as $sectionKey => $fields) {
            if (!is_string($sectionKey)) {
                continue;
            }
            if (is_array($order) && in_array($sectionKey, $order, true)) {
                continue;
            }
            if (!is_array($fields)) {
                continue;
            }
            $list[] = [
                'section_key' => $sectionKey,
                'fields' => $fields,
            ];
        }

        return $list;
    }

    private function getChangedKeys(array $before, array $after): array
    {
        $beforeFlat = $this->flattenConfig($before);
        $afterFlat = $this->flattenConfig($after);

        $allKeys = array_unique(array_merge(array_keys($beforeFlat), array_keys($afterFlat)));
        $changed = [];

        foreach ($allKeys as $key) {
            $beforeValue = $beforeFlat[$key] ?? null;
            $afterValue = $afterFlat[$key] ?? null;

            if ($beforeValue !== $afterValue) {
                $changed[] = $key;
            }
        }

        sort($changed);
        return $changed;
    }

    private function flattenConfig(array $data): array
    {
        $flat = Arr::dot($data);

        foreach ($flat as $key => $value) {
            if (is_array($value)) {
                $flat[$key] = json_encode($value);
            }
        }

        return $flat;
    }
}