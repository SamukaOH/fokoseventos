<?php
require_once __DIR__ . '/app/config/config.php';
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

echo "VERSAO NO AR: " . APP_VERSION . "\n";
echo "(se não for 7.3.0, o fix ainda não subiu)\n\n";

echo "APP_URL: " . APP_URL . "\n\n";

// Simular o parsing do URI para /api/financeiro
$_test = '/api/financeiro';
$basePath = rtrim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/');
$reqPath = $_test;
if ($basePath !== '' && strpos($reqPath, $basePath) === 0) {
    $uri = substr($reqPath, strlen($basePath)) ?: '/';
} else {
    $uri = $reqPath ?: '/';
}
if ($uri === '' || $uri[0] !== '/') $uri = '/' . $uri;

echo "TESTE DE ROTEAMENTO:\n";
echo "  Request: /api/financeiro\n";
echo "  basePath extraído: [" . $basePath . "]\n";
echo "  URI final que o router recebe: [" . $uri . "]\n\n";

if ($uri === '/api/financeiro') {
    echo "✓ CORRETO — vai chamar lista() (JSON)\n";
} else {
    echo "✗ BUG — vai chamar a rota errada e retornar HTML\n";
    echo "  Este é o problema. Precisa subir a versão 7.3.0.\n";
}
