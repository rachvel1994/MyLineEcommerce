<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Accessory;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccessoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Accessory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Accessory');
    }

    public function update(AuthUser $authUser, Accessory $accessory): bool
    {
        return $authUser->can('Update:Accessory');
    }

    public function delete(AuthUser $authUser, Accessory $accessory): bool
    {
        return $authUser->can('Delete:Accessory');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Accessory');
    }

    public function viewAnyConsignmentAccessories(AuthUser $authUser, Accessory $accessory): bool
    {
        return $authUser->can('ViewAnyConsignmentAccessories:Accessory');
    }

    public function viewConsignmentAccessories(AuthUser $authUser, Accessory $accessory): bool
    {
        return $authUser->can('ViewConsignmentAccessories:Accessory');
    }

    public function createConsignmentAccessories(AuthUser $authUser, Accessory $accessory): bool
    {
        return $authUser->can('CreateConsignmentAccessories:Accessory');
    }

    public function updateConsignmentAccessories(AuthUser $authUser, Accessory $accessory): bool
    {
        return $authUser->can('UpdateConsignmentAccessories:Accessory');
    }

    public function deleteConsignmentAccessories(AuthUser $authUser, Accessory $accessory): bool
    {
        return $authUser->can('DeleteConsignmentAccessories:Accessory');
    }

    public function attachConsignmentAccessories(AuthUser $authUser, Accessory $accessory): bool
    {
        return $authUser->can('AttachConsignmentAccessories:Accessory');
    }

    public function detachConsignmentAccessories(AuthUser $authUser, Accessory $accessory): bool
    {
        return $authUser->can('DetachConsignmentAccessories:Accessory');
    }

}