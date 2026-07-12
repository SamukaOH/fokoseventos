<?php
// ============================================================
// FOKOS EVENTOS — Instalador do banco (Railway)
// ============================================================
require_once __DIR__ . '/app/config/config.php';
header('Content-Type: text/plain; charset=utf-8');

$key = getenv('SETUP_KEY');
if (!$key) { http_response_code(403); die("SETUP_KEY não configurada.\n"); }
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
    die("Banco tem " . count($tabelas) . " tabelas. Adicione &force para apagar e recriar.\n");
}
if (count($tabelas) > 0) {
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    foreach ($tabelas as $t) $pdo->exec("DROP TABLE `{$t}`");
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "Tabelas antigas removidas (" . count($tabelas) . ").\n";
}

$sqlFile = __DIR__ . '/instalacao_completa.sql';
if (!file_exists($sqlFile)) die("instalacao_completa.sql não encontrado.\n");

$raw = file_get_contents($sqlFile);

// Limpar: remover CREATE DATABASE / USE, e IF NOT EXISTS em ALTER
$raw = preg_replace('/^\s*(CREATE DATABASE|USE)\b.*?;/mi', '', $raw);
$raw = preg_replace('/ADD COLUMN IF NOT EXISTS/i', 'ADD COLUMN', $raw);
$raw = preg_replace('/ADD FOREIGN KEY IF NOT EXISTS/i', 'ADD FOREIGN KEY', $raw);
$raw = preg_replace('/ADD INDEX IF NOT EXISTS/i', 'ADD INDEX', $raw);
$raw = preg_replace('/ADD IF NOT EXISTS/i', 'ADD COLUMN', $raw);

// Separar em statements: linha por linha, acumulando até encontrar ";"
$lines = explode("\n", $raw);
$statements = [];
$buffer = '';
foreach ($lines as $line) {
    $trimmed = trim($line);
    // Pular linhas vazias e comentários puros
    if ($trimmed === '' || strpos($trimmed, '--') === 0) continue;
    $buffer .= $line . "\n";
    // Se a linha termina com ";" (fora de string), é fim do statement
    if (preg_match('/;\s*$/', $trimmed)) {
        $statements[] = trim($buffer);
        $buffer = '';
    }
}
if (trim($buffer)) $statements[] = trim($buffer);

$executed = 0;
$errors = [];

foreach ($statements as $stmt) {
    if ($stmt === '') continue;
    try {
        $pdo->exec($stmt);
        $executed++;
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        // Ignorar erros de duplicação (migrações idempotentes)
        if (preg_match('/1060|1050|1061|already exists|Duplicate/i', $msg)) continue;
        $errors[] = mb_substr($stmt, 0, 60) . '... → ' . $msg;
    }
}

$total = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "\n✓ Instalação concluída!\n";
echo "Tabelas: " . count($total) . "\n";
echo "Statements: {$executed}\n";

try {
    $users = $pdo->query("SELECT email, tipo FROM usuarios ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $u) echo "  → {$u['email']} ({$u['tipo']})\n";
} catch (PDOException $e) {
    echo "⚠ Não foi possível listar usuários: " . $e->getMessage() . "\n";
}

if ($errors) {
    echo "\n⚠ Erros (" . count($errors) . "):\n";
    foreach (array_slice($errors, 0, 10) as $e) echo "  · {$e}\n";
}

echo "\nRemova o setup.php ou troque a SETUP_KEY.\n";
