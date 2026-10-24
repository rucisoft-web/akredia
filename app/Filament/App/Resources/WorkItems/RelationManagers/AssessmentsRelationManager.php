<?php

namespace App\Filament\App\Resources\WorkItems\RelationManagers;

use App\Enums\Track;
use App\Services\ReadinessCalculator;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Penilaian biner per jalur: mandiri (PIC), asesor internal (reviewer/asesor, bukan PIC), dan hasil asesmen resmi.
 */
class AssessmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assessments';

    protected static ?string $title = 'Penilaian';

    public const KINDS = ['self' => 'Mandiri (PIC)', 'internal' => 'Asesor internal', 'final' => 'Hasil asesmen BAN-PT'];

    protected function role()
    {
        return auth()->user()?->roleIn(filament()->getTenant());
    }

    public function isReadOnly(): bool
    {
        return $this->role()?->canWrite() !== true && $this->role()?->canReview() !== true && $this->role()?->canAudit() !== true;
    }

    /** @return array<string, string> jenis penilaian yang boleh dicatat pengguna ini. */
    protected function allowedKinds(): array
    {
        $role = $this->role();
        $item = $this->getOwnerRecord();
        $kinds = [];

        if ($role?->canWrite()) {
            $kinds['self'] = self::KINDS['self'];
        }

        if (($role?->canReview() || $role?->canAudit()) && ! in_array(auth()->id(), [$item->pic_id, $item->second_pic_id], true)) {
            $kinds['internal'] = self::KINDS['internal'];
        }

        if ($role?->canManage()) {
            $kinds['final'] = self::KINDS['final'];
        }

        return $kinds;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('kind')->label('Jenis penilaian')->options(fn () => $this->allowedKinds())->required()
                ->helperText('Asesor internal tidak boleh PIC butir ini (prinsip empat mata).'),
            Select::make('track')->label('Jalur')->options(Track::class)->default(fn () => $this->getOwnerRecord()->project->track->value)->required(),
            Toggle::make('is_met')->label('Memenuhi deskriptor')->helperText('Penilaian IAPT 4.1 bersifat biner: memenuhi atau tidak.'),
            Textarea::make('note')->label('Justifikasi')->required()->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        $refresh = fn () => app(ReadinessCalculator::class)->refresh($this->getOwnerRecord()->project);

        return $table
            ->description('Proyeksi skor memakai penilaian tertinggi tingkatannya: hasil asesmen, lalu asesor internal, lalu mandiri.')
            ->columns([
                TextColumn::make('kind')->label('Jenis')->formatStateUsing(fn ($state) => self::KINDS[$state] ?? $state)->badge()
                    ->color(fn ($state) => ['self' => 'gray', 'internal' => 'info', 'final' => 'primary'][$state] ?? 'gray'),
                TextColumn::make('track')->label('Jalur')->badge(),
                IconColumn::make('is_met')->label('Memenuhi')->boolean(),
                TextColumn::make('note')->label('Justifikasi')->wrap()->lineClamp(3),
                TextColumn::make('assessor.name')->label('Penilai')->placeholder('—'),
                TextColumn::make('assessed_at')->label('Waktu')->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([
                CreateAction::make()->label('Catat penilaian')
                    ->visible(fn () => $this->allowedKinds() !== [])
                    ->using(function (array $data) {
                        $record = $this->getOwnerRecord()->assessments()->updateOrCreate(
                            ['kind' => $data['kind'], 'track' => $data['track']],
                            ['is_met' => $data['is_met'] ?? false, 'note' => $data['note'], 'assessor_id' => auth()->id(), 'assessed_at' => now(), 'institution_id' => $this->getOwnerRecord()->institution_id],
                        );

                        return $record;
                    })
                    ->after(function () use ($refresh) {
                        $refresh();
                        Notification::make()->success()->title('Penilaian tersimpan; proyeksi dan warna diperbarui')->send();
                    }),
            ])
            ->recordActions([
                EditAction::make()->visible(fn (Model $record) => array_key_exists($record->kind, $this->allowedKinds()))
                    ->mutateDataUsing(fn (array $data) => $data + ['assessor_id' => auth()->id(), 'assessed_at' => now()])
                    ->after($refresh),
                DeleteAction::make()->visible(fn () => (bool) $this->role()?->canManage())->after($refresh),
            ]);
    }
}
