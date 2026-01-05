<?php

namespace App\Models\Document;

use App\Models\Document\Document;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DebitNote extends Document
{
    use \App\Traits\HasSunatAttributes;
    public const TYPE = Document::DEBIT_NOTE_TYPE;

    protected $appends = ['attachment', 'amount_without_tax', 'discount', 'paid', 'received_at', 'status_label', 'sent_at', 'reconciled', 'contact_location', 'invoice_number', 'reason_description'];

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

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'bill_id')
            ->bill();
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\Banking\Transaction', 'document_id')->where('type', 'debit_note_refund');
    }

    public function getTemplatePathAttribute($value = null)
    {
        return $value ?: 'purchases.debit_notes.print_' . setting('debit-note.template', 'default');
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

/*
    protected static function newFactory(): Factory
    {
        return DebitNoteFactory::new();
    }
*/
}
