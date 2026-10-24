<?php

namespace App\Filament\Platform\Resources\Institutions;

use App\Enums\InstitutionType;
use App\Enums\Role;
use App\Filament\Platform\Resources\Institutions\Pages\ManageInstitutions;
use App\Models\Institution;
use App\Models\Membership;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class InstitutionResource extends Resource
{
    protected static ?string $model = Institution::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Institusi';

    protected static ?string $pluralModelLabel = 'Institusi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identitas')->columns(2)->schema([
                TextInput::make('name')->label('Nama perguruan tinggi')->required()->maxLength(255)->columnSpanFull(),
                TextInput::make('code')->label('Kode PT (PDDikti)')->maxLength(20),
                TextInput::make('slug')->label('Slug alamat')->helperText('Dipakai di URL /app/{slug}. Kosongkan agar dibuat otomatis.')->alphaDash()->unique(ignoreRecord: true)->maxLength(60),
                Select::make('type')->label('Jenis PT (varian Buku 3)')->options(InstitutionType::class)->required()->default('pts_akademik'),
                TextInput::make('city')->label('Kota'),
                TextInput::make('legal_entity')->label('Badan penyelenggara'),
                TextInput::make('website')->label('Laman')->url(),
            ]),
            Section::make('Akreditasi terakhir')->columns(3)->schema([
                TextInput::make('last_accreditation_rank')->label('Peringkat'),
                TextInput::make('last_accreditation_sk')->label('Nomor SK'),
                DatePicker::make('last_accreditation_until')->label('Berlaku sampai'),
            ]),
            Section::make('Pengaturan')->columns(3)->schema([
                Toggle::make('is_active')->label('Aktif')->default(true),
                Toggle::make('ai_enabled')->label('Asisten AI diizinkan')->default(true),
                Toggle::make('is_demo')->label('Data contoh')->disabled(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->label('Institusi')->searchable()->weight('bold')->description(fn (Institution $r) => '/app/'.$r->slug),
                TextColumn::make('type')->label('Jenis')->badge(),
                TextColumn::make('city')->label('Kota')->placeholder('—'),
                TextColumn::make('memberships_count')->label('Pengguna')->counts('memberships'),
                TextColumn::make('last_accreditation_until')->label('Akreditasi s.d.')->date('d M Y')->placeholder('—'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                IconColumn::make('is_demo')->label('Contoh')->boolean(),
            ])
            ->filters([TernaryFilter::make('is_active')->label('Aktif'), TernaryFilter::make('is_demo')->label('Data contoh')])
            ->recordActions([
                Action::make('open')->label('Buka')->icon(Heroicon::OutlinedArrowTopRightOnSquare)->url(fn (Institution $r) => url('/app/'.$r->slug))->openUrlInNewTab(),
                Action::make('admin')->label('Tambah admin PT')->icon(Heroicon::OutlinedUserPlus)
                    ->schema([
                        TextInput::make('name')->label('Nama')->required(),
                        TextInput::make('email')->label('Email')->email()->required(),
                        TextInput::make('password')->label('Kata sandi awal')->password()->revealable()->required()->minLength(10),
                        Select::make('role')->label('Peran')->options(Role::class)->default('admin')->required(),
                    ])
                    ->action(function (Institution $record, array $data) {
                        $user = User::firstOrCreate(['email' => $data['email']], ['name' => $data['name'], 'password' => $data['password'], 'is_active' => true]);
                        Membership::withoutGlobalScopes()->updateOrCreate(['institution_id' => $record->id, 'user_id' => $user->id], ['role' => $data['role'], 'is_active' => true]);
                        Notification::make()->success()->title("{$user->email} ditambahkan ke {$record->name}")->send();
                    }),
                EditAction::make(),
                DeleteAction::make()->modalDescription('Seluruh data institusi ini (proyek, bukti, LED, data kinerja) akan terhapus permanen.'),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageInstitutions::route('/')];
    }
}
