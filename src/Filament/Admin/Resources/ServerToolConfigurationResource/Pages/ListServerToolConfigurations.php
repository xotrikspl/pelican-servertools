<?php

namespace Xotriks\Servertools\Filament\Admin\Resources\ServerToolConfigurationResource\Pages;

use Xotriks\Servertools\Filament\Admin\Resources\ServerToolConfigurationResource;
use Xotriks\Servertools\Models\ServerToolConfiguration;
use Xotriks\Servertools\Models\ServerToolTranslationCategory;
use Xotriks\Servertools\Services\ServerToolTranslationService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ListServerToolConfigurations extends ListRecords
{
    protected static string $resource = ServerToolConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->label(ServerToolTranslationService::translate('admin.actions.create_profile')),

            Action::make('export_profiles')
                ->label(ServerToolTranslationService::translate('admin.actions.export_profiles'))
                ->icon('tabler-download')
                ->action(function () {
                    $payload = $this->buildProfilesExportPayload();
                    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    $fileName = 'servertools-profiles-' . Carbon::now()->format('Ymd_His') . '.json';

                    return Response::streamDownload(function () use ($json) {
                        echo $json;
                    }, $fileName, ['Content-Type' => 'application/json']);
                }),

            Action::make('import_profiles')
                ->label(ServerToolTranslationService::translate('admin.actions.import_profiles'))
                ->icon('tabler-upload')
                ->form([
                    FileUpload::make('file')
                        ->label(ServerToolTranslationService::translate('admin.import_export.file'))
                        ->acceptedFileTypes(['application/json', 'text/json', 'text/plain'])
                        ->disk('local')
                        ->directory('servertools/imports')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $path = $data['file'] ?? null;
                    if (!$path || !Storage::disk('local')->exists($path)) {
                        Notification::make()
                            ->title(ServerToolTranslationService::translate('admin.notifications.import_profiles_failed'))
                            ->danger()
                            ->send();
                        return;
                    }

                    $raw = Storage::disk('local')->get($path);
                    Storage::disk('local')->delete($path);

                    $payload = json_decode($raw, true);
                    if (!is_array($payload) || !isset($payload['profiles']) || !is_array($payload['profiles'])) {
                        Notification::make()
                            ->title(ServerToolTranslationService::translate('admin.notifications.import_invalid_json'))
                            ->danger()
                            ->send();
                        return;
                    }

                    $imported = 0;
                    $updated = 0;
                    $skipped = 0;

                    foreach ($payload['profiles'] as $profileData) {
                        if (!is_array($profileData)) {
                            $skipped++;
                            continue;
                        }

                        $profileName = $profileData['profile_name'] ?? null;
                        $name = $profileData['name'] ?? null;
                        $config = $profileData['config'] ?? null;

                        if (!is_string($profileName) || $profileName === '' || !is_array($config)) {
                            $skipped++;
                            continue;
                        }

                        $translationCategoryId = null;
                        $categoryData = $profileData['translation_category'] ?? null;
                        if (is_array($categoryData)) {
                            $slug = $categoryData['slug'] ?? null;
                            $catName = $categoryData['name'] ?? $slug;
                            if (is_string($slug) && $slug !== '') {
                                $category = ServerToolTranslationCategory::query()
                                    ->firstOrCreate(['slug' => $slug], ['name' => $catName ?: $slug]);
                                $translationCategoryId = $category->id;
                            }
                        }

                        $attributes = [
                            'name' => is_string($name) && $name !== '' ? $name : $profileName,
                            'description' => is_string($profileData['description'] ?? null)
                                ? $profileData['description']
                                : null,
                            'egg_id' => $profileData['egg_id'] ?? null,
                            'server_tools_enabled' => (bool) ($profileData['server_tools_enabled'] ?? false),
                            'translation_category_id' => $translationCategoryId,
                            'config' => $config,
                        ];

                        $existing = ServerToolConfiguration::query()
                            ->where('profile_name', $profileName)
                            ->first();

                        if ($existing) {
                            $existing->update($attributes);
                            $updated++;
                        } else {
                            ServerToolConfiguration::query()->create(array_merge($attributes, [
                                'profile_name' => $profileName,
                            ]));
                            $imported++;
                        }
                    }

                    Log::debug('[ServerTools] profiles imported', [
                        'imported' => $imported,
                        'updated' => $updated,
                        'skipped' => $skipped,
                    ]);

                    Notification::make()
                        ->title(ServerToolTranslationService::translate('admin.notifications.import_profiles_success'))
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function buildProfilesExportPayload(): array
    {
        $profiles = ServerToolConfiguration::query()
            ->with('translationCategory')
            ->orderBy('id')
            ->get();

        return [
            'version' => 1,
            'exported_at' => Carbon::now()->toISOString(),
            'profiles' => $profiles->map(function (ServerToolConfiguration $profile) {
                return [
                    'name' => $profile->name,
                    'profile_name' => $profile->profile_name,
                    'description' => $profile->description,
                    'egg_id' => $profile->egg_id,
                    'server_tools_enabled' => $profile->server_tools_enabled,
                    'translation_category' => $profile->translationCategory
                        ? [
                            'name' => $profile->translationCategory->name,
                            'slug' => $profile->translationCategory->slug,
                        ]
                        : null,
                    'config' => $profile->config,
                ];
            })->values()->all(),
        ];
    }
}
