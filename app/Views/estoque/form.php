<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$isEdicao = !empty($material['id']);
$action = $isEdicao ? APP_URL . '/estoque/' . $material['id'] . '/editar' : APP_URL . '/estoque/novo';
$statusLabels = [
    'ativo' => 'Ativo',
    'inativo' => 'Inativo',
];

function estoqueMoney($value): string {
    return number_format((float)($value ?? 0), 2, ',', '.');
}

function estoqueDecimal($value): string {
    $value = (float)($value ?? 0);
    return rtrim(rtrim(number_format($value, 3, ',', '.'), '0'), ',');
}
?>

<form action="<?= $action ?>" method="POST" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-archive me-2 text-primary-kroma"></i>Dados do Material</h6>
                    <span class="badge badge-info"><?= $isEdicao ? 'Edição' : 'Novo cadastro' ?></span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Código</label>
                            <input class="form-control" name="codigo" value="<?= htmlspecialchars($material['codigo'] ?? '') ?>" placeholder="Automático">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nome *</label>
                            <input class="form-control" name="nome" required value="<?= htmlspecialchars($material['nome'] ?? '') ?>" placeholder="Ex: Lona 440g">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <?php foreach ($statusLabels as $value => $label): ?>
                                <option value="<?= $value ?>" <?= ($material['status'] ?? 'ativo') === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categoria</label>
                            <input class="form-control" name="categoria" value="<?= htmlspecialchars($material['categoria'] ?? '') ?>" placeholder="Lonas, tintas, vinil...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Unidade</label>
                            <input class="form-control" name="unidade" value="<?= htmlspecialchars($material['unidade'] ?? 'un') ?>" placeholder="un, m², m, kg">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Custo atual</label>
                            <input class="form-control money" name="custo_atual" value="<?= estoqueMoney($material['custo_atual'] ?? 0) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estoque mínimo</label>
                            <input class="form-control" name="estoque_minimo" value="<?= estoqueDecimal($material['estoque_minimo'] ?? 0) ?>">
                        </div>
                        <?php if (!$isEdicao): ?>
                        <div class="col-md-3">
                            <label class="form-label">Saldo inicial</label>
                            <input class="form-control" name="estoque_atual" value="<?= estoqueDecimal($material['estoque_atual'] ?? 0) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reserva inicial</label>
                            <input class="form-control" name="estoque_reservado" value="<?= estoqueDecimal($material['estoque_reservado'] ?? 0) ?>">
                        </div>
                        <?php endif; ?>
                        <div class="col-md-6">
                            <label class="form-label">Fornecedor</label>
                            <input class="form-control" name="fornecedor" value="<?= htmlspecialchars($material['fornecedor'] ?? '') ?>" placeholder="Fornecedor principal">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Localização</label>
                            <input class="form-control" name="localizacao" value="<?= htmlspecialchars($material['localizacao'] ?? '') ?>" placeholder="Prateleira, setor, sala...">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" name="observacoes" rows="4" placeholder="Regras de compra, marcas aceitas, cuidados de armazenagem"><?= htmlspecialchars($material['observacoes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-info-circle me-2 text-info"></i>Saldo</h6>
                </div>
                <div class="p-3 d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between"><span>Atual</span><strong><?= estoqueDecimal($material['estoque_atual'] ?? 0) ?> <?= htmlspecialchars($material['unidade'] ?? '') ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Reservado</span><span class="badge badge-warning"><?= estoqueDecimal($material['estoque_reservado'] ?? 0) ?></span></div>
                    <div class="d-flex justify-content-between"><span>Disponível</span><span class="badge badge-info"><?= estoqueDecimal(($material['estoque_atual'] ?? 0) - ($material['estoque_reservado'] ?? 0)) ?></span></div>
                    <?php if ($isEdicao): ?>
                    <span class="badge badge-secondary align-self-start">Use movimentações para alterar saldo</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-check2-circle me-2 text-success-kroma"></i>Ações</h6>
                </div>
                <div class="p-3 d-flex flex-column gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> <?= $isEdicao ? 'Atualizar Material' : 'Cadastrar Material' ?></button>
                    <a class="btn btn-secondary" href="<?= APP_URL ?>/estoque"><i class="bi bi-arrow-left"></i> Voltar</a>
                    <?php if ($isEdicao): ?>
                    <a class="btn btn-secondary" href="<?= APP_URL ?>/estoque/<?= $material['id'] ?>"><i class="bi bi-eye"></i> Ver Ficha</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>
