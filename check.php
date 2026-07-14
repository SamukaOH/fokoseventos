<?php
require_once __DIR__ . '/app/config/config.php';
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
echo "VERSAO: " . APP_VERSION . "\n\n";
$port = $_SERVER['SERVER_PORT'] ?? '8080';
$ctx = stream_context_create(['http'=>['timeout'=>8,'ignore_errors'=>true]]);
echo "=== /api/financeiro?__raw=1 (o que o PHP recebe do Nginx) ===\n";
$r = @file_get_contents("http://127.0.0.1:$port/api/financeiro?__raw=1", false, $ctx);
echo $r . "\n\n";
echo "SE REQUEST_URI mostrar '/api/financeiro' → parsing quebrou\n";
echo "SE REQUEST_URI mostrar '/index.php' → Nginx apaga a URI (o bug!)\n";
echo "SE retornar a VIEW → nem esse debug rodou (deploy velho)\n";
