<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$tipoClasses = [
    'entrada' => 'badge-success',
    'saida' => 'badge-danger',
    'ajuste' => 'badge-info',
    'reserva' => 'badge-warning',
    'baixa_reserva' => 'badge-primary',
    'cancelamento_reserva' => 'badge-secondary',
];
$disponivel = (float)$material['estoque_disponivel'];
$alertaClass = 'badge-success';
$alertaLabel = 'OK';
if ($material['status'] !== 'ativo') {
    $alertaClass = 'badge-secondary';
    $alertaLabel = 'Inativo';
} elseif ($disponivel <= (float)$material['estoque_minimo']) {
    $alertaClass = 'badge-danger';
    $alertaLabel = 'Crítico';
} elseif ((float)$material['estoque_minimo'] > 0 && $disponivel <= ((float)$material['estoque_minimo'] * 1.5)) {
    $alertaClass = 'badge-warning';
    $alertaLabel = 'Baixo';
}
?>

<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-archive me-2 text-primary-kroma"></i>Ficha do Material</h6>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge <?= $alertaClass ?>"><?= $alertaLabel ?></span>
                    <span class="badge <?= $material['status'] === 'ativo' ? 'badge-success' : 'badge-secondary' ?>"><?= $statusLabels[$material['status']] ?? $material['status'] ?></span>
                </div>
            </div>
            <div class="p-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <span class="form-label">Código</span>
                        <div class="fw-bold"><?= htmlspecialchars($material['codigo'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Categoria</span>
                        <div class="fw-bold"><?= htmlspecialchars($material['categoria'] ?: '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Unidade</span>
                        <div class="fw-bold"><?= htmlspecialchars($material['unidade'] ?: '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Fornecedor</span>
                        <div><?= htmlspecialchars($material['fornecedor'] ?: '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Localização</span>
                        <div><?= htmlspecialchars($material['localizacao'] ?: '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Custo atual</span>
                        <div><strong>R$ <?= number_format((float)$material['custo_atual'], 2, ',', '.') ?></strong></div>
                    </div>
                    <div class="col-12">
                        <span class="form-label">Observações</span>
                        <div><?= nl2br(htmlspecialchars($material['observacoes'] ?: '-')) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-box-seam me-2 text-success-kroma"></i>Saldos</h6>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <div class="d-flex justify-content-between"><span>Saldo atual</span><strong><?= number_format((float)$material['estoque_atual'], 3, ',', '.') ?> <?= htmlspecialchars($material['unidade']) ?></strong></div>
                <div class="d-flex justify-content-between"><span>Reservado</span><span class="badge badge-warning"><?= number_format((float)$material['estoque_reservado'], 3, ',', '.') ?></span></div>
                <div class="d-flex justify-content-between"><span>Disponível</span><span class="badge badge-info"><?= number_format($disponivel, 3, ',', '.') ?></span></div>
                <div class="d-flex justify-content-between"><span>Estoque mínimo</span><span class="badge badge-secondary"><?= number_format((float)$material['estoque_minimo'], 3, ',', '.') ?></span></div>
                <hr>
                <div class="d-flex justify-content-between"><span>Valor estimado</span><strong>R$ <?= number_format((float)$material['estoque_atual'] * (float)$material['custo_atual'], 2, ',', '.') ?></strong></div>
                <a class="btn btn-secondary" href="<?= APP_URL ?>/estoque/<?= $material['id'] ?>/editar"><i class="bi bi-pencil"></i> Editar Material</a>
                <a class="btn btn-secondary" href="<?= APP_URL ?>/estoque"><i class="bi bi-arrow-left"></i> Voltar</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-arrow-left-right me-2 text-primary-kroma"></i>Nova Movimentação</h6>
            </div>
            <form action="<?= APP_URL ?>/estoque/<?= $material['id'] ?>/movimentar" method="POST" data-loading>
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" name="tipo">
                                <?php foreach ($tipoMovLabels as $value => $label): ?>
                                <option value="<?= $value ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Quantidade</label>
                            <input class="form-control" name="quantidade" value="1">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Custo unitário</label>
                            <input class="form-control money" name="custo_unitario" value="<?= number_format((float)$material['custo_atual'], 2, ',', '.') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">OS vinculada</label>
                            <select class="form-select" name="ordem_servico_id">
                                <option value="">-- Sem OS --</option>
                                <?php foreach ($ordens as $ordem): ?>
                                <option value="<?= $ordem['id'] ?>"><?= htmlspecialchars($ordem['codigo'] . ' - ' . $ordem['titulo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Origem</label>
                            <input class="form-control" name="origem" placeholder="Compra, OS, ajuste, inventário">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observação</label>
                            <textarea class="form-control" name="observacao" rows="3" placeholder="Motivo da movimentação"></textarea>
                        </div>
                    </div>
                    <button class="btn btn-primary w-100 mt-3" type="submit"><i class="bi bi-check2-circle"></i> Registrar Movimentação</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-clock-history me-2 text-info"></i>Histórico</h6>
                <span class="badge badge-info"><?= count($movimentacoes) ?> registros</span>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Qtd.</th>
                            <th>Saldo</th>
                            <th>Reserva</th>
                            <th>OS</th>
                            <th>Usuário</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimentacoes as $mov): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($mov['created_at'])) ?></td>
                            <td><span class="badge <?= $tipoClasses[$mov['tipo']] ?? 'badge-secondary' ?>"><?= $tipoMovLabels[$mov['tipo']] ?? $mov['tipo'] ?></span></td>
                            <td><?= number_format((float)$mov['quantidade'], 3, ',', '.') ?></td>
                            <td><?= number_format((float)$mov['saldo_anterior'], 3, ',', '.') ?> → <?= number_format((float)$mov['saldo_posterior'], 3, ',', '.') ?></td>
                            <td><?= number_format((float)$mov['reservado_anterior'], 3, ',', '.') ?> → <?= number_format((float)$mov['reservado_posterior'], 3, ',', '.') ?></td>
                            <td>
                                <?php if (!empty($mov['ordem_servico_id'])): ?>
                                    <a href="<?= APP_URL ?>/producao/<?= $mov['ordem_servico_id'] ?>" class="badge badge-primary text-decoration-none"><?= htmlspecialchars($mov['os_codigo'] ?? ('OS #' . $mov['ordem_servico_id'])) ?></a>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Sem OS</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($mov['usuario_nome'] ?? '-') ?></td>
                        </tr>
                        <?php if (!empty($mov['observacao'])): ?>
                        <tr>
                            <td colspan="7" class="pt-0"><span class="badge badge-secondary">Obs.</span> <?= htmlspecialchars($mov['observacao']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if (empty($movimentacoes)): ?>
                        <tr><td colspan="7"><span class="badge badge-secondary">Sem movimentações</span></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
