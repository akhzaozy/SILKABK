<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Salin data dari kolom Indonesia ke kolom English jika masih kosong
        DB::statement("UPDATE therapy_types SET name = nama_terapi WHERE name IS NULL AND nama_terapi IS NOT NULL");
        DB::statement("UPDATE therapy_types SET description = deskripsi WHERE description IS NULL AND deskripsi IS NOT NULL");

        Schema::table('therapy_types', function (Blueprint $table) {
            // Tambah kolom harga per jam
            $table->decimal('price_per_hour', 12, 2)->default(0)->after('description');
        });

        // Salin harga_per_sesi → price_per_hour (dianggap sudah per jam)
        DB::statement("UPDATE therapy_types SET price_per_hour = harga_per_sesi WHERE harga_per_sesi > 0");

        Schema::table('therapy_types', function (Blueprint $table) {
            // Hapus kolom duplikat Indonesia dan duration (diganti per jam)
            $table->dropColumn(['nama_terapi', 'deskripsi', 'durasi_menit', 'harga_per_sesi', 'duration_minutes']);
        });

        // Pastikan name tidak null
        DB::statement("UPDATE therapy_types SET name = 'Tanpa Nama' WHERE name IS NULL OR name = ''");
        Schema::table('therapy_types', function (Blueprint $table) {
            $table->string('name', 100)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('therapy_types', function (Blueprint $table) {
            $table->string('nama_terapi', 100)->nullable();
            $table->text('deskripsi')->nullable();
            $table->integer('durasi_menit')->default(60);
            $table->decimal('harga_per_sesi', 12, 2)->default(0);
            $table->integer('duration_minutes')->default(60);
            $table->dropColumn('price_per_hour');
        });
    }
};
