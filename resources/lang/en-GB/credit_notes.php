<?php

return [

    'credit_note_number'     => 'Credit Note Number',
    'document_number'        => 'Credit Note Number',
    'credit_note_date'       => 'Credit Note Date',
    'issued_at'              => 'Credit Note Date',
    'related_document_date'  => 'Credit Note Date',
    'credit_note_amount'     => 'Credit Note Amount',
    'total_price'            => 'Total Price',
    'issue_date'             => 'Issue Date',
    'related_invoice_number' => 'Invoice Number',
    'bill_to'                => 'Bill To',

    'quantity'      => 'Quantity',
    'price'         => 'Price',
    'sub_total'     => 'Subtotal',
    'discount'      => 'Discount',
    'item_discount' => 'Line Discount',
    'tax_total'     => 'Tax Total',
    'total'         => 'Total',

    'item_name' => 'Item Name|Item Names',

    'credit_customer_account' => 'Credit Customer Account',
    'show_discount'           => ':discount% Discount',
    'add_discount'            => 'Add Discount',
    'discount_desc'           => 'of subtotal',

    'customer_credited_with' => ':customer credited with :amount',
    'credit_cancelled'       => ':amount credit cancelled',
    'refunded_customer_with' => 'Refunded :customer with :amount',
    'refund_to_customer'     => 'Refund to a customer',

    'histories'           => 'Histories',
    'type'                => 'Type',
    'credit'              => 'Credit',
    'refund'              => 'Refund',
    'make_refund'         => 'Make Refund',
    'mark_sent'           => 'Mark Sent',
    'mark_viewed'         => 'Mark Viewed',
    'mark_cancelled'      => 'Mark Cancelled',
    'download_pdf'        => 'Download PDF',
    'send_mail'           => 'Send Email',
    'all_credit_notes'    => 'Login to view all credit notes',
    'create_credit_note'  => 'Create Credit Note',
    'send_credit_note'    => 'Send Credit Note',
    'timeline_sent_title' => 'Send Credit Note',
    'refund_customer'     => 'Refund Customer',
    'refunds_made'        => 'Refunds Made',

    'refund_transaction' => 'A refund for :amount was made using :account.',

    'statuses' => [
        'draft'     => 'Draft',
        'sent'      => 'Sent',
        'viewed'    => 'Viewed',
        'approved'  => 'Approved',
        'partial'   => 'Partial',
        'cancelled' => 'Cancelled',
    ],

    'credit_note_details'   => 'Credit Note Details',
    'customer_credited_with' => 'Customer :customer credited with :amount',
    'use_credits'           => 'Use Credits',
    'credits_used'          => 'Credits used!',
    'invoice_amount_is_exceeded' => 'Invoice Amount Is Exceeded!',

    'messages' => [
        'error' => [
            'over_payment'       => 'Error: Credits not used! The amount you entered passes the total: :amount',
            'not_enough_credits' => 'Error: Credits not used! The amount you entered passes the available credits amount: :credits',
        ],
    ],
];
