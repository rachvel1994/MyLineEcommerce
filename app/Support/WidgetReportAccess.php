<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;

final class WidgetReportAccess
{
    public const ROLE_ID = 1;

    public static function allowed(?User $user): bool
    {
        return $user !== null
            && $user->roles()->whereKey(self::ROLE_ID)->exists();
    }
}
