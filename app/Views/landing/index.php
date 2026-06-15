<section class="hero-band py-5">
    <div class="container-fluid px-4 py-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <span class="badge badge-primary mb-3"><i class="bi bi-printer"></i> Comunicação visual completa</span>
                <h1 class="display-5 fw-bold mb-3" style="color:var(--text-primary)">Impressão, fachadas, DTF e LED com controle de produção.</h1>
                <p class="lead text-secondary mb-4">A KROMA PRINT atende empresas, revendas, eventos e indústrias com orçamento rápido, upload de arquivos e acompanhamento comercial pelo CRM.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="#orcamento" class="btn btn-primary btn-lg"><i class="bi bi-send"></i> Solicitar Orçamento</a>
                    <a href="https://wa.me/5500000000000" target="_blank" class="btn btn-secondary btn-lg"><i class="bi bi-whatsapp"></i> Falar no WhatsApp</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="landing-card p-4">
                    <div class="ratio ratio-16x9 rounded overflow-hidden" style="background:linear-gradient(135deg,#272e3f,#00658d);">
                        <div class="d-flex flex-column justify-content-center p-4 text-white">
                            <span class="badge badge-info align-self-start mb-3">Painel Operacional</span>
                            <h2 class="fw-bold">Da venda à produção</h2>
                            <div class="row g-2 mt-2">
                                <div class="col-6"><span class="badge badge-success">Arte aprovada</span></div>
                                <div class="col-6"><span class="badge badge-warning">Produção hoje</span></div>
                                <div class="col-6"><span class="badge badge-primary">DTF</span></div>
                                <div class="col-6"><span class="badge badge-info">Fachada ACM</span></div>
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
    <div class="container-fluid px-4 d-flex flex-wrap justify-content-between gap-2">
        <strong>KROMA PRINT</strong>
        <span class="opacity-75">Comunicação visual, impressão digital, DTF, brindes e painéis de LED.</span>
    </div>
</footer>
