<?php

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:User');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:User');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:User');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:User');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:User');
    }

    public function showAllProducts(AuthUser $authUser): bool
    {
        return $authUser->can('ShowAllProducts:User');
    }

    public function canAccessPanel(AuthUser $authUser): bool
    {
        return $authUser->can('CanAccessPanel:User');
    }

    public function canSendSms(AuthUser $authUser): bool
    {
        return $authUser->can('CanSendSms:User');
    }

}