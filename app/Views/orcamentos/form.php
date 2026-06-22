<?php

use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$isEdicao = !empty($orcamento['id']);
$action = $isEdicao ? APP_URL . '/orcamentos/' . $orcamento['id'] . '/editar' : APP_URL . '/orcamentos/novo';

function orcMoney($value): string
{
    return number_format((float)($value ?? 0), 2, ',', '.');
}

function orcDecimal($value): string
{
    $value = (float)($value ?? 0);
    return rtrim(rtrim(number_format($value, 3, ',', '.'), '0'), ',');
}

function orcProdutoOptions(array $produtos, $selected = null): string
{
    $html = '<option value="">-- Selecionar produto --</option>';
    foreach ($produtos as $produto) {
        $sel = (string)$selected === (string)$produto['id'] ? ' selected' : '';
        $label = trim(($produto['codigo'] ? $produto['codigo'] . ' - ' : '') . $produto['nome']);
        $estoque = (float)($produto['estoque_atual'] ?? 0) - (float)($produto['estoque_reservado'] ?? 0);
        $attrs = [
            'data-nome' => $produto['nome'],
            'data-unidade' => $produto['unidade'] ?: 'un',
            'data-largura' => $produto['largura_padrao'] ?? 0,
            'data-altura' => $produto['altura_padrao'] ?? 0,
            'data-custo-material' => $produto['custo_material'] ?? 0,
            'data-custo-tinta' => $produto['custo_tinta'] ?? 0,
            'data-custo-acabamento' => $produto['custo_acabamento'] ?? 0,
            'data-custo-mao-obra' => $produto['custo_mao_obra'] ?? 0,
            'data-custo-maquina' => $produto['custo_maquina'] ?? 0,
            'data-custo-terceiros' => $produto['custo_terceiros'] ?? 0,
            'data-desperdicio' => $produto['desperdicio_percent'] ?? 5,
            'data-margem' => $produto['margem_percent'] ?? 35,
            'data-impostos' => $produto['impostos_percent'] ?? 8,
            'data-comissao' => $produto['comissao_percent'] ?? 5,
            'data-estoque' => $estoque,
        ];
        $html .= '<option value="' . htmlspecialchars((string)$produto['id']) . '"' . $sel;
        foreach ($attrs as $key => $value) {
            $html .= ' ' . $key . '="' . htmlspecialchars((string)$value) . '"';
        }
        $html .= '>' . htmlspecialchars($label) . '</option>';
    }
    return $html;
}

function orcMaterialOptions(array $materiais, $selected = null): string
{
    $html = '<option value="">-- Sem reserva --</option>';
    foreach ($materiais as $material) {
        $sel = (string)$selected === (string)$material['id'] ? ' selected' : '';
        $label = trim(($material['codigo'] ? $material['codigo'] . ' - ' : '') . $material['nome']);
        $html .= '<option value="' . htmlspecialchars((string)$material['id']) . '"' . $sel
            . ' data-nome="' . htmlspecialchars($material['nome']) . '"'
            . ' data-unidade="' . htmlspecialchars($material['unidade'] ?: 'un') . '"'
            . ' data-custo="' . htmlspecialchars((string)($material['custo_atual'] ?? 0)) . '"'
            . ' data-disponivel="' . htmlspecialchars((string)($material['estoque_disponivel'] ?? 0)) . '">'
            . htmlspecialchars($label) . '</option>';
    }
    return $html;
}

