<?php
/**
 * Controlador do CRM — KROMA PRINT ERP
 */

namespace App\Controllers;

use App\Services\Auth;
use App\Middleware\AuthMiddleware;

class CrmController
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    /**
     * Board Kanban principal
     */
    public function kanban(): void
    {
        $estagios = [
            'nova_solicitacao'  => ['label' => 'Nova Solicitação',   'cor' => '#6C63FF'],
            'orcamento'         => ['label' => 'Orçamento',          'cor' => '#FFAA00'],
            'orcamento_enviado' => ['label' => 'Orçamento Enviado',  'cor' => '#F97316'],
            'aprovado'          => ['label' => 'Aprovado',           'cor' => '#00D68F'],
            'em_producao'       => ['label' => 'Em Produção',        'cor' => '#14B8A6'],
            'entregue'          => ['label' => 'Entregue',           'cor' => '#22C55E'],
            'pos_venda'         => ['label' => 'Pós-venda',          'cor' => '#8B5CF6'],
            'perdido'           => ['label' => 'Perdido',            'cor' => '#FF3D71'],
        ];

        $exibirConcluidos = ($_GET['concluidos'] ?? '') === '1';

        // Carrega leads agrupados por estágio (exclui concluídos, a menos que filtro ativo)
        $leads = [];
        try {
            $sql = "SELECT l.*, u.nome AS vendedor_nome FROM leads l
                    LEFT JOIN usuarios u ON u.id = l.vendedor_id";
            if (!$exibirConcluidos) {
                $sql .= " WHERE l.concluido_at IS NULL";
            }
            $sql .= " ORDER BY l.prioridade DESC, l.created_at DESC";

            $stmt = db()->query($sql);
            $rows = $stmt->fetchAll();

            foreach ($rows as $row) {
                $leads[$row['estagio']][] = $row;
            }
        } catch (\Exception $e) {}

        $titulo     = 'CRM — Kanban de Vendas';
        $subtitulo  = 'Acompanhe o funil de vendas em tempo real';
        $breadcrumbs = [['label' => 'CRM', 'url' => '']];
        $exibirConcluidos = $exibirConcluidos;

        ob_start();
        require APP_PATH . '/Views/crm/kanban.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    /**
     * Listagem de leads
     */
    public function leads(): void
    {
        try {
            $stmt = db()->query(
                "SELECT l.*, u.nome AS vendedor_nome, c.nome AS cliente_nome
                 FROM leads l
                 LEFT JOIN usuarios u ON u.id = l.vendedor_id
                 LEFT JOIN clientes c ON c.id = l.cliente_id
                 ORDER BY l.created_at DESC"
            );
            $leads = $stmt->fetchAll();
        } catch (\Exception $e) {
            $leads = [];
        }

        $titulo      = 'Leads';
        $subtitulo   = 'Todos os leads cadastrados';
        $breadcrumbs = [['label' => 'CRM', 'url' => '/crm'], ['label' => 'Leads', 'url' => '']];
        $headerActions = '<a href="' . APP_URL . '/crm/leads/novo" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Novo Lead</a>';

        ob_start();
        require APP_PATH . '/Views/crm/leads.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    /**
     * Formulário de novo lead
     */
    public function novoLead(): void
    {
        $vendedores = $this->getVendedores();
        $clientes   = $this->getClientes();
        $lead       = [];
        $titulo     = 'Novo Lead';
        $breadcrumbs = [
            ['label' => 'CRM', 'url' => '/crm'],
            ['label' => 'Leads', 'url' => '/crm/leads'],
            ['label' => 'Novo Lead', 'url' => ''],
        ];

        ob_start();
        require APP_PATH . '/Views/crm/lead_form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    /**
     * Cria novo lead
     */
    public function criarLead(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::verificarCsrf($token)) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/crm/leads/novo');
            exit;
        }

        $campos = [
            'nome'               => trim($_POST['nome'] ?? ''),
            'email'              => trim($_POST['email'] ?? ''),
            'telefone'           => $_POST['telefone'] ?? '',
            'whatsapp'           => $_POST['whatsapp'] ?? '',
            'empresa'            => trim($_POST['empresa'] ?? ''),
            'produto_interesse'  => trim($_POST['produto_interesse'] ?? ''),
            'descricao'          => trim($_POST['descricao'] ?? ''),
            'origem'             => $_POST['origem'] ?? 'outro',
            'estagio'            => $_POST['estagio'] ?? 'nova_solicitacao',
            'valor_estimado'     => !empty($_POST['valor_estimado']) ? floatval(str_replace(['.', ','], ['', '.'], $_POST['valor_estimado'])) : null,
            'probabilidade'      => (int)($_POST['probabilidade'] ?? 50),
            'data_follow_up'     => $_POST['data_follow_up'] ?? null,
            'prioridade'         => $_POST['prioridade'] ?? 'media',
            'temperatura'        => $_POST['temperatura'] ?? 'morno',
            'observacoes'        => trim($_POST['observacoes'] ?? ''),
            'vendedor_id'        => !empty($_POST['vendedor_id']) ? (int)$_POST['vendedor_id'] : Auth::id(),
            'cliente_id'         => !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null,
        ];

        if (empty($campos['nome'])) {
            $_SESSION['flash_error'] = 'Nome é obrigatório.';
            header('Location: ' . APP_URL . '/crm/leads/novo');
            exit;
        }

        try {
            $stmt = db()->prepare(
                "INSERT INTO leads (nome, email, telefone, whatsapp, empresa, produto_interesse, descricao,
                    origem, estagio, valor_estimado, probabilidade, data_follow_up, prioridade, temperatura,
                    observacoes, vendedor_id, cliente_id, created_at)
                 VALUES (:nome, :email, :telefone, :whatsapp, :empresa, :produto_interesse, :descricao,
                    :origem, :estagio, :valor_estimado, :probabilidade, :data_follow_up, :prioridade, :temperatura,
                    :observacoes, :vendedor_id, :cliente_id, NOW())"
            );
            $stmt->execute($campos);
            $id = db()->lastInsertId();

            Auth::registrarAuditoria('leads', 'criar', $id, null, $campos);
            $_SESSION['flash_success'] = 'Lead criado com sucesso!';
            header('Location: ' . APP_URL . '/crm');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao salvar lead: ' . $e->getMessage();
            header('Location: ' . APP_URL . '/crm/leads/novo');
        }
        exit;
    }

    /**
     * Exibe detalhes do lead
     */
    public function verLead(string $id): void
    {
        $lead = $this->buscarLead($id);
        if (!$lead) {
            $_SESSION['flash_error'] = 'Lead não encontrado.';
            header('Location: ' . APP_URL . '/crm');
            exit;
        }

        $arquivosLead = $this->arquivosDoLead($lead['observacoes'] ?? '');
        $titulo = $lead['nome'];
        $subtitulo = 'Detalhes do lead comercial';
        $breadcrumbs = [
            ['label' => 'CRM', 'url' => '/crm'],
            ['label' => 'Leads', 'url' => '/crm/leads'],
            ['label' => $lead['nome'], 'url' => ''],
        ];
        $headerActions = '
            <a href="' . APP_URL . '/crm/leads/' . $id . '/editar" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> Editar Lead</a>
            <a href="' . APP_URL . '/crm" class="btn btn-secondary btn-sm"><i class="bi bi-kanban"></i> Ver Kanban</a>
        ';

        ob_start();
        require APP_PATH . '/Views/crm/show.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    /**
     * Retorna lead em JSON para modal do Kanban
     */
    public function leadJson(string $id): void
    {
        $lead = $this->buscarLead($id);
        if ($lead) {
            $observacoes = $lead['observacoes'] ?? '';
            $lead['arquivos'] = $this->arquivosDoLead($observacoes);
            $lead['observacoes_limpa'] = $this->observacoesSemArquivos($observacoes);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['lead' => $lead], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Formulário de edição do lead
     */
    public function editarLead(string $id): void
    {
        $lead = $this->buscarLead($id);
        if (!$lead) {
            $_SESSION['flash_error'] = 'Lead não encontrado.';
            header('Location: ' . APP_URL . '/crm');
            exit;
        }

        $vendedores = $this->getVendedores();
        $clientes = $this->getClientes();
        $titulo = 'Editar Lead';
        $breadcrumbs = [
            ['label' => 'CRM', 'url' => '/crm'],
            ['label' => 'Leads', 'url' => '/crm/leads'],
            ['label' => 'Editar', 'url' => ''],
        ];

        ob_start();
        require APP_PATH . '/Views/crm/lead_form.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    /**
     * Atualiza lead
     */
    public function atualizarLead(string $id): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/crm/leads/' . $id . '/editar');
            exit;
        }

        $campos = [
            'nome'               => trim($_POST['nome'] ?? ''),
            'email'              => trim($_POST['email'] ?? ''),
            'telefone'           => $_POST['telefone'] ?? '',
            'whatsapp'           => $_POST['whatsapp'] ?? '',
            'empresa'            => trim($_POST['empresa'] ?? ''),
            'produto_interesse'  => trim($_POST['produto_interesse'] ?? ''),
            'descricao'          => trim($_POST['descricao'] ?? ''),
            'origem'             => $_POST['origem'] ?? 'outro',
            'estagio'            => $_POST['estagio'] ?? 'nova_solicitacao',
            'valor_estimado'     => !empty($_POST['valor_estimado']) ? floatval(str_replace(['.', ','], ['', '.'], $_POST['valor_estimado'])) : null,
            'probabilidade'      => (int)($_POST['probabilidade'] ?? 50),
            'data_follow_up'     => $_POST['data_follow_up'] ?: null,
            'prioridade'         => $_POST['prioridade'] ?? 'media',
            'temperatura'        => $_POST['temperatura'] ?? 'morno',
            'observacoes'        => trim($_POST['observacoes'] ?? ''),
            'vendedor_id'        => !empty($_POST['vendedor_id']) ? (int)$_POST['vendedor_id'] : Auth::id(),
            'cliente_id'         => !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null,
        ];

        if (empty($campos['nome'])) {
            $_SESSION['flash_error'] = 'Nome é obrigatório.';
            header('Location: ' . APP_URL . '/crm/leads/' . $id . '/editar');
            exit;
        }

        $antigo = $this->buscarLead($id);

        try {
            $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($campos)));
            $campos['id_registro'] = $id;
            $stmt = db()->prepare("UPDATE leads SET $sets, updated_at = NOW() WHERE id = :id_registro");
            $stmt->execute($campos);

            Auth::registrarAuditoria('leads', 'editar', (int)$id, $antigo, $campos);
            $_SESSION['flash_success'] = 'Lead atualizado com sucesso.';
            header('Location: ' . APP_URL . '/crm/leads/' . $id);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao atualizar lead: ' . $e->getMessage();
            header('Location: ' . APP_URL . '/crm/leads/' . $id . '/editar');
        }
        exit;
    }

    /**
     * Move lead para outro estágio (AJAX)
     */
    public function moverLead(string $id): void
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $novoEstagio = $input['estagio'] ?? '';

        $estagiosValidos = [
            'nova_solicitacao','orcamento','orcamento_enviado',
            'aprovado','em_producao','entregue','pos_venda','perdido'
        ];

        if (!in_array($novoEstagio, $estagiosValidos)) {
            echo json_encode(['success' => false, 'message' => 'Estágio inválido']);
            exit;
        }

        try {
            // Busca lead completo
            $stmt = db()->prepare("SELECT * FROM leads WHERE id = ?");
            $stmt->execute([$id]);
            $lead = $stmt->fetch();

            if (!$lead) {
                echo json_encode(['success' => false, 'message' => 'Lead não encontrado']);
                exit;
            }

            $estagioAnterior = $lead['estagio'];

            // Se mover para "orcamento", criar orçamento automaticamente
            if ($novoEstagio === 'orcamento') {
                $orcamentoId = $this->criarOrcamentoDoLead($lead);
                if ($orcamentoId) {
                    // Auto-move para orcamento_enviado após criar
                    $novoEstagio = 'orcamento_enviado';
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erro ao criar orçamento.']);
                    exit;
                }
            }

            // Se mover para "perdido" vindo de estágios ativos, registra motivo
            $descricaoHistorico = 'Estágio alterado via Kanban';
            if ($novoEstagio === 'perdido' && !in_array($estagioAnterior, ['perdido', ''], true)) {
                $descricaoHistorico = 'Lead perdido';
            }

            // Atualiza estágio
            $stmt = db()->prepare("UPDATE leads SET estagio = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$novoEstagio, $id]);

            // Registra histórico
            $stmt = db()->prepare(
                "INSERT INTO historico_leads (lead_id, usuario_id, tipo, descricao, estagio_anterior, estagio_novo, created_at)
                 VALUES (?, ?, 'estagio', ?, ?, ?, NOW())"
            );
            $stmt->execute([
                $id,
                Auth::id(),
                $descricaoHistorico,
                $estagioAnterior,
                $novoEstagio
            ]);

            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function concluirLead(string $id): void
    {
        header('Content-Type: application/json');

        try {
            $stmt = db()->prepare("SELECT id, estagio FROM leads WHERE id = ?");
            $stmt->execute([$id]);
            $lead = $stmt->fetch();

            if (!$lead) {
                echo json_encode(['success' => false, 'message' => 'Lead não encontrado.']);
                exit;
            }

            db()->prepare("UPDATE leads SET concluido_at = NOW(), estagio = 'pos_venda', updated_at = NOW() WHERE id = ?")
                ->execute([$id]);

            // Registra histórico
            db()->prepare(
                "INSERT INTO historico_leads (lead_id, usuario_id, tipo, descricao, estagio_anterior, estagio_novo, created_at)
                 VALUES (?, ?, 'concluir', 'Atendimento concluído', ?, 'pos_venda', NOW())"
            )->execute([$id, Auth::id(), $lead['estagio']]);

            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Exclui lead
     */
    public function excluirLead(string $id): void
    {
        try {
            $stmt = db()->prepare("DELETE FROM leads WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash_success'] = 'Lead excluído.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao excluir lead.';
        }
        header('Location: ' . APP_URL . '/crm');
        exit;
    }

    // Helpers
    private function criarOrcamentoDoLead(array $lead): ?int
    {
        try {
            $pdo = db();

            // Gera código (mesma lógica do OrcamentoController)
            $prefixo = 'ORC-' . date('Ym') . '-';
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM orcamentos WHERE codigo LIKE ?");
            $stmt->execute([$prefixo . '%']);
            $seq = (int)$stmt->fetchColumn() + 1;
            $codigo = $prefixo . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);

            $dados = [
                'codigo' => $codigo,
                'cliente_id' => !empty($lead['cliente_id']) ? (int)$lead['cliente_id'] : null,
                'lead_id' => (int)$lead['id'],
                'vendedor_id' => !empty($lead['vendedor_id']) ? (int)$lead['vendedor_id'] : null,
                'tipo' => 'rapido',
                'status' => 'rascunho',
                'titulo' => 'Orçamento - ' . ($lead['produto_interesse'] ?: $lead['nome']),
                'descricao' => $lead['descricao'] ?? '',
                'validade' => date('Y-m-d', strtotime('+7 days')),
                'observacoes' => $lead['observacoes'] ?? '',
                'tipo_preco' => 'cliente_final',
                'margem_percent' => 35,
                'impostos_percent' => 8,
                'comissao_percent' => 5,
                'desperdicio_percent' => 5,
                'desconto_percent' => 0,
                'desconto_valor' => 0,
                'subtotal_custo' => 0,
                'subtotal_venda' => 0,
                'preco_minimo' => 0,
                'lucro_previsto' => 0,
                'total' => 0,
            ];

            $colunas = implode(', ', array_keys($dados));
            $placeholders = ':' . implode(', :', array_keys($dados));
            $pdo->prepare("INSERT INTO orcamentos ($colunas, created_at) VALUES ($placeholders, NOW())")
                ->execute($dados);

            return (int)$pdo->lastInsertId();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getVendedores(): array
    {
        try {
            $stmt = db()->query(
                "SELECT u.id, u.nome FROM usuarios u
                 JOIN perfis p ON p.id = u.perfil_id
                 WHERE u.ativo = 1 AND p.nome IN ('vendedor','comercial','gerente','administrador')
                 ORDER BY u.nome"
            );
            return $stmt->fetchAll();
        } catch (\Exception $e) { return []; }
    }

    private function getClientes(): array
    {
        try {
            $stmt = db()->query("SELECT id, nome FROM clientes WHERE status = 'ativo' ORDER BY nome LIMIT 200");
            return $stmt->fetchAll();
        } catch (\Exception $e) { return []; }
    }

    private function buscarLead(string $id): ?array
    {
        try {
            $stmt = db()->prepare(
                "SELECT l.*, u.nome AS vendedor_nome, c.nome AS cliente_nome
                 FROM leads l
                 LEFT JOIN usuarios u ON u.id = l.vendedor_id
                 LEFT JOIN clientes c ON c.id = l.cliente_id
                 WHERE l.id = ?"
            );
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function arquivosDoLead(string $observacoes): array
    {
        if (!preg_match_all('/-\s*(.+?):\s*(https?:\/\/\S+)/', $observacoes, $matches, PREG_SET_ORDER)) {
            return [];
        }

        return array_map(function ($match) {
            $url = rtrim(trim($match[2]), " \t\n\r\0\x0B,.;");
            $path = parse_url($url, PHP_URL_PATH) ?: '';
            $basename = rawurldecode(basename($path));
            $label = trim($match[1]);
            $nome = preg_match('/^arquivo enviado$/iu', $label) && $basename !== '' ? $basename : $label;
            $ext = strtolower(pathinfo($basename ?: $nome, PATHINFO_EXTENSION));
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);

            return [
                'nome' => $nome,
                'url' => $url,
                'extensao' => $ext,
                'imagem' => $isImage,
            ];
        }, $matches);
    }

    private function observacoesSemArquivos(string $observacoes): string
    {
        $limpa = preg_replace('/-\s*.+?:\s*https?:\/\/\S+/u', '', $observacoes);
        $limpa = preg_replace('/Arquivos enviados:\s*/iu', '', $limpa ?? '');
        $linhas = array_filter(array_map('trim', preg_split('/\R/', $limpa ?? '')), fn($linha) => $linha !== '');

        return trim(implode("\n", $linhas));
    }
}
