<?php

namespace App\Services;

class AlertasService
{
    public function todos(): array
    {
        $alertas = array_merge(
            $this->chamados(),
            $this->producao(),
            $this->compras(),
            $this->financeiro(),
            $this->estoque(),
            $this->qualidade()
        );

        usort($alertas, function (array $a, array $b) {
            $peso = ['danger' => 1, 'warning' => 2, 'info' => 3, 'success' => 4, 'secondary' => 5];
            $pa = $peso[$a['severidade']] ?? 9;
            $pb = $peso[$b['severidade']] ?? 9;
            if ($pa !== $pb) {
                return $pa <=> $pb;
            }
            return strcmp((string)($a['data_referencia'] ?? ''), (string)($b['data_referencia'] ?? ''));
        });

        return $alertas;
    }

    public function total(): int
    {
        return count($this->todos());
    }

    public function resumo(): array
    {
        $alertas = $this->todos();
        return [
            'total' => count($alertas),
            'criticos' => count(array_filter($alertas, fn($a) => $a['severidade'] === 'danger')),
            'atencao' => count(array_filter($alertas, fn($a) => $a['severidade'] === 'warning')),
            'informativos' => count(array_filter($alertas, fn($a) => $a['severidade'] === 'info')),
            'por_modulo' => $this->porModulo($alertas),
        ];
    }

    private function chamados(): array
    {
        $rows = $this->query(
            "SELECT id, codigo, titulo, setor, prioridade, status, prazo
             FROM chamados
             WHERE status NOT IN ('concluido','cancelado')
               AND prazo IS NOT NULL
               AND prazo <= DATE_ADD(NOW(), INTERVAL 1 DAY)
             ORDER BY prazo
             LIMIT 30"
        );

        return array_map(function (array $row) {
            $atrasado = strtotime($row['prazo']) < time();
            return [
                'modulo' => 'Chamados',
                'tipo' => $atrasado ? 'Chamado atrasado' : 'Chamado vence em 24h',
                'titulo' => $row['codigo'] . ' - ' . $row['titulo'],
                'descricao' => trim(($row['setor'] ?: 'Sem setor') . ' / Prioridade ' . ucfirst($row['prioridade'])),
                'severidade' => $atrasado ? 'danger' : 'warning',
                'url' => APP_URL . '/chamados/' . $row['id'],
                'data_referencia' => $row['prazo'],
                'badge' => $atrasado ? 'Atrasado' : 'Próximo prazo',
            ];
        }, $rows);
    }

    private function producao(): array
    {
        $rows = $this->query(
            "SELECT os.id, os.codigo, os.titulo, os.prioridade, os.status, os.data_prometida, c.nome AS cliente_nome
             FROM ordem_servicos os
             LEFT JOIN clientes c ON c.id = os.cliente_id
             WHERE os.status NOT IN ('finalizada','cancelada')
               AND os.data_prometida IS NOT NULL
               AND os.data_prometida <= DATE_ADD(CURDATE(), INTERVAL 1 DAY)
             ORDER BY os.data_prometida
             LIMIT 30"
        );

        return array_map(function (array $row) {
            $atrasado = $row['data_prometida'] < date('Y-m-d');
            return [
                'modulo' => 'Produção',
                'tipo' => $atrasado ? 'OS atrasada' : 'OS vence em 24h',
                'titulo' => $row['codigo'] . ' - ' . $row['titulo'],
                'descricao' => trim(($row['cliente_nome'] ?: 'Sem cliente') . ' / Prioridade ' . ucfirst($row['prioridade'])),
                'severidade' => $atrasado ? 'danger' : 'warning',
                'url' => APP_URL . '/producao/' . $row['id'],
                'data_referencia' => $row['data_prometida'],
                'badge' => $atrasado ? 'Atrasada' : 'Próximo prazo',
            ];
        }, $rows);
    }

    private function compras(): array
    {
        $rows = $this->query(
            "SELECT c.id, c.codigo, c.titulo, c.status, c.previsao_entrega, f.nome AS fornecedor_nome
             FROM compras c
             LEFT JOIN fornecedores f ON f.id = c.fornecedor_id
             WHERE c.status IN ('rascunho','solicitada','aprovada')
               AND c.previsao_entrega IS NOT NULL
               AND c.previsao_entrega <= DATE_ADD(CURDATE(), INTERVAL 1 DAY)
             ORDER BY c.previsao_entrega
             LIMIT 30"
        );

        return array_map(function (array $row) {
            $atrasada = $row['previsao_entrega'] < date('Y-m-d');
            return [
                'modulo' => 'Compras',
                'tipo' => $atrasada ? 'Compra atrasada' : 'Compra vence em 24h',
                'titulo' => $row['codigo'] . ' - ' . $row['titulo'],
                'descricao' => trim(($row['fornecedor_nome'] ?: 'Sem fornecedor') . ' / Status ' . ucfirst($row['status'])),
                'severidade' => $atrasada ? 'danger' : 'warning',
                'url' => APP_URL . '/compras/' . $row['id'],
                'data_referencia' => $row['previsao_entrega'],
                'badge' => $atrasada ? 'Atrasada' : 'Próximo prazo',
            ];
        }, $rows);
    }

