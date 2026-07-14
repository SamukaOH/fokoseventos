<?php
// Auto-prepend: garante que as classes core estejam carregadas
// antes de qualquer script PHP (blindagem para o Nginx + FPM)
require_once '/var/www/html/app/config/config.php';
require_once '/var/www/html/app/config/database.php';
require_once '/var/www/html/app/config/DbSessionHandler.php';
require_once '/var/www/html/app/helpers/helpers.php';
require_once '/var/www/html/app/middlewares/Router.php';

// ═══ Blindagem: qualquer erro fatal em requisição AJAX vira JSON ═══
$__isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($__isAjax) {
    // Erros fatais (parse, memória, etc)
    register_shutdown_function(function() {
        $e = error_get_last();
        if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode(['erro' => 'Erro interno', 'detalhe' => $e['message']]);
        }
    });
    // Exceções não capturadas
    set_exception_handler(function($ex) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(['erro' => 'Exceção', 'detalhe' => $ex->getMessage()]);
        exit;
    });
}
