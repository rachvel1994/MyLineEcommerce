<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Product;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Product');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Product');
    }

    public function update(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('Update:Product');
    }

    public function delete(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('Delete:Product');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Product');
    }

    public function view(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('View:Product');
    }

    public function canViewSku(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewSku:Product');
    }

    public function canViewOrderId(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewOrderId:Product');
    }

    public function canViewPrice(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewPrice:Product');
    }

    public function canViewIsConsigned(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewIsConsigned:Product');
    }

    public function canViewRetailPrice(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewRetailPrice:Product');
    }

    public function canViewSalePrice(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewSalePrice:Product');
    }

    public function canViewUser(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewUser:Product');
    }

    public function canViewMobile(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewMobile:Product');
    }

    public function canViewPdf(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewPdf:Product');
    }

    public function canViewNeedReset(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewNeedReset:Product');
    }

    public function canViewCondition(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewCondition:Product');
    }

    public function canViewStatus(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewStatus:Product');
    }

    public function canViewHearAbout(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewHearAbout:Product');
    }

    public function canViewDelivery(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewDelivery:Product');
    }

    public function canCreateGuarantee(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanCreateGuarantee:Product');
    }

    public function canViewGuarantee(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewGuarantee:Product');
    }

    public function canViewComment(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewComment:Product');
    }

    public function canViewServiceComment(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewServiceComment:Product');
    }

    public function canViewModel(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewModel:Product');
    }

    public function canViewCategory(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewCategory:Product');
    }

    public function canViewBattery(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewBattery:Product');
    }

    public function canDownloadExcel(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanDownloadExcel:Product');
    }

    public function canViewColor(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewColor:Product');
    }

    public function canViewCompany(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewCompany:Product');
    }

    public function canViewStorage(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewStorage:Product');
    }

    public function canViewIsRepaired(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewIsRepaired:Product');
    }

    public function canViewShowRepairedInformation(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewShowRepairedInformation:Product');
    }

    public function viewProductStatusBulkUpdate(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('ViewProductStatusBulkUpdate:Product');
    }

    public function bulkAttachConsignmentProducts(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('BulkAttachConsignmentProducts:Product');
    }

    public function bulkAttachServiceProducts(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('BulkAttachServiceProducts:Product');
    }

    public function updateConsignmentProducts(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('UpdateConsignmentProducts:Product');
    }

    public function viewAnyConsignmentProducts(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('ViewAnyConsignmentProducts:Product');
    }

    public function viewConsignmentProducts(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('ViewConsignmentProducts:Product');
    }

    public function createConsignmentProducts(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CreateConsignmentProducts:Product');
    }

    public function deleteConsignmentProducts(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('DeleteConsignmentProducts:Product');
    }

    public function attachConsignmentProducts(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('AttachConsignmentProducts:Product');
    }

    public function detachConsignmentProducts(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('DetachConsignmentProducts:Product');
    }

    public function canViewRepairAction(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('CanViewRepairAction:Product');
    }

    public function updateServiceProducts(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('UpdateServiceProducts:Product');
    }

    public function detachServiceProducts(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('DetachServiceProducts:Product');
    }

}