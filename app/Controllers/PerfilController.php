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
                "SELECT p.*,
                        SUM(CASE WHEN pm.pode_ver = 1 THEN 1 ELSE 0 END) AS total_ver,
                        SUM(CASE WHEN pm.pode_criar = 1 THEN 1 ELSE 0 END) AS total_criar,
                        SUM(CASE WHEN pm.pode_editar = 1 THEN 1 ELSE 0 END) AS total_editar,
                        SUM(CASE WHEN pm.pode_excluir = 1 THEN 1 ELSE 0 END) AS total_excluir
                 FROM perfis p
                 LEFT JOIN permissoes pm ON pm.perfil_id = p.id
                 GROUP BY p.id
                 ORDER BY p.nivel, p.label"
            )->fetchAll();
        } catch (\Exception $e) {
            $perfis = [];
        }

        $titulo = 'Perfis';
        $subtitulo = 'Permissões por perfil de acesso';
        $breadcrumbs = [['label' => 'Perfis', 'url' => '']];
        $headerActions = '<a href="' . APP_URL . '/auditoria" class="btn btn-secondary btn-sm"><i class="bi bi-clipboard-data"></i> Auditoria</a>';

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

        $grupos = [];
        foreach ($modulos as $modulo) {
            $grupo = $modulo['grupo'] ?: 'Outros';
            $grupos[$grupo][] = $modulo;
        }

        $resumo = [
            'ver' => array_sum(array_map(fn($p) => (int)$p['pode_ver'], $permissoesRows)),
            'criar' => array_sum(array_map(fn($p) => (int)$p['pode_criar'], $permissoesRows)),
            'editar' => array_sum(array_map(fn($p) => (int)$p['pode_editar'], $permissoesRows)),
            'excluir' => array_sum(array_map(fn($p) => (int)$p['pode_excluir'], $permissoesRows)),
        ];

        $titulo = 'Permissões';
        $subtitulo = 'Perfil: ' . $perfil['label'];
        $breadcrumbs = [
            ['label' => 'Perfis', 'url' => '/perfis'],
            ['label' => $perfil['label'], 'url' => ''],
        ];
        $headerActions = '<a href="' . APP_URL . '/auditoria" class="btn btn-secondary btn-sm"><i class="bi bi-clipboard-data"></i> Auditoria</a>';

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
            $perfil = $this->buscarPerfil($id);
            if (!$perfil) {
                $_SESSION['flash_error'] = 'Perfil não encontrado.';
                header('Location: ' . APP_URL . '/perfis');
                exit;
            }

            $modulos = db()->query("SELECT slug FROM modulos")->fetchAll();
            $pdo = db();
            $permissoesAntigas = $this->permissoesNormalizadas($id);
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
                $podeCriar = isset($perms['criar']) ? 1 : 0;
                $podeEditar = isset($perms['editar']) ? 1 : 0;
                $podeExcluir = isset($perms['excluir']) ? 1 : 0;
                $podeVer = (isset($perms['ver']) || $podeCriar || $podeEditar || $podeExcluir) ? 1 : 0;

                $stmt->execute([
                    $id,
                    $slug,
                    $podeVer,
                    $podeCriar,
                    $podeEditar,
                    $podeExcluir,
                ]);
            }

            $pdo->commit();
            $permissoesNovas = $this->permissoesNormalizadas($id);
            Auth::registrarAuditoria('permissoes', 'editar_perfil', (int)$id, $permissoesAntigas, $permissoesNovas);
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

    private function permissoesNormalizadas(string $perfilId): array
    {
        try {
            $stmt = db()->prepare(
                "SELECT modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir
                 FROM permissoes
                 WHERE perfil_id = ?
                 ORDER BY modulo_slug"
            );
            $stmt->execute([$perfilId]);
            $linhas = $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }

        $dados = [];
        foreach ($linhas as $linha) {
            $dados[$linha['modulo_slug']] = [
                'ver' => (int)$linha['pode_ver'],
                'criar' => (int)$linha['pode_criar'],
                'editar' => (int)$linha['pode_editar'],
                'excluir' => (int)$linha['pode_excluir'],
            ];
        }
        return $dados;
    }
}
