<?php
use App\Services\Auth;
$csrfToken = Auth::csrfToken();
?>

<h2 class="auth-title">Recuperar senha</h2>
<p class="auth-subtitle">Informe seu e-mail para receber as instruções</p>

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

<form action="<?= APP_URL ?>/esqueci-senha" method="POST" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <div class="mb-3">
        <label class="form-label">E-mail</label>
        <div class="input-group">
            <i class="bi bi-envelope input-group-icon"></i>
            <input class="form-control" type="email" name="email" required placeholder="seu@email.com">
        </div>
    </div>
    <button class="btn btn-primary w-100 btn-lg" type="submit"><i class="bi bi-send"></i> Enviar Instruções</button>
    <a href="<?= APP_URL ?>/login" class="btn btn-secondary w-100 mt-2"><i class="bi bi-arrow-left"></i> Voltar ao Login</a>
</form>
