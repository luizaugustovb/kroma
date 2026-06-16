<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$isEdicao = !empty($compra['id']);
$action = $isEdicao ? APP_URL . '/compras/' . $compra['id'] . '/editar' : APP_URL . '/compras/novo';

function compraMoney($value): string { return number_format((float)($value ?? 0), 2, ',', '.'); }
function compraDecimal($value): string {
    $value = (float)($value ?? 0);
    return rtrim(rtrim(number_format($value, 3, ',', '.'), '0'), ',');
}
function materialOptions(array $materiais, $selected = null): string {
    $html = '<option value="">-- Item manual --</option>';
    foreach ($materiais as $material) {
        $sel = (string)$selected === (string)$material['id'] ? ' selected' : '';
        $label = trim(($material['codigo'] ? $material['codigo'] . ' - ' : '') . $material['nome']);
        $html .= '<option value="' . htmlspecialchars((string)$material['id']) . '"' . $sel .
            ' data-nome="' . htmlspecialchars($material['nome']) . '"' .
            ' data-unidade="' . htmlspecialchars($material['unidade'] ?? 'un') . '"' .
            ' data-custo="' . htmlspecialchars((string)($material['custo_atual'] ?? 0)) . '">' .
            htmlspecialchars($label) . '</option>';
    }
    return $html;
}
?>

