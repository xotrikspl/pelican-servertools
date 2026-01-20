<?php

namespace Xotriks\Servertools\Filament\Admin\Resources\TranslationCategoryResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Xotriks\Servertools\Filament\Admin\Resources\TranslationCategoryResource;

class CreateTranslationCategory extends CreateRecord
{
    protected static string $resource = TranslationCategoryResource::class;

    public function getTitle(): string
    {
        return trans('servertools::admin.pages.create_translation_category');
    }

    public function getHeading(): string
    {
        return trans('servertools::admin.pages.create_translation_category');
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
                ->action('create')
                ->keyBindings(['mod+s']),
            Action::make('cancel')
                ->label(trans('servertools::admin.actions.cancel'))
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }

    protected function afterCreate(): void
    {
        \Log::debug('[ServerTools] translation category created', [
            'category_id' => $this->record->id,
            'name' => $this->record->name,
            'slug' => $this->record->slug,
        ]);
    }
}
