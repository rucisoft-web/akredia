<?php

namespace App\Filament\App\Pages;

use App\Enums\InstitutionType;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** Profil PT sesuai halaman identitas LKPT (FR-INS-01). */
class ProfilInstitusi extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Institusi';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Profil Institusi';

    protected static ?string $title = 'Profil Institusi';

    protected static ?string $slug = 'profil-institusi';

    protected string $view = 'filament.pages.profil-institusi';

    public ?array $data = [];

    public function canEditProfile(): bool
    {
        return (bool) auth()->user()?->roleIn(filament()->getTenant())?->canManage();
    }

    public function mount(): void
    {
        $this->form->fill(filament()->getTenant()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->disabled(fn () => ! $this->canEditProfile())->components([
            Section::make('Identitas')->columns(3)->schema([
                TextInput::make('name')->label('Nama perguruan tinggi')->required()->columnSpan(2),
                TextInput::make('code')->label('Kode PT'),
                Select::make('type')->label('Jenis PT (menentukan varian instrumen)')->options(InstitutionType::class)->required(),
                TextInput::make('legal_entity')->label('Badan penyelenggara')->columnSpan(2),
                TextInput::make('sk_number')->label('Nomor SK pendirian'),
                DatePicker::make('sk_date')->label('Tanggal SK pendirian'),
                TextInput::make('first_intake_year')->label('Tahun pertama menerima mahasiswa')->numeric(),
                TextInput::make('city')->label('Kota'),
                TextInput::make('website')->label('Laman')->url(),
                TextInput::make('email')->label('Email')->email(),
                Textarea::make('address')->label('Alamat')->columnSpanFull(),
            ]),
            Section::make('Akreditasi terakhir & diferensiasi misi')->columns(3)->schema([
                TextInput::make('last_accreditation_rank')->label('Peringkat akreditasi institusi terakhir'),
                TextInput::make('last_accreditation_sk')->label('Nomor SK'),
                DatePicker::make('last_accreditation_until')->label('Berlaku sampai'),
                Textarea::make('vision')->label('Visi')->columnSpanFull(),
                Textarea::make('mission_focus')->label('Fokus diferensiasi misi')->helperText('Dipakai pada kriteria 4 (indikator 36–39).')->columnSpanFull(),
                Toggle::make('ai_enabled')->label('Izinkan asisten narasi AI (bila kunci API dipasang)'),
            ]),
        ]);
    }

    public function save(): void
    {
        abort_unless($this->canEditProfile(), 403);
        filament()->getTenant()->update($this->form->getState());
        Notification::make()->success()->title('Profil institusi tersimpan')->send();
    }
}
