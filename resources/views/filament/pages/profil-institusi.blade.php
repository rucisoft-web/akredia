<x-filament-panels::page>
<form wire:submit="save" style="max-width: 1100px">
    {{ $this->form }}
    @if ($this->canEditProfile())
        <div style="margin-top:1rem"><x-filament::button type="submit" icon="heroicon-o-check">Simpan profil</x-filament::button></div>
    @endif
</form>
</x-filament-panels::page>
