<?php
/**
 * Controlador de Usuários — KROMA PRINT ERP
 */

namespace App\Controllers;

use App\Services\Auth;
use App\Middleware\AuthMiddleware;

class UsuarioController
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    public function index(): void
    {
        AuthMiddleware::requerPerfil(['administrador', 'diretor', 'gerente', 'rh']);

        try {
            $stmt = db()->query(
                "SELECT u.*, p.label AS perfil_label, c.nome AS cliente_nome FROM usuarios u
                 JOIN perfis p ON p.id = u.perfil_id
                 LEFT JOIN clientes c ON c.id = u.cliente_id
                 ORDER BY u.nome"
            );
            $usuarios = $stmt->fetchAll();
        } catch (\Exception $e) { $usuarios = []; }

        $titulo    = 'Usuários';
        $subtitulo = 'Gerenciamento de usuários e acessos';
        $headerActions = Auth::temPerfil(['administrador', 'diretor']) ?
            '<a href="' . APP_URL . '/usuarios/novo" class="btn btn-primary"><i class="bi bi-person-plus"></i> Novo Usuário</a>' : '';

        ob_start();
        require APP_PATH . '/Views/usuarios/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function novo(): void
    {
        AuthMiddleware::requerPerfil(['administrador', 'diretor']);
        $usuario  = [];
        $perfis   = $this->getPerfis();
        $clientes = $this->getClientes();
        $titulo   = 'Novo Usuário';
        $breadcrumbs = [['label' => 'Usuários', 'url' => '/usuarios'], ['label' => 'Novo', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/usuarios/form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criar(): void
    {
        AuthMiddleware::requerPerfil(['administrador', 'diretor']);

        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/usuarios/novo');
            exit;
        }

        $campos = $this->extrairCampos();

        if (empty($campos['nome']) || empty($campos['email'])) {
            $_SESSION['flash_error'] = 'Nome e e-mail são obrigatórios.';
            header('Location: ' . APP_URL . '/usuarios/novo');
            exit;
        }

        $senha = $_POST['senha'] ?? '';
        if (strlen($senha) < 8) {
            $_SESSION['flash_error'] = 'A senha deve ter pelo menos 8 caracteres.';
            header('Location: ' . APP_URL . '/usuarios/novo');
            exit;
        }

        $campos['senha'] = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);

        try {
            $colunas = implode(', ', array_keys($campos));
            $placeholders = ':' . implode(', :', array_keys($campos));
            $stmt = db()->prepare("INSERT INTO usuarios ($colunas, created_at) VALUES ($placeholders, NOW())");
            $stmt->execute($campos);
            $id = db()->lastInsertId();

            Auth::registrarAuditoria('usuarios', 'criar', $id);
            $_SESSION['flash_success'] = 'Usuário criado com sucesso!';
            header('Location: ' . APP_URL . '/usuarios');
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $_SESSION['flash_error'] = 'Este e-mail já está cadastrado.';
            } else {
                $_SESSION['flash_error'] = 'Erro: ' . $e->getMessage();
            }
            header('Location: ' . APP_URL . '/usuarios/novo');
        }
        exit;
    }

    public function ver(string $id): void
    {
        AuthMiddleware::requerPerfil(['administrador', 'diretor', 'gerente', 'rh']);
        $usuario = $this->buscarPorId($id);

        if (!$usuario) {
            $_SESSION['flash_error'] = 'Usuário não encontrado.';
            header('Location: ' . APP_URL . '/usuarios');
            exit;
        }

        $titulo = $usuario['nome'];
        $subtitulo = 'Ficha do usuário';
        $breadcrumbs = [
            ['label' => 'Usuários', 'url' => '/usuarios'],
            ['label' => $usuario['nome'], 'url' => ''],
        ];
        $headerActions = '<a href="' . APP_URL . '/usuarios/' . $id . '/editar" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> Editar</a>';

        ob_start();
        require APP_PATH . '/Views/usuarios/show.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function editar(string $id): void
    {
        AuthMiddleware::requerPerfil(['administrador', 'diretor']);
        $usuario = $this->buscarPorId($id);

        if (!$usuario) {
            $_SESSION['flash_error'] = 'Usuário não encontrado.';
            header('Location: ' . APP_URL . '/usuarios');
            exit;
        }

        $perfis      = $this->getPerfis();
        $clientes    = $this->getClientes();
        $titulo      = 'Editar Usuário';
        $breadcrumbs = [['label' => 'Usuários', 'url' => '/usuarios'], ['label' => $usuario['nome'], 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/usuarios/form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function atualizar(string $id): void
    {
        AuthMiddleware::requerPerfil(['administrador', 'diretor']);

        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/usuarios/' . $id . '/editar');
            exit;
        }

        $campos = $this->extrairCampos();
        unset($campos['senha']); // Senha em campo separado

        // Atualiza senha apenas se preenchida
        $novaSenha = $_POST['senha'] ?? '';
        if (!empty($novaSenha)) {
            if (strlen($novaSenha) < 8) {
                $_SESSION['flash_error'] = 'A senha deve ter pelo menos 8 caracteres.';
                header('Location: ' . APP_URL . '/usuarios/' . $id . '/editar');
                exit;
            }
            $campos['senha'] = password_hash($novaSenha, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        try {
            $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($campos)));
            $campos['id_reg'] = $id;
            $stmt = db()->prepare("UPDATE usuarios SET $sets, updated_at = NOW() WHERE id = :id_reg");
            $stmt->execute($campos);

            Auth::registrarAuditoria('usuarios', 'editar', $id);
            $_SESSION['flash_success'] = 'Usuário atualizado com sucesso!';
            header('Location: ' . APP_URL . '/usuarios');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro: ' . $e->getMessage();
            header('Location: ' . APP_URL . '/usuarios/' . $id . '/editar');
        }
        exit;
    }

    public function toggleStatus(string $id): void
    {
        AuthMiddleware::requerPerfil(['administrador', 'diretor']);

        try {
            $stmt = db()->prepare("UPDATE usuarios SET ativo = NOT ativo WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash_success'] = 'Status do usuário alterado.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao alterar status.';
        }

        header('Location: ' . APP_URL . '/usuarios');
        exit;
    }

    public function excluir(string $id): void
    {
        AuthMiddleware::requerPerfil(['administrador', 'diretor']);

        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/usuarios');
            exit;
        }

        if ((int)$id === (int)Auth::id()) {
            $_SESSION['flash_warning'] = 'Você não pode inativar o próprio usuário.';
            header('Location: ' . APP_URL . '/usuarios');
            exit;
        }

        try {
            $stmt = db()->prepare("UPDATE usuarios SET ativo = 0, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            Auth::registrarAuditoria('usuarios', 'excluir', (int)$id);
            $_SESSION['flash_success'] = 'Usuário inativado.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao inativar usuário.';
        }

        header('Location: ' . APP_URL . '/usuarios');
        exit;
    }

    public function meuPerfil(): void
    {
        $usuario = Auth::usuario();
        $perfis  = [];
        $titulo  = 'Meu Perfil';

        ob_start();
        require APP_PATH . '/Views/usuarios/meu_perfil.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function atualizarMeuPerfil(): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/perfil');
            exit;
        }

        $campos = [
            'nome'      => trim($_POST['nome'] ?? ''),
            'telefone'  => $_POST['telefone'] ?? '',
            'whatsapp'  => $_POST['whatsapp'] ?? '',
        ];

        $novaSenha = $_POST['senha_nova'] ?? '';
        $senhaAtual = $_POST['senha_atual'] ?? '';

        if (!empty($novaSenha)) {
            $stmt = db()->prepare("SELECT senha FROM usuarios WHERE id = ?");
            $stmt->execute([Auth::id()]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($senhaAtual, $hash)) {
                $_SESSION['flash_error'] = 'Senha atual incorreta.';
                header('Location: ' . APP_URL . '/perfil');
                exit;
            }

            if (strlen($novaSenha) < 8) {
                $_SESSION['flash_error'] = 'A nova senha deve ter pelo menos 8 caracteres.';
                header('Location: ' . APP_URL . '/perfil');
                exit;
            }

            $campos['senha'] = password_hash($novaSenha, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        try {
            $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($campos)));
            $campos['id_reg'] = Auth::id();
            $stmt = db()->prepare("UPDATE usuarios SET $sets WHERE id = :id_reg");
            $stmt->execute($campos);

            // Atualiza sessão
            $_SESSION['usuario']['nome'] = $campos['nome'];
            $_SESSION['flash_success'] = 'Perfil atualizado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/perfil');
        exit;
    }

    // Helpers
    private function buscarPorId(string $id): ?array
    {
        try {
            $stmt = db()->prepare(
                "SELECT u.*, c.nome AS cliente_nome
                 FROM usuarios u
                 LEFT JOIN clientes c ON c.id = u.cliente_id
                 WHERE u.id = ?"
            );
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) { return null; }
    }

    private function getPerfis(): array
    {
        try {
            return db()->query("SELECT * FROM perfis WHERE ativo = 1 ORDER BY nivel")->fetchAll();
        } catch (\Exception $e) { return []; }
    }

    private function getClientes(): array
    {
        try {
            return db()->query("SELECT id, nome, email, whatsapp FROM clientes WHERE status = 'ativo' ORDER BY nome LIMIT 500")->fetchAll();
        } catch (\Exception $e) { return []; }
    }

    private function extrairCampos(): array
    {
        return [
            'perfil_id'  => (int)($_POST['perfil_id'] ?? 1),
            'nome'       => trim($_POST['nome'] ?? ''),
            'email'      => trim(strtolower($_POST['email'] ?? '')),
            'telefone'   => $_POST['telefone'] ?? '',
            'whatsapp'   => $_POST['whatsapp'] ?? '',
            'cargo'      => trim($_POST['cargo'] ?? ''),
            'setor'      => trim($_POST['setor'] ?? ''),
            'cliente_id' => !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null,
            'ativo'      => isset($_POST['ativo']) ? 1 : 0,
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];
    }
}
