<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$statusClasses = [
    'aberto' => 'badge-info',
    'parcial' => 'badge-warning',
    'pago' => 'badge-success',
    'cancelado' => 'badge-danger',
];
$caixaClasses = ['entrada' => 'badge-success', 'saida' => 'badge-danger'];
$receberAberto = array_filter($receber, fn($c) => in_array($c['status'], ['aberto','parcial'], true));
$pagarAberto = array_filter($pagar, fn($c) => in_array($c['status'], ['aberto','parcial'], true));
$vencidosReceber = array_filter($receberAberto, fn($c) => !empty($c['vencimento']) && $c['vencimento'] < date('Y-m-d'));
$vencidosPagar = array_filter($pagarAberto, fn($c) => !empty($c['vencimento']) && $c['vencimento'] < date('Y-m-d'));
$totalReceber = array_sum(array_map(fn($c) => max(0, (float)$c['valor'] - (float)$c['valor_pago']), $receberAberto));
$totalPagar = array_sum(array_map(fn($c) => max(0, (float)$c['valor'] - (float)$c['valor_pago']), $pagarAberto));
$saldoCaixa = array_sum(array_map(fn($m) => $m['tipo'] === 'entrada' ? (float)$m['valor'] : -(float)$m['valor'], $caixa));

function financeiroPrazoBadge(?string $vencimento, string $status): array {
    if (in_array($status, ['pago','cancelado'], true)) {
        return ['badge-secondary', '-'];
    }
    if (!$vencimento) {
        return ['badge-secondary', 'Sem vencimento'];
    }
    if ($vencimento < date('Y-m-d')) {
        return ['badge-danger', 'Vencido'];
    }
    if ($vencimento === date('Y-m-d')) {
        return ['badge-warning', 'Vence hoje'];
    }
    return ['badge-info', date('d/m/Y', strtotime($vencimento))];
}
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-arrow-down-circle"></i></div>
            <div class="kpi-value" style="font-size:22px">R$ <?= number_format($totalReceber, 2, ',', '.') ?></div>
            <div class="kpi-label">A Receber</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon danger"><i class="bi bi-arrow-up-circle"></i></div>
            <div class="kpi-value" style="font-size:22px">R$ <?= number_format($totalPagar, 2, ',', '.') ?></div>
            <div class="kpi-label">A Pagar</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-exclamation-octagon"></i></div>
            <div class="kpi-value"><?= count($vencidosReceber) + count($vencidosPagar) ?></div>
            <div class="kpi-label">Vencidos</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-wallet2"></i></div>
            <div class="kpi-value" style="font-size:22px">R$ <?= number_format($saldoCaixa, 2, ',', '.') ?></div>
            <div class="kpi-label">Caixa Recente</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-7">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-arrow-down-circle me-2 text-success-kroma"></i>Contas a Receber</h6>
                <span class="badge badge-success"><?= count($receberAberto) ?> abertas</span>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Cliente</th>
                            <th>Valor</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                            <th width="90">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($receber, 0, 20) as $conta): ?>
                        <?php [$prazoClass, $prazoLabel] = financeiroPrazoBadge($conta['vencimento'], $conta['status']); ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($conta['codigo']) ?></strong><div class="small text-muted"><?= htmlspecialchars($origemLabels[$conta['origem']] ?? $conta['origem']) ?></div></td>
                            <td><?= htmlspecialchars($conta['cliente_nome'] ?? '-') ?></td>
                            <td>
                                <strong>R$ <?= number_format((float)$conta['valor'], 2, ',', '.') ?></strong>
                                <?php if ((float)$conta['valor_pago'] > 0): ?>
                                    <div><span class="badge badge-success">Pago R$ <?= number_format((float)$conta['valor_pago'], 2, ',', '.') ?></span></div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge <?= $prazoClass ?>"><?= $prazoLabel ?></span></td>
                            <td><span class="badge <?= $statusClasses[$conta['status']] ?? 'badge-secondary' ?>"><?= $statusLabels[$conta['status']] ?? $conta['status'] ?></span></td>
                            <td><a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/financeiro/receber/<?= $conta['id'] ?>"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($receber)): ?>
                        <tr><td colspan="6"><span class="badge badge-secondary">Sem contas a receber</span></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-arrow-up-circle me-2 text-danger"></i>Contas a Pagar</h6>
                <span class="badge badge-danger"><?= count($pagarAberto) ?> abertas</span>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Fornecedor</th>
                            <th>Valor</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                            <th width="220">Baixa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($pagar, 0, 20) as $conta): ?>
                        <?php [$prazoClass, $prazoLabel] = financeiroPrazoBadge($conta['vencimento'], $conta['status']); ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($conta['codigo']) ?></strong><div class="small text-muted"><?= htmlspecialchars($conta['categoria'] ?: '-') ?></div></td>
                            <td><?= htmlspecialchars($conta['fornecedor'] ?: '-') ?></td>
                            <td><strong>R$ <?= number_format((float)$conta['valor'], 2, ',', '.') ?></strong></td>
                            <td><span class="badge <?= $prazoClass ?>"><?= $prazoLabel ?></span></td>
                            <td><span class="badge <?= $statusClasses[$conta['status']] ?? 'badge-secondary' ?>"><?= $statusLabels[$conta['status']] ?? $conta['status'] ?></span></td>
                            <td>
                                <?php if (!in_array($conta['status'], ['pago','cancelado'], true)): ?>
                                <form action="<?= APP_URL ?>/financeiro/pagar/<?= $conta['id'] ?>/baixar" method="POST" class="d-flex gap-1" data-loading>
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="data_pagamento" value="<?= date('Y-m-d') ?>">
                                    <input type="hidden" name="forma_pagamento" value="Caixa">
                                    <input class="form-control form-control-sm" name="valor_pago" value="<?= number_format((float)$conta['valor'] - (float)$conta['valor_pago'], 2, ',', '.') ?>">
                                    <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-check2"></i></button>
                                </form>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Fechada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pagar)): ?>
                        <tr><td colspan="6"><span class="badge badge-secondary">Sem contas a pagar</span></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-wallet2 me-2 text-info"></i>Caixa</h6>
                <span class="badge badge-info"><?= count($caixa) ?> movimentos</span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach ($caixa as $mov): ?>
                <div class="border-kroma rounded-kroma p-2">
                    <div class="d-flex justify-content-between gap-2">
                        <strong><?= htmlspecialchars($mov['descricao']) ?></strong>
                        <span class="badge <?= $caixaClasses[$mov['tipo']] ?? 'badge-secondary' ?>"><?= $mov['tipo'] === 'entrada' ? 'Entrada' : 'Saída' ?></span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span><?= !empty($mov['data_movimento']) ? date('d/m/Y', strtotime($mov['data_movimento'])) : '-' ?></span>
                        <strong>R$ <?= number_format((float)$mov['valor'], 2, ',', '.') ?></strong>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($caixa)): ?>
                    <span class="badge badge-secondary align-self-start">Sem movimento de caixa</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
