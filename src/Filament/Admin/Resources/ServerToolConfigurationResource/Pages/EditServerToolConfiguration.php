<?php

namespace Xotriks\Servertools\Filament\Admin\Resources\ServerToolConfigurationResource\Pages;

use Xotriks\Servertools\Filament\Admin\Resources\ServerToolConfigurationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditServerToolConfiguration extends EditRecord
{
	protected static string $resource = ServerToolConfigurationResource::class;

	public function getTitle(): string
	{
		return trans('servertools::admin.pages.edit_profile');
	}

	public function getHeading(): string
	{
		return trans('servertools::admin.pages.edit_profile');
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
