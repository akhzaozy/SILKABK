<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Buat patient_id nullable agar bisa membuat slot kosong (belum ada pasien)
            $table->foreignId('patient_id')->nullable()->change();

            // Buat therapist_id nullable (slot bisa untuk dokter atau psikolog)
            $table->foreignId('therapist_id')->nullable()->change();

            // Tambah relasi ke dokter dan psikolog
            $table->foreignId('doctor_id')->nullable()->after('therapist_id')->constrained('doctors')->nullOnDelete();
            $table->foreignId('psychologist_id')->nullable()->after('doctor_id')->constrained('psychologists')->nullOnDelete();

            // Tipe tenaga profesional: terapis | dokter | psikolog
            $table->string('professional_type', 20)->default('terapis')->after('psychologist_id');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->dropForeign(['psychologist_id']);
            $table->dropColumn(['doctor_id', 'psychologist_id', 'professional_type']);
            $table->foreignId('patient_id')->nullable(false)->change();
            $table->foreignId('therapist_id')->nullable(false)->change();
        });
    }
};
