<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\HearAbout;
use Illuminate\Auth\Access\HandlesAuthorization;

class HearAboutPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HearAbout');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HearAbout');
    }

    public function update(AuthUser $authUser, HearAbout $hearAbout): bool
    {
        return $authUser->can('Update:HearAbout');
    }

    public function delete(AuthUser $authUser, HearAbout $hearAbout): bool
    {
        return $authUser->can('Delete:HearAbout');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:HearAbout');
    }

}