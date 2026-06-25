<?php

namespace App\Controllers;

class LandingController
{
    public function index(): void
    {
        $empresa = $this->empresa();
        $site = $this->siteConfig();
        $servicos = $this->siteServicos();
        $portfolio = $this->sitePortfolio();

        $seoTitulo = $site['seo_titulo'] ?: 'Kroma Comunicação Visual | Impressão Digital, Fachadas, Banners e Adesivos em Mossoró/RN';
        $seoDescricao = $site['seo_descricao'] ?: 'A Kroma Comunicação Visual é especialista em impressão digital, fachadas em ACM, banners, lonas, adesivos, DTF, brindes personalizados, painéis de LED e soluções completas para empresas em Mossoró/RN e região. Solicite seu orçamento.';
        $seoKeywords = $site['seo_keywords'] ?: 'comunicação visual mossoró, gráfica mossoró, impressão digital, fachadas em acm, banners personalizados, lonas impressas, adesivos personalizados, plotagem, dtf, uniformes personalizados, brindes personalizados, painel de led, placas comerciais, letras caixa, recorte eletrônico, corte a laser, acrílico, mdf';
        $canonical = rtrim($site['canonical_url'] ?: 'https://kroma.ind.br/', '/') . '/';
        $analyticsId = trim($site['google_analytics_id'] ?? '');
        $gtmId = trim($site['google_tag_manager_id'] ?? '');
        $ogTitle = trim($site['og_title'] ?? '') ?: $seoTitulo;
        $ogDescription = trim($site['og_description'] ?? '') ?: $seoDescricao;
        $ogImage = trim($site['og_image'] ?? '') ?: APP_URL . '/public/assets/img/landing-hero-printshop.png';
        $faviconUrl = trim($site['favicon_url'] ?? '') ?: APP_URL . '/public/assets/img/icone.png';
        $robotsIndex = ($site['robots_index'] ?? 'index') === 'noindex' ? 'noindex' : 'index';
        $robotsFollow = ($site['robots_follow'] ?? 'follow') === 'nofollow' ? 'nofollow' : 'follow';
        $googleVerification = trim($site['google_verification'] ?? '');
        $bingVerification = trim($site['bing_verification'] ?? '');
        $clarityId = trim($site['ms_clarity_id'] ?? '');
        $pixelId = trim($site['meta_pixel_id'] ?? '');
        $heroImage = $site['hero_image_url'] ?: APP_URL . '/public/assets/img/landing-hero-printshop.png';
        $structuredData = $this->structuredData($empresa, $site, $canonical, $ogImage);

        ob_start();
        require APP_PATH . '/Views/landing/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/landing.php';
    }

    public function orcamentoRapido(): void
    {
        $this->capturarLead('Orçamento rápido');
    }

    public function contato(): void
    {
        $this->capturarLead('Contato pelo site');
    }

