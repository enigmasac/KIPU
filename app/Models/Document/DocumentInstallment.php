<?php

namespace App\Models\Document;

use App\Abstracts\Model;

class DocumentInstallment extends Model
{
    protected $fillable = [
        'company_id',
        'document_id',
        'amount',
        'due_at',
    ];

    protected $casts = [
        'amount' => 'double',
        'due_at' => 'date',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
