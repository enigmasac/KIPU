<?php

return [

    'name'        => 'WooCommerce',
    'description' => 'Synchroniser les articles, les catégories, les clients, les taux de taxe et les factures',
    'woocommerce' => 'WooCommerce',

    'form' => [
        'general'               => 'Général',
        'url'                   => 'URL WordPress',
        'consumer_key'          => 'Clé client WooCommerce',
        'consumer_secret'       => 'Mot de passe WooCommerce',
        'sync'                  => 'Synchroniser les données actuelles',
        'products_category'     => 'Catégorie de produits',
        'order_status_ids'      => 'ID de statut de commande',
        'invoices_category'     => 'Catégorie de factures',
        'account_id'            => 'Compte de paiements',
        'api_key'               => 'Clé API',
        'two_way_create_update' => 'Création/Mise à jour bidirectionnelle',
        'two_way_delete'        => 'Suppression bidirectionnelle',
        'not_transferred'       => 'L\'enregistrement n\'a pas encore été transféré à WooCommerce. Veuillez entrer votre clé API WooCommerce dans les paramètres de l\'application.',
        'akaunting_sync'        => 'Synchronisation avec WooCommerce',
        'install_custom_fields' => 'Si vous souhaitez synchroniser vos champs personnalisés dans WooCommerce, veuillez installer <a href=":link" target="_blank">Application de champs personnalisés</a>',
        'custom_fields_mapping' => 'Mappage des champs personnalisés',
        'wp_custom_fields'      => 'Champs personnalisés WordPress',
        'custom_fields'         => 'Champs personnalisés application',
    ],

    'types' => [
        'categories'      => 'Catégorie|Catégories',
        'contacts'        => 'Contact|Contacts',
        'items'           => 'Article|Articles',
        'invoices'        => 'Facture|Factures',
        'taxes'           => 'Taxe|Taxes',
        'payment_methods' => 'Mode de paiement|Modes de paiement',
    ],

    'sync_text' => 'Synchronisation de :type: :value ...',
    'finished'  => 'Synchronisation terminée',
    'total'     => 'Total des éléments à synchroniser : :count',
    'total_all' => 'Total des clients : :clients, Total des produits: :produits, Total des commandes: :orders',

    'confirm' => 'Confirmer',
    'error'   => [
        'api_connection_error'      => 'Il y a eu une erreur de communication avec l\'API REST Woocommerce. Veuillez consulter <a href="https://akaunting.com/docs/app-manual/ecommerce/woocommerce">la documentation. akaunting.com/docs</a>',
        'nothing_to_sync'           => 'Rien à synchroniser de WooCommerce vers l\'application',
        'nothing_to_sync_akaunting' => 'Rien à synchroniser de l\'application vers WooCommerce',
        'order_no_item'             => 'La commande #:id n\'a aucun article donc la synchronisation a échoué.',
        'customer_sync_error'       => 'Le client #:id n\'est pas synchronisé',
    ],

    'success' => [
        'added'      => ':type ajouté par WooCommerce Intégration',
        'updated'    => ':type mis à jour par l\'intégration WooCommerce',
        'deleted'    => ':type supprimé par l\'intégration WooCommerce',
        'duplicated' => ':type dupliqué par l\'intégration WooCommerce',
        'imported'   => ':type importé par l\'intégration WooCommerce',
        'enabled'    => ':type activé par l\'intégration WooCommerce',
        'disabled'   => ':type désactivé par l\'intégration WooCommerce',
    ],

    'status' => [
        'pending'    => 'En attente',
        'processing' => 'Traitement en cours',
        'on_hold'    => 'En attente',
        'completed'  => 'Terminé',
        'cancelled'  => 'Annulé',
        'refunded'   => 'Remboursé',
        'failed'     => 'Echoué',
        'trash'      => 'Corbeille',
    ],
];
