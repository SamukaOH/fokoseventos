<?php
// ============================================================
// FOKOS EVENTOS — Entry Point / Front Controller
// ============================================================

// Capturar erros PHP e retornar JSON para requisições AJAX
set_exception_handler(function(Throwable $e) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['erro' => $e->getMessage()]);
        exit;
    }
    echo '<pre style="background:#1a0000;color:#ff6b6b;padding:20px;margin:0">';
    echo '<strong>Erro:</strong> ' . htmlspecialchars($e->getMessage()) . "\n";
    echo '<strong>Arquivo:</strong> ' . $e->getFile() . ':' . $e->getLine();
    echo '</pre>';
    exit;
});

set_error_handler(function(int $errno, string $errstr, string $errfile, int $errline) {
    if (!($errno & error_reporting())) return false;
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['erro' => "$errstr em $errfile:$errline"]);
        exit;
    }
    return false;
});

require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/helpers/helpers.php';
require_once __DIR__ . '/app/middlewares/Router.php';
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/DashboardController.php';
require_once __DIR__ . '/app/controllers/DemandasController.php';
require_once __DIR__ . '/app/controllers/EstoqueController.php';
require_once __DIR__ . '/app/controllers/OtherControllers.php';
require_once __DIR__ . '/app/controllers/FinanceiroController.php';

// Instanciar novos controllers
$clientesCtrl = new ClientesController();
$usuariosCtrl = new UsuariosController();

startSession();

// ---- Proteção XSS global ----
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// ---- URI ----
$baseLen = strlen(parse_url(APP_URL, PHP_URL_PATH) ?? '');
$uri     = substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $baseLen) ?: '/';
$method  = $_SERVER['REQUEST_METHOD'];

// ---- Router ----
$router = new Router();

// AUTH
$router->get('/',               [new AuthController(), 'loginPage']);
$router->post('/auth/login',    [new AuthController(), 'login']);
$router->get('/logout',         [new AuthController(), 'logout']);

// ADMIN — Dashboard
$router->get('/dashboard',          [new DashboardController(), 'index']);
$router->get('/api/dashboard',      [new DashboardController(), 'dadosAjax']);

// ADMIN — Demandas
$demCtrl = new DemandasController();
$letCtrl = new LetreirosController();

$router->get('/demandas',                        [$demCtrl, 'index']);
$router->get('/api/demandas',                    [$demCtrl, 'lista']);
$router->post('/api/demandas',                   [$demCtrl, 'criar']);
$router->get('/api/demandas/:id',                [$demCtrl, 'getOne']);
$router->post('/api/demandas/:id',               [$demCtrl, 'update']);
$router->post('/api/demandas/:id/status',        [$demCtrl, 'atualizarStatus']);
$router->post('/api/demandas/:id/status-admin',  [$demCtrl, 'atualizarStatusAdmin']);
$router->post('/api/demandas/:id/editar',        [new DemandasController(), 'editar']);
$router->post('/api/demandas/:id/cancelar',      [$demCtrl, 'cancelar']);
$router->post('/api/demandas/:id/foto',          [$demCtrl, 'uploadFoto']);

// Estoque de letreiros
$router->get('/estoque',                              [new EstoqueController(), 'index']);
$router->get('/api/estoque/letreiros/:id',            [$letCtrl, 'get']);
$router->post('/api/estoque/letreiros/add',           [$letCtrl, 'add']);
$router->post('/api/estoque/letreiros/massa',         [$letCtrl, 'massa']);
$router->post('/api/estoque/letreiros/verificar',     [$letCtrl, 'verificar']);
$router->post('/api/estoque/letreiros/:id/ajustar',   [$letCtrl, 'ajustar']);
$router->post('/api/estoque/letreiros/:id/vender',    [$letCtrl, 'vender']);
$router->post('/api/estoque/letreiros/:id/deletar',   [$letCtrl, 'deletar']);