<form action="<?= $action ?>" method="POST" data-loading id="formCompra">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-cart me-2 text-primary-kroma"></i>Dados da Compra</h6>
                    <span class="badge badge-info"><?= $isEdicao ? 'Edição' : 'Nova compra' ?></span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label">Título *</label>
                            <input class="form-control" name="titulo" required value="<?= htmlspecialchars($compra['titulo'] ?? '') ?>" placeholder="Ex: Reposição de lona 440g">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Fornecedor</label>
                            <select class="form-select" name="fornecedor_id">
                                <option value="">-- Sem fornecedor --</option>
                                <?php foreach ($contexto['fornecedores'] as $fornecedor): ?>
                                <option value="<?= $fornecedor['id'] ?>" <?= (string)($compra['fornecedor_id'] ?? '') === (string)$fornecedor['id'] ? 'selected' : '' ?>><?= htmlspecialchars($fornecedor['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <?php foreach ($contexto['statusLabels'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= ($compra['status'] ?? 'rascunho') === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Origem</label>
                            <select class="form-select" name="origem">
                                <?php foreach ($contexto['origemLabels'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= ($compra['origem'] ?? 'manual') === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Solicitante</label>
                            <select class="form-select" name="solicitante_id">
                                <?php foreach ($contexto['usuarios'] as $usuario): ?>
                                <option value="<?= $usuario['id'] ?>" <?= (string)($compra['solicitante_id'] ?? Auth::id()) === (string)$usuario['id'] ? 'selected' : '' ?>><?= htmlspecialchars($usuario['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Solicitação</label>
                            <input class="form-control" type="date" name="data_solicitacao" value="<?= htmlspecialchars($compra['data_solicitacao'] ?? date('Y-m-d')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Previsão de Entrega</label>
                            <input class="form-control" type="date" name="previsao_entrega" value="<?= htmlspecialchars($compra['previsao_entrega'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <label class="d-flex align-items-center gap-2 mb-2">
                                <input class="form-check-input mt-0" type="checkbox" name="gerar_conta_pagar" value="1" <?= !empty($compra['gerar_conta_pagar']) ? 'checked' : '' ?>>
                                <span class="badge badge-warning">Gerar conta a pagar no recebimento</span>
                            </label>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" name="observacoes" rows="3" placeholder="Condições, frete, prazo, negociação"><?= htmlspecialchars($compra['observacoes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-list-check me-2 text-info"></i>Itens</h6>
                    <button type="button" class="btn btn-secondary btn-sm" id="addCompraItem"><i class="bi bi-plus"></i> Adicionar Item</button>
                </div>
                <div class="p-3">
                    <div id="compraItens" class="d-flex flex-column gap-3">
                        <?php foreach ($itens as $index => $item): ?>
                        <div class="border-kroma rounded-kroma p-3" data-compra-item>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge badge-primary">Item <span data-compra-item-number><?= $index + 1 ?></span></span>
                                <button type="button" class="btn btn-secondary btn-sm" data-remove-compra-item><i class="bi bi-trash"></i> Remover</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <label class="form-label">Material cadastrado</label>
                                    <select class="form-select" name="item_material_id[]" data-material-select>
                                        <?= materialOptions($contexto['materiais'], $item['material_id'] ?? null) ?>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Descrição *</label>
                                    <input class="form-control" name="item_descricao[]" required value="<?= htmlspecialchars($item['descricao'] ?? '') ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Unidade</label>
                                    <input class="form-control" name="item_unidade[]" value="<?= htmlspecialchars($item['unidade'] ?? 'un') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Quantidade</label>
                                    <input class="form-control calc-compra" name="item_quantidade[]" value="<?= compraDecimal($item['quantidade'] ?? 1) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Custo unitário</label>
                                    <input class="form-control money calc-compra" name="item_custo_unitario[]" value="<?= compraMoney($item['custo_unitario'] ?? 0) ?>">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <span class="badge badge-success w-100 justify-content-center" data-item-total>R$ <?= compraMoney($item['total'] ?? 0) ?></span>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <?php if (!empty($item['recebido'])): ?>
                                        <span class="badge badge-success">Recebido</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Pendente</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-calculator me-2 text-success-kroma"></i>Total</h6>
                </div>
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Total da compra</span>
                        <strong class="h4 mb-0 text-primary-kroma" id="totalCompra">R$ 0,00</strong>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-check2-circle me-2 text-success-kroma"></i>Ações</h6>
                </div>
                <div class="p-3 d-flex flex-column gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> <?= $isEdicao ? 'Atualizar Compra' : 'Criar Compra' ?></button>
                    <a class="btn btn-secondary" href="<?= APP_URL ?>/compras"><i class="bi bi-arrow-left"></i> Voltar</a>
                    <?php if ($isEdicao): ?>
                    <a class="btn btn-secondary" href="<?= APP_URL ?>/compras/<?= $compra['id'] ?>"><i class="bi bi-eye"></i> Ver Compra</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="compraItemTemplate">
    <div class="border-kroma rounded-kroma p-3" data-compra-item>
        <div class="d-flex justify-content-between align-items-start mb-3">
            <span class="badge badge-primary">Item <span data-compra-item-number>1</span></span>
            <button type="button" class="btn btn-secondary btn-sm" data-remove-compra-item><i class="bi bi-trash"></i> Remover</button>
        </div>
        <div class="row g-2">
            <div class="col-md-5"><label class="form-label">Material cadastrado</label><select class="form-select" name="item_material_id[]" data-material-select><?= materialOptions($contexto['materiais']) ?></select></div>
            <div class="col-md-5"><label class="form-label">Descrição *</label><input class="form-control" name="item_descricao[]" required></div>
            <div class="col-md-2"><label class="form-label">Unidade</label><input class="form-control" name="item_unidade[]" value="un"></div>
            <div class="col-md-3"><label class="form-label">Quantidade</label><input class="form-control calc-compra" name="item_quantidade[]" value="1"></div>
            <div class="col-md-3"><label class="form-label">Custo unitário</label><input class="form-control money calc-compra" name="item_custo_unitario[]" value="0,00"></div>
            <div class="col-md-3 d-flex align-items-end"><span class="badge badge-success w-100 justify-content-center" data-item-total>R$ 0,00</span></div>
            <div class="col-md-3 d-flex align-items-end"><span class="badge badge-secondary">Pendente</span></div>
        </div>
    </div>
</template>

<script>
(function() {
    const parseBR = value => {
        value = String(value || '0').replace(/[^\d,.-]/g, '');
        if (value.includes(',')) value = value.replace(/\./g, '').replace(',', '.');
        return parseFloat(value) || 0;
    };
    const money = value => value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    const calc = () => {
        let total = 0;
        document.querySelectorAll('[data-compra-item]').forEach((item, index) => {
            const number = item.querySelector('[data-compra-item-number]');
            if (number) number.textContent = index + 1;
            const qtd = parseBR(item.querySelector('[name="item_quantidade[]"]')?.value);
            const custo = parseBR(item.querySelector('[name="item_custo_unitario[]"]')?.value);
            const subtotal = qtd * custo;
            total += subtotal;
            const badge = item.querySelector('[data-item-total]');
            if (badge) badge.textContent = money(subtotal);
        });
        document.getElementById('totalCompra').textContent = money(total);
    };
    document.addEventListener('input', event => {
        if (event.target.matches('.calc-compra')) calc();
    });
    document.addEventListener('change', event => {
        if (!event.target.matches('[data-material-select]')) return;
        const option = event.target.selectedOptions[0];
        const item = event.target.closest('[data-compra-item]');
        if (!option || !option.value || !item) return;
        item.querySelector('[name="item_descricao[]"]').value = option.dataset.nome || '';
        item.querySelector('[name="item_unidade[]"]').value = option.dataset.unidade || 'un';
        item.querySelector('[name="item_custo_unitario[]"]').value = (parseFloat(option.dataset.custo || 0)).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        calc();
    });
    document.getElementById('addCompraItem')?.addEventListener('click', () => {
        document.getElementById('compraItens').appendChild(document.getElementById('compraItemTemplate').content.cloneNode(true));
        calc();
    });
    document.addEventListener('click', event => {
        const btn = event.target.closest('[data-remove-compra-item]');
        if (!btn) return;
        const items = document.querySelectorAll('[data-compra-item]');
        if (items.length <= 1) return;
        btn.closest('[data-compra-item]').remove();
        calc();
    });
    calc();
})();
</script>
