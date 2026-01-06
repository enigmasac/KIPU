<?php

namespace Modules\Sunat\Models;

use App\Abstracts\Model;
use App\Models\Document\Document;

class Emission extends Model
{
    protected $table = 'sunat_emissions';

    protected $fillable = [
        'company_id',
        'document_id',
        'document_type',
        'document_number',
        'sunat_code',
        'sunat_message',
        'ticket',
        'hash',
        'xml_path',
        'cdr_path',
        'status',
        'observations',
        'retry_count',
        'emitted_at',
        'last_attempt_at',
    ];

    protected $casts = [
        'observations' => 'array',
        'retry_count' => 'integer',
        'emitted_at' => 'datetime',
        'last_attempt_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_OBSERVED = 'observed';
    public const STATUS_ERROR = 'error';

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isPending(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_ERROR]);
    }

    public function canRetry(): bool
    {
        $maxRetries = config('sunat.max_retries', 3);
        return $this->isPending() && $this->retry_count < $maxRetries;
    }

    public function markAsAccepted(string $code, string $message, ?string $hash = null): void
    {
        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'sunat_code' => $code,
            'sunat_message' => $message,
            'hash' => $hash,
            'emitted_at' => now(),
        ]);
    }

    public function markAsRejected(string $code, string $message): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'sunat_code' => $code,
            'sunat_message' => $message,
        ]);
    }

    public function markAsError(string $message): void
    {
        $this->update([
            'status' => self::STATUS_ERROR,
            'sunat_message' => $message,
            'last_attempt_at' => now(),
        ]);
        $this->increment('retry_count');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_ERROR]);
    }
}
