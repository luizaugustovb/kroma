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
$prioridadeClass = [
    'baixa' => 'badge-secondary',
    'media' => 'badge-info',
    'alta' => 'badge-warning',
    'urgente' => 'badge-danger',
];
$statusCompra = [
    'rascunho' => 'Rascunho',
    'solicitada' => 'Solicitada',
    'aprovada' => 'Aprovada',
    'recebida' => 'Recebida',
    'cancelada' => 'Cancelada',
];
$statusCompraClass = [
    'rascunho' => 'badge-secondary',
    'solicitada' => 'badge-warning',
    'aprovada' => 'badge-primary',
    'recebida' => 'badge-success',
    'cancelada' => 'badge-danger',
];

function biMoney(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

function biDate(?string $date): string
{
    return $date ? date('d/m/Y', strtotime($date)) : '-';
}

function biMesLabel(string $mes): string
{
    $partes = explode('-', $mes);
    if (count($partes) !== 2) {
        return $mes;
    }
    return $partes[1] . '/' . substr($partes[0], 2);
}

$kpis = $dados['kpis'];
$rhResumo = $dados['rh_resumo'];
$orcamentoLabels = array_map(fn($row) => $statusOrcamento[$row['status']] ?? $row['status'], $dados['orcamentos_status']);
$orcamentoValues = array_map(fn($row) => (int)$row['total'], $dados['orcamentos_status']);
$orcamentoValorValues = array_map(fn($row) => (float)$row['valor'], $dados['orcamentos_status']);
$mesLabels = array_map(fn($row) => biMesLabel($row['mes']), $dados['orcamentos_meses']);
$mesVendas = array_map(fn($row) => (float)$row['venda'], $dados['orcamentos_meses']);
$mesLucro = array_map(fn($row) => (float)$row['lucro'], $dados['orcamentos_meses']);
$caixaLabels = array_map(fn($row) => biMesLabel($row['mes']), $dados['caixa_meses']);
$caixaEntradas = array_map(fn($row) => (float)$row['entradas'], $dados['caixa_meses']);
$caixaSaidas = array_map(fn($row) => (float)$row['saidas'], $dados['caixa_meses']);
$caixaSaldo = array_map(fn($row) => (float)$row['entradas'] - (float)$row['saidas'], $dados['caixa_meses']);
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-arrow-down-circle"></i></div>
            <div class="kpi-value" style="font-size:22px"><?= biMoney((float)$kpis['receber_aberto']) ?></div>
            <div class="kpi-label">A receber aberto</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon danger"><i class="bi bi-arrow-up-circle"></i></div>
            <div class="kpi-value" style="font-size:22px"><?= biMoney((float)$kpis['pagar_aberto']) ?></div>
            <div class="kpi-label">A pagar aberto</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-wallet2"></i></div>
            <div class="kpi-value" style="font-size:22px"><?= biMoney((float)$kpis['saldo_previsto']) ?></div>
            <div class="kpi-label">Saldo previsto</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-percent"></i></div>
            <div class="kpi-value"><?= number_format((float)$kpis['margem_media'], 1, ',', '.') ?>%</div>
            <div class="kpi-label">Margem média enviada</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="kpi-value" style="font-size:22px"><?= biMoney((float)$kpis['receita_mes']) ?></div>
            <div class="kpi-label">Receita recebida no mês</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-file-earmark-check"></i></div>
            <div class="kpi-value" style="font-size:22px"><?= biMoney((float)$kpis['orcamentos_aprovados_mes']) ?></div>
            <div class="kpi-label">Orçamentos aprovados no mês</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-clock-history"></i></div>
            <div class="kpi-value"><?= number_format((int)$kpis['os_atrasadas']) ?></div>
            <div class="kpi-label">OS atrasadas</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon danger"><i class="bi bi-exclamation-octagon"></i></div>
            <div class="kpi-value"><?= number_format((int)$kpis['estoque_critico']) ?></div>
            <div class="kpi-label">Materiais críticos</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-bar-chart-line me-2 text-primary-kroma"></i>Orçamentos dos últimos meses</h6>
                <span class="badge badge-info"><?= count($dados['orcamentos_meses']) ?> meses</span>
            </div>
            <div style="height:300px; padding:16px">
                <canvas id="chartOrcamentosMes"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-pie-chart me-2 text-info"></i>Status dos orçamentos</h6>
                <span class="badge badge-secondary"><?= array_sum($orcamentoValues) ?> registros</span>
            </div>
            <div style="height:300px; padding:16px">
                <canvas id="chartOrcamentosStatus"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-wallet2 me-2 text-success-kroma"></i>Fluxo de caixa mensal</h6>
                <span class="badge <?= (float)$kpis['saldo_mes'] >= 0 ? 'badge-success' : 'badge-danger' ?>">Saldo mês <?= biMoney((float)$kpis['saldo_mes']) ?></span>
            </div>
            <div style="height:300px; padding:16px">
                <canvas id="chartCaixaMes"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-person-badge me-2 text-warning"></i>RH e recursos</h6>
                <span class="badge badge-info"><?= number_format((int)$rhResumo['colaboradores_ativos']) ?> colaboradores</span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <div class="d-flex justify-content-between border-kroma rounded-kroma p-2">
                    <span>Folha mensal ativa</span>
                    <span class="badge badge-success"><?= biMoney((float)$rhResumo['folha_mensal']) ?></span>
                </div>
                <div class="d-flex justify-content-between border-kroma rounded-kroma p-2">
                    <span>Custo/hora médio</span>
                    <span class="badge badge-primary"><?= biMoney((float)$rhResumo['custo_hora_medio']) ?></span>
                </div>
                <div class="d-flex justify-content-between border-kroma rounded-kroma p-2">
                    <span>Equipamentos em manutenção</span>
                    <span class="badge <?= (int)$rhResumo['equipamentos_manutencao'] > 0 ? 'badge-warning' : 'badge-success' ?>"><?= number_format((int)$rhResumo['equipamentos_manutencao']) ?></span>
                </div>
                <div class="d-flex justify-content-between border-kroma rounded-kroma p-2">
                    <span>Veículos em manutenção</span>
                    <span class="badge <?= (int)$rhResumo['veiculos_manutencao'] > 0 ? 'badge-warning' : 'badge-success' ?>"><?= number_format((int)$rhResumo['veiculos_manutencao']) ?></span>
                </div>
                <div class="d-flex justify-content-between border-kroma rounded-kroma p-2">
                    <span>Compras pendentes</span>
                    <span class="badge <?= (int)$kpis['compras_pendentes'] > 0 ? 'badge-warning' : 'badge-success' ?>"><?= number_format((int)$kpis['compras_pendentes']) ?></span>
                </div>
                <div class="d-flex justify-content-between border-kroma rounded-kroma p-2">
                    <span>OS em produção</span>
                    <span class="badge badge-primary"><?= number_format((int)$kpis['os_producao']) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-7">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-file-earmark-text me-2 text-primary-kroma"></i>Orçamentos recentes</h6>
                <a class="badge badge-primary text-decoration-none" href="<?= APP_URL ?>/orcamentos">Ver orçamentos</a>
            </div>
            <div class="table-wrapper">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Cliente</th>
                            <th>Valor</th>
                            <th>Lucro</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dados['orcamentos_recentes'] as $orcamento): ?>
                        <tr>
                            <td>
                                <a href="<?= APP_URL ?>/orcamentos/<?= $orcamento['id'] ?>"><strong><?= htmlspecialchars($orcamento['codigo']) ?></strong></a>
                                <div class="small text-muted"><?= htmlspecialchars($orcamento['titulo']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($orcamento['cliente_nome'] ?? '-') ?></td>
                            <td><span class="badge badge-info"><?= biMoney((float)$orcamento['total']) ?></span></td>
                            <td><span class="badge <?= (float)$orcamento['lucro_previsto'] >= 0 ? 'badge-success' : 'badge-danger' ?>"><?= biMoney((float)$orcamento['lucro_previsto']) ?></span></td>
                            <td><span class="badge <?= $statusOrcamentoClass[$orcamento['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($statusOrcamento[$orcamento['status']] ?? $orcamento['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($dados['orcamentos_recentes'])): ?>
                        <tr><td colspan="5"><span class="badge badge-secondary">Sem orçamentos recentes</span></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-gear me-2 text-warning"></i>Produção em atenção</h6>
                <a class="badge badge-primary text-decoration-none" href="<?= APP_URL ?>/producao">Ver produção</a>
            </div>
            <div class="table-wrapper">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>OS</th>
                            <th>Cliente</th>
                            <th>Prazo</th>
                            <th>Prioridade</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dados['os_risco'] as $os): ?>
                        <?php $atrasada = !empty($os['data_prometida']) && $os['data_prometida'] < date('Y-m-d'); ?>
                        <tr>
                            <td>
                                <a href="<?= APP_URL ?>/producao/<?= $os['id'] ?>"><strong><?= htmlspecialchars($os['codigo']) ?></strong></a>
                                <div class="small text-muted"><?= htmlspecialchars($os['titulo']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($os['cliente_nome'] ?? '-') ?></td>
                            <td><span class="badge <?= $atrasada ? 'badge-danger' : 'badge-info' ?>"><?= $atrasada ? 'Atrasada ' : '' ?><?= biDate($os['data_prometida']) ?></span></td>
                            <td><span class="badge <?= $prioridadeClass[$os['prioridade']] ?? 'badge-secondary' ?>"><?= htmlspecialchars(ucfirst($os['prioridade'])) ?></span></td>
                            <td><span class="badge <?= $statusOsClass[$os['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($statusOs[$os['status']] ?? $os['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($dados['os_risco'])): ?>
                        <tr><td colspan="5"><span class="badge badge-success">Sem produção em risco</span></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-archive me-2 text-danger"></i>Estoque crítico</h6>
                <a class="badge badge-primary text-decoration-none" href="<?= APP_URL ?>/estoque">Ver estoque</a>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach ($dados['estoque_critico'] as $material): ?>
                <div class="border-kroma rounded-kroma p-2">
                    <div class="d-flex justify-content-between gap-2">
                        <strong><?= htmlspecialchars($material['nome']) ?></strong>
                        <span class="badge badge-danger">Crítico</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span><?= htmlspecialchars($material['categoria'] ?: '-') ?></span>
                        <span>Disp. <?= number_format((float)$material['disponivel'], 3, ',', '.') ?> <?= htmlspecialchars($material['unidade']) ?></span>
                    </div>
                    <div class="mt-1">
                        <span class="badge badge-warning">Mínimo <?= number_format((float)$material['estoque_minimo'], 3, ',', '.') ?></span>
                        <span class="badge badge-info">Custo <?= biMoney((float)$material['custo_atual']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($dados['estoque_critico'])): ?>
                    <span class="badge badge-success align-self-start">Sem materiais críticos</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Financeiro vencido</h6>
                <a class="badge badge-primary text-decoration-none" href="<?= APP_URL ?>/financeiro">Ver financeiro</a>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach ($dados['financeiro_vencido'] as $conta): ?>
                <?php $saldo = max(0, (float)$conta['valor'] - (float)$conta['valor_pago']); ?>
                <div class="border-kroma rounded-kroma p-2">
                    <div class="d-flex justify-content-between gap-2">
                        <strong><?= htmlspecialchars($conta['codigo']) ?></strong>
                        <span class="badge <?= $conta['tipo'] === 'receber' ? 'badge-success' : 'badge-danger' ?>"><?= $conta['tipo'] === 'receber' ? 'Receber' : 'Pagar' ?></span>
                    </div>
                    <div class="small text-muted"><?= htmlspecialchars($conta['descricao']) ?></div>
                    <div class="mt-1">
                        <span class="badge badge-danger">Venceu <?= biDate($conta['vencimento']) ?></span>
                        <span class="badge badge-info"><?= biMoney($saldo) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($dados['financeiro_vencido'])): ?>
                    <span class="badge badge-success align-self-start">Sem financeiro vencido</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-cart me-2 text-info"></i>Compras pendentes</h6>
                <a class="badge badge-primary text-decoration-none" href="<?= APP_URL ?>/compras">Ver compras</a>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach ($dados['compras_pendentes'] as $compra): ?>
                <?php $atrasada = !empty($compra['previsao_entrega']) && $compra['previsao_entrega'] < date('Y-m-d'); ?>
                <div class="border-kroma rounded-kroma p-2">
                    <div class="d-flex justify-content-between gap-2">
                        <a href="<?= APP_URL ?>/compras/<?= $compra['id'] ?>"><strong><?= htmlspecialchars($compra['codigo']) ?></strong></a>
                        <span class="badge <?= $statusCompraClass[$compra['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($statusCompra[$compra['status']] ?? $compra['status']) ?></span>
                    </div>
                    <div class="small text-muted"><?= htmlspecialchars($compra['titulo']) ?></div>
                    <div class="mt-1">
                        <span class="badge <?= $atrasada ? 'badge-danger' : 'badge-info' ?>"><?= $atrasada ? 'Atrasada ' : 'Previsão ' ?><?= biDate($compra['previsao_entrega']) ?></span>
                        <span class="badge badge-secondary"><?= biMoney((float)$compra['total']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($dados['compras_pendentes'])): ?>
                    <span class="badge badge-success align-self-start">Sem compras pendentes</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.defaults.color = '#9AA0B4';
    Chart.defaults.font = { family: 'Inter', size: 12 };
    const gridColor = 'rgba(255,255,255,0.06)';
    const moneyTick = value => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', maximumFractionDigits: 0 }).format(value);

    const chartOrcamentosMes = document.getElementById('chartOrcamentosMes');
    if (chartOrcamentosMes) {
        new Chart(chartOrcamentosMes, {
            type: 'bar',
            data: {
                labels: <?= json_encode($mesLabels) ?>,
                datasets: [
                    { label: 'Venda', data: <?= json_encode($mesVendas) ?>, backgroundColor: 'rgba(0, 176, 255, 0.7)', borderRadius: 6 },
                    { label: 'Lucro', data: <?= json_encode($mesLucro) ?>, backgroundColor: 'rgba(0, 214, 143, 0.7)', borderRadius: 6 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { beginAtZero: true, grid: { color: gridColor }, ticks: { callback: moneyTick } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    const chartOrcamentosStatus = document.getElementById('chartOrcamentosStatus');
    if (chartOrcamentosStatus) {
        new Chart(chartOrcamentosStatus, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($orcamentoLabels) ?>,
                datasets: [{
                    data: <?= json_encode($orcamentoValues) ?>,
                    valor: <?= json_encode($orcamentoValorValues) ?>,
                    backgroundColor: ['#6C63FF', '#00B0FF', '#FFAA00', '#00D68F', '#FF3D71', '#5C6278', '#FF6584'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            afterLabel: context => 'Valor: ' + moneyTick(context.dataset.valor[context.dataIndex] || 0)
                        }
                    }
                }
            }
        });
    }

    const chartCaixaMes = document.getElementById('chartCaixaMes');
    if (chartCaixaMes) {
        new Chart(chartCaixaMes, {
            type: 'line',
            data: {
                labels: <?= json_encode($caixaLabels) ?>,
                datasets: [
                    { label: 'Entradas', data: <?= json_encode($caixaEntradas) ?>, borderColor: '#00D68F', backgroundColor: 'rgba(0, 214, 143, 0.12)', tension: 0.35, fill: true },
                    { label: 'Saídas', data: <?= json_encode($caixaSaidas) ?>, borderColor: '#FF3D71', backgroundColor: 'rgba(255, 61, 113, 0.08)', tension: 0.35, fill: true },
                    { label: 'Saldo', data: <?= json_encode($caixaSaldo) ?>, borderColor: '#FFAA00', backgroundColor: 'rgba(255, 170, 0, 0.08)', tension: 0.35, fill: false }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { grid: { color: gridColor }, ticks: { callback: moneyTick } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
