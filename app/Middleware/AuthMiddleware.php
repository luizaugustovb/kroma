<?php
/**
 * Middleware de autenticação — KROMA PRINT ERP
 * Protege rotas que exigem login
 */

namespace App\Middleware;

use App\Services\Auth;

class AuthMiddleware
{
    /**
     * Verifica autenticação e redireciona se necessário
     */
    public static function handle(): void
    {
        if (!Auth::check() || !Auth::verificarSessao()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    /**
     * Verifica permissão específica, redireciona com erro se não tiver
     */
    public static function requer(string $permissao): void
    {
        self::handle();

        if (!Auth::pode($permissao)) {
            $_SESSION['flash_error'] = 'Você não tem permissão para acessar esta área.';
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
    }

    /**
     * Verifica se o usuário tem um dos perfis exigidos
     */
    public static function requerPerfil(string|array $perfis): void
    {
        self::handle();

        if (!Auth::temPerfil($perfis)) {
            $_SESSION['flash_error'] = 'Acesso restrito ao seu perfil.';
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
    }
}
