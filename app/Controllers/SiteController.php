<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class SiteController
{
    public function __construct()
    {
        AuthMiddleware::requer('empresa');
    }

    public function index(): void
    {
        $config = $this->config();
        $servicos = $this->query('SELECT * FROM site_servicos ORDER BY ordem, id');
        $portfolio = $this->query('SELECT * FROM site_portfolio ORDER BY ordem, id');

        $titulo = 'Site Público';
        $subtitulo = 'Landing page, SEO, Google Analytics, serviços e portfólio';
        $breadcrumbs = [['label' => 'Administrativo', 'url' => ''], ['label' => 'Site Público', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/site/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function salvarConfig(): void
    {
        $this->validarCsrf('/site');

        try {
            $configAtual = $this->config();
            $heroImage = trim($_POST['hero_image_url'] ?? ($configAtual['hero_image_url'] ?? ''));
            $upload = $this->salvarImagem('hero_arquivo', 'hero');
            if ($upload !== null) {
                $heroImage = $upload;
            }

            $campos = [
                'google_analytics_id' => trim($_POST['google_analytics_id'] ?? ''),
                'google_tag_manager_id' => trim($_POST['google_tag_manager_id'] ?? ''),
                'seo_titulo' => trim($_POST['seo_titulo'] ?? ''),
                'seo_descricao' => trim($_POST['seo_descricao'] ?? ''),
                'seo_keywords' => trim($_POST['seo_keywords'] ?? ''),
                'canonical_url' => trim($_POST['canonical_url'] ?? ''),
                'og_title' => trim($_POST['og_title'] ?? ''),
                'og_description' => trim($_POST['og_description'] ?? ''),
                'og_image' => trim($_POST['og_image'] ?? ''),
                'favicon_url' => trim($_POST['favicon_url'] ?? ''),
                'robots_index' => ($_POST['robots_index'] ?? '') === 'noindex' ? 'noindex' : 'index',
                'robots_follow' => ($_POST['robots_follow'] ?? '') === 'nofollow' ? 'nofollow' : 'follow',
                'google_verification' => trim($_POST['google_verification'] ?? ''),
                'bing_verification' => trim($_POST['bing_verification'] ?? ''),
                'ms_clarity_id' => trim($_POST['ms_clarity_id'] ?? ''),
                'meta_pixel_id' => trim($_POST['meta_pixel_id'] ?? ''),
                'hero_badge' => trim($_POST['hero_badge'] ?? ''),
                'hero_titulo' => trim($_POST['hero_titulo'] ?? ''),
                'hero_subtitulo' => trim($_POST['hero_subtitulo'] ?? ''),
                'hero_cta_texto' => trim($_POST['hero_cta_texto'] ?? 'Solicitar Orçamento'),
                'hero_cta_secundario' => trim($_POST['hero_cta_secundario'] ?? 'Falar no WhatsApp'),
                'hero_image_url' => $heroImage,
            ];

            if (!empty($configAtual['id'])) {
                $sets = implode(', ', array_map(fn($campo) => "$campo = :$campo", array_keys($campos)));
                $campos['id'] = $configAtual['id'];
                db()->prepare("UPDATE site_configuracoes SET $sets, updated_at = NOW() WHERE id = :id")->execute($campos);
            } else {
                $colunas = implode(', ', array_keys($campos));
                $placeholders = ':' . implode(', :', array_keys($campos));
                db()->prepare("INSERT INTO site_configuracoes ($colunas, created_at) VALUES ($placeholders, NOW())")->execute($campos);
            }
            Auth::registrarAuditoria('site_configuracoes', 'salvar', (int)($configAtual['id'] ?? 0), $configAtual, $campos);
            $_SESSION['flash_success'] = 'Configurações do site atualizadas.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao salvar site: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/site');
        exit;
    }

    public function salvarServico(): void
    {
        $this->validarCsrf('/site');

        $id = (int)($_POST['id'] ?? 0);
        $campos = [
            'titulo' => trim($_POST['titulo'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'icone' => trim($_POST['icone'] ?? 'bi-stars') ?: 'bi-stars',
            'ordem' => (int)($_POST['ordem'] ?? 0),
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
        ];

        if ($campos['titulo'] === '') {
            $_SESSION['flash_error'] = 'Informe o título do serviço.';
            header('Location: ' . APP_URL . '/site#servicos');
            exit;
        }

        try {
            if ($id > 0) {
                $campos['id'] = $id;
                db()->prepare('UPDATE site_servicos SET titulo = :titulo, descricao = :descricao, icone = :icone, ordem = :ordem, ativo = :ativo, updated_at = NOW() WHERE id = :id')->execute($campos);
                Auth::registrarAuditoria('site_servicos', 'editar', $id);
            } else {
                db()->prepare('INSERT INTO site_servicos (titulo, descricao, icone, ordem, ativo, created_at) VALUES (:titulo, :descricao, :icone, :ordem, :ativo, NOW())')->execute($campos);
                Auth::registrarAuditoria('site_servicos', 'criar', (int)db()->lastInsertId());
            }
            $_SESSION['flash_success'] = 'Serviço salvo.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao salvar serviço: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/site#servicos');
        exit;
    }

    public function excluirServico(string $id): void
    {
        $this->validarCsrf('/site#servicos');

        try {
            db()->prepare('DELETE FROM site_servicos WHERE id = ?')->execute([(int)$id]);
            Auth::registrarAuditoria('site_servicos', 'excluir', (int)$id);
            $_SESSION['flash_success'] = 'Serviço removido.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao remover serviço: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/site#servicos');
        exit;
    }

    public function salvarPortfolio(): void
    {
        $this->validarCsrf('/site');

        $id = (int)($_POST['id'] ?? 0);

        if (trim($_POST['titulo'] ?? '') === '') {
            $_SESSION['flash_error'] = 'Informe o título do item do portfólio.';
            header('Location: ' . APP_URL . '/site#portfolio');
            exit;
        }

        try {
            $imagem = trim($_POST['imagem_url'] ?? '');
            $upload = $this->salvarImagem('portfolio_arquivo', 'portfolio');
            if ($upload !== null) {
                $imagem = $upload;
            }

            $campos = [
                'titulo' => trim($_POST['titulo'] ?? ''),
                'categoria' => trim($_POST['categoria'] ?? ''),
                'descricao' => trim($_POST['descricao'] ?? ''),
                'imagem_url' => $imagem,
                'ordem' => (int)($_POST['ordem'] ?? 0),
                'ativo' => isset($_POST['ativo']) ? 1 : 0,
            ];

            if ($id > 0) {
                $campos['id'] = $id;
                db()->prepare('UPDATE site_portfolio SET titulo = :titulo, categoria = :categoria, descricao = :descricao, imagem_url = :imagem_url, ordem = :ordem, ativo = :ativo, updated_at = NOW() WHERE id = :id')->execute($campos);
                Auth::registrarAuditoria('site_portfolio', 'editar', $id);
            } else {
                db()->prepare('INSERT INTO site_portfolio (titulo, categoria, descricao, imagem_url, ordem, ativo, created_at) VALUES (:titulo, :categoria, :descricao, :imagem_url, :ordem, :ativo, NOW())')->execute($campos);
                Auth::registrarAuditoria('site_portfolio', 'criar', (int)db()->lastInsertId());
            }
            $_SESSION['flash_success'] = 'Portfólio salvo.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao salvar portfólio: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/site#portfolio');
        exit;
    }

    public function excluirPortfolio(string $id): void
    {
        $this->validarCsrf('/site#portfolio');

        try {
            db()->prepare('DELETE FROM site_portfolio WHERE id = ?')->execute([(int)$id]);
            Auth::registrarAuditoria('site_portfolio', 'excluir', (int)$id);
            $_SESSION['flash_success'] = 'Item do portfólio removido.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao remover portfólio: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/site#portfolio');
        exit;
    }

    private function config(): array
    {
        try {
            return db()->query('SELECT * FROM site_configuracoes ORDER BY id LIMIT 1')->fetch() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function query(string $sql): array
    {
        try {
            return db()->query($sql)->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function salvarImagem(string $campo, string $pasta): ?string
    {
        if (empty($_FILES[$campo]) || ($_FILES[$campo]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($_FILES[$campo]['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Erro no upload da imagem.');
        }

        if (($_FILES[$campo]['size'] ?? 0) > UPLOAD_MAX_SIZE) {
            throw new \RuntimeException('Imagem acima do limite de 100MB.');
        }

        $dir = PUBLIC_PATH . '/uploads/site/' . $pasta;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $nome = date('YmdHis') . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '-', $_FILES[$campo]['name'] ?? 'imagem');
        $destino = $dir . '/' . $nome;
        if (!move_uploaded_file($_FILES[$campo]['tmp_name'], $destino)) {
            throw new \RuntimeException('Não foi possível salvar a imagem.');
        }

        return APP_URL . '/public/uploads/site/' . rawurlencode($pasta) . '/' . rawurlencode($nome);
    }

    private function validarCsrf(string $redirect): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . $redirect);
            exit;
        }
    }
}
