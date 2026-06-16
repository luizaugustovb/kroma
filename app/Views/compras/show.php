<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$statusClasses = [
    'rascunho' => 'badge-secondary',
    'solicitada' => 'badge-info',
    'aprovada' => 'badge-warning',
    'recebida' => 'badge-success',
    'cancelada' => 'badge-danger',
];
$origemClasses = [
    'manual' => 'badge-secondary',
    'estoque_critico' => 'badge-danger',
];
$podeReceber = !in_array($compra['status'], ['recebida', 'cancelada'], true);
?>

<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-cart-check me-2 text-primary-kroma"></i>Resumo da Compra</h6>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge <?= $statusClasses[$compra['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($statusLabels[$compra['status']] ?? $compra['status']) ?></span>
                    <span class="badge <?= $origemClasses[$compra['origem']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($origemLabels[$compra['origem']] ?? $compra['origem']) ?></span>
                </div>
            </div>
            <div class="p-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <span class="form-label">Código</span>
                        <div class="fw-bold"><?= htmlspecialchars($compra['codigo']) ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Fornecedor</span>
                        <div class="fw-bold"><?= htmlspecialchars($compra['fornecedor_nome'] ?: '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Documento</span>
                        <div><?= htmlspecialchars($compra['fornecedor_doc'] ?: '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Solicitante</span>
                        <div><?= htmlspecialchars($compra['solicitante_nome'] ?: '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Aprovador</span>
                        <div><?= htmlspecialchars($compra['aprovador_nome'] ?: '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Total</span>
                        <div><strong class="text-primary-kroma">R$ <?= number_format((float)$compra['total'], 2, ',', '.') ?></strong></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Solicitação</span>
                        <div><?= !empty($compra['data_solicitacao']) ? date('d/m/Y', strtotime($compra['data_solicitacao'])) : '-' ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Previsão</span>
                        <div><?= !empty($compra['previsao_entrega']) ? date('d/m/Y', strtotime($compra['previsao_entrega'])) : '-' ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Recebimento</span>
                        <div><?= !empty($compra['data_recebimento']) ? date('d/m/Y H:i', strtotime($compra['data_recebimento'])) : '-' ?></div>
                    </div>
                    <div class="col-12">
                        <span class="form-label">Observações</span>
                        <div><?= nl2br(htmlspecialchars($compra['observacoes'] ?: '-')) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-check2-circle me-2 text-success-kroma"></i>Ações</h6>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php if (!in_array($compra['status'], ['recebida', 'cancelada'], true)): ?>
                <form action="<?= APP_URL ?>/compras/<?= $compra['id'] ?>/status" method="POST" class="d-flex gap-2" data-loading>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <select class="form-select" name="status">
                        <option value="rascunho" <?= $compra['status'] === 'rascunho' ? 'selected' : '' ?>>Rascunho</option>
                        <option value="solicitada" <?= $compra['status'] === 'solicitada' ? 'selected' : '' ?>>Solicitada</option>
                        <option value="aprovada" <?= $compra['status'] === 'aprovada' ? 'selected' : '' ?>>Aprovada</option>
                        <option value="cancelada" <?= $compra['status'] === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                    </select>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-arrow-repeat"></i></button>
                </form>
                <?php else: ?>
                    <span class="badge <?= $statusClasses[$compra['status']] ?? 'badge-secondary' ?>">Compra <?= htmlspecialchars(strtolower($statusLabels[$compra['status']] ?? $compra['status'])) ?></span>
                <?php endif; ?>

                <?php if ($podeReceber): ?>
                <form action="<?= APP_URL ?>/compras/<?= $compra['id'] ?>/receber" method="POST" class="border-kroma rounded-kroma p-2" data-loading>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <label class="form-label">Vencimento da conta</label>
                    <input class="form-control mb-2" type="date" name="vencimento_conta" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                    <button class="btn btn-success w-100" type="submit"><i class="bi bi-box-arrow-in-down"></i> Receber Compra</button>
                </form>
                <?php endif; ?>

                <?php if ($contaPagar): ?>
                    <span class="badge badge-success align-self-start">Conta a pagar: <?= htmlspecialchars($contaPagar['codigo']) ?></span>
                    <span class="badge <?= ($contaPagar['status'] ?? '') === 'pago' ? 'badge-success' : 'badge-warning' ?> align-self-start">Financeiro: <?= htmlspecialchars($contaPagar['status']) ?></span>
                <?php elseif (!empty($compra['gerar_conta_pagar'])): ?>
                    <span class="badge badge-warning align-self-start">Conta será gerada no recebimento</span>
                <?php else: ?>
                    <span class="badge badge-secondary align-self-start">Sem conta a pagar automática</span>
                <?php endif; ?>

                <a class="btn btn-secondary" href="<?= APP_URL ?>/compras/<?= $compra['id'] ?>/editar"><i class="bi bi-pencil"></i> Editar Compra</a>
                <a class="btn btn-secondary" href="<?= APP_URL ?>/compras"><i class="bi bi-arrow-left"></i> Voltar</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-list-check me-2 text-info"></i>Itens da Compra</h6>
        <span class="badge badge-info"><?= count($itens) ?> itens</span>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Material</th>
                    <th>Quantidade</th>
                    <th>Custo Unitário</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $index => $item): ?>
                <tr>
                    <td><span class="badge badge-primary"><?= $index + 1 ?></span></td>
                    <td>
                        <strong><?= htmlspecialchars($item['descricao']) ?></strong>
                        <?php if (!empty($item['material_id'])): ?>
                            <div class="small text-muted"><?= htmlspecialchars(($item['material_codigo'] ?? '-') . ' - ' . ($item['material_nome'] ?? '-')) ?></div>
                        <?php else: ?>
                            <div><span class="badge badge-secondary">Sem material vinculado</span></div>
                        <?php endif; ?>
                    </td>
                    <td><?= number_format((float)$item['quantidade'], 3, ',', '.') ?> <?= htmlspecialchars($item['unidade']) ?></td>
                    <td>R$ <?= number_format((float)$item['custo_unitario'], 2, ',', '.') ?></td>
                    <td><strong>R$ <?= number_format((float)$item['total'], 2, ',', '.') ?></strong></td>
                    <td>
                        <?php if (!empty($item['recebido'])): ?>
                            <span class="badge badge-success">Recebido</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Pendente</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($itens)): ?>
                <tr><td colspan="6"><span class="badge badge-secondary">Sem itens cadastrados</span></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
