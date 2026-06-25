<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class EstoqueController
{
    private array $statusLabels = [
        'ativo' => 'Ativo',
        'inativo' => 'Inativo',
    ];

    private array $tipoMovLabels = [
        'entrada' => 'Entrada',
        'saida' => 'Saída',
        'ajuste' => 'Ajuste',
        'reserva' => 'Reserva para OS',
        'baixa_reserva' => 'Baixa de reserva',
        'cancelamento_reserva' => 'Cancelar reserva',
    ];

    public function __construct()
    {
        AuthMiddleware::requer('estoque');
    }

    public function index(): void
    {
        try {
            $materiais = db()->query(
                "SELECT m.*,
                    (m.estoque_atual - m.estoque_reservado) AS estoque_disponivel,
                    (SELECT MAX(created_at) FROM estoque_movimentacoes em WHERE em.material_id = m.id) AS ultima_movimentacao
                 FROM materiais m
                 ORDER BY FIELD(m.status, 'ativo','inativo'), m.nome"
            )->fetchAll();
        } catch (\Exception $e) {
            $materiais = [];
        }

        $movimentacoes = $this->query(
            "SELECT em.*, m.nome AS material_nome, os.codigo AS os_codigo, u.nome AS usuario_nome
             FROM estoque_movimentacoes em
             JOIN materiais m ON m.id = em.material_id
             LEFT JOIN ordem_servicos os ON os.id = em.ordem_servico_id
             LEFT JOIN usuarios u ON u.id = em.usuario_id
             ORDER BY em.created_at DESC
             LIMIT 20"
        );

        $titulo = 'Estoque';
        $subtitulo = 'Materiais, saldos, reservas para OS e movimentações';
        $headerActions = '<a href="' . APP_URL . '/estoque/novo" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Novo Material</a>';
        $statusLabels = $this->statusLabels;
        $tipoMovLabels = $this->tipoMovLabels;

        ob_start();
        require APP_PATH . '/Views/estoque/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function novo(): void
    {
        $material = [
            'unidade' => 'un',
            'custo_atual' => 0,
            'estoque_atual' => 0,
            'estoque_minimo' => 0,
            'estoque_reservado' => 0,
            'status' => 'ativo',
        ];
        $titulo = 'Novo Material';
        $subtitulo = 'Cadastro de insumo para produção e compras';
        $breadcrumbs = [['label' => 'Estoque', 'url' => '/estoque'], ['label' => 'Novo', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/estoque/form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criar(): void
    {
        $this->salvar();
    }

    public function ver(string $id): void
    {
        $material = $this->buscar($id);
        if (!$material) {
            $_SESSION['flash_error'] = 'Material não encontrado.';
            header('Location: ' . APP_URL . '/estoque');
            exit;
        }

        $movimentacoes = $this->movimentacoes($id);
        $ordens = $this->ordensAtivas();
        $statusLabels = $this->statusLabels;
        $tipoMovLabels = $this->tipoMovLabels;
        $titulo = $material['nome'];
        $subtitulo = 'Ficha de saldo, reserva e histórico do material';
        $breadcrumbs = [['label' => 'Estoque', 'url' => '/estoque'], ['label' => $material['nome'], 'url' => '']];
        $headerActions = '<a href="' . APP_URL . '/estoque/' . $id . '/editar" class="btn btn-secondary btn-sm"><i class="bi bi-pencil"></i> Editar</a>';

        ob_start();
        require APP_PATH . '/Views/estoque/show.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function editar(string $id): void
    {
        $material = $this->buscar($id);
        if (!$material) {
            $_SESSION['flash_error'] = 'Material não encontrado.';
            header('Location: ' . APP_URL . '/estoque');
            exit;
        }

        $titulo = 'Editar Material';
        $subtitulo = ($material['codigo'] ?: 'Material') . ' - ' . $material['nome'];
        $breadcrumbs = [['label' => 'Estoque', 'url' => '/estoque'], ['label' => $material['nome'], 'url' => '/estoque/' . $id], ['label' => 'Editar', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/estoque/form.php';
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
            header('Location: ' . APP_URL . '/estoque');
            exit;
        }

        if (!Auth::temPerfil('administrador')) {
            $_SESSION['flash_error'] = 'Apenas administradores podem excluir materiais.';
            header('Location: ' . APP_URL . '/estoque');
            exit;
        }

        $material = $this->buscar($id);
        if (!$material) {
            $_SESSION['flash_error'] = 'Material não encontrado.';
            header('Location: ' . APP_URL . '/estoque');
            exit;
        }

        if ((float)$material['estoque_reservado'] > 0) {
            $_SESSION['flash_error'] = 'Não é possível excluir: material possui estoque reservado em ordens abertas.';
            header('Location: ' . APP_URL . '/estoque');
            exit;
        }

        try {
            if ($material['status'] === 'inativo') {
                // Segunda fase: exclusão permanente
                $em_compras = db()->prepare("SELECT COUNT(*) FROM compra_itens WHERE material_id = ?")->execute([$id]);
                $stmt = db()->prepare("SELECT COUNT(*) FROM compra_itens WHERE material_id = ?");
                $stmt->execute([$id]);
                if ((int)$stmt->fetchColumn() > 0) {
                    $_SESSION['flash_error'] = 'Não é possível excluir: material está vinculado a ordens de compra.';
                    header('Location: ' . APP_URL . '/estoque');
                    exit;
                }
                db()->prepare("DELETE FROM materiais WHERE id = ?")->execute([$id]);
                Auth::registrarAuditoria('materiais', 'excluir_permanente', (int)$id);
                $_SESSION['flash_success'] = 'Material excluído permanentemente.';
            } else {
                // Primeira fase: inativar
                db()->prepare("UPDATE materiais SET status = 'inativo', updated_at = NOW() WHERE id = ?")->execute([$id]);
                Auth::registrarAuditoria('materiais', 'inativar', (int)$id);
                $_SESSION['flash_success'] = 'Material inativado. Para excluir permanentemente, clique em excluir novamente.';
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao excluir material.';
        }

        header('Location: ' . APP_URL . '/estoque');
        exit;
    }

    public function movimentar(string $id): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/estoque/' . $id);
            exit;
        }

        $material = $this->buscar($id);
        if (!$material) {
            $_SESSION['flash_error'] = 'Material não encontrado.';
            header('Location: ' . APP_URL . '/estoque');
            exit;
        }

        $tipo = $_POST['tipo'] ?? '';
        if (!isset($this->tipoMovLabels[$tipo])) {
            $_SESSION['flash_error'] = 'Tipo de movimentação inválido.';
            header('Location: ' . APP_URL . '/estoque/' . $id);
            exit;
        }

        $quantidade = $this->numero($_POST['quantidade'] ?? 0);
        if ($quantidade <= 0 && $tipo !== 'ajuste') {
            $_SESSION['flash_error'] = 'Quantidade deve ser maior que zero.';
            header('Location: ' . APP_URL . '/estoque/' . $id);
            exit;
        }

        try {
            $pdo = db();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT * FROM materiais WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $materialAtual = $stmt->fetch();
            if (!$materialAtual) {
                throw new \Exception('Material não encontrado.');
            }

            $saldoAnterior = (float)$materialAtual['estoque_atual'];
            $reservadoAnterior = (float)$materialAtual['estoque_reservado'];
            $saldoPosterior = $saldoAnterior;
            $reservadoPosterior = $reservadoAnterior;
            $custoUnitario = $this->numero($_POST['custo_unitario'] ?? $materialAtual['custo_atual']);

            if ($tipo === 'entrada') {
                $saldoPosterior = $saldoAnterior + $quantidade;
                if ($custoUnitario > 0) {
                    $custoAtual = (float)$materialAtual['custo_atual'];
                    $valorAtual = $saldoAnterior * $custoAtual;
                    $valorEntrada = $quantidade * $custoUnitario;
                    $novoCusto = $saldoPosterior > 0 ? round(($valorAtual + $valorEntrada) / $saldoPosterior, 2) : $custoUnitario;
                    $pdo->prepare("UPDATE materiais SET custo_atual = ? WHERE id = ?")->execute([$novoCusto, $id]);
                }
            } elseif ($tipo === 'saida') {
                $saldoPosterior = $saldoAnterior - $quantidade;
            } elseif ($tipo === 'ajuste') {
                $saldoPosterior = $quantidade;
            } elseif ($tipo === 'reserva') {
                $reservadoPosterior = $reservadoAnterior + $quantidade;
            } elseif ($tipo === 'baixa_reserva') {
                $saldoPosterior = $saldoAnterior - $quantidade;
                $reservadoPosterior = max(0, $reservadoAnterior - $quantidade);
            } elseif ($tipo === 'cancelamento_reserva') {
                $reservadoPosterior = max(0, $reservadoAnterior - $quantidade);
            }

            if ($saldoPosterior < 0) {
                throw new \Exception('Saldo insuficiente para esta movimentação.');
            }
            if ($reservadoPosterior > $saldoPosterior && $tipo === 'reserva') {
                throw new \Exception('Reserva maior que o saldo em estoque.');
            }

            $pdo->prepare("UPDATE materiais SET estoque_atual = ?, estoque_reservado = ?, updated_at = NOW() WHERE id = ?")
                ->execute([$saldoPosterior, $reservadoPosterior, $id]);

            $stmt = $pdo->prepare(
                "INSERT INTO estoque_movimentacoes
                 (material_id, ordem_servico_id, usuario_id, tipo, origem, quantidade, custo_unitario, saldo_anterior, saldo_posterior, reservado_anterior, reservado_posterior, observacao, created_at)
                 VALUES
                 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                $id,
                !empty($_POST['ordem_servico_id']) ? (int)$_POST['ordem_servico_id'] : null,
                Auth::id(),
                $tipo,
                trim($_POST['origem'] ?? ''),
                $quantidade,
                $custoUnitario,
                $saldoAnterior,
                $saldoPosterior,
                $reservadoAnterior,
                $reservadoPosterior,
                trim($_POST['observacao'] ?? ''),
            ]);

            Auth::registrarAuditoria('estoque_movimentacoes', $tipo, (int)$pdo->lastInsertId());
            $pdo->commit();
            $_SESSION['flash_success'] = 'Movimentação registrada.';
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_error'] = 'Erro no estoque: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/estoque/' . $id);
        exit;
    }

    private function salvar(?string $id = null): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/estoque' . ($id ? '/' . $id . '/editar' : '/novo'));
            exit;
        }

        $dados = $this->extrairDados();
        if ($dados['nome'] === '') {
            $_SESSION['flash_error'] = 'Nome do material é obrigatório.';
            header('Location: ' . APP_URL . '/estoque' . ($id ? '/' . $id . '/editar' : '/novo'));
            exit;
        }

        if ($dados['codigo'] === '') {
            $dados['codigo'] = $this->gerarCodigo();
        }

        try {
            if ($id) {
                unset($dados['estoque_atual'], $dados['estoque_reservado']);
                $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($dados)));
                $dados['id'] = $id;
                db()->prepare("UPDATE materiais SET $sets, updated_at = NOW() WHERE id = :id")->execute($dados);
                Auth::registrarAuditoria('materiais', 'editar', (int)$id);
                $_SESSION['flash_success'] = 'Material atualizado.';
                header('Location: ' . APP_URL . '/estoque/' . $id);
            } else {
                $colunas = implode(', ', array_keys($dados));
                $placeholders = ':' . implode(', :', array_keys($dados));
                db()->prepare("INSERT INTO materiais ($colunas, created_at) VALUES ($placeholders, NOW())")->execute($dados);
                $materialId = (int)db()->lastInsertId();
                Auth::registrarAuditoria('materiais', 'criar', $materialId);
                $_SESSION['flash_success'] = 'Material cadastrado.';
                header('Location: ' . APP_URL . '/estoque/' . $materialId);
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao salvar material: ' . $e->getMessage();
            header('Location: ' . APP_URL . '/estoque' . ($id ? '/' . $id . '/editar' : '/novo'));
        }
        exit;
    }

    private function extrairDados(): array
    {
        return [
            'codigo' => trim($_POST['codigo'] ?? ''),
            'nome' => trim($_POST['nome'] ?? ''),
            'categoria' => trim($_POST['categoria'] ?? ''),
            'unidade' => trim($_POST['unidade'] ?? 'un'),
            'fornecedor' => trim($_POST['fornecedor'] ?? ''),
            'custo_atual' => $this->numero($_POST['custo_atual'] ?? 0),
            'estoque_atual' => $this->numero($_POST['estoque_atual'] ?? 0),
            'estoque_minimo' => $this->numero($_POST['estoque_minimo'] ?? 0),
            'estoque_reservado' => $this->numero($_POST['estoque_reservado'] ?? 0),
            'localizacao' => trim($_POST['localizacao'] ?? ''),
            'status' => $_POST['status'] ?? 'ativo',
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];
    }

    private function buscar(string $id): ?array
    {
        try {
            $stmt = db()->prepare("SELECT *, (estoque_atual - estoque_reservado) AS estoque_disponivel FROM materiais WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function movimentacoes(string $id): array
    {
        return $this->queryPreparada(
            "SELECT em.*, os.codigo AS os_codigo, u.nome AS usuario_nome
             FROM estoque_movimentacoes em
             LEFT JOIN ordem_servicos os ON os.id = em.ordem_servico_id
             LEFT JOIN usuarios u ON u.id = em.usuario_id
             WHERE em.material_id = ?
             ORDER BY em.created_at DESC, em.id DESC
             LIMIT 100",
            [$id]
        );
    }

    private function ordensAtivas(): array
    {
        return $this->query("SELECT id, codigo, titulo FROM ordem_servicos WHERE status NOT IN ('finalizada','cancelada') ORDER BY data_prometida IS NULL, data_prometida, created_at DESC LIMIT 200");
    }

    private function gerarCodigo(): string
    {
        $prefixo = 'MAT-' . date('Ym') . '-';
        try {
            $stmt = db()->prepare("SELECT COUNT(*) FROM materiais WHERE codigo LIKE ?");
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

    public function correcao(): void
    {
        if (!Auth::temPerfil('administrador')) {
            $_SESSION['flash_error'] = 'Acesso restrito a administradores.';
            header('Location: ' . APP_URL . '/estoque');
            exit;
        }

        try {
            $materiais = db()->query(
                "SELECT m.*, (m.estoque_atual - m.estoque_reservado) AS estoque_disponivel
                 FROM materiais m WHERE m.status = 'ativo' ORDER BY m.nome"
            )->fetchAll();
        } catch (\Exception $e) {
            $materiais = [];
        }

        $titulo = 'Correção de Estoque';
        $subtitulo = 'Ajuste de saldo e custo dos materiais — somente administradores';
        $breadcrumbs = [['label' => 'Estoque', 'url' => '/estoque'], ['label' => 'Correção', 'url' => '']];
        $csrfToken = Auth::csrfToken();

        ob_start();
        require APP_PATH . '/Views/estoque/correcao.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function salvarCorrecao(): void
    {
        if (!Auth::temPerfil('administrador')) {
            $_SESSION['flash_error'] = 'Acesso restrito a administradores.';
            header('Location: ' . APP_URL . '/estoque');
            exit;
        }

        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/estoque/correcao');
            exit;
        }

        $ids = $_POST['material_id'] ?? [];
        $quantidades = $_POST['estoque_novo'] ?? [];
        $custos = $_POST['custo_novo'] ?? [];
        $justificativas = $_POST['justificativa'] ?? [];
        $alterados = 0;

        try {
            $pdo = db();
            $pdo->beginTransaction();

            foreach ($ids as $i => $id) {
                $id = (int)$id;
                if ($id <= 0) continue;

                $novaQtd = isset($quantidades[$i]) && $quantidades[$i] !== '' ? $this->numero($quantidades[$i]) : null;
                $novoCusto = isset($custos[$i]) && $custos[$i] !== '' ? $this->numero($custos[$i]) : null;
                $justificativa = trim($justificativas[$i] ?? 'Correção administrativa');

                if ($novaQtd === null && $novoCusto === null) continue;

                $stmt = $pdo->prepare("SELECT * FROM materiais WHERE id = ? FOR UPDATE");
                $stmt->execute([$id]);
                $material = $stmt->fetch();
                if (!$material) continue;

                $saldoAnterior = (float)$material['estoque_atual'];
                $saldoPosterior = $novaQtd !== null ? $novaQtd : $saldoAnterior;
                $custoFinal = $novoCusto !== null ? $novoCusto : (float)$material['custo_atual'];

                $updates = ['updated_at = NOW()'];
                $params = [];
                if ($novaQtd !== null) {
                    $updates[] = 'estoque_atual = ?';
                    $params[] = $saldoPosterior;
                }
                if ($novoCusto !== null) {
                    $updates[] = 'custo_atual = ?';
                    $params[] = $custoFinal;
                }
                $params[] = $id;
                $pdo->prepare('UPDATE materiais SET ' . implode(', ', $updates) . ' WHERE id = ?')->execute($params);

                if ($novaQtd !== null) {
                    $pdo->prepare(
                        "INSERT INTO estoque_movimentacoes
                         (material_id, usuario_id, tipo, origem, quantidade, custo_unitario, saldo_anterior, saldo_posterior, reservado_anterior, reservado_posterior, observacao, created_at)
                         VALUES (?, ?, 'ajuste', 'Correção de estoque', ?, ?, ?, ?, ?, ?, ?, NOW())"
                    )->execute([
                        $id,
                        Auth::id(),
                        abs($saldoPosterior - $saldoAnterior),
                        $custoFinal,
                        $saldoAnterior,
                        $saldoPosterior,
                        (float)$material['estoque_reservado'],
                        (float)$material['estoque_reservado'],
                        $justificativa,
                    ]);
                }

                Auth::registrarAuditoria('materiais', 'correcao_estoque', $id, ['saldo' => $saldoAnterior, 'custo' => $material['custo_atual']], ['saldo' => $saldoPosterior, 'custo' => $custoFinal]);
                $alterados++;
            }

            $pdo->commit();
            $_SESSION['flash_success'] = "Correção aplicada a {$alterados} material(is).";
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['flash_error'] = 'Erro na correção: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/estoque/correcao');
        exit;
    }
}
