<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors');
            $table->date('visit_date');
            $table->text('chief_complaint');
            $table->text('physical_examination')->nullable();
            $table->text('diagnosis');
            $table->string('icd_code', 20)->nullable();
            $table->text('treatment_plan')->nullable();
            $table->jsonb('kpsp_result')->nullable(); // PostgreSQL jsonb
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
