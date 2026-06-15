<?php
/**
 * Layout de autenticação — KROMA PRINT ERP
 * Usado para telas de login, esqueci senha, etc.
 */

$titulo = $titulo ?? 'Login';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> — <?= APP_NAME ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/kroma.css">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-base);
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                linear-gradient(90deg, rgba(0, 101, 141, 0.06) 1px, transparent 1px),
                linear-gradient(0deg, rgba(0, 101, 141, 0.06) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: fixed;
            inset: auto 0 0 0;
            height: 220px;
            background: linear-gradient(180deg, transparent, rgba(0, 163, 224, 0.08));
            pointer-events: none;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 40px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.12);
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .auth-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            margin-bottom: 32px;
        }

        .auth-logo-icon {
            width: 52px;
            height: 52px;
            background: var(--gradient-primary);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 800;
            color: #fff;
            box-shadow: 0 8px 24px var(--kroma-primary-glow);
        }

        .auth-logo-text .brand   { font-size: 20px; font-weight: 800; color: var(--text-primary); line-height: 1; }
        .auth-logo-text .sub     { font-size: 11px; font-weight: 500; color: var(--kroma-primary); letter-spacing: 2px; text-transform: uppercase; }

        .auth-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-primary);
            text-align: center;
            margin-bottom: 6px;
        }

        .auth-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 28px;
        }

        .auth-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-logo">
        <div class="auth-logo-icon">K</div>
        <div class="auth-logo-text">
            <div class="brand">KROMA PRINT</div>
            <div class="sub">Sistema ERP</div>
        </div>
    </div>

    <?= $content ?? '' ?>

    <div class="auth-footer">
        &copy; <?= date('Y') ?> KROMA PRINT. Todos os direitos reservados.
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php if (isset($extraJs)): echo $extraJs; endif; ?>
</body>
</html>
