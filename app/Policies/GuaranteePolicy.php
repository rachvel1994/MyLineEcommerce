<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Guarantee;
use Illuminate\Auth\Access\HandlesAuthorization;

class GuaranteePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Guarantee');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Guarantee');
    }

    public function update(AuthUser $authUser, Guarantee $guarantee): bool
    {
        return $authUser->can('Update:Guarantee');
    }

    public function delete(AuthUser $authUser, Guarantee $guarantee): bool
    {
        return $authUser->can('Delete:Guarantee');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Guarantee');
    }

}