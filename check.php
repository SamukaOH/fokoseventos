<?php
require_once __DIR__ . '/app/config/config.php';
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
echo "VERSAO: " . APP_VERSION . "\n\n";

$port = $_SERVER['SERVER_PORT'] ?? '8080';
$base = "http://127.0.0.1:$port";

function get($url) {
    $ctx = stream_context_create(['http'=>['timeout'=>8,'ignore_errors'=>true,'header'=>"X-Requested-With: XMLHttpRequest\r\n"]]);
    $r = @file_get_contents($url, false, $ctx);
    $status = $http_response_header[0] ?? '?';
    $ct = '';
    foreach ($http_response_header ?? [] as $h) if (stripos($h,'content-type')===0) $ct=trim($h);
    return [$status, $ct, $r];
}

echo "1. CSS (app-v72.css):\n";
[$s,$ct,$r] = get("$base/public/assets/css/app-v72.css");
echo "   $s | $ct | " . strlen($r??'') . " bytes\n";
echo "   Começa com: " . substr(trim($r??''),0,40) . "\n\n";

echo "2. JS (app.js):\n";
[$s,$ct,$r] = get("$base/public/assets/js/app.js");
echo "   $s | $ct | " . strlen($r??'') . " bytes\n\n";

echo "3. API financeiro:\n";
[$s,$ct,$r] = get("$base/api/financeiro?modo=mensal&mes=".date('Y-m'));
echo "   $s | $ct\n";
echo "   " . substr(trim($r??''),0,60) . "\n\n";

echo "DIAGNÓSTICO:\n";
echo "- Se CSS vier 404 ou com HTML → Nginx não serve estáticos (causa da tela sem estilo)\n";
echo "- Se API vier text/html → roteamento ainda quebrado\n";
