<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Condition;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConditionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Condition');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Condition');
    }

    public function update(AuthUser $authUser, Condition $condition): bool
    {
        return $authUser->can('Update:Condition');
    }

    public function delete(AuthUser $authUser, Condition $condition): bool
    {
        return $authUser->can('Delete:Condition');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Condition');
    }

}