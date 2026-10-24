<?php

namespace App\Filament\App\Resources\VisitQuestions;

use App\Filament\App\Resources\VisitQuestions\Pages\ManageVisitQuestions;
use App\Filament\Concerns\WriterPermissions;
use App\Models\VisitQuestion;
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

class VisitQuestionResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = VisitQuestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Asesmen Lapangan';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Pertanyaan';

    protected static ?string $pluralModelLabel = 'Bank Pertanyaan AL';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('indicator_id')->label('Indikator')->options(fn () => \App\Models\Indicator::query()->orderBy('number')->get()->mapWithKeys(fn ($i) => [$i->id => $i->label()]))->searchable(),
            Select::make('group')->label('Kelompok narasumber')->options(VisitGroup::class)->required(),
            Textarea::make('question')->label('Pertanyaan asesor yang mungkin muncul')->required()->columnSpanFull(),
            Textarea::make('answer_points')->label('Pokok jawaban dan bukti yang ditunjukkan')->columnSpanFull(),
            TextInput::make('owner')->label('Penjawab utama'),
            Toggle::make('is_rehearsed')->label('Sudah digladikan'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('indicator_id')
            ->columns([
                TextColumn::make('indicator.number')->label('Ind.')->sortable(),
                TextColumn::make('group')->label('Kelompok')->badge(),
                TextColumn::make('question')->label('Pertanyaan')->wrap()->searchable(),
                TextColumn::make('owner')->label('Penjawab')->toggleable(),
                IconColumn::make('is_rehearsed')->label('Gladi')->boolean(),
            ])
            ->filters([
                SelectFilter::make('group')->label('Kelompok')->options(VisitGroup::class),
                SelectFilter::make('indicator_id')->label('Indikator')->options(fn () => \App\Models\Indicator::query()->orderBy('number')->get()->mapWithKeys(fn ($i) => [$i->id => $i->label()]))->searchable(),
                TernaryFilter::make('is_rehearsed')->label('Sudah digladikan'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageVisitQuestions::route('/')];
    }
}
