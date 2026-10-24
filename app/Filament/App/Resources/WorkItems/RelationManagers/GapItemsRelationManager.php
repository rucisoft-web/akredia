<?php

namespace App\Filament\App\Resources\WorkItems\RelationManagers;

use App\Enums\GapStatus;
use App\Models\Membership;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Kertas kerja analisis kesenjangan: satu baris per kata kunci deskriptor (FR-GAP-01). */
class GapItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'gapItems';

    protected static ?string $title = 'Kertas Kerja';

    public function isReadOnly(): bool
    {
        return auth()->user()?->roleIn(filament()->getTenant())?->canWrite() !== true;
    }

    protected function members(): array
    {
        return Membership::query()->with('user')->where('is_active', true)->get()->mapWithKeys(fn ($m) => [$m->user_id => $m->user->name])->sort()->all();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('keyword_text')->label('Kata kunci deskriptor')->required()->columnSpanFull(),
            Select::make('status')->label('Status')->options(GapStatus::class)->default('belum')->required(),
            Select::make('owner_id')->label('Penanggung jawab')->options(fn () => $this->members())->searchable(),
            DatePicker::make('due_date')->label('Tenggat'),
            Textarea::make('implementation_note')->label('Kondisi implementasi saat ini')->columnSpanFull(),
            Textarea::make('rtl')->label('Rencana tindak lanjut (RTL)')->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->description('Bandingkan kondisi nyata dengan setiap kata kunci deskriptor jalur target. Kata kunci yang belum terbukti dapat dijadikan tugas RTL.')
            ->columns([
                TextColumn::make('keyword_text')->label('Kata kunci')->wrap()->weight('bold'),
                SelectColumn::make('status')->label('Status')->options(GapStatus::class)->selectablePlaceholder(false)->disabled(fn () => $this->isReadOnly()),
                TextColumn::make('implementation_note')->label('Kondisi')->wrap()->lineClamp(2)->placeholder('—'),
                TextColumn::make('rtl')->label('RTL')->wrap()->lineClamp(2)->placeholder('—'),
                TextColumn::make('owner.name')->label('PJ')->placeholder('—'),
                TextColumn::make('due_date')->label('Tenggat')->date('d/m/Y')->placeholder('—'),
                TextColumn::make('task.number')->label('Tugas')->placeholder('—'),
            ])
            ->headerActions([CreateAction::make()->label('Tambah baris')])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
