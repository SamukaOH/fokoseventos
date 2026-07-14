<?php
// Diagnóstico direto — servido pelo Nginx como arquivo .php físico
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/helpers/helpers.php';

startSession();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

echo json_encode([
    'ARQUIVO_FISICO' => 'whoami.php executou direto pelo Nginx',
    'REQUEST_URI'    => $_SERVER['REQUEST_URI'] ?? 'n/a',
    'SCRIPT_NAME'    => $_SERVER['SCRIPT_NAME'] ?? 'n/a',
    'session_id'     => session_id(),
    'user_id'        => $_SESSION['user_id'] ?? null,
    'logged_in'      => isset($_SESSION['user_id']),
    'cookie_secure'  => session_get_cookie_params()['secure'],
    'https_detect'   => [
        'HTTPS'             => $_SERVER['HTTPS'] ?? 'unset',
        'X_FORWARDED_PROTO' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'unset',
    ],
    'cookie_recebido'=> isset($_COOKIE[SESSION_NAME]) ? substr($_COOKIE[SESSION_NAME],0,12).'...' : 'NENHUM',
    'save_path'      => session_save_path() ?: ini_get('session.save_path'),
    'save_writable'  => is_writable(session_save_path() ?: sys_get_temp_dir()),
    'app_url'        => APP_URL,
    'tabela_sessions_existe' => (function(){
        try { Database::query('SELECT 1 FROM sessions LIMIT 1'); return 'SIM'; }
        catch (\Throwable $e) { return 'NAO — rode migrate.php! (' . $e->getMessage() . ')'; }
    })(),
    'handler_ativo' => session_module_name(),
], JSON_PRETTY_PRINT);
