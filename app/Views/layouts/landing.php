<?php $titulo = $titulo ?? APP_NAME; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>
    <link rel="icon" type="image/png" href="<?= APP_URL ?>/public/assets/img/icone.png">
    <link rel="shortcut icon" type="image/png" href="<?= APP_URL ?>/public/assets/img/icone.png">
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
        .landing-nav .logo-img {
            height: 32px;
            width: auto;
            max-width: 100%;
            display: block;
            transition: transform 0.2s;
        }
        .landing-nav .logo-img:hover {
            transform: scale(1.04);
        }
        .landing-nav .nav-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--kroma-primary);
            cursor: pointer;
            padding: 4px;
        }
        @media (max-width: 767px) {
            .landing-nav .nav-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .landing-nav .nav-mobile-menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #fff;
                border-bottom: 1px solid var(--border-color);
                padding: 12px 16px;
                flex-direction: column;
                gap: 8px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            }
            .landing-nav .nav-mobile-menu.open {
                display: flex;
            }
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
        .btn-primary {
            background: var(--kroma-primary) !important;
            border-color: var(--kroma-primary) !important;
        }
        .btn-primary:hover {
            background: var(--kroma-primary-dark) !important;
            border-color: var(--kroma-primary-dark) !important;
        }
    </style>
</head>
<body>
<nav class="landing-nav position-relative">
    <div class="container-fluid px-4 py-3 d-flex align-items-center justify-content-between">
        <a href="<?= APP_URL ?>/" class="d-flex align-items-center text-decoration-none flex-shrink-0">
            <img src="<?= APP_URL ?>/public/assets/img/nome.png" alt="KROMA PRINT" class="logo-img">
        </a>
        <div class="d-none d-md-flex align-items-center gap-3">
            <a href="#servicos" class="text-secondary text-decoration-none">Serviços</a>
            <a href="#portfolio" class="text-secondary text-decoration-none">Portfólio</a>
            <a href="#orcamento" class="text-secondary text-decoration-none">Orçamento</a>
            <a href="<?= APP_URL ?>/login" class="btn btn-secondary btn-sm"><i class="bi bi-lock"></i> Área Interna</a>
            <a href="#orcamento" class="btn btn-primary btn-sm"><i class="bi bi-whatsapp"></i> Solicitar Orçamento</a>
        </div>
        <button class="nav-toggle d-md-none" id="navToggle" aria-label="Menu">
            <i class="bi bi-list"></i>
        </button>
    </div>
    <div class="nav-mobile-menu d-md-none" id="navMobileMenu">
        <a href="#servicos" class="text-secondary text-decoration-none py-2 px-2 rounded" style="font-size:14px;">Serviços</a>
        <a href="#portfolio" class="text-secondary text-decoration-none py-2 px-2 rounded" style="font-size:14px;">Portfólio</a>
        <a href="#orcamento" class="text-secondary text-decoration-none py-2 px-2 rounded" style="font-size:14px;">Orçamento</a>
        <a href="<?= APP_URL ?>/login" class="btn btn-secondary btn-sm w-100"><i class="bi bi-lock"></i> Área Interna</a>
        <a href="#orcamento" class="btn btn-primary btn-sm w-100"><i class="bi bi-whatsapp"></i> Solicitar Orçamento</a>
    </div>
</nav>
<script>
document.getElementById('navToggle')?.addEventListener('click', function() {
    document.getElementById('navMobileMenu').classList.toggle('open');
    const icon = this.querySelector('i');
    icon.classList.toggle('bi-list');
    icon.classList.toggle('bi-x');
});
</script>

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
