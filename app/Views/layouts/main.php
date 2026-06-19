<?php
/**
 * Layout principal do sistema ERP — KROMA PRINT
 * Inclui sidebar, topbar e área de conteúdo
 * 
 * Variáveis esperadas:
 * $titulo   - Título da página
 * $subtitulo - Subtítulo (opcional)
 * $breadcrumbs - Array de ['label' => '...', 'url' => '...'] (opcional)
 */

use App\Services\Auth;
use App\Services\AlertasService;

$usuario    = Auth::usuario();
$csrfToken  = Auth::csrfToken();
$titulo     = $titulo ?? 'Dashboard';
$subtitulo  = $subtitulo ?? '';
$breadcrumbs = $breadcrumbs ?? [];
$homeUrl = Auth::pode('dashboard') ? '/dashboard' : (Auth::pode('portal') ? '/portal' : '/perfil');

// Alertas
$notifCount = 0;
try {
    $notifCount = (new AlertasService())->total();
} catch (Exception $e) {}

// Gera iniciais do nome para avatar
$iniciais = implode('', array_map(fn($p) => strtoupper($p[0]), array_slice(explode(' ', $usuario['nome'] ?? 'U'), 0, 2)));
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= APP_URL ?>">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
    <title><?= htmlspecialchars($titulo) ?> — <?= APP_NAME ?></title>

    <link rel="icon" type="image/png" href="<?= APP_URL ?>/public/assets/img/icone.png">
    <link rel="shortcut icon" type="image/png" href="<?= APP_URL ?>/public/assets/img/icone.png">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- KROMA CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/kroma.css">

    <?php if (isset($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
</head>
<body>

<!-- Overlay mobile -->
<div class="sidebar-overlay"></div>

<div class="kroma-wrapper">

    <!-- ======================== SIDEBAR ======================== -->
    <aside class="sidebar" id="sidebar">

        <!-- Logo -->
        <div class="sidebar-brand">
            <img src="<?= APP_URL ?>/public/assets/img/icone.png" alt="KROMA" class="logo-icon-img">
            <div>
                <img src="<?= APP_URL ?>/public/assets/img/nome.png" alt="KROMA PRINT" class="logo-text-img">
                <div class="logo-sub">Sistema ERP</div>
            </div>
        </div>

        <!-- Navegação -->
        <nav class="sidebar-nav" id="sidebarNav">

            <!-- Principal -->
            <?php if (Auth::pode('dashboard')): ?>
            <div class="nav-group">
                <div class="nav-group-label">Principal</div>
                <a href="<?= APP_URL ?>/dashboard" class="nav-item" data-tooltip="Dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <span class="nav-label">Dashboard</span>
                </a>
            </div>
            <?php endif; ?>

            <!-- Cliente -->
            <?php if (Auth::pode('portal')): ?>
            <div class="nav-group">
                <div class="nav-group-label">Cliente</div>
                <a href="<?= APP_URL ?>/portal" class="nav-item" data-tooltip="Portal do Cliente">
                    <i class="bi bi-person-workspace"></i>
                    <span class="nav-label">Portal do Cliente</span>
                </a>
            </div>
            <?php endif; ?>

            <!-- Comercial -->
            <?php if (Auth::pode('crm') || Auth::pode('clientes') || Auth::pode('orcamentos')): ?>
            <div class="nav-group">
                <div class="nav-group-label">Comercial</div>

                <?php if (Auth::pode('crm')): ?>
                <a href="<?= APP_URL ?>/crm" class="nav-item" data-tooltip="CRM">
                    <i class="bi bi-kanban"></i>
                    <span class="nav-label">CRM / Kanban</span>
                    <?php if ($notifCount > 0): ?>
                    <span class="nav-badge"><?= $notifCount ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>

                <?php if (Auth::pode('clientes')): ?>
                <a href="<?= APP_URL ?>/clientes" class="nav-item" data-tooltip="Clientes">
                    <i class="bi bi-people"></i>
                    <span class="nav-label">Clientes</span>
                </a>
                <?php endif; ?>

                <?php if (Auth::pode('orcamentos')): ?>
                <a href="<?= APP_URL ?>/orcamentos" class="nav-item" data-tooltip="Orçamentos">
                    <i class="bi bi-file-earmark-text"></i>
                    <span class="nav-label">Orçamentos</span>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Operacional -->
            <?php if (Auth::pode('produtos') || Auth::pode('producao') || Auth::pode('agenda') || Auth::pode('led') || Auth::pode('estoque') || Auth::pode('compras')): ?>
            <div class="nav-group">
                <div class="nav-group-label">Operacional</div>

                <?php if (Auth::pode('produtos')): ?>
                <a href="<?= APP_URL ?>/produtos" class="nav-item" data-tooltip="Produtos">
                    <i class="bi bi-box"></i>
                    <span class="nav-label">Produtos</span>
                </a>
                <?php endif; ?>

                <?php if (Auth::pode('producao')): ?>
                <a href="<?= APP_URL ?>/producao" class="nav-item" data-tooltip="Produção">
                    <i class="bi bi-gear"></i>
                    <span class="nav-label">OS / Produção</span>
                </a>
                <?php endif; ?>

                <?php if (Auth::pode('agenda')): ?>
                <a href="<?= APP_URL ?>/agenda" class="nav-item" data-tooltip="Agenda de Instalações">
                    <i class="bi bi-calendar-check"></i>
                    <span class="nav-label">Agenda de Instalações</span>
                </a>
                <?php endif; ?>

                <?php if (Auth::pode('led')): ?>
                <a href="<?= APP_URL ?>/led" class="nav-item" data-tooltip="Painéis de LED">
                    <i class="bi bi-display"></i>
                    <span class="nav-label">Painéis de LED</span>
                </a>
                <?php endif; ?>

                <?php if (Auth::pode('estoque')): ?>
                <a href="<?= APP_URL ?>/estoque" class="nav-item" data-tooltip="Estoque">
                    <i class="bi bi-archive"></i>
                    <span class="nav-label">Estoque</span>
                </a>
                <?php endif; ?>

                <?php if (Auth::pode('compras')): ?>
                <a href="<?= APP_URL ?>/compras" class="nav-item" data-tooltip="Compras">
                    <i class="bi bi-cart"></i>
                    <span class="nav-label">Compras</span>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- RH -->
            <?php if (Auth::pode('colaboradores') || Auth::pode('equipamentos')): ?>
            <div class="nav-group">
                <div class="nav-group-label">RH</div>
                <a href="<?= APP_URL ?>/rh" class="nav-item" data-tooltip="RH Operacional">
                    <i class="bi bi-person-badge"></i>
                    <span class="nav-label">RH Operacional</span>
                </a>
            </div>
            <?php endif; ?>

            <!-- Qualidade -->
            <?php if (Auth::pode('pops')): ?>
            <div class="nav-group">
                <div class="nav-group-label">Qualidade</div>
                <a href="<?= APP_URL ?>/qualidade" class="nav-item" data-tooltip="Qualidade / POPs">
                    <i class="bi bi-clipboard-check"></i>
                    <span class="nav-label">Qualidade / POPs</span>
                </a>
            </div>
            <?php endif; ?>

            <!-- Comunicação -->
            <?php if (Auth::pode('chamados') || Auth::pode('whatsapp') || Auth::pode('chat')): ?>
            <div class="nav-group">
                <div class="nav-group-label">Comunicação</div>
                <?php if (Auth::pode('chamados')): ?>
                <a href="<?= APP_URL ?>/chamados" class="nav-item" data-tooltip="Chamados Internos">
                    <i class="bi bi-ticket-detailed"></i>
                    <span class="nav-label">Chamados Internos</span>
                </a>
                <?php endif; ?>

                <?php if (Auth::pode('chat')): ?>
                <a href="<?= APP_URL ?>/chat" class="nav-item" data-tooltip="Chat Interno">
                    <i class="bi bi-chat-dots"></i>
                    <span class="nav-label">Chat Interno</span>
                </a>
                <?php endif; ?>

                <?php if (Auth::pode('whatsapp')): ?>
                <a href="<?= APP_URL ?>/whatsapp" class="nav-item" data-tooltip="WhatsApp">
                    <i class="bi bi-whatsapp"></i>
                    <span class="nav-label">WhatsApp</span>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Financeiro -->
            <?php if (Auth::pode('financeiro') || Auth::pode('comissoes')): ?>
            <div class="nav-group">
                <div class="nav-group-label">Financeiro</div>

                <?php if (Auth::pode('financeiro')): ?>
                <a href="<?= APP_URL ?>/financeiro" class="nav-item" data-tooltip="Financeiro">
                    <i class="bi bi-cash-stack"></i>
                    <span class="nav-label">Financeiro</span>
                </a>
                <?php endif; ?>

                <?php if (Auth::pode('comissoes')): ?>
                <a href="<?= APP_URL ?>/comissoes" class="nav-item" data-tooltip="Comissões">
                    <i class="bi bi-percent"></i>
                    <span class="nav-label">Comissões</span>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Inteligência -->
            <?php if (Auth::pode('bi') || Auth::pode('alertas') || Auth::pode('ia')): ?>
            <div class="nav-group">
                <div class="nav-group-label">Inteligência</div>
                <?php if (Auth::pode('alertas')): ?>
                <a href="<?= APP_URL ?>/alertas" class="nav-item" data-tooltip="Central de Alertas">
                    <i class="bi bi-bell"></i>
                    <span class="nav-label">Central de Alertas</span>
                </a>
                <?php endif; ?>

                <?php if (Auth::pode('ia')): ?>
                <a href="<?= APP_URL ?>/ia" class="nav-item" data-tooltip="Central de IA">
                    <i class="bi bi-stars"></i>
                    <span class="nav-label">Central de IA</span>
                </a>
                <?php endif; ?>

                <?php if (Auth::pode('bi')): ?>
                <a href="<?= APP_URL ?>/bi" class="nav-item" data-tooltip="BI Executivo">
                    <i class="bi bi-bar-chart-line"></i>
                    <span class="nav-label">BI Executivo</span>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Administrativo -->
            <?php if (Auth::pode('usuarios') || Auth::pode('empresa')): ?>
            <div class="nav-group">
                <div class="nav-group-label">Administrativo</div>

                <?php if (Auth::pode('usuarios')): ?>
                <a href="<?= APP_URL ?>/usuarios" class="nav-item" data-tooltip="Usuários">
                    <i class="bi bi-person-gear"></i>
                    <span class="nav-label">Usuários</span>
                </a>
                <a href="<?= APP_URL ?>/perfis" class="nav-item" data-tooltip="Perfis">
                    <i class="bi bi-shield-check"></i>
                    <span class="nav-label">Perfis</span>
                </a>
                <?php endif; ?>

                <?php if (Auth::pode('empresa')): ?>
                <a href="<?= APP_URL ?>/empresa" class="nav-item" data-tooltip="Empresa">
                    <i class="bi bi-building"></i>
                    <span class="nav-label">Empresa</span>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </nav>

        <!-- Footer da sidebar -->
        <div class="sidebar-footer">
            <div class="dropdown">
                <div class="sidebar-user" data-bs-toggle="dropdown">
                    <div class="avatar avatar-sm"><?= htmlspecialchars($iniciais) ?></div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($usuario['nome'] ?? '') ?></div>
                        <div class="user-role"><?= htmlspecialchars($usuario['perfil'] ?? '') ?></div>
                    </div>
                </div>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="<?= APP_URL ?>/perfil">
                            <i class="bi bi-person"></i> Meu Perfil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= APP_URL ?>/empresa">
                            <i class="bi bi-gear"></i> Configurações
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger-kroma" href="<?= APP_URL ?>/logout">
                            <i class="bi bi-box-arrow-right"></i> Sair
                        </a>
                    </li>
                </ul>
            </div>
        </div>

    </aside>
    <!-- ======================== FIM SIDEBAR ======================== -->

    <!-- ======================== CONTEÚDO PRINCIPAL ======================== -->
    <div class="main-content" id="mainContent">

        <!-- Topbar -->
        <header class="topbar">
            <button class="topbar-toggle" id="sidebarToggle" aria-label="Menu">
                <i class="bi bi-list"></i>
            </button>

            <!-- Breadcrumb -->
            <div class="topbar-breadcrumb">
                <a href="<?= APP_URL . $homeUrl ?>"><i class="bi bi-house"></i></a>
                <?php foreach ($breadcrumbs as $crumb): ?>
                    <i class="bi bi-chevron-right" style="font-size:10px"></i>
                    <?php if (!empty($crumb['url'])): ?>
                        <a href="<?= APP_URL . $crumb['url'] ?>"><?= htmlspecialchars($crumb['label']) ?></a>
                    <?php else: ?>
                        <span class="current"><?= htmlspecialchars($crumb['label']) ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if (!empty($breadcrumbs)): ?>
                    <i class="bi bi-chevron-right" style="font-size:10px"></i>
                <?php endif; ?>
                <span class="current"><?= htmlspecialchars($titulo) ?></span>
            </div>

            <!-- Ações da topbar -->
            <div class="topbar-actions">
                <!-- Busca rápida -->
                <a href="#" class="topbar-btn" data-bs-toggle="tooltip" title="Busca rápida">
                    <i class="bi bi-search"></i>
                </a>

                <!-- Notificações -->
                <?php if (Auth::pode('alertas')): ?>
                <a href="<?= APP_URL ?>/alertas" class="topbar-btn" id="notif-btn" data-bs-toggle="tooltip" title="Central de Alertas">
                    <i class="bi bi-bell"></i>
                    <?php if ($notifCount > 0): ?>
                    <span class="badge" id="notif-badge"><?= $notifCount ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>

                <!-- WhatsApp -->
                <?php if (Auth::pode('whatsapp')): ?>
                <a href="<?= APP_URL ?>/whatsapp" class="topbar-btn" data-bs-toggle="tooltip" title="WhatsApp">
                    <i class="bi bi-whatsapp"></i>
                </a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Conteúdo da página -->
        <main class="page-content">

            <!-- Flash Messages -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="flash-message flash-success">
                <span class="badge badge-success"><i class="bi bi-check-circle-fill"></i> Sucesso</span>
                <span class="flash-text"><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
            </div>
            <?php unset($_SESSION['flash_success']); endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="flash-message flash-error">
                <span class="badge badge-danger"><i class="bi bi-x-circle-fill"></i> Erro</span>
                <span class="flash-text"><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
            </div>
            <?php unset($_SESSION['flash_error']); endif; ?>

            <?php if (!empty($_SESSION['flash_warning'])): ?>
            <div class="flash-message flash-warning">
                <span class="badge badge-warning"><i class="bi bi-exclamation-triangle-fill"></i> Atenção</span>
                <span class="flash-text"><?= htmlspecialchars($_SESSION['flash_warning']) ?></span>
            </div>
            <?php unset($_SESSION['flash_warning']); endif; ?>

            <?php if (!empty($_SESSION['flash_info'])): ?>
            <div class="flash-message flash-info">
                <span class="badge badge-info"><i class="bi bi-info-circle-fill"></i> Informação</span>
                <span class="flash-text"><?= htmlspecialchars($_SESSION['flash_info']) ?></span>
            </div>
            <?php unset($_SESSION['flash_info']); endif; ?>

            <!-- Cabeçalho da página -->
            <?php if ($titulo): ?>
            <div class="page-header">
                <div>
                    <h1 class="page-title"><?= htmlspecialchars($titulo) ?></h1>
                    <?php if ($subtitulo): ?>
                    <p class="page-subtitle"><?= htmlspecialchars($subtitulo) ?></p>
                    <?php endif; ?>
                </div>
                <?php if (isset($headerActions)): ?>
                <div class="d-flex gap-2 flex-wrap">
                    <?= $headerActions ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- CONTEÚDO DINÂMICO -->
            <?= $content ?? '' ?>

        </main>

    </div>
    <!-- ======================== FIM CONTEÚDO PRINCIPAL ======================== -->

</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<!-- KROMA JS -->
<script src="<?= APP_URL ?>/public/assets/js/kroma.js"></script>

<?php if (isset($extraJs)): ?>
    <?= $extraJs ?>
<?php endif; ?>

</body>
</html>
