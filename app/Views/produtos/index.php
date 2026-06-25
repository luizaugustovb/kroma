<?php
use App\Services\Auth;

$statusClasses = [
    'ativo' => 'badge-success',
    'inativo' => 'badge-secondary',
    'em_revisao' => 'badge-warning',
];
$ativos = array_filter($produtos, fn($p) => $p['status'] === 'ativo');
$prioritarios = array_filter($produtos, fn($p) => (int)$p['prioridade_8020'] === 1);
$revisao = array_filter($produtos, fn($p) => $p['status'] === 'em_revisao');
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-box"></i></div>
            <div class="kpi-value"><?= count($produtos) ?></div>
            <div class="kpi-label">Produtos Cadastrados</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-check2-circle"></i></div>
            <div class="kpi-value"><?= count($ativos) ?></div>
            <div class="kpi-label">Ativos</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-star"></i></div>
            <div class="kpi-value"><?= count($prioritarios) ?></div>
            <div class="kpi-label">Prioridade 80/20</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-arrow-repeat"></i></div>
            <div class="kpi-value"><?= count($revisao) ?></div>
            <div class="kpi-label">Em Revisão</div>
        </div>
    </div>
</div>

<div class="table-wrapper">
    <table class="table datatable">
        <thead>
            <tr>
                <th>Código</th>
                <th>Produto</th>
                <th>Categoria</th>
                <th>Tipo</th>
                <th>Preço Base</th>
                <th>Preço Mínimo</th>
                <th>Composição</th>
                <th>Status</th>
                <th width="120">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $produto): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($produto['codigo'] ?? '-') ?></strong></td>
                    <td>
                        <div style="font-weight:700"><?= htmlspecialchars($produto['nome']) ?></div>
                        <div style="font-size:12px;color:var(--text-muted)">
                            <?= htmlspecialchars($produto['unidade']) ?>
                            <?php if ((int)$produto['prioridade_8020'] === 1): ?>
                                <span class="badge badge-warning ms-1">80/20</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($produto['categoria_nome'] ?? '-') ?></td>
                    <td><span class="badge badge-info"><?= $tipoLabels[$produto['tipo']] ?? $produto['tipo'] ?></span></td>
                    <td><strong>R$ <?= number_format((float)$produto['preco_base'], 2, ',', '.') ?></strong></td>
                    <td><span class="badge badge-warning">R$ <?= number_format((float)$produto['preco_minimo'], 2, ',', '.') ?></span></td>
                    <td>
                        <span class="badge badge-secondary"><?= (int)$produto['total_variacoes'] ?> variações</span>
                        <span class="badge badge-primary"><?= (int)$produto['total_processos'] ?> processos</span>
                    </td>
                    <td><span class="badge <?= $statusClasses[$produto['status']] ?? 'badge-secondary' ?>"><?= $statusLabels[$produto['status']] ?? $produto['status'] ?></span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/produtos/<?= $produto['id'] ?>" title="Ver"><i class="bi bi-eye"></i></a>
                            <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/produtos/<?= $produto['id'] ?>/editar" title="Editar"><i class="bi bi-pencil"></i></a>
                            <?php if (Auth::temPerfil('administrador')): ?>
                                <?php if ($produto['status'] === 'inativo'): ?>
                                    <form method="POST" action="<?= APP_URL ?>/produtos/<?= $produto['id'] ?>/excluir" class="d-inline" onsubmit="return confirm('EXCLUIR PERMANENTEMENTE \" <?= htmlspecialchars(addslashes($produto['nome'])) ?>\"? Esta ação não pode ser desfeita!')">
                                        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                                        <button type="submit" class="btn btn-icon btn-danger btn-sm" title="Excluir permanentemente"><i class="bi bi-trash-fill"></i></button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="<?= APP_URL ?>/produtos/<?= $produto['id'] ?>/excluir" class="d-inline" onsubmit="return confirm('Inativar produto \" <?= htmlspecialchars(addslashes($produto['nome'])) ?>\"?')">
                                        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                                        <button type="submit" class="btn btn-icon btn-warning btn-sm" title="Inativar"><i class="bi bi-slash-circle"></i></button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
