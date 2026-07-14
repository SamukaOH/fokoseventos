<?php
// ============================================================
// Endpoint FÍSICO do financeiro — contorna o roteador
// Servido direto pelo Nginx (location ~ \.php$), igual check.php
// ============================================================
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/config/DbSessionHandler.php';
require_once __DIR__ . '/app/helpers/helpers.php';
require_once __DIR__ . '/app/controllers/FinanceiroController.php';

startSession();

// Não logado? JSON de erro (o frontend trata)
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['erro' => 'Sessão expirada.', 'redirect' => APP_URL . '/']);
    exit;
}

// Executar o método correto conforme o parâmetro ?acao=
$acao = $_GET['acao'] ?? 'lista';
$c = new FinanceiroController();

try {
    switch ($acao) {
        case 'dashboard':
            $c->dashboard();
            break;
        case 'precos':
            $c->precos();
            break;
        case 'orcamento':
            $c->orcamento($_GET['id'] ?? '0');
            break;
        default:
            $c->lista();
    }
} catch (\Throwable $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()]);
}
