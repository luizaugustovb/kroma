<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
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
$etapaStatusClasses = [
    'pendente' => 'badge-secondary',
    'em_producao' => 'badge-primary',
    'pausada' => 'badge-warning',
    'concluida' => 'badge-success',
    'cancelada' => 'badge-danger',
];
$totalEtapas = max(1, count($etapas));
$etapasConcluidas = count(array_filter($etapas, fn($e) => $e['status'] === 'concluida'));
$progresso = round(($etapasConcluidas / $totalEtapas) * 100);
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

<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-gear me-2 text-primary-kroma"></i>Resumo da OS</h6>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge <?= $statusClasses[$ordem['status']] ?? 'badge-secondary' ?>"><?= $statusLabels[$ordem['status']] ?? $ordem['status'] ?></span>
                    <span class="badge <?= $prioridadeClasses[$ordem['prioridade']] ?? 'badge-secondary' ?>"><?= $prioridadeLabels[$ordem['prioridade']] ?? $ordem['prioridade'] ?></span>
                    <span class="badge <?= $prazoClass ?>"><?= $prazoLabel ?></span>
                </div>
            </div>
            <div class="p-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <span class="form-label">Cliente</span>
                        <div class="fw-bold"><?= htmlspecialchars($ordem['cliente_nome'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Responsável</span>
                        <div class="fw-bold"><?= htmlspecialchars($ordem['responsavel_nome'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Orçamento</span>
                        <div>
                            <?php if (!empty($ordem['orcamento_id'])): ?>
                                <a href="<?= APP_URL ?>/orcamentos/<?= $ordem['orcamento_id'] ?>" class="badge badge-info text-decoration-none"><?= htmlspecialchars($ordem['orcamento_codigo'] ?? ('#' . $ordem['orcamento_id'])) ?></a>
                            <?php else: ?>
                                <span class="badge badge-secondary">Manual</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Entrada</span>
                        <div><?= !empty($ordem['data_entrada']) ? date('d/m/Y', strtotime($ordem['data_entrada'])) : '-' ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Início</span>
                        <div><?= !empty($ordem['data_inicio']) ? date('d/m/Y H:i', strtotime($ordem['data_inicio'])) : '-' ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Finalização</span>
                        <div><?= !empty($ordem['data_finalizacao']) ? date('d/m/Y H:i', strtotime($ordem['data_finalizacao'])) : '-' ?></div>
                    </div>
                    <div class="col-12">
                        <span class="form-label">Descrição</span>
                        <div><?= nl2br(htmlspecialchars($ordem['descricao'] ?: '-')) ?></div>
                    </div>
                    <div class="col-12">
                        <span class="form-label">Observações</span>
                        <div><?= nl2br(htmlspecialchars($ordem['observacoes'] ?: '-')) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-graph-up me-2 text-success-kroma"></i>Progresso</h6>
                <span class="badge badge-info"><?= $progresso ?>%</span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <div class="d-flex justify-content-between"><span>Itens</span><strong><?= count($itens) ?></strong></div>
                <div class="d-flex justify-content-between"><span>Etapas</span><strong><?= count($etapas) ?></strong></div>
                <div class="d-flex justify-content-between"><span>Concluídas</span><span class="badge badge-success"><?= $etapasConcluidas ?></span></div>
                <div class="progress" style="height:10px">
                    <div class="progress-bar" style="width: <?= $progresso ?>%"></div>
                </div>
                <hr>
                <form action="<?= APP_URL ?>/producao/<?= $ordem['id'] ?>/status" method="POST" data-loading>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <label class="form-label">Alterar status da OS</label>
                    <div class="d-flex gap-2">
                        <select class="form-select" name="status">
                            <?php foreach ($statusLabels as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $ordem['status'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i></button>
                    </div>
                </form>
                <a class="btn btn-secondary" href="<?= APP_URL ?>/producao/<?= $ordem['id'] ?>/editar"><i class="bi bi-pencil"></i> Editar OS</a>
                <a class="btn btn-secondary" href="<?= APP_URL ?>/producao"><i class="bi bi-arrow-left"></i> Voltar</a>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-diagram-3 me-2 text-primary-kroma"></i>Etapas de Produção</h6>
        <span class="badge badge-primary"><?= count($etapas) ?> etapas</span>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Ordem</th>
                    <th>Etapa</th>
                    <th>Setor</th>
                    <th>Status</th>
                    <th>Prazo</th>
                    <th>Apontamento</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etapas as $etapa): ?>
                <?php
                    $etapaPrazoClass = 'badge-secondary';
                    $etapaPrazoLabel = !empty($etapa['prazo']) ? date('d/m/Y H:i', strtotime($etapa['prazo'])) : 'Sem prazo';
                    if (!in_array($etapa['status'], ['concluida','cancelada'], true) && !empty($etapa['prazo'])) {
                        if (strtotime($etapa['prazo']) < time()) {
                            $etapaPrazoClass = 'badge-danger';
                            $etapaPrazoLabel = 'Atrasada';
                        } elseif (date('Y-m-d', strtotime($etapa['prazo'])) === date('Y-m-d')) {
                            $etapaPrazoClass = 'badge-warning';
                            $etapaPrazoLabel = 'Hoje';
                        }
                    }
                ?>
                <tr>
                    <td><span class="badge badge-secondary"><?= (int)$etapa['ordem'] ?></span></td>
                    <td>
                        <strong><?= htmlspecialchars($etapa['nome']) ?></strong>
                        <?php if (!empty($etapa['checklist'])): ?>
                            <div class="small text-muted"><?= nl2br(htmlspecialchars($etapa['checklist'])) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($etapa['setor'] ?? '-') ?></td>
                    <td><span class="badge <?= $etapaStatusClasses[$etapa['status']] ?? 'badge-secondary' ?>"><?= $etapaStatusLabels[$etapa['status']] ?? $etapa['status'] ?></span></td>
                    <td><span class="badge <?= $etapaPrazoClass ?>"><?= $etapaPrazoLabel ?></span></td>
                    <td style="min-width:280px">
                        <form action="<?= APP_URL ?>/producao/etapas/<?= $etapa['id'] ?>/status" method="POST" data-loading>
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <div class="d-flex gap-2 mb-2">
                                <select class="form-select form-select-sm" name="status">
                                    <?php foreach ($etapaStatusLabels as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $etapa['status'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-check2"></i></button>
                            </div>
                            <input class="form-control form-control-sm" name="observacao" value="<?= htmlspecialchars($etapa['observacao'] ?? '') ?>" placeholder="Observação rápida">
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($etapas)): ?>
                <tr><td colspan="6"><span class="badge badge-secondary">Nenhuma etapa cadastrada</span></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-list-check me-2 text-info"></i>Itens da OS</h6>
        <span class="badge badge-info"><?= count($itens) ?> itens</span>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Produto/Serviço</th>
                    <th>Qtd.</th>
                    <th>Área</th>
                    <th>Material</th>
                    <th>Acabamento</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($item['produto_nome']) ?></strong>
                        <div class="small text-muted"><?= htmlspecialchars($item['descricao'] ?? '') ?></div>
                        <?php if (!empty($item['arquivo_ref'])): ?>
                            <span class="badge badge-secondary"><?= htmlspecialchars($item['arquivo_ref']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= number_format((float)$item['quantidade'], 3, ',', '.') ?> <?= htmlspecialchars($item['unidade']) ?></td>
                    <td><?= number_format((float)$item['area_m2'], 3, ',', '.') ?> m²</td>
                    <td><?= htmlspecialchars($item['material'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($item['acabamento'] ?: '-') ?></td>
                    <td><span class="badge badge-info"><?= htmlspecialchars(str_replace('_', ' ', $item['status'])) ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($itens)): ?>
                <tr><td colspan="6"><span class="badge badge-secondary">Nenhum item cadastrado</span></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
