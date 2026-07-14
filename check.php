<?php
require_once __DIR__ . '/app/config/config.php';
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
echo "VERSAO: " . APP_VERSION . "\n\n";
echo "O QUE O NGINX PASSA PARA O PHP:\n";
echo "  REQUEST_URI:  " . ($_SERVER['REQUEST_URI'] ?? 'VAZIO') . "\n";
echo "  SCRIPT_NAME:  " . ($_SERVER['SCRIPT_NAME'] ?? 'VAZIO') . "\n";
echo "  QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'VAZIO') . "\n";
echo "  PATH_INFO:    " . ($_SERVER['PATH_INFO'] ?? 'VAZIO') . "\n\n";
echo "IMPORTANTE: se REQUEST_URI mostrar /check.php e não a rota real,\n";
echo "o Nginx está reescrevendo a URI e QUEBRANDO o roteamento.\n";
