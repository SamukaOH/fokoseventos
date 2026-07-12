<?php
// Router para o servidor embutido do PHP (Railway / dev)
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Arquivo estático existe? Serve direto (CSS, JS, imagens, fontes)
$file = __DIR__ . $uri;
if ($uri !== '/' && is_file($file)) {
    // MIME types que o built-in server nem sempre acerta
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mimes = [
        'css'=>'text/css','js'=>'application/javascript','json'=>'application/json',
        'png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','gif'=>'image/gif',
        'webp'=>'image/webp','svg'=>'image/svg+xml','ico'=>'image/x-icon',
        'woff'=>'font/woff','woff2'=>'font/woff2','ttf'=>'font/ttf','eot'=>'application/vnd.ms-fontobject',
        'pdf'=>'application/pdf','sql'=>'text/plain',
    ];
    if (isset($mimes[$ext])) header('Content-Type: ' . $mimes[$ext]);
    return false; // deixa o built-in server servir o arquivo
}

// setup.php (instalador do banco)
if (preg_match('#^/setup\.php#', $uri)) {
    require __DIR__ . '/setup.php';
    return true;
}

// Tudo mais: rota pelo index.php
require __DIR__ . '/index.php';
