<x-filament-panels::page id="form" :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()" wire:submit="save">
    <div class="space-y-6">
        <!-- Filament Form Rendering -->
        @if($selectedFile && $profile && $this->data)
            <div class="mt-6">
                {{ $this->form }}
            </div>
        @endif
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
