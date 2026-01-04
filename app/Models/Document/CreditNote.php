<?php

namespace App\Models\Document;

use App\Models\Document\Document;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
// use Modules\CreditDebitNotes\Database\Factories\CreditNote as CreditNoteFactory;
use App\Traits\Documents;

class CreditNote extends Document
{
    use Documents;

    public const TYPE = Document::CREDIT_NOTE_TYPE;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope(self::TYPE, function (Builder $builder) {
            $builder->where('type', self::TYPE);
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'invoice_id')
            ->invoice();
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\Banking\Transaction', 'document_id')
            ->where('type', 'credit_note_refund');
    }

    public function credits_transactions()
    {
        return $this->hasMany('App\Models\Document\CreditsTransaction', 'document_id')
            ->where('type', 'income');
    }

    public function getTemplatePathAttribute($value = null)
    {
        return $value ?: 'sales.credit_notes.print_' . setting('credit-note.template', 'default');
    }

    public function getPaidAttribute()
    {
        return 0;
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status == 'sent') {
            return 'status-success';
        }

        return parent::getStatusLabelAttribute();
    }

    public function getInvoiceNumberAttribute(): string
    {
        return optional($this->invoice)->document_number ?? '';
    }

/*
    protected static function newFactory(): Factory
    {
        return CreditNoteFactory::new();
    }
*/

    
}
