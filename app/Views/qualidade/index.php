<?php
$statusClasses = [
    'rascunho' => 'badge-secondary',
    'em_revisao' => 'badge-warning',
    'aprovado' => 'badge-success',
    'obsoleto' => 'badge-danger',
];

function qualidadeData(?string $data): string
{
    return $data ? date('d/m/Y', strtotime($data)) : '-';
}
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-clipboard-check"></i></div>
            <div class="kpi-value"><?= number_format($resumo['total']) ?></div>
            <div class="kpi-label">POPs cadastrados</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-check2-circle"></i></div>
            <div class="kpi-value"><?= number_format($resumo['aprovados']) ?></div>
            <div class="kpi-label">Aprovados</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-value"><?= number_format($resumo['revisao']) ?></div>
            <div class="kpi-label">Em revisão</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon danger"><i class="bi bi-calendar-x"></i></div>
            <div class="kpi-value"><?= number_format($resumo['vencendo']) ?></div>
            <div class="kpi-label">Revisão em 30 dias</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-clipboard-check me-2 text-primary-kroma"></i>Procedimentos Operacionais</h6>
        <span class="badge <?= $resumo['vencidos'] > 0 ? 'badge-danger' : 'badge-success' ?>"><?= number_format($resumo['vencidos']) ?> vencidos</span>
    </div>
    <div class="table-wrapper">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Setor</th>
                    <th>Processo</th>
                    <th>Versão</th>
                    <th>Revisão</th>
                    <th>Status</th>
                    <th width="120">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pops as $pop): ?>
                <?php
                $revisaoVencida = !empty($pop['revisao_prevista']) && $pop['revisao_prevista'] < date('Y-m-d') && $pop['status'] !== 'obsoleto';
                $revisaoProxima = !empty($pop['revisao_prevista']) && $pop['revisao_prevista'] <= date('Y-m-d', strtotime('+30 days')) && $pop['status'] !== 'obsoleto';
                ?>
                <tr>
                    <td>
                        <a href="<?= APP_URL ?>/qualidade/pops/<?= $pop['id'] ?>"><strong><?= htmlspecialchars($pop['codigo']) ?></strong></a>
                        <div class="small text-muted"><?= htmlspecialchars($pop['titulo']) ?></div>
                    </td>
                    <td><span class="badge badge-info"><?= htmlspecialchars($pop['setor'] ?: '-') ?></span></td>
                    <td><?= htmlspecialchars($pop['processo_nome'] ?? '-') ?></td>
                    <td><span class="badge badge-primary">v<?= (int)$pop['versao'] ?></span></td>
                    <td>
                        <span class="badge <?= $revisaoVencida ? 'badge-danger' : ($revisaoProxima ? 'badge-warning' : 'badge-secondary') ?>">
                            <?= $revisaoVencida ? 'Vencida ' : ($revisaoProxima ? 'Próxima ' : '') ?><?= qualidadeData($pop['revisao_prevista']) ?>
                        </span>
                    </td>
                    <td><span class="badge <?= $statusClasses[$pop['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($statusLabels[$pop['status']] ?? $pop['status']) ?></span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/qualidade/pops/<?= $pop['id'] ?>" title="Ver"><i class="bi bi-eye"></i></a>
                            <?php if (App\Services\Auth::pode('pops.editar')): ?>
                            <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/qualidade/pops/<?= $pop['id'] ?>/editar" title="Editar"><i class="bi bi-pencil"></i></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($pops)): ?>
                <tr><td colspan="7"><span class="badge badge-secondary">Sem POPs cadastrados</span></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
