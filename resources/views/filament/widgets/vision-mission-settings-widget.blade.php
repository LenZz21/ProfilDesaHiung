<x-filament-widgets::widget>
    <x-filament::section :heading="$this->sectionHeading">
        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" color="warning">
                {{ $this->saveButtonLabel }}
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>