    public function uploadArquivo(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $upload = $this->salvarArquivo('arquivo');
        if (!$upload['ok']) {
            echo json_encode(['success' => false, 'message' => $upload['erro']], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Arquivo recebido com sucesso.',
            'arquivo' => $upload['url'],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function capturarLead(string $origemDescricao): void
    {
        $nome = trim($_POST['nome'] ?? '');
        $whatsapp = trim($_POST['whatsapp'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $servico = trim($_POST['servico'] ?? ($_POST['produto_interesse'] ?? ''));
        $mensagem = trim($_POST['mensagem'] ?? ($_POST['descricao'] ?? ''));

        if ($nome === '' || $whatsapp === '') {
            $_SESSION['flash_error'] = 'Informe nome e WhatsApp para solicitar atendimento.';
            header('Location: ' . APP_URL . '/#orcamento');
            exit;
        }

        $upload = ['ok' => true, 'url' => null, 'nome' => null];
        if (!empty($_FILES['arquivo']) && ($_FILES['arquivo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $upload = $this->salvarArquivo('arquivo');
            if (!$upload['ok']) {
                $_SESSION['flash_error'] = $upload['erro'];
                header('Location: ' . APP_URL . '/#orcamento');
                exit;
            }
        }

        $descricao = trim($origemDescricao . ': ' . $mensagem);
        $observacoes = '';
        if (!empty($upload['url'])) {
            $observacoes = '- Arquivo enviado: ' . $upload['url'];
            $descricao .= "\nArquivo enviado: " . $upload['url'];
        }

        try {
            $stmt = db()->prepare(
                "INSERT INTO leads (nome, email, whatsapp, produto_interesse, descricao, origem, estagio, prioridade, temperatura, observacoes, created_at)
                 VALUES (?, ?, ?, ?, ?, 'landing_page', 'nova_solicitacao', 'media', 'morno', ?, NOW())"
            );
            $stmt->execute([$nome, $email, $whatsapp, $servico, $descricao, $observacoes]);
            $_SESSION['flash_success'] = 'Solicitação recebida. Nossa equipe entrará em contato pelo WhatsApp.';
        } catch (\Exception $e) {
            $_SESSION['flash_warning'] = 'Solicitação registrada localmente, mas o banco ainda não está instalado.';
        }

        header('Location: ' . APP_URL . '/#orcamento');
        exit;
    }

    private function salvarArquivo(string $campo): array
    {
        if (empty($_FILES[$campo])) {
            return ['ok' => false, 'erro' => 'Nenhum arquivo enviado.'];
        }

        $arquivo = $_FILES[$campo];
        if (($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'erro' => 'Erro no envio do arquivo.'];
        }

        if (($arquivo['size'] ?? 0) > UPLOAD_MAX_SIZE) {
            return ['ok' => false, 'erro' => 'Arquivo acima do limite de 100MB.'];
        }

        $dir = PUBLIC_PATH . '/uploads/landing';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $original = $arquivo['name'] ?? 'arquivo';
        $nomeSeguro = date('YmdHis') . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '-', $original);
        $destino = $dir . '/' . $nomeSeguro;

        if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
            return ['ok' => false, 'erro' => 'Não foi possível salvar o arquivo.'];
        }

        return [
            'ok' => true,
            'nome' => $original,
            'url' => APP_URL . '/public/uploads/landing/' . rawurlencode($nomeSeguro),
        ];
    }

    private function siteConfig(): array
    {
        $padrao = [
            'google_analytics_id' => '',
            'google_tag_manager_id' => '',
            'seo_titulo' => 'Kroma Comunicação Visual | Impressão Digital, Fachadas, Banners e Adesivos em Mossoró/RN',
            'seo_descricao' => 'A Kroma Comunicação Visual é especialista em impressão digital, fachadas em ACM, banners, lonas, adesivos, DTF, brindes personalizados, painéis de LED e soluções completas para empresas em Mossoró/RN e região. Solicite seu orçamento.',
            'seo_keywords' => 'comunicação visual mossoró, gráfica mossoró, impressão digital, fachadas em acm, banners personalizados, lonas impressas, adesivos personalizados, plotagem, dtf, uniformes personalizados, brindes personalizados, painel de led, placas comerciais, letras caixa, recorte eletrônico, corte a laser, acrílico, mdf',
            'canonical_url' => 'https://kroma.ind.br/',
            'og_title' => '',
            'og_description' => '',
            'og_image' => '',
            'hero_badge' => 'Comunicação visual completa',
            'hero_titulo' => 'Comunicação visual que sai bonita no layout e impecável na produção.',
            'hero_subtitulo' => 'Fachadas, lonas, adesivos, DTF, uniformes, brindes e painéis de LED com atendimento rápido e acompanhamento comercial pelo CRM.',
            'hero_cta_texto' => 'Solicitar Orçamento',
            'hero_cta_secundario' => 'Falar no WhatsApp',
            'hero_image_url' => APP_URL . '/public/assets/img/landing-hero-printshop.png',
            'favicon_url' => '',
            'robots_index' => 'index',
            'robots_follow' => 'follow',
            'google_verification' => '',
            'bing_verification' => '',
            'ms_clarity_id' => '',
            'meta_pixel_id' => '',
        ];

        try {
            $row = db()->query('SELECT * FROM site_configuracoes ORDER BY id LIMIT 1')->fetch() ?: [];
            return array_merge($padrao, array_filter($row, fn($value) => $value !== null && $value !== ''));
        } catch (\Exception $e) {
            return $padrao;
        }
    }

    private function siteServicos(): array
    {
        try {
            $rows = db()->query("SELECT * FROM site_servicos WHERE ativo = 1 ORDER BY ordem, id")->fetchAll();
            if ($rows) {
                return $rows;
            }
        } catch (\Exception $e) {
        }

        return [
            ['titulo' => 'Fachadas e ACM', 'icone' => 'bi-shop', 'descricao' => 'ACM, letras caixa, totens e sinalização para destacar sua marca.'],
            ['titulo' => 'Banners e Lonas', 'icone' => 'bi-image', 'descricao' => 'Impressão em grandes formatos para eventos, obras e pontos de venda.'],
            ['titulo' => 'DTF e Uniformes', 'icone' => 'bi-printer', 'descricao' => 'Personalização têxtil para equipes, campanhas e revendas.'],
            ['titulo' => 'Adesivos e Envelopamento', 'icone' => 'bi-layers', 'descricao' => 'Recorte, impressão, laminação e aplicação profissional.'],
            ['titulo' => 'Brindes Personalizados', 'icone' => 'bi-gift', 'descricao' => 'Produtos promocionais sob demanda para sua empresa.'],
            ['titulo' => 'Paineis de LED', 'icone' => 'bi-display', 'descricao' => 'Locação, operação e conteúdo para eventos e mídia indoor.'],
        ];
    }

    private function sitePortfolio(): array
    {
        try {
            $rows = db()->query("SELECT * FROM site_portfolio WHERE ativo = 1 ORDER BY ordem, id")->fetchAll();
            if ($rows) {
                return $rows;
            }
        } catch (\Exception $e) {
        }

        return [
            ['titulo' => 'Fachadas comerciais', 'categoria' => 'Fachadas', 'descricao' => 'ACM, letras e iluminação para lojas.', 'imagem_url' => ''],
            ['titulo' => 'Eventos e campanhas', 'categoria' => 'Eventos', 'descricao' => 'Lonas, banners e sinalização promocional.', 'imagem_url' => ''],
            ['titulo' => 'Frotas e adesivos', 'categoria' => 'Adesivos', 'descricao' => 'Envelopamento e identidade visual veicular.', 'imagem_url' => ''],
            ['titulo' => 'Uniformes DTF', 'categoria' => 'DTF', 'descricao' => 'Personalização têxtil com acabamento profissional.', 'imagem_url' => ''],
        ];
    }

    private function empresa(): array
    {
        try {
            return db()->query('SELECT * FROM empresas ORDER BY id LIMIT 1')->fetch() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function structuredData(array $empresa, array $site, string $canonical, string $ogImage = ''): string
    {
        $nome = $empresa['nome_fantasia'] ?? $empresa['razao_social'] ?? 'KROMA PRINT';
        $whatsapp = $empresa['whatsapp'] ?? '';
        $telefone = $empresa['telefone'] ?? '';
        $dados = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $nome,
            'url' => $canonical,
            'description' => $site['seo_descricao'] ?? '',
            'telephone' => $telefone ?: $whatsapp,
            'email' => $empresa['email'] ?? '',
            'image' => $ogImage ?: APP_URL . '/public/assets/img/landing-hero-printshop.png',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => trim(($empresa['endereco'] ?? '') . ', ' . ($empresa['numero'] ?? ''), ' ,'),
                'addressLocality' => $empresa['cidade'] ?? '',
                'addressRegion' => $empresa['estado'] ?? '',
                'postalCode' => $empresa['cep'] ?? '',
                'addressCountry' => 'BR',
            ],
            'areaServed' => 'Brasil',
            'keywords' => $site['seo_keywords'] ?? '',
            'sameAs' => [
                'https://www.instagram.com/kromacomunicacaovisual/',
                'https://www.facebook.com/kromacomunicacaovisual/',
            ],
        ];
        if ($whatsapp) {
            $dados['contactPoint'] = [
                '@type' => 'ContactPoint',
                'telephone' => $whatsapp,
                'contactType' => 'sales',
                'areaServed' => 'Brasil',
            ];
        }

        return json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
