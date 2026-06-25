<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class DreController
{
    public function __construct()
    {
        AuthMiddleware::requer('financeiro');
    }

    public function index(): void
    {
        $dataInicio = $_GET['de'] ?? date('Y-m-01');
        $dataFim = $_GET['ate'] ?? date('Y-m-t');

        $dre = $this->calcular($dataInicio, $dataFim);

        $titulo = 'DRE Gerencial';
        $subtitulo = 'Demonstração do Resultado do Exercício';
        $breadcrumbs = [['label' => 'Financeiro', 'url' => '/financeiro'], ['label' => 'DRE Gerencial', 'url' => '']];
        $headerActions = '<a href="' . APP_URL . '/financeiro/dre/categorias" class="btn btn-secondary btn-sm"><i class="bi bi-tags"></i> Categorias</a>';

        ob_start();
        require APP_PATH . '/Views/financeiro/dre/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function categorias(): void
    {
        $categorias = $this->query(
            "SELECT * FROM categorias_financeiras ORDER BY FIELD(tipo,'receita','imposto','custo_variavel','despesa_operacional','depreciacao','juros'), nome"
        );

        $titulo = 'Categorias Financeiras';
        $subtitulo = 'Classifique as contas para a DRE Gerencial';
        $breadcrumbs = [['label' => 'Financeiro', 'url' => '/financeiro'], ['label' => 'Categorias', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/financeiro/dre/categorias.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criarCategoria(): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/financeiro/dre/categorias');
            exit;
        }

        $nome = trim($_POST['nome'] ?? '');
        $tipo = $_POST['tipo'] ?? '';
        $palavrasChave = trim($_POST['palavras_chave'] ?? '');

        if ($nome === '' || !in_array($tipo, ['receita', 'imposto', 'custo_variavel', 'despesa_operacional', 'depreciacao', 'juros'], true)) {
            $_SESSION['flash_error'] = 'Nome e tipo são obrigatórios.';
            header('Location: ' . APP_URL . '/financeiro/dre/categorias');
            exit;
        }

        try {
            db()->prepare(
                "INSERT INTO categorias_financeiras (nome, tipo, palavras_chave, created_at) VALUES (?, ?, ?, NOW())"
            )->execute([$nome, $tipo, $palavrasChave]);
            Auth::registrarAuditoria('categorias_financeiras', 'criar', (int)db()->lastInsertId());
            $_SESSION['flash_success'] = 'Categoria criada.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao criar categoria: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/financeiro/dre/categorias');
        exit;
    }

    public function atualizarCategoria(string $id): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/financeiro/dre/categorias');
            exit;
        }

        $nome = trim($_POST['nome'] ?? '');
        $tipo = $_POST['tipo'] ?? '';
        $palavrasChave = trim($_POST['palavras_chave'] ?? '');

        if ($nome === '' || !in_array($tipo, ['receita', 'imposto', 'custo_variavel', 'despesa_operacional', 'depreciacao', 'juros'], true)) {
            $_SESSION['flash_error'] = 'Nome e tipo são obrigatórios.';
            header('Location: ' . APP_URL . '/financeiro/dre/categorias');
            exit;
        }

        try {
            db()->prepare(
                "UPDATE categorias_financeiras SET nome = ?, tipo = ?, palavras_chave = ?, updated_at = NOW() WHERE id = ?"
            )->execute([$nome, $tipo, $palavrasChave, $id]);
            Auth::registrarAuditoria('categorias_financeiras', 'atualizar', (int)$id);
            $_SESSION['flash_success'] = 'Categoria atualizada.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao atualizar categoria: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/financeiro/dre/categorias');
        exit;
    }

    public function excluirCategoria(string $id): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/financeiro/dre/categorias');
            exit;
        }

        try {
            db()->prepare("DELETE FROM categorias_financeiras WHERE id = ?")->execute([$id]);
            Auth::registrarAuditoria('categorias_financeiras', 'excluir', (int)$id);
            $_SESSION['flash_success'] = 'Categoria excluída.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao excluir categoria: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/financeiro/dre/categorias');
        exit;
    }

    private function calcular(string $dataInicio, string $dataFim): array
    {
        $receitaBruta = $this->somarReceitas($dataInicio, $dataFim);
        $categorias = $this->listarCategorias();

        $itens = [];
        $somaImpostos = 0;
        $somaCustoVariavel = 0;
        $somaDespesaOperacional = 0;
        $somaDepreciacao = 0;
        $somaJuros = 0;

        foreach ($categorias as $cat) {
            $valor = 0;
            if ($cat['tipo'] !== 'receita') {
                $valor = $this->somarPorPalavrasChave($dataInicio, $dataFim, $cat['palavras_chave']);
            }

            $itens[] = [
                'id' => $cat['id'],
                'nome' => $cat['nome'],
                'tipo' => $cat['tipo'],
                'valor' => $valor,
            ];

            switch ($cat['tipo']) {
                case 'imposto':
                    $somaImpostos += $valor;
                    break;
                case 'custo_variavel':
                    $somaCustoVariavel += $valor;
                    break;
                case 'despesa_operacional':
                    $somaDespesaOperacional += $valor;
                    break;
                case 'depreciacao':
                    $somaDepreciacao += $valor;
                    break;
                case 'juros':
                    $somaJuros += $valor;
                    break;
            }
        }

        $receitaLiquida = $receitaBruta - $somaImpostos;
        $lucroBruto = $receitaLiquida - $somaCustoVariavel;
        $ebitda = $lucroBruto - $somaDespesaOperacional;
        $lucroLiquido = $ebitda - $somaDepreciacao - $somaJuros;

        return [
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'receita_bruta' => $receitaBruta,
            'impostos' => $somaImpostos,
            'receita_liquida' => $receitaLiquida,
            'custos_variaveis' => $somaCustoVariavel,
            'lucro_bruto' => $lucroBruto,
            'despesas_operacionais' => $somaDespesaOperacional,
            'ebitda' => $ebitda,
            'depreciacao' => $somaDepreciacao,
            'juros' => $somaJuros,
            'lucro_liquido' => $lucroLiquido,
            'itens' => $itens,
        ];
    }

    private function somarReceitas(string $dataInicio, string $dataFim): float
    {
        return (float)$this->scalar(
            "SELECT COALESCE(SUM(valor_pago), 0)
             FROM contas_receber
             WHERE status IN ('pago','parcial')
               AND data_pagamento IS NOT NULL
               AND data_pagamento BETWEEN ? AND ?",
            [$dataInicio, $dataFim]
        );
    }

    private function somarPorPalavrasChave(string $dataInicio, string $dataFim, ?string $palavrasChave): float
    {
        if (empty($palavrasChave)) {
            return $this->somarNaoCategorizado($dataInicio, $dataFim);
        }

        $palavras = array_map('trim', explode(',', $palavrasChave));
        $palavras = array_filter($palavras, fn($p) => $p !== '');

        if (empty($palavras)) {
            return 0.0;
        }

        $conditions = [];
        $params = [$dataInicio, $dataFim];

        foreach ($palavras as $palavra) {
            $conditions[] = "LOWER(categoria) LIKE LOWER(?)";
            $params[] = '%' . $palavra . '%';
        }

        $where = '(' . implode(' OR ', $conditions) . ')';

        return (float)$this->scalar(
            "SELECT COALESCE(SUM(valor_pago), 0)
             FROM contas_pagar
             WHERE status IN ('pago','parcial')
               AND data_pagamento IS NOT NULL
               AND data_pagamento BETWEEN ? AND ?
               AND $where",
            $params
        );
    }

    private function somarNaoCategorizado(string $dataInicio, string $dataFim): float
    {
        $todasPalavras = $this->scalarArray(
            "SELECT palavras_chave FROM categorias_financeiras WHERE tipo IN ('imposto','custo_variavel','despesa_operacional','depreciacao','juros') AND palavras_chave IS NOT NULL AND palavras_chave != ''"
        );

        $palavrasUnicas = [];
        foreach ($todasPalavras as $row) {
            $palavras = array_map('trim', explode(',', $row));
            foreach ($palavras as $p) {
                if ($p !== '') {
                    $palavrasUnicas[$p] = true;
                }
            }
        }

        if (empty($palavrasUnicas)) {
            return (float)$this->scalar(
                "SELECT COALESCE(SUM(valor_pago), 0)
                 FROM contas_pagar
                 WHERE status IN ('pago','parcial')
                   AND data_pagamento IS NOT NULL
                   AND data_pagamento BETWEEN ? AND ?",
                [$dataInicio, $dataFim]
            );
        }

        $conditions = [];
        $params = [$dataInicio, $dataFim];
        foreach (array_keys($palavrasUnicas) as $palavra) {
            $conditions[] = "LOWER(categoria) LIKE LOWER(?)";
            $params[] = '%' . $palavra . '%';
        }

        $where = '(' . implode(' OR ', $conditions) . ')';

        return (float)$this->scalar(
            "SELECT COALESCE(SUM(valor_pago), 0)
             FROM contas_pagar
             WHERE status IN ('pago','parcial')
               AND data_pagamento IS NOT NULL
               AND data_pagamento BETWEEN ? AND ?
               AND NOT ($where)",
            $params
        );
    }

    private function listarCategorias(): array
    {
        return $this->query(
            "SELECT * FROM categorias_financeiras ORDER BY FIELD(tipo,'receita','imposto','custo_variavel','despesa_operacional','depreciacao','juros'), nome"
        );
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

    private function scalarArray(string $sql): array
    {
        try {
            $stmt = db()->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];
        } catch (\Exception $e) {
            return [];
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