function renderOrcamentoItem(array $item, int $index, array $contexto): void
{
    $tipoItem = $item['tipo_item'] ?? 'personalizado';
    $materialTipo = htmlspecialchars($item['material_tipo'] ?? '');
    $materiaisTipo = ['Lona', 'Papel', 'Adesivo', 'Tecido', 'Vinil', 'Acetinado', 'Fosco', 'Canvas', 'Backlight', 'Outro'];
    $custos = [
        'item_custo_material' => ['Material', 'custo_material'],
        'item_custo_tinta' => ['Tinta', 'custo_tinta'],
        'item_custo_acabamento' => ['Acabamento', 'custo_acabamento'],
        'item_custo_mao_obra' => ['Mão de Obra', 'custo_mao_obra'],
        'item_custo_maquina' => ['Hora Máquina', 'custo_maquina'],
        'item_custo_terceiros' => ['Terceiros/Frete', 'custo_terceiros'],
    ];
    $percents = [
        'item_desperdicio_percent' => ['Desperdício %', 'desperdicio_percent', 5],
        'item_margem_percent' => ['Margem %', 'margem_percent', 35],
        'item_impostos_percent' => ['Impostos %', 'impostos_percent', 8],
        'item_comissao_percent' => ['Comissão %', 'comissao_percent', 5],
        'item_desconto_percent' => ['Desc. Item %', 'desconto_percent', 0],
    ];
?>
    <div class="orcamento-item border-kroma rounded-kroma p-3" data-item data-tipo-item="<?= $tipoItem ?>">
        <!-- Cabeçalho: número + toggle + remover -->
        <div class="d-flex justify-content-between align-items-start mb-3 gap-2 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <span class="badge badge-primary">Item <span data-item-number><?= $index + 1 ?></span></span>
                <input type="hidden" name="item_tipo_item[]" class="tipo-item-input" value="<?= $tipoItem ?>">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-secondary tipo-btn <?= $tipoItem === 'personalizado' ? 'active' : '' ?>" data-tipo="personalizado">
                        <i class="bi bi-pencil-square"></i> Personalizado
                    </button>
                    <button type="button" class="btn btn-secondary tipo-btn <?= $tipoItem === 'pronto' ? 'active' : '' ?>" data-tipo="pronto">
                        <i class="bi bi-box-seam"></i> Produto Pronto
                    </button>
                </div>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" data-remove-item><i class="bi bi-trash"></i></button>
        </div>

        <!-- Seção: PRODUTO PRONTO -->
        <div class="item-secao-pronto" <?= $tipoItem !== 'pronto' ? 'style="display:none"' : '' ?>>
            <div class="row g-2 mb-2">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Produto do Catálogo *</label>
                    <select class="form-select item-input" name="item_produto_id[]" data-produto-select>
                        <?= orcProdutoOptions($contexto['produtos'], $item['produto_id'] ?? null) ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quantidade</label>
                    <input class="form-control item-input" name="item_quantidade[]" value="<?= orcDecimal($item['quantidade'] ?? 1) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unidade</label>
                    <input class="form-control item-input" name="item_unidade[]" value="<?= htmlspecialchars($item['unidade'] ?? 'un') ?>">
                </div>
                <div class="col-12">
                    <span class="badge badge-info me-2" data-estoque-badge>Estoque: --</span>
                    <span class="badge badge-secondary" data-item-dims>Dimensão: --</span>
                </div>
            </div>
        </div>

        <!-- Seção: PERSONALIZADO -->
        <div class="item-secao-personalizado" <?= $tipoItem === 'pronto' ? 'style="display:none"' : '' ?>>
            <div class="row g-2 mb-2">
                <div class="col-md-5">
                    <!-- campo produto_id oculto (sem produto) para personalizado -->
                    <input type="hidden" name="item_produto_id[]" value="">
                    <label class="form-label fw-bold">Produto/Serviço *</label>
                    <input class="form-control item-input" name="item_produto_nome[]" required value="<?= htmlspecialchars($item['produto_nome'] ?? '') ?>" placeholder="Banner, ACM, Adesivo, DTF...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Qtd.</label>
                    <input class="form-control item-input" name="item_quantidade[]" value="<?= orcDecimal($item['quantidade'] ?? 1) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unidade</label>
                    <input class="form-control item-input" name="item_unidade[]" value="<?= htmlspecialchars($item['unidade'] ?? 'un') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Material</label>
                    <select class="form-select item-input" name="item_material_tipo[]">
                        <option value="">-- Tipo de material --</option>
                        <?php foreach ($materiaisTipo as $mt): ?>
                            <option value="<?= $mt ?>" <?= $materialTipo === $mt ? 'selected' : '' ?>><?= $mt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Dimensão m²</label>
                    <div class="d-flex gap-1 align-items-center">
                        <input class="form-control item-input" name="item_largura[]" value="<?= orcDecimal($item['largura'] ?? 0) ?>" placeholder="L (m)">
                        <span class="small">×</span>
                        <input class="form-control item-input" name="item_altura[]" value="<?= orcDecimal($item['altura'] ?? 0) ?>" placeholder="A (m)">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <span class="badge badge-info w-100 justify-content-center" data-area-badge>0,00 m²</span>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Material estoque para reserva</label>
                    <select class="form-select item-input" name="item_material_id[]" data-material-select>
                        <?= orcMaterialOptions($contexto['materiais'], $item['material_id'] ?? null) ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Qtd./item</label>
                    <input class="form-control item-input" name="item_material_quantidade[]" value="<?= orcDecimal($item['material_quantidade'] ?? 0) ?>" data-material-qtd>
                </div>
            </div>
        </div>

        <!-- Seção COMUM: nome (pronto usa hidden), descrição, custos, percentuais -->
        <div class="row g-2">
            <?php if ($tipoItem === 'pronto'): ?>
                <!-- Pronto: nome e dimensão vindos do produto via JS -->
                <input type="hidden" name="item_produto_nome[]" class="item-nome-hidden" value="<?= htmlspecialchars($item['produto_nome'] ?? '') ?>">
                <input type="hidden" name="item_material_tipo[]" value="">
                <input type="hidden" name="item_largura[]" value="<?= orcDecimal($item['largura'] ?? 0) ?>">
                <input type="hidden" name="item_altura[]" value="<?= orcDecimal($item['altura'] ?? 0) ?>">
                <input type="hidden" name="item_material_id[]" value="">
                <input type="hidden" name="item_material_quantidade[]" value="0">
            <?php endif; ?>
            <div class="col-12">
                <label class="form-label">Descrição</label>
                <input class="form-control item-input" name="item_descricao[]" value="<?= htmlspecialchars($item['descricao'] ?? '') ?>" placeholder="Acabamento, observações técnicas">
            </div>
            <?php foreach ($custos as $name => [$label, $key]): ?>
                <div class="col-md-2">
                    <label class="form-label"><?= $label ?></label>
                    <input class="form-control item-input money" name="<?= $name ?>[]" value="<?= orcMoney($item[$key] ?? 0) ?>">
                </div>
            <?php endforeach; ?>
            <?php foreach ($percents as $name => [$label, $key, $default]): ?>
                <div class="col-md-2">
                    <label class="form-label"><?= $label ?></label>
                    <input class="form-control item-input percent" name="<?= $name ?>[]" value="<?= htmlspecialchars((string)($item[$key] ?? $default)) ?>">
                </div>
            <?php endforeach; ?>
            <div class="col-md-2 d-flex align-items-end">
                <span class="badge badge-success w-100 justify-content-center" data-item-total>R$ 0,00</span>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <span class="badge badge-warning w-100 justify-content-center" data-material-preview>Sem reserva</span>
            </div>
        </div>
    </div>
<?php
}
?>

