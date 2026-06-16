<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Battery;
use Illuminate\Auth\Access\HandlesAuthorization;

class BatteryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Battery');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Battery');
    }

    public function update(AuthUser $authUser, Battery $battery): bool
    {
        return $authUser->can('Update:Battery');
    }

    public function delete(AuthUser $authUser, Battery $battery): bool
    {
        return $authUser->can('Delete:Battery');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Battery');
    }

}