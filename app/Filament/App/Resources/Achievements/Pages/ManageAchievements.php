<?php

namespace App\Filament\App\Resources\Achievements\Pages;

use App\Filament\App\Resources\Achievements\AchievementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAchievements extends ManageRecords
{
    protected static string $resource = AchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
