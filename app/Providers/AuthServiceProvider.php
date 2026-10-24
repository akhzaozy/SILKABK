<?php

namespace App\Providers;

use App\Policies\OwnershipPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        /**
         * Super Admin melewati semua Gate check secara otomatis.
         * Sehingga tidak perlu menambahkan pengecekan super_admin
         * di setiap policy/gate secara manual.
         */
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
        });

        /**
         * Definisikan Gate untuk ownership checks.
         * Digunakan di controller: $this->authorize('view-schedule', $schedule);
         */
        $policy = new OwnershipPolicy();

        Gate::define('view-patient', fn ($user, $patient) =>
            $policy->viewPatient($user, $patient)
        );

        Gate::define('view-schedule', fn ($user, $schedule) =>
            $policy->viewSchedule($user, $schedule)
        );

        Gate::define('modify-schedule', fn ($user, $schedule) =>
            $policy->modifySchedule($user, $schedule)
        );

        Gate::define('view-payment', fn ($user, $payment) =>
            $policy->viewPayment($user, $payment)
        );

        Gate::define('view-registration', fn ($user, $registration) =>
            $policy->viewRegistration($user, $registration)
        );

        Gate::define('view-medical-record', fn ($user, $record) =>
            $policy->viewMedicalRecord($user, $record)
        );
    }
}