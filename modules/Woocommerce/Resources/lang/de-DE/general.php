<?php

return [

    'name'        => 'WooCommerce',
    'description' => 'Artikel, Kategorien, Kunden, Steuersätze und Rechnungen synchronisieren',
    'woocommerce' => 'WooCommerce',

    'form' => [
        'general'               => 'Allgemein',
        'url'                   => 'WordPress URL',
        'consumer_key'          => 'WooCommerce Consumer Key',
        'consumer_secret'       => 'WooCommerce Consumer Secret',
        'sync'                  => 'Aktuelle Daten synchronisieren',
        'products_category'     => 'Produktkategorie',
        'order_status_ids'      => 'Bestellstatus IDs',
        'invoices_category'     => 'Rechnungskategorie',
        'account_id'            => 'Zahlungen Konto',
        'api_key'               => 'API Key',
        'two_way_create_update' => '2-Wege-Erstellen/Aktualisieren',
        'two_way_delete'        => '2-Wege-Löschen',
        'not_transferred'       => 'Der Datensatz wurde noch nicht zu WooCommerce übertragen. Bitte geben Sie Ihren WooCommerce-API-Schlüssel in die App-Einstellungen ein.',
        'akaunting_sync'        => 'Akaunting zu WooCommerce Sync',
        'install_custom_fields' => 'Wenn Sie Ihre benutzerdefinierten Felder in WooCommerce synchronisieren möchten, installieren Sie bitte <a href=":link" target="_blank">Benutzerdefinierte Felder-App</a>',
        'field_mapping'         => 'Feldzuordnung',
        'wp_fields'             => 'WordPress-Felder',
        'fields'                => 'Akaunting Felder',
    ],

    'types' => [
        'categories'      => 'Kategorie|Kategorien',
        'contacts'        => 'Kontakt|Kontakte',
        'items'           => 'Artikel|Artikel',
        'invoices'        => 'Rechnung|Rechnungen',
        'orders'          => 'Bestellung|Bestellungen',
        'taxes'           => 'Steuer|Steuern',
        'payment_methods' => 'Zahlungsmethode|Zahlungsmethoden',
        'attributes'      => 'Eigenschaft|Eigenschaften',
    ],

    'sync_text' => 'Synchronisiere :type: :value ...',
    'finished'  => 'Synchronisierung abgeschlossen',
    'total'     => 'Gesamte zu synchronisierende Elemente: :count',
    'total_all' => 'Gesamtkunden: :customers, Gesamtprodukte: :products, Gesamtrechnungen: :invoices',

    'confirm' => 'Bestätigen',
    'error'   => [
        'api_connection_error'      => 'Es gab einen Fehler bei der Kommunikation mit Woocommerce REST API. Bitte sehen Sie sich <a href="https://akaunting.com/docs/app-manual/ecommerce/woocommerce">die Dokumentation an. akaunting.com/docs</a>',
        'nothing_to_sync'           => 'Nichts zu synchronisieren von WooCommerce nach Akaunting',
        'nothing_to_sync_akaunting' => 'Nichts zu synchronisieren von Akaunting nach WooCommerce',
        'order_no_item'             => 'Bestellung #:id hat keine Artikel, daher ist die Synchronisierung fehlgeschlagen.',
        'product_no_variation'      => 'Produkt #:id hat keine Variante. Übersprungen!',
        'customer_sync_error'       => 'Kunde #:id ist nicht synchronisiert',
        'contact_sync_error'        => 'Kontakt #:id ist nicht synchronisiert',
        'item_sync_error'           => 'Artikel #:id ist nicht synchronisiert',
        'category_sync_error'       => 'Kategorie #:id ist nicht synchronisiert',
        'sync_already_running'      => 'Synchronisierung läuft bereits im Hintergrund!',
    ],

    'success' => [
        'added'      => ':type hinzugefügt von WooCommerce Integration',
        'updated'    => ':type aktualisiert von WooCommerce Integration',
        'deleted'    => ':type von WooCommerce Integration gelöscht',
        'duplicated' => ':type dupliziert von WooCommerce Integration',
        'imported'   => ':type importiert von WooCommerce Integration',
        'enabled'    => ':type aktiviert durch WooCommerce Integration',
        'disabled'   => ':type deaktiviert durch WooCommerce Integration',
    ],

    'status' => [
        'pending'    => 'Wartend',
        'processing' => 'Verarbeitung',
        'on_hold'    => 'Abwarten',
        'completed'  => 'Abgeschlossen',
        'cancelled'  => 'Storniert',
        'refunded'   => 'Zurückerstattet',
        'failed'     => 'Fehgeschlagen',
        'trash'      => 'Papierkorb',
    ],
];
