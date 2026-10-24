<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Dataset kinerja (M06), rekonsiliasi (M07), dan survei/tracer (M11). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecturers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_program_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nidn', 20)->nullable();
            $table->string('name');
            $table->string('employment', 12)->default('tetap');
            $table->string('highest_degree', 5)->default('S2');
            $table->string('academic_rank', 5)->default('none');
            $table->boolean('is_certified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['institution_id', 'nidn']);
        });

        Schema::create('program_year_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_program_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('applicants')->default(0);
            $table->unsignedInteger('new_students')->default(0);
            $table->unsignedInteger('active_students')->default(0);
            $table->unsignedInteger('graduates')->default(0);
            $table->unsignedInteger('students_outside_10sks')->default(0);
            $table->decimal('avg_study_months', 5, 1)->nullable();
            $table->decimal('avg_gpa', 4, 2)->nullable();
            $table->timestamps();
            $table->unique(['study_program_id', 'year']);
        });

        Schema::create('student_cohorts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_program_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('intake_year');
            $table->unsignedInteger('intake_count');
            $table->unsignedInteger('graduated_on_time')->default(0);
            $table->unsignedInteger('graduated_within_double')->default(0);
            $table->unsignedInteger('still_active')->default(0);
            $table->unsignedInteger('dropped_out')->default(0);
            $table->timestamps();
            $table->unique(['study_program_id', 'intake_year']);
        });

        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_program_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('title');
            $table->string('level', 15);
            $table->string('rank', 10);
            $table->string('kind', 15)->default('akademik');
            $table->unsignedSmallInteger('student_count')->default(1);
            $table->timestamps();
        });

        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lecturer_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('title', 500);
            $table->string('type', 30);
            $table->string('category', 12)->default('penelitian');
            $table->string('venue')->nullable();
            $table->unsignedInteger('citations')->default(0);
            $table->string('url', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('ip_rights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lecturer_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('title');
            $table->string('ip_type', 20);
            $table->string('number')->nullable();
            $table->boolean('is_adopted')->default(false);
            $table->string('adopter')->nullable();
            $table->timestamps();
        });

        Schema::create('recognitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lecturer_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('title');
            $table->string('kind', 30);
            $table->string('level', 15);
            $table->timestamps();
        });

        Schema::create('research_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('category', 12)->default('penelitian');
            $table->string('source', 15);
            $table->string('title');
            $table->decimal('amount', 16, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('financial_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('auditor');
            $table->string('opinion', 5);
            $table->date('report_date')->nullable();
            $table->unsignedBigInteger('document_id')->nullable();
            $table->timestamps();
        });

        Schema::create('metric_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accreditation_project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('metric_code', 20);
            $table->foreignId('study_program_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->decimal('value', 14, 4)->nullable();
            $table->json('inputs')->nullable();
            $table->string('source', 12)->default('internal');
            $table->string('status', 12)->default('draft');
            $table->foreignId('computed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('data_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accreditation_project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('kind', 12)->default('ts');
            $table->json('payload');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accreditation_project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('metric_code', 30);
            $table->string('scope_label')->nullable();
            $table->decimal('internal_value', 14, 4)->nullable();
            $table->decimal('pddikti_value', 14, 4)->nullable();
            $table->decimal('difference', 14, 4)->nullable();
            $table->decimal('difference_pct', 8, 2)->nullable();
            $table->decimal('tolerance_pct', 6, 2)->default(2);
            $table->string('severity', 12)->default('rendah');
            $table->string('state', 15)->default('open');
            $table->foreignId('owner_unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('fix_document_id')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('stakeholder', 15);
            $table->string('title');
            $table->unsignedInteger('population')->default(0);
            $table->unsignedInteger('respondents')->default(0);
            $table->unsignedInteger('sb')->default(0);
            $table->unsignedInteger('b')->default(0);
            $table->unsignedInteger('c')->default(0);
            $table->unsignedInteger('kb')->default(0);
            $table->text('follow_up')->nullable();
            $table->string('public_token', 40)->nullable()->unique();
            $table->boolean('is_open')->default(false);
            $table->timestamps();
        });

        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->json('answers');
            $table->text('comment')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('tracer_studies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('graduation_year');
            $table->unsignedInteger('graduates');
            $table->unsignedInteger('respondents');
            $table->unsignedInteger('employed')->default(0);
            $table->unsignedInteger('further_study')->default(0);
            $table->unsignedInteger('entrepreneur')->default(0);
            $table->unsignedInteger('within_one_year')->default(0);
            $table->decimal('avg_wait_months', 4, 1)->nullable();
            $table->unsignedInteger('global_work')->default(0);
            $table->unsignedInteger('certified')->default(0);
            $table->timestamps();
            $table->unique(['institution_id', 'graduation_year']);
        });

        Schema::create('employer_satisfactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('aspect', 30);
            $table->unsignedInteger('sb')->default(0);
            $table->unsignedInteger('b')->default(0);
            $table->unsignedInteger('c')->default(0);
            $table->unsignedInteger('kb')->default(0);
            $table->text('follow_up')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['employer_satisfactions', 'tracer_studies', 'survey_responses', 'surveys', 'reconciliations', 'data_snapshots', 'metric_values', 'financial_audits', 'research_grants', 'recognitions', 'ip_rights', 'publications', 'achievements', 'student_cohorts', 'program_year_stats', 'lecturers'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
