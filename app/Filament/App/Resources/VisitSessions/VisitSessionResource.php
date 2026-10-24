<?php

namespace App\Filament\App\Resources\VisitSessions;

use App\Filament\App\Resources\VisitSessions\Pages\ManageVisitSessions;
use App\Filament\Concerns\WriterPermissions;
use App\Models\VisitSession;
use App\Enums\VisitGroup;
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

class VisitSessionResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = VisitSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static string|UnitEnum|null $navigationGroup = 'Asesmen Lapangan';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Sesi';

    protected static ?string $pluralModelLabel = 'Agenda Sesi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_visit_id')->label('Asesmen lapangan')->options(fn () => \App\Models\SiteVisit::query()->orderByDesc('starts_on')->pluck('title', 'id'))->required()->default(fn () => \App\Models\SiteVisit::query()->latest('starts_on')->value('id')),
            Select::make('group')->label('Kelompok')->options(VisitGroup::class)->required(),
            DateTimePicker::make('starts_at')->label('Mulai')->required()->seconds(false),
            DateTimePicker::make('ends_at')->label('Selesai')->seconds(false),
            TextInput::make('topic')->label('Topik / acara')->required()->columnSpanFull(),
            TextInput::make('room')->label('Ruang / tautan'),
            TextInput::make('coordinator')->label('Koordinator'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at')
            ->columns([
                TextColumn::make('starts_at')->label('Waktu')->dateTime('D, d M H:i')->sortable()->description(fn ($record) => $record->ends_at ? 's.d. '.$record->ends_at->format('H:i') : null),
                TextColumn::make('group')->label('Kelompok')->badge(),
                TextColumn::make('topic')->label('Topik')->wrap()->weight('bold'),
                TextColumn::make('room')->label('Ruang')->placeholder('—'),
                TextColumn::make('coordinator')->label('Koordinator')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('site_visit_id')->label('Asesmen')->options(fn () => \App\Models\SiteVisit::query()->orderByDesc('starts_on')->pluck('title', 'id')),
                SelectFilter::make('group')->label('Kelompok')->options(VisitGroup::class),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageVisitSessions::route('/')];
    }
}
