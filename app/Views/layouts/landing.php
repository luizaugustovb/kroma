<?php
$titulo = $seoTitulo ?? APP_NAME;
$metaDescription = $seoDescricao ?? '';
$metaKeywords = $seoKeywords ?? '';
$canonical = $canonical ?? APP_URL . '/';
$analyticsId = trim($analyticsId ?? '');
$gtmId = trim($gtmId ?? '');
$ogTitle = trim($ogTitle ?? '') ?: $titulo;
$ogDescription = trim($ogDescription ?? '') ?: $metaDescription;
$ogImage = trim($ogImage ?? '') ?: APP_URL . '/public/assets/img/landing-hero-printshop.png';
$faviconUrl = trim($faviconUrl ?? '') ?: APP_URL . '/public/assets/img/icone.png';
$robotsIndex = $robotsIndex ?? 'index';
$robotsFollow = $robotsFollow ?? 'follow';
$googleVerification = trim($googleVerification ?? '');
$bingVerification = trim($bingVerification ?? '');
$clarityId = trim($clarityId ?? '');
$pixelId = trim($pixelId ?? '');
$structuredData = $structuredData ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($metaKeywords) ?>">
    <meta name="robots" content="<?= $robotsIndex ?>, <?= $robotsFollow ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($ogTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($ogDescription) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:site_name" content="<?= htmlspecialchars($titulo) ?>">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($ogTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($ogDescription) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($ogImage) ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($faviconUrl) ?>">
    <link rel="shortcut icon" type="image/png" href="<?= htmlspecialchars($faviconUrl) ?>">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($faviconUrl) ?>">

    <!-- Verificação -->
    <?php if ($googleVerification !== ''): ?>
    <meta name="google-site-verification" content="<?= htmlspecialchars($googleVerification) ?>">
    <?php endif; ?>
    <?php if ($bingVerification !== ''): ?>
    <meta name="msvalidate.01" content="<?= htmlspecialchars($bingVerification) ?>">
    <?php endif; ?>

    <!-- Google Tag Manager (head) -->
    <?php if ($gtmId !== ''): ?>
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?= htmlspecialchars($gtmId, ENT_QUOTES) ?>');</script>
    <?php endif; ?>

    <!-- Google Analytics (só se começar com G-) -->
    <?php if ($analyticsId !== '' && str_starts_with($analyticsId, 'G-')): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars(rawurlencode($analyticsId)) ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= htmlspecialchars($analyticsId, ENT_QUOTES) ?>');
    </script>
    <?php endif; ?>

    <!-- Microsoft Clarity -->
    <?php if ($clarityId !== ''): ?>
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src='https://www.clarity.ms/tag/'+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);})(window,document,'clarity','script','<?= htmlspecialchars($clarityId, ENT_QUOTES) ?>');
    </script>
    <?php endif; ?>

    <!-- Meta Pixel -->
    <?php if ($pixelId !== ''): ?>
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '<?= htmlspecialchars($pixelId, ENT_QUOTES) ?>');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?= htmlspecialchars(rawurlencode($pixelId)) ?>&ev=PageView&noscript=1"></noscript>
    <?php endif; ?>

    <!-- JSON-LD Schema.org -->
    <?php if ($structuredData): ?>
    <script type="application/ld+json"><?= $structuredData ?></script>
    <?php endif; ?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/kroma.css">

    <style>
        body { background:#f6f8fb; color:var(--text-primary); }
        .landing-nav {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        }
        .landing-nav .logo-img { height:32px; width:auto; max-width:100%; display:block; }
        .landing-nav .nav-toggle {
            display:none;
            background:none;
            border:0;
            color:var(--kroma-primary);
            font-size:24px;
            padding:4px;
        }
        .landing-hero {
            min-height: calc(100vh - 72px);
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
            background: #111827;
        }
        .landing-hero::before {
            content:"";
            position:absolute;
            inset:0;
            background-image:
                linear-gradient(90deg, rgba(5, 12, 22, 0.90) 0%, rgba(5, 12, 22, 0.70) 43%, rgba(5, 12, 22, 0.22) 72%, rgba(5, 12, 22, 0.08) 100%),
                var(--hero-image);
            background-size: cover;
            background-position: center;
            transform: scale(1.01);
        }
        .landing-hero::after {
            content:"";
            position:absolute;
            left:0;
            right:0;
            bottom:0;
            height:120px;
            background: linear-gradient(180deg, transparent, #f6f8fb);
        }
        .hero-content { position:relative; z-index:1; color:#fff; padding:72px 0 120px; }
        .hero-kicker {
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:8px 12px;
            border:1px solid rgba(255,255,255,0.22);
            border-radius:999px;
            background:rgba(255,255,255,0.12);
            color:#fff;
            font-size:13px;
            font-weight:800;
            text-transform:uppercase;
        }
        .hero-title {
            max-width: 760px;
            font-size: clamp(2.3rem, 6vw, 5.2rem);
            line-height: 0.98;
            font-weight: 900;
            letter-spacing: 0;
        }
        .hero-copy {
            max-width: 620px;
            color: rgba(255,255,255,0.82);
            font-size: 1.08rem;
        }
        .hero-stats {
            display:flex;
            flex-wrap:wrap;
            gap:10px;
        }
        .hero-stat {
            display:flex;
            align-items:center;
            gap:8px;
            padding:10px 12px;
            border:1px solid rgba(255,255,255,0.18);
            border-radius:8px;
            background:rgba(255,255,255,0.10);
            color:#fff;
            font-weight:800;
            font-size:13px;
        }
        .landing-section { padding:72px 0; }
        .section-eyebrow { color:var(--kroma-primary); font-size:12px; font-weight:900; text-transform:uppercase; }
        .landing-card {
            background:#fff;
            border:1px solid rgba(15, 23, 42, 0.08);
            border-radius:8px;
            box-shadow:0 16px 42px rgba(15, 23, 42, 0.08);
        }
        .service-card { height:100%; padding:24px; transition:transform .18s, box-shadow .18s; }
        .service-card:hover { transform:translateY(-3px); box-shadow:0 20px 48px rgba(15,23,42,.12); }
        .portfolio-shot {
            position:relative;
            min-height:260px;
            background:linear-gradient(135deg,#dce7f1,#f5f7fb);
            overflow:hidden;
        }
        .portfolio-shot img { width:100%; height:100%; object-fit:cover; position:absolute; inset:0; }
        .portfolio-shot .placeholder {
            min-height:260px;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#64748b;
            font-size:44px;
        }
        .quote-band { background:#101827; color:#fff; }
        .quote-band .form-control,
        .quote-band .form-select { background:#fff; border-color:rgba(255,255,255,0.18); }
        .file-drop {
            border:1px dashed rgba(255,255,255,0.36);
            border-radius:8px;
            padding:14px;
            background:rgba(255,255,255,0.08);
        }
        .btn-primary { background:var(--kroma-primary) !important; border-color:var(--kroma-primary) !important; }
        .btn-primary:hover { background:var(--kroma-primary-dark) !important; border-color:var(--kroma-primary-dark) !important; }
        @media (max-width: 767px) {
            .landing-nav .nav-toggle { display:flex; align-items:center; justify-content:center; }
            .landing-nav .nav-mobile-menu {
                display:none;
                position:absolute;
                top:100%;
                left:0;
                right:0;
                background:#fff;
                border-bottom:1px solid var(--border-color);
                padding:12px 16px;
                flex-direction:column;
                gap:8px;
                box-shadow:0 8px 24px rgba(0,0,0,0.08);
            }
            .landing-nav .nav-mobile-menu.open { display:flex; }
            .landing-hero { min-height:auto; }
            .landing-hero::before {
                background-image:
                    linear-gradient(180deg, rgba(5, 12, 22, 0.94) 0%, rgba(5, 12, 22, 0.82) 56%, rgba(5, 12, 22, 0.64) 100%),
                    var(--hero-image);
                background-position:center right;
            }
            .hero-content { padding:56px 0 96px; }
        }
    </style>
</head>
<body>
    <?php if ($gtmId !== ''): ?>
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= htmlspecialchars(rawurlencode($gtmId)) ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <?php endif; ?>
<nav class="landing-nav position-relative">
    <div class="container-fluid px-4 py-3 d-flex align-items-center justify-content-between">
        <a href="<?= APP_URL ?>/" class="d-flex align-items-center text-decoration-none flex-shrink-0">
            <img src="<?= APP_URL ?>/public/assets/img/nome.png" alt="KROMA PRINT" class="logo-img">
        </a>
        <div class="d-none d-md-flex align-items-center gap-3">
            <a href="#servicos" class="text-secondary text-decoration-none">Serviços</a>
            <a href="#portfolio" class="text-secondary text-decoration-none">Portfólio</a>
            <a href="#orcamento" class="text-secondary text-decoration-none">Orçamento</a>
            <a href="<?= APP_URL ?>/login" class="btn btn-secondary btn-sm"><i class="bi bi-lock"></i> Area Interna</a>
            <a href="#orcamento" class="btn btn-primary btn-sm"><i class="bi bi-whatsapp"></i> Solicitar Orçamento</a>
        </div>
        <button class="nav-toggle d-md-none" id="navToggle" aria-label="Menu">
            <i class="bi bi-list"></i>
        </button>
    </div>
    <div class="nav-mobile-menu d-md-none" id="navMobileMenu">
        <a href="#servicos" class="text-secondary text-decoration-none py-2 px-2 rounded">Serviços</a>
        <a href="#portfolio" class="text-secondary text-decoration-none py-2 px-2 rounded">Portfólio</a>
        <a href="#orcamento" class="text-secondary text-decoration-none py-2 px-2 rounded">Orçamento</a>
        <a href="<?= APP_URL ?>/login" class="btn btn-secondary btn-sm w-100"><i class="bi bi-lock"></i> Area Interna</a>
        <a href="#orcamento" class="btn btn-primary btn-sm w-100"><i class="bi bi-whatsapp"></i> Solicitar Orçamento</a>
    </div>
</nav>

<?php foreach (['success' => 'Sucesso', 'error' => 'Erro', 'warning' => 'Atencao'] as $tipo => $label): ?>
    <?php if (!empty($_SESSION['flash_' . $tipo])): ?>
    <div class="container-fluid px-4 pt-3">
        <div class="flash-message flash-<?= $tipo ?>">
            <span class="badge badge-<?= $tipo === 'error' ? 'danger' : $tipo ?>"><?= htmlspecialchars($label) ?></span>
            <span class="flash-text"><?= htmlspecialchars($_SESSION['flash_' . $tipo]) ?></span>
        </div>
    </div>
    <?php unset($_SESSION['flash_' . $tipo]); endif; ?>
<?php endforeach; ?>

<?= $content ?? '' ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/kroma.js"></script>
<script>
document.getElementById('navToggle')?.addEventListener('click', function() {
    document.getElementById('navMobileMenu').classList.toggle('open');
    const icon = this.querySelector('i');
    icon.classList.toggle('bi-list');
    icon.classList.toggle('bi-x');
});
</script>
</body>
</html>
