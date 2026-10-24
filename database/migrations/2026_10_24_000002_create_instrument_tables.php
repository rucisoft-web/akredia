<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Pustaka Instrumen: instrumen disimpan sebagai data, dipakai bersama semua institusi. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instrument_versions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('regulation')->nullable();
            $table->date('effective_at')->nullable();
            $table->date('revoked_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('instrument_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_version_id')->constrained()->cascadeOnDelete();
            $table->string('code', 10);
            $table->string('name');
            $table->string('institution_type', 20);
            $table->decimal('threshold_accredited', 5, 2)->default(80);
            $table->decimal('threshold_excellent', 5, 2)->default(80);
            $table->unsignedTinyInteger('score_met')->default(2);
            $table->string('source_note')->nullable();
            $table->timestamps();
            $table->unique(['instrument_version_id', 'code']);
        });

        Schema::create('criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_version_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('number');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('indicator_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criterion_id')->constrained('criteria')->cascadeOnDelete();
            $table->string('code', 10);
            $table->string('name');
            $table->unsignedTinyInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('criterion_id')->constrained('criteria')->cascadeOnDelete();
            $table->foreignId('indicator_group_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('number');
            $table->string('quality_target', 10);
            $table->text('statement');
            $table->text('evidence_hint')->nullable();
            $table->string('pic_suggestion')->nullable();
            $table->string('legacy_criteria')->nullable();
            $table->timestamps();
            $table->unique(['instrument_version_id', 'number']);
        });

        Schema::create('sub_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicator_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instrument_variant_id')->constrained()->cascadeOnDelete();
            $table->string('code', 10);
            $table->text('statement');
            $table->text('assessment_aspects')->nullable();
            $table->text('data_sources')->nullable();
            $table->decimal('weight', 6, 2)->default(1);
            $table->boolean('is_prerequisite_accredited')->default(false);
            $table->boolean('is_prerequisite_excellent')->default(false);
            $table->string('new_pt_rule')->nullable();
            $table->string('metric_code', 20)->nullable();
            $table->string('lkpt_table')->nullable();
            $table->text('guidance')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
            $table->unique(['instrument_variant_id', 'code']);
        });

        Schema::create('descriptors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_indicator_id')->constrained()->cascadeOnDelete();
            $table->string('track', 12);
            $table->text('text');
            $table->string('threshold_expression')->nullable();
            $table->timestamps();
            $table->unique(['sub_indicator_id', 'track']);
        });

        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('descriptor_id')->constrained()->cascadeOnDelete();
            $table->string('text');
            $table->unsignedTinyInteger('sequence')->default(1);
            $table->timestamps();
        });

        // Metrik kuantitatif: rumus sebagai data, dievaluasi MetricEngine.
        Schema::create('metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 20);
            $table->string('name');
            $table->string('formula');
            $table->string('formula_text')->nullable();
            $table->string('unit', 10)->default('%');
            $table->string('indicator_ref', 10)->nullable();
            $table->decimal('accredited_min', 10, 3)->nullable();
            $table->decimal('accredited_max', 10, 3)->nullable();
            $table->decimal('excellent_min', 10, 3)->nullable();
            $table->decimal('excellent_max', 10, 3)->nullable();
            $table->decimal('tolerance_pct', 6, 2)->default(2);
            $table->boolean('is_prerequisite')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['metrics', 'keywords', 'descriptors', 'sub_indicators', 'indicators', 'indicator_groups', 'criteria', 'instrument_variants', 'instrument_versions'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
