<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Service;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServicePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Service');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Service');
    }

    public function update(AuthUser $authUser, Service $service): bool
    {
        return $authUser->can('Update:Service');
    }

    public function delete(AuthUser $authUser, Service $service): bool
    {
        return $authUser->can('Delete:Service');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Service');
    }

    public function viewCustomer(AuthUser $authUser, Service $service): bool
    {
        return $authUser->can('ViewCustomer:Service');
    }

    public function viewCreator(AuthUser $authUser, Service $service): bool
    {
        return $authUser->can('ViewCreator:Service');
    }

    public function viewPaidAmount(AuthUser $authUser, Service $service): bool
    {
        return $authUser->can('ViewPaidAmount:Service');
    }

    public function viewSubtotal(AuthUser $authUser, Service $service): bool
    {
        return $authUser->can('ViewSubtotal:Service');
    }

    public function viewDebt(AuthUser $authUser, Service $service): bool
    {
        return $authUser->can('ViewDebt:Service');
    }

    public function viewIsPaid(AuthUser $authUser, Service $service): bool
    {
        return $authUser->can('ViewIsPaid:Service');
    }

    public function viewRepairHistories(AuthUser $authUser, Service $service): bool
    {
        return $authUser->can('ViewRepairHistories:Service');
    }

    public function canPay(AuthUser $authUser, Service $service): bool
    {
        return $authUser->can('CanPay:Service');
    }

    public function canPayAll(AuthUser $authUser, Service $service): bool
    {
        return $authUser->can('CanPayAll:Service');
    }

}