<?php
// Health check ultra-simples — sem require, sem banco, sem nada
header('Content-Type: text/plain');
echo "PONG\n";
echo "PHP: " . PHP_VERSION . "\n";
echo "Arquivo index.php existe: " . (file_exists(__DIR__.'/index.php') ? 'SIM' : 'NAO') . "\n";
echo "Config existe: " . (file_exists(__DIR__.'/app/config/config.php') ? 'SIM' : 'NAO') . "\n";
if (file_exists(__DIR__.'/app/config/config.php')) {
    require __DIR__.'/app/config/config.php';
    echo "VERSAO APP: " . APP_VERSION . "\n";
    echo "APP_URL: " . APP_URL . "\n";
}
