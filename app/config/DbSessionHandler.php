<?php
// ============================================================
// FOKOS EVENTOS — Session handler no MySQL
// Motivo: em PaaS (Railway), o disco do container é efêmero —
// cada deploy destrói /tmp e derruba todas as sessões em arquivo.
// Guardando no banco, as sessões sobrevivem a deploys/restarts.
// ============================================================

class DbSessionHandler implements SessionHandlerInterface
{
    public function open($path, $name): bool { return true; }
    public function close(): bool { return true; }

    public function read($id): string
    {
        try {
            $row = Database::fetchOne(
                "SELECT data FROM sessions WHERE id = ? AND expires > ?",
                [$id, time()]
            );
            return $row['data'] ?? '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function write($id, $data): bool
    {
        try {
            $expires = time() + (int) SESSION_LIFETIME;
            Database::query(
                "INSERT INTO sessions (id, data, expires) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE data = VALUES(data), expires = VALUES(expires)",
                [$id, $data, $expires]
            );
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function destroy($id): bool
    {
        try {
            Database::query("DELETE FROM sessions WHERE id = ?", [$id]);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function gc($maxlifetime): int
    {
        try {
            Database::query("DELETE FROM sessions WHERE expires < ?", [time()]);
            return 1;
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
