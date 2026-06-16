<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ExpenseType;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExpenseTypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ExpenseType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ExpenseType');
    }

    public function update(AuthUser $authUser, ExpenseType $expenseType): bool
    {
        return $authUser->can('Update:ExpenseType');
    }

    public function delete(AuthUser $authUser, ExpenseType $expenseType): bool
    {
        return $authUser->can('Delete:ExpenseType');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ExpenseType');
    }

}