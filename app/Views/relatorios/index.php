<?php
$statusOrcamento = [
    'rascunho' => 'Rascunho',
    'em_calculo' => 'Em cálculo',
    'enviado' => 'Enviado',
    'aprovado' => 'Aprovado',
    'recusado' => 'Recusado',
    'cancelado' => 'Cancelado',
    'expirado' => 'Expirado',
];
$statusOrcamentoClass = [
    'rascunho' => 'badge-secondary',
    'em_calculo' => 'badge-info',
    'enviado' => 'badge-warning',
    'aprovado' => 'badge-success',
    'recusado' => 'badge-danger',
    'cancelado' => 'badge-secondary',
    'expirado' => 'badge-danger',
];
$statusFinanceiroClass = [
    'aberto' => 'badge-warning',
    'parcial' => 'badge-info',
    'pago' => 'badge-success',
    'cancelado' => 'badge-danger',
];
$statusOs = [
    'aberta' => 'Aberta',
    'em_producao' => 'Em produção',
    'aguardando' => 'Aguardando',
    'finalizada' => 'Finalizada',
    'cancelada' => 'Cancelada',
];
$statusOsClass = [
    'aberta' => 'badge-info',
    'em_producao' => 'badge-primary',
    'aguardando' => 'badge-warning',
    'finalizada' => 'badge-success',
    'cancelada' => 'badge-secondary',
];
$statusCompraClass = [
    'rascunho' => 'badge-secondary',
    'solicitada' => 'badge-warning',
    'aprovada' => 'badge-primary',
    'recebida' => 'badge-success',
    'cancelada' => 'badge-danger',
];
$statusLedClass = [
    'reservado' => 'badge-info',
    'instalado' => 'badge-primary',
    'manutencao' => 'badge-warning',
    'retirado' => 'badge-success',
    'cancelado' => 'badge-danger',
];
$prioridadeClass = [
    'baixa' => 'badge-secondary',
    'media' => 'badge-info',
    'alta' => 'badge-warning',
    'urgente' => 'badge-danger',
];

