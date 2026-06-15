<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$isEdicao = !empty($produto['id']);
$action = $isEdicao ? APP_URL . '/produtos/' . $produto['id'] . '/editar' : APP_URL . '/produtos/novo';
$produtoProcessos = array_map('strval', $produtoProcessos ?? []);
$produtoAcabamentos = array_map('strval', $produtoAcabamentos ?? []);
$produtoAcabamentosObrigatorios = array_map('strval', $produtoAcabamentosObrigatorios ?? []);

function produtoMoneyInput($value): string {
    return number_format((float)($value ?? 0), 2, ',', '.');
}

function produtoDecimalInput($value): string {
    $value = (float)($value ?? 0);
    return rtrim(rtrim(number_format($value, 4, ',', '.'), '0'), ',');
}
?>

<form action="<?= $action ?>" method="POST" data-loading id="formProduto">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-box me-2 text-primary-kroma"></i>Dados do Produto</h6>
                    <span class="badge badge-info"><?= $isEdicao ? 'Edição' : 'Novo cadastro' ?></span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Código</label>
                            <input class="form-control" name="codigo" value="<?= htmlspecialchars($produto['codigo'] ?? '') ?>" placeholder="Automático">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Nome *</label>
                            <input class="form-control" name="nome" required value="<?= htmlspecialchars($produto['nome'] ?? '') ?>" placeholder="Ex: Banner lona 440g">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categoria</label>
                            <select class="form-select" name="categoria_id">
                                <option value="">-- Sem categoria --</option>
                                <?php foreach ($contexto['categorias'] as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>" <?= (string)($produto['categoria_id'] ?? '') === (string)$categoria['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nome']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" name="tipo">
                                <?php foreach ($contexto['tipoLabels'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= ($produto['tipo'] ?? 'produto') === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unidade</label>
                            <input class="form-control" name="unidade" value="<?= htmlspecialchars($produto['unidade'] ?? 'un') ?>" placeholder="un, m², m, kg">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Largura padrão</label>
                            <input class="form-control calc-produto" name="largura_padrao" value="<?= produtoDecimalInput($produto['largura_padrao'] ?? 0) ?>" placeholder="0,00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Altura padrão</label>
                            <input class="form-control calc-produto" name="altura_padrao" value="<?= produtoDecimalInput($produto['altura_padrao'] ?? 0) ?>" placeholder="0,00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <?php foreach ($contexto['statusLabels'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= ($produto['status'] ?? 'ativo') === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <label class="d-flex align-items-center gap-2 mb-2">
                                <input class="form-check-input mt-0" type="checkbox" name="prioridade_8020" value="1" <?= !empty($produto['prioridade_8020']) ? 'checked' : '' ?>>
                                <span class="badge badge-warning">Prioridade 80/20</span>
                            </label>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <label class="d-flex align-items-center gap-2 mb-2">
                                <input class="form-check-input mt-0" type="checkbox" name="perecivel" value="1" <?= !empty($produto['perecivel']) ? 'checked' : '' ?>>
                                <span class="badge badge-info">Perecível</span>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Validade em dias</label>
                            <input class="form-control" name="validade_dias" value="<?= htmlspecialchars($produto['validade_dias'] ?? 0) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição comercial</label>
                            <textarea class="form-control" name="descricao" rows="2" placeholder="Resumo usado pela equipe comercial"><?= htmlspecialchars($produto['descricao'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição para IA</label>
                            <textarea class="form-control" name="descricao_ia" rows="2" placeholder="Contexto técnico para propostas e automações"><?= htmlspecialchars($produto['descricao_ia'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Questionário de venda</label>
                            <textarea class="form-control" name="questionario" rows="3" placeholder="Perguntas que o vendedor precisa fazer"><?= htmlspecialchars($produto['questionario'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Campos obrigatórios</label>
                            <textarea class="form-control" name="campos_obrigatorios" rows="3" placeholder="Ex: medida, material, acabamento, prazo"><?= htmlspecialchars($produto['campos_obrigatorios'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-diagram-3 me-2 text-success-kroma"></i>Processos Produtivos</h6>
                    <span class="badge badge-secondary">Roteiro</span>
                </div>
                <div class="p-3">
                    <div class="row g-2">
                        <?php foreach ($contexto['processos'] as $processo): ?>
                        <div class="col-md-6">
                            <label class="border-kroma rounded-kroma p-2 d-flex gap-2 align-items-start h-100">
                                <input class="form-check-input mt-1" type="checkbox" name="processos[]" value="<?= $processo['id'] ?>" <?= in_array((string)$processo['id'], $produtoProcessos, true) ? 'checked' : '' ?>>
                                <span>
                                    <strong><?= htmlspecialchars($processo['nome']) ?></strong>
                                    <span class="badge badge-info ms-1"><?= htmlspecialchars($processo['setor'] ?? 'Produção') ?></span>
                                    <?php if (!empty($processo['maquina'])): ?>
                                        <span class="d-block small text-muted"><?= htmlspecialchars($processo['maquina']) ?></span>
                                    <?php endif; ?>
                                </span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-layers me-2 text-info"></i>Variações</h6>
                    <button type="button" class="btn btn-secondary btn-sm" id="addVariacao"><i class="bi bi-plus"></i> Adicionar</button>
                </div>
                <div class="p-3">
                    <div id="variacoesWrapper" class="d-flex flex-column gap-3">
                        <?php foreach ($variacoes as $index => $variacao): ?>
                        <div class="border-kroma rounded-kroma p-3" data-variacao>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge badge-primary">Variação <span data-variacao-number><?= $index + 1 ?></span></span>
                                <button type="button" class="btn btn-secondary btn-sm" data-remove-variacao><i class="bi bi-trash"></i> Remover</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label">Nome</label>
                                    <input class="form-control" name="variacao_nome[]" value="<?= htmlspecialchars($variacao['nome'] ?? '') ?>" placeholder="Ex: 1,00 x 1,00m">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">SKU</label>
                                    <input class="form-control" name="variacao_sku[]" value="<?= htmlspecialchars($variacao['sku'] ?? '') ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Unidade</label>
                                    <input class="form-control" name="variacao_unidade[]" value="<?= htmlspecialchars($variacao['unidade'] ?? '') ?>" placeholder="un">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Largura</label>
                                    <input class="form-control" name="variacao_largura[]" value="<?= produtoDecimalInput($variacao['largura'] ?? 0) ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Altura</label>
                                    <input class="form-control" name="variacao_altura[]" value="<?= produtoDecimalInput($variacao['altura'] ?? 0) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Custo extra</label>
                                    <input class="form-control money" name="variacao_custo_extra[]" value="<?= produtoMoneyInput($variacao['custo_extra'] ?? 0) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Preço extra</label>
                                    <input class="form-control money" name="variacao_preco_extra[]" value="<?= produtoMoneyInput($variacao['preco_extra'] ?? 0) ?>">
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
                    <h6 class="card-title"><i class="bi bi-calculator me-2 text-success-kroma"></i>Precificação</h6>
                </div>
                <div class="p-3">
                    <div class="row g-2">
                        <?php
                        $custos = [
                            'custo_material' => 'Material',
                            'custo_tinta' => 'Tinta',
                            'custo_acabamento' => 'Acabamento',
                            'custo_mao_obra' => 'Mão de obra',
                            'custo_maquina' => 'Hora máquina',
                            'custo_terceiros' => 'Terceiros/Frete',
                        ];
                        foreach ($custos as $name => $label):
                        ?>
                        <div class="col-6">
                            <label class="form-label"><?= $label ?></label>
                            <input class="form-control money calc-produto" name="<?= $name ?>" value="<?= produtoMoneyInput($produto[$name] ?? 0) ?>">
                        </div>
                        <?php endforeach; ?>
                        <div class="col-6"><label class="form-label">Desperdício %</label><input class="form-control calc-produto" name="desperdicio_percent" value="<?= htmlspecialchars($produto['desperdicio_percent'] ?? 5) ?>"></div>
                        <div class="col-6"><label class="form-label">Margem %</label><input class="form-control calc-produto" name="margem_percent" value="<?= htmlspecialchars($produto['margem_percent'] ?? 35) ?>"></div>
                        <div class="col-6"><label class="form-label">Impostos %</label><input class="form-control calc-produto" name="impostos_percent" value="<?= htmlspecialchars($produto['impostos_percent'] ?? 8) ?>"></div>
                        <div class="col-6"><label class="form-label">Comissão %</label><input class="form-control calc-produto" name="comissao_percent" value="<?= htmlspecialchars($produto['comissao_percent'] ?? 5) ?>"></div>
                    </div>
                    <hr>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between"><span>Custo base</span><strong id="previewCustoProduto">R$ 0,00</strong></div>
                        <div class="d-flex justify-content-between"><span>Preço mínimo</span><span class="badge badge-warning" id="previewMinimoProduto">R$ 0,00</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Preço base</span>
                            <strong class="h4 mb-0 text-primary-kroma" id="previewBaseProduto">R$ 0,00</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-stars me-2 text-warning"></i>Acabamentos</h6>
                </div>
                <div class="p-3 d-flex flex-column gap-2">
                    <?php foreach ($contexto['acabamentos'] as $acabamento): ?>
                    <div class="border-kroma rounded-kroma p-2">
                        <label class="d-flex gap-2 align-items-start mb-2">
                            <input class="form-check-input mt-1" type="checkbox" name="acabamentos[]" value="<?= $acabamento['id'] ?>" <?= in_array((string)$acabamento['id'], $produtoAcabamentos, true) ? 'checked' : '' ?>>
                            <span>
                                <strong><?= htmlspecialchars($acabamento['nome']) ?></strong>
                                <span class="badge badge-secondary ms-1">R$ <?= number_format((float)$acabamento['custo_base'], 2, ',', '.') ?></span>
                            </span>
                        </label>
                        <label class="d-inline-flex align-items-center gap-2 mb-0">
                            <input class="form-check-input mt-0" type="checkbox" name="acabamentos_obrigatorios[]" value="<?= $acabamento['id'] ?>" <?= in_array((string)$acabamento['id'], $produtoAcabamentosObrigatorios, true) ? 'checked' : '' ?>>
                            <span class="badge badge-warning">Obrigatório</span>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-check2-circle me-2 text-success-kroma"></i>Ações</h6>
                </div>
                <div class="p-3 d-flex flex-column gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> <?= $isEdicao ? 'Atualizar Produto' : 'Cadastrar Produto' ?></button>
                    <a class="btn btn-secondary" href="<?= APP_URL ?>/produtos"><i class="bi bi-arrow-left"></i> Voltar</a>
                    <?php if ($isEdicao): ?>
                    <a class="btn btn-secondary" href="<?= APP_URL ?>/produtos/<?= $produto['id'] ?>"><i class="bi bi-eye"></i> Ver Ficha</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="variacaoTemplate">
    <div class="border-kroma rounded-kroma p-3" data-variacao>
        <div class="d-flex justify-content-between align-items-start mb-3">
            <span class="badge badge-primary">Variação <span data-variacao-number>1</span></span>
            <button type="button" class="btn btn-secondary btn-sm" data-remove-variacao><i class="bi bi-trash"></i> Remover</button>
        </div>
        <div class="row g-2">
            <div class="col-md-4"><label class="form-label">Nome</label><input class="form-control" name="variacao_nome[]" placeholder="Ex: 1,00 x 1,00m"></div>
            <div class="col-md-2"><label class="form-label">SKU</label><input class="form-control" name="variacao_sku[]"></div>
            <div class="col-md-2"><label class="form-label">Unidade</label><input class="form-control" name="variacao_unidade[]" placeholder="un"></div>
            <div class="col-md-2"><label class="form-label">Largura</label><input class="form-control" name="variacao_largura[]" value="0"></div>
            <div class="col-md-2"><label class="form-label">Altura</label><input class="form-control" name="variacao_altura[]" value="0"></div>
            <div class="col-md-3"><label class="form-label">Custo extra</label><input class="form-control money" name="variacao_custo_extra[]" value="0,00"></div>
            <div class="col-md-3"><label class="form-label">Preço extra</label><input class="form-control money" name="variacao_preco_extra[]" value="0,00"></div>
        </div>
    </div>
</template>

<script>
(function() {
    const parseBR = value => {
        value = String(value || '0').replace(/[^\d,.-]/g, '');
        if (value.includes(',')) {
            value = value.replace(/\./g, '').replace(',', '.');
        }
        return parseFloat(value) || 0;
    };
    const money = value => value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

    const calc = () => {
        const form = document.getElementById('formProduto');
        if (!form) return;
        const costKeys = ['custo_material','custo_tinta','custo_acabamento','custo_mao_obra','custo_maquina','custo_terceiros'];
        const custo = costKeys.reduce((total, key) => total + parseBR(form.elements[key]?.value), 0);
        const desperdicio = parseBR(form.elements['desperdicio_percent']?.value);
        const margem = parseBR(form.elements['margem_percent']?.value);
        const impostos = parseBR(form.elements['impostos_percent']?.value);
        const comissao = parseBR(form.elements['comissao_percent']?.value);
        const custoComDesperdicio = custo * (1 + desperdicio / 100);
        const minimo = custoComDesperdicio * (1 + (impostos + comissao) / 100);
        const base = custoComDesperdicio * (1 + (margem + impostos + comissao) / 100);
        document.getElementById('previewCustoProduto').textContent = money(custoComDesperdicio);
        document.getElementById('previewMinimoProduto').textContent = money(minimo);
        document.getElementById('previewBaseProduto').textContent = money(base);
    };

    const renumerar = () => {
        document.querySelectorAll('[data-variacao]').forEach((item, index) => {
            const number = item.querySelector('[data-variacao-number]');
            if (number) number.textContent = index + 1;
        });
    };

    document.querySelectorAll('.calc-produto').forEach(input => input.addEventListener('input', calc));
    document.getElementById('addVariacao')?.addEventListener('click', () => {
        const tpl = document.getElementById('variacaoTemplate');
        const wrapper = document.getElementById('variacoesWrapper');
        wrapper.appendChild(tpl.content.cloneNode(true));
        renumerar();
    });
    document.addEventListener('click', event => {
        const btn = event.target.closest('[data-remove-variacao]');
        if (!btn) return;
        const items = document.querySelectorAll('[data-variacao]');
        if (items.length <= 1) return;
        btn.closest('[data-variacao]').remove();
        renumerar();
    });
    calc();
    renumerar();
})();
</script>
