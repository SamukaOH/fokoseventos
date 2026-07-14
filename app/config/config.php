<?php
// ============================================================
// FOKOS EVENTOS — Configuração
// ============================================================

define('APP_NAME',    'Fokos Eventos');
define('APP_VERSION', '7.2.2');

// URL: detecta automaticamente em produção (Railway), ou usa APP_URL do ambiente, ou fallback XAMPP
$envUrl = getenv('APP_URL');
if (!$envUrl && getenv('RAILWAY_PUBLIC_DOMAIN')) {
    $envUrl = 'https://' . getenv('RAILWAY_PUBLIC_DOMAIN');
}
if (!$envUrl && isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost') {
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
             (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
             ? 'https' : 'http';
    $envUrl = $proto . '://' . $_SERVER['HTTP_HOST'];
}
define('APP_URL', rtrim($envUrl ?: 'http://localhost/fokos', '/'));

// Paths — config fica em /fokos/app/config/, sobe 3 níveis para chegar na raiz
define('BASE_PATH',   dirname(dirname(dirname(__FILE__))));
define('APP_PATH',    BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Banco
// Banco: variáveis da Railway (plugin MySQL) com fallback XAMPP
define('DB_HOST',    getenv('MYSQLHOST')     ?: getenv('DB_HOST') ?: 'localhost');
define('DB_PORT',    getenv('MYSQLPORT')     ?: getenv('DB_PORT') ?: '3306');
define('DB_NAME',    getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'fokos_eventos');
define('DB_USER',    getenv('MYSQLUSER')     ?: getenv('DB_USER') ?: 'root');
define('DB_PASS',    getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Uploads
define('UPLOAD_PATH',    BASE_PATH . '/public/uploads');
define('UPLOAD_URL',     APP_URL . '/public/uploads');
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_ALLOWED', ['jpg','jpeg','png','gif','webp','pdf']);

// Segurança
define('SESSION_NAME',     'fokos_sess');
define('SESSION_LIFETIME', 86400); // 24 horas
define('CSRF_TOKEN_NAME',  '_fokos_csrf');
define('RATE_LIMIT_MAX',   5);
define('RATE_LIMIT_TIME',  900);
