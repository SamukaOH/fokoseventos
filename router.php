<?php
// ════════════════════════════════════════════════
// Router para PHP built-in server (Railway)
// ════════════════════════════════════════════════
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// ── Arquivo estático: serve explicitamente ──
$file = __DIR__ . $uri;
if ($uri !== '/' && is_file($file)) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mimes = [
        'css'=>'text/css','js'=>'application/javascript','json'=>'application/json',
        'png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','gif'=>'image/gif',
        'webp'=>'image/webp','svg'=>'image/svg+xml','ico'=>'image/x-icon',
        'woff'=>'font/woff','woff2'=>'font/woff2','ttf'=>'font/ttf',
        'pdf'=>'application/pdf','sql'=>'text/plain','map'=>'application/json',
    ];
    $ct = $mimes[$ext] ?? mime_content_type($file) ?: 'application/octet-stream';
    header('Content-Type: ' . $ct);
    header('Content-Length: ' . filesize($file));
    header('Cache-Control: public, max-age=2592000');
    readfile($file);
    exit;
}

// ── Diretório de trabalho ──
chdir(__DIR__);

// ── Setup (instalador) ──
if (preg_match('#^/setup\.php#', $uri)) {
    require __DIR__ . '/setup.php';
    exit;
}

// ── Core (blindagem) ──
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/helpers/helpers.php';
require_once __DIR__ . '/app/middlewares/Router.php';

// ── App ──
require __DIR__ . '/index.php';
