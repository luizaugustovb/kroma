<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;

class BiController
{
    public function __construct()
    {
        AuthMiddleware::requer('bi');
    }

    public function index(): void
    {
        $dados = $this->carregarDados();

        $titulo = 'BI Executivo';
        $subtitulo = 'Indicadores consolidados de vendas, produção, financeiro, estoque e RH';
        $breadcrumbs = [['label' => 'BI Executivo', 'url' => '']];
        $headerActions = '<a href="' . APP_URL . '/financeiro" class="btn btn-secondary btn-sm"><i class="bi bi-cash-stack"></i> Financeiro</a> <a href="' . APP_URL . '/producao" class="btn btn-primary btn-sm"><i class="bi bi-gear"></i> Produção</a>';

        ob_start();
        require APP_PATH . '/Views/bi/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    private function carregarDados(): array
    {
        return [
            'kpis' => $this->kpis(),
            'orcamentos_status' => $this->orcamentosPorStatus(),
            'orcamentos_meses' => $this->orcamentosPorMes(),
            'caixa_meses' => $this->caixaPorMes(),
            'orcamentos_recentes' => $this->orcamentosRecentes(),
            'os_risco' => $this->ordensEmRisco(),
            'estoque_critico' => $this->estoqueCritico(),
            'financeiro_vencido' => $this->financeiroVencido(),
            'compras_pendentes' => $this->comprasPendentes(),
            'rh_resumo' => $this->rhResumo(),
        ];
    }

    private function kpis(): array
    {
        $receberAberto = (float)$this->scalar(
            "SELECT COALESCE(SUM(GREATEST(valor - valor_pago, 0)), 0)
             FROM contas_receber
             WHERE status IN ('aberto','parcial')"
        );
        $pagarAberto = (float)$this->scalar(
            "SELECT COALESCE(SUM(GREATEST(valor - valor_pago, 0)), 0)
             FROM contas_pagar
             WHERE status IN ('aberto','parcial')"
        );
        $receitaMes = (float)$this->scalar(
            "SELECT COALESCE(SUM(valor_pago), 0)
             FROM contas_receber
             WHERE status IN ('pago','parcial') AND data_pagamento >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
        );
        $saidaMes = (float)$this->scalar(
            "SELECT COALESCE(SUM(valor_pago), 0)
             FROM contas_pagar
             WHERE status IN ('pago','parcial') AND data_pagamento >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
        );
        $orcamentosAprovadosMes = (float)$this->scalar(
            "SELECT COALESCE(SUM(total), 0)
             FROM orcamentos
             WHERE status = 'aprovado' AND COALESCE(aprovado_at, created_at) >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
        );
        $margemMedia = (float)$this->scalar(
            "SELECT COALESCE(AVG(CASE WHEN total > 0 THEN (lucro_previsto / total) * 100 END), 0)
             FROM orcamentos
             WHERE status IN ('enviado','aprovado')"
        );

        return [
            'receber_aberto' => $receberAberto,
            'pagar_aberto' => $pagarAberto,
            'saldo_previsto' => $receberAberto - $pagarAberto,
            'receita_mes' => $receitaMes,
            'saida_mes' => $saidaMes,
            'saldo_mes' => $receitaMes - $saidaMes,
            'orcamentos_aprovados_mes' => $orcamentosAprovadosMes,
            'margem_media' => $margemMedia,
            'os_producao' => (int)$this->scalar("SELECT COUNT(*) FROM ordem_servicos WHERE status = 'em_producao'"),
            'os_atrasadas' => (int)$this->scalar(
                "SELECT COUNT(*)
                 FROM ordem_servicos
                 WHERE status NOT IN ('finalizada','cancelada') AND data_prometida IS NOT NULL AND data_prometida < CURDATE()"
            ),
            'estoque_critico' => (int)$this->scalar(
                "SELECT COUNT(*)
                 FROM materiais
                 WHERE status = 'ativo' AND (estoque_atual - estoque_reservado) <= estoque_minimo"
            ),
            'compras_pendentes' => (int)$this->scalar("SELECT COUNT(*) FROM compras WHERE status IN ('rascunho','solicitada','aprovada')"),
        ];
    }

    private function orcamentosPorStatus(): array
    {
        return $this->query(
            "SELECT status, COUNT(*) AS total, COALESCE(SUM(total), 0) AS valor
             FROM orcamentos
             GROUP BY status
             ORDER BY FIELD(status,'rascunho','em_calculo','enviado','aprovado','recusado','cancelado','expirado')"
        );
    }

    private function orcamentosPorMes(): array
    {
        return $this->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS mes,
                    COALESCE(SUM(total), 0) AS venda,
                    COALESCE(SUM(lucro_previsto), 0) AS lucro,
                    COUNT(*) AS quantidade
             FROM orcamentos
             WHERE created_at >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 5 MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY mes"
        );
    }

