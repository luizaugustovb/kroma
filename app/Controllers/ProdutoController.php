<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class ProdutoController
{
    private array $statusLabels = [
        'ativo' => 'Ativo',
        'inativo' => 'Inativo',
        'em_revisao' => 'Em Revisão',
    ];

    private array $tipoLabels = [
        'produto' => 'Produto',
        'servico' => 'Serviço',
        'composto' => 'Produto Composto',
        'locacao' => 'Locação',
    ];

    public function __construct()
    {
        AuthMiddleware::requer('produtos');
    }

    public function index(): void
    {
        try {
            $produtos = db()->query(
                "SELECT p.*, c.nome AS categoria_nome,
                    (SELECT COUNT(*) FROM produto_variacoes v WHERE v.produto_id = p.id) AS total_variacoes,
                    (SELECT COUNT(*) FROM produto_processos pp WHERE pp.produto_id = p.id) AS total_processos
                 FROM produtos p
                 LEFT JOIN categorias_produtos c ON c.id = p.categoria_id
                 ORDER BY p.updated_at DESC, p.nome"
            )->fetchAll();
        } catch (\Exception $e) {
            $produtos = [];
        }

        $titulo = 'Produtos';
        $subtitulo = 'Cadastro base, variações, composição, processos e precificação';
        $headerActions = '<a href="' . APP_URL . '/produtos/novo" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Novo Produto</a>';
        $statusLabels = $this->statusLabels;
        $tipoLabels = $this->tipoLabels;

        ob_start();
        require APP_PATH . '/Views/produtos/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function novo(): void
    {
        $produto = [
            'tipo' => 'produto',
            'unidade' => 'un',
            'desperdicio_percent' => 5,
            'margem_percent' => 35,
            'impostos_percent' => 8,
            'comissao_percent' => 5,
            'status' => 'ativo',
        ];
        $variacoes = [$this->variacaoVazia()];
        $produtoProcessos = [];
        $produtoAcabamentos = [];
        $produtoAcabamentosObrigatorios = [];
        $contexto = $this->contextoFormulario();
        $titulo = 'Novo Produto';
        $subtitulo = 'Cadastre produto base, composição e processos produtivos';
        $breadcrumbs = [['label' => 'Produtos', 'url' => '/produtos'], ['label' => 'Novo', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/produtos/form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criar(): void
    {
        $this->salvar();
    }

    public function ver(string $id): void
    {
        $produto = $this->buscar($id);
        if (!$produto) {
            $_SESSION['flash_error'] = 'Produto não encontrado.';
            header('Location: ' . APP_URL . '/produtos');
            exit;
        }

        $variacoes = $this->variacoes($id);
        $processos = $this->processosDoProduto($id);
        $acabamentos = $this->acabamentosDoProduto($id);
        $statusLabels = $this->statusLabels;
        $tipoLabels = $this->tipoLabels;
        $titulo = $produto['nome'];
        $subtitulo = 'Ficha técnica e composição do produto';
        $breadcrumbs = [['label' => 'Produtos', 'url' => '/produtos'], ['label' => $produto['nome'], 'url' => '']];
        $headerActions = '<a href="' . APP_URL . '/produtos/' . $id . '/editar" class="btn btn-secondary btn-sm"><i class="bi bi-pencil"></i> Editar</a>';

        ob_start();
        require APP_PATH . '/Views/produtos/show.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function editar(string $id): void
    {
        $produto = $this->buscar($id);
        if (!$produto) {
            $_SESSION['flash_error'] = 'Produto não encontrado.';
            header('Location: ' . APP_URL . '/produtos');
            exit;
        }

        $variacoes = $this->variacoes($id) ?: [$this->variacaoVazia()];
        $acabamentosProduto = $this->acabamentosDoProduto($id);
        $produtoProcessos = array_column($this->processosDoProduto($id), 'processo_id');
        $produtoAcabamentos = array_column($acabamentosProduto, 'acabamento_id');
        $produtoAcabamentosObrigatorios = array_column(
            array_filter($acabamentosProduto, fn($a) => (int)($a['obrigatorio'] ?? 0) === 1),
            'acabamento_id'
        );
        $contexto = $this->contextoFormulario();
        $titulo = 'Editar Produto';
        $subtitulo = $produto['codigo'] . ' - ' . $produto['nome'];
        $breadcrumbs = [['label' => 'Produtos', 'url' => '/produtos'], ['label' => $produto['nome'], 'url' => '/produtos/' . $id], ['label' => 'Editar', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/produtos/form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function atualizar(string $id): void
    {
        $this->salvar($id);
    }

    public function excluir(string $id): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/produtos');
            exit;
        }

        try {
            db()->prepare("UPDATE produtos SET status = 'inativo', updated_at = NOW() WHERE id = ?")->execute([$id]);
            Auth::registrarAuditoria('produtos', 'excluir', (int)$id);
            $_SESSION['flash_success'] = 'Produto inativado.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao inativar produto.';
        }

        header('Location: ' . APP_URL . '/produtos');
        exit;
    }

    public function duplicar(string $id): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/produtos/' . $id);
            exit;
        }

        $produto = $this->buscar($id);
        if (!$produto) {
            $_SESSION['flash_error'] = 'Produto não encontrado.';
            header('Location: ' . APP_URL . '/produtos');
            exit;
        }

        try {
            $pdo = db();
            $pdo->beginTransaction();
            $novo = $produto;
            unset($novo['id'], $novo['categoria_nome']);
            $novo['codigo'] = $this->gerarCodigo();
            $novo['nome'] = $produto['nome'] . ' - Cópia';
            $colunas = array_keys($novo);
            $colunas = array_filter($colunas, fn($c) => !in_array($c, ['created_at','updated_at'], true));
            $sql = "INSERT INTO produtos (" . implode(', ', $colunas) . ", created_at) VALUES (:" . implode(', :', $colunas) . ", NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_intersect_key($novo, array_flip($colunas)));
            $novoId = (int)$pdo->lastInsertId();

            foreach ($this->variacoes($id) as $v) {
                unset($v['id'], $v['created_at']);
                $v['produto_id'] = $novoId;
                $this->insertAssociativo('produto_variacoes', $v);
            }
            foreach ($this->processosDoProduto($id) as $p) {
                $this->insertAssociativo('produto_processos', [
                    'produto_id' => $novoId,
                    'processo_id' => $p['processo_id'],
                    'ordem' => $p['ordem'],
                    'tempo_min' => $p['tempo_min'],
                    'observacao' => $p['observacao'],
                ]);
            }
            foreach ($this->acabamentosDoProduto($id) as $a) {
                $this->insertAssociativo('produto_acabamentos', [
                    'produto_id' => $novoId,
                    'acabamento_id' => $a['acabamento_id'],
                    'obrigatorio' => $a['obrigatorio'],
                ]);
            }
            $pdo->commit();
            $_SESSION['flash_success'] = 'Produto duplicado com sucesso.';
            header('Location: ' . APP_URL . '/produtos/' . $novoId . '/editar');
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_error'] = 'Erro ao duplicar produto: ' . $e->getMessage();
            header('Location: ' . APP_URL . '/produtos/' . $id);
        }
        exit;
    }

    private function salvar(?string $id = null): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/produtos' . ($id ? '/' . $id . '/editar' : '/novo'));
            exit;
        }

        $dados = $this->extrairDados();
        if ($dados['nome'] === '') {
            $_SESSION['flash_error'] = 'Nome do produto é obrigatório.';
            header('Location: ' . APP_URL . '/produtos' . ($id ? '/' . $id . '/editar' : '/novo'));
            exit;
        }

        if ($dados['codigo'] === '') {
            $dados['codigo'] = $this->gerarCodigo();
        }

        $dados = array_merge($dados, $this->calcularPreco($dados));
        $variacoes = $this->extrairVariacoes();
        $processos = $_POST['processos'] ?? [];
        $acabamentos = $_POST['acabamentos'] ?? [];

        try {
            $pdo = db();
            $pdo->beginTransaction();

            if ($id) {
                $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($dados)));
                $dados['id'] = $id;
                $pdo->prepare("UPDATE produtos SET $sets, updated_at = NOW() WHERE id = :id")->execute($dados);
                $produtoId = (int)$id;
                $pdo->prepare("DELETE FROM produto_variacoes WHERE produto_id = ?")->execute([$produtoId]);
                $pdo->prepare("DELETE FROM produto_processos WHERE produto_id = ?")->execute([$produtoId]);
                $pdo->prepare("DELETE FROM produto_acabamentos WHERE produto_id = ?")->execute([$produtoId]);
                $acao = 'editar';
            } else {
                $colunas = implode(', ', array_keys($dados));
                $placeholders = ':' . implode(', :', array_keys($dados));
                $pdo->prepare("INSERT INTO produtos ($colunas, created_at) VALUES ($placeholders, NOW())")->execute($dados);
                $produtoId = (int)$pdo->lastInsertId();
                $acao = 'criar';
            }

            foreach ($variacoes as $variacao) {
                $variacao['produto_id'] = $produtoId;
                $this->insertAssociativo('produto_variacoes', $variacao);
            }
            $ordem = 1;
            foreach ($processos as $processoId) {
                if ((int)$processoId <= 0) continue;
                $this->insertAssociativo('produto_processos', [
                    'produto_id' => $produtoId,
                    'processo_id' => (int)$processoId,
                    'ordem' => $ordem++,
                    'tempo_min' => 0,
                    'observacao' => null,
                ]);
            }
            foreach ($acabamentos as $acabamentoId) {
                if ((int)$acabamentoId <= 0) continue;
                $this->insertAssociativo('produto_acabamentos', [
                    'produto_id' => $produtoId,
                    'acabamento_id' => (int)$acabamentoId,
                    'obrigatorio' => in_array((string)$acabamentoId, $_POST['acabamentos_obrigatorios'] ?? [], true) ? 1 : 0,
                ]);
            }

            Auth::registrarAuditoria('produtos', $acao, $produtoId);
            $pdo->commit();
            $_SESSION['flash_success'] = $id ? 'Produto atualizado.' : 'Produto cadastrado.';
            header('Location: ' . APP_URL . '/produtos/' . $produtoId);
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_error'] = 'Erro ao salvar produto: ' . $e->getMessage();
            header('Location: ' . APP_URL . '/produtos' . ($id ? '/' . $id . '/editar' : '/novo'));
        }
        exit;
    }

    private function extrairDados(): array
    {
        return [
            'categoria_id' => !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null,
            'codigo' => trim($_POST['codigo'] ?? ''),
            'nome' => trim($_POST['nome'] ?? ''),
            'tipo' => $_POST['tipo'] ?? 'produto',
            'unidade' => trim($_POST['unidade'] ?? 'un'),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'descricao_ia' => trim($_POST['descricao_ia'] ?? ''),
            'questionario' => trim($_POST['questionario'] ?? ''),
            'campos_obrigatorios' => trim($_POST['campos_obrigatorios'] ?? ''),
            'largura_padrao' => $this->numero($_POST['largura_padrao'] ?? 0),
            'altura_padrao' => $this->numero($_POST['altura_padrao'] ?? 0),
            'custo_material' => $this->numero($_POST['custo_material'] ?? 0),
            'custo_tinta' => $this->numero($_POST['custo_tinta'] ?? 0),
            'custo_acabamento' => $this->numero($_POST['custo_acabamento'] ?? 0),
            'custo_mao_obra' => $this->numero($_POST['custo_mao_obra'] ?? 0),
            'custo_maquina' => $this->numero($_POST['custo_maquina'] ?? 0),
            'custo_terceiros' => $this->numero($_POST['custo_terceiros'] ?? 0),
            'desperdicio_percent' => $this->numero($_POST['desperdicio_percent'] ?? 5),
            'margem_percent' => $this->numero($_POST['margem_percent'] ?? 35),
            'impostos_percent' => $this->numero($_POST['impostos_percent'] ?? 8),
            'comissao_percent' => $this->numero($_POST['comissao_percent'] ?? 5),
            'prioridade_8020' => isset($_POST['prioridade_8020']) ? 1 : 0,
            'perecivel' => isset($_POST['perecivel']) ? 1 : 0,
            'validade_dias' => (int)($_POST['validade_dias'] ?? 0),
            'status' => $_POST['status'] ?? 'ativo',
        ];
    }

    private function extrairVariacoes(): array
    {
        $nomes = $_POST['variacao_nome'] ?? [];
        $variacoes = [];
        foreach ($nomes as $i => $nome) {
            $nome = trim($nome);
            if ($nome === '') continue;
            $variacoes[] = [
                'nome' => $nome,
                'sku' => trim($_POST['variacao_sku'][$i] ?? ''),
                'unidade' => trim($_POST['variacao_unidade'][$i] ?? ''),
                'largura' => $this->numero($_POST['variacao_largura'][$i] ?? 0),
                'altura' => $this->numero($_POST['variacao_altura'][$i] ?? 0),
                'custo_extra' => $this->numero($_POST['variacao_custo_extra'][$i] ?? 0),
                'preco_extra' => $this->numero($_POST['variacao_preco_extra'][$i] ?? 0),
                'ativo' => 1,
            ];
        }
        return $variacoes;
    }

    private function calcularPreco(array $dados): array
    {
        $custo = $dados['custo_material'] + $dados['custo_tinta'] + $dados['custo_acabamento'] + $dados['custo_mao_obra'] + $dados['custo_maquina'] + $dados['custo_terceiros'];
        $custoComDesperdicio = $custo * (1 + ($dados['desperdicio_percent'] / 100));
        $precoMinimo = $custoComDesperdicio * (1 + (($dados['impostos_percent'] + $dados['comissao_percent']) / 100));
        $precoBase = $custoComDesperdicio * (1 + (($dados['margem_percent'] + $dados['impostos_percent'] + $dados['comissao_percent']) / 100));
        return [
            'preco_minimo' => round($precoMinimo, 2),
            'preco_base' => round($precoBase, 2),
        ];
    }

    private function contextoFormulario(): array
    {
        return [
            'categorias' => $this->query("SELECT * FROM categorias_produtos WHERE ativo = 1 ORDER BY ordem, nome"),
            'processos' => $this->query("SELECT * FROM processos_produtivos WHERE ativo = 1 ORDER BY setor, nome"),
            'acabamentos' => $this->query("SELECT * FROM acabamentos WHERE ativo = 1 ORDER BY nome"),
            'tipoLabels' => $this->tipoLabels,
            'statusLabels' => $this->statusLabels,
        ];
    }

    private function buscar(string $id): ?array
    {
        try {
            $stmt = db()->prepare(
                "SELECT p.*, c.nome AS categoria_nome
                 FROM produtos p
                 LEFT JOIN categorias_produtos c ON c.id = p.categoria_id
                 WHERE p.id = ?"
            );
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function variacoes(string $produtoId): array
    {
        try {
            $stmt = db()->prepare("SELECT * FROM produto_variacoes WHERE produto_id = ? ORDER BY id");
            $stmt->execute([$produtoId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function processosDoProduto(string $produtoId): array
    {
        try {
            $stmt = db()->prepare(
                "SELECT pp.*, pr.nome, pr.setor, pr.maquina, pr.custo_hora
                 FROM produto_processos pp
                 JOIN processos_produtivos pr ON pr.id = pp.processo_id
                 WHERE pp.produto_id = ?
                 ORDER BY pp.ordem, pr.nome"
            );
            $stmt->execute([$produtoId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function acabamentosDoProduto(string $produtoId): array
    {
        try {
            $stmt = db()->prepare(
                "SELECT pa.*, a.nome, a.custo_base
                 FROM produto_acabamentos pa
                 JOIN acabamentos a ON a.id = pa.acabamento_id
                 WHERE pa.produto_id = ?
                 ORDER BY a.nome"
            );
            $stmt->execute([$produtoId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function gerarCodigo(): string
    {
        $prefixo = 'PROD-' . date('Ym') . '-';
        try {
            $stmt = db()->prepare("SELECT COUNT(*) FROM produtos WHERE codigo LIKE ?");
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

    private function insertAssociativo(string $tabela, array $dados): void
    {
        $colunas = implode(', ', array_keys($dados));
        $placeholders = ':' . implode(', :', array_keys($dados));
        db()->prepare("INSERT INTO $tabela ($colunas) VALUES ($placeholders)")->execute($dados);
    }

    private function numero($valor): float
    {
        if ($valor === null || $valor === '') return 0.0;
        if (is_numeric($valor)) return (float)$valor;
        return (float)str_replace(['.', ','], ['', '.'], preg_replace('/[^0-9,.-]/', '', (string)$valor));
    }

    private function variacaoVazia(): array
    {
        return ['nome' => '', 'sku' => '', 'unidade' => '', 'largura' => 0, 'altura' => 0, 'custo_extra' => 0, 'preco_extra' => 0];
    }
}
