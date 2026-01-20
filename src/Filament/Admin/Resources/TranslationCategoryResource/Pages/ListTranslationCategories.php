<?php

namespace Xotriks\Servertools\Filament\Admin\Resources\TranslationCategoryResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Xotriks\Servertools\Filament\Admin\Resources\TranslationCategoryResource;
use Xotriks\Servertools\Models\ServerToolProfileTranslation;
use Xotriks\Servertools\Models\ServerToolTranslationCategory;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ListTranslationCategories extends ListRecords
{
    protected static string $resource = TranslationCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->icon('tabler-plus')
                ->label(trans('servertools::admin.actions.create_translation_category')),

            Action::make('export_translations')
                ->label(trans('servertools::admin.actions.export_translations'))
                ->icon('tabler-download')
                ->action(function () {
                    $payload = $this->buildTranslationsExportPayload();
                    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    $fileName = 'servertools-translations-' . Carbon::now()->format('Ymd_His') . '.json';

                    return Response::streamDownload(function () use ($json) {
                        echo $json;
                    }, $fileName, ['Content-Type' => 'application/json']);
                }),

            Action::make('import_translations')
                ->label(trans('servertools::admin.actions.import_translations'))
                ->icon('tabler-upload')
                ->form([
                    FileUpload::make('file')
                        ->label(trans('servertools::admin.import_export.file'))
                        ->acceptedFileTypes(['application/json', 'text/json', 'text/plain'])
                        ->disk('local')
                        ->directory('servertools/imports')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $path = $data['file'] ?? null;
                    if (!$path || !Storage::disk('local')->exists($path)) {
                        Notification::make()
                            ->title(trans('servertools::admin.notifications.import_translations_failed'))
                            ->danger()
                            ->send();
                        return;
                    }

                    $raw = Storage::disk('local')->get($path);
                    Storage::disk('local')->delete($path);

                    $payload = json_decode($raw, true);
                    if (!is_array($payload) || !isset($payload['categories']) || !is_array($payload['categories'])) {
                        Notification::make()
                            ->title(trans('servertools::admin.notifications.import_invalid_json'))
                            ->danger()
                            ->send();
                        return;
                    }

                    $imported = 0;
                    $updated = 0;
                    $skipped = 0;

                    foreach ($payload['categories'] as $categoryData) {
                        if (!is_array($categoryData)) {
                            $skipped++;
                            continue;
                        }

                        $slug = $categoryData['slug'] ?? null;
                        if (!is_string($slug) || $slug === '') {
                            $skipped++;
                            continue;
                        }

                        $name = $categoryData['name'] ?? $slug;
                        $category = ServerToolTranslationCategory::query()
                            ->firstOrCreate(['slug' => $slug], ['name' => $name]);

                        if ($category->name !== $name && is_string($name) && $name !== '') {
                            $category->update(['name' => $name]);
                        }

                        $translations = $categoryData['translations'] ?? [];
                        if (!is_array($translations)) {
                            $skipped++;
                            continue;
                        }

                        foreach ($translations as $translationData) {
                            if (!is_array($translationData)) {
                                $skipped++;
                                continue;
                            }

                            $locale = $translationData['locale'] ?? null;
                            $key = $translationData['key'] ?? null;
                            $value = $translationData['value'] ?? null;

                            if (!is_string($locale) || $locale === '' || !is_string($key) || $key === '' || !is_string($value)) {
                                $skipped++;
                                continue;
                            }

                            $model = ServerToolProfileTranslation::query()->updateOrCreate(
                                [
                                    'translation_category_id' => $category->id,
                                    'locale' => $locale,
                                    'key' => $key,
                                ],
                                ['value' => $value]
                            );

                            if ($model->wasRecentlyCreated) {
                                $imported++;
                            } else {
                                $updated++;
                            }
                        }
                    }

                    Log::debug('[ServerTools] translations imported', [
                        'imported' => $imported,
                        'updated' => $updated,
                        'skipped' => $skipped,
                    ]);

                    Notification::make()
                        ->title(trans('servertools::admin.notifications.import_translations_success'))
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function buildTranslationsExportPayload(): array
    {
        $categories = ServerToolTranslationCategory::query()
            ->with('translations')
            ->orderBy('id')
            ->get();

        return [
            'version' => 1,
            'exported_at' => Carbon::now()->toISOString(),
            'categories' => $categories->map(function (ServerToolTranslationCategory $category) {
                return [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'translations' => $category->translations
                        ->sortBy(fn (ServerToolProfileTranslation $translation) => $translation->locale . '|' . $translation->key)
                        ->values()
                        ->map(function (ServerToolProfileTranslation $translation) {
                            return [
                                'locale' => $translation->locale,
                                'key' => $translation->key,
                                'value' => $translation->value,
                            ];
                        })
                        ->all(),
                ];
            })->values()->all(),
        ];
    }
}
