<?php
// ════════════════════════════════════════════════
// Router para PHP built-in server (Railway / dev)
// ════════════════════════════════════════════════
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Arquivo estático? Serve direto
$file = __DIR__ . $uri;
if ($uri !== '/' && is_file($file)) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mimes = [
        'css'=>'text/css','js'=>'application/javascript','json'=>'application/json',
        'png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','gif'=>'image/gif',
        'webp'=>'image/webp','svg'=>'image/svg+xml','ico'=>'image/x-icon',
        'woff'=>'font/woff','woff2'=>'font/woff2','ttf'=>'font/ttf',
        'pdf'=>'application/pdf','sql'=>'text/plain',
    ];
    if (isset($mimes[$ext])) header('Content-Type: ' . $mimes[$ext]);
    return false;
}

// Garantir diretório correto
chdir(__DIR__);

// Setup
if (preg_match('#^/setup\.php#', $uri)) {
    require __DIR__ . '/setup.php';
    return true;
}

// Pré-carregar core (blindagem contra edge-case de path)
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/helpers/helpers.php';
require_once __DIR__ . '/app/middlewares/Router.php';

// Rota pelo index.php
require __DIR__ . '/index.php';
