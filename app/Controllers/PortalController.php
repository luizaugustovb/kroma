<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class PortalController
{
    private array $orcamentoStatusLabels = [
        'rascunho' => 'Rascunho',
        'em_calculo' => 'Em cálculo',
        'enviado' => 'Enviado',
        'aprovado' => 'Aprovado',
        'recusado' => 'Recusado',
        'cancelado' => 'Cancelado',
        'expirado' => 'Expirado',
    ];

    private array $ordemStatusLabels = [
        'aberta' => 'Aberta',
        'em_producao' => 'Em produção',
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

    private array $financeiroStatusLabels = [
        'aberto' => 'Aberto',
        'parcial' => 'Parcial',
        'pago' => 'Pago',
        'cancelado' => 'Cancelado',
    ];

    public function __construct()
    {
        AuthMiddleware::requer('portal');
    }

    public function index(): void
    {
        $cliente = $this->clienteDoUsuario();
        $orcamentos = [];
        $ordens = [];
        $financeiro = [];
        $solicitacoes = [];

        if ($cliente) {
            $clienteId = (int)$cliente['id'];
            $orcamentos = $this->orcamentos($clienteId);
            $ordens = $this->ordens($clienteId);
            $financeiro = $this->financeiro($clienteId);
            $solicitacoes = $this->solicitacoes($clienteId);
        }

        $resumo = $this->resumo($orcamentos, $ordens, $financeiro, $solicitacoes);
        $titulo = 'Portal do Cliente';
        $subtitulo = 'Acompanhamento de orçamentos, produção e financeiro';
        $breadcrumbs = [['label' => 'Portal do Cliente', 'url' => '']];
        $statusOrcamento = $this->orcamentoStatusLabels;
        $statusOrdem = $this->ordemStatusLabels;
        $prioridadeLabels = $this->prioridadeLabels;
        $statusFinanceiro = $this->financeiroStatusLabels;

        ob_start();
        require APP_PATH . '/Views/portal/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function solicitarOrcamento(): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/portal');
            exit;
        }

        $cliente = $this->clienteDoUsuario();
        if (!$cliente) {
            $_SESSION['flash_warning'] = 'Cliente não vinculado ao usuário.';
            header('Location: ' . APP_URL . '/portal');
            exit;
        }

        $produto = trim($_POST['produto_interesse'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $prazo = trim($_POST['prazo_desejado'] ?? '');

        if ($produto === '' || $descricao === '') {
            $_SESSION['flash_warning'] = 'Informe o produto e a descrição da solicitação.';
            header('Location: ' . APP_URL . '/portal');
            exit;
        }

        $campos = [
            'cliente_id' => (int)$cliente['id'],
            'vendedor_id' => null,
            'nome' => $cliente['nome'],
            'email' => $cliente['email'] ?? '',
            'telefone' => $cliente['telefone'] ?? '',
            'whatsapp' => $cliente['whatsapp'] ?? '',
            'empresa' => $cliente['nome_fantasia'] ?: $cliente['nome'],
            'produto_interesse' => $produto,
            'descricao' => $descricao,
            'origem' => 'outro',
            'estagio' => 'novo_lead',
            'valor_estimado' => null,
            'probabilidade' => 50,
            'data_follow_up' => null,
            'prioridade' => 'media',
            'temperatura' => 'morno',
            'observacoes' => $prazo !== '' ? 'Prazo desejado: ' . $prazo : '',
        ];

        try {
            $stmt = db()->prepare(
                "INSERT INTO leads (cliente_id, vendedor_id, nome, email, telefone, whatsapp, empresa,
                    produto_interesse, descricao, origem, estagio, valor_estimado, probabilidade,
                    data_follow_up, prioridade, temperatura, observacoes, created_at)
                 VALUES (:cliente_id, :vendedor_id, :nome, :email, :telefone, :whatsapp, :empresa,
                    :produto_interesse, :descricao, :origem, :estagio, :valor_estimado, :probabilidade,
                    :data_follow_up, :prioridade, :temperatura, :observacoes, NOW())"
            );
            $stmt->execute($campos);
            $leadId = (int)db()->lastInsertId();

            Auth::registrarAuditoria('leads', 'criar_portal', $leadId, null, $campos);
            $_SESSION['flash_success'] = 'Solicitação enviada para o comercial.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao enviar solicitação: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/portal');
        exit;
    }

    private function clienteDoUsuario(): ?array
    {
        $usuario = Auth::usuario() ?? [];

        if (!empty($usuario['cliente_id'])) {
            $cliente = $this->buscarClientePorId((int)$usuario['cliente_id']);
            if ($cliente) {
                return $cliente;
            }
        }

        $email = trim(strtolower($usuario['email'] ?? ''));
        if ($email !== '') {
            try {
                $stmt = db()->prepare("SELECT * FROM clientes WHERE LOWER(email) = ? AND status = 'ativo' ORDER BY id DESC LIMIT 1");
                $stmt->execute([$email]);
                $cliente = $stmt->fetch();
                if ($cliente) {
                    return $cliente;
                }
            } catch (\Exception $e) {}
        }

        return null;
    }

    private function buscarClientePorId(int $id): ?array
    {
        try {
            $stmt = db()->prepare("SELECT * FROM clientes WHERE id = ? AND status = 'ativo'");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function orcamentos(int $clienteId): array
    {
        return $this->query(
            "SELECT id, codigo, titulo, status, total, validade, created_at
             FROM orcamentos
             WHERE cliente_id = ?
             ORDER BY created_at DESC
             LIMIT 20",
            [$clienteId]
        );
    }

    private function ordens(int $clienteId): array
    {
        return $this->query(
            "SELECT os.id, os.codigo, os.titulo, os.status, os.prioridade, os.data_prometida, os.created_at,
                    (SELECT COUNT(*) FROM ordem_servico_etapas e WHERE e.ordem_servico_id = os.id) AS total_etapas,
                    (SELECT COUNT(*) FROM ordem_servico_etapas e WHERE e.ordem_servico_id = os.id AND e.status = 'concluida') AS etapas_concluidas
             FROM ordem_servicos os
             WHERE os.cliente_id = ?
             ORDER BY FIELD(os.status, 'em_producao','aberta','aguardando','finalizada','cancelada'), os.data_prometida IS NULL, os.data_prometida, os.created_at DESC
             LIMIT 20",
            [$clienteId]
        );
    }

    private function financeiro(int $clienteId): array
    {
        return $this->query(
            "SELECT id, codigo, descricao, valor, valor_pago, vencimento, status
             FROM contas_receber
             WHERE cliente_id = ?
             ORDER BY FIELD(status, 'aberto','parcial','pago','cancelado'), vencimento IS NULL, vencimento, created_at DESC
             LIMIT 20",
            [$clienteId]
        );
    }

    private function solicitacoes(int $clienteId): array
    {
        return $this->query(
            "SELECT id, produto_interesse, estagio, created_at
             FROM leads
             WHERE cliente_id = ?
             ORDER BY created_at DESC
             LIMIT 10",
            [$clienteId]
        );
    }

    private function resumo(array $orcamentos, array $ordens, array $financeiro, array $solicitacoes): array
    {
        $osAbertas = array_filter($ordens, fn($ordem) => !in_array($ordem['status'], ['finalizada', 'cancelada'], true));
        $financeiroAberto = array_filter($financeiro, fn($conta) => in_array($conta['status'], ['aberto', 'parcial'], true));

        return [
            'orcamentos' => count($orcamentos),
            'os_abertas' => count($osAbertas),
            'financeiro_aberto' => array_sum(array_map(fn($conta) => max(0, (float)$conta['valor'] - (float)$conta['valor_pago']), $financeiroAberto)),
            'solicitacoes' => count($solicitacoes),
        ];
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
