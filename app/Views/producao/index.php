<?php
$statusClasses = [
    'aberta' => 'badge-secondary',
    'em_producao' => 'badge-primary',
    'aguardando' => 'badge-warning',
    'finalizada' => 'badge-success',
    'cancelada' => 'badge-danger',
];
$prioridadeClasses = [
    'baixa' => 'badge-secondary',
    'media' => 'badge-info',
    'alta' => 'badge-warning',
    'urgente' => 'badge-danger',
];
$ativas = array_filter($ordens, fn($o) => !in_array($o['status'], ['finalizada','cancelada'], true));
$emProducao = array_filter($ordens, fn($o) => $o['status'] === 'em_producao');
$atrasadas = array_filter($ordens, fn($o) => !in_array($o['status'], ['finalizada','cancelada'], true) && !empty($o['data_prometida']) && $o['data_prometida'] < date('Y-m-d'));
$hoje = array_filter($ordens, fn($o) => !in_array($o['status'], ['finalizada','cancelada'], true) && !empty($o['data_prometida']) && $o['data_prometida'] === date('Y-m-d'));
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-gear"></i></div>
            <div class="kpi-value"><?= count($ativas) ?></div>
            <div class="kpi-label">OS Ativas</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-play-circle"></i></div>
            <div class="kpi-value"><?= count($emProducao) ?></div>
            <div class="kpi-label">Em Produção</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon danger"><i class="bi bi-exclamation-octagon"></i></div>
            <div class="kpi-value"><?= count($atrasadas) ?></div>
            <div class="kpi-label">Atrasadas</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-calendar-event"></i></div>
            <div class="kpi-value"><?= count($hoje) ?></div>
            <div class="kpi-label">Vencem Hoje</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php foreach (['aberta' => 'Aberta', 'em_producao' => 'Em Produção', 'aguardando' => 'Aguardando', 'finalizada' => 'Finalizada'] as $status => $label): ?>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><?= $label ?></h6>
                <span class="badge <?= $statusClasses[$status] ?>"><?= count(array_filter($ordens, fn($o) => $o['status'] === $status)) ?></span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach (array_slice(array_filter($ordens, fn($o) => $o['status'] === $status), 0, 4) as $ordem): ?>
                <?php
                    $totalEtapas = max(1, (int)$ordem['total_etapas']);
                    $progresso = round(((int)$ordem['etapas_concluidas'] / $totalEtapas) * 100);
                    $prazoClass = 'badge-secondary';
                    $prazoLabel = !empty($ordem['data_prometida']) ? date('d/m/Y', strtotime($ordem['data_prometida'])) : 'Sem prazo';
                    if (!in_array($ordem['status'], ['finalizada','cancelada'], true) && !empty($ordem['data_prometida'])) {
                        if ($ordem['data_prometida'] < date('Y-m-d')) {
                            $prazoClass = 'badge-danger';
                            $prazoLabel = 'Atrasada';
                        } elseif ($ordem['data_prometida'] === date('Y-m-d')) {
                            $prazoClass = 'badge-warning';
                            $prazoLabel = 'Hoje';
                        }
                    }
                ?>
                <a class="border-kroma rounded-kroma p-2 text-decoration-none d-block" href="<?= APP_URL ?>/producao/<?= $ordem['id'] ?>">
                    <div class="d-flex justify-content-between gap-2 mb-2">
                        <strong><?= htmlspecialchars($ordem['codigo']) ?></strong>
                        <span class="badge <?= $prioridadeClasses[$ordem['prioridade']] ?? 'badge-secondary' ?>"><?= $prioridadeLabels[$ordem['prioridade']] ?? $ordem['prioridade'] ?></span>
                    </div>
                    <div class="small fw-bold text-body"><?= htmlspecialchars($ordem['titulo']) ?></div>
                    <div class="small text-muted mb-2"><?= htmlspecialchars($ordem['cliente_nome'] ?? '-') ?></div>
                    <div class="d-flex justify-content-between">
                        <span class="badge <?= $prazoClass ?>"><?= $prazoLabel ?></span>
                        <span class="badge badge-info"><?= $progresso ?>%</span>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php if (empty(array_filter($ordens, fn($o) => $o['status'] === $status))): ?>
                    <span class="badge badge-secondary align-self-start">Sem OS</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="table-wrapper">
    <table class="table datatable">
        <thead>
            <tr>
                <th>Código</th>
                <th>OS</th>
                <th>Cliente</th>
                <th>Prioridade</th>
                <th>Status</th>
                <th>Prazo</th>
                <th>Progresso</th>
                <th>Responsável</th>
                <th width="120">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ordens as $ordem): ?>
            <?php
                $totalEtapas = max(1, (int)$ordem['total_etapas']);
                $progresso = round(((int)$ordem['etapas_concluidas'] / $totalEtapas) * 100);
                $prazoClass = 'badge-secondary';
                $prazoLabel = !empty($ordem['data_prometida']) ? date('d/m/Y', strtotime($ordem['data_prometida'])) : 'Sem prazo';
                if (!in_array($ordem['status'], ['finalizada','cancelada'], true) && !empty($ordem['data_prometida'])) {
                    if ($ordem['data_prometida'] < date('Y-m-d')) {
                        $prazoClass = 'badge-danger';
                        $prazoLabel = 'Atrasada desde ' . date('d/m/Y', strtotime($ordem['data_prometida']));
                    } elseif ($ordem['data_prometida'] === date('Y-m-d')) {
                        $prazoClass = 'badge-warning';
                        $prazoLabel = 'Vence hoje';
                    }
                }
            ?>
            <tr>
                <td><strong><?= htmlspecialchars($ordem['codigo']) ?></strong></td>
                <td>
                    <div class="fw-bold"><?= htmlspecialchars($ordem['titulo']) ?></div>
                    <div style="font-size:12px;color:var(--text-muted)"><?= (int)$ordem['total_itens'] ?> itens · <?= (int)$ordem['total_etapas'] ?> etapas</div>
                </td>
                <td><?= htmlspecialchars($ordem['cliente_nome'] ?? '-') ?></td>
                <td><span class="badge <?= $prioridadeClasses[$ordem['prioridade']] ?? 'badge-secondary' ?>"><?= $prioridadeLabels[$ordem['prioridade']] ?? $ordem['prioridade'] ?></span></td>
                <td><span class="badge <?= $statusClasses[$ordem['status']] ?? 'badge-secondary' ?>"><?= $statusLabels[$ordem['status']] ?? $ordem['status'] ?></span></td>
                <td><span class="badge <?= $prazoClass ?>"><?= $prazoLabel ?></span></td>
                <td><span class="badge badge-info"><?= $progresso ?>%</span></td>
                <td><?= htmlspecialchars($ordem['responsavel_nome'] ?? '-') ?></td>
                <td>
                    <div class="d-flex gap-1">
                        <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/producao/<?= $ordem['id'] ?>" title="Ver"><i class="bi bi-eye"></i></a>
                        <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/producao/<?= $ordem['id'] ?>/editar" title="Editar"><i class="bi bi-pencil"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
