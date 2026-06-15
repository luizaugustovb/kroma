<?php
/**
 * View: Tela de Login — KROMA PRINT ERP
 * Layout: auth.php
 */

use App\Services\Auth;
$csrfToken = Auth::csrfToken();
?>

<h2 class="auth-title">Bem-vindo de volta</h2>
<p class="auth-subtitle">Entre com suas credenciais para acessar o sistema</p>

<!-- Flash messages -->
<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="flash-message flash-error mb-3">
    <span class="badge badge-danger"><i class="bi bi-x-circle-fill"></i> Erro</span>
    <span class="flash-text"><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="flash-message flash-success mb-3">
    <span class="badge badge-success"><i class="bi bi-check-circle-fill"></i> Sucesso</span>
    <span class="flash-text"><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<form action="<?= APP_URL ?>/login" method="POST" id="loginForm" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <!-- E-mail -->
    <div class="mb-3">
        <label class="form-label" for="email">E-mail</label>
        <div class="input-group">
            <i class="bi bi-envelope input-group-icon"></i>
            <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                placeholder="seu@email.com"
                required
                autocomplete="email"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            >
        </div>
    </div>

    <!-- Senha -->
    <div class="mb-3">
        <label class="form-label" for="senha">
            Senha
            <a href="<?= APP_URL ?>/esqueci-senha" class="float-end" style="font-size:11px;font-weight:400;text-transform:none">
                Esqueceu a senha?
            </a>
        </label>
        <div class="input-group" style="position:relative">
            <i class="bi bi-lock input-group-icon"></i>
            <input
                type="password"
                class="form-control"
                id="senha"
                name="senha"
                placeholder="••••••••"
                required
                autocomplete="current-password"
                style="padding-right: 44px;"
            >
            <button type="button" id="toggleSenha" style="
                position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
                background: none; border: none; color: var(--text-muted); cursor: pointer;
                z-index: 5; font-size: 15px;
            ">
                <i class="bi bi-eye" id="senhaIcon"></i>
            </button>
        </div>
    </div>

    <!-- Lembrar -->
    <div class="mb-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="lembrar" name="lembrar" value="1">
            <label class="form-check-label" for="lembrar" style="font-size:13px;color:var(--text-secondary)">
                Lembrar de mim por 30 dias
            </label>
        </div>
    </div>

    <!-- Botão -->
    <button type="submit" class="btn btn-primary w-100 btn-lg" id="btnLogin">
        <i class="bi bi-box-arrow-in-right"></i> Entrar no Sistema
    </button>
</form>

<script>
document.getElementById('toggleSenha').addEventListener('click', function() {
    const input = document.getElementById('senha');
    const icon  = document.getElementById('senhaIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
});
</script>
