<?php
/**
 * Serviço de Autenticação — KROMA PRINT ERP
 */

namespace App\Services;

class Auth
{
    /**
     * Verifica se o usuário está autenticado
     */
    public static function check(): bool
    {
        return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
    }

    /**
     * Retorna o usuário autenticado
     */
    public static function usuario(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    /**
     * Retorna o ID do usuário autenticado
     */
    public static function id(): ?int
    {
        return $_SESSION['usuario_id'] ?? null;
    }

    /**
     * Retorna o perfil do usuário autenticado
     */
    public static function perfil(): ?string
    {
        return $_SESSION['usuario']['perfil'] ?? null;
    }

    /**
     * Verifica se o usuário tem determinado perfil
     */
    public static function temPerfil(string|array $perfis): bool
    {
        $perfilAtual = self::perfil();
        if (!$perfilAtual) return false;

        if (is_array($perfis)) {
            return in_array($perfilAtual, $perfis);
        }

        return $perfilAtual === $perfis;
    }

    /**
     * Verifica se o usuário tem determinada permissão
     */
    public static function pode(string $permissao): bool
    {
        $permissoes = $_SESSION['permissoes'] ?? [];
        return in_array($permissao, $permissoes) || self::temPerfil('administrador');
    }

    /**
     * Realiza o login do usuário
     */
    public static function login(array $usuario, array $permissoes = []): void
    {
        session_regenerate_id(true);

        $_SESSION['usuario_id']  = $usuario['id'];
        $_SESSION['usuario']     = $usuario;
        $_SESSION['permissoes']  = $permissoes;
        $_SESSION['login_time']  = time();
        $_SESSION['last_active'] = time();

        // Registra log de acesso
        self::registrarLog($usuario['id'], 'login');
    }

    /**
     * Realiza o logout do usuário
     */
    public static function logout(): void
    {
        if (self::check()) {
            self::registrarLog(self::id(), 'logout');
        }

        $_SESSION = [];
        session_destroy();
    }

    /**
     * Verifica se a sessão expirou
     */
    public static function verificarSessao(): bool
    {
        if (!self::check()) return false;

        $lastActive = $_SESSION['last_active'] ?? 0;
        if ((time() - $lastActive) > SESSION_LIFETIME) {
            self::logout();
            return false;
        }

        $_SESSION['last_active'] = time();
        return true;
    }

    /**
     * Gera um token CSRF
     */
    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Valida o token CSRF
     */
    public static function verificarCsrf(string $token): bool
    {
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    /**
     * Verifica se o usuário está bloqueado por tentativas de login
     */
    public static function estaBloqueado(string $email): bool
    {
        try {
            $stmt = db()->prepare(
                "SELECT tentativas, bloqueado_ate FROM login_tentativas 
                 WHERE email = ? AND bloqueado_ate > NOW()"
            );
            $stmt->execute([$email]);
            $registro = $stmt->fetch();

            return !empty($registro);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Registra tentativa de login falha
     */
    public static function registrarTentativaFalha(string $email): void
    {
        try {
            $stmt = db()->prepare(
                "INSERT INTO login_tentativas (email, tentativas, ultima_tentativa) 
                 VALUES (?, 1, NOW()) 
                 ON DUPLICATE KEY UPDATE 
                    tentativas = tentativas + 1,
                    ultima_tentativa = NOW(),
                    bloqueado_ate = IF(tentativas + 1 >= ?, DATE_ADD(NOW(), INTERVAL ? SECOND), NULL)"
            );
            $stmt->execute([$email, LOGIN_MAX_ATTEMPTS, LOGIN_BLOCK_TIME]);
        } catch (\Exception $e) {
            // Falha silenciosa
        }
    }

    /**
     * Limpa tentativas de login após login bem-sucedido
     */
    public static function limparTentativas(string $email): void
    {
        try {
            $stmt = db()->prepare("DELETE FROM login_tentativas WHERE email = ?");
            $stmt->execute([$email]);
        } catch (\Exception $e) {
            // Falha silenciosa
        }
    }

    /**
     * Registra log de acesso
     */
    private static function registrarLog(int $usuarioId, string $acao): void
    {
        try {
            $stmt = db()->prepare(
                "INSERT INTO logs_acesso (usuario_id, acao, ip, user_agent, created_at) 
                 VALUES (?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                $usuarioId,
                $acao,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
            ]);
        } catch (\Exception $e) {
            // Falha silenciosa
        }
    }

    /**
     * Registra uma ação de auditoria
     */
    public static function registrarAuditoria(string $tabela, string $acao, int $registroId, ?array $dadosAntigos = null, ?array $dadosNovos = null): void
    {
        try {
            $stmt = db()->prepare(
                "INSERT INTO logs_acoes (usuario_id, tabela, acao, registro_id, dados_antigos, dados_novos, ip, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                self::id(),
                $tabela,
                $acao,
                $registroId,
                $dadosAntigos ? json_encode($dadosAntigos, JSON_UNESCAPED_UNICODE) : null,
                $dadosNovos ? json_encode($dadosNovos, JSON_UNESCAPED_UNICODE) : null,
                $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
        } catch (\Exception $e) {
            // Falha silenciosa
        }
    }
}
