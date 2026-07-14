<?php
$key = getenv('SETUP_KEY');
require_once __DIR__ . '/app/config/config.php';
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

echo "VERSAO: " . APP_VERSION . "\n\n";

// Chamar a API real internamente com o cookie de sessão do browser
$cookie = '';
foreach ($_COOKIE as $k=>$v) $cookie .= "$k=$v; ";

$port = $_SERVER['SERVER_PORT'] ?? '8080';
$url = "http://127.0.0.1:$port/api/financeiro?modo=mensal&mes=" . date('Y-m');

$ctx = stream_context_create(['http' => [
    'method' => 'GET',
    'header' => "X-Requested-With: XMLHttpRequest\r\nCookie: $cookie\r\n",
    'ignore_errors' => true,
    'timeout' => 10,
]]);
$resp = @file_get_contents($url, false, $ctx);
$status = $http_response_header[0] ?? '?';
$ctype = '';
foreach ($http_response_header ?? [] as $h) if (stripos($h,'content-type')===0) $ctype=$h;

echo "API /api/financeiro:\n";
echo "  Status: $status\n";
echo "  $ctype\n";
echo "  Resposta (200 chars): " . substr($resp ?: '(vazia)',0,200) . "\n\n";
echo "  É JSON? " . (json_decode($resp)!==null ? "SIM ✓ — FINANCEIRO OK!" : "NÃO ✗") . "\n\n";

// Diagnóstico de sessão nesta chamada interna
echo "NOTA: esta chamada interna NÃO tem sua sessão real do browser,\n";
echo "então pode dar 'sessão expirada' — isso é esperado aqui.\n";
echo "O que importa: o Content-Type é JSON (não text/html).\n";
