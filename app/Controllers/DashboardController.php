<?php
/**
 * Controlador do Dashboard — KROMA PRINT ERP
 */

namespace App\Controllers;

use App\Services\Auth;
use App\Middleware\AuthMiddleware;

class DashboardController
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    /**
     * Dashboard principal
     */
    public function index(): void
    {
        $dados = $this->carregarDados();

        $titulo    = 'Dashboard';
        $subtitulo = 'Visão geral da operação em tempo real';

        ob_start();
        require APP_PATH . '/Views/dashboard/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    /**
     * Dados via AJAX
     */
    public function dados(): void
    {
        AuthMiddleware::handle();
        header('Content-Type: application/json');
        echo json_encode($this->carregarDados());
    }

    /**
     * Carrega todos os dados do dashboard
     */
    private function carregarDados(): array
    {
        $dados = [
            'leads_novos'       => 0,
            'leads_total'       => 0,
            'clientes_total'    => 0,
            'usuarios_ativos'   => 0,
            'leads_por_estagio' => [],
            'leads_por_origem'  => [],
            'ultimos_leads'     => [],
            'ultimos_clientes'  => [],
        ];

        try {
            $pdo = db();

            // Total de leads
            $dados['leads_total'] = (int)$pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();

            // Leads novos (hoje)
            $dados['leads_novos'] = (int)$pdo->query(
                "SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()"
            )->fetchColumn();

            // Total de clientes
            $dados['clientes_total'] = (int)$pdo->query(
                "SELECT COUNT(*) FROM clientes WHERE status = 'ativo'"
            )->fetchColumn();

            // Usuários ativos
            $dados['usuarios_ativos'] = (int)$pdo->query(
                "SELECT COUNT(*) FROM usuarios WHERE ativo = 1"
            )->fetchColumn();

            // Leads por estágio
            $stmt = $pdo->query(
                "SELECT estagio, COUNT(*) AS total FROM leads GROUP BY estagio ORDER BY total DESC"
            );
            $dados['leads_por_estagio'] = $stmt->fetchAll();

            // Leads por origem
            $stmt = $pdo->query(
                "SELECT origem, COUNT(*) AS total FROM leads GROUP BY origem ORDER BY total DESC LIMIT 6"
            );
            $dados['leads_por_origem'] = $stmt->fetchAll();

            // Últimos 5 leads
            $stmt = $pdo->query(
                "SELECT l.*, u.nome AS vendedor_nome FROM leads l 
                 LEFT JOIN usuarios u ON u.id = l.vendedor_id
                 ORDER BY l.created_at DESC LIMIT 5"
            );
            $dados['ultimos_leads'] = $stmt->fetchAll();

            // Últimos 5 clientes
            $stmt = $pdo->query(
                "SELECT * FROM clientes ORDER BY created_at DESC LIMIT 5"
            );
            $dados['ultimos_clientes'] = $stmt->fetchAll();

        } catch (\Exception $e) {
            // Retorna dados vazios se banco não tiver tabelas ainda
        }

        return $dados;
    }
}
