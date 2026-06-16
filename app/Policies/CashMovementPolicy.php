<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CashMovement;
use Illuminate\Auth\Access\HandlesAuthorization;

class CashMovementPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CashMovement');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CashMovement');
    }

    public function update(AuthUser $authUser, CashMovement $cashMovement): bool
    {
        return $authUser->can('Update:CashMovement');
    }

    public function delete(AuthUser $authUser, CashMovement $cashMovement): bool
    {
        return $authUser->can('Delete:CashMovement');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CashMovement');
    }

}