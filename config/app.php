<?php
/**
 * Configurações gerais da aplicação KROMA PRINT
 */

// Ambiente: 'development' ou 'production'
define('APP_ENV', 'development');
define('APP_NAME', 'KROMA PRINT ERP');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/kroma');
define('APP_TIMEZONE', 'America/Sao_Paulo');
define('APP_LOCALE', 'pt_BR');

// Exibição de erros (desabilitar em produção)
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Timezone
date_default_timezone_set(APP_TIMEZONE);

// Configurações de upload
define('UPLOAD_MAX_SIZE', 100 * 1024 * 1024); // 100MB
define('UPLOAD_ALLOWED_TYPES', [
    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
    'application/pdf',
    'application/zip', 'application/x-rar-compressed',
    'image/svg+xml',
    'video/mp4', 'video/avi',
    'application/illustrator', 'image/vnd.adobe.photoshop',
    'application/postscript',
]);

// Configurações de sessão
define('SESSION_LIFETIME', 7200);    // 2 horas
define('SESSION_REMEMBER', 2592000); // 30 dias
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_BLOCK_TIME', 900);     // 15 minutos em segundos

// Configurações de paginação
define('ITEMS_PER_PAGE', 25);

// Chave secreta para CSRF e tokens
define('APP_SECRET', 'kroma_secret_2026_change_in_production');
