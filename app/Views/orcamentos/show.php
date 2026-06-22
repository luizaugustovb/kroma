<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$statusClasses = [
    'rascunho' => 'badge-secondary',
    'em_calculo' => 'badge-info',
    'enviado' => 'badge-primary',
    'aprovado' => 'badge-success',
    'recusado' => 'badge-warning',
    'cancelado' => 'badge-danger',
    'expirado' => 'badge-warning',
];
?>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-file-earmark-text me-2 text-primary-kroma"></i>Resumo Comercial</h6>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge <?= $statusClasses[$orcamento['status']] ?? 'badge-secondary' ?>"><?= $statusLabels[$orcamento['status']] ?? $orcamento['status'] ?></span>
                    <?php if ($ordem): ?>
                        <a href="<?= APP_URL ?>/producao/<?= $ordem['id'] ?>" class="badge badge-primary text-decoration-none">OS <?= htmlspecialchars($ordem['codigo']) ?></a>
                    <?php endif; ?>
                    <?php if ($contaReceber): ?>
                        <a href="<?= APP_URL ?>/financeiro/receber/<?= $contaReceber['id'] ?>" class="badge badge-success text-decoration-none">Cobrança <?= htmlspecialchars($contaReceber['codigo']) ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="form-label">Cliente</span>
                        <div><?= htmlspecialchars($orcamento['cliente_nome'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6">
                        <span class="form-label">Lead</span>
                        <div><?= htmlspecialchars($orcamento['lead_nome'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Tipo</span>
                        <div><span class="badge badge-info"><?= $tipoLabels[$orcamento['tipo']] ?? $orcamento['tipo'] ?></span></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Vendedor</span>
                        <div><?= htmlspecialchars($orcamento['vendedor_nome'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Validade</span>
                        <div><?= !empty($orcamento['validade']) ? date('d/m/Y', strtotime($orcamento['validade'])) : '-' ?></div>
                    </div>
                    <div class="col-12">
                        <span class="form-label">Descrição</span>
                        <div><?= nl2br(htmlspecialchars($orcamento['descricao'] ?: '-')) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-list-check me-2 text-primary-kroma"></i>Itens e Reservas</h6>
                <span class="badge badge-info"><?= count($itens) ?> itens</span>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Produto/Serviço</th>
                            <th>Qtd.</th>
                            <th>Área</th>
                            <th>Custo</th>
                            <th>Unitário</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itens as $item): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($item['produto_nome']) ?></strong>
                                <?php if (!empty($item['produto_codigo'])): ?>
                                    <span class="badge badge-secondary ms-1"><?= htmlspecialchars($item['produto_codigo']) ?></span>
                                <?php endif; ?>
                                <div class="small text-muted"><?= htmlspecialchars($item['descricao'] ?? '') ?></div>
                                <?php foreach (($materiaisPorItem[$item['id']] ?? []) as $material): ?>
                                    <div class="mt-1">
                                        <span class="badge badge-warning">
                                            <?= htmlspecialchars($material['material_nome']) ?> · <?= number_format((float)$material['quantidade'], 3, ',', '.') ?> <?= htmlspecialchars($material['unidade']) ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                            <td><?= number_format((float)$item['quantidade'], 3, ',', '.') ?> <?= htmlspecialchars($item['unidade']) ?></td>
                            <td><?= number_format((float)$item['area_m2'], 3, ',', '.') ?> m²</td>
                            <td>R$ <?= number_format((float)$item['custo_total'], 2, ',', '.') ?></td>
                            <td>R$ <?= number_format((float)$item['preco_unitario'], 2, ',', '.') ?></td>
                            <td><strong>R$ <?= number_format((float)$item['total'], 2, ',', '.') ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($itens)): ?>
                        <tr><td colspan="6"><span class="badge badge-secondary">Sem itens cadastrados</span></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-calculator me-2 text-success-kroma"></i>Totais</h6>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <div class="d-flex justify-content-between"><span>Subtotal Custo</span><strong>R$ <?= number_format((float)$orcamento['subtotal_custo'], 2, ',', '.') ?></strong></div>
                <div class="d-flex justify-content-between"><span>Subtotal Venda</span><strong>R$ <?= number_format((float)$orcamento['subtotal_venda'], 2, ',', '.') ?></strong></div>
                <div class="d-flex justify-content-between"><span>Desconto</span><strong>R$ <?= number_format((float)$orcamento['desconto_valor'], 2, ',', '.') ?></strong></div>
                <div class="d-flex justify-content-between"><span>Preço Mínimo</span><span class="badge badge-warning">R$ <?= number_format((float)$orcamento['preco_minimo'], 2, ',', '.') ?></span></div>
                <div class="d-flex justify-content-between"><span>Lucro Previsto</span><span class="badge <?= (float)$orcamento['lucro_previsto'] >= 0 ? 'badge-success' : 'badge-danger' ?>">R$ <?= number_format((float)$orcamento['lucro_previsto'], 2, ',', '.') ?></span></div>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Total</span>
                    <strong class="h4 text-primary-kroma mb-0">R$ <?= number_format((float)$orcamento['total'], 2, ',', '.') ?></strong>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-percent me-2 text-primary-kroma"></i>Comissão</h6>
            </div>
            <div class="p-3">
                <?php if ($comissao): ?>
                    <div class="d-flex justify-content-between mb-2"><span>Status</span><span class="badge badge-info"><?= ucfirst($comissao['status']) ?></span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Percentual</span><strong><?= number_format((float)$comissao['percentual'], 2, ',', '.') ?>%</strong></div>
                    <div class="d-flex justify-content-between"><span>Valor</span><strong>R$ <?= number_format((float)$comissao['valor'], 2, ',', '.') ?></strong></div>
                <?php else: ?>
                    <span class="badge badge-secondary">Gerada na aprovação</span>
                    <p class="text-secondary mt-2 mb-0">Percentual previsto: <?= number_format((float)$orcamento['comissao_percent'], 2, ',', '.') ?>%</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-lightning-charge me-2 text-warning-kroma"></i>Ações</h6>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <a href="<?= APP_URL ?>/orcamentos/<?= $orcamento['id'] ?>/editar" class="btn btn-secondary"><i class="bi bi-pencil"></i> Editar</a>
                <?php if (!in_array($orcamento['status'], ['aprovado', 'cancelado'], true)): ?>
                <form action="<?= APP_URL ?>/orcamentos/<?= $orcamento['id'] ?>/enviar" method="POST" data-loading>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-send"></i> Marcar como Enviado</button>
                </form>
                <form action="<?= APP_URL ?>/orcamentos/<?= $orcamento['id'] ?>/aprovar" method="POST" data-loading>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <?php if (!empty($designers)): ?>
                    <label class="form-label">Direcionar para designer</label>
                    <select class="form-select mb-2" name="designer_id" required>
                        <?php foreach ($designers as $designer): ?>
                        <option value="<?= $designer['id'] ?>"><?= htmlspecialchars($designer['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php else: ?>
                    <span class="badge badge-warning align-self-start mb-2">Nenhum designer ativo cadastrado</span>
                    <?php endif; ?>
                    <button class="btn btn-success w-100" type="submit"><i class="bi bi-check2-circle"></i> Aprovar e Gerar Fluxo</button>
                </form>
                <form action="<?= APP_URL ?>/orcamentos/<?= $orcamento['id'] ?>/cancelar" method="POST" data-loading>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <button class="btn btn-danger w-100" type="submit"><i class="bi bi-x-circle"></i> Cancelar</button>
                </form>
                <?php else: ?>
                    <?php if ($orcamento['status'] === 'aprovado'): ?>
                        <span class="badge badge-success align-self-start">Fluxo comercial aprovado</span>
                    <?php endif; ?>
                    <?php if (!$ordem && $orcamento['status'] === 'aprovado' && Auth::pode('producao')): ?>
                    <a href="<?= APP_URL ?>/producao/novo?orcamento_id=<?= $orcamento['id'] ?>" class="btn btn-primary"><i class="bi bi-gear"></i> Gerar OS Manual</a>
                    <?php endif; ?>
                    <?php if (!$contaReceber && $orcamento['status'] === 'aprovado' && Auth::pode('financeiro')): ?>
                    <a href="<?= APP_URL ?>/financeiro/receber/novo?orcamento_id=<?= $orcamento['id'] ?>" class="btn btn-success"><i class="bi bi-cash-stack"></i> Gerar Cobrança Manual</a>
                    <?php endif; ?>
                <?php endif; ?>
                <a href="<?= APP_URL ?>/orcamentos" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
            </div>
        </div>
    </div>
</div>
