<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accreditation_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instrument_variant_id')->constrained();
            $table->foreignId('ts_period_id')->nullable()->constrained('academic_periods')->nullOnDelete();
            $table->string('name');
            $table->string('track', 12)->default('accredited');
            $table->date('target_date')->nullable();
            $table->string('state', 20)->default('draft');
            $table->timestamp('track_locked_at')->nullable();
            $table->foreignId('track_locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('frozen_at')->nullable();
            $table->foreignId('frozen_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('state_note')->nullable();
            $table->unsignedSmallInteger('word_limit')->default(1500);
            $table->json('aki_weights')->nullable();
            $table->decimal('aki', 5, 2)->nullable();
            $table->json('aki_components')->nullable();
            $table->decimal('projection_accredited', 5, 2)->nullable();
            $table->decimal('projection_excellent', 5, 2)->nullable();
            $table->timestamp('computed_at')->nullable();
            $table->date('submitted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('eligibility_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accreditation_project_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_met')->default(false);
            $table->unsignedBigInteger('document_id')->nullable();
            $table->text('note')->nullable();
            $table->unsignedTinyInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('work_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accreditation_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sub_indicator_id')->constrained();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('second_pic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->string('state', 20)->default('not_assessed');
            $table->boolean('is_blocked')->default(false);
            $table->string('color', 10)->default('gray');
            $table->string('color_reason')->nullable();
            $table->text('current_condition')->nullable();
            $table->timestamps();
            $table->unique(['accreditation_project_id', 'sub_indicator_id']);
        });

        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kind', 12);
            $table->string('track', 12)->default('accredited');
            $table->boolean('is_met');
            $table->text('note')->nullable();
            $table->timestamp('assessed_at')->nullable();
            $table->timestamps();
            $table->unique(['work_item_id', 'kind', 'track']);
        });

        Schema::create('gap_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('keyword_id')->nullable()->constrained()->nullOnDelete();
            $table->string('keyword_text')->nullable();
            $table->string('status', 12)->default('belum');
            $table->text('implementation_note')->nullable();
            $table->text('rtl')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('code', 30)->nullable();
            $table->string('title');
            $table->string('doc_type', 30);
            $table->string('number')->nullable();
            $table->date('doc_date')->nullable();
            $table->string('approved_by')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('confidentiality', 12)->default('internal');
            $table->boolean('is_public')->default(false);
            $table->string('public_slug')->nullable();
            $table->string('status', 12)->default('draft');
            $table->text('description')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->unique(['institution_id', 'public_slug']);
        });

        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('version');
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime', 100)->nullable();
            $table->string('url', 1000)->nullable();
            $table->char('sha256', 64)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['document_id', 'version']);
        });

        Schema::create('evidence_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('keyword_id')->nullable()->constrained()->nullOnDelete();
            $table->string('relevance_note')->nullable();
            $table->timestamps();
        });

        Schema::create('link_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('led_section_id')->nullable();
            $table->string('url', 1000);
            $table->unsignedSmallInteger('last_status')->nullable();
            $table->boolean('is_ok')->default(true);
            $table->unsignedSmallInteger('failures')->default(0);
            $table->string('last_error')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('led_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_item_id')->unique()->constrained()->cascadeOnDelete();
            $table->longText('body')->nullable();
            $table->unsignedInteger('word_count')->default(0);
            $table->string('state', 20)->default('draft');
            $table->decimal('keyword_coverage', 5, 2)->default(0);
            $table->json('missing_keywords')->nullable();
            $table->text('review_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('ratified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ratified_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('led_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('led_section_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('revision');
            $table->longText('body')->nullable();
            $table->unsignedInteger('word_count')->default(0);
            $table->string('state', 20)->nullable();
            $table->string('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('led_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('led_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('quote')->nullable();
            $table->text('body');
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();
        });

        Schema::create('led_citations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('led_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('document_version_id')->nullable()->constrained()->nullOnDelete();
            $table->string('metric_code', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('readiness_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accreditation_project_id')->constrained()->cascadeOnDelete();
            $table->date('taken_on');
            $table->decimal('aki', 5, 2);
            $table->json('components')->nullable();
            $table->decimal('projection_accredited', 5, 2)->nullable();
            $table->decimal('projection_excellent', 5, 2)->nullable();
            $table->unsignedSmallInteger('prerequisites_failed')->default(0);
            $table->timestamps();
            $table->unique(['accreditation_project_id', 'taken_on']);
        });
    }

    public function down(): void
    {
        foreach (['readiness_snapshots', 'led_citations', 'led_comments', 'led_revisions', 'led_sections', 'link_checks', 'evidence_links', 'document_versions', 'documents', 'gap_items', 'assessments', 'work_items', 'eligibility_items', 'accreditation_projects'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
