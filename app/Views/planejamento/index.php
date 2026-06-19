<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$statusMetaClasses = [
    'planejada' => 'badge-secondary',
    'em_andamento' => 'badge-primary',
    'atingida' => 'badge-success',
    'risco' => 'badge-warning',
    'cancelada' => 'badge-danger',
];
$statusAcaoClasses = [
    'pendente' => 'badge-secondary',
    'em_execucao' => 'badge-primary',
    'concluida' => 'badge-success',
    'cancelada' => 'badge-danger',
];
$prioridadeClasses = [
    'baixa' => 'badge-secondary',
    'media' => 'badge-info',
    'alta' => 'badge-warning',
    'urgente' => 'badge-danger',
];
$prioridadeLabels = [
    'baixa' => 'Baixa',
    'media' => 'Média',
    'alta' => 'Alta',
    'urgente' => 'Urgente',
];

if (!function_exists('planejamentoValor')) {
    function planejamentoValor($valor, string $unidade): string {
        $valor = (float)$valor;
        return match ($unidade) {
            'valor' => 'R$ ' . number_format($valor, 2, ',', '.'),
            'percentual' => number_format($valor, 1, ',', '.') . '%',
            default => number_format($valor, 0, ',', '.'),
        };
    }
}

if (!function_exists('planejamentoData')) {
    function planejamentoData(?string $data): string {
        return $data ? date('d/m/Y', strtotime($data)) : '-';
    }
}

if (!function_exists('planejamentoProgresso')) {
    function planejamentoProgresso($atual, $meta): float {
        $meta = (float)$meta;
        if ($meta <= 0) {
            return 0.0;
        }
        return min(999.0, round(((float)$atual / $meta) * 100, 1));
    }
}