    private function caixaPorMes(): array
    {
        return $this->query(
            "SELECT DATE_FORMAT(data_movimento, '%Y-%m') AS mes,
                    COALESCE(SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END), 0) AS entradas,
                    COALESCE(SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END), 0) AS saidas
             FROM caixa_movimentacoes
             WHERE data_movimento >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 5 MONTH)
             GROUP BY DATE_FORMAT(data_movimento, '%Y-%m')
             ORDER BY mes"
        );
    }

    private function orcamentosRecentes(): array
    {
        return $this->query(
            "SELECT o.id, o.codigo, o.titulo, o.status, o.total, o.lucro_previsto, o.margem_percent,
                    o.created_at, c.nome AS cliente_nome, u.nome AS vendedor_nome
             FROM orcamentos o
             LEFT JOIN clientes c ON c.id = o.cliente_id
             LEFT JOIN usuarios u ON u.id = o.vendedor_id
             ORDER BY o.created_at DESC
             LIMIT 8"
        );
    }

    private function ordensEmRisco(): array
    {
        return $this->query(
            "SELECT os.id, os.codigo, os.titulo, os.status, os.prioridade, os.data_prometida,
                    c.nome AS cliente_nome
             FROM ordem_servicos os
             LEFT JOIN clientes c ON c.id = os.cliente_id
             WHERE os.status NOT IN ('finalizada','cancelada')
             ORDER BY (os.data_prometida IS NOT NULL AND os.data_prometida < CURDATE()) DESC,
                      FIELD(os.prioridade,'urgente','alta','media','baixa'),
                      os.data_prometida IS NULL,
                      os.data_prometida
             LIMIT 8"
        );
    }

    private function estoqueCritico(): array
    {
        return $this->query(
            "SELECT id, codigo, nome, categoria, unidade, estoque_atual, estoque_reservado,
                    estoque_minimo, custo_atual,
                    (estoque_atual - estoque_reservado) AS disponivel
             FROM materiais
             WHERE status = 'ativo' AND (estoque_atual - estoque_reservado) <= estoque_minimo
             ORDER BY (estoque_atual - estoque_reservado), nome
             LIMIT 8"
        );
    }

    private function financeiroVencido(): array
    {
        $receber = $this->query(
            "SELECT 'receber' AS tipo, id, codigo, descricao, valor, valor_pago, vencimento, status
             FROM contas_receber
             WHERE status IN ('aberto','parcial') AND vencimento IS NOT NULL AND vencimento < CURDATE()
             ORDER BY vencimento
             LIMIT 6"
        );
        $pagar = $this->query(
            "SELECT 'pagar' AS tipo, id, codigo, descricao, valor, valor_pago, vencimento, status
             FROM contas_pagar
             WHERE status IN ('aberto','parcial') AND vencimento IS NOT NULL AND vencimento < CURDATE()
             ORDER BY vencimento
             LIMIT 6"
        );
        return array_slice(array_merge($receber, $pagar), 0, 10);
    }

    private function comprasPendentes(): array
    {
        return $this->query(
            "SELECT c.id, c.codigo, c.titulo, c.status, c.total, c.previsao_entrega,
                    f.nome AS fornecedor_nome
             FROM compras c
             LEFT JOIN fornecedores f ON f.id = c.fornecedor_id
             WHERE c.status IN ('rascunho','solicitada','aprovada')
             ORDER BY c.previsao_entrega IS NULL, c.previsao_entrega, c.created_at DESC
             LIMIT 8"
        );
    }

    private function rhResumo(): array
    {
        return [
            'colaboradores_ativos' => (int)$this->scalar("SELECT COUNT(*) FROM colaboradores WHERE status IN ('ativo','ferias')"),
            'folha_mensal' => (float)$this->scalar("SELECT COALESCE(SUM(salario), 0) FROM colaboradores WHERE status IN ('ativo','ferias')"),
            'custo_hora_medio' => (float)$this->scalar("SELECT COALESCE(AVG(NULLIF(custo_hora, 0)), 0) FROM colaboradores WHERE status IN ('ativo','ferias')"),
            'equipamentos_manutencao' => (int)$this->scalar("SELECT COUNT(*) FROM equipamentos WHERE status = 'manutencao'"),
            'veiculos_manutencao' => (int)$this->scalar("SELECT COUNT(*) FROM veiculos WHERE status = 'manutencao'"),
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
