<?php
require_once __DIR__ . '/app/config/config.php';
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
echo "VERSAO: " . APP_VERSION . "\n";
echo "(precisa ser 7.3.6+ pro debug funcionar)\n\n";

// Chamar /api/financeiro?__debug=1 internamente
$port = $_SERVER['SERVER_PORT'] ?? '8080';
$ctx = stream_context_create(['http'=>['timeout'=>8,'ignore_errors'=>true]]);

echo "=== /api/financeiro?__debug=1 ===\n";
$r = @file_get_contents("http://127.0.0.1:$port/api/financeiro?__debug=1", false, $ctx);
$ct='';foreach($http_response_header??[] as $h) if(stripos($h,'content-type')===0)$ct=$h;
echo "Content-Type: $ct\n";
echo "Resposta:\n$r\n\n";

echo "INTERPRETAÇÃO:\n";
if (strpos($r,'uri_final') !== false) {
    echo "✓ index.php executou o debug. Veja 'uri_final' acima.\n";
    echo "  Se uri_final != /api/financeiro → achamos o bug do parsing.\n";
} elseif (strpos($r,'FINANCEIRO') !== false) {
    echo "✗ Retornou a VIEW. O index.php NÃO está processando /api/financeiro.\n";
    echo "  Isso significa que o Nginx serve /api/financeiro de forma errada,\n";
    echo "  OU o parsing do URI está mandando pra rota /financeiro.\n";
} else {
    echo "? Resposta inesperada.\n";
}
