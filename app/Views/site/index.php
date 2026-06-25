<?php

use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$config = $config ?? [];
$servicos = $servicos ?? [];
$portfolio = $portfolio ?? [];

$valor = fn(string $campo, string $padrao = '') => htmlspecialchars($config[$campo] ?? $padrao);
?>

<div class="row g-3">
    <div class="col-lg-8">
        <form action="<?= APP_URL ?>/site/config" method="POST" enctype="multipart/form-data" data-loading>
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-graph-up-arrow me-2 text-primary-kroma"></i>SEO e Analytics</h6>
                    <span class="badge badge-info">Landing Page</span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Google Analytics ID <small class="text-muted">(G-...)</small></label>
                            <input class="form-control" name="google_analytics_id" placeholder="G-XXXXXXXXXX" value="<?= $valor('google_analytics_id') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Google Tag Manager ID</label>
                            <input class="form-control" name="google_tag_manager_id" placeholder="GTM-XXXXXXX" value="<?= $valor('google_tag_manager_id') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Microsoft Clarity ID</label>
                            <input class="form-control" name="ms_clarity_id" placeholder="abcdef1234" value="<?= $valor('ms_clarity_id') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Meta Pixel ID</label>
                            <input class="form-control" name="meta_pixel_id" placeholder="1234567890" value="<?= $valor('meta_pixel_id') ?>">
                        </div>
                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-md-6">
                            <label class="form-label">URL canônica</label>
                            <input class="form-control" name="canonical_url" placeholder="https://kroma.ind.br/" value="<?= $valor('canonical_url', 'https://kroma.ind.br/') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Robots index</label>
                            <select class="form-select" name="robots_index">
                                <option value="index" <?= $valor('robots_index', 'index') === 'index' ? 'selected' : '' ?>>Indexar</option>
                                <option value="noindex" <?= $valor('robots_index') === 'noindex' ? 'selected' : '' ?>>Não indexar</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Robots follow</label>
                            <select class="form-select" name="robots_follow">
                                <option value="follow" <?= $valor('robots_follow', 'follow') === 'follow' ? 'selected' : '' ?>>Seguir links</option>
                                <option value="nofollow" <?= $valor('robots_follow') === 'nofollow' ? 'selected' : '' ?>>Não seguir</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Título (Meta Title)</label>
                            <input class="form-control" name="seo_titulo" maxlength="180" value="<?= $valor('seo_titulo', 'Kroma Comunicação Visual | Impressão Digital, Fachadas, Banners e Adesivos em Mossoró/RN') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição (Meta Description)</label>
                            <textarea class="form-control" name="seo_descricao" rows="3"><?= $valor('seo_descricao', 'A Kroma Comunicação Visual é especialista em impressão digital, fachadas em ACM, banners, lonas, adesivos, DTF, brindes personalizados, painéis de LED e soluções completas para empresas em Mossoró/RN e região. Solicite seu orçamento.') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Palavras-chave</label>
                            <textarea class="form-control" name="seo_keywords" rows="2"><?= $valor('seo_keywords', 'comunicação visual mossoró, gráfica mossoró, impressão digital, fachadas em acm, banners personalizados, lonas impressas, adesivos personalizados, plotagem, dtf, uniformes personalizados, brindes personalizados, painel de led, placas comerciais, letras caixa, recorte eletrônico, corte a laser, acrílico, mdf') ?></textarea>
                        </div>
                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-md-6">
                            <label class="form-label">Open Graph Title</label>
                            <input class="form-control" name="og_title" maxlength="180" placeholder="Deixe vazio para usar o Meta Title" value="<?= $valor('og_title') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Open Graph Description</label>
                            <input class="form-control" name="og_description" maxlength="320" placeholder="Deixe vazio para usar a Meta Description" value="<?= $valor('og_description') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Open Graph Image URL</label>
                            <input class="form-control" name="og_image" placeholder="<?= APP_URL ?>/public/assets/img/landing-hero-printshop.png" value="<?= $valor('og_image') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Favicon URL</label>
                            <input class="form-control" name="favicon_url" placeholder="<?= APP_URL ?>/public/assets/img/icone.png" value="<?= $valor('favicon_url') ?>">
                        </div>
                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-md-6">
                            <label class="form-label">Google Search Console Verification</label>
                            <input class="form-control" name="google_verification" placeholder="código de verificação" value="<?= $valor('google_verification') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bing Verification</label>
                            <input class="form-control" name="bing_verification" placeholder="código de verificação" value="<?= $valor('bing_verification') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-image me-2 text-success-kroma"></i>Topo da Landing</h6>
                    <a class="badge badge-secondary text-decoration-none" href="<?= APP_URL ?>/" target="_blank" rel="noopener">Ver site</a>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Badge</label>
                            <input class="form-control" name="hero_badge" value="<?= $valor('hero_badge', 'Comunicação visual completa') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Texto do botao principal</label>
                            <input class="form-control" name="hero_cta_texto" value="<?= $valor('hero_cta_texto', 'Solicitar Orçamento') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Titulo do topo</label>
                            <input class="form-control" name="hero_titulo" value="<?= $valor('hero_titulo', 'Comunicação visual que sai bonita no layout e impecável na produção.') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subtitulo</label>
                            <textarea class="form-control" name="hero_subtitulo" rows="3"><?= $valor('hero_subtitulo', 'Fachadas, lonas, adesivos, DTF, uniformes, brindes e painéis de LED com atendimento rápido e acompanhamento comercial pelo CRM.') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Texto do botao WhatsApp</label>
                            <input class="form-control" name="hero_cta_secundario" value="<?= $valor('hero_cta_secundario', 'Falar no WhatsApp') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Imagem de fundo</label>
                            <input class="form-control" type="file" name="hero_arquivo" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label">URL da imagem de fundo</label>
                            <input class="form-control" name="hero_image_url" value="<?= $valor('hero_image_url', APP_URL . '/public/assets/img/landing-hero-printshop.png') ?>">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Salvar Site</button>
                        <a class="btn btn-secondary" href="<?= APP_URL ?>/"><i class="bi bi-box-arrow-up-right"></i> Abrir Landing</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-search me-2 text-warning-kroma"></i>Checklist SEO</h6>
            </div>
            <div class="p-3 d-flex flex-column gap-1" style="font-size:13px">
                <div class="d-flex justify-content-between"><span>Title</span><span class="badge <?= !empty($config['seo_titulo']) ? 'badge-success' : 'badge-warning' ?>"><?= !empty($config['seo_titulo']) ? 'OK' : 'Pendente' ?></span></div>
                <div class="d-flex justify-content-between"><span>Description</span><span class="badge <?= !empty($config['seo_descricao']) ? 'badge-success' : 'badge-warning' ?>"><?= !empty($config['seo_descricao']) ? 'OK' : 'Pendente' ?></span></div>
                <div class="d-flex justify-content-between"><span>Keywords</span><span class="badge <?= !empty($config['seo_keywords']) ? 'badge-success' : 'badge-warning' ?>"><?= !empty($config['seo_keywords']) ? 'OK' : 'Pendente' ?></span></div>
                <div class="d-flex justify-content-between"><span>Canonical</span><span class="badge <?= !empty($config['canonical_url']) ? 'badge-success' : 'badge-warning' ?>"><?= !empty($config['canonical_url']) ? 'OK' : 'Pendente' ?></span></div>
                <div class="d-flex justify-content-between"><span>GA4</span><span class="badge <?= !empty($config['google_analytics_id']) ? 'badge-success' : 'badge-info' ?>"><?= !empty($config['google_analytics_id']) ? 'Ativo' : 'Não configurado' ?></span></div>
                <div class="d-flex justify-content-between"><span>GTM</span><span class="badge <?= !empty($config['google_tag_manager_id']) ? 'badge-success' : 'badge-info' ?>"><?= !empty($config['google_tag_manager_id']) ? 'Ativo' : 'Não configurado' ?></span></div>
                <div class="d-flex justify-content-between"><span>OG Tags</span><span class="badge <?= !empty($config['og_title']) ? 'badge-success' : 'badge-info' ?>"><?= !empty($config['og_title']) ? 'OK' : 'Usa title' ?></span></div>
                <div class="d-flex justify-content-between"><span>Favicon</span><span class="badge <?= !empty($config['favicon_url']) ? 'badge-success' : 'badge-info' ?>"><?= !empty($config['favicon_url']) ? 'OK' : 'Padrão' ?></span></div>
                <div class="d-flex justify-content-between"><span>Clarity</span><span class="badge <?= !empty($config['ms_clarity_id']) ? 'badge-success' : 'badge-info' ?>"><?= !empty($config['ms_clarity_id']) ? 'Ativo' : 'Não configurado' ?></span></div>
                <div class="d-flex justify-content-between"><span>Pixel</span><span class="badge <?= !empty($config['meta_pixel_id']) ? 'badge-success' : 'badge-info' ?>"><?= !empty($config['meta_pixel_id']) ? 'Ativo' : 'Não configurado' ?></span></div>
                <div class="d-flex justify-content-between"><span>Serviços</span><span class="badge badge-primary"><?= count(array_filter($servicos, fn($s) => (int)($s['ativo'] ?? 0) === 1)) ?></span></div>
                <div class="d-flex justify-content-between"><span>Portfólio</span><span class="badge badge-primary"><?= count(array_filter($portfolio, fn($p) => (int)($p['ativo'] ?? 0) === 1)) ?></span></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-bullseye me-2 text-primary-kroma"></i>Termos importantes</h6>
            </div>
            <div class="p-3 d-flex flex-wrap gap-2">
                <?php foreach (['comunicação visual','fachada acm','impressão digital','banners e lonas','adesivos personalizados','dtf','uniformes personalizados','brindes personalizados','painel de led'] as $tag): ?>
                    <span class="badge badge-secondary"><?= htmlspecialchars($tag) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3" id="servicos">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-grid-3x3-gap me-2 text-primary-kroma"></i>Serviços da Landing</h6>
        <span class="badge badge-info"><?= count($servicos) ?> cadastrado(s)</span>
    </div>
    <div class="p-3">
        <form action="<?= APP_URL ?>/site/servicos/salvar" method="POST" class="row g-2 align-items-end mb-3" data-loading>
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="col-md-2">
                <label class="form-label">Ordem</label>
                <input class="form-control" type="number" name="ordem" value="10">
            </div>
            <div class="col-md-3">
                <label class="form-label">Título</label>
                <input class="form-control" name="titulo" required placeholder="Fachadas e ACM">
            </div>
            <div class="col-md-2">
                <label class="form-label">Ícone</label>
                <input class="form-control" name="icone" value="bi-stars">
            </div>
            <div class="col-md-4">
                <label class="form-label">Descrição</label>
                <input class="form-control" name="descricao" placeholder="Texto curto para o card">
            </div>
            <div class="col-md-1 d-grid">
                <input type="hidden" name="ativo" value="1">
                <button class="btn btn-primary" type="submit"><i class="bi bi-plus-circle"></i></button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width:70px;">Ordem</th>
                        <th>Serviço</th>
                        <th>Descrição</th>
                        <th style="width:110px;">Status</th>
                        <th style="width:170px;">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servicos as $servico): ?>
                    <tr>
                        <td><?= (int)$servico['ordem'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="kpi-icon primary" style="width:32px;height:32px;"><i class="bi <?= htmlspecialchars($servico['icone'] ?? 'bi-stars') ?>"></i></span>
                                <strong><?= htmlspecialchars($servico['titulo']) ?></strong>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($servico['descricao'] ?? '') ?></td>
                        <td><span class="badge <?= (int)($servico['ativo'] ?? 0) === 1 ? 'badge-success' : 'badge-secondary' ?>"><?= (int)($servico['ativo'] ?? 0) === 1 ? 'Ativo' : 'Inativo' ?></span></td>
                        <td class="d-flex gap-1">
                            <button class="btn btn-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#servico<?= (int)$servico['id'] ?>"><i class="bi bi-pencil"></i></button>
                            <form action="<?= APP_URL ?>/site/servicos/<?= (int)$servico['id'] ?>/excluir" method="POST" data-confirm="Remover este serviço?">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <button class="btn btn-danger btn-sm" type="submit"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <tr class="collapse" id="servico<?= (int)$servico['id'] ?>">
                        <td colspan="5">
                            <form action="<?= APP_URL ?>/site/servicos/salvar" method="POST" class="row g-2 align-items-end" data-loading>
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="id" value="<?= (int)$servico['id'] ?>">
                                <div class="col-md-1"><label class="form-label">Ordem</label><input class="form-control" type="number" name="ordem" value="<?= (int)$servico['ordem'] ?>"></div>
                                <div class="col-md-3"><label class="form-label">Título</label><input class="form-control" name="titulo" value="<?= htmlspecialchars($servico['titulo']) ?>"></div>
                                <div class="col-md-2"><label class="form-label">Ícone</label><input class="form-control" name="icone" value="<?= htmlspecialchars($servico['icone'] ?? '') ?>"></div>
                                <div class="col-md-4"><label class="form-label">Descrição</label><input class="form-control" name="descricao" value="<?= htmlspecialchars($servico['descricao'] ?? '') ?>"></div>
                                <div class="col-md-1"><label class="form-label">Ativo</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="ativo" <?= (int)($servico['ativo'] ?? 0) === 1 ? 'checked' : '' ?>></div></div>
                                <div class="col-md-1 d-grid"><button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i></button></div>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$servicos): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Nenhum serviço cadastrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-3" id="portfolio">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-images me-2 text-success-kroma"></i>Portfólio da Landing</h6>
        <span class="badge badge-info"><?= count($portfolio) ?> item(ns)</span>
    </div>
    <div class="p-3">
        <form action="<?= APP_URL ?>/site/portfolio/salvar" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end mb-3" data-loading>
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="col-md-1"><label class="form-label">Ordem</label><input class="form-control" type="number" name="ordem" value="10"></div>
            <div class="col-md-2"><label class="form-label">Título</label><input class="form-control" name="titulo" required></div>
            <div class="col-md-2"><label class="form-label">Categoria</label><input class="form-control" name="categoria" placeholder="Fachadas"></div>
            <div class="col-md-3"><label class="form-label">Descrição</label><input class="form-control" name="descricao"></div>
            <div class="col-md-3"><label class="form-label">Imagem</label><input class="form-control" type="file" name="portfolio_arquivo" accept="image/*"></div>
            <div class="col-md-1 d-grid">
                <input type="hidden" name="ativo" value="1">
                <button class="btn btn-primary" type="submit"><i class="bi bi-plus-circle"></i></button>
            </div>
        </form>

        <div class="row g-3">
            <?php foreach ($portfolio as $item): ?>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="ratio ratio-16x9 bg-light">
                        <?php if (!empty($item['imagem_url'])): ?>
                            <img src="<?= htmlspecialchars($item['imagem_url']) ?>" alt="<?= htmlspecialchars($item['titulo']) ?>" class="w-100 h-100" style="object-fit:cover;">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center text-muted"><i class="bi bi-image fs-1"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="p-3">
                        <div class="d-flex justify-content-between gap-2 mb-2">
                            <strong><?= htmlspecialchars($item['titulo']) ?></strong>
                            <span class="badge <?= (int)($item['ativo'] ?? 0) === 1 ? 'badge-success' : 'badge-secondary' ?>"><?= (int)($item['ativo'] ?? 0) === 1 ? 'Ativo' : 'Inativo' ?></span>
                        </div>
                        <div class="small text-muted mb-2"><?= htmlspecialchars($item['categoria'] ?? '') ?></div>
                        <p class="text-secondary mb-3" style="font-size:13px;"><?= htmlspecialchars($item['descricao'] ?? '') ?></p>
                        <button class="btn btn-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#portfolio<?= (int)$item['id'] ?>"><i class="bi bi-pencil"></i> Editar</button>
                    </div>
                    <div class="collapse border-top" id="portfolio<?= (int)$item['id'] ?>">
                        <form action="<?= APP_URL ?>/site/portfolio/salvar" method="POST" enctype="multipart/form-data" class="p-3 d-flex flex-column gap-2" data-loading>
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                            <label class="form-label mb-0">Título</label>
                            <input class="form-control" name="titulo" value="<?= htmlspecialchars($item['titulo']) ?>">
                            <label class="form-label mb-0">Categoria</label>
                            <input class="form-control" name="categoria" value="<?= htmlspecialchars($item['categoria'] ?? '') ?>">
                            <label class="form-label mb-0">Descrição</label>
                            <textarea class="form-control" name="descricao" rows="2"><?= htmlspecialchars($item['descricao'] ?? '') ?></textarea>
                            <label class="form-label mb-0">URL da imagem</label>
                            <input class="form-control" name="imagem_url" value="<?= htmlspecialchars($item['imagem_url'] ?? '') ?>">
                            <label class="form-label mb-0">Trocar imagem</label>
                            <input class="form-control" type="file" name="portfolio_arquivo" accept="image/*">
                            <div class="row g-2 align-items-center">
                                <div class="col-6"><label class="form-label mb-0">Ordem</label><input class="form-control" type="number" name="ordem" value="<?= (int)$item['ordem'] ?>"></div>
                                <div class="col-6"><label class="form-label mb-0">Ativo</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="ativo" <?= (int)($item['ativo'] ?? 0) === 1 ? 'checked' : '' ?>></div></div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-check2"></i> Salvar</button>
                            </div>
                        </form>
                        <form action="<?= APP_URL ?>/site/portfolio/<?= (int)$item['id'] ?>/excluir" method="POST" class="px-3 pb-3" data-confirm="Remover este item do portfólio?">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <button class="btn btn-danger btn-sm" type="submit"><i class="bi bi-trash"></i> Remover</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (!$portfolio): ?>
            <div class="col-12 text-center text-muted py-4">Nenhum item de portfólio cadastrado.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
