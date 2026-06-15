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
            'novo_lead'         => ['label' => 'Novo Lead',          'cor' => '#6C63FF'],
            'primeiro_contato'  => ['label' => 'Primeiro Contato',   'cor' => '#00B0FF'],
            'orcamento_rapido'  => ['label' => 'Orçamento Rápido',   'cor' => '#FFAA00'],
            'orcamento_ia'      => ['label' => 'Orçamento IA',       'cor' => '#A855F7'],
            'orcamento_enviado' => ['label' => 'Orçamento Enviado',  'cor' => '#F97316'],
            'negociacao'        => ['label' => 'Negociação',         'cor' => '#FF6584'],
            'aprovado'          => ['label' => 'Aprovado',           'cor' => '#00D68F'],
            'em_producao'       => ['label' => 'Em Produção',        'cor' => '#14B8A6'],
            'entregue'          => ['label' => 'Entregue',           'cor' => '#22C55E'],
            'pos_venda'         => ['label' => 'Pós-venda',          'cor' => '#8B5CF6'],
            'recorrencia'       => ['label' => 'Recorrência',        'cor' => '#06B6D4'],
            'perdido'           => ['label' => 'Perdido',            'cor' => '#FF3D71'],
        ];

        // Carrega leads agrupados por estágio
        $leads = [];
        try {
            $stmt = db()->prepare(
                "SELECT l.*, u.nome AS vendedor_nome FROM leads l
                 LEFT JOIN usuarios u ON u.id = l.vendedor_id
                 ORDER BY l.prioridade DESC, l.created_at DESC"
            );
            $stmt->execute();
            $rows = $stmt->fetchAll();

            foreach ($rows as $row) {
                $leads[$row['estagio']][] = $row;
            }
        } catch (\Exception $e) {}

        $titulo     = 'CRM — Kanban de Vendas';
        $subtitulo  = 'Acompanhe o funil de vendas em tempo real';
        $breadcrumbs = [['label' => 'CRM', 'url' => '']];

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
            'estagio'            => $_POST['estagio'] ?? 'novo_lead',
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
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['lead' => $this->buscarLead($id)], JSON_UNESCAPED_UNICODE);
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
            'estagio'            => $_POST['estagio'] ?? 'novo_lead',
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
            'novo_lead','primeiro_contato','orcamento_rapido','orcamento_ia',
            'orcamento_enviado','negociacao','aprovado','em_producao',
            'entregue','pos_venda','recorrencia','perdido'
        ];

        if (!in_array($novoEstagio, $estagiosValidos)) {
            echo json_encode(['success' => false, 'message' => 'Estágio inválido']);
            exit;
        }

        try {
            // Busca estágio atual para histórico
            $stmt = db()->prepare("SELECT estagio FROM leads WHERE id = ?");
            $stmt->execute([$id]);
            $lead = $stmt->fetch();

            if (!$lead) {
                echo json_encode(['success' => false, 'message' => 'Lead não encontrado']);
                exit;
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
                'Estágio alterado via Kanban',
                $lead['estagio'],
                $novoEstagio
            ]);

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
}
