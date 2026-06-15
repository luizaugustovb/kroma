<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class ProducaoController
{
    private array $statusLabels = [
        'aberta' => 'Aberta',
        'em_producao' => 'Em Produção',
        'aguardando' => 'Aguardando',
        'finalizada' => 'Finalizada',
        'cancelada' => 'Cancelada',
    ];

    private array $prioridadeLabels = [
        'baixa' => 'Baixa',
        'media' => 'Média',
        'alta' => 'Alta',
        'urgente' => 'Urgente',
    ];

    private array $etapaStatusLabels = [
        'pendente' => 'Pendente',
        'em_producao' => 'Em Produção',
        'pausada' => 'Pausada',
        'concluida' => 'Concluída',
        'cancelada' => 'Cancelada',
    ];

    public function __construct()
    {
        AuthMiddleware::requer('producao');
    }

    public function index(): void
    {
        try {
            $ordens = db()->query(
                "SELECT os.*, c.nome AS cliente_nome, u.nome AS responsavel_nome,
                    (SELECT COUNT(*) FROM ordem_servico_itens i WHERE i.ordem_servico_id = os.id) AS total_itens,
                    (SELECT COUNT(*) FROM ordem_servico_etapas e WHERE e.ordem_servico_id = os.id) AS total_etapas,
                    (SELECT COUNT(*) FROM ordem_servico_etapas e WHERE e.ordem_servico_id = os.id AND e.status = 'concluida') AS etapas_concluidas
                 FROM ordem_servicos os
                 LEFT JOIN clientes c ON c.id = os.cliente_id
                 LEFT JOIN usuarios u ON u.id = os.responsavel_id
                 ORDER BY FIELD(os.status, 'em_producao','aberta','aguardando','finalizada','cancelada'), os.data_prometida IS NULL, os.data_prometida, os.created_at DESC"
            )->fetchAll();
        } catch (\Exception $e) {
            $ordens = [];
        }

        $titulo = 'Produção';
        $subtitulo = 'Fila de ordens de serviço, etapas produtivas e prazos';
        $headerActions = '<a href="' . APP_URL . '/producao/novo" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nova OS</a>';
        $statusLabels = $this->statusLabels;
        $prioridadeLabels = $this->prioridadeLabels;

        ob_start();
        require APP_PATH . '/Views/producao/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function novo(): void
    {
        $ordem = [
            'orcamento_id' => $_GET['orcamento_id'] ?? null,
            'cliente_id' => null,
            'responsavel_id' => Auth::id(),
            'titulo' => '',
            'descricao' => '',
            'prioridade' => 'media',
            'status' => 'aberta',
            'data_entrada' => date('Y-m-d'),
            'data_prometida' => date('Y-m-d', strtotime('+7 days')),
            'observacoes' => '',
        ];
        $itens = [$this->itemVazio()];
        $processosSelecionados = [];

        if (!empty($_GET['orcamento_id'])) {
            $orcamento = $this->orcamento((string)$_GET['orcamento_id']);
            if ($orcamento) {
                $ordem['orcamento_id'] = $orcamento['id'];
                $ordem['cliente_id'] = $orcamento['cliente_id'];
                $ordem['titulo'] = 'OS - ' . $orcamento['titulo'];
                $ordem['descricao'] = $orcamento['descricao'] ?? '';
                $ordem['data_prometida'] = date('Y-m-d', strtotime('+7 days'));
                $itens = $this->itensDoOrcamento((string)$orcamento['id']);
                if (empty($itens)) {
                    $itens = [$this->itemVazio()];
                }
            }
        }

        $contexto = $this->contextoFormulario();
        $titulo = 'Nova Ordem de Serviço';
        $subtitulo = 'Transforme venda aprovada em fila de produção';
        $breadcrumbs = [['label' => 'Produção', 'url' => '/producao'], ['label' => 'Nova OS', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/producao/form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criar(): void
    {
        $this->salvar();
    }

    public function ver(string $id): void
    {
        $ordem = $this->buscar($id);
        if (!$ordem) {
            $_SESSION['flash_error'] = 'Ordem de serviço não encontrada.';
            header('Location: ' . APP_URL . '/producao');
            exit;
        }

        $itens = $this->itens($id);
        $etapas = $this->etapas($id);
        $statusLabels = $this->statusLabels;
        $prioridadeLabels = $this->prioridadeLabels;
        $etapaStatusLabels = $this->etapaStatusLabels;
        $titulo = $ordem['codigo'];
        $subtitulo = $ordem['titulo'];
        $breadcrumbs = [['label' => 'Produção', 'url' => '/producao'], ['label' => $ordem['codigo'], 'url' => '']];
        $headerActions = '<a href="' . APP_URL . '/producao/' . $id . '/editar" class="btn btn-secondary btn-sm"><i class="bi bi-pencil"></i> Editar</a>';

        ob_start();
        require APP_PATH . '/Views/producao/show.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function editar(string $id): void
    {
        $ordem = $this->buscar($id);
        if (!$ordem) {
            $_SESSION['flash_error'] = 'Ordem de serviço não encontrada.';
            header('Location: ' . APP_URL . '/producao');
            exit;
        }

        $itens = $this->itens($id) ?: [$this->itemVazio()];
        $processosSelecionados = array_filter(array_unique(array_column($this->etapas($id), 'processo_id')));
        $contexto = $this->contextoFormulario();
        $titulo = 'Editar Ordem de Serviço';
        $subtitulo = $ordem['codigo'] . ' - ' . $ordem['titulo'];
        $breadcrumbs = [['label' => 'Produção', 'url' => '/producao'], ['label' => $ordem['codigo'], 'url' => '/producao/' . $id], ['label' => 'Editar', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/producao/form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function atualizar(string $id): void
    {
        $this->salvar($id);
    }

    public function alterarStatus(string $id): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/producao/' . $id);
            exit;
        }

        $status = $_POST['status'] ?? '';
        if (!isset($this->statusLabels[$status])) {
            $_SESSION['flash_error'] = 'Status de OS inválido.';
            header('Location: ' . APP_URL . '/producao/' . $id);
            exit;
        }

        $sets = ['status = ?'];
        $params = [$status];
        if ($status === 'em_producao') {
            $sets[] = 'data_inicio = COALESCE(data_inicio, NOW())';
        }
        if ($status === 'finalizada') {
            $sets[] = 'data_finalizacao = NOW()';
            db()->prepare("UPDATE ordem_servico_etapas SET status = 'concluida', data_fim = COALESCE(data_fim, NOW()) WHERE ordem_servico_id = ? AND status NOT IN ('concluida','cancelada')")->execute([$id]);
            db()->prepare("UPDATE ordem_servico_itens SET status = 'concluido' WHERE ordem_servico_id = ? AND status <> 'cancelado'")->execute([$id]);
        }
        $sets[] = 'updated_at = NOW()';
        $params[] = $id;

        try {
            db()->prepare('UPDATE ordem_servicos SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);
            Auth::registrarAuditoria('ordem_servicos', 'status_' . $status, (int)$id);
            $_SESSION['flash_success'] = 'Status da OS atualizado.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao atualizar status da OS.';
        }

        header('Location: ' . APP_URL . '/producao/' . $id);
        exit;
    }

    public function etapaStatus(string $id): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/producao');
            exit;
        }

        $status = $_POST['status'] ?? '';
        if (!isset($this->etapaStatusLabels[$status])) {
            $_SESSION['flash_error'] = 'Status de etapa inválido.';
            header('Location: ' . APP_URL . '/producao');
            exit;
        }

        $etapa = $this->buscarEtapa($id);
        if (!$etapa) {
            $_SESSION['flash_error'] = 'Etapa não encontrada.';
            header('Location: ' . APP_URL . '/producao');
            exit;
        }

        $sets = ['status = ?', 'observacao = ?'];
        $params = [$status, trim($_POST['observacao'] ?? '') ?: $etapa['observacao']];
        if ($status === 'em_producao') {
            $sets[] = 'data_inicio = COALESCE(data_inicio, NOW())';
            db()->prepare("UPDATE ordem_servicos SET status = 'em_producao', data_inicio = COALESCE(data_inicio, NOW()), updated_at = NOW() WHERE id = ? AND status = 'aberta'")->execute([$etapa['ordem_servico_id']]);
        }
        if ($status === 'concluida') {
            $sets[] = 'data_fim = NOW()';
        }
        $sets[] = 'updated_at = NOW()';
        $params[] = $id;

        try {
            db()->prepare('UPDATE ordem_servico_etapas SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);
            $this->sincronizarConclusao((int)$etapa['ordem_servico_id']);
            Auth::registrarAuditoria('ordem_servico_etapas', 'status_' . $status, (int)$id);
            $_SESSION['flash_success'] = 'Etapa atualizada.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao atualizar etapa.';
        }

        header('Location: ' . APP_URL . '/producao/' . $etapa['ordem_servico_id']);
        exit;
    }

    private function salvar(?string $id = null): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/producao' . ($id ? '/' . $id . '/editar' : '/novo'));
            exit;
        }

        $dados = $this->extrairDados();
        $itens = $this->extrairItens();
        if ($dados['titulo'] === '') {
            $_SESSION['flash_error'] = 'Título da OS é obrigatório.';
            header('Location: ' . APP_URL . '/producao' . ($id ? '/' . $id . '/editar' : '/novo'));
            exit;
        }
        if (empty($itens)) {
            $_SESSION['flash_error'] = 'Inclua pelo menos um item na OS.';
            header('Location: ' . APP_URL . '/producao' . ($id ? '/' . $id . '/editar' : '/novo'));
            exit;
        }

        try {
            $pdo = db();
            $pdo->beginTransaction();

            if ($id) {
                $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($dados)));
                $dados['id'] = $id;
                $pdo->prepare("UPDATE ordem_servicos SET $sets, updated_at = NOW() WHERE id = :id")->execute($dados);
                $ordemId = (int)$id;
                $pdo->prepare("DELETE FROM ordem_servico_etapas WHERE ordem_servico_id = ?")->execute([$ordemId]);
                $pdo->prepare("DELETE FROM ordem_servico_itens WHERE ordem_servico_id = ?")->execute([$ordemId]);
                $acao = 'editar';
            } else {
                $dados['codigo'] = $this->gerarCodigo();
                $colunas = implode(', ', array_keys($dados));
                $placeholders = ':' . implode(', :', array_keys($dados));
                $pdo->prepare("INSERT INTO ordem_servicos ($colunas, created_at) VALUES ($placeholders, NOW())")->execute($dados);
                $ordemId = (int)$pdo->lastInsertId();
                $acao = 'criar';
            }

            $itensSalvos = $this->salvarItens($ordemId, $itens);
            $processos = $this->processosSelecionados($itens);
            $this->salvarEtapas($ordemId, $processos, $dados['data_prometida']);

            if (!empty($dados['orcamento_id'])) {
                $pdo->prepare("UPDATE orcamentos SET status = 'aprovado', aprovado_at = COALESCE(aprovado_at, NOW()), updated_at = NOW() WHERE id = ?")->execute([$dados['orcamento_id']]);
            }

            Auth::registrarAuditoria('ordem_servicos', $acao, $ordemId);
            $pdo->commit();
            $_SESSION['flash_success'] = $id ? 'Ordem de serviço atualizada.' : 'Ordem de serviço criada.';
            header('Location: ' . APP_URL . '/producao/' . $ordemId);
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_error'] = 'Erro ao salvar OS: ' . $e->getMessage();
            header('Location: ' . APP_URL . '/producao' . ($id ? '/' . $id . '/editar' : '/novo'));
        }
        exit;
    }

    private function extrairDados(): array
    {
        return [
            'orcamento_id' => !empty($_POST['orcamento_id']) ? (int)$_POST['orcamento_id'] : null,
            'cliente_id' => !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null,
            'responsavel_id' => !empty($_POST['responsavel_id']) ? (int)$_POST['responsavel_id'] : Auth::id(),
            'titulo' => trim($_POST['titulo'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'prioridade' => $_POST['prioridade'] ?? 'media',
            'status' => $_POST['status'] ?? 'aberta',
            'data_entrada' => $_POST['data_entrada'] ?: date('Y-m-d'),
            'data_prometida' => $_POST['data_prometida'] ?: null,
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];
    }

    private function extrairItens(): array
    {
        $nomes = $_POST['item_produto_nome'] ?? [];
        $itens = [];
        foreach ($nomes as $i => $nome) {
            $nome = trim($nome);
            if ($nome === '') {
                continue;
            }
            $largura = $this->numero($_POST['item_largura'][$i] ?? 0);
            $altura = $this->numero($_POST['item_altura'][$i] ?? 0);
            $quantidade = max(0.001, $this->numero($_POST['item_quantidade'][$i] ?? 1));
            $itens[] = [
                'produto_id' => !empty($_POST['item_produto_id'][$i]) ? (int)$_POST['item_produto_id'][$i] : null,
                'orcamento_item_id' => !empty($_POST['item_orcamento_item_id'][$i]) ? (int)$_POST['item_orcamento_item_id'][$i] : null,
                'produto_nome' => $nome,
                'descricao' => trim($_POST['item_descricao'][$i] ?? ''),
                'quantidade' => $quantidade,
                'unidade' => trim($_POST['item_unidade'][$i] ?? 'un'),
                'largura' => $largura,
                'altura' => $altura,
                'area_m2' => $largura > 0 && $altura > 0 ? round($largura * $altura * $quantidade, 3) : 0,
                'material' => trim($_POST['item_material'][$i] ?? ''),
                'acabamento' => trim($_POST['item_acabamento'][$i] ?? ''),
                'arquivo_ref' => trim($_POST['item_arquivo_ref'][$i] ?? ''),
                'status' => $_POST['item_status'][$i] ?? 'pendente',
            ];
        }
        return $itens;
    }

    private function salvarItens(int $ordemId, array $itens): array
    {
        $stmt = db()->prepare(
            "INSERT INTO ordem_servico_itens
             (ordem_servico_id, produto_id, orcamento_item_id, produto_nome, descricao, quantidade, unidade, largura, altura, area_m2, material, acabamento, arquivo_ref, status, created_at)
             VALUES
             (:ordem_servico_id, :produto_id, :orcamento_item_id, :produto_nome, :descricao, :quantidade, :unidade, :largura, :altura, :area_m2, :material, :acabamento, :arquivo_ref, :status, NOW())"
        );
        $salvos = [];
        foreach ($itens as $item) {
            $item['ordem_servico_id'] = $ordemId;
            $stmt->execute($item);
            $item['id'] = (int)db()->lastInsertId();
            $salvos[] = $item;
        }
        return $salvos;
    }

    private function processosSelecionados(array $itens): array
    {
        $ids = array_filter(array_map('intval', $_POST['processos'] ?? []));
        if (empty($ids)) {
            $produtoIds = array_filter(array_unique(array_column($itens, 'produto_id')));
            if (!empty($produtoIds)) {
                $placeholders = implode(',', array_fill(0, count($produtoIds), '?'));
                $stmt = db()->prepare(
                    "SELECT DISTINCT pr.*
                     FROM produto_processos pp
                     JOIN processos_produtivos pr ON pr.id = pp.processo_id
                     WHERE pp.produto_id IN ($placeholders) AND pr.ativo = 1
                     ORDER BY pp.ordem, pr.nome"
                );
                $stmt->execute(array_values($produtoIds));
                $processos = $stmt->fetchAll();
                if (!empty($processos)) {
                    return $processos;
                }
            }
        }

        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = db()->prepare("SELECT * FROM processos_produtivos WHERE id IN ($placeholders) ORDER BY FIELD(id, $placeholders)");
            $stmt->execute(array_merge($ids, $ids));
            return $stmt->fetchAll();
        }

        return [[
            'id' => null,
            'nome' => 'Produção',
            'setor' => 'Produção',
            'checklist' => null,
        ]];
    }

    private function salvarEtapas(int $ordemId, array $processos, ?string $dataPrometida): void
    {
        $stmt = db()->prepare(
            "INSERT INTO ordem_servico_etapas
             (ordem_servico_id, processo_id, nome, setor, ordem, status, prazo, checklist, created_at)
             VALUES (?, ?, ?, ?, ?, 'pendente', ?, ?, NOW())"
        );
        $prazo = $dataPrometida ? $dataPrometida . ' 18:00:00' : null;
        $ordem = 1;
        foreach ($processos as $processo) {
            $stmt->execute([
                $ordemId,
                $processo['id'] ?? null,
                $processo['nome'],
                $processo['setor'] ?? 'Produção',
                $ordem++,
                $prazo,
                $processo['checklist'] ?? null,
            ]);
        }
    }

    private function sincronizarConclusao(int $ordemId): void
    {
        $stmt = db()->prepare("SELECT COUNT(*) FROM ordem_servico_etapas WHERE ordem_servico_id = ? AND status NOT IN ('concluida','cancelada')");
        $stmt->execute([$ordemId]);
        if ((int)$stmt->fetchColumn() === 0) {
            db()->prepare("UPDATE ordem_servicos SET status = 'finalizada', data_finalizacao = COALESCE(data_finalizacao, NOW()), updated_at = NOW() WHERE id = ? AND status NOT IN ('finalizada','cancelada')")->execute([$ordemId]);
            db()->prepare("UPDATE ordem_servico_itens SET status = 'concluido' WHERE ordem_servico_id = ? AND status <> 'cancelado'")->execute([$ordemId]);
        }
    }

    private function contextoFormulario(): array
    {
        return [
            'clientes' => $this->query("SELECT id, nome FROM clientes WHERE status = 'ativo' ORDER BY nome LIMIT 500"),
            'usuarios' => $this->query("SELECT id, nome FROM usuarios WHERE ativo = 1 ORDER BY nome"),
            'produtos' => $this->query("SELECT id, nome, codigo, unidade, largura_padrao, altura_padrao FROM produtos WHERE status = 'ativo' ORDER BY nome"),
            'processos' => $this->query("SELECT * FROM processos_produtivos WHERE ativo = 1 ORDER BY setor, nome"),
            'orcamentos' => $this->query("SELECT id, codigo, titulo FROM orcamentos WHERE status = 'aprovado' ORDER BY aprovado_at DESC, created_at DESC LIMIT 200"),
            'statusLabels' => $this->statusLabels,
            'prioridadeLabels' => $this->prioridadeLabels,
        ];
    }

    private function buscar(string $id): ?array
    {
        try {
            $stmt = db()->prepare(
                "SELECT os.*, c.nome AS cliente_nome, u.nome AS responsavel_nome, o.codigo AS orcamento_codigo
                 FROM ordem_servicos os
                 LEFT JOIN clientes c ON c.id = os.cliente_id
                 LEFT JOIN usuarios u ON u.id = os.responsavel_id
                 LEFT JOIN orcamentos o ON o.id = os.orcamento_id
                 WHERE os.id = ?"
            );
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function buscarEtapa(string $id): ?array
    {
        try {
            $stmt = db()->prepare("SELECT * FROM ordem_servico_etapas WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function itens(string $ordemId): array
    {
        return $this->queryPreparada("SELECT * FROM ordem_servico_itens WHERE ordem_servico_id = ? ORDER BY id", [$ordemId]);
    }

    private function etapas(string $ordemId): array
    {
        return $this->queryPreparada(
            "SELECT e.*, u.nome AS responsavel_nome
             FROM ordem_servico_etapas e
             LEFT JOIN usuarios u ON u.id = e.responsavel_id
             WHERE e.ordem_servico_id = ?
             ORDER BY e.ordem, e.id",
            [$ordemId]
        );
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

    private function itensDoOrcamento(string $orcamentoId): array
    {
        $itens = $this->queryPreparada("SELECT * FROM orcamento_itens WHERE orcamento_id = ? ORDER BY id", [$orcamentoId]);
        return array_map(fn($item) => [
            'produto_id' => null,
            'orcamento_item_id' => $item['id'],
            'produto_nome' => $item['produto_nome'],
            'descricao' => $item['descricao'],
            'quantidade' => $item['quantidade'],
            'unidade' => $item['unidade'],
            'largura' => $item['largura'],
            'altura' => $item['altura'],
            'area_m2' => $item['area_m2'],
            'material' => '',
            'acabamento' => '',
            'arquivo_ref' => '',
            'status' => 'pendente',
        ], $itens);
    }

    private function gerarCodigo(): string
    {
        $prefixo = 'OS-' . date('Ym') . '-';
        try {
            $stmt = db()->prepare("SELECT COUNT(*) FROM ordem_servicos WHERE codigo LIKE ?");
            $stmt->execute([$prefixo . '%']);
            $seq = (int)$stmt->fetchColumn() + 1;
        } catch (\Exception $e) {
            $seq = 1;
        }
        return $prefixo . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
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

    private function itemVazio(): array
    {
        return [
            'produto_id' => null,
            'orcamento_item_id' => null,
            'produto_nome' => '',
            'descricao' => '',
            'quantidade' => 1,
            'unidade' => 'un',
            'largura' => 0,
            'altura' => 0,
            'area_m2' => 0,
            'material' => '',
            'acabamento' => '',
            'arquivo_ref' => '',
            'status' => 'pendente',
        ];
    }
}
