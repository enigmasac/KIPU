<?php

namespace Modules\Sunat\Models;

use App\Abstracts\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Certificate extends Model
{
    use SoftDeletes;

    protected $table = 'sunat_certificates';

    protected $fillable = [
        'company_id',
        'name',
        'content',
        'password_encrypted',
        'expires_at',
        'is_active',
        'thumbprint',
        'issuer',
        'subject',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'content',
        'password_encrypted',
    ];

    public function getDecryptedContent(): string
    {
        return Crypt::decryptString($this->content);
    }

    public function setContentAttribute(string $value): void
    {
        $this->attributes['content'] = Crypt::encryptString($value);
    }

    public function getDecryptedPassword(): ?string
    {
        if (empty($this->password_encrypted)) {
            return null;
        }
        return Crypt::decryptString($this->password_encrypted);
    }

    public function setPasswordEncryptedAttribute(?string $value): void
    {
        $this->attributes['password_encrypted'] = $value ? Crypt::encryptString($value) : null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expires_at && $this->expires_at->diffInDays(now()) <= $days;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
