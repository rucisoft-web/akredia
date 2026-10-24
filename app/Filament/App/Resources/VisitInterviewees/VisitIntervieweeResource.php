<?php

namespace App\Filament\App\Resources\VisitInterviewees;

use App\Filament\App\Resources\VisitInterviewees\Pages\ManageVisitInterviewees;
use App\Filament\Concerns\WriterPermissions;
use App\Models\VisitInterviewee;
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

class VisitIntervieweeResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = VisitInterviewee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Asesmen Lapangan';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'Narasumber';

    protected static ?string $pluralModelLabel = 'Daftar Narasumber';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_visit_id')->label('Asesmen lapangan')->options(fn () => \App\Models\SiteVisit::query()->orderByDesc('starts_on')->pluck('title', 'id'))->required()->default(fn () => \App\Models\SiteVisit::query()->latest('starts_on')->value('id')),
            Select::make('group')->label('Kelompok')->options(VisitGroup::class)->required(),
            TextInput::make('name')->label('Nama')->required(),
            TextInput::make('position')->label('Jabatan / keterangan'),
            TextInput::make('phone')->label('Telepon'),
            Toggle::make('is_confirmed')->label('Sudah konfirmasi hadir'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('group')
            ->columns([
                TextColumn::make('group')->label('Kelompok')->badge(),
                TextColumn::make('name')->label('Nama')->weight('bold')->searchable(),
                TextColumn::make('position')->label('Jabatan')->placeholder('—'),
                TextColumn::make('phone')->label('Telepon')->placeholder('—'),
                IconColumn::make('is_confirmed')->label('Konfirmasi')->boolean(),
            ])
            ->filters([
                SelectFilter::make('site_visit_id')->label('Asesmen')->options(fn () => \App\Models\SiteVisit::query()->orderByDesc('starts_on')->pluck('title', 'id')),
                SelectFilter::make('group')->label('Kelompok')->options(VisitGroup::class),
                TernaryFilter::make('is_confirmed')->label('Konfirmasi'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageVisitInterviewees::route('/')];
    }
}
