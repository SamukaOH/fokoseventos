<?php
// ============================================================
// FOKOS EVENTOS — Auth Controller
// ============================================================

class AuthController {

    public function loginPage(): void {
        if (isLoggedIn()) {
            $this->redirectByRole();
        }
        include APP_PATH . '/views/auth/login.php';
    }

    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['erro' => 'Método não permitido.'], 405);
        }

        $ip = clientIp();
        if (!RateLimiter::check('login_' . $ip, RATE_LIMIT_MAX, RATE_LIMIT_TIME)) {
            jsonResponse(['erro' => 'Muitas tentativas. Aguarde alguns minutos.'], 429);
        }

        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $csrf  = $_POST['_csrf'] ?? '';

        if (!verifyCsrf($csrf)) {
            jsonResponse(['erro' => 'Token inválido. Recarregue a página.'], 403);
        }
        if (empty($email) || empty($senha)) {
            jsonResponse(['erro' => 'Preencha e-mail e senha.'], 422);
        }

        $user = Database::fetchOne(
            "SELECT * FROM usuarios WHERE email = ? AND status = 'ativo' LIMIT 1",
            [$email]
        );

        // Senha demo: password_verify com hash padrão OU hash_equals para demo
        $senhaOk = $user && password_verify($senha, $user['senha']);

        if (!$senhaOk) {
            jsonResponse(['erro' => 'E-mail ou senha incorretos.'], 401);
        }

        RateLimiter::reset('login_' . $ip);

        // Inicia sessão
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['user_tipo'] = $user['tipo'];
        $_SESSION['user_foto'] = $user['foto'] ?? null;

        Database::query("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?", [$user['id']]);
        logActivity('Login efetuado', 'auth', $user['id']);

        $redirect = $user['tipo'] === 'admin' ? APP_URL . '/dashboard' : APP_URL . '/motorista';
        jsonResponse(['sucesso' => true, 'redirect' => $redirect]);
    }

    public function logout(): void {
        if (isLoggedIn()) {
            logActivity('Logout', 'auth', $_SESSION['user_id']);
        }
        session_destroy();
        header('Location: ' . APP_URL . '/');
        exit;
    }

    private function redirectByRole(): void {
        $url = $_SESSION['user_tipo'] === 'admin' ? APP_URL . '/dashboard' : APP_URL . '/motorista';
        header('Location: ' . $url);
        exit;
    }
}
