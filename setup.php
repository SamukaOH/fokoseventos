<?php
// ============================================================
// FOKOS EVENTOS — Instalador do banco (Railway)
// Uso ÚNICO: /setup.php?key=SUA_SETUP_KEY
// ============================================================
require_once __DIR__ . '/app/config/config.php';

header('Content-Type: text/plain; charset=utf-8');

$key = getenv('SETUP_KEY');
if (!$key) { http_response_code(403); die("SETUP_KEY não configurada no ambiente.\n"); }
if (($_GET['key'] ?? '') !== $key) { http_response_code(403); die("Chave inválida.\n"); }

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(500); die("Falha ao conectar: " . $e->getMessage() . "\n");
}

$tabelas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
if (count($tabelas) > 0 && !isset($_GET['force'])) {
    die("O banco já possui " . count($tabelas) . " tabelas — instalação abortada.\nPara forçar reinstalação: adicione &force na URL (vai apagar tudo e recriar).\n");
}
if (count($tabelas) > 0 && isset($_GET['force'])) {
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    foreach ($tabelas as $t) { $pdo->exec("DROP TABLE `{$t}`"); }
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "Tabelas antigas removidas (" . count($tabelas) . ").\n";
}

$sqlFile = __DIR__ . '/instalacao_completa.sql';
if (!file_exists($sqlFile)) { http_response_code(500); die("instalacao_completa.sql não encontrado.\n"); }

$sql = file_get_contents($sqlFile);

// Remover comandos de criação/uso de database (Railway já fornece o schema)
$sql = preg_replace('/^\s*(CREATE DATABASE|USE)\b.*?;/mi', '', $sql);

// Remover "IF NOT EXISTS" de ALTER TABLE ADD COLUMN (não suportado em MySQL 5.7/8.0 padrão)
$sql = preg_replace('/ADD COLUMN IF NOT EXISTS/i', 'ADD COLUMN', $sql);
$sql = preg_replace('/ADD IF NOT EXISTS/i', 'ADD COLUMN', $sql);

// Separar por ";" e executar um por um (PDO::exec não suporta multi-statement em todos os drivers)
$statements = preg_split('/;\s*$/m', $sql);
$executed = 0;
$errors = [];

foreach ($statements as $stmt) {
    $stmt = trim($stmt);
    if ($stmt === '' || strpos($stmt, '--') === 0) continue;
    try {
        $pdo->exec($stmt);
        $executed++;
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        // Ignorar erros de "coluna já existe" ou "tabela já existe" (migrações idempotentes)
        if (strpos($msg, 'Duplicate column') !== false
         || strpos($msg, 'already exists') !== false
         || strpos($msg, '1060') !== false
         || strpos($msg, '1050') !== false) {
            continue;
        }
        $errors[] = substr($stmt, 0, 80) . '... → ' . $msg;
    }
}

$total = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "✓ Instalação concluída!\n";
echo "Tabelas criadas: " . count($total) . "\n";
echo "Statements executados: {$executed}\n";

$users = $pdo->query("SELECT email, tipo FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) echo "  usuário: {$u['email']} ({$u['tipo']})\n";

if ($errors) {
    echo "\n⚠ Avisos (" . count($errors) . "):\n";
    foreach ($errors as $e) echo "  · {$e}\n";
}

echo "\nPor segurança, remova o setup.php ou troque a SETUP_KEY.\n";
