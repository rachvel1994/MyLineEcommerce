<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CashDrawer;
use Illuminate\Auth\Access\HandlesAuthorization;

class CashDrawerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CashDrawer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CashDrawer');
    }

    public function update(AuthUser $authUser, CashDrawer $cashDrawer): bool
    {
        return $authUser->can('Update:CashDrawer');
    }

    public function delete(AuthUser $authUser, CashDrawer $cashDrawer): bool
    {
        return $authUser->can('Delete:CashDrawer');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CashDrawer');
    }

    public function canSetOpeningBalance(AuthUser $authUser, CashDrawer $cashDrawer): bool
    {
        return $authUser->can('CanSetOpeningBalance:CashDrawer');
    }

    public function canWithdraw(AuthUser $authUser, CashDrawer $cashDrawer): bool
    {
        return $authUser->can('CanWithdraw:CashDrawer');
    }

    public function canDeposit(AuthUser $authUser, CashDrawer $cashDrawer): bool
    {
        return $authUser->can('CanDeposit:CashDrawer');
    }

    public function canClose(AuthUser $authUser, CashDrawer $cashDrawer): bool
    {
        return $authUser->can('CanClose:CashDrawer');
    }

    public function canReopen(AuthUser $authUser, CashDrawer $cashDrawer): bool
    {
        return $authUser->can('CanReopen:CashDrawer');
    }

}