    private function financeiro(): array
    {
        $receber = $this->query(
            "SELECT 'Receber' AS direcao, id, codigo, descricao, valor, valor_pago, vencimento, status
             FROM contas_receber
             WHERE status IN ('aberto','parcial')
               AND vencimento IS NOT NULL
               AND vencimento <= CURDATE()
             ORDER BY vencimento
             LIMIT 25"
        );
        $pagar = $this->query(
            "SELECT 'Pagar' AS direcao, id, codigo, descricao, valor, valor_pago, vencimento, status
             FROM contas_pagar
             WHERE status IN ('aberto','parcial')
               AND vencimento IS NOT NULL
               AND vencimento <= CURDATE()
             ORDER BY vencimento
             LIMIT 25"
        );

        return array_map(function (array $row) {
            $vencido = $row['vencimento'] < date('Y-m-d');
            $saldo = max(0, (float)$row['valor'] - (float)$row['valor_pago']);
            $rota = $row['direcao'] === 'Receber' ? '/financeiro/receber/' . $row['id'] : '/financeiro';
            return [
                'modulo' => 'Financeiro',
                'tipo' => $vencido ? 'Conta vencida' : 'Conta vence hoje',
                'titulo' => $row['codigo'] . ' - ' . $row['descricao'],
                'descricao' => $row['direcao'] . ' / Saldo R$ ' . number_format($saldo, 2, ',', '.'),
                'severidade' => $vencido ? 'danger' : 'warning',
                'url' => APP_URL . $rota,
                'data_referencia' => $row['vencimento'],
                'badge' => $vencido ? 'Vencida' : 'Vence hoje',
            ];
        }, array_merge($receber, $pagar));
    }

    private function estoque(): array
    {
        $rows = $this->query(
            "SELECT id, codigo, nome, categoria, unidade, estoque_minimo,
                    (estoque_atual - estoque_reservado) AS disponivel
             FROM materiais
             WHERE status = 'ativo'
               AND (estoque_atual - estoque_reservado) <= estoque_minimo
             ORDER BY (estoque_atual - estoque_reservado), nome
             LIMIT 40"
        );

        return array_map(function (array $row) {
            $critico = (float)$row['disponivel'] <= 0;
            return [
                'modulo' => 'Estoque',
                'tipo' => $critico ? 'Estoque crítico' : 'Estoque baixo',
                'titulo' => trim(($row['codigo'] ? $row['codigo'] . ' - ' : '') . $row['nome']),
                'descricao' => 'Disponível ' . number_format((float)$row['disponivel'], 3, ',', '.') . ' ' . $row['unidade'] . ' / Mínimo ' . number_format((float)$row['estoque_minimo'], 3, ',', '.'),
                'severidade' => $critico ? 'danger' : 'warning',
                'url' => APP_URL . '/estoque/' . $row['id'],
                'data_referencia' => null,
                'badge' => $critico ? 'Crítico' : 'Baixo',
            ];
        }, $rows);
    }

    private function qualidade(): array
    {
        $rows = $this->query(
            "SELECT id, codigo, titulo, setor, status, revisao_prevista
             FROM qualidade_pops
             WHERE status <> 'obsoleto'
               AND revisao_prevista IS NOT NULL
               AND revisao_prevista <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
             ORDER BY revisao_prevista
             LIMIT 30"
        );

        return array_map(function (array $row) {
            $vencido = $row['revisao_prevista'] < date('Y-m-d');
            return [
                'modulo' => 'Qualidade',
                'tipo' => $vencido ? 'POP vencido' : 'POP vence em 30 dias',
                'titulo' => $row['codigo'] . ' - ' . $row['titulo'],
                'descricao' => trim(($row['setor'] ?: 'Sem setor') . ' / Status ' . str_replace('_', ' ', $row['status'])),
                'severidade' => $vencido ? 'danger' : 'warning',
                'url' => APP_URL . '/qualidade/pops/' . $row['id'],
                'data_referencia' => $row['revisao_prevista'],
                'badge' => $vencido ? 'Vencido' : 'Revisão próxima',
            ];
        }, $rows);
    }

    private function porModulo(array $alertas): array
    {
        $resumo = [];
        foreach ($alertas as $alerta) {
            $modulo = $alerta['modulo'];
            if (!isset($resumo[$modulo])) {
                $resumo[$modulo] = ['modulo' => $modulo, 'total' => 0, 'criticos' => 0, 'atencao' => 0];
            }
            $resumo[$modulo]['total']++;
            if ($alerta['severidade'] === 'danger') {
                $resumo[$modulo]['criticos']++;
            }
            if ($alerta['severidade'] === 'warning') {
                $resumo[$modulo]['atencao']++;
            }
        }

        usort($resumo, fn($a, $b) => $b['total'] <=> $a['total']);
        return $resumo;
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
