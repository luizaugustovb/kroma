<?php
/**
 * Controlador de Autenticação — KROMA PRINT ERP
 */

namespace App\Controllers;

use App\Services\Auth;

class AuthController
{
    /**
     * Exibe o formulário de login
     */
    public function showLogin(): void
    {
        if (Auth::check()) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }

        $titulo = 'Acesso ao Sistema';
        ob_start();
        require APP_PATH . '/Views/auth/login.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/auth.php';
    }

    /**
     * Processa o login
     */
    public function login(): void
    {
        if (Auth::check()) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }

        // Valida CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::verificarCsrf($token)) {
            $_SESSION['flash_error'] = 'Token de segurança inválido. Tente novamente.';
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $email = trim(strtolower($_POST['email'] ?? ''));
        $senha = $_POST['senha'] ?? '';

        // Verifica campos
        if (empty($email) || empty($senha)) {
            $_SESSION['flash_error'] = 'Preencha e-mail e senha.';
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        // Verifica bloqueio
        if (Auth::estaBloqueado($email)) {
            $_SESSION['flash_error'] = 'Conta temporariamente bloqueada por excesso de tentativas. Aguarde 15 minutos.';
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        // Busca o usuário
        try {
            $stmt = db()->prepare(
                "SELECT u.*, p.nome AS perfil FROM usuarios u 
                 JOIN perfis p ON p.id = u.perfil_id 
                 WHERE u.email = ? AND u.ativo = 1"
            );
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro interno. Tente novamente.';
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        // Verifica senha
        if (!$usuario || !password_verify($senha, $usuario['senha'])) {
            Auth::registrarTentativaFalha($email);
            $_SESSION['flash_error'] = 'E-mail ou senha incorretos.';
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        // Login bem-sucedido
        Auth::limparTentativas($email);

        // Carrega permissões
        $permissoes = $this->carregarPermissoes($usuario['perfil_id']);

        Auth::login($usuario, $permissoes);

        // Atualiza último acesso
        try {
            $stmt = db()->prepare("UPDATE usuarios SET ultimo_acesso = NOW(), ip_ultimo = ? WHERE id = ?");
            $stmt->execute([$_SERVER['REMOTE_ADDR'] ?? '', $usuario['id']]);
        } catch (\Exception $e) {}

        // Redireciona
        $redirect = $_SESSION['redirect_after_login'] ?? (APP_URL . '/dashboard');
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
        exit;
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        Auth::logout();
        header('Location: ' . APP_URL . '/login');
        exit;
    }

    /**
     * Exibe formulário de esqueci a senha
     */
    public function showEsqueciSenha(): void
    {
        $titulo = 'Recuperar Senha';
        ob_start();
        require APP_PATH . '/Views/auth/esqueci_senha.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/auth.php';
    }

    /**
     * Processa solicitação de recuperação de senha
     */
    public function esqueciSenha(): void
    {
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $_SESSION['flash_error'] = 'Informe seu e-mail.';
            header('Location: ' . APP_URL . '/esqueci-senha');
            exit;
        }

        // Sempre mostra mensagem genérica por segurança
        $_SESSION['flash_success'] = 'Se o e-mail estiver cadastrado, você receberá as instruções em breve.';
        header('Location: ' . APP_URL . '/esqueci-senha');
        exit;
    }

    /**
     * Carrega as permissões do perfil
     */
    private function carregarPermissoes(int $perfilId): array
    {
        try {
            $stmt = db()->prepare(
                "SELECT modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir 
                 FROM permissoes WHERE perfil_id = ?"
            );
            $stmt->execute([$perfilId]);
            $rows = $stmt->fetchAll();

            $permissoes = [];
            foreach ($rows as $row) {
                if ($row['pode_ver'])    $permissoes[] = $row['modulo_slug'];
                if ($row['pode_criar'])  $permissoes[] = $row['modulo_slug'] . '.criar';
                if ($row['pode_editar']) $permissoes[] = $row['modulo_slug'] . '.editar';
                if ($row['pode_excluir']) $permissoes[] = $row['modulo_slug'] . '.excluir';
            }

            return $permissoes;
        } catch (\Exception $e) {
            return [];
        }
    }
}
