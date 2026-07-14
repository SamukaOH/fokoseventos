<?php
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/database.php';
header('Content-Type: text/plain; charset=utf-8');
$key = getenv('SETUP_KEY');
if (($_GET['key'] ?? '') !== $key) { http_response_code(403); die("Chave inválida.\n"); }
try {
    Database::query("CREATE TABLE IF NOT EXISTS `sessions` (`id` VARCHAR(128) NOT NULL PRIMARY KEY, `data` MEDIUMTEXT NOT NULL, `expires` INT UNSIGNED NOT NULL, INDEX `idx_expires` (`expires`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓ Tabela 'sessions' OK.\n";
} catch (\Throwable $e) { http_response_code(500); echo "Erro: " . $e->getMessage() . "\n"; }
