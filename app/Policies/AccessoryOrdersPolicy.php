<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AccessoryOrders;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccessoryOrdersPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AccessoryOrders');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AccessoryOrders');
    }

    public function update(AuthUser $authUser, AccessoryOrders $accessoryOrders): bool
    {
        return $authUser->can('Update:AccessoryOrders');
    }

    public function delete(AuthUser $authUser, AccessoryOrders $accessoryOrders): bool
    {
        return $authUser->can('Delete:AccessoryOrders');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AccessoryOrders');
    }

}