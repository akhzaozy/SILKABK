<?php

namespace App\Policies;

use App\Models\MedicalRecord;
use App\Models\User;

class MedicalRecordPolicy
{
    public function view(User $user, MedicalRecord $record): bool
    {
        return ($user->doctor?->id && $record->doctor_id === $user->doctor->id)
            || ($user->psychologist?->id && $record->psychologist_id === $user->psychologist->id);
    }

    public function update(User $user, MedicalRecord $record): bool
    {
        return $this->view($user, $record);
    }
}
