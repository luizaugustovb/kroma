<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class AgendaController
{
    private array $statusLabels = [
        'agendada' => 'Agendada',
        'em_rota' => 'Em rota',
        'em_execucao' => 'Em execução',
        'concluida' => 'Concluída',
        'cancelada' => 'Cancelada',
    ];

    private array $prioridadeLabels = [
        'baixa' => 'Baixa',
        'media' => 'Média',
        'alta' => 'Alta',
        'urgente' => 'Urgente',
    ];

    public function __construct()
    {
        AuthMiddleware::requer('agenda');
    }

    public function index(): void
    {
        $filtros = [
            'data' => $_GET['data'] ?? date('Y-m-d'),
            'status' => $_GET['status'] ?? '',
            'responsavel_id' => $_GET['responsavel_id'] ?? '',
        ];

        $agendamentos = $this->agendamentos($filtros);
        $resumo = $this->resumo($filtros['data']);
        $contexto = $this->contexto();
        $statusLabels = $this->statusLabels;
        $prioridadeLabels = $this->prioridadeLabels;

        $titulo = 'Agenda de Instalações';
        $subtitulo = 'Agenda operacional de equipes, instalações e entregas externas';
        $breadcrumbs = [['label' => 'Operacional', 'url' => '/producao'], ['label' => 'Agenda', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/agenda/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criar(): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/agenda');
            exit;
        }

        if (!Auth::pode('agenda.criar')) {
            $_SESSION['flash_error'] = 'Você não tem permissão para criar agendamentos.';
            header('Location: ' . APP_URL . '/agenda');
            exit;
        }

        $dados = $this->extrairCampos();
        if ($dados['titulo'] === '' || empty($dados['data_inicio'])) {
            $_SESSION['flash_warning'] = 'Informe o título e a data de início.';
            header('Location: ' . APP_URL . '/agenda');
            exit;
        }

        try {
            $dados['codigo'] = $this->gerarCodigo();
            $this->insert('agenda_instalacoes', $dados);
            $id = (int)db()->lastInsertId();
            Auth::registrarAuditoria('agenda_instalacoes', 'criar', $id);
            $_SESSION['flash_success'] = 'Agendamento criado.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao criar agendamento: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/agenda?data=' . urlencode(substr($dados['data_inicio'], 0, 10)));
        exit;
    }

    public function status(string $id): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/agenda');
            exit;
        }

        if (!Auth::pode('agenda.editar')) {
            $_SESSION['flash_error'] = 'Você não tem permissão para alterar a agenda.';
            header('Location: ' . APP_URL . '/agenda');
            exit;
        }

        $status = $_POST['status'] ?? '';
        if (!array_key_exists($status, $this->statusLabels)) {
            $_SESSION['flash_error'] = 'Status de agenda inválido.';
            header('Location: ' . APP_URL . '/agenda');
            exit;
        }

        $agenda = $this->agendamento($id);
        if (!$agenda) {
            $_SESSION['flash_error'] = 'Agendamento não encontrado.';
            header('Location: ' . APP_URL . '/agenda');
            exit;
        }

        try {
            db()->prepare('UPDATE agenda_instalacoes SET status = ?, updated_at = NOW() WHERE id = ?')->execute([$status, $id]);
            Auth::registrarAuditoria('agenda_instalacoes', 'status_' . $status, (int)$id, $agenda, ['status' => $status]);
            $_SESSION['flash_success'] = 'Status da agenda atualizado.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao atualizar status: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/agenda?data=' . urlencode(substr($agenda['data_inicio'], 0, 10)));
        exit;
    }

    public function excluir(string $id): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/agenda');
            exit;
        }

        if (!Auth::pode('agenda.excluir')) {
            $_SESSION['flash_error'] = 'Você não tem permissão para excluir agendamentos.';
            header('Location: ' . APP_URL . '/agenda');
            exit;
        }

        $agenda = $this->agendamento($id);
        if (!$agenda) {
            $_SESSION['flash_error'] = 'Agendamento não encontrado.';
            header('Location: ' . APP_URL . '/agenda');
            exit;
        }

        try {
            db()->prepare("UPDATE agenda_instalacoes SET status = 'cancelada', updated_at = NOW() WHERE id = ?")->execute([$id]);
            Auth::registrarAuditoria('agenda_instalacoes', 'cancelar', (int)$id);
            $_SESSION['flash_success'] = 'Agendamento cancelado.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao cancelar agendamento.';
        }

        header('Location: ' . APP_URL . '/agenda?data=' . urlencode(substr($agenda['data_inicio'], 0, 10)));
        exit;
    }

    private function agendamentos(array $filtros): array
    {
        $where = ['DATE(a.data_inicio) = ?'];
        $params = [$filtros['data'] ?: date('Y-m-d')];

        if ($filtros['status'] !== '') {
            $where[] = 'a.status = ?';
            $params[] = $filtros['status'];
        }

        if ($filtros['responsavel_id'] !== '') {
            $where[] = 'a.responsavel_id = ?';
            $params[] = (int)$filtros['responsavel_id'];
        }

        return $this->queryPreparada(
            "SELECT a.*, c.nome AS cliente_nome, c.whatsapp AS cliente_whatsapp,
                    o.codigo AS orcamento_codigo, os.codigo AS os_codigo, u.nome AS responsavel_nome
             FROM agenda_instalacoes a
             LEFT JOIN clientes c ON c.id = a.cliente_id
             LEFT JOIN orcamentos o ON o.id = a.orcamento_id
             LEFT JOIN ordem_servicos os ON os.id = a.ordem_servico_id
             LEFT JOIN usuarios u ON u.id = a.responsavel_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY a.data_inicio, FIELD(a.prioridade, 'urgente','alta','media','baixa'), a.id",
            $params
        );
    }

    private function resumo(string $data): array
    {
        $resumo = [
            'total' => 0,
            'agendada' => 0,
            'em_rota' => 0,
            'em_execucao' => 0,
            'concluida' => 0,
            'cancelada' => 0,
        ];

        foreach ($this->queryPreparada("SELECT status, COUNT(*) AS total FROM agenda_instalacoes WHERE DATE(data_inicio) = ? GROUP BY status", [$data ?: date('Y-m-d')]) as $row) {
            $resumo[$row['status']] = (int)$row['total'];
            $resumo['total'] += (int)$row['total'];
        }

        return $resumo;
    }

    private function agendamento(string $id): ?array
    {
        try {
            $stmt = db()->prepare('SELECT * FROM agenda_instalacoes WHERE id = ?');
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function contexto(): array
    {
        return [
            'clientes' => $this->query("SELECT id, nome, cidade, estado, endereco FROM clientes WHERE status = 'ativo' ORDER BY nome LIMIT 500"),
            'orcamentos' => $this->query("SELECT id, codigo, titulo, cliente_id FROM orcamentos WHERE status IN ('enviado','aprovado') ORDER BY created_at DESC LIMIT 300"),
            'ordens' => $this->query("SELECT id, codigo, titulo, cliente_id, orcamento_id FROM ordem_servicos WHERE status NOT IN ('finalizada','cancelada') ORDER BY created_at DESC LIMIT 300"),
            'responsaveis' => $this->query("SELECT id, nome FROM usuarios WHERE ativo = 1 ORDER BY nome"),
        ];
    }

    private function extrairCampos(): array
    {
        return [
            'cliente_id' => !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null,
            'orcamento_id' => !empty($_POST['orcamento_id']) ? (int)$_POST['orcamento_id'] : null,
            'ordem_servico_id' => !empty($_POST['ordem_servico_id']) ? (int)$_POST['ordem_servico_id'] : null,
            'responsavel_id' => !empty($_POST['responsavel_id']) ? (int)$_POST['responsavel_id'] : null,
            'titulo' => trim($_POST['titulo'] ?? ''),
            'equipe' => trim($_POST['equipe'] ?? ''),
            'endereco' => trim($_POST['endereco'] ?? ''),
            'cidade' => trim($_POST['cidade'] ?? ''),
            'estado' => strtoupper(substr(trim($_POST['estado'] ?? ''), 0, 2)),
            'data_inicio' => $this->datetime($_POST['data_inicio'] ?? ''),
            'data_fim' => $this->datetime($_POST['data_fim'] ?? ''),
            'prioridade' => $_POST['prioridade'] ?? 'media',
            'status' => 'agendada',
            'checklist' => trim($_POST['checklist'] ?? ''),
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];
    }

    private function datetime(string $valor): ?string
    {
        if ($valor === '') {
            return null;
        }
        return str_replace('T', ' ', $valor) . (strlen($valor) === 16 ? ':00' : '');
    }

    private function gerarCodigo(): string
    {
        $prefixo = 'AGD-' . date('Ym') . '-';
        try {
            $stmt = db()->prepare("SELECT COUNT(*) FROM agenda_instalacoes WHERE codigo LIKE ?");
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

    private function queryPreparada(string $sql, array $params = []): array
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
