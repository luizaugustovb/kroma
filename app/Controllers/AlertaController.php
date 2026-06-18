<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\AlertasService;

class AlertaController
{
    public function __construct()
    {
        AuthMiddleware::requer('alertas');
    }

    public function index(): void
    {
        $service = new AlertasService();
        $alertas = $service->todos();
        $resumo = $service->resumo();

        $titulo = 'Central de Alertas';
        $subtitulo = 'Pendências críticas e prazos dos módulos operacionais';
        $breadcrumbs = [['label' => 'Alertas', 'url' => '']];
        $headerActions = '<a href="' . APP_URL . '/dashboard" class="btn btn-secondary btn-sm"><i class="bi bi-speedometer2"></i> Dashboard</a>';

        ob_start();
        require APP_PATH . '/Views/alertas/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }
}
