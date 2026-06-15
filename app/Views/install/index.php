<?php
/**
 * View: Instalação do Sistema — KROMA PRINT ERP
 * Acessível via: /install
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação — KROMA PRINT ERP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/kroma.css">
    <style>
        body { min-height:100vh; display:flex; align-items:center; justify-content:center; background:var(--bg-base); }
        .install-card { width:100%; max-width:560px; animation: fadeInUp 0.5s ease; }
        @keyframes fadeInUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
    </style>
</head>
<body>

<div class="install-card">
    <div class="card">
        <div style="padding:32px">

            <!-- Logo -->
            <div style="text-align:center; margin-bottom:28px;">
                <div style="width:64px;height:64px;background:var(--gradient-primary);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;font-size:28px;font-weight:800;color:#fff;margin-bottom:12px">K</div>
                <h1 style="font-size:20px;font-weight:700;color:var(--text-primary)">KROMA PRINT ERP</h1>
                <p style="color:var(--text-muted);font-size:13px">Assistente de Instalação v<?= APP_VERSION ?></p>
            </div>

            <?php if ($sucesso): ?>
            <!-- Sucesso -->
            <div style="text-align:center; padding:20px 0;">
                <div style="font-size:56px; margin-bottom:16px">✅</div>
                <h2 style="color:var(--kroma-accent);font-size:20px;font-weight:700">Sistema Instalado!</h2>
                <p style="color:var(--text-secondary); font-size:13px; margin:12px 0 24px">
                    Banco de dados criado com sucesso. Faça login com as credenciais master.
                </p>
                <div class="card" style="background:rgba(0,214,143,0.08);border-color:rgba(0,214,143,0.2);padding:16px;margin-bottom:20px;text-align:left">
                    <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">CREDENCIAIS MASTER</div>
                    <div style="font-size:13px;color:var(--text-primary)">
                        <i class="bi bi-envelope"></i> <strong>contato@luizaugusto.me</strong><br>
                        <i class="bi bi-lock"></i> <strong>Luiz2012@</strong>
                    </div>
                </div>
                <a href="<?= APP_URL ?>/login" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Ir para o Login
                </a>
            </div>

            <?php elseif (!empty($erros)): ?>
            <!-- Erros -->
            <?php foreach ($erros as $erro): ?>
            <div class="flash-message flash-error mb-3">
                <span class="badge badge-danger"><i class="bi bi-x-circle-fill"></i> Erro</span>
                <span class="flash-text"><?= htmlspecialchars($erro) ?></span>
            </div>
            <?php endforeach; ?>
            <p style="font-size:13px;color:var(--text-muted);margin-bottom:20px">
                Verifique as configurações de banco de dados em <code>config/database.php</code> e tente novamente.
            </p>
            <form action="<?= APP_URL ?>/install" method="POST">
                <button type="submit" class="btn btn-danger w-100">
                    <i class="bi bi-arrow-clockwise"></i> Tentar Novamente
                </button>
            </form>

            <?php else: ?>
            <!-- Tela inicial de instalação -->
            <div class="mb-4">
                <h5 style="color:var(--text-primary);font-size:15px;font-weight:600;margin-bottom:12px">
                    <i class="bi bi-database me-2" style="color:var(--kroma-primary)"></i>Pré-requisitos
                </h5>
                <div style="display:flex;flex-direction:column;gap:8px">
                    <?php
                    $reqs = [
                        ['PHP 8.0+', version_compare(PHP_VERSION, '8.0.0', '>='), PHP_VERSION],
                        ['PDO MySQL', extension_loaded('pdo_mysql'), 'Extensão PDO MySQL'],
                        ['MySQL', true, 'Verificado na instalação'],
                        ['GD (imagens)', extension_loaded('gd'), 'Extensão GD'],
                        ['Diretório gravável', is_writable(ROOT_PATH), ROOT_PATH],
                    ];
                    foreach ($reqs as [$label, $ok, $info]):
                    ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:<?= $ok ? 'rgba(0,214,143,0.08)' : 'rgba(255,61,113,0.08)' ?>;border-radius:6px;border:1px solid <?= $ok ? 'rgba(0,214,143,0.2)' : 'rgba(255,61,113,0.2)' ?>">
                        <span style="font-size:13px;color:var(--text-primary)"><?= $label ?></span>
                        <span style="font-size:12px;color:<?= $ok ? 'var(--kroma-accent)' : 'var(--kroma-danger)' ?>">
                            <i class="bi <?= $ok ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?>"></i>
                            <?= htmlspecialchars($info) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card mb-4" style="background:rgba(108,99,255,0.05);border-color:rgba(108,99,255,0.2);padding:16px;">
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:8px">CONFIGURAÇÃO DO BANCO</div>
                <div style="font-size:13px;color:var(--text-secondary)">
                    <i class="bi bi-server me-1"></i> Host: <strong><?= DB_HOST ?></strong><br>
                    <i class="bi bi-database me-1"></i> Banco: <strong><?= DB_NAME ?></strong><br>
                    <i class="bi bi-person me-1"></i> Usuário: <strong><?= DB_USER ?></strong>
                </div>
            </div>

            <form action="<?= APP_URL ?>/install" method="POST">
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-play-circle"></i> Instalar o Sistema
                </button>
            </form>

            <p style="font-size:11px;color:var(--text-muted);text-align:center;margin-top:12px">
                Este processo criará o banco de dados e inserirá os dados iniciais.
            </p>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>
