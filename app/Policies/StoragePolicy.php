<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Storage;
use Illuminate\Auth\Access\HandlesAuthorization;

class StoragePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Storage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Storage');
    }

    public function update(AuthUser $authUser, Storage $storage): bool
    {
        return $authUser->can('Update:Storage');
    }

    public function delete(AuthUser $authUser, Storage $storage): bool
    {
        return $authUser->can('Delete:Storage');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Storage');
    }

}