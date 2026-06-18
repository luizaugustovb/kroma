<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class ComissaoController
{
    private array $statusLabels = [
        'prevista' => 'Prevista',
        'liberada' => 'Liberada',
        'paga' => 'Paga',
        'bloqueada' => 'Bloqueada',
        'cancelada' => 'Cancelada',
    ];

    public function __construct()
    {
        AuthMiddleware::requer('comissoes');
    }

    public function index(): void
    {
        $filtros = [
            'status' => $_GET['status'] ?? '',
            'vendedor_id' => $_GET['vendedor_id'] ?? '',
        ];

        $comissoes = $this->comissoes($filtros);
        $resumo = $this->resumo();
        $vendedores = $this->vendedores();
        $statusLabels = $this->statusLabels;
        $margemMinima = $this->margemMinima();

        $titulo = 'Comissões';
        $subtitulo = 'Controle de comissões por orçamento aprovado, margem e recebimento';
        $breadcrumbs = [['label' => 'Financeiro', 'url' => '/financeiro'], ['label' => 'Comissões', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/comissoes/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function sincronizar(): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/comissoes');
            exit;
        }

        if (!Auth::pode('comissoes.criar')) {
            $_SESSION['flash_error'] = 'Você não tem permissão para sincronizar comissões.';
            header('Location: ' . APP_URL . '/comissoes');
            exit;
        }

        $geradas = 0;
        $margemMinima = $this->margemMinima();

        try {
            $orcamentos = $this->query(
                "SELECT o.*
                 FROM orcamentos o
                 LEFT JOIN comissoes c ON c.orcamento_id = o.id
                 WHERE o.status = 'aprovado'
                   AND o.vendedor_id IS NOT NULL
                   AND o.total > 0
                   AND c.id IS NULL"
            );

            $stmt = db()->prepare(
                "INSERT INTO comissoes (orcamento_id, usuario_id, base_calculo, percentual, valor, status, observacoes, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
            );

            foreach ($orcamentos as $orcamento) {
                $base = (float)$orcamento['total'];
                $percentual = (float)$orcamento['comissao_percent'];
                $valor = round($base * ($percentual / 100), 2);
                $margemReal = $base > 0 ? (((float)$orcamento['lucro_previsto'] / $base) * 100) : 0;
                $bloqueada = $margemReal < $margemMinima;
                $status = $bloqueada ? 'bloqueada' : 'prevista';
                $obs = $bloqueada ? 'Comissão bloqueada: margem abaixo do mínimo configurado.' : 'Comissão gerada por sincronização.';
                $stmt->execute([$orcamento['id'], $orcamento['vendedor_id'], $base, $percentual, $valor, $status, $obs]);
                $geradas++;
            }

            $_SESSION['flash_success'] = $geradas > 0 ? $geradas . ' comissão(ões) sincronizada(s).' : 'Nenhuma comissão pendente para sincronizar.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao sincronizar comissões: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/comissoes');
        exit;
    }

    public function liberar(string $id): void
    {
        $this->alterarStatus($id, 'liberada');
    }

    public function pagar(string $id): void
    {
        $this->alterarStatus($id, 'paga');
    }

    public function bloquear(string $id): void
    {
        $this->alterarStatus($id, 'bloqueada');
    }

    public function cancelar(string $id): void
    {
        $this->alterarStatus($id, 'cancelada');
    }

    private function alterarStatus(string $id, string $status): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/comissoes');
            exit;
        }

        if (!Auth::pode('comissoes.editar')) {
            $_SESSION['flash_error'] = 'Você não tem permissão para alterar comissões.';
            header('Location: ' . APP_URL . '/comissoes');
            exit;
        }

        $comissao = $this->comissao($id);
        if (!$comissao) {
            $_SESSION['flash_error'] = 'Comissão não encontrada.';
            header('Location: ' . APP_URL . '/comissoes');
            exit;
        }

        if ($comissao['status'] === 'paga' && $status !== 'paga') {
            $_SESSION['flash_warning'] = 'Comissão paga não pode ser alterada.';
            header('Location: ' . APP_URL . '/comissoes');
            exit;
        }

        if ($status === 'liberada' && $comissao['status'] === 'bloqueada' && !Auth::temPerfil(['administrador', 'diretor', 'gerente'])) {
            $_SESSION['flash_warning'] = 'Comissão bloqueada por margem mínima exige aprovação gerencial.';
            header('Location: ' . APP_URL . '/comissoes');
            exit;
        }

        if ($status === 'paga' && $comissao['status'] !== 'liberada') {
            $_SESSION['flash_warning'] = 'Comissão precisa estar liberada antes do pagamento.';
            header('Location: ' . APP_URL . '/comissoes');
            exit;
        }

        $sets = ['status = ?', 'updated_at = NOW()'];
        $params = [$status];
        if ($status === 'liberada') {
            $sets[] = 'data_liberacao = COALESCE(data_liberacao, NOW())';
        }
        if ($status === 'paga') {
            $sets[] = 'data_pagamento = COALESCE(data_pagamento, NOW())';
        }
        if (in_array($status, ['bloqueada', 'cancelada'], true)) {
            $sets[] = 'observacoes = ?';
            $params[] = trim($_POST['observacoes'] ?? '') ?: ($status === 'bloqueada' ? 'Comissão bloqueada manualmente.' : 'Comissão cancelada.');
        }
        $params[] = (int)$id;

        try {
            db()->prepare('UPDATE comissoes SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);
            Auth::registrarAuditoria('comissoes', $status, (int)$id, $comissao, ['status' => $status]);
            $_SESSION['flash_success'] = 'Status da comissão atualizado.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao atualizar comissão: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/comissoes');
        exit;
    }

    private function comissoes(array $filtros): array
    {
        $where = [];
        $params = [];

        if ($filtros['status'] !== '') {
            $where[] = 'c.status = ?';
            $params[] = $filtros['status'];
        }

        if ($filtros['vendedor_id'] !== '') {
            $where[] = 'c.usuario_id = ?';
            $params[] = (int)$filtros['vendedor_id'];
        }

        $sql = "SELECT c.*, o.codigo AS orcamento_codigo, o.titulo AS orcamento_titulo,
                    o.status AS orcamento_status, o.total AS orcamento_total,
                    o.lucro_previsto, o.margem_percent, o.aprovado_at,
                    u.nome AS vendedor_nome, cli.nome AS cliente_nome,
                    COALESCE(fr.valor_total, 0) AS financeiro_total,
                    COALESCE(fr.valor_pago, 0) AS financeiro_pago
                FROM comissoes c
                JOIN orcamentos o ON o.id = c.orcamento_id
                LEFT JOIN usuarios u ON u.id = c.usuario_id
                LEFT JOIN clientes cli ON cli.id = o.cliente_id
                LEFT JOIN (
                    SELECT orcamento_id, SUM(valor) AS valor_total, SUM(valor_pago) AS valor_pago
                    FROM contas_receber
                    GROUP BY orcamento_id
                ) fr ON fr.orcamento_id = o.id";

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= " ORDER BY FIELD(c.status, 'bloqueada','prevista','liberada','paga','cancelada'), c.created_at DESC";
        return $this->queryPreparada($sql, $params);
    }

    private function resumo(): array
    {
        $base = [
            'prevista' => ['total' => 0, 'valor' => 0.0],
            'liberada' => ['total' => 0, 'valor' => 0.0],
            'paga' => ['total' => 0, 'valor' => 0.0],
            'bloqueada' => ['total' => 0, 'valor' => 0.0],
            'cancelada' => ['total' => 0, 'valor' => 0.0],
        ];

        foreach ($this->query("SELECT status, COUNT(*) AS total, COALESCE(SUM(valor), 0) AS valor FROM comissoes GROUP BY status") as $row) {
            $base[$row['status']] = ['total' => (int)$row['total'], 'valor' => (float)$row['valor']];
        }

        return $base;
    }

    private function vendedores(): array
    {
        return $this->query(
            "SELECT DISTINCT u.id, u.nome
             FROM usuarios u
             JOIN comissoes c ON c.usuario_id = u.id
             ORDER BY u.nome"
        );
    }

    private function comissao(string $id): ?array
    {
        try {
            $stmt = db()->prepare(
                "SELECT c.*, o.total AS orcamento_total, o.lucro_previsto, o.margem_percent
                 FROM comissoes c
                 JOIN orcamentos o ON o.id = c.orcamento_id
                 WHERE c.id = ?"
            );
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function margemMinima(): float
    {
        try {
            $stmt = db()->prepare("SELECT valor FROM configuracoes WHERE chave = 'margem_minima' LIMIT 1");
            $stmt->execute();
            return (float)($stmt->fetchColumn() ?: 30);
        } catch (\Exception $e) {
            return 30.0;
        }
    }

    private function query(string $sql): array
    {
        try {
            return db()->query($sql)->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function queryPreparada(string $sql, array $params = []): array
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
