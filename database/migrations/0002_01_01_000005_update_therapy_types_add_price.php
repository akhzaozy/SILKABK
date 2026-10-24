<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('therapy_types', function (Blueprint $table) {
            // Ubah kolom lama jadi nullable
            $table->string('name', 100)->nullable()->change();
            $table->text('description')->nullable()->change();
            
            // Tambah kolom baru dengan nama Indonesia
            $table->string('nama_terapi', 100)->nullable()->after('id');
            $table->text('deskripsi')->nullable()->after('nama_terapi');
            $table->integer('durasi_menit')->default(60)->after('deskripsi');
            $table->decimal('harga_per_sesi', 12, 2)->default(0)->after('durasi_menit');
        });
    }

    public function down(): void
    {
        Schema::table('therapy_types', function (Blueprint $table) {
            $table->string('name', 100)->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
            $table->dropColumn(['nama_terapi', 'deskripsi', 'durasi_menit', 'harga_per_sesi']);
        });
    }
};
