<?php

return [

    'name'        => 'WooCommerce',
    'description' => 'Sync items, categories, customers, tax rates, and invoices',
    'woocommerce' => 'WooCommerce',

    'form' => [
        'general'               => 'General',
        'custom_authentication' => 'Custom Authentication',
        'authenticate'          => 'Authenticate',
        're-authenticate'       => 'Re-authenticate',
        'url'                   => 'WordPress URL',
        'consumer_key'          => 'WooCommerce Consumer Key',
        'consumer_secret'       => 'WooCommerce Consumer Secret',
        'sync'                  => 'Sync Current Data',
        'products_category'     => 'Products Category',
        'order_status_ids'      => 'Order Status IDs',
        'invoices_category'     => 'Invoices Category',
        'account_id'            => 'Payments Account',
        'api_key'               => 'API Key',
        'two_way_create_update' => '2-way Create/Update',
        'two_way_delete'        => '2-way Delete',
        'not_transferred'       => 'Record has not been transferred to WooCommerce, yet. ' .
                                   'Please, enter your WooCommerce API key into app settings.',
        'akaunting_sync'        => 'Akaunting to WooCommerce Sync',
        'install_custom_fields' => 'If you want to sync your custom fields in WooCommerce, ' .
                                   'please install <a href=":link" target="_blank">Custom Fields App</a>',
        'auth_description'      => 'Please click into Authenticate button and connect your shop with Akaunting ' .
                                   'application to can access to your data and have option ' .
                                   'to copy it to Akaunting application.',
        'auth_warning'          => 'Please make sure that activate pretty permalinks in your ' .
                                   'WordPress Dashboard > Settings > Permalinks. Default permalinks will not work.',
        'auth_success'          => 'Successfully connected to Woocommerce',
        'field_mapping'         => 'Field Mapping',
        'wp_fields'             => 'WordPress Fields',
        'fields'                => 'Akaunting Fields',
        'new_field'            => 'New Field',
    ],

    'types' => [
        'categories'      => 'Category|Categories',
        'contacts'        => 'Contact|Contacts',
        'items'           => 'Item|Items',
        'invoices'        => 'Invoice|Invoices',
        'orders'          => 'Order|Orders',
        'taxes'           => 'Tax|Taxes',
        'payment_methods' => 'Payment Method|Payment Methods',
        'attributes'      => 'Attributes|Attribute',
    ],

    'sync_text'         => 'Syncing :type: :value ...',
    'finished'          => 'Sync finished',
    'total'             => 'Total items to be synced: :count',
    'total_all'         => 'Total Customers: :customers, Total Products: :products, Total Invoices: :invoices',
    'amount_correction' => 'Total amount correction',

    'notifications'     => [
        'module_disabled' => '<a href=":url">The app has been disabled due to communication issues with your store.</a>',
    ],

    'confirm' => 'Confirm',
    'error'   => [
        'api_connection_error'      => 'There was an error communicating with Woocommerce REST API.' .
                                       ' Please see' .
                                       ' <a href="https://akaunting.com/docs/app-manual/ecommerce/woocommerce">' .
                                       'the documentation. akaunting.com/docs</a>',
        'nothing_to_sync'           => 'Nothing to sync from WooCommerce to Akaunting',
        'nothing_to_sync_akaunting' => 'Nothing to sync from Akaunting to WooCommerce',
        'order_no_item'             => 'Order #:id does not have any item so synchronisation is failed.',
        'product_no_variation'      => 'Product #:id does not have any variation. Skipped!',
        'customer_sync_error'       => 'Customer #:id is not synced',
        'contact_sync_error'        => 'Contact #:id is not synced',
        'item_sync_error'           => 'Item #:id is not synced',
        'category_sync_error'       => 'Category #:id is not synced',
        'sync_already_running'      => 'Synchronisation is already running on the background!',
        'restart'                   => '<a href=":route"><u>Click here</u></a> to stop background sync and restart sync.'
    ],

    'success' => [
        'added'      => ':type added by WooCommerce Integration',
        'updated'    => ':type updated by WooCommerce Integration',
        'deleted'    => ':type deleted by WooCommerce Integration',
        'duplicated' => ':type duplicated by WooCommerce Integration',
        'imported'   => ':type imported by WooCommerce Integration',
        'enabled'    => ':type enabled by WooCommerce Integration',
        'disabled'   => ':type disabled by WooCommerce Integration',
    ],

    'status' => [
        'pending'    => 'Pending',
        'processing' => 'Processing',
        'on_hold'    => 'On-Hold',
        'completed'  => 'Completed',
        'cancelled'  => 'Cancelled',
        'refunded'   => 'Refunded',
        'failed'     => 'Failed',
        'trash'      => 'Trash',
    ],
];
