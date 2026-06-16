<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Table Columns
    |--------------------------------------------------------------------------
    */

    'column.name' => 'სახელი',
    'column.guard_name' => 'გუარდის სახელი',
    'column.roles' => 'როლები',
    'column.permissions' => 'ნებართვები',
    'column.updated_at' => 'განახლების თარიღი',

    /*
    |--------------------------------------------------------------------------
    | Form Fields
    |--------------------------------------------------------------------------
    */

    'field.name' => 'სახელი',
    'field.guard_name' => 'გუარდის სახელი',
    'field.permissions' => 'ნებართვები',
    'field.select_all.name' => 'ყველას მონიშვნა',
    'field.select_all.message' => 'ჩართეთ ყველა ნებართვა, რომელიც ამჟამად <span class="text-primary font-medium">ჩართულია</span> ამ როლისთვის',

    /*
    |--------------------------------------------------------------------------
    | Navigation & Resource
    |--------------------------------------------------------------------------
    */

    'nav.group' => 'Filament Shield',
    'nav.role.label' => 'როლები',
    'nav.role.icon' => 'heroicon-o-shield-check',
    'resource.label.role' => 'როლი',
    'resource.label.roles' => 'როლები',

    /*
    |--------------------------------------------------------------------------
    | Section & Tabs
    |--------------------------------------------------------------------------
    */

    'section' => 'ერთეულები',
    'resources' => 'რესურსები',
    'widgets' => 'ვიდჯეტები',
    'pages' => 'გვერდები',
    'custom' => 'მომხმარებლის ნებართვები',

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

    'forbidden' => 'თქვენ არ გაქვთ წვდომის ნებართვა',

    /*
    |--------------------------------------------------------------------------
    | Resource Permissions' Labels
    |--------------------------------------------------------------------------
    */

    'resource_permission_prefixes_labels' => [
        'view' => 'ნახვა',
        'view_any' => 'ნებისმიერის ნახვა',
        'create' => 'შექმნა',
        'update' => 'განახლება',
        'delete' => 'წაშლა',
        'delete_any' => 'ნებისმიერის წაშლა',
        'force_delete' => 'იძულებითი წაშლა',
        'force_delete_any' => 'ნებისმიერის იძულებითი წაშლა',
        'restore' => 'აღდგენა',
        'reorder' => 'გადალაგება',
        'restore_any' => 'ნებისმიერის აღდგენა',
        'replicate' => 'დუბლირება',

        'view_any_consignment_accessories' => 'ყველა კონსიგნაციის აქსესუარის ნახვა',
        'view_consignment_accessories' => 'კონსიგნაციის აქსესუარის ნახვა',
        'create_consignment_accessories' => 'კონსიგნაციის აქსესუარის შექმნა',
        'update_consignment_accessories' => 'კონსიგნაციის აქსესუარის განახლება',
        'delete_consignment_accessories' => 'კონსიგნაციის აქსესუარის წაშლა',
        'attach_consignment_accessories' => 'კონსიგნაციის აქსესუარის მიბმა',
        'detach_consignment_accessories' => 'კონსიგნაციის აქსესუარის მოხსნა',

        'can_set_opening_balance' => 'საწყისი ბალანსის დაყენება',
        'can_withdraw' => 'თანხის გატანა',
        'can_deposit' => 'თანხის შეტანა',
        'can_close' => 'დახურვა',
        'can_reopen' => 'ხელახლა გახსნა',

        'view_customer' => 'კლიენტის ნახვა',
        'view_creator' => 'შემქმნელის ნახვა',
        'view_paid_amount' => 'გადახდილი თანხის ნახვა',
        'view_subtotal' => 'ჯამური თანხის ნახვა',
        'view_debt' => 'ვალის ნახვა',
        'view_is_paid' => 'გადახდის სტატუსის ნახვა',
        'view_price_changes' => 'ფასის ცვლილებების ნახვა',

        'can_view_sku' => 'SKU-ს ნახვა',
        'can_view_order_id' => 'ორდერის ID-ის ნახვა',
        'can_view_price' => 'ფასის ნახვა',
        'can_view_is_consigned' => 'კომისიაშია თუ არა - ნახვა',
        'can_view_retail_price' => 'რიტეილ ფასის ნახვა',
        'can_view_sale_price' => 'გასაყიდი ფასის ნახვა',
        'can_view_user' => 'მომხმარებლის ნახვა',
        'can_view_mobile' => 'ტელეფონის ნომრის ნახვა',
        'can_view_pdf' => 'PDF-ის ნახვა',
        'can_view_need_reset' => 'გასანულებელია თუ არა - ნახვა',
        'can_view_condition' => 'მდგომარეობის ნახვა',
        'can_view_status' => 'სტატუსის ნახვა',
        'can_view_hear_about' => 'როგორ გაიგო - ნახვა',
        'can_view_delivery' => 'მიწოდების ნახვა',

        'can_create_guarantee' => 'გარანტიის შექმნა',
        'can_view_guarantee' => 'გარანტიის ნახვა',
        'can_view_comment' => 'კომენტარის ნახვა',
        'can_view_service_comment' => 'სერვისის კომენტარის ნახვა',
        'can_view_model' => 'მოდელის ნახვა',
        'can_view_category' => 'კატეგორიის ნახვა',
        'can_view_battery' => 'ბატარეის ნახვა',

        'can_download_excel' => 'Excel-ის ჩამოტვირთვა',

        'can_view_color' => 'ფერის ნახვა',
        'can_view_storage' => 'მეხსიერების ნახვა',
        'can_view_is_repaired' => 'შეკეთებულია თუ არა - ნახვა',
        'can_view_is_payed' => 'გადახდილია თუ არა - ნახვა',
        'can_view_repair_action' => 'აღდგენის ღილაკის ნახვა',
        'can_view_show_repaired_information' => 'შეკეთების ინფორმაციის ნახვა',

        'view_product_status_bulk_update' => 'პროდუქტის სტატუსის მასიური განახლების ნახვა',
        'bulk_attach_consignment_products' => 'კონსიგნაციის პროდუქტების მასიურად მიბმა',
        'update_consignment_products' => 'კონსიგნაციის პროდუქტების განახლება',

        'view_any_consignment_products' => 'ყველა კონსიგნაციის პროდუქტის ნახვა',
        'view_consignment_products' => 'კონსიგნაციის პროდუქტის ნახვა',
        'create_consignment_products' => 'კონსიგნაციის პროდუქტის შექმნა',
        'delete_consignment_products' => 'კონსიგნაციის პროდუქტის წაშლა',
        'attach_consignment_products' => 'კონსიგნაციის პროდუქტის მიბმა',
        'detach_consignment_products' => 'კონსიგნაციის პროდუქტის მოხსნა',

        'can_view_repair_action_show_all_products' => 'რემონტის მოქმედებაში ყველა პროდუქტის ნახვა',

        'can_access_panel' => 'პანელზე წვდომა',
        'can_send_sms' => 'SMS-ის გაგზავნა',

        'can_view_company' => 'კომპანიის ნახვა',

        'show_all_products' => 'ყველა პროდუქციის ჩვენება',
        'can_view_created_at' => 'შექმნილიას ნახვა',

        'view_repair_histories' => 'შეკეთების ისტორიის ნახვა',
        'update_service_products' => 'სერვისის პროდუქტის განახლება',
        'detach_service_products' => 'სერვისის პროდუქტის მოხსნა',
        'bulk_attach_service_products' => 'სერვისის პროდუქტების მასიურად მიბმა',

        'can_pay' => 'გადახდის უფლება',
        'can_pay_all' => 'ყველას გადახდის უფლება',
    ],
];
