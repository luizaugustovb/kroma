<?php
/**
 * View: Dashboard Principal — KROMA PRINT ERP
 * 
 * Variáveis: $dados (array com KPIs e listas)
 */

use App\Services\Auth;

$estagioLabels = [
    'novo_lead'         => 'Novo Lead',
    'primeiro_contato'  => 'Primeiro Contato',
    'orcamento_rapido'  => 'Orçamento Rápido',
    'orcamento_ia'      => 'Orçamento IA',
    'orcamento_enviado' => 'Orçamento Enviado',
    'negociacao'        => 'Negociação',
    'aprovado'          => 'Aprovado',
    'em_producao'       => 'Em Produção',
    'entregue'          => 'Entregue',
    'pos_venda'         => 'Pós-venda',
    'recorrencia'       => 'Recorrência',
    'perdido'           => 'Perdido',
];

$origemLabels = [
    'landing_page' => 'Landing Page',
    'whatsapp'     => 'WhatsApp',
    'indicacao'    => 'Indicação',
    'visita'       => 'Visita',
    'ligacao'      => 'Ligação',
    'email'        => 'E-mail',
    'instagram'    => 'Instagram',
    'facebook'     => 'Facebook',
    'google'       => 'Google',
    'outro'        => 'Outro',
];
?>

<!-- ====== KPIs ====== -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-people"></i></div>
            <div class="kpi-value"><?= number_format($dados['clientes_total']) ?></div>
            <div class="kpi-label">Clientes Ativos</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-funnel"></i></div>
            <div class="kpi-value"><?= number_format($dados['leads_total']) ?></div>
            <div class="kpi-label">Leads no Funil</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-star"></i></div>
            <div class="kpi-value"><?= number_format($dados['leads_novos']) ?></div>
            <div class="kpi-label">Leads Hoje</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-person-badge"></i></div>
            <div class="kpi-value"><?= number_format($dados['usuarios_ativos']) ?></div>
            <div class="kpi-label">Usuários Ativos</div>
        </div>
    </div>
</div>

<!-- ====== Gráficos ====== -->
<div class="row g-3 mb-4">

    <!-- Funil de Vendas -->
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-bar-chart-line me-2" style="color:var(--kroma-primary)"></i>Leads por Estágio do Funil</h6>
                <a href="<?= APP_URL ?>/crm" class="btn btn-sm btn-outline">Ver Kanban</a>
            </div>
            <?php
            $chartLabels = [];
            $chartValues = [];
            foreach ($dados['leads_por_estagio'] as $row) {
                $chartLabels[] = $estagioLabels[$row['estagio']] ?? $row['estagio'];
                $chartValues[] = $row['total'];
            }
            ?>
            <div style="padding: 20px; height: 260px;">
                <canvas id="chartFunil"></canvas>
            </div>
        </div>
    </div>

    <!-- Origem dos Leads -->
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-pie-chart me-2" style="color:var(--kroma-secondary)"></i>Origem dos Leads</h6>
            </div>
            <?php
            $origemLabelsChart = [];
            $origemValues = [];
            foreach ($dados['leads_por_origem'] as $row) {
                $origemLabelsChart[] = $origemLabels[$row['origem']] ?? $row['origem'];
                $origemValues[] = $row['total'];
            }
            ?>
            <div style="padding: 20px; height: 260px; display:flex; align-items:center; justify-content:center;">
                <canvas id="chartOrigem"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- ====== Últimas Atividades ====== -->
