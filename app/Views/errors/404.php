<?php
/**
 * View: 404 Not Found — KROMA PRINT ERP
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Página não encontrada | KROMA PRINT ERP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap');
        body { margin:0; font-family:'Inter',sans-serif; background:#0B0D17; color:#E8EAED; min-height:100vh; display:flex; align-items:center; justify-content:center; text-align:center; }
        h1 { font-size:120px; font-weight:800; background:linear-gradient(135deg,#6C63FF,#FF6584); -webkit-background-clip:text; -webkit-text-fill-color:transparent; line-height:1; margin:0; }
        h2 { font-size:24px; font-weight:700; margin:16px 0 8px; }
        p  { color:#9AA0B4; margin:0 0 28px; }
        a  { display:inline-flex; align-items:center; gap:8px; padding:10px 24px; background:#6C63FF; color:#fff; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px; }
        a:hover { background:#5a52e0; }
    </style>
</head>
<body>
    <div>
        <h1>404</h1>
        <h2>Página não encontrada</h2>
        <p>A rota que você tentou acessar não existe.</p>
        <a href="<?= APP_URL ?? '' ?>/dashboard">
            <i class="bi bi-house"></i> Voltar ao Dashboard
        </a>
    </div>
</body>
</html>
