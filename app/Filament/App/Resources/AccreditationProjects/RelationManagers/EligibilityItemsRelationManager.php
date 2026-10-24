<?php

namespace App\Filament\App\Resources\AccreditationProjects\RelationManagers;

use App\Models\Document;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class EligibilityItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'eligibilityItems';

    protected static ?string $title = 'Daftar Eligibilitas';

    public function isReadOnly(): bool
    {
        return auth()->user()?->roleIn(filament()->getTenant())?->canManage() !== true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('label')->label('Butir')->required()->columnSpanFull(),
            Textarea::make('description')->label('Keterangan')->columnSpanFull(),
            Select::make('document_id')->label('Bukti pendukung')->options(fn () => Document::query()->orderBy('title')->pluck('title', 'id'))->searchable(),
            Toggle::make('is_met')->label('Terpenuhi'),
            Textarea::make('note')->label('Catatan')->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->description('Prasyarat administratif sebelum pengajuan ke SAPTO. Seluruh butir harus terpenuhi agar gerbang Siap Diajukan terbuka.')
            ->columns([
                TextColumn::make('label')->label('Butir')->wrap()->weight('bold')->description(fn ($record) => $record->description),
                TextColumn::make('document_id')->label('Bukti')->formatStateUsing(fn ($state) => Document::find($state)?->code)->placeholder('—'),
                ToggleColumn::make('is_met')->label('Terpenuhi')->disabled(fn () => $this->isReadOnly()),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