$evolucaoLabels = array_map(fn($row) => substr($row['periodo_mes'], 5, 2) . '/' . substr($row['periodo_mes'], 2, 2), $evolucao);
$evolucaoMetas = array_map(fn($row) => (float)$row['meta'], $evolucao);
$evolucaoRealizado = array_map(fn($row) => (float)$row['realizado'], $evolucao);
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-bullseye"></i></div>
            <div class="kpi-value"><?= number_format((int)$resumo['total']) ?></div>
            <div class="kpi-label">Metas no período</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-check2-circle"></i></div>
            <div class="kpi-value"><?= number_format((int)$resumo['atingidas']) ?></div>
            <div class="kpi-label">Metas atingidas</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="kpi-value"><?= number_format((int)$resumo['risco']) ?></div>
            <div class="kpi-label">Metas em risco</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-list-check"></i></div>
            <div class="kpi-value"><?= number_format((int)$resumo['acoes_abertas']) ?></div>
            <div class="kpi-label">Ações abertas</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-funnel me-2 text-primary-kroma"></i>Filtros</h6>
                <span class="badge badge-info"><?= htmlspecialchars($filtros['periodo_mes']) ?></span>
            </div>
            <form method="GET" action="<?= APP_URL ?>/planejamento" class="p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Período</label>
                        <input class="form-control" type="month" name="periodo_mes" value="<?= htmlspecialchars($filtros['periodo_mes']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Todos</option>
                            <?php foreach ($statusMetaLabels as $value => $label): ?>
                            <option value="<?= $value ?>" <?= ($filtros['status'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="tipo">
                            <option value="">Todos</option>
                            <?php foreach ($tipoLabels as $value => $label): ?>
                            <option value="<?= $value ?>" <?= ($filtros['tipo'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-secondary w-100" type="submit"><i class="bi bi-search"></i> Filtrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-speedometer2 me-2 text-info"></i>Progresso médio</h6>
                <span class="badge <?= (float)$resumo['progresso_medio'] >= 100 ? 'badge-success' : ((float)$resumo['progresso_medio'] >= 70 ? 'badge-info' : 'badge-warning') ?>">
                    <?= number_format((float)$resumo['progresso_medio'], 1, ',', '.') ?>%
                </span>
            </div>
            <div class="p-3">
                <div class="progress" style="height:14px">
                    <div class="progress-bar" style="width: <?= min(100, (float)$resumo['progresso_medio']) ?>%"></div>
                </div>
                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <?php foreach ($statusMetaLabels as $status => $label): ?>
                    <span class="badge <?= $statusMetaClasses[$status] ?? 'badge-secondary' ?>">
                        <?= htmlspecialchars($label) ?>: <?= count(array_filter($metas, fn($m) => $m['status'] === $status)) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (Auth::pode('planejamento.criar')): ?>
<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-plus-circle me-2 text-success-kroma"></i>Nova meta</h6>
                <span class="badge badge-success">Meta estratégica</span>
            </div>
            <form action="<?= APP_URL ?>/planejamento/metas/novo" method="POST" class="p-3" data-loading>
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label">Título *</label>
                        <input class="form-control" name="titulo" required placeholder="Meta de vendas do mês">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Período *</label>
                        <input class="form-control" type="month" name="periodo_mes" required value="<?= htmlspecialchars($filtros['periodo_mes']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="tipo">
                            <?php foreach ($tipoLabels as $value => $label): ?>
                            <option value="<?= $value ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Indicador</label>
                        <select class="form-select" name="indicador">
                            <?php foreach ($indicadorLabels as $value => $label): ?>
                            <option value="<?= $value ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Unidade</label>
                        <select class="form-select" name="unidade">
                            <?php foreach ($unidadeLabels as $value => $label): ?>
                            <option value="<?= $value ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Vendedor / responsável</label>
                        <select class="form-select" name="usuario_id">
                            <option value="">-- Geral --</option>
                            <?php foreach ($contexto['usuarios'] as $usuario): ?>
                            <option value="<?= $usuario['id'] ?>"><?= htmlspecialchars($usuario['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Produto</label>
                        <select class="form-select" name="produto_id">
                            <option value="">-- Todos --</option>
                            <?php foreach ($contexto['produtos'] as $produto): ?>
                            <option value="<?= $produto['id'] ?>"><?= htmlspecialchars($produto['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Setor</label>
                        <input class="form-control" name="setor" list="listaSetores" placeholder="Comercial">
                        <datalist id="listaSetores">
                            <?php foreach ($contexto['setores'] as $setor): ?>
                            <option value="<?= htmlspecialchars($setor) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Meta *</label>
                        <input class="form-control" type="number" step="0.01" min="0" name="valor_meta" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Realizado inicial</label>
                        <input class="form-control" type="number" step="0.01" min="0" name="valor_atual">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Início</label>
                        <input class="form-control" type="date" name="data_inicio" value="<?= htmlspecialchars($filtros['periodo_mes']) ?>-01">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fim</label>
                        <input class="form-control" type="date" name="data_fim" value="<?= date('Y-m-t', strtotime($filtros['periodo_mes'] . '-01')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Observações</label>
                        <textarea class="form-control" name="observacoes" rows="3"></textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Cadastrar meta</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-list-check me-2 text-success-kroma"></i>Novo plano de ação</h6>
                <span class="badge badge-info">Execução</span>
            </div>
            <form action="<?= APP_URL ?>/planejamento/acoes/novo" method="POST" class="p-3" data-loading>
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label">Título *</label>
                        <input class="form-control" name="titulo" required placeholder="Ação comercial da semana">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Meta vinculada</label>
                        <select class="form-select" name="meta_id">
                            <option value="">-- Sem meta --</option>
                            <?php foreach ($contexto['metas'] as $meta): ?>
                            <option value="<?= $meta['id'] ?>"><?= htmlspecialchars($meta['codigo'] . ' - ' . $meta['titulo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Responsável</label>
                        <select class="form-select" name="responsavel_id">
                            <option value="">-- Sem responsável --</option>
                            <?php foreach ($contexto['usuarios'] as $usuario): ?>
                            <option value="<?= $usuario['id'] ?>"><?= htmlspecialchars($usuario['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Prioridade</label>
                        <select class="form-select" name="prioridade">
                            <?php foreach ($prioridadeLabels as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $value === 'media' ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Prazo</label>
                        <input class="form-control" type="date" name="prazo" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" name="descricao" rows="4" placeholder="O que precisa ser feito, por quem e com qual resultado esperado"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Resultado esperado</label>
                        <textarea class="form-control" name="resultado" rows="2"></textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-plus-circle"></i> Criar ação</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Meta</th>
                        <th>Responsável / foco</th>
                        <th>Progresso</th>
                        <th>Status</th>
                        <th width="230">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($metas as $meta): ?>
                    <?php $progresso = planejamentoProgresso($meta['valor_atual'], $meta['valor_meta']); ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($meta['codigo']) ?></strong>
                            <div class="fw-bold"><?= htmlspecialchars($meta['titulo']) ?></div>
                            <div class="d-flex gap-1 flex-wrap mt-1">
                                <span class="badge badge-info"><?= htmlspecialchars($indicadorLabels[$meta['indicador']] ?? $meta['indicador']) ?></span>
                                <span class="badge badge-secondary"><?= htmlspecialchars($tipoLabels[$meta['tipo']] ?? $meta['tipo']) ?></span>
                                <span class="badge badge-secondary"><?= htmlspecialchars($meta['periodo_mes']) ?></span>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($meta['usuario_nome'])): ?>
                            <span class="badge badge-primary"><?= htmlspecialchars($meta['usuario_nome']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($meta['produto_nome'])): ?>
                            <span class="badge badge-info"><?= htmlspecialchars($meta['produto_nome']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($meta['setor'])): ?>
                            <span class="badge badge-secondary"><?= htmlspecialchars($meta['setor']) ?></span>
                            <?php endif; ?>
                            <?php if (empty($meta['usuario_nome']) && empty($meta['produto_nome']) && empty($meta['setor'])): ?>
                            <span class="badge badge-secondary">Geral</span>
                            <?php endif; ?>
                            <div class="mt-1">
                                <span class="badge badge-info"><?= planejamentoData($meta['data_inicio']) ?></span>
                                <span class="badge badge-secondary">Fim <?= planejamentoData($meta['data_fim']) ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-between gap-2">
                                <span class="badge badge-success"><?= planejamentoValor($meta['valor_atual'], $meta['unidade']) ?></span>
                                <span class="badge badge-secondary"><?= planejamentoValor($meta['valor_meta'], $meta['unidade']) ?></span>
                            </div>
                            <div class="progress mt-2" style="height:10px">
                                <div class="progress-bar" style="width: <?= min(100, $progresso) ?>%"></div>
                            </div>
                            <span class="badge <?= $progresso >= 100 ? 'badge-success' : ($progresso >= 70 ? 'badge-info' : 'badge-warning') ?> mt-1"><?= number_format($progresso, 1, ',', '.') ?>%</span>
                            <span class="badge badge-secondary mt-1"><?= (int)$meta['acoes_concluidas'] ?>/<?= (int)$meta['acoes_total'] ?> ações</span>
                        </td>
                        <td>
                            <span class="badge <?= $statusMetaClasses[$meta['status']] ?? 'badge-secondary' ?>">
                                <?= htmlspecialchars($statusMetaLabels[$meta['status']] ?? $meta['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <?php if (Auth::pode('planejamento.editar')): ?>
                                <form action="<?= APP_URL ?>/planejamento/metas/<?= $meta['id'] ?>/sincronizar" method="POST" data-loading>
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <button class="btn btn-secondary btn-sm" type="submit"><i class="bi bi-arrow-repeat"></i> Sincronizar</button>
                                </form>
                                <?php foreach (['em_andamento' => 'Andamento', 'atingida' => 'Atingir', 'risco' => 'Risco', 'cancelada' => 'Cancelar'] as $status => $label): ?>
                                <?php if ($meta['status'] !== $status): ?>
                                <form action="<?= APP_URL ?>/planejamento/metas/<?= $meta['id'] ?>/status" method="POST" data-loading>
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="status" value="<?= $status ?>">
                                    <button class="btn btn-secondary btn-sm" type="submit"><?= htmlspecialchars($label) ?></button>
                                </form>
                                <?php endif; ?>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <span class="badge badge-secondary">Sem permissão</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php if (!empty($meta['observacoes'])): ?>
                    <tr>
                        <td colspan="5">
                            <span class="badge badge-secondary">Observações</span>
                            <span class="small text-muted"><?= nl2br(htmlspecialchars($meta['observacoes'])) ?></span>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (empty($metas)): ?>
                    <tr><td colspan="5"><span class="badge badge-secondary">Nenhuma meta cadastrada para este filtro</span></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-graph-up-arrow me-2 text-info"></i>Evolução mensal</h6>
                <span class="badge badge-info"><?= count($evolucao) ?> meses</span>
            </div>
            <div style="height:300px; padding:16px">
                <canvas id="chartPlanejamento"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="table-wrapper">
    <table class="table">
        <thead>
            <tr>
                <th>Plano de ação</th>
                <th>Meta</th>
                <th>Prazo</th>
                <th>Status</th>
                <th width="260">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($acoes as $acao): ?>
            <?php $atrasada = !empty($acao['prazo']) && $acao['prazo'] < date('Y-m-d') && !in_array($acao['status'], ['concluida','cancelada'], true); ?>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($acao['titulo']) ?></strong>
                    <div class="small text-muted"><?= nl2br(htmlspecialchars($acao['descricao'] ?: '-')) ?></div>
                    <span class="badge <?= $prioridadeClasses[$acao['prioridade']] ?? 'badge-secondary' ?>">
                        <?= htmlspecialchars($prioridadeLabels[$acao['prioridade']] ?? $acao['prioridade']) ?>
                    </span>
                    <?php if (!empty($acao['responsavel_nome'])): ?>
                    <span class="badge badge-primary"><?= htmlspecialchars($acao['responsavel_nome']) ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($acao['meta_id'])): ?>
                    <span class="badge badge-info"><?= htmlspecialchars($acao['meta_codigo']) ?></span>
                    <div class="small text-muted"><?= htmlspecialchars($acao['meta_titulo']) ?></div>
                    <?php else: ?>
                    <span class="badge badge-secondary">Sem meta</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge <?= $atrasada ? 'badge-danger' : 'badge-secondary' ?>">
                        <?= $atrasada ? 'Atrasada ' : '' ?><?= planejamentoData($acao['prazo']) ?>
                    </span>
                </td>
                <td>
                    <span class="badge <?= $statusAcaoClasses[$acao['status']] ?? 'badge-secondary' ?>">
                        <?= htmlspecialchars($statusAcaoLabels[$acao['status']] ?? $acao['status']) ?>
                    </span>
                </td>
                <td>
                    <?php if (Auth::pode('planejamento.editar')): ?>
                    <div class="d-flex gap-1 flex-wrap">
                        <?php foreach (['em_execucao' => 'Executar', 'concluida' => 'Concluir', 'cancelada' => 'Cancelar'] as $status => $label): ?>
                        <?php if ($acao['status'] !== $status): ?>
                        <form action="<?= APP_URL ?>/planejamento/acoes/<?= $acao['id'] ?>/status" method="POST" data-loading>
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="status" value="<?= $status ?>">
                            <input type="hidden" name="resultado" value="<?= htmlspecialchars($acao['resultado'] ?? '') ?>">
                            <button class="btn btn-secondary btn-sm" type="submit"><?= htmlspecialchars($label) ?></button>
                        </form>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <span class="badge badge-secondary">Sem permissão</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if (!empty($acao['resultado'])): ?>
            <tr>
                <td colspan="5">
                    <span class="badge badge-success">Resultado</span>
                    <span class="small text-muted"><?= nl2br(htmlspecialchars($acao['resultado'])) ?></span>
                </td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
            <?php if (empty($acoes)): ?>
            <tr><td colspan="5"><span class="badge badge-secondary">Nenhum plano de ação cadastrado</span></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chart = document.getElementById('chartPlanejamento');
    if (!chart) return;

    Chart.defaults.color = '#9AA0B4';
    Chart.defaults.font = { family: 'Inter', size: 12 };
    const moneyTick = value => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', maximumFractionDigits: 0 }).format(value);

    new Chart(chart, {
        type: 'bar',
        data: {
            labels: <?= json_encode($evolucaoLabels) ?>,
            datasets: [
                { label: 'Meta', data: <?= json_encode($evolucaoMetas) ?>, backgroundColor: 'rgba(108, 99, 255, 0.7)', borderRadius: 6 },
                { label: 'Realizado', data: <?= json_encode($evolucaoRealizado) ?>, backgroundColor: 'rgba(0, 214, 143, 0.7)', borderRadius: 6 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { callback: moneyTick } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
