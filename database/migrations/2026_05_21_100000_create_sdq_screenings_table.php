<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sdq_screenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // orang tua yang mengisi
            $table->jsonb('answers');                   // array 25 integer (0/1/2)
            $table->unsignedTinyInteger('emotional_score');
            $table->unsignedTinyInteger('conduct_score');
            $table->unsignedTinyInteger('hyperactivity_score');
            $table->unsignedTinyInteger('peer_score');
            $table->unsignedTinyInteger('prosocial_score');
            $table->unsignedTinyInteger('total_score');
            $table->enum('interpretation', ['normal', 'borderline', 'abnormal']);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'created_at'], 'idx_sdq_patient_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sdq_screenings');
    }
};
