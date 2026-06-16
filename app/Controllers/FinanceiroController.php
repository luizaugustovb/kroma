<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class FinanceiroController
{
    private array $statusLabels = [
        'aberto' => 'Aberto',
        'parcial' => 'Parcial',
        'pago' => 'Pago',
        'cancelado' => 'Cancelado',
    ];

    private array $origemLabels = [
        'manual' => 'Manual',
        'orcamento' => 'Orçamento',
        'ordem_servico' => 'OS',
    ];

    public function __construct()
    {
        AuthMiddleware::requer('financeiro');
    }

    public function index(): void
    {
        $receber = $this->query(
            "SELECT cr.*, c.nome AS cliente_nome, o.codigo AS orcamento_codigo, os.codigo AS os_codigo
             FROM contas_receber cr
             LEFT JOIN clientes c ON c.id = cr.cliente_id
             LEFT JOIN orcamentos o ON o.id = cr.orcamento_id
             LEFT JOIN ordem_servicos os ON os.id = cr.ordem_servico_id
             ORDER BY FIELD(cr.status, 'aberto','parcial','pago','cancelado'), cr.vencimento IS NULL, cr.vencimento, cr.created_at DESC"
        );
        $pagar = $this->query(
            "SELECT *
             FROM contas_pagar
             ORDER BY FIELD(status, 'aberto','parcial','pago','cancelado'), vencimento IS NULL, vencimento, created_at DESC"
        );
        $caixa = $this->query(
            "SELECT *
             FROM caixa_movimentacoes
             ORDER BY data_movimento DESC, created_at DESC
             LIMIT 30"
        );

        $titulo = 'Financeiro';
        $subtitulo = 'Contas a receber, contas a pagar, baixas e caixa';
        $headerActions = '<a href="' . APP_URL . '/financeiro/receber/novo" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Receber</a> <a href="' . APP_URL . '/financeiro/pagar/novo" class="btn btn-secondary"><i class="bi bi-plus-circle"></i> Pagar</a>';
        $statusLabels = $this->statusLabels;
        $origemLabels = $this->origemLabels;

        ob_start();
        require APP_PATH . '/Views/financeiro/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function novoReceber(): void
    {
        $conta = [
            'cliente_id' => null,
            'orcamento_id' => $_GET['orcamento_id'] ?? null,
            'ordem_servico_id' => $_GET['os_id'] ?? null,
            'descricao' => '',
            'origem' => 'manual',
            'valor' => 0,
            'vencimento' => date('Y-m-d', strtotime('+7 days')),
            'observacoes' => '',
        ];

        if (!empty($_GET['orcamento_id'])) {
            $orcamento = $this->orcamento((string)$_GET['orcamento_id']);
            if ($orcamento) {
                $conta['cliente_id'] = $orcamento['cliente_id'];
                $conta['descricao'] = 'Recebimento ' . $orcamento['codigo'] . ' - ' . $orcamento['titulo'];
                $conta['valor'] = $orcamento['total'];
                $conta['origem'] = 'orcamento';
            }
        }

        if (!empty($_GET['os_id'])) {
            $ordem = $this->ordem((string)$_GET['os_id']);
            if ($ordem) {
                $conta['cliente_id'] = $ordem['cliente_id'];
                $conta['orcamento_id'] = $ordem['orcamento_id'];
                $conta['descricao'] = 'Faturamento ' . $ordem['codigo'] . ' - ' . $ordem['titulo'];
                $conta['valor'] = $this->valorOrdem($ordem);
                $conta['origem'] = 'ordem_servico';
            }
        }

        $clientes = $this->clientes();
        $orcamentos = $this->orcamentosAprovados();
        $ordens = $this->ordensFaturaveis();
        $titulo = 'Nova Conta a Receber';
        $subtitulo = 'Gere cobrança manual, por orçamento ou por OS';
        $breadcrumbs = [['label' => 'Financeiro', 'url' => '/financeiro'], ['label' => 'Receber', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/financeiro/receber_form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criarReceber(): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/financeiro/receber/novo');
            exit;
        }

        $dados = [
            'codigo' => $this->gerarCodigo('REC', 'contas_receber'),
            'cliente_id' => !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null,
            'orcamento_id' => !empty($_POST['orcamento_id']) ? (int)$_POST['orcamento_id'] : null,
            'ordem_servico_id' => !empty($_POST['ordem_servico_id']) ? (int)$_POST['ordem_servico_id'] : null,
            'descricao' => trim($_POST['descricao'] ?? ''),
            'origem' => $_POST['origem'] ?? 'manual',
            'valor' => $this->numero($_POST['valor'] ?? 0),
            'valor_pago' => 0,
            'vencimento' => $_POST['vencimento'] ?: null,
            'status' => 'aberto',
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];

        if ($dados['descricao'] === '' || $dados['valor'] <= 0) {
            $_SESSION['flash_error'] = 'Descrição e valor são obrigatórios.';
            header('Location: ' . APP_URL . '/financeiro/receber/novo');
            exit;
        }

        try {
            $this->insert('contas_receber', $dados);
            $id = (int)db()->lastInsertId();
            Auth::registrarAuditoria('contas_receber', 'criar', $id);
            $_SESSION['flash_success'] = 'Conta a receber criada.';
            header('Location: ' . APP_URL . '/financeiro/receber/' . $id);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao criar conta: ' . $e->getMessage();
            header('Location: ' . APP_URL . '/financeiro/receber/novo');
        }
        exit;
    }

    public function verReceber(string $id): void
    {
        $conta = $this->contaReceber($id);
        if (!$conta) {
            $_SESSION['flash_error'] = 'Conta a receber não encontrada.';
            header('Location: ' . APP_URL . '/financeiro');
            exit;
        }

        $movimentos = $this->queryPreparada("SELECT * FROM caixa_movimentacoes WHERE conta_receber_id = ? ORDER BY data_movimento DESC, id DESC", [$id]);
        $statusLabels = $this->statusLabels;
        $origemLabels = $this->origemLabels;
        $titulo = $conta['codigo'];
        $subtitulo = $conta['descricao'];
        $breadcrumbs = [['label' => 'Financeiro', 'url' => '/financeiro'], ['label' => $conta['codigo'], 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/financeiro/receber_show.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function baixarReceber(string $id): void
    {
        $this->baixarConta('receber', $id);
    }

    public function cancelarReceber(string $id): void
    {
        $this->cancelarConta('receber', $id);
    }

    public function novoPagar(): void
    {
        $conta = [
            'fornecedor' => '',
            'categoria' => '',
            'descricao' => '',
            'valor' => 0,
            'vencimento' => date('Y-m-d', strtotime('+7 days')),
            'observacoes' => '',
        ];
        $titulo = 'Nova Conta a Pagar';
        $subtitulo = 'Despesas, fornecedores, compras e custos fixos';
        $breadcrumbs = [['label' => 'Financeiro', 'url' => '/financeiro'], ['label' => 'Pagar', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/financeiro/pagar_form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criarPagar(): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/financeiro/pagar/novo');
            exit;
        }

        $dados = [
            'codigo' => $this->gerarCodigo('PAG', 'contas_pagar'),
            'fornecedor' => trim($_POST['fornecedor'] ?? ''),
            'categoria' => trim($_POST['categoria'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'valor' => $this->numero($_POST['valor'] ?? 0),
            'valor_pago' => 0,
            'vencimento' => $_POST['vencimento'] ?: null,
            'status' => 'aberto',
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];

        if ($dados['descricao'] === '' || $dados['valor'] <= 0) {
            $_SESSION['flash_error'] = 'Descrição e valor são obrigatórios.';
            header('Location: ' . APP_URL . '/financeiro/pagar/novo');
            exit;
        }

        try {
            $this->insert('contas_pagar', $dados);
            Auth::registrarAuditoria('contas_pagar', 'criar', (int)db()->lastInsertId());
            $_SESSION['flash_success'] = 'Conta a pagar criada.';
            header('Location: ' . APP_URL . '/financeiro');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao criar conta: ' . $e->getMessage();
            header('Location: ' . APP_URL . '/financeiro/pagar/novo');
        }
        exit;
    }

    public function baixarPagar(string $id): void
    {
        $this->baixarConta('pagar', $id);
    }

    public function cancelarPagar(string $id): void
    {
        $this->cancelarConta('pagar', $id);
    }

    private function baixarConta(string $tipoConta, string $id): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/financeiro');
            exit;
        }

        $tabela = $tipoConta === 'receber' ? 'contas_receber' : 'contas_pagar';
        $fk = $tipoConta === 'receber' ? 'conta_receber_id' : 'conta_pagar_id';
        $tipoCaixa = $tipoConta === 'receber' ? 'entrada' : 'saida';
        $conta = $tipoConta === 'receber' ? $this->contaReceber($id) : $this->contaPagar($id);
        if (!$conta || in_array($conta['status'], ['pago','cancelado'], true)) {
            $_SESSION['flash_error'] = 'Conta inválida para baixa.';
            header('Location: ' . APP_URL . '/financeiro');
            exit;
        }

        $valor = $this->numero($_POST['valor_pago'] ?? 0);
        $restante = max(0, (float)$conta['valor'] - (float)$conta['valor_pago']);
        if ($valor <= 0 || $valor > $restante) {
            $_SESSION['flash_error'] = 'Valor de baixa inválido.';
            header('Location: ' . APP_URL . ($tipoConta === 'receber' ? '/financeiro/receber/' . $id : '/financeiro'));
            exit;
        }

        try {
            $pdo = db();
            $pdo->beginTransaction();
            $novoPago = round((float)$conta['valor_pago'] + $valor, 2);
            $status = $novoPago >= (float)$conta['valor'] ? 'pago' : 'parcial';
            $dataPagamento = $_POST['data_pagamento'] ?: date('Y-m-d');
            $forma = trim($_POST['forma_pagamento'] ?? '');
            $pdo->prepare("UPDATE $tabela SET valor_pago = ?, status = ?, data_pagamento = ?, forma_pagamento = ?, updated_at = NOW() WHERE id = ?")
                ->execute([$novoPago, $status, $dataPagamento, $forma, $id]);
            $this->insert('caixa_movimentacoes', [
                $fk => (int)$id,
                'usuario_id' => Auth::id(),
                'tipo' => $tipoCaixa,
                'descricao' => ($tipoConta === 'receber' ? 'Recebimento ' : 'Pagamento ') . $conta['codigo'],
                'valor' => $valor,
                'forma_pagamento' => $forma,
                'data_movimento' => $dataPagamento,
                'observacoes' => trim($_POST['observacoes'] ?? ''),
            ]);
            Auth::registrarAuditoria($tabela, 'baixar', (int)$id);
            $pdo->commit();
            $_SESSION['flash_success'] = 'Baixa registrada.';
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_error'] = 'Erro ao baixar conta: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . ($tipoConta === 'receber' ? '/financeiro/receber/' . $id : '/financeiro'));
        exit;
    }

    private function cancelarConta(string $tipoConta, string $id): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/financeiro');
            exit;
        }

        $tabela = $tipoConta === 'receber' ? 'contas_receber' : 'contas_pagar';
        try {
            db()->prepare("UPDATE $tabela SET status = 'cancelado', updated_at = NOW() WHERE id = ? AND status <> 'pago'")->execute([$id]);
            Auth::registrarAuditoria($tabela, 'cancelar', (int)$id);
            $_SESSION['flash_success'] = 'Conta cancelada.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao cancelar conta.';
        }

        header('Location: ' . APP_URL . '/financeiro');
        exit;
    }

    private function contaReceber(string $id): ?array
    {
        try {
            $stmt = db()->prepare(
                "SELECT cr.*, c.nome AS cliente_nome, o.codigo AS orcamento_codigo, os.codigo AS os_codigo
                 FROM contas_receber cr
                 LEFT JOIN clientes c ON c.id = cr.cliente_id
                 LEFT JOIN orcamentos o ON o.id = cr.orcamento_id
                 LEFT JOIN ordem_servicos os ON os.id = cr.ordem_servico_id
                 WHERE cr.id = ?"
            );
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function contaPagar(string $id): ?array
    {
        try {
            $stmt = db()->prepare("SELECT * FROM contas_pagar WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function orcamento(string $id): ?array
    {
        try {
            $stmt = db()->prepare("SELECT * FROM orcamentos WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function ordem(string $id): ?array
    {
        try {
            $stmt = db()->prepare("SELECT * FROM ordem_servicos WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function valorOrdem(array $ordem): float
    {
        if (!empty($ordem['orcamento_id'])) {
            $orcamento = $this->orcamento((string)$ordem['orcamento_id']);
            if ($orcamento) {
                return (float)$orcamento['total'];
            }
        }
        return 0.0;
    }

    private function clientes(): array
    {
        return $this->query("SELECT id, nome FROM clientes WHERE status = 'ativo' ORDER BY nome LIMIT 500");
    }

    private function orcamentosAprovados(): array
    {
        return $this->query("SELECT id, codigo, titulo, total FROM orcamentos WHERE status = 'aprovado' ORDER BY aprovado_at DESC, created_at DESC LIMIT 300");
    }

    private function ordensFaturaveis(): array
    {
        return $this->query("SELECT id, codigo, titulo FROM ordem_servicos WHERE status <> 'cancelada' ORDER BY created_at DESC LIMIT 300");
    }

    private function gerarCodigo(string $prefixoBase, string $tabela): string
    {
        $prefixo = $prefixoBase . '-' . date('Ym') . '-';
        try {
            $stmt = db()->prepare("SELECT COUNT(*) FROM $tabela WHERE codigo LIKE ?");
            $stmt->execute([$prefixo . '%']);
            $seq = (int)$stmt->fetchColumn() + 1;
        } catch (\Exception $e) {
            $seq = 1;
        }
        return $prefixo . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    private function insert(string $tabela, array $dados): void
    {
        $colunas = implode(', ', array_keys($dados));
        $placeholders = ':' . implode(', :', array_keys($dados));
        db()->prepare("INSERT INTO $tabela ($colunas, created_at) VALUES ($placeholders, NOW())")->execute($dados);
    }

    private function query(string $sql): array
    {
        try {
            return db()->query($sql)->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function queryPreparada(string $sql, array $params): array
    {
        try {
            $stmt = db()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
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
}
