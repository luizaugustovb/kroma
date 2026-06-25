<?php
$site = $site ?? [];
$servicos = $servicos ?? [];
$portfolio = $portfolio ?? [];
$empresa = $empresa ?? [];
$heroImage = $heroImage ?? APP_URL . '/public/assets/img/landing-hero-printshop.png';
$whats = preg_replace('/\D+/', '', $empresa['whatsapp'] ?? $empresa['telefone'] ?? '');
$whatsNumero = $whats && str_starts_with($whats, '55') ? $whats : '55' . ltrim($whats, '0');
$whatsUrl = $whats ? 'https://wa.me/' . $whatsNumero : '#orcamento';
?>

<section class="landing-hero" style="--hero-image:url('<?= htmlspecialchars($heroImage, ENT_QUOTES) ?>');">
    <div class="container-fluid px-4">
        <div class="hero-content">
            <div class="hero-kicker mb-3">
                <i class="bi bi-stars"></i>
                <?= htmlspecialchars($site['hero_badge'] ?? 'Comunicação visual completa') ?>
            </div>
            <h1 class="hero-title mb-4"><?= htmlspecialchars($site['hero_titulo'] ?? 'Comunicação visual que sai bonita no layout e impecável na produção.') ?></h1>
            <p class="hero-copy mb-4"><?= htmlspecialchars($site['hero_subtitulo'] ?? 'Fachadas, lonas, adesivos, DTF, uniformes, brindes e painéis de LED com atendimento rápido e acompanhamento comercial pelo CRM.') ?></p>
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="#orcamento" class="btn btn-primary btn-lg"><i class="bi bi-send"></i> <?= htmlspecialchars($site['hero_cta_texto'] ?? 'Solicitar Orçamento') ?></a>
                <a href="<?= htmlspecialchars($whatsUrl) ?>" target="_blank" rel="noopener" class="btn btn-light btn-lg"><i class="bi bi-whatsapp"></i> <?= htmlspecialchars($site['hero_cta_secundario'] ?? 'Falar no WhatsApp') ?></a>
            </div>
            <div class="hero-stats">
                <span class="hero-stat"><i class="bi bi-shop"></i> Fachadas e ACM</span>
                <span class="hero-stat"><i class="bi bi-printer"></i> DTF e impressão</span>
                <span class="hero-stat"><i class="bi bi-lightning-charge"></i> Atendimento rápido</span>
            </div>
        </div>
    </div>
</section>

