<?php

return [

    'credit_note_number'     => 'Número de nota de crédito',
    'document_number'        => 'Número de nota de crédito',
    'credit_note_date'       => 'Fecha de nota de crédito',
    'invoice_date'           => 'Fecha de nota de crédito',
    'due_date'               => 'Fecha de vencimiento',
    'issued_at'              => 'Fecha de nota de crédito',
    'related_document_date'  => 'Fecha de nota de crédito',
    'credit_note_amount'     => 'Monto de nota de crédito',
    'total_price'            => 'Precio total',
    'issue_date'             => 'Fecha de emisión',
    'related_invoice_number' => 'Número de factura',
    'bill_to'                => 'Facturar a',

    'quantity'      => 'Cantidad',
    'price'         => 'Precio',
    'sub_total'     => 'Subtotal',
    'discount'      => 'Descuento',
    'item_discount' => 'Descuento de línea',
    'tax_total'     => 'Total de impuestos',
    'total'         => 'Total',

    'item_name' => 'Nombre del artículo|Nombres de artículos',

    'credit_customer_account' => 'Acreditar cuenta del cliente',
    'show_discount'           => ':discount% Descuento',
    'add_discount'            => 'Añadir descuento',
    'discount_desc'           => 'del subtotal',

    'customer_credited_with' => ':customer acreditado con :amount',
    'credit_cancelled'       => ':amount crédito cancelado',
    'refunded_customer_with' => 'Reembolsado :customer con :amount',
    'refund_to_customer'     => 'Reembolso a un cliente',

    'histories'           => 'Historiales',
    'type'                => 'Tipo',
    'credit'              => 'Crédito',
    'refund'              => 'Reembolso',
    'make_refund'         => 'Realizar reembolso',
    'mark_sent'           => 'Emitir en SUNAT',
    'mark_viewed'         => 'Marcar como visto',
    'mark_cancelled'      => 'Marcar como cancelado',
    'download_pdf'        => 'Descargar PDF',
    'send_mail'           => 'Enviar correo electrónico',
    'all_credit_notes'    => 'Inicie sesión para ver todas las notas de crédito',
    'create_credit_note'  => 'Emitir Nota de Crédito',
    'send_credit_note'    => 'Emitir Nota de Crédito',
    'timeline_sent_title' => 'Emitir Nota de Crédito',
    'refund_customer'     => 'Reembolsar cliente',
    'refunds_made'        => 'Reembolsos realizados',

    'refund_transaction' => 'Se realizó un reembolso de :amount usando :account.',

    'statuses' => [
        'draft'     => 'Borrador',
        'sent'      => 'Enviado',
        'viewed'    => 'Visto',
        'approved'  => 'Aprobado',
        'partial'   => 'Parcial',
        'cancelled' => 'Cancelado',
    ],

    'messages' => [
        'email_sent'       => '¡El correo electrónico de la nota de crédito ha sido enviado!',
        'marked_sent'      => '¡Nota de crédito marcada como enviada!',
        'marked_viewed'    => '¡Nota de crédito marcada como vista!',
        'marked_cancelled' => '¡Nota de crédito marcada como cancelada!',
        'refund_was_made'  => '¡Se realizó el reembolso!',
        'email_required'   => '¡No hay dirección de correo electrónico para este cliente!',
        'draft'            => 'Esta es una nota de crédito en <b>BORRADOR</b> y se reflejará en los gráficos después de que sea emitida en SUNAT.',

        'status' => [
            'created' => 'Creado el :date',
            'viewed'  => 'Visto',
            'send'    => [
                'draft' => 'No enviado',
                'sent'  => 'Enviado el :date',
            ],
        ],
    ],

];
