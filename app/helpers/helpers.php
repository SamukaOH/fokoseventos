<?php
// ============================================================
// FOKOS EVENTOS — Helpers de Sessão, Auth, CSRF, Log
// ============================================================

// ---- Inicialização segura de sessão ----
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        // Sessões no banco (sobrevivem a deploys na Railway).
        // Fallback para arquivo se a tabela não existir ainda.
        if (class_exists('DbSessionHandler')) {
            try {
                Database::getInstance(); // garante conexão
                session_set_save_handler(new DbSessionHandler(), true);
            } catch (\Throwable $e) {
                $sessDir = sys_get_temp_dir() . '/fokos_sessions';
                if (!is_dir($sessDir)) @mkdir($sessDir, 0777, true);
                if (is_dir($sessDir) && is_writable($sessDir)) session_save_path($sessDir);
            }
        }

        // Detectar HTTPS (direto ou via proxy da Railway)
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
              || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
              || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');

        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => $https,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }
}

// ---- Auth ----
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        if (isAjax()) {
            jsonResponse(['erro' => 'Sessão expirada.', 'redirect' => APP_URL . '/'], 401);
        }
        header('Location: ' . APP_URL . '/');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['user_tipo'] !== 'admin') {
        if (isAjax()) {
            jsonResponse(['erro' => 'Acesso negado.'], 403);
        }
        header('Location: ' . APP_URL . '/motorista');
        exit;
    }
}

function getUser(): array {
    return [
        'id'   => $_SESSION['user_id']   ?? 0,
        'nome' => $_SESSION['user_nome'] ?? '',
        'tipo' => $_SESSION['user_tipo'] ?? '',
        'foto' => $_SESSION['user_foto'] ?? null,
    ];
}

// ---- CSRF ----
function csrfToken(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCsrf(string $token): bool {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// ---- JSON Response ----
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ---- Detecção AJAX ----
function isAjax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// ---- Log de Atividade ----
function logActivity(string $acao, string $modulo = '', int $refId = 0): void {
    try {
        $userId = $_SESSION['user_id'] ?? null;
        $ip     = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        Database::query(
            "INSERT INTO logs_atividade (usuario_id, acao, modulo, referencia_id, ip) VALUES (?,?,?,?,?)",
            [$userId, $acao, $modulo, $refId ?: null, $ip]
        );
    } catch (Exception $e) { /* silencioso */ }
}

// ---- Notificação ----
function criarNotificacao(int $userId, string $titulo, string $msg, string $tipo = 'info', string $link = ''): void {
    Database::query(
        "INSERT INTO notificacoes (usuario_id, titulo, mensagem, tipo, link) VALUES (?,?,?,?,?)",
        [$userId, $titulo, $msg, $tipo, $link]
    );
}

// ---- Sanitize ----
function sanitize(mixed $val): string {
    return htmlspecialchars(strip_tags(trim((string)$val)), ENT_QUOTES, 'UTF-8');
}

// ---- Formatar moeda ----
function moeda(float $v): string {
    return 'R$ ' . number_format($v, 2, ',', '.');
}

// ---- Formatar data ----
function dataFormatada(string $date): string {
    if (!$date) return '-';
    return date('d/m/Y', strtotime($date));
}

function dataHoraFormatada(string $dt): string {
    if (!$dt) return '-';
    return date('d/m/Y H:i', strtotime($dt));
}

// ---- Upload de arquivo ----
function uploadArquivo(array $file, string $subdir = ''): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > UPLOAD_MAX_SIZE) return null;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, UPLOAD_ALLOWED)) return null;

    $dir = UPLOAD_PATH . ($subdir ? '/' . trim($subdir, '/') : '');
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $nome = uniqid('fk_', true) . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $dir . '/' . $nome)) {
        return ($subdir ? $subdir . '/' : '') . $nome;
    }
    return null;
}

// ---- IP do cliente ----
function clientIp(): string {
    return $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['REMOTE_ADDR']
        ?? '0.0.0.0';
}
