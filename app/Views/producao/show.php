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
$estoqueTipoClasses = [
    'entrada' => 'badge-success',
    'saida' => 'badge-danger',
    'ajuste' => 'badge-info',
    'reserva' => 'badge-warning',
    'baixa_reserva' => 'badge-primary',
    'cancelamento_reserva' => 'badge-secondary',
];
$estoqueTipoLabels = [
    'entrada' => 'Entrada',
    'saida' => 'Saída',
    'ajuste' => 'Ajuste',
    'reserva' => 'Reserva',
    'baixa_reserva' => 'Baixa',
    'cancelamento_reserva' => 'Cancelamento',
];
$totalEtapas = max(1, count($etapas));
$etapasConcluidas = count(array_filter($etapas, fn($e) => $e['status'] === 'concluida'));
$progresso = round(($etapasConcluidas / $totalEtapas) * 100);
$prazoClass = 'badge-secondary';
$prazoLabel = !empty($ordem['data_prometida']) ? date('d/m/Y', strtotime($ordem['data_prometida'])) : 'Sem prazo';
if (!in_array($ordem['status'], ['finalizada', 'cancelada'], true) && !empty($ordem['data_prometida'])) {
    if ($ordem['data_prometida'] < date('Y-m-d')) {
        $prazoClass = 'badge-danger';
        $prazoLabel = 'Atrasada desde ' . date('d/m/Y', strtotime($ordem['data_prometida']));
    } elseif ($ordem['data_prometida'] === date('Y-m-d')) {
        $prazoClass = 'badge-warning';
        $prazoLabel = 'Vence hoje';
    }
}
$logoPrint = APP_URL . '/public/assets/img/logo.png';
?>

