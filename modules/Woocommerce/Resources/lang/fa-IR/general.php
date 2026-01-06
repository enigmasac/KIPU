<?php

return [

    'name'        => 'ووکامرس',
    'description' => 'موارد، دسته ها، مشتریان، نرخ مالیات و فاکتورها را همگام سازی کنید',
    'woocommerce' => 'ووکامرس',

    'form' => [
        'general'               => 'عمومی',
        'url'                   => 'لینک وردپرس',
        'consumer_key'          => 'کلید مصرف کننده ووکامرس',
        'consumer_secret'       => 'رمز مصرف کننده ووکامرس',
        'sync'                  => 'همگام سازی اطلاعات فعلی',
        'products_category'     => 'دسته بندی محصولات',
        'order_status_ids'      => 'شناسه های وضعیت سفارش',
        'invoices_category'     => 'دسته بندی فاکتورها',
        'account_id'            => 'حساب پرداخت ها',
        'api_key'               => 'کلید API',
        'two_way_create_update' => 'ایجاد/بروزرسانی دو طرفه',
        'two_way_delete'        => 'حذف دو طرفه',
        'not_transferred'       => 'هنوز رکورد به ووکامرس منتقل نشده است. لطفاً، کلید API ووکامرس خود را وارد تنظیمات برنامه کنید.',
        'akaunting_sync'        => 'همگام سازی از Akaunting به WooCommerce',
        'install_custom_fields' => 'اگر می خواهید قسمت های سفارشی خود را در WooCommerce همگام سازی کنید ، لطفاً آن را نصب کنید <a href=":link" target="_blank">Custom Fields App</a>',
        'custom_fields_mapping' => 'نگاشت فیلد سفارشی',
        'wp_custom_fields'      => 'زمینه های سفارشی وردپرس',
        'custom_fields'         => 'زمینه های سفارشی Akaunting',
    ],

    'types' => [
        'categories'      => 'بخش |بخش ها',
        'contacts'        => 'مخاطب|مخاطبین',
        'items'           => 'مورد | موارد',
        'invoices'        => 'فاکتور | فاکتورها',
        'taxes'           => 'مالیات | مالیات ها',
        'payment_methods' => 'روش پرداخت | روش های پرداخت',
        'attributes'      => 'جنبه | جنبه ها',
    ],

    'sync_text' => 'همگام سازی :type: :value ...',
    'finished'  => 'همگام سازی به پایان رسید',
    'total'     => 'کل مواردی که باید همگام سازی شود: :count',
    'total_all' => 'کل مشتریان: :customers, کل محصولات: :products, کل فاکتورها: :invoices',

    'confirm' => 'تایید',
    'error'   => [
        'api_connection_error'      => 'هنگام برقراری ارتباط خطایی روی داد با Woocommerce REST API. لطفا ببینید <a href="https://akaunting.com/docs/app-manual/ecommerce/woocommerce">the documentation. akaunting.com/docs</a>',
        'nothing_to_sync'           => 'هیچ چیزی برای همگام سازی از WooCommerce به Akaunting وجود ندارد',
        'nothing_to_sync_akaunting' => 'هیچ چیزی برای همگام سازی از Akaunting به WooCommerce وجود ندارد',
        'order_no_item'             => 'سفارش #:id هیچ موردی ندارد بنابراین همگام سازی ناموفق است..',
        'product_no_variation'     => 'محصول #:id هیچ گونه تنوعی ندارد. این مورد از قلم انداخته شد!',
        'customer_sync_error'       => 'مشتری #:id همگام سازی نشد',
        'contact_sync_error'       => 'مخاطب #:id همگام سازی نشد',
        'item_sync_error'       => 'آیتم #:id همگام سازی نشد',
        'category_sync_error'       => 'بخش #:id همگام سازی نشد',
    ],

    'success' => [
        'added'      => ':type اضافه شده توسط WooCommerce Integration',
        'updated'    => ':type بروز شده توسط WooCommerce Integration',
        'deleted'    => ':type حذف شده توسط WooCommerce Integration',
        'duplicated' => ':type دوباره کپی شده توسط WooCommerce Integration',
        'imported'   => ':type درون ریزی شده توسط WooCommerce Integration',
        'enabled'    => ':type فعال شده توسط WooCommerce Integration',
        'disabled'   => ':type غیرفعال شده توسط WooCommerce Integration',
    ],

    'status' => [
        'pending'    => 'در حالت انتظار',
        'processing' => 'در حالل پردازش',
        'on_hold'    => 'در انتظار',
        'completed'  => 'تکمیل شده',
        'cancelled'  => 'لغو شده',
        'refunded'   => 'بازپرداخت شده',
        'failed'     => 'شکست خورد',
        'trash'      => 'سطل زباله',
    ],
];
