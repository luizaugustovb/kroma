<?php
// Barra de filtro
$pAtivo = $filtro['periodo'] ?? 'todos';
$pDe    = htmlspecialchars($filtro['de']  ?? '');
$pAte   = htmlspecialchars($filtro['ate'] ?? '');
$btnClass = fn($p) => 'btn btn-sm ' . ($pAtivo === $p ? 'btn-primary' : 'btn-secondary');
?>
<div class="card mb-3">
    <div class="p-2 d-flex gap-2 flex-wrap align-items-center">
        <span class="fw-bold small me-1">Período:</span>
        <a href="?periodo=hoje" class="<?= $btnClass('hoje') ?>">Hoje</a>
        <a href="?periodo=semana" class="<?= $btnClass('semana') ?>">Esta semana</a>
        <a href="?periodo=mes" class="<?= $btnClass('mes') ?>">Este mês</a>
        <a href="?periodo=todos" class="<?= $btnClass('todos') ?>">Todos</a>
        <span class="text-muted small">ou</span>
        <form class="d-flex gap-2 align-items-center" method="get">
            <input type="date" name="de" class="form-control form-control-sm" value="<?= $pDe ?>" style="width:140px">
            <span class="small">até</span>
            <input type="date" name="ate" class="form-control form-control-sm" value="<?= $pAte ?>" style="width:140px">
            <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
            <?php if ($pDe || $pAte): ?><a href="?periodo=todos" class="btn btn-sm btn-secondary">Limpar</a><?php endif; ?>
        </form>
    </div>
</div>
<?php
$statusClasses = [
    'rascunho' => 'badge-secondary',
    'em_calculo' => 'badge-info',
    'enviado' => 'badge-primary',
    'aprovado' => 'badge-success',
    'recusado' => 'badge-warning',
    'cancelado' => 'badge-danger',
    'expirado' => 'badge-warning',
];
$totalGeral = array_sum(array_map(fn($o) => (float)$o['total'], $orcamentos));
$aprovados = array_filter($orcamentos, fn($o) => $o['status'] === 'aprovado');
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-file-earmark-text"></i></div>
            <div class="kpi-value"><?= count($orcamentos) ?></div>
            <div class="kpi-label">Orçamentos</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-check2-circle"></i></div>
            <div class="kpi-value"><?= count($aprovados) ?></div>
            <div class="kpi-label">Aprovados</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-cash-stack"></i></div>
            <div class="kpi-value" style="font-size:22px">R$ <?= number_format($totalGeral, 2, ',', '.') ?></div>
            <div class="kpi-label">Pipeline Orçado</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="kpi-value" style="font-size:22px">R$ <?= count($orcamentos) ? number_format($totalGeral / count($orcamentos), 2, ',', '.') : '0,00' ?></div>
            <div class="kpi-label">Ticket Médio</div>
        </div>
    </div>
</div>

<div class="table-wrapper">
    <table class="table datatable">
        <thead>
            <tr>
                <th>Código</th>
                <th>Título</th>
                <th>Cliente/Lead</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Total</th>
                <th>Lucro Previsto</th>
                <th>Vendedor</th>
                <th width="120">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orcamentos as $orcamento): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($orcamento['codigo']) ?></strong></td>
                    <td>
                        <?= htmlspecialchars($orcamento['titulo']) ?>
                        <div style="font-size:11px;color:var(--text-muted)">Criado em <?= date('d/m/Y', strtotime($orcamento['created_at'])) ?></div>
                    </td>
                    <td>
                        <?= htmlspecialchars($orcamento['cliente_nome'] ?: ($orcamento['lead_nome'] ?: '-')) ?>
                    </td>
                    <td><span class="badge badge-info"><?= $tipoLabels[$orcamento['tipo']] ?? $orcamento['tipo'] ?></span></td>
                    <td><span class="badge <?= $statusClasses[$orcamento['status']] ?? 'badge-secondary' ?>"><?= $statusLabels[$orcamento['status']] ?? $orcamento['status'] ?></span></td>
                    <td><strong>R$ <?= number_format((float)$orcamento['total'], 2, ',', '.') ?></strong></td>
                    <td>
                        <span class="badge <?= (float)$orcamento['lucro_previsto'] >= 0 ? 'badge-success' : 'badge-danger' ?>">
                            R$ <?= number_format((float)$orcamento['lucro_previsto'], 2, ',', '.') ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($orcamento['vendedor_nome'] ?? '-') ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/orcamentos/<?= $orcamento['id'] ?>" title="Ver"><i class="bi bi-eye"></i></a>
                            <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/orcamentos/<?= $orcamento['id'] ?>/editar" title="Editar"><i class="bi bi-pencil"></i></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>