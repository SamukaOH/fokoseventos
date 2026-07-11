<?php
// ============================================================
// FOKOS EVENTOS — Instalador do banco (Railway)
// Uso ÚNICO: /setup.php?key=SUA_SETUP_KEY
// Requisitos: variável de ambiente SETUP_KEY definida no serviço
// Segurança: só roda se o banco estiver VAZIO (sem tabelas)
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
if (count($tabelas) > 0) {
    die("O banco já possui " . count($tabelas) . " tabelas — instalação abortada por segurança.\nPara reinstalar, apague as tabelas manualmente antes.\n");
}

$sqlFile = __DIR__ . '/instalacao_completa.sql';
if (!file_exists($sqlFile)) { http_response_code(500); die("instalacao_completa.sql não encontrado.\n"); }

$sql = file_get_contents($sqlFile);
// remover comandos de criação/uso de database (a Railway já fornece o schema)
$sql = preg_replace('/^\s*(CREATE DATABASE|USE)\b.*?;/mi', '', $sql);

$pdo->exec($sql);

$total = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "✓ Instalação concluída!\n";
echo "Tabelas criadas: " . count($total) . "\n";
$users = $pdo->query("SELECT email, tipo FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) echo "  usuário: {$u['email']} ({$u['tipo']})\n";
echo "\nIMPORTANTE: por segurança, remova o setup.php do repositório ou troque a SETUP_KEY após instalar.\n";
