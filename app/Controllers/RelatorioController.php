<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;

class RelatorioController
{
    private array $tiposExportacao = [
        'comercial' => 'Comercial',
        'financeiro' => 'Financeiro',
        'producao' => 'Produção',
        'estoque' => 'Estoque e compras',
        'led' => 'Painéis de LED',
    ];

    public function __construct()
    {
        AuthMiddleware::requer('relatorios');
    }

    public function index(): void
    {
        $filtros = $this->filtros();
        $dados = [
            'resumo' => $this->resumo($filtros),
            'comercial' => $this->comercial($filtros),
            'financeiro' => $this->financeiro($filtros),
            'producao' => $this->producao($filtros),
            'estoque' => $this->estoqueCompras($filtros),
            'led' => $this->led($filtros),
        ];
        $contexto = $this->contexto();
        $tiposExportacao = $this->tiposExportacao;

        $titulo = 'Relatórios Gerenciais';
        $subtitulo = 'Visão consolidada comercial, financeira, operacional, estoque e LED';
        $breadcrumbs = [['label' => 'Inteligência', 'url' => '/bi'], ['label' => 'Relatórios', 'url' => '']];
        $headerActions = '<a href="' . APP_URL . '/bi" class="btn btn-secondary btn-sm"><i class="bi bi-bar-chart-line"></i> BI Executivo</a>';

        ob_start();
        require APP_PATH . '/Views/relatorios/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function exportar(): void
    {
        $filtros = $this->filtros();
        $tipo = $_GET['tipo'] ?? 'comercial';
        if (!array_key_exists($tipo, $this->tiposExportacao)) {
            $tipo = 'comercial';
        }

        [$cabecalho, $linhas] = $this->csvDados($tipo, $filtros);
        $arquivo = 'relatorio_' . $tipo . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $arquivo . '"');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $cabecalho, ';');
        foreach ($linhas as $linha) {
            fputcsv($out, $linha, ';');
        }
        fclose($out);
        exit;
    }

    private function filtros(): array
    {
        $inicio = $_GET['inicio'] ?? date('Y-m-01');
        $fim = $_GET['fim'] ?? date('Y-m-t');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $inicio)) {
            $inicio = date('Y-m-01');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fim)) {
            $fim = date('Y-m-t');
        }
        if (strtotime($fim) < strtotime($inicio)) {
            $fim = $inicio;
        }

        return [
            'inicio' => $inicio,
            'fim' => $fim,
            'cliente_id' => $_GET['cliente_id'] ?? '',
            'vendedor_id' => $_GET['vendedor_id'] ?? '',
            'status' => $_GET['status'] ?? '',
        ];
    }

    private function resumo(array $filtros): array
    {
        $inicioData = $filtros['inicio'];
        $fimData = $filtros['fim'];
        $inicio = $inicioData . ' 00:00:00';
        $fim = $fimData . ' 23:59:59';

        return [
            'orcamentos_valor' => (float)$this->scalar(
                "SELECT COALESCE(SUM(total), 0) FROM orcamentos
                 WHERE status = 'aprovado' AND COALESCE(aprovado_at, created_at) BETWEEN ? AND ?",
                [$inicio, $fim]
            ),
            'orcamentos_qtd' => (int)$this->scalar(
                "SELECT COUNT(*) FROM orcamentos
                 WHERE status IN ('enviado','aprovado') AND created_at BETWEEN ? AND ?",
                [$inicio, $fim]
            ),
            'recebido' => (float)$this->scalar(
                "SELECT COALESCE(SUM(valor_pago), 0) FROM contas_receber
                 WHERE status IN ('pago','parcial') AND data_pagamento BETWEEN ? AND ?",
                [$inicioData, $fimData]
            ),
            'a_pagar' => (float)$this->scalar(
                "SELECT COALESCE(SUM(GREATEST(valor - valor_pago, 0)), 0) FROM contas_pagar
                 WHERE status IN ('aberto','parcial') AND vencimento BETWEEN ? AND ?",
                [$inicioData, $fimData]
            ),
            'os_finalizadas' => (int)$this->scalar(
                "SELECT COUNT(*) FROM ordem_servicos
                 WHERE status = 'finalizada' AND COALESCE(data_finalizacao, updated_at) BETWEEN ? AND ?",
                [$inicio, $fim]
            ),
            'led_faturamento' => (float)$this->scalar(
                "SELECT COALESCE(SUM(valor_total), 0) FROM led_locacoes
                 WHERE status IN ('reservado','instalado','retirado') AND data_inicio <= ? AND data_fim >= ?",
                [$fim, $inicio]
            ),
        ];
    }

    private function comercial(array $filtros): array
    {
        $where = ['o.created_at BETWEEN ? AND ?'];
        $params = [$filtros['inicio'] . ' 00:00:00', $filtros['fim'] . ' 23:59:59'];
        $this->aplicarFiltrosComerciais($where, $params, $filtros, 'o');

        return $this->query(
            "SELECT o.id, o.codigo, o.titulo, o.status, o.total, o.lucro_previsto, o.margem_percent, o.created_at,
                    c.nome AS cliente_nome, u.nome AS vendedor_nome
             FROM orcamentos o
             LEFT JOIN clientes c ON c.id = o.cliente_id
             LEFT JOIN usuarios u ON u.id = o.vendedor_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY o.created_at DESC
             LIMIT 120",
            $params
        );
    }

    private function financeiro(array $filtros): array
    {
        $inicio = $filtros['inicio'];
        $fim = $filtros['fim'];
        $whereReceber = ['(cr.vencimento BETWEEN ? AND ? OR cr.data_pagamento BETWEEN ? AND ?)'];
        $paramsReceber = [$inicio, $fim, $inicio, $fim];
        $wherePagar = ['(cp.vencimento BETWEEN ? AND ? OR cp.data_pagamento BETWEEN ? AND ?)'];
        $paramsPagar = [$inicio, $fim, $inicio, $fim];

        if ($filtros['status'] !== '') {
            $whereReceber[] = 'cr.status = ?';
            $paramsReceber[] = $filtros['status'];
            $wherePagar[] = 'cp.status = ?';
            $paramsPagar[] = $filtros['status'];
        }

        $receber = $this->query(
            "SELECT 'receber' AS tipo, cr.codigo, cr.descricao, cr.status, cr.valor, cr.valor_pago,
                    cr.vencimento, cr.data_pagamento, c.nome AS pessoa
             FROM contas_receber cr
             LEFT JOIN clientes c ON c.id = cr.cliente_id
             WHERE " . implode(' AND ', $whereReceber) . "
             ORDER BY cr.vencimento, cr.codigo
             LIMIT 120",
            $paramsReceber
        );

        $pagar = $this->query(
            "SELECT 'pagar' AS tipo, cp.codigo, cp.descricao, cp.status, cp.valor, cp.valor_pago,
                    cp.vencimento, cp.data_pagamento, cp.fornecedor AS pessoa
             FROM contas_pagar cp
             WHERE " . implode(' AND ', $wherePagar) . "
             ORDER BY cp.vencimento, cp.codigo
             LIMIT 120",
            $paramsPagar
        );

        return array_slice(array_merge($receber, $pagar), 0, 160);
    }

    private function producao(array $filtros): array
    {
        $where = ['COALESCE(os.data_finalizacao, os.data_entrada, os.created_at) BETWEEN ? AND ?'];
        $params = [$filtros['inicio'] . ' 00:00:00', $filtros['fim'] . ' 23:59:59'];

        if ($filtros['cliente_id'] !== '') {
            $where[] = 'os.cliente_id = ?';
            $params[] = (int)$filtros['cliente_id'];
        }
        if ($filtros['status'] !== '') {
            $where[] = 'os.status = ?';
            $params[] = $filtros['status'];
        }

        return $this->query(
            "SELECT os.id, os.codigo, os.titulo, os.status, os.prioridade, os.data_entrada,
                    os.data_prometida, os.data_finalizacao, c.nome AS cliente_nome, u.nome AS responsavel_nome,
                    (SELECT COUNT(*) FROM ordem_servico_etapas e WHERE e.ordem_servico_id = os.id) AS etapas_total,
                    (SELECT COUNT(*) FROM ordem_servico_etapas e WHERE e.ordem_servico_id = os.id AND e.status = 'concluida') AS etapas_concluidas
             FROM ordem_servicos os
             LEFT JOIN clientes c ON c.id = os.cliente_id
             LEFT JOIN usuarios u ON u.id = os.responsavel_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY os.data_prometida IS NULL, os.data_prometida, os.created_at DESC
             LIMIT 120",
            $params
        );
    }

    private function estoqueCompras(array $filtros): array
    {
        $whereCompras = ['COALESCE(c.data_solicitacao, c.created_at) BETWEEN ? AND ?'];
        $paramsCompras = [$filtros['inicio'] . ' 00:00:00', $filtros['fim'] . ' 23:59:59'];

        if ($filtros['status'] !== '') {
            $whereCompras[] = 'c.status = ?';
            $paramsCompras[] = $filtros['status'];
        }

        return [
            'materiais_criticos' => $this->query(
                "SELECT codigo, nome, categoria, unidade, estoque_atual, estoque_reservado, estoque_minimo,
                        (estoque_atual - estoque_reservado) AS disponivel, custo_atual, status
                 FROM materiais
                 WHERE status = 'ativo' AND (estoque_atual - estoque_reservado) <= estoque_minimo
                 ORDER BY disponivel, nome
                 LIMIT 80"
            ),
            'compras' => $this->query(
                "SELECT c.codigo, c.titulo, c.status, c.total, c.data_solicitacao, c.previsao_entrega,
                        f.nome AS fornecedor_nome
                 FROM compras c
                 LEFT JOIN fornecedores f ON f.id = c.fornecedor_id
                 WHERE " . implode(' AND ', $whereCompras) . "
                 ORDER BY c.previsao_entrega IS NULL, c.previsao_entrega, c.created_at DESC
                 LIMIT 100",
                $paramsCompras
            ),
        ];
    }

    private function led(array $filtros): array
    {
        $where = ['l.data_inicio <= ?', 'l.data_fim >= ?'];
        $params = [$filtros['fim'] . ' 23:59:59', $filtros['inicio'] . ' 00:00:00'];

        if ($filtros['cliente_id'] !== '') {
            $where[] = 'l.cliente_id = ?';
            $params[] = (int)$filtros['cliente_id'];
        }
        if ($filtros['status'] !== '') {
            $where[] = 'l.status = ?';
            $params[] = $filtros['status'];
        }

        return $this->query(
            "SELECT l.codigo, l.titulo, l.status, l.valor_total, l.data_inicio, l.data_fim,
                    l.local_instalacao, p.codigo AS painel_codigo, p.nome AS painel_nome,
                    c.nome AS cliente_nome, u.nome AS responsavel_nome
             FROM led_locacoes l
             INNER JOIN led_paineis p ON p.id = l.painel_id
             LEFT JOIN clientes c ON c.id = l.cliente_id
             LEFT JOIN usuarios u ON u.id = l.responsavel_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY l.data_inicio DESC
             LIMIT 120",
            $params
        );
    }

    private function aplicarFiltrosComerciais(array &$where, array &$params, array $filtros, string $alias): void
    {
        if ($filtros['cliente_id'] !== '') {
            $where[] = $alias . '.cliente_id = ?';
            $params[] = (int)$filtros['cliente_id'];
        }
        if ($filtros['vendedor_id'] !== '') {
            $where[] = $alias . '.vendedor_id = ?';
            $params[] = (int)$filtros['vendedor_id'];
        }
        if ($filtros['status'] !== '') {
            $where[] = $alias . '.status = ?';
            $params[] = $filtros['status'];
        }
    }

    private function contexto(): array
    {
        return [
            'clientes' => $this->query("SELECT id, nome FROM clientes WHERE status = 'ativo' ORDER BY nome LIMIT 500"),
            'vendedores' => $this->query("SELECT id, nome FROM usuarios WHERE ativo = 1 ORDER BY nome"),
        ];
    }

    private function csvDados(string $tipo, array $filtros): array
    {
        return match ($tipo) {
            'financeiro' => $this->csvFinanceiro($filtros),
            'producao' => $this->csvProducao($filtros),
            'estoque' => $this->csvEstoque($filtros),
            'led' => $this->csvLed($filtros),
            default => $this->csvComercial($filtros),
        };
    }

    private function csvComercial(array $filtros): array
    {
        $linhas = array_map(fn($r) => [
            $r['codigo'], $r['titulo'], $r['cliente_nome'], $r['vendedor_nome'], $r['status'],
            $r['total'], $r['lucro_previsto'], $r['margem_percent'], $r['created_at'],
        ], $this->comercial($filtros));
        return [['Código', 'Título', 'Cliente', 'Vendedor', 'Status', 'Valor', 'Lucro', 'Margem %', 'Criado em'], $linhas];
    }

    private function csvFinanceiro(array $filtros): array
    {
        $linhas = array_map(fn($r) => [
            $r['tipo'], $r['codigo'], $r['pessoa'], $r['descricao'], $r['status'],
            $r['valor'], $r['valor_pago'], $r['vencimento'], $r['data_pagamento'],
        ], $this->financeiro($filtros));
        return [['Tipo', 'Código', 'Cliente/Fornecedor', 'Descrição', 'Status', 'Valor', 'Valor pago', 'Vencimento', 'Pagamento'], $linhas];
    }

    private function csvProducao(array $filtros): array
    {
        $linhas = array_map(fn($r) => [
            $r['codigo'], $r['titulo'], $r['cliente_nome'], $r['responsavel_nome'], $r['status'],
            $r['prioridade'], $r['data_entrada'], $r['data_prometida'], $r['data_finalizacao'],
            $r['etapas_concluidas'], $r['etapas_total'],
        ], $this->producao($filtros));
        return [['OS', 'Título', 'Cliente', 'Responsável', 'Status', 'Prioridade', 'Entrada', 'Prometida', 'Finalização', 'Etapas concluídas', 'Etapas total'], $linhas];
    }

    private function csvEstoque(array $filtros): array
    {
        $dados = $this->estoqueCompras($filtros);
        $linhas = [];
        foreach ($dados['materiais_criticos'] as $r) {
            $linhas[] = ['Material crítico', $r['codigo'], $r['nome'], $r['categoria'], $r['status'], $r['disponivel'], $r['estoque_minimo'], $r['custo_atual'], ''];
        }
        foreach ($dados['compras'] as $r) {
            $linhas[] = ['Compra', $r['codigo'], $r['titulo'], $r['fornecedor_nome'], $r['status'], $r['total'], '', '', $r['previsao_entrega']];
        }
        return [['Tipo', 'Código', 'Nome/Título', 'Categoria/Fornecedor', 'Status', 'Valor/Disponível', 'Mínimo', 'Custo', 'Previsão'], $linhas];
    }

    private function csvLed(array $filtros): array
    {
        $linhas = array_map(fn($r) => [
            $r['codigo'], $r['titulo'], $r['cliente_nome'], $r['painel_codigo'], $r['painel_nome'],
            $r['status'], $r['valor_total'], $r['data_inicio'], $r['data_fim'], $r['local_instalacao'],
        ], $this->led($filtros));
        return [['Código', 'Título', 'Cliente', 'Painel código', 'Painel', 'Status', 'Valor', 'Início', 'Fim', 'Local'], $linhas];
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
