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
        Schema::create('therapy_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->foreignId('therapist_id')->constrained('therapists');
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->date('session_date');
            $table->text('activities');
            $table->text('patient_response');
            $table->text('goals_achieved')->nullable();
            $table->text('next_plan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('therapy_results');
    }
};
