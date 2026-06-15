<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$isEdicao = !empty($ordem['id']);
$action = $isEdicao ? APP_URL . '/producao/' . $ordem['id'] . '/editar' : APP_URL . '/producao/novo';
$processosSelecionados = array_map('strval', $processosSelecionados ?? []);

function osDecimal($value): string {
    $value = (float)($value ?? 0);
    return rtrim(rtrim(number_format($value, 3, ',', '.'), '0'), ',');
}

function osProdutoOptions(array $produtos, $selected = null): string {
    $html = '<option value="">-- Item manual --</option>';
    foreach ($produtos as $produto) {
        $sel = (string)$selected === (string)$produto['id'] ? ' selected' : '';
        $label = trim(($produto['codigo'] ? $produto['codigo'] . ' - ' : '') . $produto['nome']);
        $html .= '<option value="' . htmlspecialchars((string)$produto['id']) . '"' . $sel . ' data-nome="' . htmlspecialchars($produto['nome']) . '" data-unidade="' . htmlspecialchars($produto['unidade'] ?? 'un') . '" data-largura="' . htmlspecialchars((string)($produto['largura_padrao'] ?? 0)) . '" data-altura="' . htmlspecialchars((string)($produto['altura_padrao'] ?? 0)) . '">' . htmlspecialchars($label) . '</option>';
    }
    return $html;
}
?>

