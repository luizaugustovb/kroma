<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$isEdicao = !empty($fornecedor['id']);
$action = $isEdicao ? APP_URL . '/compras/fornecedores/' . $fornecedor['id'] . '/editar' : APP_URL . '/compras/fornecedores/novo';
?>

<form action="<?= $action ?>" method="POST" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-building me-2 text-primary-kroma"></i>Dados do Fornecedor</h6>
                    <span class="badge badge-info"><?= $isEdicao ? 'Edição' : 'Novo cadastro' ?></span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Código</label>
                            <input class="form-control" name="codigo" value="<?= htmlspecialchars($fornecedor['codigo'] ?? '') ?>" placeholder="Automático">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nome *</label>
                            <input class="form-control" name="nome" required value="<?= htmlspecialchars($fornecedor['nome'] ?? '') ?>" placeholder="Nome fantasia ou razão social">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="ativo" <?= ($fornecedor['status'] ?? 'ativo') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                <option value="inativo" <?= ($fornecedor['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" name="tipo_pessoa">
                                <option value="juridica" <?= ($fornecedor['tipo_pessoa'] ?? 'juridica') === 'juridica' ? 'selected' : '' ?>>Pessoa Jurídica</option>
                                <option value="fisica" <?= ($fornecedor['tipo_pessoa'] ?? '') === 'fisica' ? 'selected' : '' ?>>Pessoa Física</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">CPF/CNPJ</label>
                            <input class="form-control" name="cpf_cnpj" value="<?= htmlspecialchars($fornecedor['cpf_cnpj'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Contato</label>
                            <input class="form-control" name="contato" value="<?= htmlspecialchars($fornecedor['contato'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">E-mail</label>
                            <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($fornecedor['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telefone</label>
                            <input class="form-control" name="telefone" value="<?= htmlspecialchars($fornecedor['telefone'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">WhatsApp</label>
                            <input class="form-control" name="whatsapp" value="<?= htmlspecialchars($fornecedor['whatsapp'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Endereço</label>
                            <input class="form-control" name="endereco" value="<?= htmlspecialchars($fornecedor['endereco'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cidade</label>
                            <input class="form-control" name="cidade" value="<?= htmlspecialchars($fornecedor['cidade'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">UF</label>
                            <input class="form-control" name="estado" maxlength="2" value="<?= htmlspecialchars($fornecedor['estado'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" name="observacoes" rows="3" placeholder="Condições comerciais, prazos, contatos extras"><?= htmlspecialchars($fornecedor['observacoes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-box-seam me-2 text-success-kroma"></i>Materiais Atendidos</h6>
                    <span class="badge badge-secondary">Opcional</span>
                </div>
                <div class="p-3">
                    <div class="row g-2">
                        <?php foreach ($materiais as $material): ?>
                        <div class="col-md-6">
                            <label class="border-kroma rounded-kroma p-2 d-flex align-items-start gap-2 h-100">
                                <input class="form-check-input mt-1" type="checkbox" name="materiais[]" value="<?= $material['id'] ?>" <?= in_array((string)$material['id'], $materiaisSelecionados, true) ? 'checked' : '' ?>>
                                <span>
                                    <strong><?= htmlspecialchars($material['nome']) ?></strong>
                                    <span class="badge badge-info ms-1"><?= htmlspecialchars($material['unidade']) ?></span>
                                    <span class="d-block small text-muted"><?= htmlspecialchars($material['codigo']) ?> · R$ <?= number_format((float)$material['custo_atual'], 2, ',', '.') ?></span>
                                </span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($materiais)): ?>
                        <div class="col-12"><span class="badge badge-secondary">Sem materiais cadastrados</span></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-check2-circle me-2 text-success-kroma"></i>Ações</h6>
                </div>
                <div class="p-3 d-flex flex-column gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> <?= $isEdicao ? 'Atualizar Fornecedor' : 'Cadastrar Fornecedor' ?></button>
                    <a class="btn btn-secondary" href="<?= APP_URL ?>/compras/fornecedores"><i class="bi bi-arrow-left"></i> Voltar</a>
                    <a class="btn btn-secondary" href="<?= APP_URL ?>/compras"><i class="bi bi-cart"></i> Compras</a>
                    <span class="badge badge-warning align-self-start">Materiais marcados agilizam novas compras</span>
                </div>
            </div>
        </div>
    </div>
</form>
