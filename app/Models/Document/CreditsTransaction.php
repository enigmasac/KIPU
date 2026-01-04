<?php

namespace App\Models\Document;

use App\Abstracts\Model;
use App\Models\Document\Document;
use App\Traits\Currencies;
use App\Traits\DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditsTransaction extends Model
{
    use Currencies, DateTime;

    protected $table = 'credit_debit_notes_credits_transactions';

    protected $dates = ['deleted_at'];

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'type',
        'paid_at',
        'amount',
        'currency_code',
        'currency_rate',
        'document_id',
        'contact_id',
        'category_id',
        'description',
    ];

    /**
     * Sortable columns.
     *
     * @var array
     */
    public $sortable = ['amount'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function credit_note(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class, 'document_id');
    }

    /**
     * Scope to include only income.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeIncome($query)
    {
        return $query->where($this->table . '.type', '=', 'income');
    }

    /**
     * Scope to include only expense.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeExpense($query)
    {
        return $query->where($this->table . '.type', '=', 'expense');
    }

    /**
     * Get by document (invoice/credit note).
     *
     * @param Builder $query
     * @param  integer $document_id
     * @return Builder
     */
    public function scopeDocument($query, $document_id)
    {
        return $query->where('document_id', '=', $document_id);
    }

    /**
     * Convert amount to double.
     *
     * @param  string  $value
     * @return void
     */
    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = (double) $value;
    }

    /**
     * Convert currency rate to double.
     *
     * @param  string  $value
     * @return void
     */
    public function setCurrencyRateAttribute($value)
    {
        $this->attributes['currency_rate'] = (double) $value;
    }
}
