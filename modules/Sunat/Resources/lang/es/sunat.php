<?php

return [
    'name' => 'SUNAT',
    'settings' => [
        'title' => 'Configuración SUNAT',
        'environment' => 'Ambiente',
        'environment_beta' => 'Beta (Pruebas)',
        'environment_production' => 'Producción',
        'auto_emit' => 'Emisión Automática',
    ],
    'credentials' => [
        'title' => 'Credenciales Clave SOL',
        'ruc' => 'RUC',
        'sol_user' => 'Usuario SOL',
        'sol_password' => 'Clave SOL',
    ],
    'certificate' => [
        'title' => 'Certificado Digital',
        'upload' => 'Subir Certificado',
        'file' => 'Archivo .pfx o .p12',
        'password' => 'Contraseña',
        'name' => 'Nombre',
        'expires_at' => 'Fecha de expiración',
        'active' => 'Activo',
        'no_certificate' => 'No hay certificado configurado',
    ],
    'status' => [
        'pending' => 'Pendiente',
        'sent' => 'Enviado',
        'accepted' => 'Aceptado',
        'rejected' => 'Rechazado',
        'observed' => 'Observado',
        'error' => 'Error',
    ],
    'actions' => [
        'emit' => 'Emitir a SUNAT',
    ],
    'messages' => [
        'emit_success' => 'Documento emitido correctamente',
        'emit_error' => 'Error al emitir: :message',
        'no_certificate' => 'Configure un certificado digital primero',
    ],
];
