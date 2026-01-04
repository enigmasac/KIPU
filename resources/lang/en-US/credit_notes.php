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

    'customer_credited_with' => 'Customer :customer credited with :amount',
    'use_credits'           => 'Use Credits',
    'credits_used'          => 'Credits used!',
    'invoice_amount_is_exceeded' => 'Invoice Amount Is Exceeded!',
    'credit_note_details'   => 'Credit Note Details',

    'messages' => [
        'error' => [
            'over_payment'       => 'Error: Credits not used! The amount you entered passes the total: :amount',
            'not_enough_credits' => 'Error: Credits not used! The amount you entered passes the available credits amount: :credits',
        ],
        'email_sent'       => 'Credit Note email has been sent!',
        'marked_sent'      => 'Credit Note marked as sent!',
        'marked_viewed'    => 'Credit Note marked as viewed!',
        'marked_cancelled' => 'Credit Note marked as cancelled!',
        'refund_was_made'  => 'Refund Was Made!',
        'email_required'   => 'No email address for this customer!',
        'draft'            => 'This is a <b>DRAFT</b> credit note and will be reflected to charts after it gets sent.',

        'status' => [
            'created' => 'Created on :date',
            'viewed'  => 'Viewed',
            'send'    => [
                'draft' => 'Not sent',
                'sent'  => 'Sent on :date',
            ],
        ],
    ],

];
