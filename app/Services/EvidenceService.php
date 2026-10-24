<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\LedSection;
use App\Models\LinkCheck;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LogicException;
use Throwable;

/** Pustaka Bukti (M05): versi berkas dengan SHA-256, validasi four-eyes, kedaluwarsa, pemeriksa tautan. */
class EvidenceService
{
    public const DISK = 'local';

    /**
     * Tambah versi baru dari berkas unggahan, path di disk privat, atau URL.
     * Versi baru mengembalikan dokumen ke status Diajukan bila sebelumnya Valid.
     */
    public function addVersion(Document $document, UploadedFile|string|null $file, ?string $url, ?User $user, ?string $note = null): DocumentVersion
    {
        $next = (int) $document->versions()->max('version') + 1;
        $data = ['institution_id' => $document->institution_id, 'document_id' => $document->id, 'version' => $next, 'url' => $url, 'note' => $note, 'uploaded_by' => $user?->id];

        if ($file instanceof UploadedFile) {
            $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'berkas';
            $path = $file->storeAs("bukti/{$document->institution_id}/{$document->id}", "v{$next}-{$name}.".strtolower($file->getClientOriginalExtension() ?: 'bin'), self::DISK);
            $data += ['file_path' => $path, 'original_name' => $file->getClientOriginalName(), 'mime' => $file->getMimeType(), 'size' => $file->getSize(), 'sha256' => hash_file('sha256', $file->getRealPath())];
        } elseif (is_string($file) && $file !== '') {
            // Path relatif di disk privat (hasil komponen unggah Filament).
            $disk = Storage::disk(self::DISK);
            $data += ['file_path' => $file, 'original_name' => basename($file), 'mime' => $disk->mimeType($file), 'size' => $disk->size($file), 'sha256' => hash('sha256', $disk->get($file))];
        } elseif ($url) {
            $data['sha256'] = null;
        }

        $version = DocumentVersion::create($data);

        if ($document->status === DocumentStatus::Valid || $document->status === DocumentStatus::Rejected || $document->status === DocumentStatus::Expired) {
            $document->update(['status' => DocumentStatus::Submitted, 'validated_by' => null, 'validated_at' => null]);
        }

        if ($url) {
            LinkCheck::updateOrCreate(['institution_id' => $document->institution_id, 'document_id' => $document->id], ['url' => $url]);
        }

        return $version;
    }

    public function submit(Document $document): void
    {
        if (! in_array($document->status, [DocumentStatus::Draft, DocumentStatus::Rejected], true)) {
            throw new LogicException('Hanya bukti berstatus Draf atau Ditolak yang dapat diajukan.');
        }

        if (! $document->versions()->exists()) {
            throw new LogicException('Unggah berkas atau isi URL terlebih dahulu.');
        }

        $document->update(['status' => DocumentStatus::Submitted, 'rejection_reason' => null]);
    }

    /** Validasi four-eyes: pengunggah versi terakhir tidak boleh memvalidasi. */
    public function validate(Document $document, User $validator): void
    {
        if ($document->status !== DocumentStatus::Submitted) {
            throw new LogicException('Hanya bukti berstatus Diajukan yang dapat divalidasi.');
        }

        $uploader = $document->latestVersion?->uploaded_by ?? $document->uploaded_by;

        if ((int) $uploader === (int) $validator->id || (int) $document->uploaded_by === (int) $validator->id) {
            throw new LogicException('Prinsip four-eyes: pengunggah tidak dapat memvalidasi buktinya sendiri.');
        }

        $document->update(['status' => DocumentStatus::Valid, 'validated_by' => $validator->id, 'validated_at' => now(), 'rejection_reason' => null]);
    }

    public function reject(Document $document, User $validator, string $reason): void
    {
        if (blank($reason)) {
            throw new LogicException('Penolakan wajib disertai alasan.');
        }

        if ((int) $document->uploaded_by === (int) $validator->id) {
            throw new LogicException('Prinsip four-eyes: pengunggah tidak dapat menilai buktinya sendiri.');
        }

        $document->update(['status' => DocumentStatus::Rejected, 'rejection_reason' => $reason, 'validated_by' => $validator->id, 'validated_at' => now()]);
    }

    /** Tandai kedaluwarsa otomatis; dijalankan terjadwal dan saat dasbor dibuka. */
    public function expireDue(?int $institutionId = null): int
    {
        return Document::withoutGlobalScopes()
            ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
            ->whereNotNull('valid_until')->whereDate('valid_until', '<', today())
            ->where('status', '!=', DocumentStatus::Expired->value)
            ->get()->each(fn (Document $d) => $d->update(['status' => DocumentStatus::Expired]))->count();
    }

    /** Daftarkan hyperlink di narasi LED ke pemeriksa tautan. */
    public function registerLedLinks(LedSection $section): void
    {
        preg_match_all('~https?://[^\s<>"\)\]]+~i', (string) $section->body, $m);
        $urls = array_unique($m[0]);

        LinkCheck::withoutGlobalScopes()->where('led_section_id', $section->id)->whereNotIn('url', $urls ?: ['-'])->delete();

        foreach ($urls as $url) {
            LinkCheck::withoutGlobalScopes()->firstOrCreate(['institution_id' => $section->institution_id, 'led_section_id' => $section->id, 'url' => $url]);
        }
    }

    /** Periksa satu tautan (HEAD lalu GET bila perlu). */
    public function check(LinkCheck $check): LinkCheck
    {
        try {
            $response = Http::timeout(10)->withOptions(['allow_redirects' => true])->head($check->url);

            if (in_array($response->status(), [403, 405, 501], true)) {
                $response = Http::timeout(10)->get($check->url);
            }

            $ok = $response->status() < 400;
            $check->fill(['last_status' => $response->status(), 'is_ok' => $ok, 'last_error' => $ok ? null : 'HTTP '.$response->status()]);
        } catch (Throwable $e) {
            $check->fill(['last_status' => 0, 'is_ok' => false, 'last_error' => Str::limit($e->getMessage(), 180)]);
        }

        $check->failures = $check->is_ok ? 0 : $check->failures + 1;
        $check->last_checked_at = now();
        $check->save();

        return $check;
    }

    /** @return array{checked:int, broken:int} */
    public function checkAll(?int $institutionId = null): array
    {
        $checks = LinkCheck::withoutGlobalScopes()->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))->get();
        $broken = 0;

        foreach ($checks as $check) {
            $broken += $this->check($check)->is_ok ? 0 : 1;
        }

        return ['checked' => $checks->count(), 'broken' => $broken];
    }

    public function logDownload(DocumentVersion $version): void
    {
        $version->loadMissing('document');

        if ($version->document?->confidentiality?->value === 'rahasia') {
            AuditLog::record('unduh_rahasia', $version->document, ['versi' => $version->version]);
        }
    }
}
