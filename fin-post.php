<?php
// Endpoint FÍSICO POST do financeiro — contorna o roteador
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/config/DbSessionHandler.php';
require_once __DIR__ . '/app/helpers/helpers.php';
require_once __DIR__ . '/app/controllers/FinanceiroController.php';

startSession();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['erro' => 'Sessão expirada.', 'redirect' => APP_URL . '/']);
    exit;
}

$acao = $_GET['acao'] ?? '';
$c = new FinanceiroController();

try {
    switch ($acao) {
        case 'criar':
            $c->criar();
            break;
        case 'delete':
            $c->deletar($_GET['id'] ?? '0');
            break;
        case 'preco':
            $c->atualizarPreco($_GET['id'] ?? '0');
            break;
        default:
            header('Content-Type: application/json');
            echo json_encode(['erro' => 'Ação inválida.']);
    }
} catch (\Throwable $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()]);
}
