<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class ComprasController
{
    private array $statusLabels = [
        'rascunho' => 'Rascunho',
        'solicitada' => 'Solicitada',
        'aprovada' => 'Aprovada',
        'recebida' => 'Recebida',
        'cancelada' => 'Cancelada',
    ];

    private array $origemLabels = [
        'manual' => 'Manual',
        'estoque_critico' => 'Estoque Crítico',
    ];

    public function __construct()
    {
        AuthMiddleware::requer('compras');
    }

    public function index(): void
    {
        $compras = $this->query(
            "SELECT c.*, f.nome AS fornecedor_nome, u.nome AS solicitante_nome,
                (SELECT COUNT(*) FROM compra_itens ci WHERE ci.compra_id = c.id) AS total_itens
             FROM compras c
             LEFT JOIN fornecedores f ON f.id = c.fornecedor_id
             LEFT JOIN usuarios u ON u.id = c.solicitante_id
             ORDER BY FIELD(c.status, 'solicitada','aprovada','rascunho','recebida','cancelada'), c.previsao_entrega IS NULL, c.previsao_entrega, c.created_at DESC"
        );
        $fornecedores = $this->query("SELECT * FROM fornecedores ORDER BY FIELD(status, 'ativo','inativo'), nome LIMIT 8");
        $materiaisCriticos = $this->query(
            "SELECT *, (estoque_atual - estoque_reservado) AS estoque_disponivel
             FROM materiais
             WHERE status = 'ativo' AND (estoque_atual - estoque_reservado) <= estoque_minimo
             ORDER BY nome
             LIMIT 10"
        );

        $titulo = 'Compras';
        $subtitulo = 'Fornecedores, solicitações, recebimento e reposição de estoque';
        $headerActions = '<a href="' . APP_URL . '/compras/novo" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nova Compra</a> <a href="' . APP_URL . '/compras/fornecedores/novo" class="btn btn-secondary"><i class="bi bi-building-add"></i> Fornecedor</a>';
        $statusLabels = $this->statusLabels;
        $origemLabels = $this->origemLabels;

        ob_start();
        require APP_PATH . '/Views/compras/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function novo(): void
    {
        $compra = [
            'fornecedor_id' => null,
            'solicitante_id' => Auth::id(),
            'status' => 'rascunho',
            'origem' => !empty($_GET['material_id']) ? 'estoque_critico' : 'manual',
            'titulo' => '',
            'data_solicitacao' => date('Y-m-d'),
            'previsao_entrega' => date('Y-m-d', strtotime('+7 days')),
            'gerar_conta_pagar' => 1,
            'observacoes' => '',
        ];
        $itens = [$this->itemVazio()];
        if (!empty($_GET['material_id'])) {
            $material = $this->material((string)$_GET['material_id']);
            if ($material) {
                $compra['titulo'] = 'Reposição - ' . $material['nome'];
                $necessario = max(1, ((float)$material['estoque_minimo'] * 2) - ((float)$material['estoque_atual'] - (float)$material['estoque_reservado']));
                $itens = [[
                    'material_id' => $material['id'],
                    'descricao' => $material['nome'],
                    'quantidade' => $necessario,
                    'unidade' => $material['unidade'],
                    'custo_unitario' => $material['custo_atual'],
                    'total' => round($necessario * (float)$material['custo_atual'], 2),
                ]];
            }
        }

        $contexto = $this->contextoFormulario();
        $titulo = 'Nova Compra';
        $subtitulo = 'Solicitação de compra manual ou por estoque crítico';
        $breadcrumbs = [['label' => 'Compras', 'url' => '/compras'], ['label' => 'Nova', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/compras/form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criar(): void
    {
        $this->salvar();
    }

    public function ver(string $id): void
    {
        $compra = $this->buscarCompra($id);
        if (!$compra) {
            $_SESSION['flash_error'] = 'Compra não encontrada.';
            header('Location: ' . APP_URL . '/compras');
            exit;
        }

        $itens = $this->itens($id);
        $contaPagar = $this->contaPagarCompra($compra);
        $statusLabels = $this->statusLabels;
        $origemLabels = $this->origemLabels;
        $titulo = $compra['codigo'];
        $subtitulo = $compra['titulo'];
        $breadcrumbs = [['label' => 'Compras', 'url' => '/compras'], ['label' => $compra['codigo'], 'url' => '']];
        $headerActions = '<a href="' . APP_URL . '/compras/' . $id . '/editar" class="btn btn-secondary btn-sm"><i class="bi bi-pencil"></i> Editar</a>';

        ob_start();
        require APP_PATH . '/Views/compras/show.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function editar(string $id): void
    {
        $compra = $this->buscarCompra($id);
        if (!$compra) {
            $_SESSION['flash_error'] = 'Compra não encontrada.';
            header('Location: ' . APP_URL . '/compras');
            exit;
        }

        $itens = $this->itens($id) ?: [$this->itemVazio()];
        $contexto = $this->contextoFormulario();
        $titulo = 'Editar Compra';
        $subtitulo = $compra['codigo'] . ' - ' . $compra['titulo'];
        $breadcrumbs = [['label' => 'Compras', 'url' => '/compras'], ['label' => $compra['codigo'], 'url' => '/compras/' . $id], ['label' => 'Editar', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/compras/form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function atualizar(string $id): void
    {
        $this->salvar($id);
    }

    public function status(string $id): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/compras/' . $id);
            exit;
        }
        $status = $_POST['status'] ?? '';
        if (!isset($this->statusLabels[$status]) || $status === 'recebida') {
            $_SESSION['flash_error'] = 'Status inválido para esta ação.';
            header('Location: ' . APP_URL . '/compras/' . $id);
            exit;
        }

        try {
            $sets = ['status = ?', 'updated_at = NOW()'];
            $params = [$status];
            if ($status === 'aprovada') {
                $sets[] = 'aprovado_por_id = ?';
                $sets[] = 'data_aprovacao = NOW()';
                $params[] = Auth::id();
            }
            $params[] = $id;
            db()->prepare('UPDATE compras SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);
            Auth::registrarAuditoria('compras', 'status_' . $status, (int)$id);
            $_SESSION['flash_success'] = 'Status da compra atualizado.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao atualizar compra.';
        }
        header('Location: ' . APP_URL . '/compras/' . $id);
        exit;
    }

    public function receber(string $id): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/compras/' . $id);
            exit;
        }

        $compra = $this->buscarCompra($id);
        if (!$compra || in_array($compra['status'], ['recebida', 'cancelada'], true)) {
            $_SESSION['flash_error'] = 'Compra inválida para recebimento.';
            header('Location: ' . APP_URL . '/compras');
            exit;
        }
        $itens = $this->itens($id);
        if (empty($itens)) {
            $_SESSION['flash_error'] = 'Compra sem itens.';
            header('Location: ' . APP_URL . '/compras/' . $id);
            exit;
        }

        try {
            $pdo = db();
            $pdo->beginTransaction();

            foreach ($itens as $item) {
                if (empty($item['material_id'])) {
                    continue;
                }
                $stmt = $pdo->prepare("SELECT * FROM materiais WHERE id = ? FOR UPDATE");
                $stmt->execute([$item['material_id']]);
                $material = $stmt->fetch();
                if (!$material) {
                    continue;
                }
                $quantidade = (float)$item['quantidade'];
                $custo = (float)$item['custo_unitario'];
                $saldoAnterior = (float)$material['estoque_atual'];
                $saldoPosterior = $saldoAnterior + $quantidade;
                $reservado = (float)$material['estoque_reservado'];
                $valorAtual = $saldoAnterior * (float)$material['custo_atual'];
                $valorEntrada = $quantidade * $custo;
                $novoCusto = $saldoPosterior > 0 ? round(($valorAtual + $valorEntrada) / $saldoPosterior, 2) : $custo;
                $pdo->prepare("UPDATE materiais SET estoque_atual = ?, custo_atual = ?, fornecedor = COALESCE(NULLIF(?, ''), fornecedor), updated_at = NOW() WHERE id = ?")
                    ->execute([$saldoPosterior, $novoCusto, $compra['fornecedor_nome'] ?? '', $item['material_id']]);
                $pdo->prepare(
                    "INSERT INTO estoque_movimentacoes
                     (material_id, usuario_id, tipo, origem, quantidade, custo_unitario, saldo_anterior, saldo_posterior, reservado_anterior, reservado_posterior, observacao, created_at)
                     VALUES (?, ?, 'entrada', ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
                )->execute([
                    $item['material_id'],
                    Auth::id(),
                    'Compra ' . $compra['codigo'],
                    $quantidade,
                    $custo,
                    $saldoAnterior,
                    $saldoPosterior,
                    $reservado,
                    $reservado,
                    'Recebimento da compra ' . $compra['codigo'],
                ]);
            }

            $pdo->prepare("UPDATE compra_itens SET recebido = 1 WHERE compra_id = ?")->execute([$id]);
            $pdo->prepare("UPDATE compras SET status = 'recebida', data_recebimento = NOW(), updated_at = NOW() WHERE id = ?")->execute([$id]);

            if ((int)$compra['gerar_conta_pagar'] === 1 && (float)$compra['total'] > 0 && !$this->contaPagarCompra($compra)) {
                $this->insert('contas_pagar', [
                    'codigo' => $this->gerarCodigo('PAG', 'contas_pagar'),
                    'fornecedor' => $compra['fornecedor_nome'] ?? '',
                    'categoria' => 'Compras',
                    'descricao' => 'Compra ' . $compra['codigo'] . ' - ' . $compra['titulo'],
                    'valor' => $compra['total'],
                    'valor_pago' => 0,
                    'vencimento' => $_POST['vencimento_conta'] ?: date('Y-m-d', strtotime('+7 days')),
                    'status' => 'aberto',
                    'observacoes' => 'Conta gerada automaticamente no recebimento da compra.',
                ]);
            }

            Auth::registrarAuditoria('compras', 'receber', (int)$id);
            $pdo->commit();
            $_SESSION['flash_success'] = 'Compra recebida, estoque atualizado e conta a pagar gerada.';
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_error'] = 'Erro ao receber compra: ' . $e->getMessage();
        }
        header('Location: ' . APP_URL . '/compras/' . $id);
        exit;
    }

    public function fornecedores(): void
    {
        $fornecedores = $this->query(
            "SELECT f.*, (SELECT COUNT(*) FROM fornecedor_materiais fm WHERE fm.fornecedor_id = f.id) AS total_materiais
             FROM fornecedores f
             ORDER BY FIELD(f.status, 'ativo','inativo'), f.nome"
        );
        $titulo = 'Fornecedores';
        $subtitulo = 'Cadastro de fornecedores e materiais atendidos';
        $headerActions = '<a href="' . APP_URL . '/compras/fornecedores/novo" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Novo Fornecedor</a>';
        ob_start();
        require APP_PATH . '/Views/compras/fornecedores.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function novoFornecedor(): void
    {
        $fornecedor = ['tipo_pessoa' => 'juridica', 'status' => 'ativo'];
        $materiaisSelecionados = [];
        $materiais = $this->materiais();
        $titulo = 'Novo Fornecedor';
        $subtitulo = 'Cadastro fiscal e materiais fornecidos';
        $breadcrumbs = [['label' => 'Compras', 'url' => '/compras'], ['label' => 'Fornecedores', 'url' => '/compras/fornecedores'], ['label' => 'Novo', 'url' => '']];
        ob_start();
        require APP_PATH . '/Views/compras/fornecedor_form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criarFornecedor(): void
    {
        $this->salvarFornecedor();
    }

    public function editarFornecedor(string $id): void
    {
        $fornecedor = $this->fornecedor($id);
        if (!$fornecedor) {
            $_SESSION['flash_error'] = 'Fornecedor não encontrado.';
            header('Location: ' . APP_URL . '/compras/fornecedores');
            exit;
        }
        $materiaisSelecionados = array_map('strval', array_column($this->materiaisFornecedor($id), 'material_id'));
        $materiais = $this->materiais();
        $titulo = 'Editar Fornecedor';
        $subtitulo = $fornecedor['codigo'] . ' - ' . $fornecedor['nome'];
        $breadcrumbs = [['label' => 'Compras', 'url' => '/compras'], ['label' => 'Fornecedores', 'url' => '/compras/fornecedores'], ['label' => 'Editar', 'url' => '']];
        ob_start();
        require APP_PATH . '/Views/compras/fornecedor_form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function atualizarFornecedor(string $id): void
    {
        $this->salvarFornecedor($id);
    }

    public function excluir(string $id): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/compras');
            exit;
        }

        if (!Auth::temPerfil('administrador')) {
            $_SESSION['flash_error'] = 'Apenas administradores podem excluir compras.';
            header('Location: ' . APP_URL . '/compras');
            exit;
        }

        $compra = $this->buscarCompra($id);
        if (!$compra) {
            $_SESSION['flash_error'] = 'Compra não encontrada.';
            header('Location: ' . APP_URL . '/compras');
            exit;
        }

        if (!in_array($compra['status'], ['rascunho', 'cancelada'], true)) {
            $_SESSION['flash_error'] = 'Somente compras em Rascunho ou Cancelada podem ser excluídas.';
            header('Location: ' . APP_URL . '/compras');
            exit;
        }

        try {
            $pdo = db();
            $pdo->beginTransaction();
            $pdo->prepare("DELETE FROM compra_itens WHERE compra_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM compras WHERE id = ?")->execute([$id]);
            Auth::registrarAuditoria('compras', 'excluir_permanente', (int)$id);
            $pdo->commit();
            $_SESSION['flash_success'] = 'Compra excluída permanentemente.';
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['flash_error'] = 'Erro ao excluir compra.';
        }

        header('Location: ' . APP_URL . '/compras');
        exit;
    }

    private function salvar(?string $id = null): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/compras' . ($id ? '/' . $id . '/editar' : '/novo'));
            exit;
        }
        $dados = $this->extrairCompra();
        $itens = $this->extrairItens();
        if ($dados['titulo'] === '' || empty($itens)) {
            $_SESSION['flash_error'] = 'Informe título e pelo menos um item.';
            header('Location: ' . APP_URL . '/compras' . ($id ? '/' . $id . '/editar' : '/novo'));
            exit;
        }
        $dados['total'] = array_sum(array_column($itens, 'total'));

        try {
            $pdo = db();
            $pdo->beginTransaction();
            if ($id) {
                $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($dados)));
                $dados['id'] = $id;
                $pdo->prepare("UPDATE compras SET $sets, updated_at = NOW() WHERE id = :id")->execute($dados);
                $compraId = (int)$id;
                $pdo->prepare("DELETE FROM compra_itens WHERE compra_id = ?")->execute([$compraId]);
                $acao = 'editar';
            } else {
                $dados['codigo'] = $this->gerarCodigo('COM', 'compras');
                $this->insert('compras', $dados);
                $compraId = (int)$pdo->lastInsertId();
                $acao = 'criar';
            }
            $stmt = $pdo->prepare(
                "INSERT INTO compra_itens (compra_id, material_id, descricao, quantidade, unidade, custo_unitario, total, recebido, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())"
            );
            foreach ($itens as $item) {
                $stmt->execute([$compraId, $item['material_id'], $item['descricao'], $item['quantidade'], $item['unidade'], $item['custo_unitario'], $item['total']]);
            }
            Auth::registrarAuditoria('compras', $acao, $compraId);
            $pdo->commit();
            $_SESSION['flash_success'] = $id ? 'Compra atualizada.' : 'Compra criada.';
            header('Location: ' . APP_URL . '/compras/' . $compraId);
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_error'] = 'Erro ao salvar compra: ' . $e->getMessage();
            header('Location: ' . APP_URL . '/compras' . ($id ? '/' . $id . '/editar' : '/novo'));
        }
        exit;
    }

    private function salvarFornecedor(?string $id = null): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/compras/fornecedores');
            exit;
        }
        $dados = [
            'codigo' => trim($_POST['codigo'] ?? ''),
            'nome' => trim($_POST['nome'] ?? ''),
            'tipo_pessoa' => $_POST['tipo_pessoa'] ?? 'juridica',
            'cpf_cnpj' => trim($_POST['cpf_cnpj'] ?? ''),
            'contato' => trim($_POST['contato'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'telefone' => trim($_POST['telefone'] ?? ''),
            'whatsapp' => trim($_POST['whatsapp'] ?? ''),
            'endereco' => trim($_POST['endereco'] ?? ''),
            'cidade' => trim($_POST['cidade'] ?? ''),
            'estado' => strtoupper(trim($_POST['estado'] ?? '')),
            'status' => $_POST['status'] ?? 'ativo',
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];
        if ($dados['nome'] === '') {
            $_SESSION['flash_error'] = 'Nome do fornecedor é obrigatório.';
            header('Location: ' . APP_URL . '/compras/fornecedores' . ($id ? '/' . $id . '/editar' : '/novo'));
            exit;
        }
        if ($dados['codigo'] === '') {
            $dados['codigo'] = $this->gerarCodigo('FOR', 'fornecedores');
        }
        try {
            $pdo = db();
            $pdo->beginTransaction();
            if ($id) {
                $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($dados)));
                $dados['id'] = $id;
                $pdo->prepare("UPDATE fornecedores SET $sets, updated_at = NOW() WHERE id = :id")->execute($dados);
                $fornecedorId = (int)$id;
                $pdo->prepare("DELETE FROM fornecedor_materiais WHERE fornecedor_id = ?")->execute([$fornecedorId]);
                $acao = 'editar';
            } else {
                $this->insert('fornecedores', $dados);
                $fornecedorId = (int)$pdo->lastInsertId();
                $acao = 'criar';
            }
            $stmt = $pdo->prepare("INSERT IGNORE INTO fornecedor_materiais (fornecedor_id, material_id, created_at) VALUES (?, ?, NOW())");
            foreach (array_filter(array_map('intval', $_POST['materiais'] ?? [])) as $materialId) {
                $stmt->execute([$fornecedorId, $materialId]);
            }
            Auth::registrarAuditoria('fornecedores', $acao, $fornecedorId);
            $pdo->commit();
            $_SESSION['flash_success'] = $id ? 'Fornecedor atualizado.' : 'Fornecedor cadastrado.';
            header('Location: ' . APP_URL . '/compras/fornecedores');
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_error'] = 'Erro ao salvar fornecedor: ' . $e->getMessage();
            header('Location: ' . APP_URL . '/compras/fornecedores');
        }
        exit;
    }

    private function extrairCompra(): array
    {
        return [
            'fornecedor_id' => !empty($_POST['fornecedor_id']) ? (int)$_POST['fornecedor_id'] : null,
            'solicitante_id' => !empty($_POST['solicitante_id']) ? (int)$_POST['solicitante_id'] : Auth::id(),
            'aprovado_por_id' => null,
            'status' => $_POST['status'] ?? 'rascunho',
            'origem' => $_POST['origem'] ?? 'manual',
            'titulo' => trim($_POST['titulo'] ?? ''),
            'data_solicitacao' => $_POST['data_solicitacao'] ?: date('Y-m-d'),
            'previsao_entrega' => $_POST['previsao_entrega'] ?: null,
            'gerar_conta_pagar' => isset($_POST['gerar_conta_pagar']) ? 1 : 0,
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];
    }

    private function extrairItens(): array
    {
        $descricoes = $_POST['item_descricao'] ?? [];
        $itens = [];
        foreach ($descricoes as $i => $descricao) {
            $descricao = trim($descricao);
            if ($descricao === '') {
                continue;
            }
            $quantidade = max(0.001, $this->numero($_POST['item_quantidade'][$i] ?? 1));
            $custo = $this->numero($_POST['item_custo_unitario'][$i] ?? 0);
            $itens[] = [
                'material_id' => !empty($_POST['item_material_id'][$i]) ? (int)$_POST['item_material_id'][$i] : null,
                'descricao' => $descricao,
                'quantidade' => $quantidade,
                'unidade' => trim($_POST['item_unidade'][$i] ?? 'un'),
                'custo_unitario' => $custo,
                'total' => round($quantidade * $custo, 2),
            ];
        }
        return $itens;
    }

    private function contextoFormulario(): array
    {
        return [
            'fornecedores' => $this->query("SELECT id, nome FROM fornecedores WHERE status = 'ativo' ORDER BY nome"),
            'usuarios' => $this->query("SELECT id, nome FROM usuarios WHERE ativo = 1 ORDER BY nome"),
            'materiais' => $this->materiais(),
            'statusLabels' => $this->statusLabels,
            'origemLabels' => $this->origemLabels,
        ];
    }

    private function buscarCompra(string $id): ?array
    {
        try {
            $stmt = db()->prepare(
                "SELECT c.*, f.nome AS fornecedor_nome, f.cpf_cnpj AS fornecedor_doc, u.nome AS solicitante_nome, a.nome AS aprovador_nome
                 FROM compras c
                 LEFT JOIN fornecedores f ON f.id = c.fornecedor_id
                 LEFT JOIN usuarios u ON u.id = c.solicitante_id
                 LEFT JOIN usuarios a ON a.id = c.aprovado_por_id
                 WHERE c.id = ?"
            );
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function itens(string $compraId): array
    {
        return $this->queryPreparada(
            "SELECT ci.*, m.nome AS material_nome, m.codigo AS material_codigo
             FROM compra_itens ci
             LEFT JOIN materiais m ON m.id = ci.material_id
             WHERE ci.compra_id = ?
             ORDER BY ci.id",
            [$compraId]
        );
    }

    private function contaPagarCompra(array $compra): ?array
    {
        try {
            $stmt = db()->prepare("SELECT * FROM contas_pagar WHERE descricao = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute(['Compra ' . $compra['codigo'] . ' - ' . $compra['titulo']]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function fornecedor(string $id): ?array
    {
        try {
            $stmt = db()->prepare("SELECT * FROM fornecedores WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function materiaisFornecedor(string $id): array
    {
        return $this->queryPreparada("SELECT material_id FROM fornecedor_materiais WHERE fornecedor_id = ?", [$id]);
    }

    private function materiais(): array
    {
        return $this->query("SELECT id, codigo, nome, unidade, custo_atual, estoque_atual, estoque_minimo, estoque_reservado FROM materiais WHERE status = 'ativo' ORDER BY nome");
    }

    private function material(string $id): ?array
    {
        try {
            $stmt = db()->prepare("SELECT * FROM materiais WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
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
        if ($valor === null || $valor === '') return 0.0;
        if (is_numeric($valor)) return (float)$valor;
        return (float)str_replace(['.', ','], ['', '.'], preg_replace('/[^0-9,.-]/', '', (string)$valor));
    }

    private function itemVazio(): array
    {
        return ['material_id' => null, 'descricao' => '', 'quantidade' => 1, 'unidade' => 'un', 'custo_unitario' => 0, 'total' => 0];
    }
}
