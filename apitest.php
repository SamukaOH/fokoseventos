<?php
$key = getenv('SETUP_KEY');
if (($_GET['key'] ?? '') !== ($key ?: '')) { die("chave"); }

header('Content-Type: text/plain; charset=utf-8');

// Chamar a própria API /api/financeiro via HTTP, com o cookie de sessão atual
$cookie = '';
foreach ($_COOKIE as $k=>$v) $cookie .= "$k=$v; ";

$url = 'http://127.0.0.1' . ':' . ($_SERVER['SERVER_PORT'] ?? '8080') . '/api/financeiro?modo=mensal&mes=' . date('Y-m');

echo "Chamando: /api/financeiro?modo=mensal&mes=" . date('Y-m') . "\n";
echo "Cookie enviado: " . substr($cookie,0,40) . "...\n\n";

$ctx = stream_context_create(['http' => [
    'method' => 'GET',
    'header' => "X-Requested-With: XMLHttpRequest\r\nCookie: $cookie\r\n",
    'ignore_errors' => true,
]]);

$resp = @file_get_contents($url, false, $ctx);
$status = $http_response_header[0] ?? 'sem status';

echo "Status HTTP: $status\n";
echo "Headers:\n";
foreach ($http_response_header ?? [] as $h) echo "  $h\n";
echo "\n=== RESPOSTA (primeiros 500 chars) ===\n";
echo substr($resp ?: '(vazia)', 0, 500);
echo "\n\n=== É JSON válido? ===\n";
$j = json_decode($resp, true);
echo $j === null ? "NÃO — " . json_last_error_msg() : "SIM";
