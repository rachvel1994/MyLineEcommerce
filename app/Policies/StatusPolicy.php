<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Status;
use Illuminate\Auth\Access\HandlesAuthorization;

class StatusPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Status');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Status');
    }

    public function update(AuthUser $authUser, Status $status): bool
    {
        return $authUser->can('Update:Status');
    }

    public function delete(AuthUser $authUser, Status $status): bool
    {
        return $authUser->can('Delete:Status');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Status');
    }

}