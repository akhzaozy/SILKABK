<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // patients: sering di-query berdasarkan guardian_id + is_active
        Schema::table('patients', function (Blueprint $table) {
            $table->index(['guardian_id', 'is_active'], 'idx_patients_guardian_active');
        });

        // schedules: dashboard queries sering filter therapist_id + date + status
        Schema::table('schedules', function (Blueprint $table) {
            $table->index(['therapist_id', 'schedule_date', 'status'], 'idx_schedules_therapist_date_status');
            $table->index(['patient_id', 'schedule_date', 'status'],   'idx_schedules_patient_date_status');
        });

        // payments: filter patient_id + status, dan status + payment_date (admin monthly revenue)
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['patient_id', 'status'],  'idx_payments_patient_status');
            $table->index(['status', 'payment_date'], 'idx_payments_status_date');
        });

        // medical_records: filter doctor_id / psychologist_id + visit_date
        Schema::table('medical_records', function (Blueprint $table) {
            $table->index(['doctor_id', 'visit_date'],       'idx_medical_records_doctor_date');
            $table->index(['psychologist_id', 'visit_date'], 'idx_medical_records_psych_date');
        });

        // registrations: filter status (admin dashboard pending count)
        Schema::table('registrations', function (Blueprint $table) {
            $table->index('status', 'idx_registrations_status');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex('idx_patients_guardian_active');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('idx_schedules_therapist_date_status');
            $table->dropIndex('idx_schedules_patient_date_status');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_patient_status');
            $table->dropIndex('idx_payments_status_date');
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropIndex('idx_medical_records_doctor_date');
            $table->dropIndex('idx_medical_records_psych_date');
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex('idx_registrations_status');
        });
    }
};