<div class="os-print-document">
    <div class="os-print-header">
        <div>
            <img src="<?= htmlspecialchars($logoPrint) ?>" alt="Kroma" class="os-print-logo">
            <div class="os-print-kicker">Ordem de Serviço</div>
            <h1><?= htmlspecialchars($ordem['codigo']) ?></h1>
        </div>
        <div class="os-print-status">
            <strong><?= htmlspecialchars($statusLabels[$ordem['status']] ?? $ordem['status']) ?></strong>
            <span><?= htmlspecialchars($prioridadeLabels[$ordem['prioridade']] ?? $ordem['prioridade']) ?></span>
        </div>
    </div>

    <div class="os-print-grid">
        <div><span>Cliente</span><strong><?= htmlspecialchars($ordem['cliente_nome'] ?? '-') ?></strong></div>
        <div><span>Responsável</span><strong><?= htmlspecialchars($ordem['responsavel_nome'] ?? '-') ?></strong></div>
        <div><span>Orçamento</span><strong><?= htmlspecialchars($ordem['orcamento_codigo'] ?? '-') ?></strong></div>
        <div><span>Entrada</span><strong><?= !empty($ordem['data_entrada']) ? date('d/m/Y', strtotime($ordem['data_entrada'])) : '-' ?></strong></div>
        <div><span>Prazo</span><strong><?= htmlspecialchars($prazoLabel) ?></strong></div>
        <div><span>Progresso</span><strong><?= $progresso ?>%</strong></div>
    </div>

    <section class="os-print-section">
        <h2>Descrição</h2>
        <p><?= nl2br(htmlspecialchars($ordem['descricao'] ?: '-')) ?></p>
    </section>

    <?php if (!empty($arquivoProjeto)): ?>
    <section class="os-print-section">
        <h2>Arquivo aprovado</h2>
        <div class="os-print-file">
            <?php if (!empty($arquivoProjeto['is_image'])): ?>
            <img src="<?= htmlspecialchars($arquivoProjeto['url']) ?>" alt="Arquivo aprovado">
            <?php endif; ?>
            <div>
                <strong><?= htmlspecialchars($arquivoProjeto['nome']) ?></strong>
                <span><?= htmlspecialchars(strtoupper($arquivoProjeto['ext'])) ?></span>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="os-print-section">
        <h2>Itens</h2>
        <table class="os-print-table">
            <thead>
                <tr>
                    <th>Produto/Serviço</th>
                    <th>Qtd.</th>
                    <th>Área</th>
                    <th>Material</th>
                    <th>Acabamento</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($item['produto_nome']) ?></strong>
                        <small><?= htmlspecialchars($item['descricao'] ?? '') ?></small>
                    </td>
                    <td><?= number_format((float)$item['quantidade'], 3, ',', '.') ?> <?= htmlspecialchars($item['unidade']) ?></td>
                    <td><?= number_format((float)$item['area_m2'], 3, ',', '.') ?> m²</td>
                    <td><?= htmlspecialchars($item['material'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($item['acabamento'] ?: '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="os-print-section">
        <h2>Etapas</h2>
        <table class="os-print-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Etapa</th>
                    <th>Setor</th>
                    <th>Status</th>
                    <th>Prazo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etapas as $etapa): ?>
                <tr>
                    <td><?= (int)$etapa['ordem'] ?></td>
                    <td><?= htmlspecialchars($etapa['nome']) ?></td>
                    <td><?= htmlspecialchars($etapa['setor'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($etapaStatusLabels[$etapa['status']] ?? $etapa['status']) ?></td>
                    <td><?= !empty($etapa['prazo']) ? date('d/m/Y H:i', strtotime($etapa['prazo'])) : '-' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>

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
                <?php if (Auth::pode('financeiro') && $ordem['status'] !== 'cancelada'): ?>
                    <a class="btn btn-success" href="<?= APP_URL ?>/financeiro/receber/novo?os_id=<?= $ordem['id'] ?>"><i class="bi bi-cash-stack"></i> Faturar OS</a>
                <?php endif; ?>
                <button class="btn btn-secondary" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir OS</button>
                <a class="btn btn-secondary" href="<?= APP_URL ?>/producao"><i class="bi bi-arrow-left"></i> Voltar</a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($arquivoProjeto)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-file-earmark-check me-2 text-success-kroma"></i>Arquivo aprovado para produção</h6>
        <span class="badge badge-success"><?= htmlspecialchars(strtoupper($arquivoProjeto['ext'])) ?></span>
    </div>
    <div class="p-3 d-flex gap-3 align-items-start flex-wrap">
        <?php if (!empty($arquivoProjeto['is_image'])): ?>
            <a href="<?= htmlspecialchars($arquivoProjeto['url']) ?>" target="_blank" class="d-block">
                <img src="<?= htmlspecialchars($arquivoProjeto['url']) ?>" alt="Arquivo aprovado" style="max-width:260px;max-height:180px;object-fit:contain;border:1px solid var(--border-color);border-radius:8px;background:#fff;">
            </a>
        <?php endif; ?>
        <div class="d-flex flex-column gap-2">
            <strong><?= htmlspecialchars($arquivoProjeto['nome']) ?></strong>
            <span class="small text-muted">Arquivo enviado no orçamento aprovado e disponível para conferência antes da produção.</span>
            <a class="btn btn-secondary btn-sm align-self-start" href="<?= htmlspecialchars($arquivoProjeto['url']) ?>" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> Abrir arquivo
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-archive me-2 text-warning"></i>Reservas de Estoque</h6>
        <span class="badge badge-warning"><?= count($reservasEstoque ?? []) ?> movimentos</span>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Tipo</th>
                    <th>Quantidade</th>
                    <th>Data</th>
                    <th>Observação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($reservasEstoque ?? []) as $mov): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($mov['material_nome']) ?></strong></td>
                        <td><span class="badge <?= $estoqueTipoClasses[$mov['tipo']] ?? 'badge-secondary' ?>"><?= $estoqueTipoLabels[$mov['tipo']] ?? $mov['tipo'] ?></span></td>
                        <td><?= number_format((float)$mov['quantidade'], 3, ',', '.') ?> <?= htmlspecialchars($mov['unidade']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($mov['created_at'])) ?></td>
                        <td><?= htmlspecialchars($mov['observacao'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($reservasEstoque)): ?>
                    <tr>
                        <td colspan="5"><span class="badge badge-secondary">Nenhuma reserva vinculada</span></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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
                    if (!in_array($etapa['status'], ['concluida', 'cancelada'], true) && !empty($etapa['prazo'])) {
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
                    <tr>
                        <td colspan="6"><span class="badge badge-secondary">Nenhuma etapa cadastrada</span></td>
                    </tr>
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
                    <tr>
                        <td colspan="6"><span class="badge badge-secondary">Nenhum item cadastrado</span></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (Auth::temPerfil(['administrador', 'producao'])): ?>
    <div class="card mt-3">
        <div class="card-header">
            <h6 class="card-title"><i class="bi bi-scissors me-2 text-warning"></i>Otimização de Material Real</h6>
            <span class="badge badge-warning">Interno — não visível ao cliente</span>
        </div>
        <form action="<?= APP_URL ?>/producao/<?= $ordem['id'] ?>/material-real" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Material Orçado</th>
                            <th>Área Orçada (m²)</th>
                            <th>Material Real Usado</th>
                            <th>Área Real (m²)</th>
                            <th>Custo Real (R$)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itens as $item): ?>
                            <tr>
                                <td>
                                    <input type="hidden" name="item_id[]" value="<?= $item['id'] ?>">
                                    <strong><?= htmlspecialchars($item['produto_nome']) ?></strong>
                                    <div class="small text-muted"><?= number_format((float)$item['quantidade'], 3, ',', '.') ?> <?= htmlspecialchars($item['unidade']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($item['material'] ?: '-') ?></td>
                                <td><?= number_format((float)$item['area_m2'], 3, ',', '.') ?> m²</td>
                                <td><input type="text" class="form-control form-control-sm" name="material_real[]" value="<?= htmlspecialchars($item['material_real'] ?? '') ?>" placeholder="Ex: Lona encaixada"></td>
                                <td><input type="number" step="0.001" class="form-control form-control-sm" name="area_real[]" value="<?= $item['area_real'] ?? '' ?>" placeholder="0,000"></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm" name="custo_real[]" value="<?= $item['custo_real'] ?? '' ?>" placeholder="0,00"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-3 d-flex gap-3 align-items-end flex-wrap">
                <?php if (!empty($ordem['custo_real'])): ?>
                    <div class="small">
                        Custo real registrado: <strong>R$ <?= number_format((float)$ordem['custo_real'], 2, ',', '.') ?></strong>
                    </div>
                <?php endif; ?>
                <div class="flex-grow-1">
                    <label class="form-label small">Observação sobre a otimização</label>
                    <input type="text" class="form-control form-control-sm" name="obs_otimizacao" value="<?= htmlspecialchars($ordem['obs_otimizacao'] ?? '') ?>" placeholder="Ex: Lona da OS anterior aproveitada, encaixe 2×2m">
                </div>
                <button type="submit" class="btn btn-warning btn-sm"><i class="bi bi-check2-circle"></i> Salvar Material Real</button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php if (($ordem['status'] === 'finalizada') && !empty($_GET['confirmar_instalacao'])): ?>
    <!-- Modal: Agendar Instalação -->
    <div class="modal fade" id="modalInstalacao" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-calendar-check me-2 text-success-kroma"></i>OS Finalizada!</h5>
                </div>
                <div class="modal-body">
                    <p>A OS <strong><?= htmlspecialchars($ordem['codigo']) ?></strong> foi concluída.</p>
                    <p>Deseja agendar a instalação para este cliente?</p>
                </div>
                <div class="modal-footer">
                    <a href="<?= APP_URL ?>/agenda/novo?os_id=<?= $ordem['id'] ?>&cliente_id=<?= $ordem['cliente_id'] ?? '' ?>" class="btn btn-primary">
                        <i class="bi bi-calendar-plus"></i> Sim, agendar instalação
                    </a>
                    <a href="<?= APP_URL ?>/producao/<?= $ordem['id'] ?>" class="btn btn-secondary">Não, obrigado</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('modalInstalacao'));
            modal.show();
        });
    </script>
<?php endif; ?>
