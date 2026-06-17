<section class="hero-band py-5">
    <div class="container-fluid px-4 py-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="<?= APP_URL ?>/public/assets/img/icone.png" alt="" style="height:24px;width:auto;">
                    <span class="badge badge-primary mb-0"><i class="bi bi-printer"></i> Comunicação visual completa</span>
                </div>
                <h1 class="display-5 fw-bold mb-3" style="color:var(--text-primary)">Impressão, fachadas, DTF e LED com <span style="color:var(--kroma-primary)">controle de produção</span>.</h1>
                <p class="lead text-secondary mb-4">A KROMA PRINT atende empresas, revendas, eventos e indústrias com orçamento rápido, upload de arquivos e acompanhamento comercial pelo CRM.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="#orcamento" class="btn btn-primary btn-lg"><i class="bi bi-send"></i> Solicitar Orçamento</a>
                    <a href="https://wa.me/5500000000000" target="_blank" class="btn btn-secondary btn-lg"><i class="bi bi-whatsapp"></i> Falar no WhatsApp</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="landing-card overflow-hidden position-relative">
                    <div class="d-flex flex-column" style="min-height:380px;">
                        <div class="px-4 pt-4 pb-3 d-flex align-items-center gap-3 border-bottom" style="border-color:var(--border-color) !important;">
                            <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:linear-gradient(135deg,#00AEEF,#EC008C);">
                                <img src="<?= APP_URL ?>/public/assets/img/icone.png" alt="" style="height:24px;width:auto;filter:brightness(0) invert(1);">
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:15px;color:var(--text-primary);">Painel Operacional</div>
                                <div style="font-size:11px;color:var(--text-muted);">KROMA PRINT · Produção</div>
                            </div>
                            <div class="ms-auto d-flex gap-1">
                                <span style="width:12px;height:12px;border-radius:50%;background:#00AEEF;"></span>
                                <span style="width:12px;height:12px;border-radius:50%;background:#EC008C;"></span>
                                <span style="width:12px;height:12px;border-radius:50%;background:#FFD100;"></span>
                                <span style="width:12px;height:12px;border-radius:50%;background:#2D2D2D;"></span>
                            </div>
                        </div>
                        <div class="px-4 py-3 d-flex align-items-center gap-3 border-bottom" style="border-color:var(--border-color) !important;background:#f9fafb;">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:#00AEEF;color:#fff;">Arte aprovada</span>
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:#EC008C;color:#fff;">Produção hoje</span>
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:#FFD100;color:#2D2D2D;">DTF</span>
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:#2D2D2D;color:#fff;">Fachada ACM</span>
                            </div>
                        </div>
                        <div class="px-4 py-3 flex-grow-1 d-flex flex-column justify-content-center" style="background:linear-gradient(180deg,#fff,#f7f9fb);">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <span style="font-size:28px;font-weight:800;background:linear-gradient(135deg,#00AEEF,#EC008C);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Da venda</span>
                                <span style="width:24px;height:2px;background:#ddd;"></span>
                                <span style="font-size:28px;font-weight:800;color:#2D2D2D;">à produção</span>
                            </div>
                            <div style="font-size:13px;color:var(--text-muted);">
                                <span class="d-inline-flex align-items-center gap-1 me-3"><span style="width:8px;height:8px;border-radius:50%;background:#00AEEF;"></span> 12 OS ativas</span>
                                <span class="d-inline-flex align-items-center gap-1"><span style="width:8px;height:8px;border-radius:50%;background:#EC008C;"></span> 4 entregas hoje</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5" id="servicos">
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Serviços</h2>
                <p class="text-secondary mb-0">Soluções para comunicação visual, impressão e personalização.</p>
            </div>
            <span class="badge badge-info">Captação automática de leads</span>
        </div>
        <div class="row g-3">
            <?php
            $servicos = [
                ['Fachadas e ACM', 'bi-shop', 'ACM, letras caixa, totens e sinalização.'],
                ['Banners e Lonas', 'bi-image', 'Grandes formatos, eventos e pontos de venda.'],
                ['DTF e Uniformes', 'bi-printer', 'Personalização têxtil para equipes e revendas.'],
                ['Brindes e Camisetas', 'bi-gift', 'Produtos personalizados sob demanda.'],
                ['Painéis de LED', 'bi-display', 'Locação, agenda e conteúdo de exibição.'],
                ['Adesivos e Envelopamento', 'bi-layers', 'Recorte, laminação e aplicação.'],
            ];
            foreach ($servicos as [$nome, $icone, $desc]):
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="landing-card p-4 h-100">
                    <div class="kpi-icon primary mb-3"><i class="bi <?= $icone ?>"></i></div>
                    <h3 class="h5 fw-bold"><?= $nome ?></h3>
                    <p class="text-secondary mb-0"><?= $desc ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5 bg-surface" id="portfolio">
    <div class="container-fluid px-4">
        <h2 class="fw-bold mb-3">Portfólio</h2>
        <div class="row g-3">
            <?php foreach (['Fachadas', 'Eventos', 'Frotas', 'Uniformes'] as $item): ?>
            <div class="col-6 col-lg-3">
                <div class="landing-card p-3">
                    <div class="ratio ratio-1x1 rounded mb-3" style="background:linear-gradient(135deg,#e0e3e5,#c6e7ff);"></div>
                    <strong><?= $item ?></strong>
                    <div><span class="badge badge-secondary mt-2">Projeto KROMA</span></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5" id="orcamento">
    <div class="container-fluid px-4">
        <div class="landing-card overflow-hidden">
            <div class="row g-0">
                <div class="col-lg-4 p-4 text-white" style="background:var(--bg-sidebar)">
                    <span class="badge badge-primary mb-3">Orçamento rápido</span>
                    <h2 class="fw-bold">Envie sua demanda</h2>
                    <p class="opacity-75">Seu pedido entra no CRM como novo lead para atendimento comercial.</p>
                    <div class="d-flex flex-column gap-2">
                        <span class="badge badge-success align-self-start">Upload até 100MB</span>
                        <span class="badge badge-info align-self-start">Resposta via WhatsApp</span>
                    </div>
                </div>
                <div class="col-lg-8 p-4">
                    <form action="<?= APP_URL ?>/orcamento-rapido" method="POST" class="row g-3" data-loading>
                        <div class="col-md-6">
                            <label class="form-label">Nome</label>
                            <input class="form-control" name="nome" required placeholder="Seu nome completo">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">WhatsApp</label>
                            <input class="form-control" name="whatsapp" required data-mask="telefone" placeholder="(00) 00000-0000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-mail</label>
                            <input class="form-control" type="email" name="email" placeholder="email@empresa.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Serviço</label>
                            <select class="form-select" name="servico">
                                <option>Fachadas e ACM</option>
                                <option>DTF e uniformes</option>
                                <option>Banners e lonas</option>
                                <option>Painéis de LED</option>
                                <option>Brindes personalizados</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <textarea class="form-control" name="mensagem" rows="3" placeholder="Medidas, quantidade, prazo e observações"></textarea>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Enviar Solicitação</button>
                            <a class="btn btn-secondary" href="<?= APP_URL ?>/login"><i class="bi bi-lock"></i> Acessar ERP</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="py-4" style="background:var(--bg-sidebar); color:#fff;">
    <div class="container-fluid px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <img src="<?= APP_URL ?>/public/assets/img/icone.png" alt="KROMA" style="height:28px;width:auto;">
            <img src="<?= APP_URL ?>/public/assets/img/nome.png" alt="KROMA PRINT" style="height:18px;width:auto;filter:brightness(0) invert(1);">
        </div>
        <span class="opacity-75">Comunicação visual, impressão digital, DTF, brindes e painéis de LED.</span>
    </div>
</footer>