<form action="<?= $action ?>" method="POST" data-loading id="formOs">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-gear me-2 text-primary-kroma"></i>Dados da OS</h6>
                    <span class="badge badge-info"><?= $isEdicao ? 'Edição' : 'Nova OS' ?></span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Título *</label>
                            <input class="form-control" name="titulo" required value="<?= htmlspecialchars($ordem['titulo'] ?? '') ?>" placeholder="Ex: Fachada ACM loja centro">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Orçamento</label>
                            <select class="form-select" name="orcamento_id" <?= !empty($ordem['orcamento_id']) ? '' : '' ?>>
                                <option value="">-- Sem orçamento --</option>
                                <?php foreach ($contexto['orcamentos'] as $orcamento): ?>
                                <option value="<?= $orcamento['id'] ?>" <?= (string)($ordem['orcamento_id'] ?? '') === (string)$orcamento['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($orcamento['codigo'] . ' - ' . $orcamento['titulo']) ?>
                                </option>
                                <?php endforeach; ?>
                                <?php if (!empty($ordem['orcamento_id']) && empty(array_filter($contexto['orcamentos'], fn($o) => (string)$o['id'] === (string)$ordem['orcamento_id']))): ?>
                                <option value="<?= htmlspecialchars((string)$ordem['orcamento_id']) ?>" selected>Orçamento vinculado #<?= htmlspecialchars((string)$ordem['orcamento_id']) ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cliente</label>
                            <select class="form-select" name="cliente_id">
                                <option value="">-- Sem cliente --</option>
                                <?php foreach ($contexto['clientes'] as $cliente): ?>
                                <option value="<?= $cliente['id'] ?>" <?= (string)($ordem['cliente_id'] ?? '') === (string)$cliente['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cliente['nome']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Responsável</label>
                            <select class="form-select" name="responsavel_id">
                                <?php foreach ($contexto['usuarios'] as $usuario): ?>
                                <option value="<?= $usuario['id'] ?>" <?= (string)($ordem['responsavel_id'] ?? Auth::id()) === (string)$usuario['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($usuario['nome']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Prioridade</label>
                            <select class="form-select" name="prioridade">
                                <?php foreach ($contexto['prioridadeLabels'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= ($ordem['prioridade'] ?? 'media') === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <?php foreach ($contexto['statusLabels'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= ($ordem['status'] ?? 'aberta') === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Entrada</label>
                            <input class="form-control" type="date" name="data_entrada" value="<?= htmlspecialchars($ordem['data_entrada'] ?? date('Y-m-d')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Data Prometida</label>
                            <input class="form-control" type="date" name="data_prometida" value="<?= htmlspecialchars($ordem['data_prometida'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <textarea class="form-control" name="descricao" rows="2" placeholder="Resumo técnico do serviço"><?= htmlspecialchars($ordem['descricao'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observações internas</label>
                            <textarea class="form-control" name="observacoes" rows="2" placeholder="Pontos de atenção para produção, instalação ou entrega"><?= htmlspecialchars($ordem['observacoes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-list-check me-2 text-primary-kroma"></i>Itens da OS</h6>
                    <button type="button" class="btn btn-secondary btn-sm" id="addItemOs"><i class="bi bi-plus"></i> Adicionar Item</button>
                </div>
                <div class="p-3">
                    <div id="itensOsWrapper" class="d-flex flex-column gap-3">
                        <?php foreach ($itens as $index => $item): ?>
                        <div class="border-kroma rounded-kroma p-3" data-os-item>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge badge-primary">Item <span data-os-item-number><?= $index + 1 ?></span></span>
                                <button type="button" class="btn btn-secondary btn-sm" data-remove-os-item><i class="bi bi-trash"></i> Remover</button>
                            </div>
                            <input type="hidden" name="item_orcamento_item_id[]" value="<?= htmlspecialchars($item['orcamento_item_id'] ?? '') ?>">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <label class="form-label">Produto cadastrado</label>
                                    <select class="form-select" name="item_produto_id[]" data-produto-select>
                                        <?= osProdutoOptions($contexto['produtos'], $item['produto_id'] ?? null) ?>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Produto/Serviço *</label>
                                    <input class="form-control" name="item_produto_nome[]" required value="<?= htmlspecialchars($item['produto_nome'] ?? '') ?>" placeholder="Banner, adesivo, fachada...">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="item_status[]">
                                        <?php foreach (['pendente' => 'Pendente', 'em_producao' => 'Em produção', 'concluido' => 'Concluído', 'cancelado' => 'Cancelado'] as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= ($item['status'] ?? 'pendente') === $value ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Qtd.</label>
                                    <input class="form-control calc-area" name="item_quantidade[]" value="<?= osDecimal($item['quantidade'] ?? 1) ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Unidade</label>
                                    <input class="form-control" name="item_unidade[]" value="<?= htmlspecialchars($item['unidade'] ?? 'un') ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Largura</label>
                                    <input class="form-control calc-area" name="item_largura[]" value="<?= osDecimal($item['largura'] ?? 0) ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Altura</label>
                                    <input class="form-control calc-area" name="item_altura[]" value="<?= osDecimal($item['altura'] ?? 0) ?>">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <span class="badge badge-info w-100 justify-content-center" data-area-preview><?= osDecimal($item['area_m2'] ?? 0) ?> m²</span>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Material</label>
                                    <input class="form-control" name="item_material[]" value="<?= htmlspecialchars($item['material'] ?? '') ?>" placeholder="Lona 440g, vinil, ACM...">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Acabamento</label>
                                    <input class="form-control" name="item_acabamento[]" value="<?= htmlspecialchars($item['acabamento'] ?? '') ?>" placeholder="Ilhós, laminação, refile...">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Arquivo/Referência</label>
                                    <input class="form-control" name="item_arquivo_ref[]" value="<?= htmlspecialchars($item['arquivo_ref'] ?? '') ?>" placeholder="Drive, pasta ou nome do arquivo">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descrição do item</label>
                                    <input class="form-control" name="item_descricao[]" value="<?= htmlspecialchars($item['descricao'] ?? '') ?>" placeholder="Detalhes técnicos, medidas finais e observações">
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
                    <h6 class="card-title"><i class="bi bi-diagram-3 me-2 text-success-kroma"></i>Etapas Produtivas</h6>
                    <span class="badge badge-secondary">Processos</span>
                </div>
                <div class="p-3">
                    <p class="small text-muted mb-3">Se nenhum processo for marcado, o sistema tenta usar os processos do produto cadastrado. Se não encontrar, cria uma etapa padrão de produção.</p>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($contexto['processos'] as $processo): ?>
                        <label class="border-kroma rounded-kroma p-2 d-flex gap-2 align-items-start">
                            <input class="form-check-input mt-1" type="checkbox" name="processos[]" value="<?= $processo['id'] ?>" <?= in_array((string)$processo['id'], $processosSelecionados, true) ? 'checked' : '' ?>>
                            <span>
                                <strong><?= htmlspecialchars($processo['nome']) ?></strong>
                                <span class="badge badge-info ms-1"><?= htmlspecialchars($processo['setor'] ?: 'Produção') ?></span>
                                <?php if (!empty($processo['maquina'])): ?>
                                    <span class="d-block small text-muted"><?= htmlspecialchars($processo['maquina']) ?></span>
                                <?php endif; ?>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-check2-circle me-2 text-success-kroma"></i>Ações</h6>
                </div>
                <div class="p-3 d-flex flex-column gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> <?= $isEdicao ? 'Atualizar OS' : 'Criar OS' ?></button>
                    <a class="btn btn-secondary" href="<?= APP_URL ?>/producao"><i class="bi bi-arrow-left"></i> Voltar</a>
                    <?php if ($isEdicao): ?>
                    <a class="btn btn-secondary" href="<?= APP_URL ?>/producao/<?= $ordem['id'] ?>"><i class="bi bi-eye"></i> Ver Ficha</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="osItemTemplate">
    <div class="border-kroma rounded-kroma p-3" data-os-item>
        <div class="d-flex justify-content-between align-items-start mb-3">
            <span class="badge badge-primary">Item <span data-os-item-number>1</span></span>
            <button type="button" class="btn btn-secondary btn-sm" data-remove-os-item><i class="bi bi-trash"></i> Remover</button>
        </div>
        <input type="hidden" name="item_orcamento_item_id[]" value="">
        <div class="row g-2">
            <div class="col-md-5"><label class="form-label">Produto cadastrado</label><select class="form-select" name="item_produto_id[]" data-produto-select><?= osProdutoOptions($contexto['produtos']) ?></select></div>
            <div class="col-md-5"><label class="form-label">Produto/Serviço *</label><input class="form-control" name="item_produto_nome[]" required></div>
            <div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="item_status[]"><option value="pendente">Pendente</option><option value="em_producao">Em produção</option><option value="concluido">Concluído</option><option value="cancelado">Cancelado</option></select></div>
            <div class="col-md-2"><label class="form-label">Qtd.</label><input class="form-control calc-area" name="item_quantidade[]" value="1"></div>
            <div class="col-md-2"><label class="form-label">Unidade</label><input class="form-control" name="item_unidade[]" value="un"></div>
            <div class="col-md-2"><label class="form-label">Largura</label><input class="form-control calc-area" name="item_largura[]" value="0"></div>
            <div class="col-md-2"><label class="form-label">Altura</label><input class="form-control calc-area" name="item_altura[]" value="0"></div>
            <div class="col-md-2 d-flex align-items-end"><span class="badge badge-info w-100 justify-content-center" data-area-preview>0 m²</span></div>
            <div class="col-md-4"><label class="form-label">Material</label><input class="form-control" name="item_material[]"></div>
            <div class="col-md-4"><label class="form-label">Acabamento</label><input class="form-control" name="item_acabamento[]"></div>
            <div class="col-md-4"><label class="form-label">Arquivo/Referência</label><input class="form-control" name="item_arquivo_ref[]"></div>
            <div class="col-12"><label class="form-label">Descrição do item</label><input class="form-control" name="item_descricao[]"></div>
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
    const formatArea = value => value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 3 }) + ' m²';
    const renumerar = () => {
        document.querySelectorAll('[data-os-item]').forEach((item, index) => {
            const number = item.querySelector('[data-os-item-number]');
            if (number) number.textContent = index + 1;
        });
    };
    const calcularArea = item => {
        const qtd = parseBR(item.querySelector('[name="item_quantidade[]"]')?.value);
        const largura = parseBR(item.querySelector('[name="item_largura[]"]')?.value);
        const altura = parseBR(item.querySelector('[name="item_altura[]"]')?.value);
        const preview = item.querySelector('[data-area-preview]');
        if (preview) preview.textContent = formatArea(qtd * largura * altura);
    };
    document.addEventListener('input', event => {
        if (!event.target.matches('.calc-area')) return;
        calcularArea(event.target.closest('[data-os-item]'));
    });
    document.addEventListener('change', event => {
        if (!event.target.matches('[data-produto-select]')) return;
        const option = event.target.selectedOptions[0];
        const item = event.target.closest('[data-os-item]');
        if (!option || !item || !option.value) return;
        item.querySelector('[name="item_produto_nome[]"]').value = option.dataset.nome || '';
        item.querySelector('[name="item_unidade[]"]').value = option.dataset.unidade || 'un';
        item.querySelector('[name="item_largura[]"]').value = option.dataset.largura || '0';
        item.querySelector('[name="item_altura[]"]').value = option.dataset.altura || '0';
        calcularArea(item);
    });
    document.getElementById('addItemOs')?.addEventListener('click', () => {
        const wrapper = document.getElementById('itensOsWrapper');
        wrapper.appendChild(document.getElementById('osItemTemplate').content.cloneNode(true));
        renumerar();
    });
    document.addEventListener('click', event => {
        const btn = event.target.closest('[data-remove-os-item]');
        if (!btn) return;
        const itens = document.querySelectorAll('[data-os-item]');
        if (itens.length <= 1) return;
        btn.closest('[data-os-item]').remove();
        renumerar();
    });
    document.querySelectorAll('[data-os-item]').forEach(calcularArea);
    renumerar();
})();
</script>
