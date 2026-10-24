<?php

namespace App\Filament\App\Resources\Documents\Pages;

use App\Filament\App\Resources\Documents\DocumentResource;
use App\Services\EvidenceService;
use Filament\Resources\Pages\CreateRecord;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected array $firstVersion = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->firstVersion = ['upload' => $data['upload'] ?? null, 'url' => $data['url'] ?? null];
        unset($data['upload'], $data['url']);

        return $data + ['uploaded_by' => auth()->id(), 'status' => 'draft'];
    }

    protected function afterCreate(): void
    {
        $upload = $this->firstVersion['upload'];
        $upload = is_array($upload) ? (array_values($upload)[0] ?? null) : $upload;

        if ($upload || $this->firstVersion['url']) {
            app(EvidenceService::class)->addVersion($this->record, $upload, $this->firstVersion['url'] ?: null, auth()->user(), 'Versi awal');
        }
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', ['record' => $this->record]);
    }
}
