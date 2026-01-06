<?php

return [

    'name'        => 'WooCommerce',
    'description' => 'Синхронізуйте товари, категорії, клієнтів, податкові ставки та рахунки-фактури',
    'woocommerce' => 'WooCommerce',

    'form' => [
        'general'               => 'Основне',
        'custom_authentication' => 'Аутентифікація користувача',
        'authenticate'          => 'Аутентифікація',
        're-authenticate'       => 'Повторна автентифікація',
        'url'                   => 'URL-адреса WordPress',
        'consumer_key'          => 'Ключ споживача WooCommerce',
        'consumer_secret'       => 'Секрет споживача WooCommerce',
        'sync'                  => 'Синхронізувати поточні дані',
        'products_category'     => 'Категорії товарів',
        'order_status_ids'      => 'Статус замовлення',
        'invoices_category'     => 'Категорії Рахунків',
        'account_id'            => 'Платіжний рахунок',
        'api_key'               => 'Api ключ',
        'two_way_create_update' => '2-стороннє створення/Оновлення',
        'two_way_delete'        => '2-стороннє видалення',
        'not_transferred'       => 'Запис ще не передано в WooCommerce. ' .
                                   'Будь ласка, введіть свій ключ WooCommerce API у налаштуваннях додатку.',
        'akaunting_sync'        => 'Akaunting до WooCommerce Синхронізації',
        'install_custom_fields' => 'Якщо ви хочете синхронізувати свої власні поля в WooCommerce, ' .
                                   'установіть <a href=":link" target="_blank">додаток Custom Fields</a>',
        'auth_description'      => 'Будь ласка, натисніть кнопку "Аутентифікувати" та під\'єднайте ваш магазин до Akaunting ' .
                                   'програма може мати доступ до ваших даних та мати можливість ' .
                                   'скопіювати його до програми Akaunting.',
        'auth_warning'          => 'Будь ласка, переконайтеся, що активували досить постійні посилання у вашому ' .
                                   'Інформаційна панель WordPress > Налаштування > Постійні посилання. Постійні посилання за замовчуванням не працюватимуть.',
        'auth_success'          => 'Успішно під\'єднано до Woocommerce',
        'field_mapping'         => 'Відображення полів',
        'wp_fields'             => 'Поля WordPress',
        'fields'                => 'Поля Akaunting',
    ],

    'types' => [
        'categories'      => 'Категорія|Категорії',
        'contacts'        => 'Контакт|Контакти',
        'items'           => 'Товар|Товари',
        'invoices'        => 'Замовлення|Замовлення',
        'orders'          => 'Замовлення|Замовлення',
        'taxes'           => 'Податок|Податки',
        'payment_methods' => 'Спосіб оплати|Способи оплати',
        'attributes'      => 'Атрибути|Атрибут',
    ],

    'sync_text'         => 'Синхронізація :type: :value ...',
    'finished'          => 'Синхронізацію завершено',
    'total'             => 'Всього елементів для синхронізації: :count',
    'total_all'         => 'Разом Покупців: :customers, Разом Товарів: :products, Разом Замовлень: :orders
',
    'amount_correction' => 'Корекція загальної суми',

    'confirm' => 'Підтвердити',
    'error'   => [
        'api_connection_error'      => 'Сталася помилка під час спілкування з Woocommerce REST API.' .
                                       ' Будь ласка, дивіться' .
                                       ' <a href="https://akaunting.com/docs/app-manual/ecommerce/woocommerce">' .
                                       'документація. akaunting.com/docs</a>',
        'nothing_to_sync'           => 'Нічого для синхронізації з WooCommerce до Akaunting',
        'nothing_to_sync_akaunting' => 'Нічого для синхронізації з Akaunting до WooCommerce',
        'order_no_item'             => 'Замовлення #:id не містить жодного товару, тому синхронізацію не вдалося.',
        'product_no_variation'      => 'Продукт #:id не містить жодного варіанту. Пропущено!',
        'customer_sync_error'       => 'Клієнт #:id не синхронізовано',
        'contact_sync_error'        => 'Контакт #:id не синхронізовано',
        'item_sync_error'           => 'Товар #:id не синхронізовано',
        'category_sync_error'       => 'Категорію #:id не синхронізовано',
        'sync_already_running'      => 'Синхронізація вже виконується у фоновому режимі!',
    ],

    'success' => [
        'added'      => ':type увімкнено інтеграцію з WooCommerce',
        'updated'    => ':type оновлено інтеграцією WooCommerce',
        'deleted'    => ':type вимкнено інтеграцію з WooCommerce',
        'duplicated' => ':type продубльовано інтеграцією WooCommerce',
        'imported'   => ':type імпортовано інтеграцією WooCommerce',
        'enabled'    => ':type увімкнено інтеграцію з WooCommerce',
        'disabled'   => ':type вимкнено інтеграцію з WooCommerce',
    ],

    'status' => [
        'pending'    => 'Очікування',
        'processing' => 'В обробці',
        'on_hold'    => 'В очікуванні',
        'completed'  => 'Завершено',
        'cancelled'  => 'Скасовано',
        'refunded'   => 'Повернуто',
        'failed'     => 'Невдало',
        'trash'      => 'Кошик',
    ],
];
