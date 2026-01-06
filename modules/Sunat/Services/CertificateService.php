<?php

namespace Modules\Sunat\Services;

use Illuminate\Http\UploadedFile;
use Modules\Sunat\Models\Certificate;

class CertificateService
{
    public function uploadCertificate(
        UploadedFile $file,
        string $password,
        int $companyId,
        ?string $name = null
    ): Certificate {
        $pfxContent = file_get_contents($file->getRealPath());
        $certs = [];

        \Log::info('Tentativa de subir certificado', ['file' => $file->getClientOriginalName(), 'company' => $companyId]);

        if (!openssl_pkcs12_read($pfxContent, $certs, $password)) {
            \Log::error('Error en openssl_pkcs12_read: ' . openssl_error_string());
            throw new \Exception('Contraseña del certificado incorrecta o archivo inválido');
        }

        $pemContent = $certs['cert'] . "\n" . $certs['pkey'];
        $certInfo = openssl_x509_parse($certs['cert']);

        // Extraer el nombre del certificado si no se proporciona
        if (!$name) {
            $name = $certInfo['subject']['CN'] ?? $file->getClientOriginalName();
        }

        $expiresAt = isset($certInfo['validTo_time_t'])
            ? date('Y-m-d', $certInfo['validTo_time_t'])
            : null;

        // Desactivar certificados anteriores
        Certificate::forCompany($companyId)->active()->update(['is_active' => false]);

        return Certificate::create([
            'company_id' => $companyId,
            'name' => $name,
            'content' => $pemContent,
            'password_encrypted' => $password,
            'expires_at' => $expiresAt,
            'is_active' => true,
            'issuer' => $certInfo['issuer']['CN'] ?? null,
            'subject' => $certInfo['subject']['CN'] ?? null,
            'thumbprint' => openssl_x509_fingerprint($certs['cert']),
        ]);
    }
}
