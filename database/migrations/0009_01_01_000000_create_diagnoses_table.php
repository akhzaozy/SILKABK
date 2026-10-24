<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors');
            $table->date('diagnosis_date');
            $table->text('symptoms');           // gejala yang dialami
            $table->string('diagnosis_name');   // nama penyakit
            $table->string('icd_code', 20)->nullable(); // kode ICD-10
            $table->text('treatment')->nullable();       // penanganan / resep
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnoses');
    }
};
