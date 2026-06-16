<?php
$statusClasses = [
    'ativo' => 'badge-success',
    'inativo' => 'badge-secondary',
];
$tipoLabels = [
    'fisica' => 'Pessoa Física',
    'juridica' => 'Pessoa Jurídica',
];
?>

<div class="card">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-buildings me-2 text-primary-kroma"></i>Fornecedores</h6>
        <span class="badge badge-info"><?= count($fornecedores) ?> cadastrados</span>
    </div>
    <div class="table-wrapper">
        <table class="table data-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Fornecedor</th>
                    <th>Contato</th>
                    <th>Local</th>
                    <th>Materiais</th>
                    <th>Status</th>
                    <th width="120">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fornecedores as $fornecedor): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($fornecedor['codigo']) ?></strong></td>
                    <td>
                        <strong><?= htmlspecialchars($fornecedor['nome']) ?></strong>
                        <div class="d-flex gap-1 flex-wrap mt-1">
                            <span class="badge badge-secondary"><?= htmlspecialchars($tipoLabels[$fornecedor['tipo_pessoa']] ?? $fornecedor['tipo_pessoa']) ?></span>
                            <?php if (!empty($fornecedor['cpf_cnpj'])): ?>
                                <span class="badge badge-info"><?= htmlspecialchars($fornecedor['cpf_cnpj']) ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div><?= htmlspecialchars($fornecedor['contato'] ?: '-') ?></div>
                        <?php if (!empty($fornecedor['telefone']) || !empty($fornecedor['whatsapp'])): ?>
                            <div class="small text-muted"><?= htmlspecialchars($fornecedor['whatsapp'] ?: $fornecedor['telefone']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($fornecedor['email'])): ?>
                            <div class="small text-muted"><?= htmlspecialchars($fornecedor['email']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(trim(($fornecedor['cidade'] ?? '') . '/' . ($fornecedor['estado'] ?? ''), '/')) ?: '-' ?></td>
                    <td><span class="badge badge-primary"><?= (int)$fornecedor['total_materiais'] ?> materiais</span></td>
                    <td><span class="badge <?= $statusClasses[$fornecedor['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars(ucfirst($fornecedor['status'])) ?></span></td>
                    <td>
                        <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/compras/fornecedores/<?= $fornecedor['id'] ?>/editar" title="Editar"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($fornecedores)): ?>
                <tr><td colspan="7"><span class="badge badge-secondary">Sem fornecedores cadastrados</span></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