<section class="landing-section" id="servicos">
    <div class="container-fluid px-4">
        <div class="row align-items-end g-3 mb-4">
            <div class="col-lg-8">
                <div class="section-eyebrow mb-2">Serviços</div>
                <h2 class="fw-bold mb-2">Tudo para sua marca aparecer melhor.</h2>
                <p class="text-secondary mb-0">Projetos de comunicação visual, impressão e personalização com foco em prazo, acabamento e acompanhamento comercial.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <span class="badge badge-info">Orçamento pelo site integrado ao CRM</span>
            </div>
        </div>
        <div class="row g-3">
            <?php foreach ($servicos as $servico): ?>
            <div class="col-md-6 col-xl-4">
                <article class="landing-card service-card">
                    <div class="kpi-icon primary mb-3"><i class="bi <?= htmlspecialchars($servico['icone'] ?? 'bi-stars') ?>"></i></div>
                    <h3 class="h5 fw-bold"><?= htmlspecialchars($servico['titulo'] ?? '') ?></h3>
                    <p class="text-secondary mb-0"><?= htmlspecialchars($servico['descricao'] ?? '') ?></p>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="landing-section bg-white" id="portfolio">
    <div class="container-fluid px-4">
        <div class="row align-items-end g-3 mb-4">
            <div class="col-lg-8">
                <div class="section-eyebrow mb-2">Portfólio</div>
                <h2 class="fw-bold mb-2">Projetos que ajudam o cliente a enxergar o resultado.</h2>
                <p class="text-secondary mb-0">Use o painel interno para manter fotos, categorias e destaques sempre atualizados.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a class="btn btn-secondary btn-sm" href="#orcamento"><i class="bi bi-chat-dots"></i> Pedir um projeto parecido</a>
            </div>
        </div>
        <div class="row g-3">
            <?php foreach ($portfolio as $item): ?>
            <div class="col-md-6 col-xl-3">
                <article class="landing-card overflow-hidden h-100">
                    <div class="portfolio-shot">
                        <?php if (!empty($item['imagem_url'])): ?>
                            <img src="<?= htmlspecialchars($item['imagem_url']) ?>" alt="<?= htmlspecialchars($item['titulo'] ?? 'Projeto de comunicação visual') ?>">
                        <?php else: ?>
                            <div class="placeholder"><i class="bi bi-image"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="p-3">
                        <span class="badge badge-secondary mb-2"><?= htmlspecialchars($item['categoria'] ?? 'Projeto KROMA') ?></span>
                        <h3 class="h6 fw-bold mb-2"><?= htmlspecialchars($item['titulo'] ?? '') ?></h3>
                        <p class="text-secondary mb-0" style="font-size:13px;"><?= htmlspecialchars($item['descricao'] ?? '') ?></p>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="landing-section quote-band" id="orcamento">
    <div class="container-fluid px-4">
        <div class="row g-4 align-items-start">
            <div class="col-lg-4">
                <div class="section-eyebrow mb-2" style="color:#7dd3fc;">Orçamento rápido</div>
                <h2 class="fw-bold mb-3">Envie sua demanda e anexe a arte, foto ou referência.</h2>
                <p class="mb-4" style="color:rgba(255,255,255,0.72);">Seu pedido entra automaticamente no CRM para atendimento comercial, com o arquivo salvo no lead.</p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge badge-success">Upload até 100MB</span>
                    <span class="badge badge-info">Resposta via WhatsApp</span>
                    <span class="badge badge-secondary">Atendimento comercial</span>
                </div>
            </div>
            <div class="col-lg-8">
                <form action="<?= APP_URL ?>/orcamento-rapido" method="POST" enctype="multipart/form-data" class="row g-3" data-loading>
                    <div class="col-md-6">
                        <label class="form-label">Nome *</label>
                        <input class="form-control" name="nome" required placeholder="Seu nome completo">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">WhatsApp *</label>
                        <input class="form-control" name="whatsapp" required data-mask="telefone" placeholder="(00) 00000-0000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">E-mail</label>
                        <input class="form-control" type="email" name="email" placeholder="email@empresa.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Serviço</label>
                        <select class="form-select" name="servico">
                            <?php foreach ($servicos as $servico): ?>
                                <option><?= htmlspecialchars($servico['titulo'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" name="mensagem" rows="4" placeholder="Medidas, quantidade, prazo, local de instalação e observações"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Arquivo</label>
                        <div class="file-drop">
                            <input class="form-control" type="file" name="arquivo" accept=".pdf,.cdr,.ai,.eps,.psd,.png,.jpg,.jpeg,.webp,.zip,.rar,image/*,application/pdf">
                            <div class="small mt-2" style="color:rgba(255,255,255,0.68);">Envie arte, foto, referência, briefing ou arquivo fechado.</div>
                        </div>
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-2">
                        <button class="btn btn-primary btn-lg" type="submit"><i class="bi bi-send"></i> Enviar Solicitação</button>
                        <a class="btn btn-outline-light btn-lg" href="<?= APP_URL ?>/login"><i class="bi bi-lock"></i> Acessar ERP</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<footer class="py-4" style="background:#080d15; color:#fff;">
    <div class="container-fluid px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <img src="<?= APP_URL ?>/public/assets/img/icone.png" alt="KROMA" style="height:28px;width:auto;">
            <img src="<?= APP_URL ?>/public/assets/img/nome.png" alt="KROMA PRINT" style="height:18px;width:auto;filter:brightness(0) invert(1);">
        </div>
        <span class="opacity-75">Comunicação visual, impressão digital, DTF, brindes e painéis de LED.</span>
    </div>
</footer>
