<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();

$tipoLabels = [
    'receita' => 'Receita',
    'imposto' => 'Imposto',
    'custo_variavel' => 'Custo Variável',
    'despesa_operacional' => 'Despesa Operacional',
    'depreciacao' => 'Depreciação',
    'juros' => 'Juros',
];

$tipoClasses = [
    'receita' => 'badge-success',
    'imposto' => 'badge-warning',
    'custo_variavel' => 'badge-info',
    'despesa_operacional' => 'badge-secondary',
    'depreciacao' => 'badge-danger',
    'juros' => 'badge-danger',
];
?>

<div class="row g-3 mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-tags me-2 text-primary-kroma"></i>Nova Categoria</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/financeiro/dre/categorias/novo" class="row g-3 align-items-end" data-loading>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <div class="col-md-4">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" placeholder="Ex: Materiais Gráficos" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($tipoLabels as $val => $label): ?>
                            <option value="<?= $val ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Palavras-chave (vírgula)</label>
                        <input type="text" name="palavras_chave" class="form-control" placeholder="material,insumo,chapa">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-list me-2 text-info"></i>Categorias Cadastradas</h6>
        <span class="badge badge-info"><?= count($categorias) ?> categorias</span>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Palavras-chave</th>
                    <th width="160">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $cat): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($cat['nome']) ?></strong></td>
                    <td><span class="badge <?= $tipoClasses[$cat['tipo']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($tipoLabels[$cat['tipo']] ?? $cat['tipo']) ?></span></td>
                    <td><span class="small text-muted"><?= htmlspecialchars($cat['palavras_chave'] ?: '-') ?></span></td>
                    <td>
                        <button class="btn btn-icon btn-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#editCat<?= $cat['id'] ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="<?= APP_URL ?>/financeiro/dre/categorias/<?= $cat['id'] ?>/excluir" method="POST" style="display:inline" data-loading
                              onsubmit="return confirm('Excluir categoria «<?= htmlspecialchars($cat['nome'], ENT_QUOTES) ?>»?')">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <button class="btn btn-icon btn-danger btn-sm" type="submit"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <tr class="collapse" id="editCat<?= $cat['id'] ?>">
                    <td colspan="4" style="padding:0; border:none">
                        <div class="p-3" style="background: rgba(255,255,255,0.02);">
                            <form method="POST" action="<?= APP_URL ?>/financeiro/dre/categorias/<?= $cat['id'] ?>/editar" class="row g-3 align-items-end" data-loading>
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <div class="col-md-4">
                                    <label class="form-label">Nome</label>
                                    <input type="text" name="nome" class="form-control form-control-sm" value="<?= htmlspecialchars($cat['nome']) ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tipo</label>
                                    <select name="tipo" class="form-select form-select-sm" required>
                                        <?php foreach ($tipoLabels as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= $cat['tipo'] === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Palavras-chave</label>
                                    <input type="text" name="palavras_chave" class="form-control form-control-sm" value="<?= htmlspecialchars($cat['palavras_chave'] ?? '') ?>">
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-check"></i></button>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($categorias)): ?>
                <tr><td colspan="4"><span class="badge badge-secondary">Nenhuma categoria cadastrada</span></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
