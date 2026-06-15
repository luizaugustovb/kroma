<?php $titulo = $titulo ?? APP_NAME; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/kroma.css">
    <style>
        body { background: #f7f9fb; color: var(--text-primary); }
        .landing-nav {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(247, 249, 251, 0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
        }
        .hero-band {
            background:
                linear-gradient(135deg, rgba(0, 101, 141, 0.12), rgba(0, 163, 224, 0.04)),
                linear-gradient(90deg, rgba(0, 101, 141, 0.05) 1px, transparent 1px),
                linear-gradient(0deg, rgba(0, 101, 141, 0.05) 1px, transparent 1px);
            background-size: auto, 34px 34px, 34px 34px;
        }
        .landing-card {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: var(--shadow-card);
        }
    </style>
</head>
<body>
<nav class="landing-nav">
    <div class="container-fluid px-4 py-3 d-flex align-items-center justify-content-between">
        <a href="<?= APP_URL ?>/" class="d-flex align-items-center gap-2 text-decoration-none">
            <span class="avatar avatar-sm">K</span>
            <strong style="color:var(--kroma-primary)">KROMA PRINT</strong>
        </a>
        <div class="d-none d-md-flex align-items-center gap-3">
            <a href="#servicos" class="text-secondary">Serviços</a>
            <a href="#portfolio" class="text-secondary">Portfólio</a>
            <a href="#orcamento" class="text-secondary">Orçamento</a>
            <a href="<?= APP_URL ?>/login" class="btn btn-secondary btn-sm"><i class="bi bi-lock"></i> Área Interna</a>
            <a href="#orcamento" class="btn btn-primary btn-sm"><i class="bi bi-whatsapp"></i> Solicitar Orçamento</a>
        </div>
    </div>
</nav>

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="container-fluid px-4 pt-3">
    <div class="flash-message flash-success">
        <span class="badge badge-success"><i class="bi bi-check-circle-fill"></i> Sucesso</span>
        <span class="flash-text"><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
    </div>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="container-fluid px-4 pt-3">
    <div class="flash-message flash-error">
        <span class="badge badge-danger"><i class="bi bi-x-circle-fill"></i> Erro</span>
        <span class="flash-text"><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
    </div>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<?php if (!empty($_SESSION['flash_warning'])): ?>
<div class="container-fluid px-4 pt-3">
    <div class="flash-message flash-warning">
        <span class="badge badge-warning"><i class="bi bi-exclamation-triangle-fill"></i> Atenção</span>
        <span class="flash-text"><?= htmlspecialchars($_SESSION['flash_warning']) ?></span>
    </div>
</div>
<?php unset($_SESSION['flash_warning']); endif; ?>

<?= $content ?? '' ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/kroma.js"></script>
</body>
</html>
