<?php
$severityClasses = [
    'danger' => 'badge-danger',
    'warning' => 'badge-warning',
    'info' => 'badge-info',
    'success' => 'badge-success',
    'secondary' => 'badge-secondary',
];
$severityLabels = [
    'danger' => 'Crítico',
    'warning' => 'Atenção',
    'info' => 'Informativo',
    'success' => 'OK',
    'secondary' => 'Neutro',
];

function alertaData(?string $data): string
{
    if (!$data) {
        return '-';
    }
    $timestamp = strtotime($data);
    return strlen($data) > 10 ? date('d/m/Y H:i', $timestamp) : date('d/m/Y', $timestamp);
}
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-bell"></i></div>
            <div class="kpi-value"><?= number_format($resumo['total']) ?></div>
            <div class="kpi-label">Alertas ativos</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon danger"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="kpi-value"><?= number_format($resumo['criticos']) ?></div>
            <div class="kpi-label">Críticos</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-value"><?= number_format($resumo['atencao']) ?></div>
            <div class="kpi-label">Atenção</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-check2-circle"></i></div>
            <div class="kpi-value"><?= number_format(max(0, 6 - count($resumo['por_modulo']))) ?></div>
            <div class="kpi-label">Módulos sem alerta</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-list-check me-2 text-primary-kroma"></i>Alertas Operacionais</h6>
                <span class="badge <?= $resumo['criticos'] > 0 ? 'badge-danger' : 'badge-success' ?>">
                    <?= $resumo['criticos'] > 0 ? $resumo['criticos'] . ' críticos' : 'Sem críticos' ?>
                </span>
            </div>
            <div class="table-wrapper">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Módulo</th>
                            <th>Alerta</th>
                            <th>Referência</th>
                            <th>Status</th>
                            <th width="90">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alertas as $alerta): ?>
                        <tr>
                            <td><span class="badge badge-info"><?= htmlspecialchars($alerta['modulo']) ?></span></td>
                            <td>
                                <strong><?= htmlspecialchars($alerta['titulo']) ?></strong>
                                <div class="small text-muted"><?= htmlspecialchars($alerta['descricao']) ?></div>
                                <span class="badge <?= $severityClasses[$alerta['severidade']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($alerta['tipo']) ?></span>
                            </td>
                            <td><span class="badge badge-secondary"><?= alertaData($alerta['data_referencia']) ?></span></td>
                            <td>
                                <span class="badge <?= $severityClasses[$alerta['severidade']] ?? 'badge-secondary' ?>">
                                    <?= htmlspecialchars($alerta['badge'] ?: ($severityLabels[$alerta['severidade']] ?? 'Alerta')) ?>
                                </span>
                            </td>
                            <td>
                                <a class="btn btn-icon btn-secondary btn-sm" href="<?= htmlspecialchars($alerta['url']) ?>" title="Abrir">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($alertas)): ?>
                        <tr>
                            <td colspan="5"><span class="badge badge-success">Sem alertas ativos</span></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-grid-3x3-gap me-2 text-info"></i>Resumo por Módulo</h6>
                <span class="badge badge-secondary"><?= count($resumo['por_modulo']) ?> módulos</span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach ($resumo['por_modulo'] as $modulo): ?>
                <div class="border-kroma rounded-kroma p-2">
                    <div class="d-flex justify-content-between gap-2 align-items-center">
                        <strong><?= htmlspecialchars($modulo['modulo']) ?></strong>
                        <span class="badge badge-primary"><?= (int)$modulo['total'] ?></span>
                    </div>
                    <div class="d-flex gap-2 flex-wrap mt-2">
                        <span class="badge <?= (int)$modulo['criticos'] > 0 ? 'badge-danger' : 'badge-success' ?>"><?= (int)$modulo['criticos'] ?> críticos</span>
                        <span class="badge <?= (int)$modulo['atencao'] > 0 ? 'badge-warning' : 'badge-success' ?>"><?= (int)$modulo['atencao'] ?> atenção</span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($resumo['por_modulo'])): ?>
                <span class="badge badge-success align-self-start">Operação sem alertas ativos</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
