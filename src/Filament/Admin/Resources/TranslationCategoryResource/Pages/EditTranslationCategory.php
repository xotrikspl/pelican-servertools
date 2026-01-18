<?php

namespace Xotriks\Servertools\Filament\Admin\Resources\TranslationCategoryResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Xotriks\Servertools\Filament\Admin\Resources\TranslationCategoryResource;
use Xotriks\Servertools\Services\ServerToolTranslationService;

class EditTranslationCategory extends EditRecord
{
    protected static string $resource = TranslationCategoryResource::class;

    public function getTitle(): string
    {
        return ServerToolTranslationService::translate('admin.pages.edit_translation_category');
    }

    public function getHeading(): string
    {
        return ServerToolTranslationService::translate('admin.pages.edit_translation_category');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label(ServerToolTranslationService::translate('admin.actions.save_changes')),
            $this->getCancelFormAction()
                ->label(ServerToolTranslationService::translate('admin.actions.cancel')),
        ];
    }

    protected function afterSave(): void
    {
        \Log::debug('[ServerTools] translation category updated', [
            'category_id' => $this->record->id,
            'name' => $this->record->name,
            'slug' => $this->record->slug,
        ]);
    }
}
