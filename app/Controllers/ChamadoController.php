<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class ChamadoController
{
    private array $statusLabels = [
        'aberto' => 'Aberto',
        'em_andamento' => 'Em andamento',
        'aguardando' => 'Aguardando',
        'concluido' => 'Concluído',
        'cancelado' => 'Cancelado',
    ];

    private array $prioridadeLabels = [
        'baixa' => 'Baixa',
        'media' => 'Média',
        'alta' => 'Alta',
        'urgente' => 'Urgente',
    ];

    public function __construct()
    {
        AuthMiddleware::requer('chamados');
    }

    public function index(): void
    {
        $chamados = $this->query(
            "SELECT c.*, s.nome AS solicitante_nome, r.nome AS responsavel_nome, cli.nome AS cliente_nome,
                    o.codigo AS orcamento_codigo, os.codigo AS os_codigo, co.codigo AS compra_codigo
             FROM chamados c
             LEFT JOIN usuarios s ON s.id = c.solicitante_id
             LEFT JOIN usuarios r ON r.id = c.responsavel_id
             LEFT JOIN clientes cli ON cli.id = c.cliente_id
             LEFT JOIN orcamentos o ON o.id = c.orcamento_id
             LEFT JOIN ordem_servicos os ON os.id = c.ordem_servico_id
             LEFT JOIN compras co ON co.id = c.compra_id
             ORDER BY FIELD(c.status,'aberto','em_andamento','aguardando','concluido','cancelado'),
                      FIELD(c.prioridade,'urgente','alta','media','baixa'),
                      c.prazo IS NULL, c.prazo, c.created_at DESC"
        );

        $resumo = $this->resumo($chamados);
        $porSetor = $this->query(
            "SELECT COALESCE(NULLIF(setor, ''), 'Sem setor') AS label, COUNT(*) AS total
             FROM chamados
             WHERE status NOT IN ('concluido','cancelado')
             GROUP BY label
             ORDER BY total DESC, label
             LIMIT 8"
        );
        $porResponsavel = $this->query(
            "SELECT COALESCE(u.nome, 'Sem responsável') AS label, COUNT(c.id) AS total
             FROM chamados c
             LEFT JOIN usuarios u ON u.id = c.responsavel_id
             WHERE c.status NOT IN ('concluido','cancelado')
             GROUP BY label
             ORDER BY total DESC, label
             LIMIT 8"
        );

        $statusLabels = $this->statusLabels;
        $prioridadeLabels = $this->prioridadeLabels;
        $titulo = 'Chamados Internos';
        $subtitulo = 'Demandas por setor, tarefas, prazos e histórico de atendimento';
        $breadcrumbs = [['label' => 'Chamados', 'url' => '']];
        $headerActions = Auth::pode('chamados.criar')
            ? '<a href="' . APP_URL . '/chamados/novo" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Novo Chamado</a>'
            : '';

        ob_start();
        require APP_PATH . '/Views/chamados/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function novo(): void
    {
        $this->requerAcao('chamados.criar');
        $chamado = [
            'codigo' => '',
            'titulo' => '',
            'descricao' => '',
            'setor' => '',
            'prioridade' => 'media',
            'status' => 'aberto',
            'solicitante_id' => Auth::id(),
            'responsavel_id' => null,
            'cliente_id' => null,
            'orcamento_id' => null,
            'ordem_servico_id' => null,
            'compra_id' => null,
            'prazo' => date('Y-m-d H:i:s', strtotime('+1 day')),
        ];
        $contexto = $this->contextoFormulario();
        $titulo = 'Novo Chamado';
        $subtitulo = 'Cadastro de demanda interna com responsável, prioridade e prazo';
        $breadcrumbs = [['label' => 'Chamados', 'url' => '/chamados'], ['label' => 'Novo', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/chamados/form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criar(): void
    {
        $this->salvar();
    }

    public function ver(string $id): void
    {
        $chamado = $this->buscarChamado($id);
        if (!$chamado) {
            $_SESSION['flash_error'] = 'Chamado não encontrado.';
            header('Location: ' . APP_URL . '/chamados');
            exit;
        }

        $comentarios = $this->comentarios($id);
        $statusLabels = $this->statusLabels;
        $prioridadeLabels = $this->prioridadeLabels;
        $titulo = $chamado['codigo'];
        $subtitulo = $chamado['titulo'];
        $breadcrumbs = [['label' => 'Chamados', 'url' => '/chamados'], ['label' => $chamado['codigo'], 'url' => '']];
        $headerActions = Auth::pode('chamados.editar')
            ? '<a href="' . APP_URL . '/chamados/' . $id . '/editar" class="btn btn-secondary btn-sm"><i class="bi bi-pencil"></i> Editar</a>'
            : '';

        ob_start();
        require APP_PATH . '/Views/chamados/show.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function editar(string $id): void
    {
        $this->requerAcao('chamados.editar');
        $chamado = $this->buscarChamado($id);
        if (!$chamado) {
            $_SESSION['flash_error'] = 'Chamado não encontrado.';
            header('Location: ' . APP_URL . '/chamados');
            exit;
        }

        $contexto = $this->contextoFormulario();
        $titulo = 'Editar Chamado';
        $subtitulo = $chamado['codigo'] . ' - ' . $chamado['titulo'];
        $breadcrumbs = [['label' => 'Chamados', 'url' => '/chamados'], ['label' => $chamado['codigo'], 'url' => '/chamados/' . $id], ['label' => 'Editar', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/chamados/form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function atualizar(string $id): void
    {
        $this->salvar($id);
    }

    public function status(string $id): void
    {
        $this->requerAcao('chamados.editar');
        $this->validarCsrf('/chamados/' . $id);
        $chamado = $this->buscarChamado($id);
        $status = $_POST['status'] ?? '';

        if (!$chamado || !isset($this->statusLabels[$status])) {
            $_SESSION['flash_error'] = 'Status inválido para o chamado.';
            header('Location: ' . APP_URL . '/chamados');
            exit;
        }

        try {
            $dados = [
                'status' => $status,
                'concluido_at' => $status === 'concluido' ? date('Y-m-d H:i:s') : null,
                'id' => $id,
            ];
            db()->prepare(
                "UPDATE chamados
                 SET status = :status, concluido_at = :concluido_at, updated_at = NOW()
                 WHERE id = :id"
            )->execute($dados);

            if ($status !== $chamado['status']) {
                $this->registrarComentario(
                    (int)$id,
                    'status',
                    'Status alterado de ' . $this->statusLabels[$chamado['status']] . ' para ' . $this->statusLabels[$status] . '.',
                    $chamado['status'],
                    $status
                );
            }

            $novo = $this->buscarChamado($id);
            Auth::registrarAuditoria('chamados', 'status_' . $status, (int)$id, $chamado, $novo);
            $_SESSION['flash_success'] = 'Status do chamado atualizado.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao atualizar status: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/chamados/' . $id);
        exit;
    }

    public function comentar(string $id): void
    {
        $this->validarCsrf('/chamados/' . $id);
        $chamado = $this->buscarChamado($id);
        $comentario = trim($_POST['comentario'] ?? '');

        if (!$chamado || $comentario === '') {
            $_SESSION['flash_error'] = 'Informe um comentário válido.';
            header('Location: ' . APP_URL . '/chamados' . ($chamado ? '/' . $id : ''));
            exit;
        }

        try {
            $this->registrarComentario((int)$id, 'comentario', $comentario);
            Auth::registrarAuditoria('chamado_comentarios', 'criar', (int)$id);
            $_SESSION['flash_success'] = 'Comentário adicionado.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao adicionar comentário: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/chamados/' . $id);
        exit;
    }

    private function salvar(?string $id = null): void
    {
        $this->requerAcao($id ? 'chamados.editar' : 'chamados.criar');
        $this->validarCsrf('/chamados' . ($id ? '/' . $id . '/editar' : '/novo'));
        $antigo = $id ? $this->buscarChamado($id) : null;

        $dados = $this->extrairDados();
        if ($dados['titulo'] === '') {
            $_SESSION['flash_error'] = 'Título do chamado é obrigatório.';
            header('Location: ' . APP_URL . '/chamados' . ($id ? '/' . $id . '/editar' : '/novo'));
            exit;
        }

        if (!isset($this->statusLabels[$dados['status']])) {
            $dados['status'] = 'aberto';
        }
        if (!isset($this->prioridadeLabels[$dados['prioridade']])) {
            $dados['prioridade'] = 'media';
        }
        $dados['concluido_at'] = $dados['status'] === 'concluido' ? date('Y-m-d H:i:s') : null;

        try {
            $pdo = db();
            $pdo->beginTransaction();

            if ($id) {
                $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($dados)));
                $dados['id'] = $id;
                $pdo->prepare("UPDATE chamados SET $sets, updated_at = NOW() WHERE id = :id")->execute($dados);
                $chamadoId = (int)$id;
                $acao = 'editar';
            } else {
                $dados['codigo'] = $this->gerarCodigo();
                $colunas = implode(', ', array_keys($dados));
                $placeholders = ':' . implode(', :', array_keys($dados));
                $pdo->prepare("INSERT INTO chamados ($colunas, created_at) VALUES ($placeholders, NOW())")->execute($dados);
                $chamadoId = (int)$pdo->lastInsertId();
                $acao = 'criar';
            }

            $novo = $this->buscarChamado((string)$chamadoId);
            if (!$id) {
                $this->registrarComentario($chamadoId, 'sistema', 'Chamado criado.');
            } elseif ($antigo && $novo && $antigo['status'] !== $novo['status']) {
                $this->registrarComentario(
                    $chamadoId,
                    'status',
                    'Status alterado de ' . $this->statusLabels[$antigo['status']] . ' para ' . $this->statusLabels[$novo['status']] . '.',
                    $antigo['status'],
                    $novo['status']
                );
            }

            Auth::registrarAuditoria('chamados', $acao, $chamadoId, $antigo, $novo);
            $pdo->commit();
            $_SESSION['flash_success'] = $id ? 'Chamado atualizado.' : 'Chamado cadastrado.';
            header('Location: ' . APP_URL . '/chamados/' . $chamadoId);
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_error'] = 'Erro ao salvar chamado: ' . $e->getMessage();
            header('Location: ' . APP_URL . '/chamados' . ($id ? '/' . $id . '/editar' : '/novo'));
        }
        exit;
    }

    private function extrairDados(): array
    {
        $prazo = trim($_POST['prazo'] ?? '');
        if ($prazo !== '') {
            $prazo = str_replace('T', ' ', $prazo);
            if (strlen($prazo) === 16) {
                $prazo .= ':00';
            }
        }

        return [
            'titulo' => trim($_POST['titulo'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'setor' => trim($_POST['setor'] ?? ''),
            'prioridade' => $_POST['prioridade'] ?? 'media',
            'status' => $_POST['status'] ?? 'aberto',
            'solicitante_id' => !empty($_POST['solicitante_id']) ? (int)$_POST['solicitante_id'] : Auth::id(),
            'responsavel_id' => !empty($_POST['responsavel_id']) ? (int)$_POST['responsavel_id'] : null,
            'cliente_id' => !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null,
            'orcamento_id' => !empty($_POST['orcamento_id']) ? (int)$_POST['orcamento_id'] : null,
            'ordem_servico_id' => !empty($_POST['ordem_servico_id']) ? (int)$_POST['ordem_servico_id'] : null,
            'compra_id' => !empty($_POST['compra_id']) ? (int)$_POST['compra_id'] : null,
            'prazo' => $prazo !== '' ? $prazo : null,
        ];
    }

    private function contextoFormulario(): array
    {
        return [
            'usuarios' => $this->query("SELECT id, nome, email FROM usuarios WHERE ativo = 1 ORDER BY nome"),
            'clientes' => $this->query("SELECT id, nome FROM clientes WHERE status = 'ativo' ORDER BY nome"),
            'orcamentos' => $this->query(
                "SELECT o.id, o.codigo, o.titulo, c.nome AS cliente_nome
                 FROM orcamentos o
                 LEFT JOIN clientes c ON c.id = o.cliente_id
                 ORDER BY o.created_at DESC
                 LIMIT 200"
            ),
            'ordens' => $this->query(
                "SELECT os.id, os.codigo, os.titulo, c.nome AS cliente_nome
                 FROM ordem_servicos os
                 LEFT JOIN clientes c ON c.id = os.cliente_id
                 ORDER BY os.created_at DESC
                 LIMIT 200"
            ),
            'compras' => $this->query(
                "SELECT co.id, co.codigo, co.titulo, f.nome AS fornecedor_nome
                 FROM compras co
                 LEFT JOIN fornecedores f ON f.id = co.fornecedor_id
                 ORDER BY co.created_at DESC
                 LIMIT 200"
            ),
            'statusLabels' => $this->statusLabels,
            'prioridadeLabels' => $this->prioridadeLabels,
            'setores' => ['Comercial', 'Design', 'Produção', 'Instalação', 'Financeiro', 'Compras', 'Estoque', 'RH', 'Qualidade', 'Diretoria'],
        ];
    }

    private function resumo(array $chamados): array
    {
        $agora = time();
        return [
            'total' => count($chamados),
            'abertos' => count(array_filter($chamados, fn($c) => in_array($c['status'], ['aberto','em_andamento','aguardando'], true))),
            'atrasados' => count(array_filter($chamados, fn($c) => $this->estaAtrasado($c, $agora))),
            'concluidos' => count(array_filter($chamados, fn($c) => $c['status'] === 'concluido')),
        ];
    }

    private function buscarChamado(string $id): ?array
    {
        try {
            $stmt = db()->prepare(
                "SELECT c.*, s.nome AS solicitante_nome, r.nome AS responsavel_nome, cli.nome AS cliente_nome,
                        o.codigo AS orcamento_codigo, o.titulo AS orcamento_titulo,
                        os.codigo AS os_codigo, os.titulo AS os_titulo,
                        co.codigo AS compra_codigo, co.titulo AS compra_titulo
                 FROM chamados c
                 LEFT JOIN usuarios s ON s.id = c.solicitante_id
                 LEFT JOIN usuarios r ON r.id = c.responsavel_id
                 LEFT JOIN clientes cli ON cli.id = c.cliente_id
                 LEFT JOIN orcamentos o ON o.id = c.orcamento_id
                 LEFT JOIN ordem_servicos os ON os.id = c.ordem_servico_id
                 LEFT JOIN compras co ON co.id = c.compra_id
                 WHERE c.id = ?"
            );
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function comentarios(string $chamadoId): array
    {
        return $this->queryPreparada(
            "SELECT cc.*, u.nome AS usuario_nome
             FROM chamado_comentarios cc
             LEFT JOIN usuarios u ON u.id = cc.usuario_id
             WHERE cc.chamado_id = ?
             ORDER BY cc.created_at DESC, cc.id DESC",
            [$chamadoId]
        );
    }

    private function registrarComentario(int $chamadoId, string $tipo, string $comentario, ?string $statusAnterior = null, ?string $statusNovo = null): void
    {
        db()->prepare(
            "INSERT INTO chamado_comentarios
             (chamado_id, usuario_id, tipo, comentario, status_anterior, status_novo, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())"
        )->execute([$chamadoId, Auth::id(), $tipo, $comentario, $statusAnterior, $statusNovo]);
    }

    private function gerarCodigo(): string
    {
        $prefixo = 'CHA-' . date('Ym') . '-';
        try {
            $stmt = db()->prepare("SELECT COUNT(*) FROM chamados WHERE codigo LIKE ?");
            $stmt->execute([$prefixo . '%']);
            $seq = (int)$stmt->fetchColumn() + 1;
        } catch (\Exception $e) {
            $seq = 1;
        }
        return $prefixo . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    private function estaAtrasado(array $chamado, ?int $agora = null): bool
    {
        if (empty($chamado['prazo']) || in_array($chamado['status'], ['concluido','cancelado'], true)) {
            return false;
        }
        return strtotime($chamado['prazo']) < ($agora ?? time());
    }

    private function requerAcao(string $permissao): void
    {
        if (!Auth::pode($permissao)) {
            $_SESSION['flash_error'] = 'Você não tem permissão para executar esta ação.';
            header('Location: ' . APP_URL . '/chamados');
            exit;
        }
    }

    private function validarCsrf(string $redirect): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . $redirect);
            exit;
        }
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
}
