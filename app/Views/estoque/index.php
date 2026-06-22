<?php

use App\Services\Auth;

$statusClasses = [
    'ativo' => 'badge-success',
    'inativo' => 'badge-secondary',
];
$tipoClasses = [
    'entrada' => 'badge-success',
    'saida' => 'badge-danger',
    'ajuste' => 'badge-info',
    'reserva' => 'badge-warning',
    'baixa_reserva' => 'badge-primary',
    'cancelamento_reserva' => 'badge-secondary',
];
$ativos = array_filter($materiais, fn($m) => $m['status'] === 'ativo');
$criticos = array_filter($materiais, fn($m) => $m['status'] === 'ativo' && (float)$m['estoque_disponivel'] <= (float)$m['estoque_minimo']);
$reservados = array_filter($materiais, fn($m) => (float)$m['estoque_reservado'] > 0);
$valorEstoque = array_sum(array_map(fn($m) => (float)$m['estoque_atual'] * (float)$m['custo_atual'], $materiais));
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-archive"></i></div>
            <div class="kpi-value"><?= count($materiais) ?></div>
            <div class="kpi-label">Materiais</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-check2-circle"></i></div>
            <div class="kpi-value"><?= count($ativos) ?></div>
            <div class="kpi-label">Ativos</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon danger"><i class="bi bi-exclamation-octagon"></i></div>
            <div class="kpi-value"><?= count($criticos) ?></div>
            <div class="kpi-label">Críticos</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-cash-stack"></i></div>
            <div class="kpi-value" style="font-size:22px">R$ <?= number_format($valorEstoque, 2, ',', '.') ?></div>
            <div class="kpi-label">Valor em Estoque</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0"><i class="bi bi-archive me-2 text-primary-kroma"></i>Materiais em Estoque</h6>
            <?php if (Auth::temPerfil('administrador')): ?>
                <a href="<?= APP_URL ?>/estoque/correcao" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i> Correção de Estoque</a>
            <?php endif; ?>
        </div>
        <div class="table-wrapper">
            <table class="table datatable">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Material</th>
                        <th>Categoria</th>
                        <th>Saldo</th>
                        <th>Reservado</th>
                        <th>Disponível</th>
                        <th>Custo</th>
                        <th>Alerta</th>
                        <th width="120">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materiais as $material): ?>
                        <?php
                        $disponivel = (float)$material['estoque_disponivel'];
                        $minimo = (float)$material['estoque_minimo'];
                        $alertaClass = 'badge-success';
                        $alertaLabel = 'OK';
                        if ($material['status'] !== 'ativo') {
                            $alertaClass = 'badge-secondary';
                            $alertaLabel = 'Inativo';
                        } elseif ($disponivel <= $minimo) {
                            $alertaClass = 'badge-danger';
                            $alertaLabel = 'Crítico';
                        } elseif ($minimo > 0 && $disponivel <= ($minimo * 1.5)) {
                            $alertaClass = 'badge-warning';
                            $alertaLabel = 'Baixo';
                        }
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($material['codigo'] ?? '-') ?></strong></td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($material['nome']) ?></div>
                                <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($material['localizacao'] ?: '-') ?></div>
                            </td>
                            <td><?= htmlspecialchars($material['categoria'] ?: '-') ?></td>
                            <td><?= number_format((float)$material['estoque_atual'], 3, ',', '.') ?> <?= htmlspecialchars($material['unidade']) ?></td>
                            <td><span class="badge badge-warning"><?= number_format((float)$material['estoque_reservado'], 3, ',', '.') ?></span></td>
                            <td><strong><?= number_format($disponivel, 3, ',', '.') ?></strong></td>
                            <td>R$ <?= number_format((float)$material['custo_atual'], 2, ',', '.') ?></td>
                            <td><span class="badge <?= $alertaClass ?>"><?= $alertaLabel ?></span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/estoque/<?= $material['id'] ?>" title="Ver"><i class="bi bi-eye"></i></a>
                                    <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/estoque/<?= $material['id'] ?>/editar" title="Editar"><i class="bi bi-pencil"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-clock-history me-2 text-info"></i>Movimentações Recentes</h6>
                <span class="badge badge-info"><?= count($movimentacoes) ?></span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach ($movimentacoes as $mov): ?>
                    <div class="border-kroma rounded-kroma p-2">
                        <div class="d-flex justify-content-between gap-2 mb-1">
                            <strong><?= htmlspecialchars($mov['material_nome']) ?></strong>
                            <span class="badge <?= $tipoClasses[$mov['tipo']] ?? 'badge-secondary' ?>"><?= $tipoMovLabels[$mov['tipo']] ?? $mov['tipo'] ?></span>
                        </div>
                        <div class="small text-muted">
                            <?= number_format((float)$mov['quantidade'], 3, ',', '.') ?>
                            <?php if (!empty($mov['os_codigo'])): ?>
                                · <?= htmlspecialchars($mov['os_codigo']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="small text-muted"><?= date('d/m/Y H:i', strtotime($mov['created_at'])) ?></div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($movimentacoes)): ?>
                    <span class="badge badge-secondary align-self-start">Sem movimentações</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>