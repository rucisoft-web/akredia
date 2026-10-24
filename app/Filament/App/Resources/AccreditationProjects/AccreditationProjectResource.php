<?php

namespace App\Filament\App\Resources\AccreditationProjects;

use App\Enums\ProjectState;
use App\Enums\Track;
use App\Filament\App\Resources\AccreditationProjects\Pages\CreateAccreditationProject;
use App\Filament\App\Resources\AccreditationProjects\Pages\EditAccreditationProject;
use App\Filament\App\Resources\AccreditationProjects\Pages\ListAccreditationProjects;
use App\Filament\App\Resources\AccreditationProjects\Pages\ViewAccreditationProject;
use App\Filament\App\Resources\AccreditationProjects\RelationManagers\EligibilityItemsRelationManager;
use App\Filament\App\Resources\AccreditationProjects\RelationManagers\SnapshotsRelationManager;
use App\Filament\Concerns\ManagerPermissions;
use App\Models\AcademicPeriod;
use App\Models\AccreditationProject;
use App\Models\InstrumentVariant;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class AccreditationProjectResource extends Resource
{
    use ManagerPermissions;

    protected static ?string $model = AccreditationProject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static string|UnitEnum|null $navigationGroup = 'Persiapan Akreditasi';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Proyek akreditasi';

    protected static ?string $pluralModelLabel = 'Proyek Akreditasi';

    public static function canEdit(Model $record): bool
    {
        return static::canManage() && ! in_array($record->state, [ProjectState::Submitted, ProjectState::Assessment, ProjectState::Done], true);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Proyek')->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->label('Nama proyek')->required()->default('Akreditasi Institusi '.now()->year)->columnSpanFull(),
                Select::make('instrument_variant_id')->label('Varian instrumen')->required()
                    ->options(fn () => InstrumentVariant::query()->with('version')->get()->mapWithKeys(fn ($v) => [$v->id => $v->version->code.' · '.$v->name]))
                    ->default(fn () => InstrumentVariant::query()->where('institution_type', static::tenantInstitution()?->type?->value ?? 'pts_akademik')->value('id'))
                    ->disabled(fn (?AccreditationProject $record) => $record?->exists && $record->workItems()->exists())
                    ->helperText('Varian menentukan sub-indikator, ambang, dan syarat perlu. Tidak dapat diganti setelah butir kerja dibangkitkan.'),
                Select::make('track')->label('Jalur target')->options(Track::class)->default('accredited')->required()
                    ->disabled(fn (?AccreditationProject $record) => (bool) $record?->track_locked_at)
                    ->helperText('Kedua jalur selalu diproyeksikan; jalur target menentukan gerbang dan deskriptor kertas kerja.'),
                Select::make('ts_period_id')->label('Tahun Sekarang (TS)')
                    ->options(fn () => AcademicPeriod::query()->orderByDesc('year_start')->pluck('name', 'id'))
                    ->helperText('Tahun akademik terakhir yang datanya lengkap.'),
                DatePicker::make('target_date')->label('Target pengajuan'),
                TextInput::make('word_limit')->label('Batas kata per sub-indikator LED')->numeric()->default(config('akredia.word_limit', 1500))->minValue(200)->maxValue(5000),
            ]),
            Section::make('Bobot Indeks Kesiapan Akredia (AKI)')->columns(5)->columnSpanFull()->collapsed()
                ->description('Bobot default 40/20/15/15/10. Ubah hanya bila pimpinan memutuskan prioritas lain; total tidak harus 100.')
                ->schema(collect(AccreditationProject::WEIGHT_LABELS)->map(fn ($label, $key) => TextInput::make("aki_weights.$key")->label($label)
                    ->numeric()->minValue(0)->maxValue(100)->default(AccreditationProject::DEFAULT_WEIGHTS[$key]))->values()->all()),
            Textarea::make('notes')->label('Catatan')->columnSpanFull(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.project.summary')->columnSpanFull(),
            Section::make('Rincian')->columns(4)->columnSpanFull()->collapsible()->collapsed()->schema([
                TextEntry::make('variant.name')->label('Varian instrumen'),
                TextEntry::make('tsPeriod.name')->label('TS')->placeholder('—'),
                TextEntry::make('word_limit')->label('Batas kata LED'),
                TextEntry::make('track_locked_at')->label('Jalur dikunci')->dateTime('d/m/Y H:i')->placeholder('Belum'),
                TextEntry::make('frozen_at')->label('Data TS dikunci')->dateTime('d/m/Y H:i')->placeholder('Belum'),
                TextEntry::make('submitted_at')->label('Tanggal diajukan')->date('d/m/Y')->placeholder('—'),
                TextEntry::make('state_note')->label('Catatan status')->placeholder('—')->columnSpan(2),
                TextEntry::make('notes')->label('Catatan')->placeholder('—')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->label('Proyek')->weight('bold')->searchable()->description(fn ($record) => $record->variant?->name),
                TextColumn::make('track')->label('Jalur')->badge(),
                TextColumn::make('state')->label('Status')->badge(),
                TextColumn::make('aki')->label('AKI')->numeric(1, locale: 'id')->weight('bold')
                    ->color(fn ($state) => $state === null ? null : ($state >= 80 ? 'success' : ($state >= 60 ? 'warning' : 'danger'))),
                TextColumn::make('projection_accredited')->label('Proyeksi Terakreditasi')->suffix('%')->numeric(1, locale: 'id'),
                TextColumn::make('projection_excellent')->label('Proyeksi Unggul')->suffix('%')->numeric(1, locale: 'id'),
                TextColumn::make('target_date')->label('Target')->date('d/m/Y')
                    ->description(fn ($record) => ($d = $record->daysToTarget()) === null ? null : ($d >= 0 ? $d.' hari lagi' : abs($d).' hari lewat')),
                TextColumn::make('work_items_count')->label('Sub-indikator')->counts('workItems'),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()]);
    }

    public static function getRelations(): array
    {
        return [EligibilityItemsRelationManager::class, SnapshotsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccreditationProjects::route('/'),
            'create' => CreateAccreditationProject::route('/create'),
            'view' => ViewAccreditationProject::route('/{record}'),
            'edit' => EditAccreditationProject::route('/{record}/edit'),
        ];
    }
}
