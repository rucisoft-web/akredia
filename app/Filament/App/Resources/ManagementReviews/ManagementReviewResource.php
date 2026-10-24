<?php

namespace App\Filament\App\Resources\ManagementReviews;

use App\Filament\App\Resources\ManagementReviews\Pages\ManageManagementReviews;
use App\Filament\Concerns\WriterPermissions;
use App\Models\ManagementReview;
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

class ManagementReviewResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = ManagementReview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static string|UnitEnum|null $navigationGroup = 'SPMI';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = 'RTM';

    protected static ?string $pluralModelLabel = 'Rapat Tinjauan Manajemen';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Judul rapat')->required(),
            DatePicker::make('meeting_date')->label('Tanggal')->required(),
            TextInput::make('chair')->label('Pimpinan rapat'),
            Textarea::make('minutes')->label('Notulen')->rows(5)->columnSpanFull(),
            Textarea::make('outcomes')->label('Keputusan dan tindak lanjut')->rows(4)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('meeting_date', 'desc')
            ->columns([
                TextColumn::make('meeting_date')->label('Tanggal')->date('d/m/Y')->sortable(),
                TextColumn::make('title')->label('Rapat')->searchable()->wrap()->weight('bold'),
                TextColumn::make('chair')->label('Pimpinan'),
                TextColumn::make('outcomes')->label('Keputusan')->limit(80)->wrap()->toggleable(),
            ])
            ->filters([

            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageManagementReviews::route('/')];
    }
}
