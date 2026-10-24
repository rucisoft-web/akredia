<?php

namespace App\Filament\App\Resources\SiteVisits;

use App\Filament\App\Resources\SiteVisits\Pages\ManageSiteVisits;
use App\Filament\Concerns\WriterPermissions;
use App\Models\SiteVisit;
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

class SiteVisitResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = SiteVisit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|UnitEnum|null $navigationGroup = 'Asesmen Lapangan';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Asesmen lapangan';

    protected static ?string $pluralModelLabel = 'Rencana Asesmen Lapangan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Judul')->required()->default('Asesmen Lapangan Akreditasi Institusi')->columnSpanFull(),
            DatePicker::make('starts_on')->label('Mulai')->required(),
            DatePicker::make('ends_on')->label('Selesai'),
            Select::make('mode')->label('Moda')->options(['luring' => 'Luring', 'daring' => 'Daring', 'hibrida' => 'Hibrida'])->default('luring')->required(),
            Select::make('state')->label('Status')->options(\App\Models\SiteVisit::STATES)->default('rencana')->required(),
            DatePicker::make('rehearsal_on')->label('Gladi'),
            TextInput::make('assessors')->label('Tim asesor'),
            TextInput::make('document_room')->label('Ruang dokumen / tautan')->columnSpanFull(),
            Textarea::make('minutes')->label('Catatan / berita acara')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_on', 'desc')
            ->columns([
                TextColumn::make('title')->label('Asesmen')->weight('bold')->wrap()->description(fn ($record) => $record->assessors),
                TextColumn::make('starts_on')->label('Tanggal')->date('d M Y')->description(fn ($record) => $record->ends_on ? 's.d. '.$record->ends_on->translatedFormat('d M Y') : null),
                TextColumn::make('mode')->label('Moda')->badge()->color('gray'),
                TextColumn::make('rehearsal_on')->label('Gladi')->date('d M Y')->placeholder('—'),
                TextColumn::make('sessions_count')->label('Sesi')->counts('sessions'),
                TextColumn::make('interviewees_count')->label('Narasumber')->counts('interviewees'),
                TextColumn::make('recommendations_count')->label('Rekomendasi')->counts('recommendations'),
                TextColumn::make('state')->label('Status')->badge()->formatStateUsing(fn ($state) => \App\Models\SiteVisit::STATES[$state] ?? $state),
            ])
            ->filters([

            ])
            ->recordActions([Action::make('agenda')->label('Agenda')->icon(Heroicon::OutlinedCalendar)->color('gray')->url(fn ($record) => \App\Filament\App\Resources\VisitSessions\VisitSessionResource::getUrl('index', ['filters' => ['site_visit_id' => ['value' => $record->id]]])), EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSiteVisits::route('/')];
    }
}