<div class="row g-3">

    <!-- Últimos Leads -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-lightning me-2" style="color:var(--kroma-warning)"></i>Últimos Leads</h6>
                <a href="<?= APP_URL ?>/crm/leads" class="btn btn-sm btn-outline">Ver todos</a>
            </div>
            <?php if (empty($dados['ultimos_leads'])): ?>
            <div style="padding:24px; text-align:center; color:var(--text-muted)">
                <i class="bi bi-inbox" style="font-size:32px; opacity:0.3"></i>
                <p class="mt-2" style="font-size:13px">Nenhum lead cadastrado ainda</p>
                <a href="<?= APP_URL ?>/crm/leads/novo" class="btn btn-primary btn-sm">Novo Lead</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table" style="margin:0">
                    <tbody>
                        <?php foreach ($dados['ultimos_leads'] as $lead): ?>
                        <tr>
                            <td style="width:36px;">
                                <div class="avatar avatar-sm">
                                    <?= strtoupper(substr($lead['nome'], 0, 1)) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:600; font-size:13px; color:var(--text-primary)">
                                    <?= htmlspecialchars($lead['nome']) ?>
                                </div>
                                <div style="font-size:11px; color:var(--text-muted)">
                                    <?= htmlspecialchars($lead['empresa'] ?? '') ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-primary" style="font-size:9px">
                                    <?= $estagioLabels[$lead['estagio']] ?? $lead['estagio'] ?>
                                </span>
                            </td>
                            <td style="font-size:11px; color:var(--text-muted)">
                                <?= date('d/m', strtotime($lead['created_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Últimos Clientes -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-person-check me-2" style="color:var(--kroma-accent)"></i>Últimos Clientes</h6>
                <a href="<?= APP_URL ?>/clientes" class="btn btn-sm btn-outline">Ver todos</a>
            </div>
            <?php if (empty($dados['ultimos_clientes'])): ?>
            <div style="padding:24px; text-align:center; color:var(--text-muted)">
                <i class="bi bi-people" style="font-size:32px; opacity:0.3"></i>
                <p class="mt-2" style="font-size:13px">Nenhum cliente cadastrado ainda</p>
                <a href="<?= APP_URL ?>/clientes/novo" class="btn btn-primary btn-sm">Novo Cliente</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table" style="margin:0">
                    <tbody>
                        <?php foreach ($dados['ultimos_clientes'] as $cli): ?>
                        <tr>
                            <td style="width:36px;">
                                <div class="avatar avatar-sm">
                                    <?= strtoupper(substr($cli['nome'], 0, 1)) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:600; font-size:13px; color:var(--text-primary)">
                                    <?= htmlspecialchars($cli['nome']) ?>
                                </div>
                                <div style="font-size:11px; color:var(--text-muted)">
                                    <?= htmlspecialchars($cli['cidade'] ?? '') ?>
                                    <?= !empty($cli['estado']) ? '/ ' . $cli['estado'] : '' ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $tipoLabels = [
                                    'cliente_final' => ['label' => 'Final', 'class' => 'badge-secondary'],
                                    'revenda'       => ['label' => 'Revenda', 'class' => 'badge-primary'],
                                    'parceiro'      => ['label' => 'Parceiro', 'class' => 'badge-info'],
                                    'corporativo'   => ['label' => 'Corp.', 'class' => 'badge-warning'],
                                    'orgao_publico' => ['label' => 'Público', 'class' => 'badge-success'],
                                ];
                                $tipo = $tipoLabels[$cli['tipo_cliente']] ?? ['label' => $cli['tipo_cliente'], 'class' => 'badge-secondary'];
                                ?>
                                <span class="badge <?= $tipo['class'] ?>" style="font-size:9px">
                                    <?= $tipo['label'] ?>
                                </span>
                            </td>
                            <td style="font-size:11px; color:var(--text-muted)">
                                <?= date('d/m', strtotime($cli['created_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Ações rápidas -->
<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-lightning-charge me-2" style="color:var(--kroma-primary)"></i>Ações Rápidas</h6>
            </div>
            <div style="padding: 16px; display:flex; flex-wrap:wrap; gap:10px;">
                <a href="<?= APP_URL ?>/crm/leads/novo" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Novo Lead
                </a>
                <a href="<?= APP_URL ?>/clientes/novo" class="btn btn-secondary btn-sm">
                    <i class="bi bi-person-plus"></i> Novo Cliente
                </a>
                <a href="<?= APP_URL ?>/crm" class="btn btn-secondary btn-sm">
                    <i class="bi bi-kanban"></i> Ver Kanban
                </a>
                <?php if (Auth::pode('usuarios')): ?>
                <a href="<?= APP_URL ?>/usuarios/novo" class="btn btn-secondary btn-sm">
                    <i class="bi bi-person-gear"></i> Novo Usuário
                </a>
                <?php endif; ?>
                <?php if (Auth::pode('empresa')): ?>
                <a href="<?= APP_URL ?>/empresa" class="btn btn-secondary btn-sm">
                    <i class="bi bi-gear"></i> Configurações
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Scripts dos gráficos -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartDefaults = {
        color: '#9AA0B4',
        font: { family: 'Inter', size: 12 }
    };
    Chart.defaults.color = chartDefaults.color;
    Chart.defaults.font  = chartDefaults.font;

    // Gráfico de Funil
    const ctxFunil = document.getElementById('chartFunil');
    if (ctxFunil) {
        new Chart(ctxFunil, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Leads',
                    data: <?= json_encode($chartValues) ?>,
                    backgroundColor: 'rgba(108, 99, 255, 0.7)',
                    borderColor: 'rgba(108, 99, 255, 1)',
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255,255,255,0.04)' },
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { maxRotation: 35, font: { size: 10 } }
                    }
                }
            }
        });
    }

    // Gráfico de Origem
    const ctxOrigem = document.getElementById('chartOrigem');
    if (ctxOrigem) {
        new Chart(ctxOrigem, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($origemLabelsChart) ?>,
                datasets: [{
                    data: <?= json_encode($origemValues) ?>,
                    backgroundColor: [
                        '#6C63FF', '#FF6584', '#00D68F', '#FFAA00',
                        '#00B0FF', '#A855F7', '#F97316', '#14B8A6'
                    ],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 12, font: { size: 11 } }
                    }
                },
                cutout: '65%'
            }
        });
    }
});
</script>
