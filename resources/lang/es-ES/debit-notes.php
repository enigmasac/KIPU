<?php

return [

    'debit_note_number'     => 'Número de nota de débito',
    'document_number'       => 'Número de nota de débito',
    'debit_note_date'       => 'Fecha de nota de débito',
    'bill_date'             => 'Fecha de nota de débito',
    'credit_note_date'       => 'Fecha de nota de crédito',
    'invoice_date'           => 'Fecha de nota de débito',
    'due_date'               => 'Fecha de vencimiento',
    'issued_at'             => 'Fecha de nota de débito',
    'related_document_date' => 'Fecha de nota de débito',
    'debit_note_amount'     => 'Monto de nota de débito',
    'total_price'           => 'Precio total',
    'issue_date'            => 'Fecha de emisión',
    'related_bill_number'   => 'Número de factura',
    'debit_note_to'         => 'Nota de débito para',
    'contact_info'          => 'Nota de débito para',

    'quantity'      => 'Cantidad',
    'price'         => 'Precio',
    'sub_total'     => 'Subtotal',
    'discount'      => 'Descuento',
    'item_discount' => 'Descuento de línea',
    'tax_total'     => 'Total de impuestos',
    'total'         => 'Total',

    'item_name' => 'Nombre del artículo|Nombres de artículos',

    'show_discount' => ':discount% Descuento',
    'add_discount'  => 'Añadir descuento',
    'discount_desc' => 'del subtotal',

    'refund_from_vendor'          => 'Reembolso de un proveedor',
    'received_refund_from_vendor' => 'Recibido :amount como reembolso de :vendor',

    'histories'           => 'Historiales',
    'type'                => 'Tipo',
    'refund'              => 'Reembolso',
    'mark_sent'           => 'Emitir en SUNAT',
    'receive_refund'      => 'Recibir reembolso',
    'mark_viewed'         => 'Marcar como visto',
    'mark_cancelled'      => 'Marcar como cancelado',
    'download_pdf'        => 'Descargar PDF',
    'send_mail'           => 'Enviar correo electrónico',
    'description'         => 'Las notas de débito son comprobantes que se emiten para corregir facturas aumentando el monto de la operación o por intereses. No tienes notas de débito.',
    'all_debit_notes'     => 'Inicie sesión para ver todas las notas de débito',
    'create_debit_note'   => 'Emitir Nota de Débito',
    'send_debit_note'     => 'Emitir Nota de Débito',
    'timeline_sent_title' => 'Emitir Nota de Débito',
    'refunds_received'    => 'Reembolsos recibidos',

    'refund_transaction' => 'Se recibió un reembolso de :amount usando :account.',

    'statuses' => [
        'draft'     => 'Borrador',
        'sent'      => 'Enviado',
        'viewed'    => 'Visto',
        'cancelled' => 'Cancelado',
    ],

    'messages' => [
        'email_sent'          => '¡El correo electrónico de la nota de débito ha sido enviado!',
        'marked_viewed'       => '¡Nota de débito marcada como vista!',
        'refund_was_received' => '¡Se recibió el reembolso!',
        'email_required'      => '¡No hay dirección de correo electrónico para este cliente!',
        'draft'               => 'Esta es una nota de débito en <b>BORRADOR</b> y se reflejará en los gráficos después de que sea emitida en SUNAT.',

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
