<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('therapists', function (Blueprint $table) {
            $table->foreignId('therapy_type_id')
                ->nullable()
                ->after('specialization')
                ->constrained('therapy_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('therapists', function (Blueprint $table) {
            $table->dropForeign(['therapy_type_id']);
            $table->dropColumn('therapy_type_id');
        });
    }
};
