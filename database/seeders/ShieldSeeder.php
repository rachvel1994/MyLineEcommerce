<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $tenants = '[]';
        $users = '[]';
        $userTenantPivot = '[]';
        $rolesWithPermissions = '[{"name":"\\u10d0\\u10d3\\u10db\\u10d8\\u10dc\\u10d8\\u10e1\\u10e2\\u10e0\\u10d0\\u10e2\\u10dd\\u10e0\\u10d8","guard_name":"web","permissions":["ViewAny:Role","Create:Role","Update:Role","Delete:Role","ViewAny:Accessory","Create:Accessory","Update:Accessory","Delete:Accessory","ViewAnyConsignmentAccessories:Accessory","ViewConsignmentAccessories:Accessory","CreateConsignmentAccessories:Accessory","UpdateConsignmentAccessories:Accessory","DeleteConsignmentAccessories:Accessory","AttachConsignmentAccessories:Accessory","DetachConsignmentAccessories:Accessory","ViewAny:AccessoryOrders","Create:AccessoryOrders","Update:AccessoryOrders","Delete:AccessoryOrders","ViewAny:Battery","Create:Battery","Update:Battery","Delete:Battery","ViewAny:Branch","Create:Branch","Update:Branch","Delete:Branch","ViewAny:CashDrawer","Create:CashDrawer","Update:CashDrawer","Delete:CashDrawer","CanSetOpeningBalance:CashDrawer","CanWithdraw:CashDrawer","CanDeposit:CashDrawer","CanClose:CashDrawer","CanReopen:CashDrawer","ViewAny:CashMovement","Create:CashMovement","Update:CashMovement","Delete:CashMovement","ViewAny:Category","Create:Category","Update:Category","Delete:Category","ViewAny:Color","Create:Color","Update:Color","Delete:Color","ViewAny:Condition","Create:Condition","Update:Condition","Delete:Condition","ViewAny:Consignment","Create:Consignment","Update:Consignment","Delete:Consignment","ViewCustomer:Consignment","ViewCreator:Consignment","ViewPaidAmount:Consignment","ViewSubtotal:Consignment","ViewDebt:Consignment","ViewIsPaid:Consignment","ViewPriceChanges:Consignment","ViewAny:Delivery","Create:Delivery","Update:Delivery","Delete:Delivery","ViewAny:ExpenseType","Create:ExpenseType","Update:ExpenseType","Delete:ExpenseType","ViewAny:Expense","Create:Expense","Update:Expense","Delete:Expense","ViewAny:Guarantee","Create:Guarantee","Update:Guarantee","Delete:Guarantee","ViewAny:HearAbout","Create:HearAbout","Update:HearAbout","Delete:HearAbout","ViewAny:Payment","Create:Payment","Update:Payment","Delete:Payment","ViewAny:ProductModel","Create:ProductModel","Update:ProductModel","Delete:ProductModel","ViewAny:Product","Create:Product","Update:Product","Delete:Product","CanViewSku:Product","CanViewOrderId:Product","CanViewPrice:Product","CanViewIsConsigned:Product","CanViewRetailPrice:Product","CanViewSalePrice:Product","CanViewUser:Product","CanViewMobile:Product","CanViewPdf:Product","CanViewNeedReset:Product","CanViewCondition:Product","CanViewStatus:Product","CanViewHearAbout:Product","CanViewDelivery:Product","CanCreateGuarantee:Product","CanViewGuarantee:Product","CanViewComment:Product","CanViewModel:Product","CanViewCategory:Product","CanViewBattery:Product","CanDownloadExcel:Product","CanViewColor:Product","CanViewStorage:Product","CanViewIsRepaired:Product","CanViewShowRepairedInformation:Product","ViewProductStatusBulkUpdate:Product","BulkAttachConsignmentProducts:Product","UpdateConsignmentProducts:Product","ViewAnyConsignmentProducts:Product","ViewConsignmentProducts:Product","CreateConsignmentProducts:Product","DeleteConsignmentProducts:Product","AttachConsignmentProducts:Product","DetachConsignmentProducts:Product","CanViewRepairAction:Product","ViewAny:RepairInformation","Create:RepairInformation","Update:RepairInformation","Delete:RepairInformation","ViewAny:Status","Create:Status","Update:Status","Delete:Status","ViewAny:Storage","Create:Storage","Update:Storage","Delete:Storage","ViewAny:User","Create:User","Update:User","Delete:User","ShowAllProducts:User","CanAccessPanel:User","CanSendSms:User","View:AnalyticsDashboard","View:CashDrawerWidget","View:ExpenseStatsWidget","View:LatestProductsWidget","View:ProductChartWidget","View:ProductListWidget","View:ProductPageWidget","View:ProductStatsWidget","View:RepairHistoryWidget","View:TopModelsWidget","View:TopUsersChartWidget","ViewAny:Service","Create:Service","Update:Service","Delete:Service","ViewCustomer:Service","ViewCreator:Service","ViewPaidAmount:Service","ViewSubtotal:Service","ViewDebt:Service","ViewIsPaid:Service","ViewRepairHistories:Service","BulkAttachServiceProducts:Product","UpdateServiceProducts:Product","DetachServiceProducts:Product","CanPay:Service","CanPayAll:Service","CanViewServiceComment:Product","View:Product","View:SalesSourcesChartWidget","View:SellerPerformanceWidget","View:LowStockByModelChart","CanViewCompany:Product","View:DeadStockWidget","ViewAny:ServiceProduct","Create:ServiceProduct","Update:ServiceProduct","Delete:ServiceProduct","DeleteAny:Accessory","DeleteAny:AccessoryOrders","DeleteAny:Battery","DeleteAny:Branch","DeleteAny:CashDrawer","DeleteAny:CashMovement","DeleteAny:Category","DeleteAny:Color","DeleteAny:Condition","DeleteAny:Consignment","DeleteAny:Delivery","DeleteAny:ExpenseType","DeleteAny:Expense","DeleteAny:Guarantee","DeleteAny:HearAbout","DeleteAny:Payment","DeleteAny:ProductModel","DeleteAny:Product","DeleteAny:RepairInformation","DeleteAny:Role","DeleteAny:ServiceProduct","DeleteAny:Service","DeleteAny:Status","DeleteAny:Storage","DeleteAny:User","View:CurrentMonthPaymentsWidget","View:SellerMonthlyStatsWidget"]},{"name":"\\u10d9\\u10dd\\u10db\\u10de\\u10d0\\u10dc\\u10d8\\u10d0","guard_name":"web","permissions":[]},{"name":"\\u10db\\u10dd\\u10db\\u10ee\\u10db\\u10d0\\u10e0\\u10d4\\u10d1\\u10d4\\u10da\\u10d8","guard_name":"web","permissions":[]},{"name":"\\u10e2\\u10d4\\u10e5\\u10dc\\u10d8\\u10d9\\u10dd\\u10e1\\u10d8","guard_name":"web","permissions":["CanAccessPanel:User","ViewAny:Service","Update:Service","ViewCreator:Service","ViewPaidAmount:Service","ViewSubtotal:Service","ViewDebt:Service","ViewIsPaid:Service","ViewRepairHistories:Service","ViewAny:ServiceProduct","Create:ServiceProduct","Update:ServiceProduct","Delete:ServiceProduct"]},{"name":"\\u10e5\\u10dd\\u10da\\u10ea\\u10d4\\u10dc\\u10e2\\u10e0\\u10d8","guard_name":"web","permissions":["ViewAny:Accessory","ViewAny:AccessoryOrders","ViewAny:Battery","ViewAny:Branch","ViewAny:Category","ViewAny:Color","ViewAny:Condition","ViewAny:Delivery","ViewAny:Guarantee","ViewAny:HearAbout","ViewAny:Payment","ViewAny:ProductModel","ViewAny:Product","CanViewSku:Product","CanViewOrderId:Product","CanViewIsConsigned:Product","CanViewRetailPrice:Product","CanViewSalePrice:Product","CanViewUser:Product","CanViewMobile:Product","CanViewPdf:Product","CanViewNeedReset:Product","CanViewCondition:Product","CanViewStatus:Product","CanViewHearAbout:Product","CanViewDelivery:Product","CanViewGuarantee:Product","CanViewComment:Product","CanViewModel:Product","CanViewCategory:Product","CanViewBattery:Product","CanViewColor:Product","CanViewStorage:Product","CanViewIsRepaired:Product","CanViewShowRepairedInformation:Product","ViewAny:RepairInformation","ViewAny:Status","ViewAny:Storage","ShowAllProducts:User","CanAccessPanel:User","View:ProductListWidget","View:ProductPageWidget","ViewAny:Service","CanViewServiceComment:Product","CanViewCompany:Product"]},{"name":"\\u10d9\\u10dd\\u10dc\\u10e1\\u10e3\\u10da\\u10e2\\u10d0\\u10dc\\u10e2\\u10d8","guard_name":"web","permissions":["ViewAny:Accessory","Create:Accessory","Update:Accessory","ViewAnyConsignmentAccessories:Accessory","ViewConsignmentAccessories:Accessory","CreateConsignmentAccessories:Accessory","UpdateConsignmentAccessories:Accessory","AttachConsignmentAccessories:Accessory","ViewAny:AccessoryOrders","Create:AccessoryOrders","Update:AccessoryOrders","ViewAny:Battery","Create:Battery","Update:Battery","ViewAny:CashDrawer","Create:CashDrawer","CanSetOpeningBalance:CashDrawer","CanWithdraw:CashDrawer","CanDeposit:CashDrawer","CanClose:CashDrawer","CanReopen:CashDrawer","ViewAny:CashMovement","ViewAny:Category","Create:Category","Update:Category","ViewAny:Color","Create:Color","Update:Color","ViewAny:Condition","Create:Condition","Update:Condition","ViewAny:Consignment","Create:Consignment","Update:Consignment","ViewCustomer:Consignment","ViewCreator:Consignment","ViewPaidAmount:Consignment","ViewSubtotal:Consignment","ViewDebt:Consignment","ViewIsPaid:Consignment","ViewPriceChanges:Consignment","ViewAny:Delivery","Create:Delivery","Update:Delivery","ViewAny:Guarantee","Create:Guarantee","Update:Guarantee","ViewAny:HearAbout","Create:HearAbout","Update:HearAbout","ViewAny:Payment","Create:Payment","Update:Payment","ViewAny:ProductModel","Create:ProductModel","Update:ProductModel","ViewAny:Product","Create:Product","Update:Product","CanViewSku:Product","CanViewOrderId:Product","CanViewIsConsigned:Product","CanViewRetailPrice:Product","CanViewSalePrice:Product","CanViewUser:Product","CanViewMobile:Product","CanViewPdf:Product","CanViewNeedReset:Product","CanViewCondition:Product","CanViewStatus:Product","CanViewHearAbout:Product","CanViewDelivery:Product","CanCreateGuarantee:Product","CanViewGuarantee:Product","CanViewComment:Product","CanViewModel:Product","CanViewCategory:Product","CanViewBattery:Product","CanDownloadExcel:Product","CanViewColor:Product","CanViewStorage:Product","CanViewIsRepaired:Product","CanViewShowRepairedInformation:Product","ViewProductStatusBulkUpdate:Product","BulkAttachConsignmentProducts:Product","UpdateConsignmentProducts:Product","ViewAnyConsignmentProducts:Product","ViewConsignmentProducts:Product","CreateConsignmentProducts:Product","DeleteConsignmentProducts:Product","AttachConsignmentProducts:Product","DetachConsignmentProducts:Product","CanViewRepairAction:Product","ViewAny:RepairInformation","Create:RepairInformation","Update:RepairInformation","ViewAny:Status","Create:Status","Update:Status","ViewAny:Storage","Create:Storage","Update:Storage","ViewAny:User","ShowAllProducts:User","CanAccessPanel:User","CanSendSms:User","View:AnalyticsDashboard","View:CashDrawerWidget","View:LatestProductsWidget","View:ProductListWidget","View:ProductPageWidget","View:TopUsersChartWidget","ViewAny:Service","ViewCustomer:Service","ViewCreator:Service","ViewPaidAmount:Service","ViewSubtotal:Service","ViewDebt:Service","ViewIsPaid:Service","ViewRepairHistories:Service","BulkAttachServiceProducts:Product","UpdateServiceProducts:Product","DetachServiceProducts:Product","CanViewServiceComment:Product","View:Product","View:SalesSourcesChartWidget","View:SellerPerformanceWidget","View:LowStockByModelChart","CanViewCompany:Product","View:DeadStockWidget","ViewAny:ServiceProduct","Create:ServiceProduct","Update:ServiceProduct","View:CurrentMonthPaymentsWidget"]},{"name":"\\u10db\\u10d4\\u10dc\\u10d4\\u10ef\\u10d4\\u10e0\\u10d8","guard_name":"web","permissions":["ViewAny:Role","Create:Role","Update:Role","ViewAny:Accessory","Create:Accessory","Update:Accessory","ViewAnyConsignmentAccessories:Accessory","ViewConsignmentAccessories:Accessory","CreateConsignmentAccessories:Accessory","UpdateConsignmentAccessories:Accessory","DeleteConsignmentAccessories:Accessory","AttachConsignmentAccessories:Accessory","DetachConsignmentAccessories:Accessory","ViewAny:AccessoryOrders","Create:AccessoryOrders","Update:AccessoryOrders","ViewAny:Battery","Create:Battery","Update:Battery","ViewAny:Branch","Create:Branch","Update:Branch","ViewAny:CashDrawer","Create:CashDrawer","Update:CashDrawer","CanSetOpeningBalance:CashDrawer","CanWithdraw:CashDrawer","CanDeposit:CashDrawer","CanClose:CashDrawer","CanReopen:CashDrawer","ViewAny:CashMovement","Create:CashMovement","Update:CashMovement","ViewAny:Category","Create:Category","Update:Category","ViewAny:Color","Create:Color","Update:Color","ViewAny:Condition","Create:Condition","Update:Condition","ViewAny:Consignment","Create:Consignment","Update:Consignment","ViewCustomer:Consignment","ViewCreator:Consignment","ViewPaidAmount:Consignment","ViewSubtotal:Consignment","ViewDebt:Consignment","ViewIsPaid:Consignment","ViewPriceChanges:Consignment","ViewAny:Delivery","Create:Delivery","Update:Delivery","ViewAny:ExpenseType","Create:ExpenseType","Update:ExpenseType","ViewAny:Expense","Create:Expense","Update:Expense","ViewAny:Guarantee","Create:Guarantee","Update:Guarantee","ViewAny:HearAbout","Create:HearAbout","Update:HearAbout","ViewAny:Payment","Create:Payment","Update:Payment","ViewAny:ProductModel","Create:ProductModel","Update:ProductModel","ViewAny:Product","Create:Product","Update:Product","CanViewSku:Product","CanViewOrderId:Product","CanViewPrice:Product","CanViewIsConsigned:Product","CanViewRetailPrice:Product","CanViewSalePrice:Product","CanViewUser:Product","CanViewMobile:Product","CanViewPdf:Product","CanViewNeedReset:Product","CanViewCondition:Product","CanViewStatus:Product","CanViewHearAbout:Product","CanViewDelivery:Product","CanCreateGuarantee:Product","CanViewGuarantee:Product","CanViewComment:Product","CanViewModel:Product","CanViewCategory:Product","CanViewBattery:Product","CanDownloadExcel:Product","CanViewColor:Product","CanViewStorage:Product","CanViewIsRepaired:Product","CanViewShowRepairedInformation:Product","ViewProductStatusBulkUpdate:Product","BulkAttachConsignmentProducts:Product","UpdateConsignmentProducts:Product","ViewAnyConsignmentProducts:Product","ViewConsignmentProducts:Product","CreateConsignmentProducts:Product","DeleteConsignmentProducts:Product","AttachConsignmentProducts:Product","DetachConsignmentProducts:Product","CanViewRepairAction:Product","ViewAny:RepairInformation","Create:RepairInformation","Update:RepairInformation","ViewAny:Status","Create:Status","Update:Status","ViewAny:Storage","Create:Storage","Update:Storage","ViewAny:User","Create:User","Update:User","ShowAllProducts:User","CanAccessPanel:User","CanSendSms:User","View:AnalyticsDashboard","View:CashDrawerWidget","View:ExpenseStatsWidget","View:LatestProductsWidget","View:ProductChartWidget","View:ProductListWidget","View:ProductPageWidget","View:ProductStatsWidget","View:RepairHistoryWidget","View:TopModelsWidget","View:TopUsersChartWidget","ViewAny:Service","Create:Service","Update:Service","ViewCustomer:Service","ViewCreator:Service","ViewPaidAmount:Service","ViewSubtotal:Service","ViewDebt:Service","ViewIsPaid:Service","ViewRepairHistories:Service","BulkAttachServiceProducts:Product","UpdateServiceProducts:Product","DetachServiceProducts:Product","CanPay:Service","CanPayAll:Service","CanViewServiceComment:Product","View:Product","View:SalesSourcesChartWidget","View:SellerPerformanceWidget","View:LowStockByModelChart","CanViewCompany:Product","View:DeadStockWidget","ViewAny:ServiceProduct","Create:ServiceProduct","Update:ServiceProduct","View:CurrentMonthPaymentsWidget","View:SellerMonthlyStatsWidget"]},{"name":"\\u10d1\\u10e3\\u10e6\\u10d0\\u10da\\u10e2\\u10d4\\u10e0\\u10d8","guard_name":"web","permissions":["ViewAny:Accessory","ViewAny:CashDrawer","ViewAny:CashMovement","ViewAny:Consignment","ViewCustomer:Consignment","ViewCreator:Consignment","ViewPaidAmount:Consignment","ViewSubtotal:Consignment","ViewDebt:Consignment","ViewIsPaid:Consignment","ViewPriceChanges:Consignment","ViewAny:Expense","ViewAny:Product","CanViewSku:Product","CanViewOrderId:Product","CanViewIsConsigned:Product","CanViewRetailPrice:Product","CanViewSalePrice:Product","CanViewUser:Product","CanViewMobile:Product","CanViewPdf:Product","CanViewNeedReset:Product","CanViewCondition:Product","CanViewStatus:Product","CanViewHearAbout:Product","CanViewDelivery:Product","CanCreateGuarantee:Product","CanViewGuarantee:Product","CanViewComment:Product","CanViewModel:Product","CanViewCategory:Product","CanViewBattery:Product","CanDownloadExcel:Product","CanViewColor:Product","CanViewStorage:Product","CanViewIsRepaired:Product","CanViewShowRepairedInformation:Product","CanAccessPanel:User","View:AnalyticsDashboard","View:CashDrawerWidget","View:ExpenseStatsWidget","View:LatestProductsWidget","View:ProductChartWidget","View:ProductListWidget","View:ProductPageWidget","View:ProductStatsWidget","View:RepairHistoryWidget","View:TopModelsWidget","View:TopUsersChartWidget","ViewAny:Service","ViewCustomer:Service","ViewCreator:Service","ViewPaidAmount:Service","ViewSubtotal:Service","ViewDebt:Service","ViewIsPaid:Service","View:Product","View:SalesSourcesChartWidget","View:SellerPerformanceWidget","View:LowStockByModelChart","CanViewCompany:Product","View:DeadStockWidget","View:CurrentMonthPaymentsWidget","View:SellerMonthlyStatsWidget"]}]';
        $directPermissions = '{"119":{"name":"CanViewIsPayed:Product","guard_name":"web"},"175":{"name":"ViewCustomer:Product","guard_name":"web"},"176":{"name":"ViewCreator:Product","guard_name":"web"},"177":{"name":"ViewPaidAmount:Product","guard_name":"web"},"178":{"name":"ViewSubtotal:Product","guard_name":"web"},"179":{"name":"ViewDebt:Product","guard_name":"web"},"180":{"name":"ViewIsPaid:Product","guard_name":"web"},"181":{"name":"ViewPriceChanges:Product","guard_name":"web"},"182":{"name":"ViewPriceChanges:Service","guard_name":"web"},"189":{"name":"View:DeadStockByModelChart","guard_name":"web"},"223":{"name":"View:AccessorySalesWidget","guard_name":"web"}}';

        // 1. Seed tenants first (if present)
        if (! blank($tenants) && $tenants !== '[]') {
            static::seedTenants($tenants);
        }

        // 2. Seed roles with permissions
        static::makeRolesWithPermissions($rolesWithPermissions);

        // 3. Seed direct permissions
        static::makeDirectPermissions($directPermissions);

        // 4. Seed users with their roles/permissions (if present)
        if (! blank($users) && $users !== '[]') {
            static::seedUsers($users);
        }

        // 5. Seed user-tenant pivot (if present)
        if (! blank($userTenantPivot) && $userTenantPivot !== '[]') {
            static::seedUserTenantPivot($userTenantPivot);
        }

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function seedTenants(string $tenants): void
    {
        if (blank($tenantData = json_decode($tenants, true))) {
            return;
        }

        $tenantModel = '';
        if (blank($tenantModel)) {
            return;
        }

        foreach ($tenantData as $tenant) {
            $tenantModel::firstOrCreate(
                ['id' => $tenant['id']],
                $tenant
            );
        }
    }

    protected static function seedUsers(string $users): void
    {
        if (blank($userData = json_decode($users, true))) {
            return;
        }

        $userModel = 'App\Models\User';
        $tenancyEnabled = false;

        foreach ($userData as $data) {
            // Extract role/permission data before creating user
            $roles = $data['roles'] ?? [];
            $permissions = $data['permissions'] ?? [];
            $tenantRoles = $data['tenant_roles'] ?? [];
            $tenantPermissions = $data['tenant_permissions'] ?? [];
            unset($data['roles'], $data['permissions'], $data['tenant_roles'], $data['tenant_permissions']);

            $user = $userModel::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            // Handle tenancy mode - sync roles/permissions per tenant
            if ($tenancyEnabled && (! empty($tenantRoles) || ! empty($tenantPermissions))) {
                foreach ($tenantRoles as $tenantId => $roleNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncRoles($roleNames);
                }

                foreach ($tenantPermissions as $tenantId => $permissionNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncPermissions($permissionNames);
                }
            } else {
                // Non-tenancy mode
                if (! empty($roles)) {
                    $user->syncRoles($roles);
                }

                if (! empty($permissions)) {
                    $user->syncPermissions($permissions);
                }
            }
        }
    }

    protected static function seedUserTenantPivot(string $pivot): void
    {
        if (blank($pivotData = json_decode($pivot, true))) {
            return;
        }

        $pivotTable = '';
        if (blank($pivotTable)) {
            return;
        }

        foreach ($pivotData as $row) {
            $uniqueKeys = [];

            if (isset($row['user_id'])) {
                $uniqueKeys['user_id'] = $row['user_id'];
            }

            $tenantForeignKey = 'team_id';
            if (! blank($tenantForeignKey) && isset($row[$tenantForeignKey])) {
                $uniqueKeys[$tenantForeignKey] = $row[$tenantForeignKey];
            }

            if (! empty($uniqueKeys)) {
                DB::table($pivotTable)->updateOrInsert($uniqueKeys, $row);
            }
        }
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $roleModel */
        $roleModel = Utils::getRoleModel();
        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        $tenancyEnabled = false;
        $teamForeignKey = 'team_id';

        foreach ($rolePlusPermissions as $rolePlusPermission) {
            $tenantId = $rolePlusPermission[$teamForeignKey] ?? null;

            // Set tenant context for role creation and permission sync
            if ($tenancyEnabled) {
                setPermissionsTeamId($tenantId);
            }

            $roleData = [
                'name' => $rolePlusPermission['name'],
                'guard_name' => $rolePlusPermission['guard_name'],
            ];

            // Include tenant ID in role data (can be null for global roles)
            if ($tenancyEnabled && ! blank($teamForeignKey)) {
                $roleData[$teamForeignKey] = $tenantId;
            }

            $role = $roleModel::firstOrCreate($roleData);

            if (! blank($rolePlusPermission['permissions'])) {
                $permissionModels = collect($rolePlusPermission['permissions'])
                    ->map(fn ($permission) => $permissionModel::firstOrCreate([
                        'name' => $permission,
                        'guard_name' => $rolePlusPermission['guard_name'],
                    ]))
                    ->all();

                $role->syncPermissions($permissionModels);
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (blank($permissions = json_decode($directPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        foreach ($permissions as $permission) {
            if ($permissionModel::whereName($permission['name'])->doesntExist()) {
                $permissionModel::create([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ]);
            }
        }
    }
}
