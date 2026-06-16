<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shield Resource
    |--------------------------------------------------------------------------
    |
    | Here you may configure the built-in role management resource. You can
    | customize the URL, choose whether to show model paths, group it under
    | a cluster, and decide which permission tabs to display.
    |
    */

    'shield_resource' => [
        'slug' => 'shield/roles',
        'show_model_path' => true,
        'cluster' => null,
        'tabs' => [
            'pages' => true,
            'widgets' => true,
            'resources' => true,
            'custom_permissions' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy
    |--------------------------------------------------------------------------
    |
    | When your application supports teams, Shield will automatically detect
    | and configure the tenant model during setup. This enables tenant-scoped
    | roles and permissions throughout your application.
    |
    */

    'tenant_model' => null,

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | This value contains the class name of your user model. This model will
    | be used for role assignments and must implement the HasRoles trait
    | provided by the Spatie\Permission package.
    |
    */

    'auth_provider_model' => 'App\\Models\\User',

    /*
    |--------------------------------------------------------------------------
    | Super Admin
    |--------------------------------------------------------------------------
    |
    | Here you may define a super admin that has unrestricted access to your
    | application. You can choose to implement this via Laravel's gate system
    | or as a traditional role with all permissions explicitly assigned.
    |
    */

    'super_admin' => [
        'enabled' => true,
        'name' => 'ადმინისტრატორი',
        'define_via_gate' => false,
        'intercept_gate' => 'before',
    ],

    /*
    |--------------------------------------------------------------------------
    | Panel User
    |--------------------------------------------------------------------------
    |
    | When enabled, Shield will create a basic panel user role that can be
    | assigned to users who should have access to your Filament panels but
    | don't need any specific permissions beyond basic authentication.
    |
    */

    'panel_user' => [
        'enabled' => false,
        'name' => 'panel_user',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Builder
    |--------------------------------------------------------------------------
    |
    | You can customize how permission keys are generated to match your
    | preferred naming convention and organizational standards. Shield uses
    | these settings when creating permission names from your resources.
    |
    | Supported formats: snake, kebab, pascal, camel, upper_snake, lower_snake
    |
    */

    'permissions' => [
        'separator' => ':',
        'case' => 'pascal',
        'generate' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Policies
    |--------------------------------------------------------------------------
    |
    | Shield can automatically generate Laravel policies for your resources.
    | When merge is enabled, the methods below will be combined with any
    | resource-specific methods you define in the resources section.
    |
    */

    'policies' => [
        'path' => app_path('Policies'),
        'merge' => true,
        'generate' => true,
        'methods' => [
            'viewAny', 'create', 'update', 'delete', 'deleteAny'
        ],
        'single_parameter_methods' => [
            'viewAny',
            'create',
            'deleteAny',
            'forceDeleteAny',
            'restoreAny',
            'reorder',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Localization
    |--------------------------------------------------------------------------
    |
    | Shield supports multiple languages out of the box. When enabled, you
    | can provide translated labels for permissions to create a more
    | localized experience for your international users.
    |
    */

    'localization' => [
        'enabled' => true,
        'key' => 'filament-shield::filament-shield.resource_permission_prefixes_labels',
    ],

    /*
    |--------------------------------------------------------------------------
    | Resources
    |--------------------------------------------------------------------------
    |
    | Here you can fine-tune permissions for specific Filament resources.
    | Use the 'manage' array to override the default policy methods for
    | individual resources, giving you granular control over permissions.
    |
    */

    'resources' => [
        'subject' => 'model',
        'manage' => [
            \BezhanSalleh\FilamentShield\Resources\Roles\RoleResource::class => [
                'viewAny',
                'view',
                'create',
                'update',
                'delete',
            ],
            \App\Filament\Resources\Users\UserResource::class => [
                'show_all_products',
                'can_access_panel',
                'can_send_sms'
            ],
            \App\Filament\Resources\Accessories\AccessoryResource::class => [
                'view_any_consignment_accessories',
                'view_consignment_accessories',
                'create_consignment_accessories',
                'update_consignment_accessories',
                'delete_consignment_accessories',
                'attach_consignment_accessories',
                'detach_consignment_accessories',
            ],
            \App\Filament\Resources\CashMovements\CashMovementResource::class => [
                'viewAny'
            ],
            \App\Filament\Resources\CashDrawers\CashDrawerResource::class => [
                'can_set_opening_balance',
                'can_withdraw',
                'can_deposit',
                'can_close',
                'can_reopen',
            ],
            \App\Filament\Resources\Consignments\ConsignmentResource::class => [
                'view_customer',
                'view_creator',
                'view_paid_amount',
                'view_subtotal',
                'view_debt',
                'view_is_paid',
                'view_price_changes',
                'view_subtotal',
                'view_paid_amount',
                'view_debt',
                'view_is_paid',
            ],
            \App\Filament\Resources\Services\ServiceResource::class => [
                'view_customer',
                'view_creator',
                'view_paid_amount',
                'view_subtotal',
                'view_debt',
                'view_is_paid',
                'view_repair_histories',
                'view_subtotal',
                'view_paid_amount',
                'view_debt',
                'view_is_paid',
                'can_pay',
                'can_pay_all',
            ],
            \App\Filament\Resources\Products\ProductResource::class => [
                'view',
                'can_view_sku',
                'can_view_order_id',
                'can_view_price',
                'can_view_is_consigned',
                'can_view_retail_price',
                'can_view_sale_price',
                'can_view_user',
                'can_view_mobile',
                'can_view_pdf',
                'can_view_need_reset',
                'can_view_condition',
                'can_view_status',
                'can_view_hear_about',
                'can_view_delivery',
                'can_create_guarantee',
                'can_view_guarantee',
                'can_view_comment',
                'can_view_service_comment',
                'can_view_model',
                'can_view_category',
                'can_view_battery',
                'can_download_excel',
                'can_view_color',
                'can_view_company',
                'can_view_storage',
                'can_view_is_repaired',
                'can_view_show_repaired_information',
                'view_product_status_bulk_update',
                'bulk_attach_consignment_products',
                'bulk_attach_service_products',
                'update_consignment_products',
                'view_any_consignment_products',
                'view_consignment_products',
                'create_consignment_products',
                'update_consignment_products',
                'delete_consignment_products',
                'attach_consignment_products',
                'detach_consignment_products',
                'can_view_repair_action',
                'update_service_products',
                'detach_service_products',
            ],
        ],
        'exclude' => [

        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    |
    | Most Filament pages only require view permissions. Pages listed in the
    | exclude array will be skipped during permission generation and won't
    | appear in your role management interface.
    |
    */

    'pages' => [
        'subject' => 'class',
        'prefix' => 'view',
        'exclude' => [
            \Filament\Pages\Dashboard::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Widgets
    |--------------------------------------------------------------------------
    |
    | Like pages, widgets typically only need view permissions. Add widgets
    | to the exclude array if you don't want them to appear in your role
    | management interface.
    |
    */

    'widgets' => [
        'subject' => 'class',
        'prefix' => 'view',
        'exclude' => [
            \Filament\Widgets\AccountWidget::class,
            \Filament\Widgets\FilamentInfoWidget::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Permissions
    |--------------------------------------------------------------------------
    |
    | Sometimes you need permissions that don't map to resources, pages, or
    | widgets. Define any custom permissions here and they'll be available
    | when editing roles in your application.
    |
    */

    'custom_permissions' => [],

    /*
    |--------------------------------------------------------------------------
    | Entity Discovery
    |--------------------------------------------------------------------------
    |
    | By default, Shield only looks for entities in your default Filament
    | panel. Enable these options if you're using multiple panels and want
    | Shield to discover entities across all of them.
    |
    */

    'discovery' => [
        'discover_all_resources' => false,
        'discover_all_widgets' => false,
        'discover_all_pages' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Policy
    |--------------------------------------------------------------------------
    |
    | Shield can automatically register a policy for role management itself.
    | This lets you control who can manage roles using Laravel's built-in
    | authorization system. Requires a RolePolicy class in your app.
    |
    */

    'register_role_policy' => true,

];
