<?php

namespace Xotriks\Servertools\Filament\Admin\Resources\ServerToolConfigurationResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Xotriks\Servertools\Services\ServerToolTranslationService;
use Xotriks\Servertools\Models\ServerToolProfileTranslation;

class TranslationsRelationManager extends RelationManager
{
    protected static string $relationship = 'translations';

    protected static ?string $title = null;

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return self::t(key: 'admin.profiles.form.translations');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema($this->getTranslationFormComponents());
    }

    public function table(Table $table): Table
    {
        $headerActions = [];
        $headerActions[] = \Filament\Actions\Action::make('create')
            ->icon('tabler-plus')
            ->form($this->getTranslationFormComponents())
            ->action(function (array $data) {
                if (isset($data['key']) && is_string($data['key'])) {
                    $data['key'] = $this->prefixKey($data['key']);
                }
                $translation = $this->getOwnerRecord()
                    ?->translations()
                    ->create($data);
                if ($translation) {
                    \Log::debug('[ServerTools] translation created', [
                        'translation_id' => $translation->id,
                        'profile_id' => $this->getOwnerRecord()?->id,
                        'locale' => $translation->locale,
                        'key' => $translation->key,
                    ]);
                }
            });

        return $table
            ->columns([
                TextColumn::make('locale')
                    ->label(self::t('admin.profiles.form.translation_locale'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('key')
                    ->label(self::t('admin.profiles.form.translation_key'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('value')
                    ->label(self::t('admin.profiles.form.translation_value'))
                    ->limit(80)
                    ->wrap()
                    ->searchable(),
            ])
            ->headerActions($headerActions)
            ->recordActions([
                \Filament\Actions\Action::make('edit')
                    ->label(self::t('admin.actions.edit'))
                    ->icon('tabler-edit')
                    ->form($this->getTranslationFormComponents())
                    ->mountUsing(function ($form, $record) {
                        $form->fill([
                            'locale' => $record->locale,
                            'key' => $this->stripPrefixKey($record->key),
                            'value' => $record->value,
                        ]);
                    })
                    ->action(function ($record, array $data) {
                        if (isset($data['key']) && is_string($data['key'])) {
                            $data['key'] = $this->prefixKey($data['key']);
                        }
                        $record->update($data);
                        \Log::debug('[ServerTools] translation updated', [
                            'translation_id' => $record->id,
                            'profile_id' => $this->getOwnerRecord()?->id,
                            'locale' => $record->locale,
                            'key' => $record->key,
                        ]);
                    }),
                \Filament\Actions\Action::make('delete')
                    ->label(self::t('admin.actions.delete'))
                    ->icon('tabler-trash')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        \Log::debug('[ServerTools] translation deleted', [
                            'translation_id' => $record->id,
                            'profile_id' => $this->getOwnerRecord()?->id,
                            'locale' => $record->locale,
                            'key' => $record->key,
                        ]);
                        $record->delete();
                    }),
            ])
            ->defaultSort('locale');
    }

    protected function getTranslationFormComponents(): array
    {
        return [
            Select::make('locale')
                ->label(self::t('admin.profiles.form.translation_locale'))
                ->options(fn () => $this->localeOptions())
                ->searchable()
                ->required(),

            TextInput::make('key')
                ->label(self::t('admin.profiles.form.translation_key'))
                ->helperText(self::t('admin.profiles.form.translation_key_help') . ' ' . self::t('admin.profiles.form.translation_key_prefix_note'))
                ->prefix('servertools::')
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $current = $get('value');
                    if (!empty($current)) {
                        return;
                    }

                    $locale = $get('locale');
                    $profileId = $this->getOwnerRecord()?->id;
                    $value = ServerToolTranslationService::getProfileTranslation($profileId, $locale, $this->prefixKey($state));

                    if (!is_null($value) && $value !== '') {
                        $set('value', $value);
                    }
                })
                ->dehydrateStateUsing(fn ($state) => $this->prefixKey($state)),

            Textarea::make('value')
                ->label(self::t('admin.profiles.form.translation_value'))
                ->rows(3)
                ->required()
                ->columnSpanFull(),
        ];
    }

    protected function localeOptions(): array
    {
        $dbLocales = ServerToolProfileTranslation::query()
            ->distinct()
            ->orderBy('locale')
            ->pluck('locale')
            ->filter()
            ->values()
            ->all();

        $supported = config('app.supported_locales');
        $supportedLocales = is_array($supported) ? array_values($supported) : [];

        $locales = array_values(array_unique(array_filter(array_merge(
            $dbLocales,
            $supportedLocales,
            [app()->getLocale()]
        ))));

        return array_combine($locales, $locales);
    }

    protected static function t(string $key): string
    {
        return trans('servertools::' . $key);
    }

    protected function prefixKey(string $key): string
    {
        if (str_starts_with($key, 'servertools::')) {
            return $key;
        }

        return 'servertools::' . $key;
    }

    protected function stripPrefixKey(mixed $key): mixed
    {
        if (!is_string($key)) {
            return $key;
        }

        if (str_starts_with($key, 'servertools::')) {
            return substr($key, strlen('servertools::'));
        }

        return $key;
    }
}
