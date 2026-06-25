<?php
use App\Services\Auth;

$statusClasses = [
    'rascunho' => 'badge-secondary',
    'solicitada' => 'badge-info',
    'aprovada' => 'badge-primary',
    'recebida' => 'badge-success',
    'cancelada' => 'badge-danger',
];
$abertas = array_filter($compras, fn($c) => in_array($c['status'], ['rascunho', 'solicitada', 'aprovada'], true));
$solicitadas = array_filter($compras, fn($c) => $c['status'] === 'solicitada');
$aprovadas = array_filter($compras, fn($c) => $c['status'] === 'aprovada');
$totalAberto = array_sum(array_map(fn($c) => (float)$c['total'], $abertas));
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-cart"></i></div>
            <div class="kpi-value"><?= count($abertas) ?></div>
            <div class="kpi-label">Compras Abertas</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-send"></i></div>
            <div class="kpi-value"><?= count($solicitadas) ?></div>
            <div class="kpi-label">Solicitadas</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-check2-circle"></i></div>
            <div class="kpi-value"><?= count($aprovadas) ?></div>
            <div class="kpi-label">Aguardando Recebimento</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-cash-stack"></i></div>
            <div class="kpi-value" style="font-size:22px">R$ <?= number_format($totalAberto, 2, ',', '.') ?></div>
            <div class="kpi-label">Total Aberto</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="table-wrapper">
            <table class="table datatable">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Compra</th>
                        <th>Fornecedor</th>
                        <th>Status</th>
                        <th>Previsão</th>
                        <th>Total</th>
                        <th>Itens</th>
                        <th width="120">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($compras as $compra): ?>
                        <?php
                        $prazoClass = 'badge-secondary';
                        $prazoLabel = !empty($compra['previsao_entrega']) ? date('d/m/Y', strtotime($compra['previsao_entrega'])) : 'Sem previsão';
                        if (!in_array($compra['status'], ['recebida', 'cancelada'], true) && !empty($compra['previsao_entrega'])) {
                            if ($compra['previsao_entrega'] < date('Y-m-d')) {
                                $prazoClass = 'badge-danger';
                                $prazoLabel = 'Atrasada';
                            } elseif ($compra['previsao_entrega'] === date('Y-m-d')) {
                                $prazoClass = 'badge-warning';
                                $prazoLabel = 'Hoje';
                            } else {
                                $prazoClass = 'badge-info';
                            }
                        }
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($compra['codigo']) ?></strong></td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($compra['titulo']) ?></div>
                                <div class="small text-muted"><?= $origemLabels[$compra['origem']] ?? $compra['origem'] ?></div>
                            </td>
                            <td><?= htmlspecialchars($compra['fornecedor_nome'] ?? '-') ?></td>
                            <td><span class="badge <?= $statusClasses[$compra['status']] ?? 'badge-secondary' ?>"><?= $statusLabels[$compra['status']] ?? $compra['status'] ?></span></td>
                            <td><span class="badge <?= $prazoClass ?>"><?= $prazoLabel ?></span></td>
                            <td><strong>R$ <?= number_format((float)$compra['total'], 2, ',', '.') ?></strong></td>
                            <td><span class="badge badge-secondary"><?= (int)$compra['total_itens'] ?> itens</span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/compras/<?= $compra['id'] ?>" title="Ver"><i class="bi bi-eye"></i></a>
                                    <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/compras/<?= $compra['id'] ?>/editar" title="Editar"><i class="bi bi-pencil"></i></a>
                                    <?php if (Auth::temPerfil('administrador') && in_array($compra['status'], ['rascunho', 'cancelada'], true)): ?>
                                        <form method="POST" action="<?= APP_URL ?>/compras/<?= $compra['id'] ?>/excluir" class="d-inline" onsubmit="return confirm('EXCLUIR PERMANENTEMENTE \" <?= htmlspecialchars(addslashes($compra['titulo'] ?? $compra['codigo'])) ?>\"? Esta ação não pode ser desfeita!')">
                                            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                                            <button type="submit" class="btn btn-icon btn-danger btn-sm" title="Excluir permanentemente"><i class="bi bi-trash-fill"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-exclamation-octagon me-2 text-danger"></i>Estoque Crítico</h6>
                <span class="badge badge-danger"><?= count($materiaisCriticos) ?></span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach ($materiaisCriticos as $material): ?>
                    <div class="border-kroma rounded-kroma p-2">
                        <div class="d-flex justify-content-between gap-2">
                            <strong><?= htmlspecialchars($material['nome']) ?></strong>
                            <span class="badge badge-danger">Crítico</span>
                        </div>
                        <div class="small text-muted mb-2">
                            Disponível <?= number_format((float)$material['estoque_disponivel'], 3, ',', '.') ?>
                            | Mín. <?= number_format((float)$material['estoque_minimo'], 3, ',', '.') ?>
                        </div>
                        <a class="btn btn-secondary btn-sm" href="<?= APP_URL ?>/compras/novo?material_id=<?= $material['id'] ?>"><i class="bi bi-cart-plus"></i> Comprar</a>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($materiaisCriticos)): ?>
                    <span class="badge badge-success align-self-start">Sem material crítico</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-buildings me-2 text-info"></i>Fornecedores</h6>
                <a class="btn btn-secondary btn-sm" href="<?= APP_URL ?>/compras/fornecedores">Ver todos</a>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach ($fornecedores as $fornecedor): ?>
                    <div class="d-flex justify-content-between border-kroma rounded-kroma p-2">
                        <span><?= htmlspecialchars($fornecedor['nome']) ?></span>
                        <span class="badge <?= $fornecedor['status'] === 'ativo' ? 'badge-success' : 'badge-secondary' ?>"><?= ucfirst($fornecedor['status']) ?></span>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($fornecedores)): ?>
                    <span class="badge badge-secondary align-self-start">Sem fornecedores</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>