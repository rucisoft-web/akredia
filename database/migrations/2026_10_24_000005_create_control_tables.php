<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tugas, risiko, keputusan (M12), SPMI (M10), asesmen lapangan (M13). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accreditation_project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('work_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number', 30)->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->string('priority', 10)->default('sedang');
            $table->string('status', 12)->default('todo');
            $table->string('source', 15)->default('manual');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('risks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accreditation_project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('indicator_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('probability');
            $table->unsignedTinyInteger('impact');
            $table->unsignedTinyInteger('score');
            $table->string('level', 10);
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('mitigation')->nullable();
            $table->date('target_date')->nullable();
            $table->string('state', 12)->default('open');
            $table->timestamps();
        });

        Schema::create('decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accreditation_project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('number', 30)->nullable();
            $table->string('title');
            $table->date('decision_date');
            $table->string('forum', 30)->default('rapat_pimpinan');
            $table->string('decided_by')->nullable();
            $table->text('content');
            $table->string('status', 12)->default('berlaku');
            $table->timestamps();
        });

        Schema::create('quality_standards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('code', 20);
            $table->string('name');
            $table->string('category', 12)->default('akademik');
            $table->text('statement')->nullable();
            $table->string('ppepp_stage', 15)->default('pelaksanaan');
            $table->date('approved_at')->nullable();
            $table->date('review_due')->nullable();
            $table->timestamps();
        });

        Schema::create('internal_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number', 30)->nullable();
            $table->string('name');
            $table->unsignedSmallInteger('cycle_year');
            $table->date('audit_date')->nullable();
            $table->string('lead_auditor')->nullable();
            $table->string('state', 12)->default('rencana');
            $table->text('summary')->nullable();
            $table->timestamps();
        });

        Schema::create('findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('internal_audit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quality_standard_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('indicator_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number', 30)->nullable();
            $table->string('category', 12);
            $table->text('description');
            $table->text('root_cause')->nullable();
            $table->string('state', 15)->default('open');
            $table->date('due_date')->nullable();
            $table->string('source', 15)->default('ami');
            $table->timestamps();
        });

        Schema::create('capas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('finding_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 12)->default('korektif');
            $table->text('action');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('verifier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_effective')->nullable();
            $table->text('verification_note')->nullable();
            $table->timestamps();
        });

        Schema::create('management_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->date('meeting_date');
            $table->string('title');
            $table->string('chair')->nullable();
            $table->text('minutes')->nullable();
            $table->text('outcomes')->nullable();
            $table->timestamps();
        });

        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accreditation_project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->string('mode', 10)->default('luring');
            $table->string('assessors')->nullable();
            $table->date('rehearsal_on')->nullable();
            $table->string('state', 12)->default('rencana');
            $table->string('document_room')->nullable();
            $table->text('minutes')->nullable();
            $table->timestamps();
        });

        Schema::create('visit_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_visit_id')->constrained()->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('group', 15);
            $table->string('topic');
            $table->string('room')->nullable();
            $table->string('coordinator')->nullable();
            $table->timestamps();
        });

        Schema::create('visit_interviewees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_visit_id')->constrained()->cascadeOnDelete();
            $table->string('group', 15);
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('phone', 30)->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->timestamps();
        });

        Schema::create('visit_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('indicator_id')->nullable()->constrained()->nullOnDelete();
            $table->string('group', 15);
            $table->text('question');
            $table->text('answer_points')->nullable();
            $table->string('owner')->nullable();
            $table->boolean('is_rehearsed')->default(false);
            $table->timestamps();
        });

        Schema::create('visit_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('indicator_id')->nullable()->constrained()->nullOnDelete();
            $table->text('recommendation');
            $table->string('follow_up', 10)->nullable();
            $table->unsignedBigInteger('follow_up_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['visit_recommendations', 'visit_questions', 'visit_interviewees', 'visit_sessions', 'site_visits', 'management_reviews', 'capas', 'findings', 'internal_audits', 'quality_standards', 'decisions', 'risks', 'tasks'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
