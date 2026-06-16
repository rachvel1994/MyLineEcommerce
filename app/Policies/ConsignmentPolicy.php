<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Consignment;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConsignmentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Consignment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Consignment');
    }

    public function update(AuthUser $authUser, Consignment $consignment): bool
    {
        return $authUser->can('Update:Consignment');
    }

    public function delete(AuthUser $authUser, Consignment $consignment): bool
    {
        return $authUser->can('Delete:Consignment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Consignment');
    }

    public function viewCustomer(AuthUser $authUser, Consignment $consignment): bool
    {
        return $authUser->can('ViewCustomer:Consignment');
    }

    public function viewCreator(AuthUser $authUser, Consignment $consignment): bool
    {
        return $authUser->can('ViewCreator:Consignment');
    }

    public function viewPaidAmount(AuthUser $authUser, Consignment $consignment): bool
    {
        return $authUser->can('ViewPaidAmount:Consignment');
    }

    public function viewSubtotal(AuthUser $authUser, Consignment $consignment): bool
    {
        return $authUser->can('ViewSubtotal:Consignment');
    }

    public function viewDebt(AuthUser $authUser, Consignment $consignment): bool
    {
        return $authUser->can('ViewDebt:Consignment');
    }

    public function viewIsPaid(AuthUser $authUser, Consignment $consignment): bool
    {
        return $authUser->can('ViewIsPaid:Consignment');
    }

    public function viewPriceChanges(AuthUser $authUser, Consignment $consignment): bool
    {
        return $authUser->can('ViewPriceChanges:Consignment');
    }

}