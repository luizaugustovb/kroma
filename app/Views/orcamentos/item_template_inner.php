<div class="orcamento-item border-kroma rounded-kroma p-3" data-item>
    <div class="d-flex justify-content-between align-items-start mb-3">
        <span class="badge badge-primary">Item <span data-item-number>1</span></span>
        <button type="button" class="btn btn-secondary btn-sm" data-remove-item><i class="bi bi-trash"></i> Remover</button>
    </div>
    <div class="row g-2">
        <div class="col-md-5">
            <label class="form-label">Produto/Serviço *</label>
            <input class="form-control item-input" name="item_produto_nome[]" required value="" placeholder="Banner, ACM, DTF...">
        </div>
        <div class="col-md-2">
            <label class="form-label">Qtd.</label>
            <input class="form-control item-input" name="item_quantidade[]" value="1">
        </div>
        <div class="col-md-2">
            <label class="form-label">Unidade</label>
            <input class="form-control item-input" name="item_unidade[]" value="un">
        </div>
        <div class="col-md-3">
            <label class="form-label">Dimensão m²</label>
            <div class="d-flex gap-2">
                <input class="form-control item-input" name="item_largura[]" value="0" placeholder="L">
                <input class="form-control item-input" name="item_altura[]" value="0" placeholder="A">
            </div>
        </div>
        <div class="col-12">
            <label class="form-label">Descrição do Item</label>
            <input class="form-control item-input" name="item_descricao[]" value="" placeholder="Material, acabamento, observações técnicas">
        </div>
        <?php
        $custos = [
            'item_custo_material' => 'Material',
            'item_custo_tinta' => 'Tinta',
            'item_custo_acabamento' => 'Acabamento',
            'item_custo_mao_obra' => 'Mão de Obra',
            'item_custo_maquina' => 'Hora Máquina',
            'item_custo_terceiros' => 'Terceiros/Frete',
        ];
        foreach ($custos as $name => $label):
        ?>
        <div class="col-md-2">
            <label class="form-label"><?= $label ?></label>
            <input class="form-control item-input money" name="<?= $name ?>[]" value="0,00">
        </div>
        <?php endforeach; ?>
        <?php
        $percents = [
            'item_desperdicio_percent' => ['Desperdício %', 5],
            'item_margem_percent' => ['Margem %', 35],
            'item_impostos_percent' => ['Impostos %', 8],
            'item_comissao_percent' => ['Comissão %', 5],
            'item_desconto_percent' => ['Desc. Item %', 0],
        ];
        foreach ($percents as $name => [$label, $value]):
        ?>
        <div class="col-md-2">
            <label class="form-label"><?= $label ?></label>
            <input class="form-control item-input percent" name="<?= $name ?>[]" value="<?= $value ?>">
        </div>
        <?php endforeach; ?>
        <div class="col-md-2 d-flex align-items-end">
            <span class="badge badge-success w-100 justify-content-center" data-item-total>R$ 0,00</span>
        </div>
    </div>
</div>
