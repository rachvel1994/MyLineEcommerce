<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ProductModel;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductModelPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProductModel');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProductModel');
    }

    public function update(AuthUser $authUser, ProductModel $productModel): bool
    {
        return $authUser->can('Update:ProductModel');
    }

    public function delete(AuthUser $authUser, ProductModel $productModel): bool
    {
        return $authUser->can('Delete:ProductModel');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ProductModel');
    }

}