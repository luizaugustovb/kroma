<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class QualidadeController
{
    private array $statusLabels = [
        'rascunho' => 'Rascunho',
        'em_revisao' => 'Em revisão',
        'aprovado' => 'Aprovado',
        'obsoleto' => 'Obsoleto',
    ];

    public function __construct()
    {
        AuthMiddleware::requer('pops');
    }

    public function index(): void
    {
        $pops = $this->query(
            "SELECT p.*, pr.nome AS processo_nome, u.nome AS responsavel_nome, a.nome AS aprovador_nome
             FROM qualidade_pops p
             LEFT JOIN processos_produtivos pr ON pr.id = p.processo_id
             LEFT JOIN usuarios u ON u.id = p.responsavel_id
             LEFT JOIN usuarios a ON a.id = p.aprovador_id
             ORDER BY FIELD(p.status,'em_revisao','rascunho','aprovado','obsoleto'), p.revisao_prevista IS NULL, p.revisao_prevista, p.titulo"
        );
        $resumo = $this->resumo($pops);
        $statusLabels = $this->statusLabels;

        $titulo = 'Qualidade / POPs';
        $subtitulo = 'Procedimentos operacionais, checklists e revisão de qualidade';
        $breadcrumbs = [['label' => 'Qualidade', 'url' => '']];
        $headerActions = Auth::pode('pops.criar')
            ? '<a href="' . APP_URL . '/qualidade/pops/novo" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Novo POP</a>'
            : '';

        ob_start();
        require APP_PATH . '/Views/qualidade/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function novo(): void
    {
        $this->requerAcao('pops.criar');
        $pop = [
            'codigo' => '',
            'versao' => 1,
            'status' => 'rascunho',
            'responsavel_id' => Auth::id(),
            'vigencia_inicio' => date('Y-m-d'),
            'revisao_prevista' => date('Y-m-d', strtotime('+180 days')),
        ];
        $contexto = $this->contextoFormulario();
        $titulo = 'Novo POP';
        $subtitulo = 'Cadastro de procedimento operacional padrão';
        $breadcrumbs = [['label' => 'Qualidade', 'url' => '/qualidade'], ['label' => 'Novo POP', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/qualidade/form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criar(): void
    {
        $this->salvarPop();
    }

    public function ver(string $id): void
    {
        $pop = $this->pop($id);
        if (!$pop) {
            $_SESSION['flash_error'] = 'POP não encontrado.';
            header('Location: ' . APP_URL . '/qualidade');
            exit;
        }
        $revisoes = $this->query(
            "SELECT r.*, u.nome AS usuario_nome
             FROM qualidade_pop_revisoes r
             LEFT JOIN usuarios u ON u.id = r.usuario_id
             WHERE r.pop_id = ?
             ORDER BY r.versao DESC, r.created_at DESC",
            [$id]
        );
        $statusLabels = $this->statusLabels;

        $titulo = 'POP ' . $pop['codigo'];
        $subtitulo = $pop['titulo'];
        $breadcrumbs = [['label' => 'Qualidade', 'url' => '/qualidade'], ['label' => $pop['codigo'], 'url' => '']];
        $headerActions = Auth::pode('pops.editar')
            ? '<a href="' . APP_URL . '/qualidade/pops/' . $id . '/editar" class="btn btn-secondary btn-sm"><i class="bi bi-pencil"></i> Editar</a>'
            : '';

        ob_start();
        require APP_PATH . '/Views/qualidade/show.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function editar(string $id): void
    {
        $this->requerAcao('pops.editar');
        $pop = $this->pop($id);
        if (!$pop) {
            $_SESSION['flash_error'] = 'POP não encontrado.';
            header('Location: ' . APP_URL . '/qualidade');
            exit;
        }
        $contexto = $this->contextoFormulario();
        $titulo = 'Editar POP';
        $subtitulo = $pop['codigo'] . ' - ' . $pop['titulo'];
        $breadcrumbs = [['label' => 'Qualidade', 'url' => '/qualidade'], ['label' => $pop['codigo'], 'url' => '/qualidade/pops/' . $id], ['label' => 'Editar', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/qualidade/form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function atualizar(string $id): void
    {
        $this->salvarPop($id);
    }

    public function status(string $id): void
    {
        $this->requerAcao('pops.editar');
        $this->validarCsrf('/qualidade/pops/' . $id);
        $pop = $this->pop($id);
        $status = $_POST['status'] ?? '';
        if (!$pop || !isset($this->statusLabels[$status])) {
            $_SESSION['flash_error'] = 'Status inválido para o POP.';
            header('Location: ' . APP_URL . '/qualidade');
            exit;
        }

        $dados = ['status' => $status];
        $sets = ['status = :status', 'updated_at = NOW()'];
        if ($status === 'aprovado') {
            $dados['aprovador_id'] = Auth::id();
            $sets[] = 'aprovador_id = :aprovador_id';
            $sets[] = 'aprovado_at = NOW()';
            if (empty($pop['vigencia_inicio'])) {
                $sets[] = 'vigencia_inicio = CURDATE()';
            }
        }
        $dados['id'] = $id;

        try {
            db()->prepare('UPDATE qualidade_pops SET ' . implode(', ', $sets) . ' WHERE id = :id')->execute($dados);
            $atualizado = $this->pop($id);
            $this->registrarRevisao((int)$id, $atualizado ?: $pop, 'Status alterado para ' . $this->statusLabels[$status]);
            if ($status === 'aprovado' && $atualizado) {
                $this->sincronizarProcesso($atualizado);
            }
            Auth::registrarAuditoria('qualidade_pops', 'status_' . $status, (int)$id, $pop, $atualizado);
            $_SESSION['flash_success'] = 'Status do POP atualizado.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao atualizar status: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/qualidade/pops/' . $id);
        exit;
    }

    public function revisar(string $id): void
    {
        $this->requerAcao('pops.editar');
        $this->validarCsrf('/qualidade/pops/' . $id);
        $pop = $this->pop($id);
        if (!$pop) {
            $_SESSION['flash_error'] = 'POP não encontrado.';
            header('Location: ' . APP_URL . '/qualidade');
            exit;
        }

        try {
            db()->prepare(
                "UPDATE qualidade_pops
                 SET versao = versao + 1, status = 'rascunho', aprovador_id = NULL, aprovado_at = NULL,
                     revisao_prevista = DATE_ADD(CURDATE(), INTERVAL 180 DAY), updated_at = NOW()
                 WHERE id = ?"
            )->execute([$id]);
            $novo = $this->pop($id);
            $this->registrarRevisao((int)$id, $novo ?: $pop, 'Nova revisão aberta');
            Auth::registrarAuditoria('qualidade_pops', 'nova_revisao', (int)$id, $pop, $novo);
            $_SESSION['flash_success'] = 'Nova revisão aberta para o POP.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao abrir revisão: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/qualidade/pops/' . $id . '/editar');
        exit;
    }

    private function salvarPop(?string $id = null): void
    {
        $this->requerAcao($id ? 'pops.editar' : 'pops.criar');
        $this->validarCsrf('/qualidade' . ($id ? '/pops/' . $id . '/editar' : '/pops/novo'));
        $antigo = $id ? $this->pop($id) : null;
        $dados = [
            'codigo' => trim($_POST['codigo'] ?? '') ?: $this->gerarCodigo(),
            'titulo' => trim($_POST['titulo'] ?? ''),
            'setor' => trim($_POST['setor'] ?? ''),
            'categoria' => trim($_POST['categoria'] ?? ''),
            'processo_id' => !empty($_POST['processo_id']) ? (int)$_POST['processo_id'] : null,
            'versao' => max(1, (int)($_POST['versao'] ?? 1)),
            'status' => $_POST['status'] ?? 'rascunho',
            'objetivo' => trim($_POST['objetivo'] ?? ''),
            'procedimento' => trim($_POST['procedimento'] ?? ''),
            'checklist' => trim($_POST['checklist'] ?? ''),
            'anexo_url' => trim($_POST['anexo_url'] ?? ''),
            'responsavel_id' => !empty($_POST['responsavel_id']) ? (int)$_POST['responsavel_id'] : Auth::id(),
            'aprovador_id' => null,
            'aprovado_at' => null,
            'vigencia_inicio' => ($_POST['vigencia_inicio'] ?? '') ?: null,
            'revisao_prevista' => ($_POST['revisao_prevista'] ?? '') ?: null,
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];
        if ($dados['titulo'] === '') {
            $_SESSION['flash_error'] = 'Título do POP é obrigatório.';
            header('Location: ' . APP_URL . '/qualidade');
            exit;
        }
        if (!isset($this->statusLabels[$dados['status']])) {
            $dados['status'] = 'rascunho';
        }
        if ($dados['status'] === 'aprovado') {
            $dados['aprovador_id'] = Auth::id();
            $dados['aprovado_at'] = date('Y-m-d H:i:s');
        }

        try {
            if ($id) {
                $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($dados)));
                $dados['id'] = $id;
                $sql = "UPDATE qualidade_pops SET $sets, updated_at = NOW() WHERE id = :id";
                db()->prepare($sql)->execute($dados);
                $popId = (int)$id;
                $acao = 'editar';
            } else {
                $colunas = implode(', ', array_keys($dados));
                $placeholders = ':' . implode(', :', array_keys($dados));
                $sql = "INSERT INTO qualidade_pops ($colunas, created_at) VALUES ($placeholders, NOW())";
                db()->prepare($sql)->execute($dados);
                $popId = (int)db()->lastInsertId();
                $acao = 'criar';
            }

            $novo = $this->pop((string)$popId);
            if ($novo) {
                $this->registrarRevisao($popId, $novo, $id ? 'POP atualizado' : 'POP criado');
                if ($novo['status'] === 'aprovado') {
                    $this->sincronizarProcesso($novo);
                }
            }
            Auth::registrarAuditoria('qualidade_pops', $acao, $popId, $antigo, $novo);
            $_SESSION['flash_success'] = $id ? 'POP atualizado.' : 'POP cadastrado.';
            header('Location: ' . APP_URL . '/qualidade/pops/' . $popId);
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao salvar POP: ' . $e->getMessage();
            header('Location: ' . APP_URL . '/qualidade');
            exit;
        }
    }

    private function contextoFormulario(): array
    {
        return [
            'usuarios' => $this->query("SELECT id, nome, email FROM usuarios WHERE ativo = 1 ORDER BY nome"),
            'processos' => $this->query("SELECT id, nome, setor FROM processos_produtivos WHERE ativo = 1 ORDER BY setor, nome"),
            'statusLabels' => $this->statusLabels,
        ];
    }

    private function resumo(array $pops): array
    {
        $hoje = date('Y-m-d');
        return [
            'total' => count($pops),
            'aprovados' => count(array_filter($pops, fn($p) => $p['status'] === 'aprovado')),
            'revisao' => count(array_filter($pops, fn($p) => $p['status'] === 'em_revisao')),
            'vencendo' => count(array_filter($pops, fn($p) => !empty($p['revisao_prevista']) && $p['revisao_prevista'] <= date('Y-m-d', strtotime('+30 days')) && $p['status'] !== 'obsoleto')),
            'vencidos' => count(array_filter($pops, fn($p) => !empty($p['revisao_prevista']) && $p['revisao_prevista'] < $hoje && $p['status'] !== 'obsoleto')),
        ];
    }

    private function pop(string $id): ?array
    {
        try {
            $stmt = db()->prepare(
                "SELECT p.*, pr.nome AS processo_nome, pr.setor AS processo_setor,
                        u.nome AS responsavel_nome, a.nome AS aprovador_nome
                 FROM qualidade_pops p
                 LEFT JOIN processos_produtivos pr ON pr.id = p.processo_id
                 LEFT JOIN usuarios u ON u.id = p.responsavel_id
                 LEFT JOIN usuarios a ON a.id = p.aprovador_id
                 WHERE p.id = ?"
            );
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function registrarRevisao(int $popId, array $pop, string $resumo): void
    {
        try {
            db()->prepare(
                "INSERT INTO qualidade_pop_revisoes
                 (pop_id, versao, status, resumo, procedimento, checklist, usuario_id, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
            )->execute([
                $popId,
                (int)$pop['versao'],
                $pop['status'],
                $resumo,
                $pop['procedimento'] ?? '',
                $pop['checklist'] ?? '',
                Auth::id(),
            ]);
        } catch (\Exception $e) {}
    }

    private function sincronizarProcesso(array $pop): void
    {
        if (empty($pop['processo_id'])) {
            return;
        }
        try {
            db()->prepare(
                "UPDATE processos_produtivos
                 SET checklist = ?, pop = ?, updated_at = NOW()
                 WHERE id = ?"
            )->execute([
                $pop['checklist'] ?? '',
                $pop['procedimento'] ?? '',
                $pop['processo_id'],
            ]);
        } catch (\Exception $e) {}
    }

    private function gerarCodigo(): string
    {
        $prefixo = 'POP-' . date('Ym') . '-';
        try {
            $stmt = db()->prepare("SELECT COUNT(*) FROM qualidade_pops WHERE codigo LIKE ?");
            $stmt->execute([$prefixo . '%']);
            $seq = (int)$stmt->fetchColumn() + 1;
        } catch (\Exception $e) {
            $seq = 1;
        }
        return $prefixo . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    private function requerAcao(string $permissao): void
    {
        if (!Auth::pode($permissao)) {
            $_SESSION['flash_error'] = 'Você não tem permissão para executar esta ação.';
            header('Location: ' . APP_URL . '/qualidade');
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