<form action="<?= $action ?>" method="POST" data-loading id="formOrcamento">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-file-earmark-text me-2 text-primary-kroma"></i>Dados do Orçamento</h6>
                    <span class="badge badge-info"><?= $isEdicao ? 'Edição' : 'Novo orçamento' ?></span>
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
                                    <option value="<?= $value ?>" <?= ($orcamento['tipo'] ?? 'completo') === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cliente</label>
                            <select class="form-select" name="cliente_id">
                                <option value="">-- Sem cliente vinculado --</option>
                                <?php foreach ($contexto['clientes'] as $cliente): ?>
                                    <option value="<?= $cliente['id'] ?>" <?= (string)($orcamento['cliente_id'] ?? '') === (string)$cliente['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cliente['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lead</label>
                            <select class="form-select" name="lead_id">
                                <option value="">-- Sem lead vinculado --</option>
                                <?php foreach ($contexto['leads'] as $lead): ?>
                                    <option value="<?= $lead['id'] ?>" <?= (string)($orcamento['lead_id'] ?? '') === (string)$lead['id'] ? 'selected' : '' ?>><?= htmlspecialchars($lead['nome'] . (!empty($lead['empresa']) ? ' - ' . $lead['empresa'] : '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Vendedor</label>
                            <select class="form-select" name="vendedor_id">
                                <?php foreach ($contexto['vendedores'] as $vendedor): ?>
                                    <option value="<?= $vendedor['id'] ?>" <?= (string)($orcamento['vendedor_id'] ?? Auth::id()) === (string)$vendedor['id'] ? 'selected' : '' ?>><?= htmlspecialchars($vendedor['nome']) ?></option>
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
                            <?php renderOrcamentoItem($item, $index, $contexto); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
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
                    <!-- Desconto global -->
                    <div>
                        <label class="form-label fw-bold">Desconto Global</label>
                        <div class="d-flex gap-2 mb-2">
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check" name="desconto_tipo" id="descontoPercent" value="percent" <?= empty($orcamento['desconto_valor']) || ($orcamento['desconto_percent'] ?? 0) > 0 ? 'checked' : '' ?>>
                                <label class="btn btn-secondary" for="descontoPercent">%</label>
                                <input type="radio" class="btn-check" name="desconto_tipo" id="descontoValor" value="valor" <?= !empty($orcamento['desconto_valor']) && ($orcamento['desconto_percent'] ?? 0) == 0 ? 'checked' : '' ?>>
                                <label class="btn btn-secondary" for="descontoValor">R$</label>
                            </div>
                            <div id="wrapDescPercent" class="flex-grow-1">
                                <input class="form-control calc-global" name="desconto_percent" value="<?= htmlspecialchars($orcamento['desconto_percent'] ?? 0) ?>" placeholder="0">
                            </div>
                            <div id="wrapDescValor" class="flex-grow-1" style="display:none">
                                <input class="form-control calc-global money" name="desconto_valor" value="<?= orcMoney($orcamento['desconto_valor'] ?? 0) ?>" placeholder="0,00">
                            </div>
                        </div>
                    </div>
                    <!-- Hidden inputs to carry zero when not used -->
                    <input type="hidden" name="_desconto_percent_zero" value="0">
                    <input type="hidden" name="_desconto_valor_zero" value="0,00">
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Total</span>
                        <strong class="h4 mb-0 text-primary-kroma" id="previewTotal">R$ 0,00</strong>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
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

            <?php if ($isEdicao): ?>
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title"><i class="bi bi-paperclip me-2 text-primary-kroma"></i>Arquivo do Projeto</h6>
                        <span class="badge badge-info">Mockup / Exemplo</span>
                    </div>
                    <div class="p-3">
                        <p class="small text-muted mb-3">Envie o arquivo do projeto (imagem, PDF, etc.) para o cliente aprovar ou reprovar o orçamento.</p>
                        <?php if (!empty($orcamento['arquivo_projeto'])): ?>
                            <div class="d-flex align-items-center gap-2 mb-3 p-2 border-kroma rounded-kroma">
                                <i class="bi bi-file-earmark-check text-success-kroma fs-5"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small"><?= htmlspecialchars(basename($orcamento['arquivo_projeto'])) ?></div>
                                </div>
                                <a href="<?= APP_URL ?>/public/uploads/orcamentos/<?= htmlspecialchars(basename($orcamento['arquivo_projeto'])) ?>" target="_blank" class="btn btn-secondary btn-sm"><i class="bi bi-eye"></i></a>
                                <form action="<?= APP_URL ?>/orcamentos/<?= $orcamento['id'] ?>/remover-arquivo" method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <button class="btn btn-danger btn-sm" type="submit" onclick="return confirm('Remover arquivo?')"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        <?php endif; ?>
                        <form action="<?= APP_URL ?>/orcamentos/<?= $orcamento['id'] ?>/upload-arquivo" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <div class="d-flex gap-2">
                                <input type="file" class="form-control" name="arquivo_projeto" accept=".pdf,.jpg,.jpeg,.png,.webp,.gif,.zip" required>
                                <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-upload"></i> Enviar</button>
                            </div>
                            <div class="small text-muted mt-1">Formatos: PDF, JPG, PNG, ZIP. Máx. 10MB.</div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title"><i class="bi bi-paperclip me-2 text-primary-kroma"></i>Arquivo do Projeto</h6>
                    </div>
                    <div class="p-3">
                        <span class="badge badge-secondary">Salve o orçamento primeiro para anexar arquivos.</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> <?= $isEdicao ? 'Atualizar Orçamento' : 'Criar Orçamento' ?></button>
        <a class="btn btn-secondary" href="<?= APP_URL ?>/orcamentos"><i class="bi bi-x"></i> Cancelar</a>
    </div>
    <!-- Campos ocultos para compatibilidade com o backend -->
    <input type="hidden" name="desperdicio_percent" value="0">
    <input type="hidden" name="impostos_percent" value="0">
    <input type="hidden" name="comissao_percent" value="0">
    <input type="hidden" name="margem_percent" value="0">
</form>

<template id="itemTemplate">
    <?php renderOrcamentoItem([], 0, $contexto); ?>
</template>

<script>
    (function() {
        const brNumber = value => {
            if (value === null || value === undefined || value === '') return 0;
            value = String(value).replace(/[^0-9,.-]/g, '');
            if (value.includes(',')) value = value.replace(/\./g, '').replace(',', '.');
            return parseFloat(value) || 0;
        };
        const brMoney = value => new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value || 0);
        const brDecimal = value => (value || 0).toLocaleString('pt-BR', {
            maximumFractionDigits: 3
        });
        const setValue = (item, name, value, money = false) => {
            const input = item.querySelector(`[name="${name}"]`);
            if (!input) return;
            input.value = money ? Number(value || 0).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) : value;
        };

        function calcularOrcamento() {
            let subtotalCusto = 0;
            let subtotalVenda = 0;
            document.querySelectorAll('[data-item]').forEach((item, index) => {
                item.querySelector('[data-item-number]').textContent = index + 1;
                const q = Math.max(0.001, brNumber(item.querySelector('[name="item_quantidade[]"]').value));
                const materialSelect = item.querySelector('[data-material-select]');
                const materialOption = materialSelect?.selectedOptions[0];
                const materialQtd = brNumber(item.querySelector('[name="item_material_quantidade[]"]').value);
                if (materialOption && materialOption.value && materialQtd > 0) {
                    setValue(item, 'item_custo_material[]', materialQtd * brNumber(materialOption.dataset.custo), true);
                    item.querySelector('[data-material-preview]').textContent = brDecimal(materialQtd * q) + ' ' + (materialOption.dataset.unidade || 'un') + ' reservados';
                    item.querySelector('[data-material-preview]').className = 'badge badge-warning w-100 justify-content-center';
                } else {
                    item.querySelector('[data-material-preview]').textContent = 'Sem reserva';
                    item.querySelector('[data-material-preview]').className = 'badge badge-secondary w-100 justify-content-center';
                }
                const custos = ['item_custo_material[]', 'item_custo_tinta[]', 'item_custo_acabamento[]', 'item_custo_mao_obra[]', 'item_custo_maquina[]', 'item_custo_terceiros[]']
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

            const descontoPercent = brNumber(document.querySelector('[name="desconto_percent"]')?.value);
            const descontoValor = brNumber(document.querySelector('[name="desconto_valor"]')?.value);
            const descontoTotal = descontoValor + (subtotalVenda * descontoPercent / 100);
            const total = Math.max(0, subtotalVenda - descontoTotal);
            // Mínimo calculado com base nos custos e percentuais dos itens
            const minimo = subtotalCusto;
            const lucro = total - subtotalCusto;

            document.getElementById('previewCusto').textContent = brMoney(subtotalCusto);
            document.getElementById('previewSubtotal').textContent = brMoney(subtotalVenda);
            document.getElementById('previewDesconto').textContent = brMoney(descontoTotal);
            document.getElementById('previewMinimo').textContent = brMoney(minimo);
            document.getElementById('previewLucro').textContent = brMoney(lucro);
            document.getElementById('previewLucro').className = 'badge ' + (lucro >= 0 ? 'badge-success' : 'badge-danger');
            document.getElementById('previewTotal').textContent = brMoney(total);
        }

        document.addEventListener('change', event => {
            if (event.target.matches('[data-produto-select]')) {
                const option = event.target.selectedOptions[0];
                const item = event.target.closest('[data-item]');
                if (!option || !option.value || !item) return;
                setValue(item, 'item_produto_nome[]', option.dataset.nome || '');
                setValue(item, 'item_unidade[]', option.dataset.unidade || 'un');
                setValue(item, 'item_largura[]', option.dataset.largura || '0');
                setValue(item, 'item_altura[]', option.dataset.altura || '0');
                setValue(item, 'item_custo_material[]', option.dataset.custoMaterial || 0, true);
                setValue(item, 'item_custo_tinta[]', option.dataset.custoTinta || 0, true);
                setValue(item, 'item_custo_acabamento[]', option.dataset.custoAcabamento || 0, true);
                setValue(item, 'item_custo_mao_obra[]', option.dataset.custoMaoObra || 0, true);
                setValue(item, 'item_custo_maquina[]', option.dataset.custoMaquina || 0, true);
                setValue(item, 'item_custo_terceiros[]', option.dataset.custoTerceiros || 0, true);
                setValue(item, 'item_desperdicio_percent[]', option.dataset.desperdicio || 5);
                setValue(item, 'item_margem_percent[]', option.dataset.margem || 35);
                setValue(item, 'item_impostos_percent[]', option.dataset.impostos || 8);
                setValue(item, 'item_comissao_percent[]', option.dataset.comissao || 5);
                calcularOrcamento();
            }
            if (event.target.matches('[data-material-select]')) {
                calcularOrcamento();
            }
        });
        document.addEventListener('input', event => {
            if (event.target.closest('#formOrcamento')) calcularOrcamento();
        });
        document.getElementById('addItem').addEventListener('click', () => {
            document.getElementById('itensWrapper').appendChild(document.getElementById('itemTemplate').content.cloneNode(true));
            calcularOrcamento();
        });
        document.addEventListener('click', event => {
            const btn = event.target.closest('[data-remove-item]');
            if (!btn) return;
            const items = document.querySelectorAll('[data-item]');
            if (items.length <= 1) {
                const badge = items[0]?.querySelector('[data-item-total]');
                if (badge) {
                    badge.textContent = 'Item obrigatório';
                    badge.className = 'badge badge-warning w-100 justify-content-center';
                }
                return;
            }
            btn.closest('[data-item]').remove();
            calcularOrcamento();
        });
        document.addEventListener('DOMContentLoaded', calcularOrcamento);

        // Toggle desconto %/R$
        document.querySelectorAll('[name="desconto_tipo"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const isPercent = this.value === 'percent';
                const wrapP = document.getElementById('wrapDescPercent');
                const wrapV = document.getElementById('wrapDescValor');
                if (wrapP) wrapP.style.display = isPercent ? '' : 'none';
                if (wrapV) wrapV.style.display = isPercent ? 'none' : '';
                // Zero the inactive field
                if (isPercent) {
                    const v = document.querySelector('[name="desconto_valor"]');
                    if (v) v.value = '0,00';
                } else {
                    const p = document.querySelector('[name="desconto_percent"]');
                    if (p) p.value = '0';
                }
                calcularOrcamento();
            });
        });
        // Init toggle state on load
        document.addEventListener('DOMContentLoaded', () => {
            const checked = document.querySelector('[name="desconto_tipo"]:checked');
            if (checked) checked.dispatchEvent(new Event('change'));
            // Init produto pronto badges
            document.querySelectorAll('[data-produto-select]').forEach(sel => {
                if (sel.value) sel.dispatchEvent(new Event('change'));
            });
            calcularOrcamento();
        });

        // ---- Toggle Produto Pronto / Personalizado ----
        document.addEventListener('click', event => {
            const btn = event.target.closest('.tipo-btn');
            if (!btn) return;
            const item = btn.closest('[data-item]');
            if (!item) return;
            const tipo = btn.dataset.tipo;

            // Update hidden input
            const tipoInput = item.querySelector('.tipo-item-input');
            if (tipoInput) tipoInput.value = tipo;

            // Toggle buttons active state
            item.querySelectorAll('.tipo-btn').forEach(b => b.classList.toggle('active', b.dataset.tipo === tipo));

            // Show/hide sections
            item.querySelector('.item-secao-pronto').style.display = tipo === 'pronto' ? '' : 'none';
            item.querySelector('.item-secao-personalizado').style.display = tipo === 'personalizado' ? '' : 'none';
        });

        // ---- Produto Pronto: update stock badge + dims + nome hidden ----
        document.addEventListener('change', event => {
            const sel = event.target.closest('[data-produto-select]');
            if (!sel) return;
            const item = sel.closest('[data-item]');
            if (!item) return;
            const opt = sel.selectedOptions[0];
            const estoque = opt ? parseFloat(opt.dataset.estoque || 0) : null;
            const badge = item.querySelector('[data-estoque-badge]');
            const dimBadge = item.querySelector('[data-item-dims]');
            if (badge) {
                if (opt && opt.value) {
                    const est = estoque >= 0 ? estoque : 0;
                    badge.textContent = 'Estoque: ' + est.toLocaleString('pt-BR', {
                        maximumFractionDigits: 3
                    }) + ' ' + (opt.dataset.unidade || 'un');
                    badge.className = 'badge me-2 ' + (est > 0 ? 'badge-success' : 'badge-danger');
                } else {
                    badge.textContent = 'Estoque: --';
                    badge.className = 'badge badge-info me-2';
                }
            }
            if (dimBadge && opt && opt.value) {
                const l = parseFloat(opt.dataset.largura || 0),
                    a = parseFloat(opt.dataset.altura || 0);
                dimBadge.textContent = l > 0 && a > 0 ?
                    'Dimensão padrão: ' + l.toLocaleString('pt-BR') + 'm × ' + a.toLocaleString('pt-BR') + 'm' :
                    'Dimensão: livre';
            }
            // Sync nome hidden (for pronto mode)
            const nomeHidden = item.querySelector('.item-nome-hidden');
            if (nomeHidden && opt) nomeHidden.value = opt.dataset.nome || '';
        });

        // ---- Personalizado: auto-calc m² badge ----
        document.addEventListener('input', event => {
            const inp = event.target;
            if (!inp.name || (!inp.name.startsWith('item_largura') && !inp.name.startsWith('item_altura'))) return;
            const item = inp.closest('[data-item]');
            if (!item) return;
            const l = parseFloat(item.querySelector('[name="item_largura[]"]')?.value || 0);
            const a = parseFloat(item.querySelector('[name="item_altura[]"]')?.value || 0);
            const areaBadge = item.querySelector('[data-area-badge]');
            if (areaBadge) {
                const qtd = parseFloat(item.querySelector('[name="item_quantidade[]"]')?.value || 1) || 1;
                const m2 = l * a;
                areaBadge.textContent = (m2 * qtd).toLocaleString('pt-BR', {
                    maximumFractionDigits: 3
                }) + ' m²';
            }
        });
    })();
</script>