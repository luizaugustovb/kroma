<?php
use App\Services\Auth;
$csrfToken = Auth::csrfToken();
$isEdicao = !empty($usuario['id']);
$action = $isEdicao ? APP_URL . '/usuarios/' . $usuario['id'] . '/editar' : APP_URL . '/usuarios/novo';
?>

<form action="<?= $action ?>" method="POST" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-person-gear me-2 text-primary-kroma"></i>Dados do Usuário</h6>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label">Nome *</label>
                            <input class="form-control" name="nome" required value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">E-mail *</label>
                            <input class="form-control" type="email" name="email" required value="<?= htmlspecialchars($usuario['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telefone</label>
                            <input class="form-control" name="telefone" data-mask="telefone" value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">WhatsApp</label>
                            <input class="form-control" name="whatsapp" data-mask="telefone" value="<?= htmlspecialchars($usuario['whatsapp'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Perfil</label>
                            <select class="form-select" name="perfil_id">
                                <?php foreach ($perfis as $perfil): ?>
                                <option value="<?= $perfil['id'] ?>" <?= ($usuario['perfil_id'] ?? '') == $perfil['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($perfil['label']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cargo</label>
                            <input class="form-control" name="cargo" value="<?= htmlspecialchars($usuario['cargo'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Setor</label>
                            <input class="form-control" name="setor" value="<?= htmlspecialchars($usuario['setor'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cliente vinculado</label>
                            <select class="form-select" name="cliente_id">
                                <option value="">-- Sem cliente vinculado --</option>
                                <?php foreach (($clientes ?? []) as $cliente): ?>
                                <option value="<?= $cliente['id'] ?>" <?= (string)($usuario['cliente_id'] ?? '') === (string)$cliente['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cliente['nome']) ?><?= !empty($cliente['email']) ? ' - ' . htmlspecialchars($cliente['email']) : '' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= $isEdicao ? 'Nova Senha' : 'Senha *' ?></label>
                            <input class="form-control" type="password" name="senha" <?= $isEdicao ? '' : 'required' ?> autocomplete="new-password">
                            <div class="form-text"><?= $isEdicao ? 'Preencha apenas se quiser trocar.' : 'Mínimo de 8 caracteres.' ?></div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="ativo" id="ativo" value="1" <?= ($usuario['ativo'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="ativo">Usuário ativo</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" name="observacoes" rows="3"><?= htmlspecialchars($usuario['observacoes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-shield-check me-2 text-success-kroma"></i>Acesso</h6>
                </div>
                <div class="p-3">
                    <span class="badge badge-info mb-2">Senha com hash seguro</span>
                    <p class="text-secondary mb-0">O perfil define permissões de menu e módulos. Ajustes finos ficam em Perfis.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> <?= $isEdicao ? 'Atualizar Usuário' : 'Criar Usuário' ?></button>
        <a class="btn btn-secondary" href="<?= APP_URL ?>/usuarios"><i class="bi bi-x"></i> Cancelar</a>
    </div>
</form>
