<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('password');
            $table->boolean('is_platform_admin')->default(false)->after('is_active');
            $table->text('app_authentication_secret')->nullable();
            $table->text('app_authentication_recovery_codes')->nullable();
            $table->timestamp('last_login_at')->nullable();
        });

        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 20)->nullable();
            $table->string('type', 20)->default('pts_akademik');
            $table->string('legal_entity')->nullable();
            $table->string('sk_number')->nullable();
            $table->date('sk_date')->nullable();
            $table->unsignedSmallInteger('first_intake_year')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->text('vision')->nullable();
            $table->text('mission_focus')->nullable();
            $table->string('last_accreditation_rank')->nullable();
            $table->string('last_accreditation_sk')->nullable();
            $table->date('last_accreditation_until')->nullable();
            $table->boolean('ai_enabled')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('code', 30)->nullable();
            $table->string('name');
            $table->string('type', 20)->default('biro');
            $table->string('head_name')->nullable();
            $table->timestamps();
        });

        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('role', 20);
            $table->string('position_title')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['institution_id', 'user_id']);
        });

        Schema::create('sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('code', 40);
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('last_number')->default(0);
            $table->unique(['institution_id', 'code', 'year']);
        });

        // Log audit: hanya ditambah dari aplikasi, tidak pernah diubah.
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 30);
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->string('label')->nullable();
            $table->json('changes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['institution_id', 'created_at']);
        });

        Schema::create('study_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code_pddikti', 20)->nullable();
            $table->string('name');
            $table->string('level', 10);
            $table->unsignedSmallInteger('opened_year')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('program_accreditations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_program_id')->constrained()->cascadeOnDelete();
            $table->string('agency', 30);
            $table->string('rank', 20);
            $table->string('sk_number')->nullable();
            $table->date('sk_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_current')->default(true);
            $table->boolean('is_international')->default(false);
            $table->unsignedBigInteger('document_id')->nullable();
            $table->timestamps();
        });

        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('name', 30);
            $table->unsignedSmallInteger('year_start');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['academic_periods', 'program_accreditations', 'study_programs', 'audit_logs', 'sequences', 'memberships', 'units', 'institutions'] as $t) {
            Schema::dropIfExists($t);
        }
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['is_active', 'is_platform_admin', 'app_authentication_secret', 'app_authentication_recovery_codes', 'last_login_at']));
    }
};
