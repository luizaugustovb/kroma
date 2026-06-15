<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class OrcamentoController
{
    private array $tipoLabels = [
        'rapido' => 'Orçamento Rápido',
        'completo' => 'Orçamento Completo',
        'ia' => 'Orçamento com IA',
        'produto' => 'Por Produto',
        'item' => 'Por Item',
        'setor' => 'Por Setor',
        'revenda' => 'Revenda',
        'cliente_final' => 'Cliente Final',
    ];

    private array $statusLabels = [
        'rascunho' => 'Rascunho',
        'em_calculo' => 'Em Cálculo',
        'enviado' => 'Enviado',
        'aprovado' => 'Aprovado',
        'recusado' => 'Recusado',
        'cancelado' => 'Cancelado',
        'expirado' => 'Expirado',
    ];

    public function __construct()
    {
        AuthMiddleware::requer('orcamentos');
    }

    public function index(): void
    {
        try {
            $stmt = db()->query(
                "SELECT o.*, c.nome AS cliente_nome, l.nome AS lead_nome, u.nome AS vendedor_nome
                 FROM orcamentos o
                 LEFT JOIN clientes c ON c.id = o.cliente_id
                 LEFT JOIN leads l ON l.id = o.lead_id
                 LEFT JOIN usuarios u ON u.id = o.vendedor_id
                 ORDER BY o.created_at DESC"
            );
            $orcamentos = $stmt->fetchAll();
        } catch (\Exception $e) {
            $orcamentos = [];
        }

        $titulo = 'Orçamentos';
        $subtitulo = 'Orçamentos rápidos, completos e preparados para IA';
        $headerActions = '<a href="' . APP_URL . '/orcamentos/novo" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Novo Orçamento</a>';
        $tipoLabels = $this->tipoLabels;
        $statusLabels = $this->statusLabels;

        ob_start();
        require APP_PATH . '/Views/orcamentos/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function novo(): void
    {
        $orcamento = [
            'tipo' => $_GET['tipo'] ?? 'rapido',
            'status' => 'rascunho',
            'validade' => date('Y-m-d', strtotime('+7 days')),
            'vendedor_id' => Auth::id(),
            'margem_percent' => 35,
            'impostos_percent' => 8,
            'comissao_percent' => 5,
            'desperdicio_percent' => 5,
            'desconto_percent' => 0,
        ];
        $itens = [$this->itemVazio()];
        $clientes = $this->clientes();
        $leads = $this->leads();
        $vendedores = $this->vendedores();
        $tipoLabels = $this->tipoLabels;
        $statusLabels = $this->statusLabels;

        if (!empty($_GET['lead_id'])) {
            $orcamento['lead_id'] = (int)$_GET['lead_id'];
            foreach ($leads as $lead) {
                if ((int)$lead['id'] === (int)$orcamento['lead_id']) {
                    $orcamento['titulo'] = 'Orçamento - ' . $lead['nome'];
                    $orcamento['cliente_id'] = $lead['cliente_id'] ?? null;
                    $itens[0]['produto_nome'] = $lead['produto_interesse'] ?: '';
                    $itens[0]['descricao'] = $lead['descricao'] ?: '';
                    break;
                }
            }
        }

        $titulo = 'Novo Orçamento';
        $subtitulo = 'Cálculo comercial com margem, impostos, desperdício e comissão';
        $breadcrumbs = [['label' => 'Orçamentos', 'url' => '/orcamentos'], ['label' => 'Novo', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/orcamentos/form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criar(): void
    {
        $this->salvar();
    }

    public function ver(string $id): void
    {
        $orcamento = $this->buscar($id);
        if (!$orcamento) {
            $_SESSION['flash_error'] = 'Orçamento não encontrado.';
            header('Location: ' . APP_URL . '/orcamentos');
            exit;
        }

        $itens = $this->itens($id);
        $comissao = $this->comissao($id);
        $tipoLabels = $this->tipoLabels;
        $statusLabels = $this->statusLabels;
        $titulo = $orcamento['codigo'];
        $subtitulo = $orcamento['titulo'];
        $breadcrumbs = [['label' => 'Orçamentos', 'url' => '/orcamentos'], ['label' => $orcamento['codigo'], 'url' => '']];
        $headerActions = '<a href="' . APP_URL . '/orcamentos/' . $id . '/editar" class="btn btn-secondary btn-sm"><i class="bi bi-pencil"></i> Editar</a>';

        ob_start();
        require APP_PATH . '/Views/orcamentos/show.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function editar(string $id): void
    {
        $orcamento = $this->buscar($id);
        if (!$orcamento) {
            $_SESSION['flash_error'] = 'Orçamento não encontrado.';
            header('Location: ' . APP_URL . '/orcamentos');
            exit;
        }

        $itens = $this->itens($id) ?: [$this->itemVazio()];
        $clientes = $this->clientes();
        $leads = $this->leads();
        $vendedores = $this->vendedores();
        $tipoLabels = $this->tipoLabels;
        $statusLabels = $this->statusLabels;
        $titulo = 'Editar Orçamento';
        $subtitulo = $orcamento['codigo'] . ' - ' . $orcamento['titulo'];
        $breadcrumbs = [['label' => 'Orçamentos', 'url' => '/orcamentos'], ['label' => $orcamento['codigo'], 'url' => '/orcamentos/' . $id], ['label' => 'Editar', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/orcamentos/form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function atualizar(string $id): void
    {
        $this->salvar($id);
    }

    public function enviar(string $id): void
    {
        $this->alterarStatus($id, 'enviado', 'Orçamento marcado como enviado.');
    }

    public function aprovar(string $id): void
    {
        $orcamento = $this->buscar($id);
        if (!$orcamento) {
            $_SESSION['flash_error'] = 'Orçamento não encontrado.';
            header('Location: ' . APP_URL . '/orcamentos');
            exit;
        }

        try {
            $pdo = db();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE orcamentos SET status = 'aprovado', aprovado_at = NOW(), updated_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);

            if (!empty($orcamento['lead_id'])) {
                $stmt = $pdo->prepare("UPDATE leads SET estagio = 'aprovado', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$orcamento['lead_id']]);
            }

            $stmt = $pdo->prepare("DELETE FROM comissoes WHERE orcamento_id = ?");
            $stmt->execute([$id]);

            $base = (float)$orcamento['total'];
            $percentual = (float)$orcamento['comissao_percent'];
            $valor = round($base * ($percentual / 100), 2);
            $stmt = $pdo->prepare(
                "INSERT INTO comissoes (orcamento_id, usuario_id, base_calculo, percentual, valor, status, observacoes, created_at)
                 VALUES (?, ?, ?, ?, ?, 'prevista', 'Comissão gerada na aprovação do orçamento.', NOW())"
            );
            $stmt->execute([$id, $orcamento['vendedor_id'], $base, $percentual, $valor]);

            $pdo->commit();
            $_SESSION['flash_success'] = 'Orçamento aprovado e comissão prevista gerada.';
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_error'] = 'Erro ao aprovar orçamento: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/orcamentos/' . $id);
        exit;
    }

    public function cancelar(string $id): void
    {
        $this->alterarStatus($id, 'cancelado', 'Orçamento cancelado.');
    }

    private function salvar(?string $id = null): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/orcamentos' . ($id ? '/' . $id . '/editar' : '/novo'));
            exit;
        }

        $dados = $this->extrairDados();
        $itens = $this->extrairItens($dados);

        if ($dados['titulo'] === '') {
            $_SESSION['flash_error'] = 'Título do orçamento é obrigatório.';
            header('Location: ' . APP_URL . '/orcamentos' . ($id ? '/' . $id . '/editar' : '/novo'));
            exit;
        }

        if (empty($itens)) {
            $_SESSION['flash_error'] = 'Inclua pelo menos um item no orçamento.';
            header('Location: ' . APP_URL . '/orcamentos' . ($id ? '/' . $id . '/editar' : '/novo'));
            exit;
        }

        $totais = $this->calcularTotais($itens, $dados);
        $dados = array_merge($dados, $totais);

        try {
            $pdo = db();
            $pdo->beginTransaction();

            if ($id) {
                $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($dados)));
                $dados['id'] = $id;
                $stmt = $pdo->prepare("UPDATE orcamentos SET $sets, updated_at = NOW() WHERE id = :id");
                $stmt->execute($dados);

                $stmt = $pdo->prepare("DELETE FROM orcamento_itens WHERE orcamento_id = ?");
                $stmt->execute([$id]);
                $orcamentoId = (int)$id;
                $acao = 'editar';
            } else {
                $dados['codigo'] = $this->gerarCodigo();
                $colunas = implode(', ', array_keys($dados));
                $placeholders = ':' . implode(', :', array_keys($dados));
                $stmt = $pdo->prepare("INSERT INTO orcamentos ($colunas, created_at) VALUES ($placeholders, NOW())");
                $stmt->execute($dados);
                $orcamentoId = (int)$pdo->lastInsertId();
                $acao = 'criar';
            }

            $this->salvarItens($orcamentoId, $itens);

            if (!empty($dados['lead_id'])) {
                $stmt = $pdo->prepare("UPDATE leads SET estagio = 'orcamento_enviado', updated_at = NOW() WHERE id = ? AND estagio NOT IN ('aprovado','perdido')");
                $stmt->execute([$dados['lead_id']]);
            }

            Auth::registrarAuditoria('orcamentos', $acao, $orcamentoId);
            $pdo->commit();

            $_SESSION['flash_success'] = $id ? 'Orçamento atualizado.' : 'Orçamento criado.';
            header('Location: ' . APP_URL . '/orcamentos/' . $orcamentoId);
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_error'] = 'Erro ao salvar orçamento: ' . $e->getMessage();
            header('Location: ' . APP_URL . '/orcamentos' . ($id ? '/' . $id . '/editar' : '/novo'));
        }
        exit;
    }

    private function alterarStatus(string $id, string $status, string $mensagem): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/orcamentos/' . $id);
            exit;
        }

        try {
            $stmt = db()->prepare("UPDATE orcamentos SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $id]);
            $_SESSION['flash_success'] = $mensagem;
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao alterar status.';
        }

        header('Location: ' . APP_URL . '/orcamentos/' . $id);
        exit;
    }

    private function extrairDados(): array
    {
        return [
            'cliente_id' => !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null,
            'lead_id' => !empty($_POST['lead_id']) ? (int)$_POST['lead_id'] : null,
            'vendedor_id' => !empty($_POST['vendedor_id']) ? (int)$_POST['vendedor_id'] : Auth::id(),
            'tipo' => $_POST['tipo'] ?? 'rapido',
            'status' => $_POST['status'] ?? 'rascunho',
            'titulo' => trim($_POST['titulo'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'validade' => $_POST['validade'] ?: null,
            'condicao_pagamento' => trim($_POST['condicao_pagamento'] ?? ''),
            'prazo_entrega' => trim($_POST['prazo_entrega'] ?? ''),
            'observacoes' => trim($_POST['observacoes'] ?? ''),
            'desperdicio_percent' => $this->numero($_POST['desperdicio_percent'] ?? 0),
            'impostos_percent' => $this->numero($_POST['impostos_percent'] ?? 0),
            'comissao_percent' => $this->numero($_POST['comissao_percent'] ?? 0),
            'margem_percent' => $this->numero($_POST['margem_percent'] ?? 0),
            'desconto_percent' => $this->numero($_POST['desconto_percent'] ?? 0),
            'desconto_valor' => $this->numero($_POST['desconto_valor'] ?? 0),
        ];
    }

    private function extrairItens(array $dados): array
    {
        $produtos = $_POST['item_produto_nome'] ?? [];
        $itens = [];

        foreach ($produtos as $i => $produto) {
            $produto = trim($produto);
            if ($produto === '') {
                continue;
            }

            $item = [
                'produto_nome' => $produto,
                'descricao' => trim($_POST['item_descricao'][$i] ?? ''),
                'quantidade' => max(0.001, $this->numero($_POST['item_quantidade'][$i] ?? 1)),
                'unidade' => trim($_POST['item_unidade'][$i] ?? 'un'),
                'largura' => $this->numero($_POST['item_largura'][$i] ?? 0),
                'altura' => $this->numero($_POST['item_altura'][$i] ?? 0),
                'custo_material' => $this->numero($_POST['item_custo_material'][$i] ?? 0),
                'custo_tinta' => $this->numero($_POST['item_custo_tinta'][$i] ?? 0),
                'custo_acabamento' => $this->numero($_POST['item_custo_acabamento'][$i] ?? 0),
                'custo_mao_obra' => $this->numero($_POST['item_custo_mao_obra'][$i] ?? 0),
                'custo_maquina' => $this->numero($_POST['item_custo_maquina'][$i] ?? 0),
                'custo_terceiros' => $this->numero($_POST['item_custo_terceiros'][$i] ?? 0),
                'desperdicio_percent' => $this->numero($_POST['item_desperdicio_percent'][$i] ?? $dados['desperdicio_percent']),
                'margem_percent' => $this->numero($_POST['item_margem_percent'][$i] ?? $dados['margem_percent']),
                'impostos_percent' => $this->numero($_POST['item_impostos_percent'][$i] ?? $dados['impostos_percent']),
                'comissao_percent' => $this->numero($_POST['item_comissao_percent'][$i] ?? $dados['comissao_percent']),
                'desconto_percent' => $this->numero($_POST['item_desconto_percent'][$i] ?? 0),
            ];

            $item['area_m2'] = $item['largura'] > 0 && $item['altura'] > 0 ? round($item['largura'] * $item['altura'] * $item['quantidade'], 3) : 0;
            $custoUnitario = $item['custo_material'] + $item['custo_tinta'] + $item['custo_acabamento'] + $item['custo_mao_obra'] + $item['custo_maquina'] + $item['custo_terceiros'];
            $custoComDesperdicio = $custoUnitario * (1 + ($item['desperdicio_percent'] / 100));
            $item['custo_total'] = round($custoComDesperdicio * $item['quantidade'], 2);
            $multiplicadorVenda = 1 + (($item['margem_percent'] + $item['impostos_percent'] + $item['comissao_percent']) / 100);
            $precoUnitario = $custoComDesperdicio * $multiplicadorVenda;
            $precoUnitario = $precoUnitario * (1 - ($item['desconto_percent'] / 100));
            $item['preco_unitario'] = round($precoUnitario, 2);
            $item['total'] = round($item['preco_unitario'] * $item['quantidade'], 2);
            $itens[] = $item;
        }

        return $itens;
    }

    private function calcularTotais(array $itens, array $dados): array
    {
        $subtotalCusto = array_sum(array_column($itens, 'custo_total'));
        $subtotalVenda = array_sum(array_column($itens, 'total'));
        $descontoValor = $dados['desconto_valor'];
        if ($dados['desconto_percent'] > 0) {
            $descontoValor += round($subtotalVenda * ($dados['desconto_percent'] / 100), 2);
        }
        $total = max(0, $subtotalVenda - $descontoValor);
        $precoMinimo = round($subtotalCusto * (1 + (($dados['impostos_percent'] + $dados['comissao_percent']) / 100)), 2);

        return [
            'subtotal_custo' => round($subtotalCusto, 2),
            'subtotal_venda' => round($subtotalVenda, 2),
            'desconto_valor' => round($descontoValor, 2),
            'preco_minimo' => $precoMinimo,
            'lucro_previsto' => round($total - $subtotalCusto, 2),
            'total' => round($total, 2),
        ];
    }

    private function salvarItens(int $orcamentoId, array $itens): void
    {
        $stmt = db()->prepare(
            "INSERT INTO orcamento_itens
             (orcamento_id, produto_nome, descricao, quantidade, unidade, largura, altura, area_m2,
              custo_material, custo_tinta, custo_acabamento, custo_mao_obra, custo_maquina, custo_terceiros,
              desperdicio_percent, margem_percent, impostos_percent, comissao_percent, desconto_percent,
              custo_total, preco_unitario, total, created_at)
             VALUES
             (:orcamento_id, :produto_nome, :descricao, :quantidade, :unidade, :largura, :altura, :area_m2,
              :custo_material, :custo_tinta, :custo_acabamento, :custo_mao_obra, :custo_maquina, :custo_terceiros,
              :desperdicio_percent, :margem_percent, :impostos_percent, :comissao_percent, :desconto_percent,
              :custo_total, :preco_unitario, :total, NOW())"
        );

        foreach ($itens as $item) {
            $item['orcamento_id'] = $orcamentoId;
            $stmt->execute($item);
        }
    }

    private function gerarCodigo(): string
    {
        $prefixo = 'ORC-' . date('Ym') . '-';
        try {
            $stmt = db()->prepare("SELECT COUNT(*) FROM orcamentos WHERE codigo LIKE ?");
            $stmt->execute([$prefixo . '%']);
            $seq = (int)$stmt->fetchColumn() + 1;
        } catch (\Exception $e) {
            $seq = 1;
        }
        return $prefixo . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    private function buscar(string $id): ?array
    {
        try {
            $stmt = db()->prepare(
                "SELECT o.*, c.nome AS cliente_nome, l.nome AS lead_nome, u.nome AS vendedor_nome
                 FROM orcamentos o
                 LEFT JOIN clientes c ON c.id = o.cliente_id
                 LEFT JOIN leads l ON l.id = o.lead_id
                 LEFT JOIN usuarios u ON u.id = o.vendedor_id
                 WHERE o.id = ?"
            );
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function itens(string $orcamentoId): array
    {
        try {
            $stmt = db()->prepare("SELECT * FROM orcamento_itens WHERE orcamento_id = ? ORDER BY id");
            $stmt->execute([$orcamentoId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function comissao(string $orcamentoId): ?array
    {
        try {
            $stmt = db()->prepare("SELECT * FROM comissoes WHERE orcamento_id = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$orcamentoId]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function clientes(): array
    {
        try {
            return db()->query("SELECT id, nome FROM clientes WHERE status = 'ativo' ORDER BY nome LIMIT 500")->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function leads(): array
    {
        try {
            return db()->query("SELECT id, cliente_id, nome, empresa, produto_interesse, descricao FROM leads ORDER BY created_at DESC LIMIT 500")->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function vendedores(): array
    {
        try {
            return db()->query("SELECT id, nome FROM usuarios WHERE ativo = 1 ORDER BY nome")->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function numero($valor): float
    {
        if ($valor === null || $valor === '') {
            return 0.0;
        }
        if (is_numeric($valor)) {
            return (float)$valor;
        }
        return (float)str_replace(['.', ','], ['', '.'], preg_replace('/[^0-9,.-]/', '', (string)$valor));
    }

    private function itemVazio(): array
    {
        return [
            'produto_nome' => '',
            'descricao' => '',
            'quantidade' => 1,
            'unidade' => 'un',
            'largura' => 0,
            'altura' => 0,
            'custo_material' => 0,
            'custo_tinta' => 0,
            'custo_acabamento' => 0,
            'custo_mao_obra' => 0,
            'custo_maquina' => 0,
            'custo_terceiros' => 0,
            'desperdicio_percent' => 5,
            'margem_percent' => 35,
            'impostos_percent' => 8,
            'comissao_percent' => 5,
            'desconto_percent' => 0,
        ];
    }
}
