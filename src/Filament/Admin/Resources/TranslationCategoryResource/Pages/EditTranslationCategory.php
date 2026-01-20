<?php

namespace Xotriks\Servertools\Filament\Admin\Resources\TranslationCategoryResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Xotriks\Servertools\Filament\Admin\Resources\TranslationCategoryResource;

class EditTranslationCategory extends EditRecord
{
    protected static string $resource = TranslationCategoryResource::class;

    public function getTitle(): string
    {
        return trans('servertools::admin.pages.edit_translation_category');
    }

    public function getHeading(): string
    {
        return trans('servertools::admin.pages.edit_translation_category');
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('servertools::admin.actions.save_changes'))
                ->color('primary')
                ->action('save')
                ->keyBindings(['mod+s']),
            Action::make('cancel')
                ->label(trans('servertools::admin.actions.cancel'))
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
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
