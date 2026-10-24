<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Link ke slot jadwal yang dipilih orang tua
            $table->foreignId('schedule_id')->nullable()->after('therapy_type_id')->constrained('schedules')->nullOnDelete();

            // Durasi yang dipilih orang tua (dalam jam, mis: 1, 1.5, 2)
            $table->decimal('duration_hours', 4, 1)->default(1.0)->after('schedule_id');

            // existing = pasien lama, new = pasien baru
            $table->string('patient_type', 10)->default('existing')->after('duration_hours');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropColumn(['schedule_id', 'duration_hours', 'patient_type']);
        });
    }
};
