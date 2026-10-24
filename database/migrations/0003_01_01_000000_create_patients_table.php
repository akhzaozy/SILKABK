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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_id')->constrained('parent_guardians')->cascadeOnDelete();
            $table->string('medical_record_number', 20)->unique();
            $table->string('name', 100);
            $table->string('gender', 10); // L | P
            $table->date('birth_date');
            $table->string('birth_place', 100);
            $table->text('address');
            $table->text('diagnosis')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
