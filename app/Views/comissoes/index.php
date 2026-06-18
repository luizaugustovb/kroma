<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$statusClasses = [
    'prevista' => 'badge-info',
    'liberada' => 'badge-success',
    'paga' => 'badge-primary',
    'bloqueada' => 'badge-warning',
    'cancelada' => 'badge-danger',
];

function comissaoMoeda(float $valor): string {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function comissaoData(?string $data): string {
    return $data ? date('d/m/Y', strtotime($data)) : '-';
}
?>

<div class="row g-3 mb-4">
    <?php foreach (['prevista', 'liberada', 'paga', 'bloqueada'] as $status): ?>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon <?= $status === 'bloqueada' ? 'warning' : ($status === 'paga' ? 'primary' : ($status === 'liberada' ? 'success' : 'info')) ?>">
                <i class="bi bi-percent"></i>
            </div>
            <div class="kpi-value" style="font-size:22px"><?= comissaoMoeda((float)$resumo[$status]['valor']) ?></div>
            <div class="kpi-label"><?= $statusLabels[$status] ?> · <?= (int)$resumo[$status]['total'] ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-funnel me-2 text-primary-kroma"></i>Filtros e sincronização</h6>
        <span class="badge badge-info">Margem mínima <?= number_format((float)$margemMinima, 2, ',', '.') ?>%</span>
    </div>
    <div class="p-3">
        <form method="GET" action="<?= APP_URL ?>/comissoes" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">Todos</option>
                    <?php foreach ($statusLabels as $value => $label): ?>
                    <option value="<?= $value ?>" <?= ($filtros['status'] ?? '') === $value ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Vendedor</label>
                <select class="form-select" name="vendedor_id">
                    <option value="">Todos</option>
                    <?php foreach ($vendedores as $vendedor): ?>
                    <option value="<?= $vendedor['id'] ?>" <?= (string)($filtros['vendedor_id'] ?? '') === (string)$vendedor['id'] ? 'selected' : '' ?>><?= htmlspecialchars($vendedor['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2 flex-wrap">
                <button class="btn btn-secondary" type="submit"><i class="bi bi-search"></i> Filtrar</button>
                <a class="btn btn-outline-secondary" href="<?= APP_URL ?>/comissoes"><i class="bi bi-x-circle"></i> Limpar</a>
            </div>
        </form>

        <?php if (Auth::pode('comissoes.criar')): ?>
        <form action="<?= APP_URL ?>/comissoes/sincronizar" method="POST" class="mt-3" data-loading>
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <button class="btn btn-primary" type="submit"><i class="bi bi-arrow-repeat"></i> Sincronizar orçamentos aprovados</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="table-wrapper">
    <table class="table datatable">
        <thead>
            <tr>
                <th>Orçamento</th>
                <th>Vendedor</th>
                <th>Cliente</th>
                <th>Base</th>
                <th>Comissão</th>
                <th>Margem</th>
                <th>Recebimento</th>
                <th>Status</th>
                <th width="210">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($comissoes as $comissao): ?>
            <?php
                $margemReal = (float)$comissao['orcamento_total'] > 0 ? (((float)$comissao['lucro_previsto'] / (float)$comissao['orcamento_total']) * 100) : 0;
                $recebidoPercent = (float)$comissao['orcamento_total'] > 0 ? min(100, ((float)$comissao['financeiro_pago'] / (float)$comissao['orcamento_total']) * 100) : 0;
                $margemClass = $margemReal >= (float)$margemMinima ? 'badge-success' : 'badge-warning';
                $recebidoClass = $recebidoPercent >= 100 ? 'badge-success' : ($recebidoPercent > 0 ? 'badge-warning' : 'badge-secondary');
            ?>
            <tr>
                <td>
                    <a href="<?= APP_URL ?>/orcamentos/<?= $comissao['orcamento_id'] ?>"><strong><?= htmlspecialchars($comissao['orcamento_codigo']) ?></strong></a>
                    <div class="small text-muted"><?= htmlspecialchars($comissao['orcamento_titulo']) ?></div>
                    <span class="badge badge-secondary"><?= comissaoData($comissao['aprovado_at'] ?: $comissao['created_at']) ?></span>
                </td>
                <td><?= htmlspecialchars($comissao['vendedor_nome'] ?? '-') ?></td>
                <td><?= htmlspecialchars($comissao['cliente_nome'] ?? '-') ?></td>
                <td><strong><?= comissaoMoeda((float)$comissao['base_calculo']) ?></strong></td>
                <td>
                    <span class="badge badge-info"><?= number_format((float)$comissao['percentual'], 2, ',', '.') ?>%</span>
                    <div class="fw-bold"><?= comissaoMoeda((float)$comissao['valor']) ?></div>
                </td>
                <td><span class="badge <?= $margemClass ?>"><?= number_format($margemReal, 2, ',', '.') ?>%</span></td>
                <td>
                    <span class="badge <?= $recebidoClass ?>"><?= number_format($recebidoPercent, 1, ',', '.') ?>%</span>
                    <div class="small text-muted"><?= comissaoMoeda((float)$comissao['financeiro_pago']) ?> recebido</div>
                </td>
                <td>
                    <span class="badge <?= $statusClasses[$comissao['status']] ?? 'badge-secondary' ?>"><?= $statusLabels[$comissao['status']] ?? $comissao['status'] ?></span>
                    <?php if (!empty($comissao['data_pagamento'])): ?>
                    <div><span class="badge badge-primary"><?= comissaoData($comissao['data_pagamento']) ?></span></div>
                    <?php elseif (!empty($comissao['data_liberacao'])): ?>
                    <div><span class="badge badge-success"><?= comissaoData($comissao['data_liberacao']) ?></span></div>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="d-flex gap-1 flex-wrap">
                        <?php if (Auth::pode('comissoes.editar') && in_array($comissao['status'], ['prevista', 'bloqueada'], true)): ?>
                        <form action="<?= APP_URL ?>/comissoes/<?= $comissao['id'] ?>/liberar" method="POST" data-loading>
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <button class="btn btn-success btn-sm" type="submit"><i class="bi bi-check2"></i> Liberar</button>
                        </form>
                        <?php endif; ?>

                        <?php if (Auth::pode('comissoes.editar') && $comissao['status'] === 'liberada'): ?>
                        <form action="<?= APP_URL ?>/comissoes/<?= $comissao['id'] ?>/pagar" method="POST" data-loading>
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-cash"></i> Pagar</button>
                        </form>
                        <?php endif; ?>

                        <?php if (Auth::pode('comissoes.editar') && in_array($comissao['status'], ['prevista', 'liberada'], true)): ?>
                        <form action="<?= APP_URL ?>/comissoes/<?= $comissao['id'] ?>/bloquear" method="POST" data-loading>
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <button class="btn btn-warning btn-sm" type="submit"><i class="bi bi-lock"></i> Bloquear</button>
                        </form>
                        <?php endif; ?>

                        <?php if (Auth::pode('comissoes.editar') && !in_array($comissao['status'], ['paga', 'cancelada'], true)): ?>
                        <form action="<?= APP_URL ?>/comissoes/<?= $comissao['id'] ?>/cancelar" method="POST" data-loading>
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <button class="btn btn-danger btn-sm" type="submit"><i class="bi bi-x-circle"></i> Cancelar</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($comissoes)): ?>
            <tr><td colspan="9"><span class="badge badge-secondary">Sem comissões para os filtros selecionados</span></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
