<?php

namespace App\Filament\App\Resources\IpRights;

use App\Filament\App\Resources\IpRights\Pages\ManageIpRights;
use App\Filament\Concerns\WriterPermissions;
use App\Models\IpRight;
use App\Enums\IpType;
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

class IpRightResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = IpRight::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    protected static string|UnitEnum|null $navigationGroup = 'Data Kinerja & LKPT';

    protected static ?int $navigationSort = 55;

    protected static ?string $modelLabel = 'HKI';

    protected static ?string $pluralModelLabel = 'HKI & Karya Diadopsi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Judul karya')->required()->columnSpanFull(),
            Select::make('lecturer_id')->label('Dosen')->options(fn () => \App\Models\Lecturer::query()->orderBy('name')->pluck('name', 'id'))->searchable(),
            TextInput::make('year')->label('Tahun')->numeric()->minValue(2000)->maxValue(2100)->default(now()->year - 1)->required(),
            Select::make('ip_type')->label('Jenis')->options(IpType::class)->required(),
            TextInput::make('number')->label('Nomor pencatatan / paten'),
            Toggle::make('is_adopted')->label('Diadopsi masyarakat / industri'),
            TextInput::make('adopter')->label('Pengadopsi'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->columns([
                TextColumn::make('title')->label('Karya')->searchable()->wrap(),
                TextColumn::make('lecturer.name')->label('Dosen')->toggleable(),
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('ip_type')->label('Jenis')->badge(),
                IconColumn::make('is_adopted')->label('Diadopsi')->boolean(),
                TextColumn::make('adopter')->label('Pengadopsi')->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('ip_type')->label('Jenis')->options(IpType::class)->multiple(),
                TernaryFilter::make('is_adopted')->label('Diadopsi'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageIpRights::route('/')];
    }
}
