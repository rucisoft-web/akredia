<?php

namespace App\Filament\App\Resources\ProgramAccreditations;

use App\Filament\App\Resources\ProgramAccreditations\Pages\ManageProgramAccreditations;
use App\Filament\Concerns\ManagerPermissions;
use App\Models\ProgramAccreditation;
use App\Enums\AccreditationAgency;
use App\Enums\AccreditationRank;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ProgramAccreditationResource extends Resource
{
    use ManagerPermissions;

    protected static ?string $model = ProgramAccreditation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;

    protected static string|UnitEnum|null $navigationGroup = 'Institusi';

    protected static ?int $navigationSort = 35;

    protected static ?string $modelLabel = 'Akreditasi prodi';

    protected static ?string $pluralModelLabel = 'Akreditasi Prodi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('study_program_id')->label('Program studi')->options(fn () => \App\Models\StudyProgram::query()->orderBy('level')->orderBy('name')->get()->mapWithKeys(fn ($p) => [$p->id => $p->label()]))->searchable()->required(),
            Select::make('agency')->label('Lembaga')->options(AccreditationAgency::class)->required(),
            Select::make('rank')->label('Status / peringkat')->options(AccreditationRank::class)->required(),
            TextInput::make('sk_number')->label('Nomor SK'),
            DatePicker::make('sk_date')->label('Tanggal SK'),
            DatePicker::make('valid_until')->label('Berlaku sampai')->required(),
            Toggle::make('is_current')->label('Akreditasi berlaku saat ini')->default(true),
            Toggle::make('is_international')->label('Akreditasi internasional'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('valid_until')
            ->columns([
                TextColumn::make('studyProgram.name')->label('Program studi')->searchable()->weight('bold')
                    ->description(fn ($record) => $record->studyProgram?->level),
                TextColumn::make('agency')->label('Lembaga')->badge(),
                TextColumn::make('rank')->label('Peringkat')->badge(),
                TextColumn::make('sk_number')->label('Nomor SK')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('valid_until')->label('Berlaku sampai')->date('d/m/Y')->sortable()
                    ->color(fn ($record) => match (true) { ($record->monthsLeft() ?? 99) < 0 => 'danger', ($record->monthsLeft() ?? 99) <= 6 => 'danger', ($record->monthsLeft() ?? 99) <= 18 => 'warning', default => null })
                    ->description(fn ($record) => $record->valid_until ? (($m = $record->monthsLeft()) < 0 ? 'Kedaluwarsa' : "sisa {$m} bulan") : null),
                IconColumn::make('is_current')->label('Berlaku')->boolean(),
            ])
            ->filters([
                SelectFilter::make('rank')->label('Peringkat')->options(AccreditationRank::class)->multiple(),
                SelectFilter::make('agency')->label('Lembaga')->options(AccreditationAgency::class),
                TernaryFilter::make('is_current')->label('Berlaku saat ini')->default(true),
                TernaryFilter::make('expiring')->label('Kedaluwarsa ≤ 18 bulan')
                    ->queries(true: fn (Builder $q) => $q->whereDate('valid_until', '<=', now()->addMonths(18)), false: fn (Builder $q) => $q->whereDate('valid_until', '>', now()->addMonths(18))),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageProgramAccreditations::route('/')];
    }
}
