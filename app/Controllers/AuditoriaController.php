<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;

class AuditoriaController
{
    public function __construct()
    {
        AuthMiddleware::requer('auditoria');
    }

    public function index(): void
    {
        $filtros = [
            'usuario_id' => trim($_GET['usuario_id'] ?? ''),
            'tabela' => trim($_GET['tabela'] ?? ''),
            'acao' => trim($_GET['acao'] ?? ''),
            'data_inicio' => trim($_GET['data_inicio'] ?? ''),
            'data_fim' => trim($_GET['data_fim'] ?? ''),
        ];

        $logs = $this->logs($filtros);
        $usuarios = $this->query("SELECT id, nome, email FROM usuarios ORDER BY nome");
        $tabelas = $this->query("SELECT DISTINCT tabela FROM logs_acoes WHERE tabela IS NOT NULL AND tabela <> '' ORDER BY tabela");
        $acoes = $this->query("SELECT DISTINCT acao FROM logs_acoes WHERE acao IS NOT NULL AND acao <> '' ORDER BY acao");
        $resumo = $this->resumo();

        $titulo = 'Auditoria';
        $subtitulo = 'Rastreamento de ações críticas por usuário, área e período';
        $breadcrumbs = [['label' => 'Perfis', 'url' => '/perfis'], ['label' => 'Auditoria', 'url' => '']];
        $headerActions = '<a href="' . APP_URL . '/perfis" class="btn btn-secondary btn-sm"><i class="bi bi-shield-check"></i> Perfis</a>';

        ob_start();
        require APP_PATH . '/Views/auditoria/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    private function logs(array $filtros): array
    {
        $where = [];
        $params = [];

        if ($filtros['usuario_id'] !== '') {
            $where[] = 'la.usuario_id = ?';
            $params[] = (int)$filtros['usuario_id'];
        }
        if ($filtros['tabela'] !== '') {
            $where[] = 'la.tabela = ?';
            $params[] = $filtros['tabela'];
        }
        if ($filtros['acao'] !== '') {
            $where[] = 'la.acao = ?';
            $params[] = $filtros['acao'];
        }
        if ($filtros['data_inicio'] !== '') {
            $where[] = 'DATE(la.created_at) >= ?';
            $params[] = $filtros['data_inicio'];
        }
        if ($filtros['data_fim'] !== '') {
            $where[] = 'DATE(la.created_at) <= ?';
            $params[] = $filtros['data_fim'];
        }

        $sql = "SELECT la.*, u.nome AS usuario_nome, u.email AS usuario_email
                FROM logs_acoes la
                LEFT JOIN usuarios u ON u.id = la.usuario_id";
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY la.created_at DESC LIMIT 300';

        return $this->query($sql, $params);
    }

    private function resumo(): array
    {
        return [
            'total' => (int)$this->scalar("SELECT COUNT(*) FROM logs_acoes"),
            'hoje' => (int)$this->scalar("SELECT COUNT(*) FROM logs_acoes WHERE DATE(created_at) = CURDATE()"),
            'usuarios' => (int)$this->scalar("SELECT COUNT(DISTINCT usuario_id) FROM logs_acoes WHERE usuario_id IS NOT NULL"),
            'areas' => (int)$this->scalar("SELECT COUNT(DISTINCT tabela) FROM logs_acoes WHERE tabela IS NOT NULL AND tabela <> ''"),
        ];
    }

    private function scalar(string $sql, array $params = []): mixed
    {
        try {
            $stmt = db()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() ?: 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function query(string $sql, array $params = []): array
    {
        try {
            $stmt = db()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }
}
