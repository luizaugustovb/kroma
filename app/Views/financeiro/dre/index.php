<?php
function dreMoney(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

function drePercent(float $value, float $total): string
{
    if ($total <= 0) {
        return '-';
    }
    return number_format(($value / $total) * 100, 2, ',', '.') . '%';
}

$receitaBruta = (float)$dre['receita_bruta'];
$itens = $dre['itens'];

$grupos = [
    'imposto' => ['label' => 'Impostos', 'subtract' => true],
    'custo_variavel' => ['label' => 'Custos Variáveis', 'subtract' => true],
    'despesa_operacional' => ['label' => 'Despesas Operacionais', 'subtract' => true],
    'depreciacao' => ['label' => 'Depreciação', 'subtract' => true],
    'juros' => ['label' => 'Juros', 'subtract' => true],
];
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="kpi-value" style="font-size:22px"><?= dreMoney($receitaBruta) ?></div>
            <div class="kpi-label">Receita Bruta</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-cash-stack"></i></div>
            <div class="kpi-value" style="font-size:22px"><?= dreMoney((float)$dre['lucro_bruto']) ?></div>
            <div class="kpi-label">Lucro Bruto</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-bar-chart"></i></div>
            <div class="kpi-value" style="font-size:22px"><?= dreMoney((float)$dre['ebitda']) ?></div>
            <div class="kpi-label">EBITDA</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon <?= (float)$dre['lucro_liquido'] >= 0 ? 'success' : 'danger' ?>"><i class="bi bi-<?= (float)$dre['lucro_liquido'] >= 0 ? 'check-circle' : 'exclamation-triangle' ?>"></i></div>
            <div class="kpi-value" style="font-size:22px"><?= dreMoney((float)$dre['lucro_liquido']) ?></div>
            <div class="kpi-label">Lucro Líquido</div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-funnel me-2 text-primary-kroma"></i>Filtrar Período</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Data Início</label>
                <input type="date" name="de" class="form-control" value="<?= htmlspecialchars($dre['data_inicio']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Data Fim</label>
                <input type="date" name="ate" class="form-control" value="<?= htmlspecialchars($dre['data_fim']) ?>">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
                <a href="<?= APP_URL ?>/financeiro/dre" class="btn btn-secondary">Limpar</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-file-earmark-text me-2 text-success-kroma"></i>Demonstração do Resultado do Exercício</h6>
        <span class="badge badge-info"><?= date('d/m/Y', strtotime($dre['data_inicio'])) ?> até <?= date('d/m/Y', strtotime($dre['data_fim'])) ?></span>
    </div>
    <div class="table-wrapper">
        <table class="table table-dre">
            <thead>
                <tr>
                    <th width="50%">Descrição</th>
                    <th width="25%" class="text-end">Valor (R$)</th>
                    <th width="25%" class="text-end">% Receita Bruta</th>
                </tr>
            </thead>
            <tbody>
                <tr class="dre-total">
                    <td><strong>Receita Bruta</strong></td>
                    <td class="text-end"><strong><?= dreMoney($receitaBruta) ?></strong></td>
                    <td class="text-end"><strong>100,00%</strong></td>
                </tr>

                <?php if (!empty($itens)): ?>
                <tr class="dre-section">
                    <td colspan="3">Deduções</td>
                </tr>
                <?php foreach ($grupos as $tipo => $grupo):
                    $itensGrupo = array_filter($itens, fn($i) => $i['tipo'] === $tipo);
                    if (empty($itensGrupo)) continue;
                    $somaGrupo = array_sum(array_column($itensGrupo, 'valor'));
                ?>
                    <tr class="dre-subtotal">
                        <td><span class="ms-3 fw-semibold"><?= htmlspecialchars($grupo['label']) ?></span></td>
                        <td class="text-end fw-semibold text-danger">(<?= dreMoney($somaGrupo) ?>)</td>
                        <td class="text-end fw-semibold"><?= drePercent($somaGrupo, $receitaBruta) ?></td>
                    </tr>
                    <?php foreach ($itensGrupo as $item): ?>
                    <tr class="dre-item">
                        <td><span class="ms-5 text-muted"><?= htmlspecialchars($item['nome']) ?></span></td>
                        <td class="text-end text-muted"><?= dreMoney((float)$item['valor']) ?></td>
                        <td class="text-end text-muted"><?= drePercent((float)$item['valor'], $receitaBruta) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <?php endif; ?>

                <tr class="dre-total">
                    <td><strong>Receita Líquida</strong></td>
                    <td class="text-end"><strong><?= dreMoney((float)$dre['receita_liquida']) ?></strong></td>
                    <td class="text-end"><strong><?= drePercent((float)$dre['receita_liquida'], $receitaBruta) ?></strong></td>
                </tr>

                <tr class="dre-total">
                    <td><strong>(-) Custos Variáveis</strong></td>
                    <td class="text-end text-danger"><strong>(<?= dreMoney((float)$dre['custos_variaveis']) ?>)</strong></td>
                    <td class="text-end"><strong><?= drePercent((float)$dre['custos_variaveis'], $receitaBruta) ?></strong></td>
                </tr>

                <tr class="dre-total dre-highlight">
                    <td><strong>Lucro Bruto</strong></td>
                    <td class="text-end <?= (float)$dre['lucro_bruto'] >= 0 ? 'text-success' : 'text-danger' ?>"><strong><?= dreMoney((float)$dre['lucro_bruto']) ?></strong></td>
                    <td class="text-end"><strong><?= drePercent((float)$dre['lucro_bruto'], $receitaBruta) ?></strong></td>
                </tr>

                <tr class="dre-total">
                    <td><strong>(-) Despesas Operacionais</strong></td>
                    <td class="text-end text-danger"><strong>(<?= dreMoney((float)$dre['despesas_operacionais']) ?>)</strong></td>
                    <td class="text-end"><strong><?= drePercent((float)$dre['despesas_operacionais'], $receitaBruta) ?></strong></td>
                </tr>

                <tr class="dre-total dre-highlight">
                    <td><strong>EBITDA</strong></td>
                    <td class="text-end <?= (float)$dre['ebitda'] >= 0 ? 'text-success' : 'text-danger' ?>"><strong><?= dreMoney((float)$dre['ebitda']) ?></strong></td>
                    <td class="text-end"><strong><?= drePercent((float)$dre['ebitda'], $receitaBruta) ?></strong></td>
                </tr>

                <tr class="dre-total">
                    <td><strong>(-) Depreciação</strong></td>
                    <td class="text-end text-danger"><strong>(<?= dreMoney((float)$dre['depreciacao']) ?>)</strong></td>
                    <td class="text-end"><strong><?= drePercent((float)$dre['depreciacao'], $receitaBruta) ?></strong></td>
                </tr>

                <tr class="dre-total">
                    <td><strong>(-) Juros</strong></td>
                    <td class="text-end text-danger"><strong>(<?= dreMoney((float)$dre['juros']) ?>)</strong></td>
                    <td class="text-end"><strong><?= drePercent((float)$dre['juros'], $receitaBruta) ?></strong></td>
                </tr>

                <tr class="dre-total dre-result">
                    <td><strong>Lucro Líquido</strong></td>
                    <td class="text-end <?= (float)$dre['lucro_liquido'] >= 0 ? 'text-success' : 'text-danger' ?>"><strong><?= dreMoney((float)$dre['lucro_liquido']) ?></strong></td>
                    <td class="text-end <?= (float)$dre['lucro_liquido'] >= 0 ? 'text-success' : 'text-danger' ?>"><strong><?= drePercent((float)$dre['lucro_liquido'], $receitaBruta) ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
.table-dre { font-size: 0.9rem; }
.table-dre td { padding: 0.5rem 0.75rem; border-top: none; }
.table-dre tbody tr:not(:last-child) td { border-bottom: 1px solid rgba(255,255,255,0.04); }
.table-dre .dre-section td {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--kroma-text-muted);
    padding-top: 1.25rem;
    padding-bottom: 0.25rem;
}
.table-dre .dre-subtotal td {
    background: rgba(255,255,255,0.03);
    border-bottom: 1px solid rgba(255,255,255,0.08) !important;
}
.table-dre .dre-total td {
    border-top: 1px solid rgba(255,255,255,0.1) !important;
    padding-top: 0.6rem;
    padding-bottom: 0.6rem;
}
.table-dre .dre-highlight td {
    background: rgba(0, 163, 224, 0.08);
}
.table-dre .dre-result td {
    background: rgba(0, 214, 143, 0.1);
    font-size: 1rem;
}
.table-dre .dre-item td {
    font-size: 0.85rem;
}
</style>
