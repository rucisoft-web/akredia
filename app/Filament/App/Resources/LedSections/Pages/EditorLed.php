<?php

namespace App\Filament\App\Resources\LedSections\Pages;

use App\Enums\LedState;
use App\Filament\App\Resources\LedSections\LedSectionResource;
use App\Filament\App\Resources\WorkItems\WorkItemResource;
use App\Models\Document;
use App\Models\LedComment;
use App\Models\LedRevision;
use App\Models\LedSection;
use App\Models\Metric;
use App\Services\LedService;
use App\Services\NarrativeAssistant;
use App\Services\ReadinessCalculator;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use LogicException;

/** Editor LED: penghitung kata langsung, panel kata kunci, sisip sitasi & angka hidup, reviu, revisi, dan asisten narasi. */
class EditorLed extends Page
{
    use InteractsWithRecord;

    protected static string $resource = LedSectionResource::class;

    protected string $view = 'filament.led.editor';

    public string $body = '';

    public string $note = '';

    public string $comment = '';

    public ?array $check = null;

    public ?array $ai = null;

    public bool $preview = false;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->body = (string) ($this->record->body ?: LedService::TEMPLATE);
    }

    protected function section(): LedSection
    {
        /** @var LedSection */
        return $this->record;
    }

    public function getTitle(): string
    {
        return 'LED · Sub-indikator '.$this->section()->workItem->subIndicator->code;
    }

    public function getBreadcrumbs(): array
    {
        return [LedSectionResource::getUrl() => 'Penyusunan LED', 'Editor'];
    }

    protected function role()
    {
        return auth()->user()?->roleIn(filament()->getTenant());
    }

    public function canWriteBody(): bool
    {
        $item = $this->section()->workItem;

        return ! in_array($this->section()->state, [LedState::Review], true)
            && ($this->role()?->canManage() || ($this->role()?->canWrite() && in_array(auth()->id(), [$item->pic_id, $item->second_pic_id], true)));
    }

    public function canReviewBody(): bool
    {
        $item = $this->section()->workItem;

        return (bool) $this->role()?->canReview() && (int) $item->pic_id !== (int) auth()->id();
    }

    protected function attempt(callable $fn, string $success): bool
    {
        try {
            $fn();
            Notification::make()->success()->title($success)->send();
            $this->record->refresh();
            app(ReadinessCalculator::class)->refresh($this->section()->workItem->project);

            return true;
        } catch (LogicException $e) {
            Notification::make()->danger()->title('Tidak dapat diproses')->body($e->getMessage())->persistent()->send();

            return false;
        }
    }

    public function save(): void
    {
        abort_unless($this->canWriteBody(), 403);
        $this->attempt(fn () => app(LedService::class)->save($this->section(), $this->body, auth()->user(), $this->note ?: null), 'Narasi tersimpan sebagai revisi baru');
        $this->note = '';
    }

    public function runCheck(): void
    {
        $this->check = app(NarrativeAssistant::class)->check($this->section(), $this->body);
    }

    public function runAi(): void
    {
        $this->ai = app(NarrativeAssistant::class)->aiReview($this->section(), $this->body);
    }

    public function addComment(): void
    {
        $this->validate(['comment' => 'required|string|max:2000']);
        LedComment::create(['institution_id' => $this->section()->institution_id, 'led_section_id' => $this->section()->id, 'user_id' => auth()->id(), 'body' => $this->comment]);
        $this->comment = '';
    }

    public function resolveComment(int $id): void
    {
        $this->section()->comments()->whereKey($id)->update(['is_resolved' => true]);
    }

    public function restore(int $revisionId): void
    {
        $rev = LedRevision::where('led_section_id', $this->section()->id)->findOrFail($revisionId);
        $this->body = (string) $rev->body;
        Notification::make()->info()->title('Revisi '.$rev->revision.' dimuat ke editor. Simpan untuk memakainya.')->send();
    }

    protected function getHeaderActions(): array
    {
        $svc = app(LedService::class);

        return [
            Action::make('item')->label('Butir kerja')->icon(Heroicon::OutlinedArrowLeft)->color('gray')
                ->url(fn () => WorkItemResource::getUrl('view', ['record' => $this->section()->work_item_id])),
            Action::make('submit')->label('Ajukan reviu')->icon(Heroicon::OutlinedPaperAirplane)
                ->visible(fn () => $this->canWriteBody() && in_array($this->section()->state, [LedState::Draft, LedState::Revision], true))
                ->requiresConfirmation()->modalDescription('Pastikan narasi sudah disimpan. Selama reviu, narasi tidak dapat diubah.')
                ->action(fn () => $this->attempt(fn () => $svc->submitForReview($this->section()), 'Diajukan ke reviewer')),
            Action::make('revise')->label('Minta revisi')->icon(Heroicon::OutlinedArrowUturnLeft)->color('warning')
                ->visible(fn () => $this->canReviewBody() && $this->section()->state === LedState::Review)
                ->schema([Textarea::make('note')->label('Catatan reviu')->required()->rows(5)])
                ->action(fn (array $data) => $this->attempt(fn () => $svc->requestRevision($this->section(), auth()->user(), $data['note']), 'Dikembalikan untuk revisi')),
            Action::make('approve')->label('Setujui')->icon(Heroicon::OutlinedCheckBadge)->color('success')
                ->visible(fn () => $this->canReviewBody() && $this->section()->state === LedState::Review)
                ->requiresConfirmation()->modalDescription('Narasi harus di bawah batas kata dan tanpa sitasi rusak.')
                ->action(fn () => $this->attempt(fn () => $svc->approve($this->section(), auth()->user()), 'Narasi disetujui reviewer')),
            Action::make('ratify')->label('Sahkan')->icon(Heroicon::OutlinedShieldCheck)->color('success')
                ->visible(fn () => (bool) $this->role()?->canLead() && $this->section()->state === LedState::ReviewerApproved)
                ->requiresConfirmation()
                ->action(fn () => $this->attempt(fn () => $svc->ratify($this->section(), auth()->user()), 'Narasi disahkan pimpinan')),
        ];
    }

    protected function getViewData(): array
    {
        $section = $this->section()->load(['workItem.subIndicator.descriptors.keywords', 'workItem.project', 'workItem.evidenceLinks.document', 'revisions.user', 'comments.user', 'approver', 'ratifier']);
        $item = $section->workItem;
        $descriptor = $item->subIndicator->descriptorFor($item->project->track);
        $linked = $item->evidenceLinks->pluck('document')->filter()->unique('id');
        $others = Document::query()->visibleTo(auth()->user(), filament()->getTenant())->where('status', 'valid')
            ->whereNotIn('id', $linked->pluck('id'))->orderBy('title')->limit(200)->get();
        $values = app(LedService::class)->metricValues($item);

        return [
            'section' => $section,
            'item' => $item,
            'descriptor' => $descriptor,
            'limit' => $item->project->word_limit,
            'linkedDocs' => $linked,
            'otherDocs' => $others,
            'metrics' => Metric::query()->orderBy('sort')->get()->map(fn ($m) => ['code' => $m->code, 'name' => $m->name, 'value' => $values[$m->code] ?? null, 'unit' => $m->unit]),
            'rendered' => $this->preview ? app(LedService::class)->render((new LedSection(['body' => $this->body, 'institution_id' => $section->institution_id, 'work_item_id' => $item->id]))->setRelation('workItem', $item)) : null,
            'canWrite' => $this->canWriteBody(),
            'missing' => app(LedService::class)->coverage($item, $this->body)[1],
            'aiAvailable' => NarrativeAssistant::aiAvailable(filament()->getTenant()),
        ];
    }
}
