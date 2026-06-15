<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class PerfilController
{
    public function __construct()
    {
        AuthMiddleware::requerPerfil(['administrador', 'diretor']);
    }

    public function index(): void
    {
        try {
            $perfis = db()->query(
                "SELECT p.*, COUNT(pm.id) AS total_permissoes
                 FROM perfis p
                 LEFT JOIN permissoes pm ON pm.perfil_id = p.id AND pm.pode_ver = 1
                 GROUP BY p.id
                 ORDER BY p.nivel, p.label"
            )->fetchAll();
        } catch (\Exception $e) {
            $perfis = [];
        }

        $titulo = 'Perfis';
        $subtitulo = 'Permissões por perfil de acesso';
        $breadcrumbs = [['label' => 'Perfis', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/perfis/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function permissoes(string $id): void
    {
        $perfil = $this->buscarPerfil($id);
        if (!$perfil) {
            $_SESSION['flash_error'] = 'Perfil não encontrado.';
            header('Location: ' . APP_URL . '/perfis');
            exit;
        }

        try {
            $modulos = db()->query("SELECT * FROM modulos ORDER BY grupo, ordem, nome")->fetchAll();
            $stmt = db()->prepare("SELECT * FROM permissoes WHERE perfil_id = ?");
            $stmt->execute([$id]);
            $permissoesRows = $stmt->fetchAll();
        } catch (\Exception $e) {
            $modulos = [];
            $permissoesRows = [];
        }

        $permissoes = [];
        foreach ($permissoesRows as $row) {
            $permissoes[$row['modulo_slug']] = $row;
        }

        $titulo = 'Permissões';
        $subtitulo = 'Perfil: ' . $perfil['label'];
        $breadcrumbs = [
            ['label' => 'Perfis', 'url' => '/perfis'],
            ['label' => $perfil['label'], 'url' => ''],
        ];

        ob_start();
        require APP_PATH . '/Views/perfis/permissoes.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function salvarPermissoes(string $id): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/perfis/' . $id . '/permissoes');
            exit;
        }

        try {
            $modulos = db()->query("SELECT slug FROM modulos")->fetchAll();
            $pdo = db();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
                 VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    pode_ver = VALUES(pode_ver),
                    pode_criar = VALUES(pode_criar),
                    pode_editar = VALUES(pode_editar),
                    pode_excluir = VALUES(pode_excluir)"
            );

            foreach ($modulos as $modulo) {
                $slug = $modulo['slug'];
                $perms = $_POST['permissoes'][$slug] ?? [];
                $stmt->execute([
                    $id,
                    $slug,
                    isset($perms['ver']) ? 1 : 0,
                    isset($perms['criar']) ? 1 : 0,
                    isset($perms['editar']) ? 1 : 0,
                    isset($perms['excluir']) ? 1 : 0,
                ]);
            }

            $pdo->commit();
            $_SESSION['flash_success'] = 'Permissões atualizadas.';
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_error'] = 'Erro ao salvar permissões: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/perfis/' . $id . '/permissoes');
        exit;
    }

    private function buscarPerfil(string $id): ?array
    {
        try {
            $stmt = db()->prepare("SELECT * FROM perfis WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
