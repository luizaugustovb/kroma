<?php
use App\Services\Auth;
$csrfToken = Auth::csrfToken();
$isEdicao = !empty($orcamento['id']);
$action = $isEdicao ? APP_URL . '/orcamentos/' . $orcamento['id'] . '/editar' : APP_URL . '/orcamentos/novo';

function moneyInput($value): string {
    return number_format((float)($value ?? 0), 2, ',', '.');
}
?>

<form action="<?= $action ?>" method="POST" data-loading id="formOrcamento">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-file-earmark-text me-2 text-primary-kroma"></i>Dados do Orçamento</h6>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Título *</label>
                            <input class="form-control" name="titulo" required value="<?= htmlspecialchars($orcamento['titulo'] ?? '') ?>" placeholder="Ex: Fachada ACM loja centro">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" name="tipo">
                                <?php foreach ($tipoLabels as $value => $label): ?>
                                <option value="<?= $value ?>" <?= ($orcamento['tipo'] ?? 'rapido') === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cliente</label>
                            <select class="form-select" name="cliente_id">
                                <option value="">-- Sem cliente vinculado --</option>
                                <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= $cliente['id'] ?>" <?= ($orcamento['cliente_id'] ?? '') == $cliente['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cliente['nome']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lead</label>
                            <select class="form-select" name="lead_id">
                                <option value="">-- Sem lead vinculado --</option>
                                <?php foreach ($leads as $lead): ?>
                                <option value="<?= $lead['id'] ?>" <?= ($orcamento['lead_id'] ?? '') == $lead['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($lead['nome'] . (!empty($lead['empresa']) ? ' - ' . $lead['empresa'] : '')) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Vendedor</label>
                            <select class="form-select" name="vendedor_id">
                                <?php foreach ($vendedores as $vendedor): ?>
                                <option value="<?= $vendedor['id'] ?>" <?= ($orcamento['vendedor_id'] ?? Auth::id()) == $vendedor['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($vendedor['nome']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <?php foreach ($statusLabels as $value => $label): ?>
                                <option value="<?= $value ?>" <?= ($orcamento['status'] ?? 'rascunho') === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Validade</label>
                            <input class="form-control" type="date" name="validade" value="<?= htmlspecialchars($orcamento['validade'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Prazo de Entrega</label>
                            <input class="form-control" name="prazo_entrega" value="<?= htmlspecialchars($orcamento['prazo_entrega'] ?? '') ?>" placeholder="Ex: 7 dias úteis">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <textarea class="form-control" name="descricao" rows="2" placeholder="Resumo técnico do orçamento"><?= htmlspecialchars($orcamento['descricao'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-list-check me-2 text-primary-kroma"></i>Itens do Orçamento</h6>
                    <button type="button" class="btn btn-secondary btn-sm" id="addItem"><i class="bi bi-plus"></i> Adicionar Item</button>
                </div>
                <div class="p-3">
                    <div id="itensWrapper" class="d-flex flex-column gap-3">
                        <?php foreach ($itens as $index => $item): ?>
                        <div class="orcamento-item border-kroma rounded-kroma p-3" data-item>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge badge-primary">Item <span data-item-number><?= $index + 1 ?></span></span>
                                <button type="button" class="btn btn-secondary btn-sm" data-remove-item><i class="bi bi-trash"></i> Remover</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <label class="form-label">Produto/Serviço *</label>
                                    <input class="form-control item-input" name="item_produto_nome[]" required value="<?= htmlspecialchars($item['produto_nome'] ?? '') ?>" placeholder="Banner, ACM, DTF...">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Qtd.</label>
                                    <input class="form-control item-input" name="item_quantidade[]" value="<?= htmlspecialchars($item['quantidade'] ?? 1) ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Unidade</label>
                                    <input class="form-control item-input" name="item_unidade[]" value="<?= htmlspecialchars($item['unidade'] ?? 'un') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Dimensão m²</label>
                                    <div class="d-flex gap-2">
                                        <input class="form-control item-input" name="item_largura[]" value="<?= htmlspecialchars($item['largura'] ?? 0) ?>" placeholder="L">
                                        <input class="form-control item-input" name="item_altura[]" value="<?= htmlspecialchars($item['altura'] ?? 0) ?>" placeholder="A">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descrição do Item</label>
                                    <input class="form-control item-input" name="item_descricao[]" value="<?= htmlspecialchars($item['descricao'] ?? '') ?>" placeholder="Material, acabamento, observações técnicas">
                                </div>
                                <?php
                                $custos = [
                                    'item_custo_material' => ['Material', 'custo_material'],
                                    'item_custo_tinta' => ['Tinta', 'custo_tinta'],
                                    'item_custo_acabamento' => ['Acabamento', 'custo_acabamento'],
                                    'item_custo_mao_obra' => ['Mão de Obra', 'custo_mao_obra'],
                                    'item_custo_maquina' => ['Hora Máquina', 'custo_maquina'],
                                    'item_custo_terceiros' => ['Terceiros/Frete', 'custo_terceiros'],
                                ];
                                foreach ($custos as $name => [$label, $key]):
                                ?>
                                <div class="col-md-2">
                                    <label class="form-label"><?= $label ?></label>
                                    <input class="form-control item-input money" name="<?= $name ?>[]" value="<?= moneyInput($item[$key] ?? 0) ?>">
                                </div>
                                <?php endforeach; ?>
                                <?php
                                $percents = [
                                    'item_desperdicio_percent' => ['Desperdício %', 'desperdicio_percent'],
                                    'item_margem_percent' => ['Margem %', 'margem_percent'],
                                    'item_impostos_percent' => ['Impostos %', 'impostos_percent'],
                                    'item_comissao_percent' => ['Comissão %', 'comissao_percent'],
                                    'item_desconto_percent' => ['Desc. Item %', 'desconto_percent'],
                                ];
                                foreach ($percents as $name => [$label, $key]):
                                ?>
                                <div class="col-md-2">
                                    <label class="form-label"><?= $label ?></label>
                                    <input class="form-control item-input percent" name="<?= $name ?>[]" value="<?= htmlspecialchars($item[$key] ?? 0) ?>">
                                </div>
                                <?php endforeach; ?>
                                <div class="col-md-2 d-flex align-items-end">
                                    <span class="badge badge-success w-100 justify-content-center" data-item-total>R$ 0,00</span>
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
                    <h6 class="card-title"><i class="bi bi-calculator me-2 text-success-kroma"></i>Parâmetros Globais</h6>
                </div>
                <div class="p-3">
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label">Desperdício %</label><input class="form-control calc-global" name="desperdicio_percent" value="<?= htmlspecialchars($orcamento['desperdicio_percent'] ?? 5) ?>"></div>
                        <div class="col-6"><label class="form-label">Impostos %</label><input class="form-control calc-global" name="impostos_percent" value="<?= htmlspecialchars($orcamento['impostos_percent'] ?? 8) ?>"></div>
                        <div class="col-6"><label class="form-label">Comissão %</label><input class="form-control calc-global" name="comissao_percent" value="<?= htmlspecialchars($orcamento['comissao_percent'] ?? 5) ?>"></div>
                        <div class="col-6"><label class="form-label">Margem %</label><input class="form-control calc-global" name="margem_percent" value="<?= htmlspecialchars($orcamento['margem_percent'] ?? 35) ?>"></div>
                        <div class="col-6"><label class="form-label">Desconto %</label><input class="form-control calc-global" name="desconto_percent" value="<?= htmlspecialchars($orcamento['desconto_percent'] ?? 0) ?>"></div>
                        <div class="col-6"><label class="form-label">Desconto R$</label><input class="form-control calc-global money" name="desconto_valor" value="<?= moneyInput($orcamento['desconto_valor'] ?? 0) ?>"></div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-graph-up me-2 text-primary-kroma"></i>Resumo Calculado</h6>
                </div>
                <div class="p-3 d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between"><span>Custo Total</span><strong id="previewCusto">R$ 0,00</strong></div>
                    <div class="d-flex justify-content-between"><span>Subtotal Venda</span><strong id="previewSubtotal">R$ 0,00</strong></div>
                    <div class="d-flex justify-content-between"><span>Descontos</span><strong id="previewDesconto">R$ 0,00</strong></div>
                    <div class="d-flex justify-content-between"><span>Preço Mínimo</span><span class="badge badge-warning" id="previewMinimo">R$ 0,00</span></div>
                    <div class="d-flex justify-content-between"><span>Lucro Previsto</span><span class="badge badge-success" id="previewLucro">R$ 0,00</span></div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Total</span>
                        <strong class="h4 mb-0 text-primary-kroma" id="previewTotal">R$ 0,00</strong>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-chat-left-text me-2 text-info"></i>Condições</h6>
                </div>
                <div class="p-3">
                    <label class="form-label">Condição de Pagamento</label>
                    <input class="form-control mb-3" name="condicao_pagamento" value="<?= htmlspecialchars($orcamento['condicao_pagamento'] ?? '') ?>" placeholder="Ex: 50% entrada + 50% entrega">
                    <label class="form-label">Observações</label>
                    <textarea class="form-control" name="observacoes" rows="4" placeholder="Condições comerciais, validade e observações"><?= htmlspecialchars($orcamento['observacoes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> <?= $isEdicao ? 'Atualizar Orçamento' : 'Criar Orçamento' ?></button>
        <a class="btn btn-secondary" href="<?= APP_URL ?>/orcamentos"><i class="bi bi-x"></i> Cancelar</a>
    </div>
</form>

<template id="itemTemplate">
    <?php $item = []; $index = 0; include APP_PATH . '/Views/orcamentos/item_template_inner.php'; ?>
</template>

<script>
const brNumber = value => {
    if (value === null || value === undefined || value === '') return 0;
    value = String(value).replace(/[^0-9,.-]/g, '');
    if (value.includes(',')) value = value.replace(/\./g, '').replace(',', '.');
    return parseFloat(value) || 0;
};
const brMoney = value => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);

function calcularOrcamento() {
    let subtotalCusto = 0;
    let subtotalVenda = 0;
    document.querySelectorAll('[data-item]').forEach((item, index) => {
        item.querySelector('[data-item-number]').textContent = index + 1;
        const q = Math.max(0.001, brNumber(item.querySelector('[name="item_quantidade[]"]').value));
        const custos = ['item_custo_material[]','item_custo_tinta[]','item_custo_acabamento[]','item_custo_mao_obra[]','item_custo_maquina[]','item_custo_terceiros[]']
            .reduce((sum, name) => sum + brNumber(item.querySelector(`[name="${name}"]`).value), 0);
        const desperdicio = brNumber(item.querySelector('[name="item_desperdicio_percent[]"]').value);
        const margem = brNumber(item.querySelector('[name="item_margem_percent[]"]').value);
        const impostos = brNumber(item.querySelector('[name="item_impostos_percent[]"]').value);
        const comissao = brNumber(item.querySelector('[name="item_comissao_percent[]"]').value);
        const desconto = brNumber(item.querySelector('[name="item_desconto_percent[]"]').value);
        const custoComDesperdicio = custos * (1 + desperdicio / 100);
        const custoTotal = custoComDesperdicio * q;
        let precoUnitario = custoComDesperdicio * (1 + ((margem + impostos + comissao) / 100));
        precoUnitario = precoUnitario * (1 - desconto / 100);
        const totalItem = precoUnitario * q;
        subtotalCusto += custoTotal;
        subtotalVenda += totalItem;
        item.querySelector('[data-item-total]').textContent = brMoney(totalItem);
    });

    const descontoPercent = brNumber(document.querySelector('[name="desconto_percent"]').value);
    const descontoValor = brNumber(document.querySelector('[name="desconto_valor"]').value);
    const impostosGlobal = brNumber(document.querySelector('[name="impostos_percent"]').value);
    const comissaoGlobal = brNumber(document.querySelector('[name="comissao_percent"]').value);
    const descontoTotal = descontoValor + (subtotalVenda * descontoPercent / 100);
    const total = Math.max(0, subtotalVenda - descontoTotal);
    const minimo = subtotalCusto * (1 + ((impostosGlobal + comissaoGlobal) / 100));
    const lucro = total - subtotalCusto;

    document.getElementById('previewCusto').textContent = brMoney(subtotalCusto);
    document.getElementById('previewSubtotal').textContent = brMoney(subtotalVenda);
    document.getElementById('previewDesconto').textContent = brMoney(descontoTotal);
    document.getElementById('previewMinimo').textContent = brMoney(minimo);
    document.getElementById('previewLucro').textContent = brMoney(lucro);
    document.getElementById('previewLucro').className = 'badge ' + (lucro >= 0 ? 'badge-success' : 'badge-danger');
    document.getElementById('previewTotal').textContent = brMoney(total);
}

document.addEventListener('input', event => {
    if (event.target.closest('#formOrcamento')) calcularOrcamento();
});
document.getElementById('addItem').addEventListener('click', () => {
    const wrapper = document.getElementById('itensWrapper');
    const template = document.getElementById('itemTemplate');
    wrapper.insertAdjacentHTML('beforeend', template.innerHTML);
    calcularOrcamento();
});
document.addEventListener('click', event => {
    const btn = event.target.closest('[data-remove-item]');
    if (!btn) return;
    const items = document.querySelectorAll('[data-item]');
    if (items.length <= 1) {
        KROMA.flash.show('O orçamento precisa ter pelo menos um item.', 'warning');
        return;
    }
    btn.closest('[data-item]').remove();
    calcularOrcamento();
});
document.addEventListener('DOMContentLoaded', calcularOrcamento);
</script>
