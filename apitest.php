<?php
// Testa a API do financeiro diretamente, mostrando o erro real
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/config/DbSessionHandler.php';
require_once __DIR__ . '/app/helpers/helpers.php';

$key = getenv('SETUP_KEY');
if (($_GET['key'] ?? '') !== $key) { die("chave"); }

header('Content-Type: text/plain; charset=utf-8');

// Simular exatamente o que a API do financeiro faz
try {
    echo "=== Testando queries do financeiro ===\n\n";
    
    echo "1. financeiro (tabela existe?):\n";
    $r = Database::fetchAll("SELECT * FROM financeiro LIMIT 1");
    echo "   OK — " . count($r) . " linha(s)\n\n";
    
    echo "2. Colunas da tabela financeiro:\n";
    $cols = Database::fetchAll("SHOW COLUMNS FROM financeiro");
    foreach ($cols as $c) echo "   - {$c['Field']} ({$c['Type']})\n";
    echo "\n";
    
    echo "3. Query do dashboard financeiro:\n";
    $r = Database::fetchAll("SELECT tipo, SUM(valor) as total FROM financeiro GROUP BY tipo");
    echo "   OK\n\n";
    
    echo "4. demandas com colunas de retirada:\n";
    $cols = Database::fetchAll("SHOW COLUMNS FROM demandas");
    $nomes = array_column($cols, 'Field');
    echo "   Colunas: " . implode(', ', $nomes) . "\n";
    echo "   motorista_retirada_id existe: " . (in_array('motorista_retirada_id', $nomes) ? 'SIM' : 'NAO') . "\n";
    echo "   data_retirada existe: " . (in_array('data_retirada', $nomes) ? 'SIM' : 'NAO') . "\n";
    echo "   horario_retirada existe: " . (in_array('horario_retirada', $nomes) ? 'SIM' : 'NAO') . "\n";
    
    echo "\n=== TUDO OK ===\n";
} catch (\Throwable $e) {
    echo "\n❌ ERRO ENCONTRADO:\n";
    echo $e->getMessage() . "\n";
    echo "\nArquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
