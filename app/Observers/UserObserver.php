<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function creating(User $user): void
    {
        $helperData = $user->mobile ?? $user->id_number;
        if (empty($user->email)) {
            $user->email = $helperData;
            $user->password = $helperData;
        }
    }
}
