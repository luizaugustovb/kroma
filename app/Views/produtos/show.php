<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$statusClasses = [
    'ativo' => 'badge-success',
    'inativo' => 'badge-secondary',
    'em_revisao' => 'badge-warning',
];
$custos = [
    'custo_material' => 'Material',
    'custo_tinta' => 'Tinta',
    'custo_acabamento' => 'Acabamento',
    'custo_mao_obra' => 'Mão de obra',
    'custo_maquina' => 'Hora máquina',
    'custo_terceiros' => 'Terceiros/Frete',
];
$custoTotal = 0;
foreach (array_keys($custos) as $campoCusto) {
    $custoTotal += (float)($produto[$campoCusto] ?? 0);
}
?>

<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-box me-2 text-primary-kroma"></i>Ficha do Produto</h6>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge <?= $statusClasses[$produto['status']] ?? 'badge-secondary' ?>"><?= $statusLabels[$produto['status']] ?? $produto['status'] ?></span>
                    <span class="badge badge-info"><?= $tipoLabels[$produto['tipo']] ?? $produto['tipo'] ?></span>
                    <?php if (!empty($produto['prioridade_8020'])): ?>
                        <span class="badge badge-warning">Prioridade 80/20</span>
                    <?php endif; ?>
                    <?php if (!empty($produto['perecivel'])): ?>
                        <span class="badge badge-primary">Perecível</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="p-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <span class="badge badge-secondary mb-2">Código</span>
                        <div class="fw-bold"><?= htmlspecialchars($produto['codigo'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="badge badge-secondary mb-2">Categoria</span>
                        <div class="fw-bold"><?= htmlspecialchars($produto['categoria_nome'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="badge badge-secondary mb-2">Unidade</span>
                        <div class="fw-bold"><?= htmlspecialchars($produto['unidade'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="badge badge-secondary mb-2">Dimensão padrão</span>
                        <div class="fw-bold">
                            <?= number_format((float)($produto['largura_padrao'] ?? 0), 2, ',', '.') ?>
                            x
                            <?= number_format((float)($produto['altura_padrao'] ?? 0), 2, ',', '.') ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <span class="badge badge-secondary mb-2">Validade</span>
                        <div class="fw-bold"><?= (int)($produto['validade_dias'] ?? 0) ?> dias</div>
                    </div>
                    <div class="col-md-4">
                        <span class="badge badge-secondary mb-2">Atualização</span>
                        <div class="fw-bold"><?= !empty($produto['updated_at']) ? date('d/m/Y H:i', strtotime($produto['updated_at'])) : '-' ?></div>
                    </div>
                    <div class="col-12">
                        <span class="badge badge-secondary mb-2">Descrição comercial</span>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($produto['descricao'] ?: 'Sem descrição.')) ?></p>
                    </div>
                    <div class="col-md-6">
                        <span class="badge badge-secondary mb-2">Questionário de venda</span>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($produto['questionario'] ?: 'Não informado.')) ?></p>
                    </div>
                    <div class="col-md-6">
                        <span class="badge badge-secondary mb-2">Campos obrigatórios</span>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($produto['campos_obrigatorios'] ?: 'Não informado.')) ?></p>
                    </div>
                    <div class="col-12">
                        <span class="badge badge-info mb-2">Descrição para IA</span>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($produto['descricao_ia'] ?: 'Sem contexto para IA.')) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-calculator me-2 text-success-kroma"></i>Precificação</h6>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <div class="d-flex justify-content-between"><span>Custo direto</span><strong>R$ <?= number_format($custoTotal, 2, ',', '.') ?></strong></div>
                <div class="d-flex justify-content-between"><span>Desperdício</span><span class="badge badge-secondary"><?= number_format((float)$produto['desperdicio_percent'], 2, ',', '.') ?>%</span></div>
                <div class="d-flex justify-content-between"><span>Margem</span><span class="badge badge-success"><?= number_format((float)$produto['margem_percent'], 2, ',', '.') ?>%</span></div>
                <div class="d-flex justify-content-between"><span>Impostos</span><span class="badge badge-info"><?= number_format((float)$produto['impostos_percent'], 2, ',', '.') ?>%</span></div>
                <div class="d-flex justify-content-between"><span>Comissão</span><span class="badge badge-primary"><?= number_format((float)$produto['comissao_percent'], 2, ',', '.') ?>%</span></div>
                <hr>
                <div class="d-flex justify-content-between"><span>Preço mínimo</span><span class="badge badge-warning">R$ <?= number_format((float)$produto['preco_minimo'], 2, ',', '.') ?></span></div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Preço base</span>
                    <strong class="h4 mb-0 text-primary-kroma">R$ <?= number_format((float)$produto['preco_base'], 2, ',', '.') ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-cash-coin me-2 text-warning"></i>Custos</h6>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach ($custos as $campo => $label): ?>
                <div class="d-flex justify-content-between">
                    <span><?= $label ?></span>
                    <strong>R$ <?= number_format((float)($produto[$campo] ?? 0), 2, ',', '.') ?></strong>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-diagram-3 me-2 text-success-kroma"></i>Processos</h6>
                <span class="badge badge-primary"><?= count($processos) ?> etapas</span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php if (empty($processos)): ?>
                    <span class="badge badge-secondary align-self-start">Nenhum processo vinculado</span>
                <?php endif; ?>
                <?php foreach ($processos as $index => $processo): ?>
                <div class="border-kroma rounded-kroma p-2">
                    <span class="badge badge-secondary mb-2">Etapa <?= $index + 1 ?></span>
                    <div class="fw-bold"><?= htmlspecialchars($processo['nome']) ?></div>
                    <div class="small text-muted">
                        <?= htmlspecialchars($processo['setor'] ?? '-') ?>
                        <?php if (!empty($processo['maquina'])): ?>
                            - <?= htmlspecialchars($processo['maquina']) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-stars me-2 text-info"></i>Acabamentos</h6>
                <span class="badge badge-info"><?= count($acabamentos) ?> opções</span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php if (empty($acabamentos)): ?>
                    <span class="badge badge-secondary align-self-start">Nenhum acabamento vinculado</span>
                <?php endif; ?>
                <?php foreach ($acabamentos as $acabamento): ?>
                <div class="border-kroma rounded-kroma p-2 d-flex justify-content-between gap-2">
                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($acabamento['nome']) ?></div>
                        <span class="badge badge-secondary">R$ <?= number_format((float)$acabamento['custo_base'], 2, ',', '.') ?></span>
                    </div>
                    <?php if ((int)$acabamento['obrigatorio'] === 1): ?>
                        <span class="badge badge-warning align-self-start">Obrigatório</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-layers me-2 text-primary-kroma"></i>Variações</h6>
        <span class="badge badge-primary"><?= count($variacoes) ?> cadastradas</span>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>SKU</th>
                    <th>Unidade</th>
                    <th>Dimensão</th>
                    <th>Custo Extra</th>
                    <th>Preço Extra</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($variacoes)): ?>
                <tr><td colspan="6"><span class="badge badge-secondary">Nenhuma variação cadastrada</span></td></tr>
                <?php endif; ?>
                <?php foreach ($variacoes as $variacao): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($variacao['nome']) ?></strong></td>
                    <td><?= htmlspecialchars($variacao['sku'] ?: '-') ?></td>
                    <td><span class="badge badge-secondary"><?= htmlspecialchars($variacao['unidade'] ?: '-') ?></span></td>
                    <td>
                        <?= number_format((float)$variacao['largura'], 2, ',', '.') ?>
                        x
                        <?= number_format((float)$variacao['altura'], 2, ',', '.') ?>
                    </td>
                    <td>R$ <?= number_format((float)$variacao['custo_extra'], 2, ',', '.') ?></td>
                    <td><span class="badge badge-success">R$ <?= number_format((float)$variacao['preco_extra'], 2, ',', '.') ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-primary" href="<?= APP_URL ?>/produtos/<?= $produto['id'] ?>/editar"><i class="bi bi-pencil"></i> Editar Produto</a>
    <form action="<?= APP_URL ?>/produtos/<?= $produto['id'] ?>/duplicar" method="POST" data-loading>
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <button class="btn btn-secondary" type="submit"><i class="bi bi-files"></i> Duplicar</button>
    </form>
    <form action="<?= APP_URL ?>/produtos/<?= $produto['id'] ?>/excluir" method="POST" data-confirm="Inativar este produto?" data-loading>
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <button class="btn btn-danger" type="submit"><i class="bi bi-slash-circle"></i> Inativar</button>
    </form>
    <a class="btn btn-secondary" href="<?= APP_URL ?>/produtos"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>