// ADMIN — Estoque
$router->get('/estoque',            [new EstoqueController(), 'index']);
$router->get('/api/estoque',        [new EstoqueController(), 'lista']);
$router->post('/api/estoque',       [new EstoqueController(), 'criar']);
$router->get('/api/estoque/:id/historico', [new EstoqueController(), 'historico']);
$router->post('/api/estoque/movimentar',   [new EstoqueController(), 'movimentar']);
$router->post('/api/estoque/:id/delete',   [new EstoqueController(), 'deletar']);

// ADMIN — Financeiro
$router->get('/financeiro',                   function(){ (new FinanceiroController())->index(); });
$router->get('/api/financeiro',               function(){ (new FinanceiroController())->lista(); });
$router->post('/api/financeiro',              function(){ (new FinanceiroController())->criar(); });
$router->get('/api/financeiro/dashboard',     function(){ (new FinanceiroController())->dashboard(); });
$router->get('/api/financeiro/precos',        function(){ (new FinanceiroController())->precos(); });
$router->get('/api/financeiro/orcamento/:id', function($id){ (new FinanceiroController())->orcamento($id); });
$router->post('/api/financeiro/precos/:id',   function($id){ (new FinanceiroController())->atualizarPreco($id); });
$router->post('/api/financeiro/:id/delete',   function($id){ (new FinanceiroController())->deletar($id); });

// ADMIN — Motoristas
$router->get('/motoristas',         [new MotoristasController(), 'index']);
$router->get('/api/motoristas',                  [new MotoristasController(), 'lista']);
$router->post('/api/motoristas',                 [new MotoristasController(), 'criar']);
$router->get('/api/motoristas/:id/demandas',     [new MotoristasController(), 'demandas']);
$router->post('/api/motoristas/:id/delete',      [new MotoristasController(), 'deletar']);
$router->post('/api/motoristas/:id/disponivel',  [new MotoristasController(), 'setDisponivel']);

// Páginas simples (em desenvolvimento)
foreach (['calendario','clientes','relatorios','usuarios','logs'] as $pg) {
    $router->get("/{$pg}", function() use ($pg) {
        requireAdmin();
        $currentPage = $pg;
        $pageContent = APP_PATH . "/views/{$pg}/index.php";
        include APP_PATH . '/views/layout.php';
    });
}

// Clientes API
$router->get('/api/clientes/:id',         [$clientesCtrl, 'get']);
$router->post('/api/clientes',            [$clientesCtrl, 'salvar']);
$router->post('/api/clientes/:id/delete', [$clientesCtrl, 'deletar']);

// Usuários API
$router->post('/api/usuarios',               [$usuariosCtrl, 'criar']);
$router->post('/api/usuarios/:id/senha',     [$usuariosCtrl, 'alterarSenha']);
$router->post('/api/usuarios/:id/desativar', [$usuariosCtrl, 'desativar']);
$router->post('/api/usuarios/:id/ativar',    [$usuariosCtrl, 'ativar']);

// Notificações
$router->get('/api/notificacoes',   [new NotificacoesController(), 'lista']);
$router->post('/api/notificacoes/lida', [new NotificacoesController(), 'marcarLida']);
$router->post('/api/notificacoes/todas-lidas', [new NotificacoesController(), 'marcarTodasLidas']);

// SPA — partial page loader
$router->get('/api/page/:page', function(string $page) {
    requireAdmin();
    $allowed = ['dashboard','demandas','estoque','financeiro','motoristas'];
    if (!in_array($page, $allowed)) {
        jsonResponse(['erro' => 'Página inválida.'], 404);
    }
    ob_start();
    include APP_PATH . "/views/{$page}/index.php";
    $html = ob_get_clean();
    jsonResponse(['html' => $html]);
});

// MOTORISTA — Área separada
$router->get('/motorista', function() {
    requireLogin();
    if ($_SESSION['user_tipo'] !== 'motorista') {
        header('Location: ' . APP_URL . '/dashboard');
        exit;
    }
    include APP_PATH . '/views/motorista/index.php';
});

$router->dispatch($method, $uri);