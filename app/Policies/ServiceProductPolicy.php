<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ServiceProduct;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceProductPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ServiceProduct');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ServiceProduct');
    }

    public function update(AuthUser $authUser, ServiceProduct $serviceProduct): bool
    {
        return $authUser->can('Update:ServiceProduct');
    }

    public function delete(AuthUser $authUser, ServiceProduct $serviceProduct): bool
    {
        return $authUser->can('Delete:ServiceProduct');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ServiceProduct');
    }

}