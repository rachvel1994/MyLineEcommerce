<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RepairInformation;
use Illuminate\Auth\Access\HandlesAuthorization;

class RepairInformationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RepairInformation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RepairInformation');
    }

    public function update(AuthUser $authUser, RepairInformation $repairInformation): bool
    {
        return $authUser->can('Update:RepairInformation');
    }

    public function delete(AuthUser $authUser, RepairInformation $repairInformation): bool
    {
        return $authUser->can('Delete:RepairInformation');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RepairInformation');
    }

}