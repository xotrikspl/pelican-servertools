<?php

namespace Xotriks\Servertools\Filament\Admin\Resources\ServerToolConfigurationResource\Pages;

use Xotriks\Servertools\Filament\Admin\Resources\ServerToolConfigurationResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Xotriks\Servertools\Services\ServerToolTranslationService;

class CreateServerToolConfiguration extends CreateRecord
{
    protected static string $resource = ServerToolConfigurationResource::class;

    public function getTitle(): string
    {
        return ServerToolTranslationService::translate('admin.pages.create_profile');
    }

    public function getHeading(): string
    {
        return ServerToolTranslationService::translate('admin.pages.create_profile');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label(ServerToolTranslationService::translate('admin.actions.save_changes')),
            $this->getCancelFormAction()
                ->label(ServerToolTranslationService::translate('admin.actions.cancel')),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure profile_name is set
        if (empty($data['profile_name']) && !empty($data['name'])) {
            $data['profile_name'] = Str::slug($data['name']);
        }

        $data['config'] = ServerToolConfigurationResource::buildConfigFromFiles($data['config_builder'] ?? []);
        unset($data['config_builder']);

        return $data;
    }

    protected function afterCreate(): void
    {
        ServerToolConfigurationResource::ensureEggHasServerToolsFeature($this->record->egg_id ?? null);

        \Log::debug('[ServerTools] profile created', [
            'profile_id' => $this->record->id,
            'profile_name' => $this->record->profile_name,
            'egg_id' => $this->record->egg_id,
        ]);
    }
}
