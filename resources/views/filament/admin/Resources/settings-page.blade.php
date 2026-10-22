<x-filament-panels::page>
    <x-filament::card>
        <form wire:submit="save">
            {{ $this->form }}
            <div class="mt-6 flex justify-end">
                <x-filament::button type="submit" color="primary" size="lg">
                    💾 حفظ الإعدادات
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>
</x-filament-panels::page>
