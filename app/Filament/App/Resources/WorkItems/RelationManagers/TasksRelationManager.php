<?php

namespace App\Filament\App\Resources\WorkItems\RelationManagers;

use App\Filament\App\Resources\Tasks\TaskResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'Tugas';

    public function isReadOnly(): bool
    {
        return auth()->user()?->roleIn(filament()->getTenant())?->canWrite() !== true;
    }

    public function form(Schema $schema): Schema
    {
        return TaskResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return TaskResource::table($table)
            ->headerActions([
                CreateAction::make()->mutateDataUsing(fn (array $data) => $data + [
                    'accreditation_project_id' => $this->getOwnerRecord()->accreditation_project_id,
                    'unit_id' => $data['unit_id'] ?? $this->getOwnerRecord()->unit_id,
                ]),
            ]);
    }
}
