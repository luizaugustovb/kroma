<?php
use App\Services\Auth;
$csrfToken = Auth::csrfToken();
?>

<form action="<?= APP_URL ?>/perfil" method="POST" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-person me-2 text-primary-kroma"></i>Meus Dados</h6>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome</label>
                            <input class="form-control" name="nome" required value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-mail</label>
                            <input class="form-control" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefone</label>
                            <input class="form-control" name="telefone" data-mask="telefone" value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">WhatsApp</label>
                            <input class="form-control" name="whatsapp" data-mask="telefone" value="<?= htmlspecialchars($usuario['whatsapp'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Senha Atual</label>
                            <input class="form-control" type="password" name="senha_atual" autocomplete="current-password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nova Senha</label>
                            <input class="form-control" type="password" name="senha_nova" autocomplete="new-password">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <span class="badge badge-primary mb-2">Perfil</span>
                <h3 class="h6"><?= htmlspecialchars($usuario['perfil'] ?? '-') ?></h3>
                <p class="text-secondary mb-0">Alterações de perfil e permissões são feitas por administradores.</p>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2 mt-3">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Salvar Meu Perfil</button>
    </div>
</form>