if (!function_exists('relMoney')) {
    function relMoney($value): string {
        return 'R$ ' . number_format((float)$value, 2, ',', '.');
    }
}
if (!function_exists('relDate')) {
    function relDate(?string $date): string {
        return $date ? date('d/m/Y', strtotime($date)) : '-';
    }
}
if (!function_exists('relDateTime')) {
    function relDateTime(?string $date): string {
        return $date ? date('d/m/Y H:i', strtotime($date)) : '-';
    }
}
if (!function_exists('relQuery')) {
    function relQuery(array $filtros, string $tipo): string {
        return http_build_query([
            'inicio' => $filtros['inicio'],
            'fim' => $filtros['fim'],
            'cliente_id' => $filtros['cliente_id'],
            'vendedor_id' => $filtros['vendedor_id'],
            'status' => $filtros['status'],
            'tipo' => $tipo,
        ]);
    }
}
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-file-earmark-check"></i></div>
            <div class="kpi-value" style="font-size:22px"><?= relMoney($dados['resumo']['orcamentos_valor']) ?></div>
            <div class="kpi-label">Orçamentos aprovados</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-arrow-down-circle"></i></div>
            <div class="kpi-value" style="font-size:22px"><?= relMoney($dados['resumo']['recebido']) ?></div>
            <div class="kpi-label">Recebido no período</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-gear"></i></div>
            <div class="kpi-value"><?= number_format((int)$dados['resumo']['os_finalizadas']) ?></div>
            <div class="kpi-label">OS finalizadas</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-display"></i></div>
            <div class="kpi-value" style="font-size:22px"><?= relMoney($dados['resumo']['led_faturamento']) ?></div>
            <div class="kpi-label">LED no período</div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-funnel me-2 text-primary-kroma"></i>Filtros gerenciais</h6>
        <span class="badge badge-info"><?= relDate($filtros['inicio']) ?> até <?= relDate($filtros['fim']) ?></span>
    </div>
    <form method="GET" action="<?= APP_URL ?>/relatorios" class="p-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Início</label>
                <input class="form-control" type="date" name="inicio" value="<?= htmlspecialchars($filtros['inicio']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fim</label>
                <input class="form-control" type="date" name="fim" value="<?= htmlspecialchars($filtros['fim']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Cliente</label>
                <select class="form-select" name="cliente_id">
                    <option value="">Todos</option>
                    <?php foreach ($contexto['clientes'] as $cliente): ?>
                    <option value="<?= $cliente['id'] ?>" <?= (string)$filtros['cliente_id'] === (string)$cliente['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cliente['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Vendedor</label>
                <select class="form-select" name="vendedor_id">
                    <option value="">Todos</option>
                    <?php foreach ($contexto['vendedores'] as $vendedor): ?>
                    <option value="<?= $vendedor['id'] ?>" <?= (string)$filtros['vendedor_id'] === (string)$vendedor['id'] ? 'selected' : '' ?>><?= htmlspecialchars($vendedor['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Status</label>
                <input class="form-control" name="status" value="<?= htmlspecialchars($filtros['status']) ?>" placeholder="status">
            </div>
            <div class="col-md-1">
                <button class="btn btn-secondary w-100" type="submit"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </form>
    <div class="px-3 pb-3 d-flex gap-2 flex-wrap">
        <?php foreach ($tiposExportacao as $tipo => $label): ?>
        <a class="badge badge-primary text-decoration-none" href="<?= APP_URL ?>/relatorios/exportar?<?= relQuery($filtros, $tipo) ?>">
            <i class="bi bi-download"></i> CSV <?= htmlspecialchars($label) ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-file-earmark-text me-2 text-primary-kroma"></i>Comercial</h6>
                <span class="badge badge-info"><?= count($dados['comercial']) ?> registros</span>
            </div>
            <div class="table-wrapper">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Orçamento</th>
                            <th>Cliente</th>
                            <th>Valor</th>
                            <th>Margem</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dados['comercial'] as $orcamento): ?>
                        <tr>
                            <td>
                                <a href="<?= APP_URL ?>/orcamentos/<?= $orcamento['id'] ?>"><strong><?= htmlspecialchars($orcamento['codigo']) ?></strong></a>
                                <div class="small text-muted"><?= htmlspecialchars($orcamento['titulo']) ?></div>
                                <span class="badge badge-secondary"><?= relDateTime($orcamento['created_at']) ?></span>
                            </td>
                            <td>
                                <?= htmlspecialchars($orcamento['cliente_nome'] ?? '-') ?>
                                <div><span class="badge badge-secondary"><?= htmlspecialchars($orcamento['vendedor_nome'] ?? 'Sem vendedor') ?></span></div>
                            </td>
                            <td><span class="badge badge-info"><?= relMoney($orcamento['total']) ?></span></td>
                            <td>
                                <span class="badge <?= (float)$orcamento['lucro_previsto'] >= 0 ? 'badge-success' : 'badge-danger' ?>"><?= relMoney($orcamento['lucro_previsto']) ?></span>
                                <div><span class="badge badge-secondary"><?= number_format((float)$orcamento['margem_percent'], 1, ',', '.') ?>%</span></div>
                            </td>
                            <td><span class="badge <?= $statusOrcamentoClass[$orcamento['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($statusOrcamento[$orcamento['status']] ?? $orcamento['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($dados['comercial'])): ?>
                        <tr><td colspan="5"><span class="badge badge-secondary">Sem orçamentos no filtro</span></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-cash-stack me-2 text-success-kroma"></i>Financeiro</h6>
                <span class="badge badge-info"><?= count($dados['financeiro']) ?> lançamentos</span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <div class="d-flex justify-content-between border-kroma rounded-kroma p-2">
                    <span>Recebido</span>
                    <span class="badge badge-success"><?= relMoney($dados['resumo']['recebido']) ?></span>
                </div>
                <div class="d-flex justify-content-between border-kroma rounded-kroma p-2">
                    <span>A pagar no período</span>
                    <span class="badge badge-warning"><?= relMoney($dados['resumo']['a_pagar']) ?></span>
                </div>
                <?php foreach (array_slice($dados['financeiro'], 0, 8) as $conta): ?>
                <div class="border-kroma rounded-kroma p-2">
                    <div class="d-flex justify-content-between gap-2">
                        <strong><?= htmlspecialchars($conta['codigo']) ?></strong>
                        <span class="badge <?= $conta['tipo'] === 'receber' ? 'badge-success' : 'badge-danger' ?>"><?= $conta['tipo'] === 'receber' ? 'Receber' : 'Pagar' ?></span>
                    </div>
                    <div class="small text-muted"><?= htmlspecialchars($conta['pessoa'] ?: '-') ?> · <?= htmlspecialchars($conta['descricao']) ?></div>
                    <div class="mt-1">
                        <span class="badge <?= $statusFinanceiroClass[$conta['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($conta['status']) ?></span>
                        <span class="badge badge-info"><?= relMoney($conta['valor']) ?></span>
                        <span class="badge badge-secondary">Venc. <?= relDate($conta['vencimento']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($dados['financeiro'])): ?>
                <span class="badge badge-secondary align-self-start">Sem financeiro no filtro</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-gear me-2 text-warning"></i>Produção</h6>
                <span class="badge badge-info"><?= count($dados['producao']) ?> OS</span>
            </div>
            <div class="table-wrapper">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>OS</th>
                            <th>Cliente</th>
                            <th>Prazo</th>
                            <th>Etapas</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dados['producao'] as $os): ?>
                        <?php $atrasada = !empty($os['data_prometida']) && $os['data_prometida'] < date('Y-m-d') && !in_array($os['status'], ['finalizada','cancelada'], true); ?>
                        <tr>
                            <td>
                                <a href="<?= APP_URL ?>/producao/<?= $os['id'] ?>"><strong><?= htmlspecialchars($os['codigo']) ?></strong></a>
                                <div class="small text-muted"><?= htmlspecialchars($os['titulo']) ?></div>
                                <span class="badge <?= $prioridadeClass[$os['prioridade']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($os['prioridade']) ?></span>
                            </td>
                            <td>
                                <?= htmlspecialchars($os['cliente_nome'] ?? '-') ?>
                                <div><span class="badge badge-secondary"><?= htmlspecialchars($os['responsavel_nome'] ?? 'Sem responsável') ?></span></div>
                            </td>
                            <td><span class="badge <?= $atrasada ? 'badge-danger' : 'badge-info' ?>"><?= $atrasada ? 'Atrasada ' : '' ?><?= relDate($os['data_prometida']) ?></span></td>
                            <td><span class="badge badge-secondary"><?= (int)$os['etapas_concluidas'] ?>/<?= (int)$os['etapas_total'] ?></span></td>
                            <td><span class="badge <?= $statusOsClass[$os['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($statusOs[$os['status']] ?? $os['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($dados['producao'])): ?>
                        <tr><td colspan="5"><span class="badge badge-secondary">Sem OS no filtro</span></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-archive me-2 text-danger"></i>Estoque e compras</h6>
                <span class="badge badge-warning"><?= count($dados['estoque']['materiais_criticos']) ?> críticos</span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach (array_slice($dados['estoque']['materiais_criticos'], 0, 6) as $material): ?>
                <div class="border-kroma rounded-kroma p-2">
                    <div class="d-flex justify-content-between gap-2">
                        <strong><?= htmlspecialchars($material['nome']) ?></strong>
                        <span class="badge badge-danger">Crítico</span>
                    </div>
                    <div class="small text-muted"><?= htmlspecialchars($material['categoria'] ?: '-') ?></div>
                    <span class="badge badge-warning">Disp. <?= number_format((float)$material['disponivel'], 3, ',', '.') ?> <?= htmlspecialchars($material['unidade']) ?></span>
                    <span class="badge badge-secondary">Mín. <?= number_format((float)$material['estoque_minimo'], 3, ',', '.') ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($dados['estoque']['materiais_criticos'])): ?>
                <span class="badge badge-success align-self-start">Sem materiais críticos</span>
                <?php endif; ?>

                <div class="mt-2 d-flex gap-2 flex-wrap">
                    <span class="badge badge-info"><?= count($dados['estoque']['compras']) ?> compras no período</span>
                    <?php foreach (array_slice($dados['estoque']['compras'], 0, 4) as $compra): ?>
                    <span class="badge <?= $statusCompraClass[$compra['status']] ?? 'badge-secondary' ?>">
                        <?= htmlspecialchars($compra['codigo']) ?> · <?= relMoney($compra['total']) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-display me-2 text-info"></i>Painéis de LED</h6>
        <span class="badge badge-info"><?= count($dados['led']) ?> locações</span>
    </div>
    <div class="table-wrapper">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Locação</th>
                    <th>Cliente</th>
                    <th>Painel</th>
                    <th>Período</th>
                    <th>Valor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dados['led'] as $led): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($led['codigo']) ?></strong>
                        <div class="small text-muted"><?= htmlspecialchars($led['titulo']) ?></div>
                    </td>
                    <td><?= htmlspecialchars($led['cliente_nome'] ?? '-') ?></td>
                    <td>
                        <span class="badge badge-primary"><?= htmlspecialchars($led['painel_codigo']) ?></span>
                        <div class="small text-muted"><?= htmlspecialchars($led['painel_nome']) ?></div>
                    </td>
                    <td>
                        <span class="badge badge-info"><?= relDateTime($led['data_inicio']) ?></span>
                        <span class="badge badge-secondary">Fim <?= relDateTime($led['data_fim']) ?></span>
                    </td>
                    <td><span class="badge badge-success"><?= relMoney($led['valor_total']) ?></span></td>
                    <td><span class="badge <?= $statusLedClass[$led['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($led['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($dados['led'])): ?>
                <tr><td colspan="6"><span class="badge badge-secondary">Sem locações de LED no filtro</span></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
