<?php
// ════════════════════════════════════════════════
// Router para PHP built-in server (Railway)
// ════════════════════════════════════════════════
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// ── Debug: /debug mostra o estado do servidor ──
if ($uri === '/debug') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "DIR: " . __DIR__ . "\n";
    echo "CWD: " . getcwd() . "\n";
    echo "URI: " . $_SERVER['REQUEST_URI'] . "\n";
    echo "HOST: " . ($_SERVER['HTTP_HOST'] ?? 'n/a') . "\n";
    echo "PROTO: " . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'n/a') . "\n";
    echo "RAILWAY_PUBLIC_DOMAIN: " . (getenv('RAILWAY_PUBLIC_DOMAIN') ?: 'n/a') . "\n";
    echo "ENV APP_URL: " . (getenv('APP_URL') ?: 'n/a') . "\n\n";
    
    $css = __DIR__ . '/public/assets/css/app.css';
    echo "CSS exists: " . (is_file($css) ? 'YES (' . filesize($css) . ' bytes)' : 'NO') . "\n";
    $js = __DIR__ . '/public/assets/js/app.js';
    echo "JS exists: " . (is_file($js) ? 'YES (' . filesize($js) . ' bytes)' : 'NO') . "\n";
    $img = __DIR__ . '/public/assets/img/logo-full.png';
    echo "Logo exists: " . (is_file($img) ? 'YES' : 'NO') . "\n\n";
    
    echo "Files in /public/assets/css/:\n";
    foreach (glob(__DIR__ . '/public/assets/css/*') as $f) echo "  " . basename($f) . " (" . filesize($f) . ")\n";
    echo "\nFiles in /public/assets/js/:\n";
    foreach (glob(__DIR__ . '/public/assets/js/*') as $f) echo "  " . basename($f) . " (" . filesize($f) . ")\n";
    
    // Test config auto-detection
    require_once __DIR__ . '/app/config/config.php';
    echo "\nAPP_URL resolved: " . APP_URL . "\n";
    echo "DB_HOST: " . DB_HOST . "\n";
    echo "DB_PORT: " . DB_PORT . "\n";
    echo "DB_NAME: " . DB_NAME . "\n";
    exit;
}

// ── Arquivo estático ──
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

// ── App ──
chdir(__DIR__);

if (preg_match('#^/setup\.php#', $uri)) {
    require __DIR__ . '/setup.php';
    exit;
}

require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/helpers/helpers.php';
require_once __DIR__ . '/app/middlewares/Router.php';

require __DIR__ . '/index.php';
