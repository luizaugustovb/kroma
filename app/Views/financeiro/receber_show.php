<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$statusClasses = [
    'aberto' => 'badge-info',
    'parcial' => 'badge-warning',
    'pago' => 'badge-success',
    'cancelado' => 'badge-danger',
];
$restante = max(0, (float)$conta['valor'] - (float)$conta['valor_pago']);
$prazoClass = 'badge-secondary';
$prazoLabel = !empty($conta['vencimento']) ? date('d/m/Y', strtotime($conta['vencimento'])) : 'Sem vencimento';
if (!in_array($conta['status'], ['pago','cancelado'], true) && !empty($conta['vencimento'])) {
    if ($conta['vencimento'] < date('Y-m-d')) {
        $prazoClass = 'badge-danger';
        $prazoLabel = 'Vencido';
    } elseif ($conta['vencimento'] === date('Y-m-d')) {
        $prazoClass = 'badge-warning';
        $prazoLabel = 'Vence hoje';
    } else {
        $prazoClass = 'badge-info';
    }
}
?>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-arrow-down-circle me-2 text-success-kroma"></i>Conta a Receber</h6>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge <?= $statusClasses[$conta['status']] ?? 'badge-secondary' ?>"><?= $statusLabels[$conta['status']] ?? $conta['status'] ?></span>
                    <span class="badge <?= $prazoClass ?>"><?= $prazoLabel ?></span>
                    <span class="badge badge-info"><?= $origemLabels[$conta['origem']] ?? $conta['origem'] ?></span>
                </div>
            </div>
            <div class="p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="form-label">Cliente</span>
                        <div class="fw-bold"><?= htmlspecialchars($conta['cliente_nome'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-3">
                        <span class="form-label">Orçamento</span>
                        <div>
                            <?php if (!empty($conta['orcamento_id'])): ?>
                                <a class="badge badge-info text-decoration-none" href="<?= APP_URL ?>/orcamentos/<?= $conta['orcamento_id'] ?>"><?= htmlspecialchars($conta['orcamento_codigo'] ?? ('#' . $conta['orcamento_id'])) ?></a>
                            <?php else: ?>
                                <span class="badge badge-secondary">-</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <span class="form-label">OS</span>
                        <div>
                            <?php if (!empty($conta['ordem_servico_id'])): ?>
                                <a class="badge badge-primary text-decoration-none" href="<?= APP_URL ?>/producao/<?= $conta['ordem_servico_id'] ?>"><?= htmlspecialchars($conta['os_codigo'] ?? ('#' . $conta['ordem_servico_id'])) ?></a>
                            <?php else: ?>
                                <span class="badge badge-secondary">-</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-12">
                        <span class="form-label">Descrição</span>
                        <div><?= htmlspecialchars($conta['descricao']) ?></div>
                    </div>
                    <div class="col-12">
                        <span class="form-label">Observações</span>
                        <div><?= nl2br(htmlspecialchars($conta['observacoes'] ?: '-')) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-wallet2 me-2 text-info"></i>Histórico de Caixa</h6>
                <span class="badge badge-info"><?= count($movimentos) ?> movimentos</span>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th>Forma</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimentos as $mov): ?>
                        <tr>
                            <td><?= !empty($mov['data_movimento']) ? date('d/m/Y', strtotime($mov['data_movimento'])) : '-' ?></td>
                            <td><?= htmlspecialchars($mov['descricao']) ?></td>
                            <td><span class="badge badge-secondary"><?= htmlspecialchars($mov['forma_pagamento'] ?: '-') ?></span></td>
                            <td><strong>R$ <?= number_format((float)$mov['valor'], 2, ',', '.') ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($movimentos)): ?>
                        <tr><td colspan="4"><span class="badge badge-secondary">Sem baixas registradas</span></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-cash-stack me-2 text-success-kroma"></i>Valores</h6>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <div class="d-flex justify-content-between"><span>Total</span><strong>R$ <?= number_format((float)$conta['valor'], 2, ',', '.') ?></strong></div>
                <div class="d-flex justify-content-between"><span>Pago</span><span class="badge badge-success">R$ <?= number_format((float)$conta['valor_pago'], 2, ',', '.') ?></span></div>
                <div class="d-flex justify-content-between"><span>Restante</span><span class="badge <?= $restante > 0 ? 'badge-warning' : 'badge-success' ?>">R$ <?= number_format($restante, 2, ',', '.') ?></span></div>
            </div>
        </div>

        <?php if (!in_array($conta['status'], ['pago','cancelado'], true)): ?>
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-check2-circle me-2 text-primary-kroma"></i>Baixar Recebimento</h6>
            </div>
            <form action="<?= APP_URL ?>/financeiro/receber/<?= $conta['id'] ?>/baixar" method="POST" data-loading>
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="p-3">
                    <label class="form-label">Valor pago</label>
                    <input class="form-control money mb-3" name="valor_pago" value="<?= number_format($restante, 2, ',', '.') ?>">
                    <label class="form-label">Data</label>
                    <input class="form-control mb-3" type="date" name="data_pagamento" value="<?= date('Y-m-d') ?>">
                    <label class="form-label">Forma</label>
                    <input class="form-control mb-3" name="forma_pagamento" value="Pix" placeholder="Pix, dinheiro, cartão...">
                    <label class="form-label">Observações</label>
                    <textarea class="form-control mb-3" name="observacoes" rows="2"></textarea>
                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-check2-circle"></i> Registrar Baixa</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-lightning-charge me-2 text-warning"></i>Ações</h6>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php if (!in_array($conta['status'], ['pago','cancelado'], true)): ?>
                <form action="<?= APP_URL ?>/financeiro/receber/<?= $conta['id'] ?>/cancelar" method="POST" data-loading>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <button class="btn btn-danger w-100" type="submit"><i class="bi bi-x-circle"></i> Cancelar Conta</button>
                </form>
                <?php endif; ?>
                <a class="btn btn-secondary" href="<?= APP_URL ?>/financeiro"><i class="bi bi-arrow-left"></i> Voltar</a>
            </div>
        </div>
    </div>
</div>
