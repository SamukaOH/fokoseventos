<?php
require __DIR__.'/app/config/config.php';
header('Content-Type: text/plain');
echo "VERSAO: ".APP_VERSION."\n";
echo "Database carregada? ".(class_exists('Database')?'NAO deveria estar aqui':'ainda nao (normal)')."\n";
// verificar se há auto_prepend ativo
echo "auto_prepend_file: ".(ini_get('auto_prepend_file') ?: 'NENHUM (correto)')."\n";
