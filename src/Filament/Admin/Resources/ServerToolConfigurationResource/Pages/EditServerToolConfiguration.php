<?php

namespace Xotriks\Servertools\Filament\Admin\Resources\ServerToolConfigurationResource\Pages;

use Xotriks\Servertools\Filament\Admin\Resources\ServerToolConfigurationResource;
use Filament\Resources\Pages\EditRecord;
use Xotriks\Servertools\Services\ServerToolTranslationService;

class EditServerToolConfiguration extends EditRecord
{
	protected static string $resource = ServerToolConfigurationResource::class;

	public function getTitle(): string
	{
		return ServerToolTranslationService::translate('admin.pages.edit_profile');
	}

	public function getHeading(): string
	{
		return ServerToolTranslationService::translate('admin.pages.edit_profile');
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

	protected function mutateFormDataBeforeFill(array $data): array
	{
		$data['config_builder'] = ServerToolConfigurationResource::mapConfigToFiles($data['config'] ?? null);

		return $data;
	}

	protected function mutateFormDataBeforeSave(array $data): array
	{
		$data['config'] = ServerToolConfigurationResource::buildConfigFromFiles($data['config_builder'] ?? []);
		unset($data['config_builder']);

		return $data;
	}

	protected function afterSave(): void
	{
		ServerToolConfigurationResource::ensureEggHasServerToolsFeature($this->record->egg_id ?? null);

		\Log::debug('[ServerTools] profile updated', [
			'profile_id' => $this->record->id,
			'profile_name' => $this->record->profile_name,
			'egg_id' => $this->record->egg_id,
		]);
	}
}
