<?php

namespace App\Models;

use App\Enums\InstitutionType;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/** Perguruan tinggi (tenant). Seluruh data persiapan akreditasi berinduk ke sini. */
class Institution extends Model implements HasName
{
    protected $attributes = ['is_active' => true, 'is_demo' => false, 'type' => 'pts_akademik'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => InstitutionType::class,
            'sk_date' => 'date',
            'last_accreditation_until' => 'date',
            'is_active' => 'boolean',
            'is_demo' => 'boolean',
            'ai_enabled' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Institution $institution) {
            $base = Str::slug($institution->code ?: $institution->name) ?: 'pt';
            $slug = $base;

            for ($i = 2; static::where('slug', $slug)->exists(); $i++) {
                $slug = "{$base}-{$i}";
            }

            $institution->slug ??= $slug;
        });
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    /** Proyek berjalan (terbaru yang belum selesai), dipakai dasbor dan beranda. */
    public function activeProject(): ?AccreditationProject
    {
        return AccreditationProject::withoutGlobalScopes()
            ->where('institution_id', $this->id)
            ->orderByRaw("state = 'done'")
            ->latest('id')
            ->first();
    }

    // Relasi yang dibutuhkan Filament untuk resource bertenant (nama jamak dari model).
    public function memberships(): HasMany { return $this->hasMany(Membership::class); }
    public function units(): HasMany { return $this->hasMany(Unit::class); }
    public function studyPrograms(): HasMany { return $this->hasMany(StudyProgram::class); }
    public function programAccreditations(): HasMany { return $this->hasMany(ProgramAccreditation::class); }
    public function academicPeriods(): HasMany { return $this->hasMany(AcademicPeriod::class); }
    public function accreditationProjects(): HasMany { return $this->hasMany(AccreditationProject::class); }
    public function eligibilityItems(): HasMany { return $this->hasMany(EligibilityItem::class); }
    public function workItems(): HasMany { return $this->hasMany(WorkItem::class); }
    public function assessments(): HasMany { return $this->hasMany(Assessment::class); }
    public function gapItems(): HasMany { return $this->hasMany(GapItem::class); }
    public function documents(): HasMany { return $this->hasMany(Document::class); }
    public function documentVersions(): HasMany { return $this->hasMany(DocumentVersion::class); }
    public function evidenceLinks(): HasMany { return $this->hasMany(EvidenceLink::class); }
    public function linkChecks(): HasMany { return $this->hasMany(LinkCheck::class); }
    public function ledSections(): HasMany { return $this->hasMany(LedSection::class); }
    public function lecturers(): HasMany { return $this->hasMany(Lecturer::class); }
    public function programYearStats(): HasMany { return $this->hasMany(ProgramYearStat::class); }
    public function studentCohorts(): HasMany { return $this->hasMany(StudentCohort::class); }
    public function achievements(): HasMany { return $this->hasMany(Achievement::class); }
    public function publications(): HasMany { return $this->hasMany(Publication::class); }
    public function ipRights(): HasMany { return $this->hasMany(IpRight::class); }
    public function recognitions(): HasMany { return $this->hasMany(Recognition::class); }
    public function researchGrants(): HasMany { return $this->hasMany(ResearchGrant::class); }
    public function financialAudits(): HasMany { return $this->hasMany(FinancialAudit::class); }
    public function metricValues(): HasMany { return $this->hasMany(MetricValue::class); }
    public function dataSnapshots(): HasMany { return $this->hasMany(DataSnapshot::class); }
    public function reconciliations(): HasMany { return $this->hasMany(Reconciliation::class); }
    public function surveys(): HasMany { return $this->hasMany(Survey::class); }
    public function tracerStudies(): HasMany { return $this->hasMany(TracerStudy::class); }
    public function employerSatisfactions(): HasMany { return $this->hasMany(EmployerSatisfaction::class); }
    public function tasks(): HasMany { return $this->hasMany(Task::class); }
    public function risks(): HasMany { return $this->hasMany(Risk::class); }
    public function decisions(): HasMany { return $this->hasMany(Decision::class); }
    public function qualityStandards(): HasMany { return $this->hasMany(QualityStandard::class); }
    public function internalAudits(): HasMany { return $this->hasMany(InternalAudit::class); }
    public function findings(): HasMany { return $this->hasMany(Finding::class); }
    public function capas(): HasMany { return $this->hasMany(Capa::class); }
    public function managementReviews(): HasMany { return $this->hasMany(ManagementReview::class); }
    public function siteVisits(): HasMany { return $this->hasMany(SiteVisit::class); }
    public function visitQuestions(): HasMany { return $this->hasMany(VisitQuestion::class); }
    public function auditLogs(): HasMany { return $this->hasMany(AuditLog::class); }
}
