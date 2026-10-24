<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            // Buat doctor_id nullable agar rekam medis bisa dibuat oleh psikolog
            $table->foreignId('doctor_id')->nullable()->change();

            // Tambah kolom psychologist_id (nullable)
            $table->foreignId('psychologist_id')
                ->nullable()
                ->after('doctor_id')
                ->constrained('psychologists')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropForeign(['psychologist_id']);
            $table->dropColumn('psychologist_id');
            $table->foreignId('doctor_id')->nullable(false)->change();
        });
    }
};
