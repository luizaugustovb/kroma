<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class PlanejamentoController
{
    private array $statusMetaLabels = [
        'planejada' => 'Planejada',
        'em_andamento' => 'Em andamento',
        'atingida' => 'Atingida',
        'risco' => 'Em risco',
        'cancelada' => 'Cancelada',
    ];

    private array $statusAcaoLabels = [
        'pendente' => 'Pendente',
        'em_execucao' => 'Em execução',
        'concluida' => 'Concluída',
        'cancelada' => 'Cancelada',
    ];

    private array $tipoLabels = [
        'geral' => 'Geral',
        'vendedor' => 'Vendedor',
        'setor' => 'Setor',
        'produto' => 'Produto',
    ];

    private array $indicadorLabels = [
        'vendas' => 'Vendas aprovadas',
        'orcamentos' => 'Orçamentos',
        'producao' => 'Produção concluída',
        'financeiro' => 'Recebimentos',
        'margem' => 'Margem média',
        'personalizado' => 'Personalizado',
    ];

    private array $unidadeLabels = [
        'valor' => 'Valor',
        'quantidade' => 'Quantidade',
        'percentual' => 'Percentual',
    ];

    public function __construct()
    {
        AuthMiddleware::requer('planejamento');
    }

    public function index(): void
    {
        $filtros = [
            'periodo_mes' => $_GET['periodo_mes'] ?? date('Y-m'),
            'status' => $_GET['status'] ?? '',
            'tipo' => $_GET['tipo'] ?? '',
        ];

        $metas = $this->metas($filtros);
        $acoes = $this->acoes($filtros);
        $resumo = $this->resumo($filtros['periodo_mes']);
        $evolucao = $this->evolucao();
        $contexto = $this->contexto();

        $statusMetaLabels = $this->statusMetaLabels;
        $statusAcaoLabels = $this->statusAcaoLabels;
        $tipoLabels = $this->tipoLabels;
        $indicadorLabels = $this->indicadorLabels;
        $unidadeLabels = $this->unidadeLabels;

        $titulo = 'Planejamento';
        $subtitulo = 'Metas mensais, planos de ação e acompanhamento estratégico';
        $breadcrumbs = [['label' => 'Inteligência', 'url' => '/bi'], ['label' => 'Planejamento', 'url' => '']];
        $headerActions = '<a href="' . APP_URL . '/bi" class="btn btn-secondary btn-sm"><i class="bi bi-bar-chart-line"></i> BI Executivo</a>';

        ob_start();
        require APP_PATH . '/Views/planejamento/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criarMeta(): void
    {
        if (!$this->csrfValido('/planejamento')) {
            return;
        }

        if (!Auth::pode('planejamento.criar')) {
            $_SESSION['flash_error'] = 'Você não tem permissão para criar metas.';
            header('Location: ' . APP_URL . '/planejamento');
            exit;
        }

        $dados = $this->extrairMeta();
        if ($dados['titulo'] === '' || $dados['periodo_mes'] === '' || $dados['valor_meta'] <= 0) {
            $_SESSION['flash_warning'] = 'Informe título, período e valor da meta.';
            header('Location: ' . APP_URL . '/planejamento');
            exit;
        }

        try {
            $dados['codigo'] = $this->gerarCodigo('planejamento_metas', 'MET');
            $this->insert('planejamento_metas', $dados);
            $id = (int)db()->lastInsertId();
            Auth::registrarAuditoria('planejamento_metas', 'criar', $id);
            $_SESSION['flash_success'] = 'Meta cadastrada.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao cadastrar meta: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/planejamento?periodo_mes=' . urlencode($dados['periodo_mes']));
        exit;
    }

    public function criarAcao(): void
    {
        if (!$this->csrfValido('/planejamento')) {
            return;
        }

        if (!Auth::pode('planejamento.criar')) {
            $_SESSION['flash_error'] = 'Você não tem permissão para criar planos de ação.';
            header('Location: ' . APP_URL . '/planejamento');
            exit;
        }

        $dados = $this->extrairAcao();
        if ($dados['titulo'] === '') {
            $_SESSION['flash_warning'] = 'Informe o título da ação.';
            header('Location: ' . APP_URL . '/planejamento');
            exit;
        }

        try {
            $this->insert('planejamento_acoes', $dados);
            $id = (int)db()->lastInsertId();
            Auth::registrarAuditoria('planejamento_acoes', 'criar', $id);
            $_SESSION['flash_success'] = 'Plano de ação cadastrado.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao cadastrar ação: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/planejamento');
        exit;
    }

    public function statusMeta(string $id): void
    {
        if (!$this->csrfValido('/planejamento')) {
            return;
        }

        if (!Auth::pode('planejamento.editar')) {
            $_SESSION['flash_error'] = 'Você não tem permissão para alterar metas.';
            header('Location: ' . APP_URL . '/planejamento');
            exit;
        }

        $status = $_POST['status'] ?? '';
        if (!array_key_exists($status, $this->statusMetaLabels)) {
            $_SESSION['flash_error'] = 'Status de meta inválido.';
            header('Location: ' . APP_URL . '/planejamento');
            exit;
        }

        $meta = $this->meta($id);
        if (!$meta) {
            $_SESSION['flash_error'] = 'Meta não encontrada.';
            header('Location: ' . APP_URL . '/planejamento');
            exit;
        }

        try {
            db()->prepare('UPDATE planejamento_metas SET status = ?, updated_at = NOW() WHERE id = ?')->execute([$status, $id]);
            Auth::registrarAuditoria('planejamento_metas', 'status_' . $status, (int)$id, $meta, ['status' => $status]);
            $_SESSION['flash_success'] = 'Status da meta atualizado.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao atualizar meta: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/planejamento?periodo_mes=' . urlencode($meta['periodo_mes']));
        exit;
    }

    public function statusAcao(string $id): void
    {
        if (!$this->csrfValido('/planejamento')) {
            return;
        }

        if (!Auth::pode('planejamento.editar')) {
            $_SESSION['flash_error'] = 'Você não tem permissão para alterar planos de ação.';
            header('Location: ' . APP_URL . '/planejamento');
            exit;
        }

        $status = $_POST['status'] ?? '';
        if (!array_key_exists($status, $this->statusAcaoLabels)) {
            $_SESSION['flash_error'] = 'Status de ação inválido.';
            header('Location: ' . APP_URL . '/planejamento');
            exit;
        }

        $acao = $this->acao($id);
        if (!$acao) {
            $_SESSION['flash_error'] = 'Ação não encontrada.';
            header('Location: ' . APP_URL . '/planejamento');
            exit;
        }

        try {
            db()->prepare('UPDATE planejamento_acoes SET status = ?, resultado = ?, updated_at = NOW() WHERE id = ?')->execute([
                $status,
                trim($_POST['resultado'] ?? $acao['resultado'] ?? ''),
                $id,
            ]);
            Auth::registrarAuditoria('planejamento_acoes', 'status_' . $status, (int)$id, $acao, ['status' => $status]);
            $_SESSION['flash_success'] = 'Status da ação atualizado.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao atualizar ação: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/planejamento');
        exit;
    }

    public function sincronizarMeta(string $id): void
    {
        if (!$this->csrfValido('/planejamento')) {
            return;
        }

        if (!Auth::pode('planejamento.editar')) {
            $_SESSION['flash_error'] = 'Você não tem permissão para sincronizar metas.';
            header('Location: ' . APP_URL . '/planejamento');
            exit;
        }

        $meta = $this->meta($id);
        if (!$meta) {
            $_SESSION['flash_error'] = 'Meta não encontrada.';
            header('Location: ' . APP_URL . '/planejamento');
            exit;
        }

        try {
            $valorAtual = $this->valorRealizado($meta);
            $novoStatus = $meta['status'] === 'cancelada' ? 'cancelada' : $this->statusPorProgresso($valorAtual, (float)$meta['valor_meta'], $meta['data_fim']);
            db()->prepare('UPDATE planejamento_metas SET valor_atual = ?, status = ?, updated_at = NOW() WHERE id = ?')->execute([$valorAtual, $novoStatus, $id]);
            Auth::registrarAuditoria('planejamento_metas', 'sincronizar', (int)$id, $meta, ['valor_atual' => $valorAtual, 'status' => $novoStatus]);
            $_SESSION['flash_success'] = 'Meta sincronizada com os dados do sistema.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao sincronizar meta: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/planejamento?periodo_mes=' . urlencode($meta['periodo_mes']));
        exit;
    }

    private function metas(array $filtros): array
    {
        $where = [];
        $params = [];

        if ($filtros['periodo_mes'] !== '') {
            $where[] = 'm.periodo_mes = ?';
            $params[] = $filtros['periodo_mes'];
        }

        if ($filtros['status'] !== '') {
            $where[] = 'm.status = ?';
            $params[] = $filtros['status'];
        }

        if ($filtros['tipo'] !== '') {
            $where[] = 'm.tipo = ?';
            $params[] = $filtros['tipo'];
        }

        $sql = "SELECT m.*, u.nome AS usuario_nome, p.nome AS produto_nome,
                       (SELECT COUNT(*) FROM planejamento_acoes a WHERE a.meta_id = m.id) AS acoes_total,
                       (SELECT COUNT(*) FROM planejamento_acoes a WHERE a.meta_id = m.id AND a.status = 'concluida') AS acoes_concluidas
                FROM planejamento_metas m
                LEFT JOIN usuarios u ON u.id = m.usuario_id
                LEFT JOIN produtos p ON p.id = m.produto_id";

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= " ORDER BY FIELD(m.status, 'risco','em_andamento','planejada','atingida','cancelada'), m.periodo_mes DESC, m.id DESC";

        return $this->query($sql, $params);
    }

    private function acoes(array $filtros): array
    {
        $where = [];
        $params = [];

        if ($filtros['periodo_mes'] !== '') {
            $where[] = '(m.periodo_mes = ? OR a.meta_id IS NULL)';
            $params[] = $filtros['periodo_mes'];
        }

        $sql = "SELECT a.*, m.codigo AS meta_codigo, m.titulo AS meta_titulo, m.periodo_mes,
                       u.nome AS responsavel_nome
                FROM planejamento_acoes a
                LEFT JOIN planejamento_metas m ON m.id = a.meta_id
                LEFT JOIN usuarios u ON u.id = a.responsavel_id";

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= " ORDER BY FIELD(a.status, 'em_execucao','pendente','concluida','cancelada'),
                         FIELD(a.prioridade, 'urgente','alta','media','baixa'),
                         a.prazo IS NULL, a.prazo, a.id DESC
                  LIMIT 200";

        return $this->query($sql, $params);
    }

    private function resumo(string $periodo): array
    {
        $resumo = [
            'total' => 0,
            'atingidas' => 0,
            'risco' => 0,
            'progresso_medio' => 0.0,
            'acoes_abertas' => 0,
        ];

        $metas = $this->query('SELECT valor_meta, valor_atual, status FROM planejamento_metas WHERE periodo_mes = ?', [$periodo]);
        $somaProgresso = 0.0;

        foreach ($metas as $meta) {
            $resumo['total']++;
            $resumo['atingidas'] += $meta['status'] === 'atingida' ? 1 : 0;
            $resumo['risco'] += $meta['status'] === 'risco' ? 1 : 0;
            $somaProgresso += $this->progresso((float)$meta['valor_atual'], (float)$meta['valor_meta']);
        }

        $resumo['progresso_medio'] = $resumo['total'] > 0 ? round($somaProgresso / $resumo['total'], 1) : 0.0;
        $resumo['acoes_abertas'] = (int)$this->scalar(
            "SELECT COUNT(*)
             FROM planejamento_acoes a
             LEFT JOIN planejamento_metas m ON m.id = a.meta_id
             WHERE a.status IN ('pendente','em_execucao') AND (m.periodo_mes = ? OR a.meta_id IS NULL)",
            [$periodo]
        );

        return $resumo;
    }

    private function evolucao(): array
    {
        return $this->query(
            "SELECT periodo_mes,
                    COALESCE(SUM(valor_meta), 0) AS meta,
                    COALESCE(SUM(valor_atual), 0) AS realizado,
                    COUNT(*) AS quantidade
             FROM planejamento_metas
             WHERE periodo_mes >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m')
             GROUP BY periodo_mes
             ORDER BY periodo_mes"
        );
    }

    private function contexto(): array
    {
        $setores = $this->query("SELECT DISTINCT setor AS nome FROM ordem_servico_etapas WHERE setor IS NOT NULL AND setor <> '' ORDER BY setor");
        $setoresRh = $this->query('SELECT nome FROM rh_setores ORDER BY nome');

        return [
            'usuarios' => $this->query('SELECT id, nome FROM usuarios WHERE ativo = 1 ORDER BY nome'),
            'produtos' => $this->query("SELECT id, nome FROM produtos WHERE status = 'ativo' ORDER BY nome LIMIT 500"),
            'setores' => array_values(array_unique(array_filter(array_merge(
                array_column($setores, 'nome'),
                array_column($setoresRh, 'nome')
            )))),
            'metas' => $this->query("SELECT id, codigo, titulo, periodo_mes FROM planejamento_metas WHERE status <> 'cancelada' ORDER BY periodo_mes DESC, titulo LIMIT 300"),
        ];
    }

    private function extrairMeta(): array
    {
        $periodo = trim($_POST['periodo_mes'] ?? date('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $periodo)) {
            $periodo = date('Y-m');
        }

        $inicio = $_POST['data_inicio'] ?? ($periodo . '-01');
        $fim = $_POST['data_fim'] ?? date('Y-m-t', strtotime($periodo . '-01'));

        return [
            'titulo' => trim($_POST['titulo'] ?? ''),
            'tipo' => array_key_exists($_POST['tipo'] ?? '', $this->tipoLabels) ? $_POST['tipo'] : 'geral',
            'indicador' => array_key_exists($_POST['indicador'] ?? '', $this->indicadorLabels) ? $_POST['indicador'] : 'vendas',
            'periodo_mes' => $periodo,
            'usuario_id' => !empty($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : null,
            'produto_id' => !empty($_POST['produto_id']) ? (int)$_POST['produto_id'] : null,
            'setor' => trim($_POST['setor'] ?? ''),
            'unidade' => array_key_exists($_POST['unidade'] ?? '', $this->unidadeLabels) ? $_POST['unidade'] : 'valor',
            'valor_meta' => $this->decimal($_POST['valor_meta'] ?? '0'),
            'valor_atual' => $this->decimal($_POST['valor_atual'] ?? '0'),
            'data_inicio' => $inicio ?: null,
            'data_fim' => $fim ?: null,
            'status' => 'planejada',
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];
    }

    private function extrairAcao(): array
    {
        return [
            'meta_id' => !empty($_POST['meta_id']) ? (int)$_POST['meta_id'] : null,
            'responsavel_id' => !empty($_POST['responsavel_id']) ? (int)$_POST['responsavel_id'] : null,
            'titulo' => trim($_POST['titulo'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'prazo' => $_POST['prazo'] ?: null,
            'prioridade' => in_array($_POST['prioridade'] ?? '', ['baixa','media','alta','urgente'], true) ? $_POST['prioridade'] : 'media',
            'status' => 'pendente',
            'resultado' => trim($_POST['resultado'] ?? ''),
        ];
    }

    private function valorRealizado(array $meta): float
    {
        $inicio = $meta['data_inicio'] ?: ($meta['periodo_mes'] . '-01');
        $fim = $meta['data_fim'] ?: date('Y-m-t', strtotime($meta['periodo_mes'] . '-01'));

        return match ($meta['indicador']) {
            'vendas' => $this->realizadoVendas($meta, $inicio, $fim),
            'orcamentos' => $this->realizadoOrcamentos($meta, $inicio, $fim),
            'producao' => $this->realizadoProducao($meta, $inicio, $fim),
            'financeiro' => $this->realizadoFinanceiro($meta, $inicio, $fim),
            'margem' => $this->realizadoMargem($meta, $inicio, $fim),
            default => (float)$meta['valor_atual'],
        };
    }

    private function realizadoVendas(array $meta, string $inicio, string $fim): float
    {
        $params = [$inicio . ' 00:00:00', $fim . ' 23:59:59'];
        $join = '';
        $where = ["o.status = 'aprovado'", 'COALESCE(o.aprovado_at, o.created_at) BETWEEN ? AND ?'];

        if (!empty($meta['usuario_id'])) {
            $where[] = 'o.vendedor_id = ?';
            $params[] = (int)$meta['usuario_id'];
        }

        if (!empty($meta['produto_id'])) {
            $join = 'JOIN orcamento_itens oi ON oi.orcamento_id = o.id';
            $where[] = 'oi.produto_id = ?';
            $params[] = (int)$meta['produto_id'];
            return (float)$this->scalar(
                'SELECT COALESCE(SUM(oi.total), 0) FROM orcamentos o ' . $join . ' WHERE ' . implode(' AND ', $where),
                $params
            );
        }

        return (float)$this->scalar('SELECT COALESCE(SUM(o.total), 0) FROM orcamentos o WHERE ' . implode(' AND ', $where), $params);
    }

    private function realizadoOrcamentos(array $meta, string $inicio, string $fim): float
    {
        $params = [$inicio . ' 00:00:00', $fim . ' 23:59:59'];
        $join = '';
        $where = ["o.status IN ('enviado','aprovado')", 'o.created_at BETWEEN ? AND ?'];

        if (!empty($meta['usuario_id'])) {
            $where[] = 'o.vendedor_id = ?';
            $params[] = (int)$meta['usuario_id'];
        }

        if (!empty($meta['produto_id'])) {
            $join = 'JOIN orcamento_itens oi ON oi.orcamento_id = o.id';
            $where[] = 'oi.produto_id = ?';
            $params[] = (int)$meta['produto_id'];
            return (float)$this->scalar('SELECT COUNT(DISTINCT o.id) FROM orcamentos o ' . $join . ' WHERE ' . implode(' AND ', $where), $params);
        }

        return (float)$this->scalar('SELECT COUNT(*) FROM orcamentos o WHERE ' . implode(' AND ', $where), $params);
    }

    private function realizadoProducao(array $meta, string $inicio, string $fim): float
    {
        $params = [$inicio . ' 00:00:00', $fim . ' 23:59:59'];
        $join = '';
        $where = ["os.status = 'finalizada'", 'COALESCE(os.data_finalizacao, os.updated_at) BETWEEN ? AND ?'];

        if (!empty($meta['produto_id'])) {
            $join .= ' JOIN ordem_servico_itens osi ON osi.ordem_servico_id = os.id';
            $where[] = 'osi.produto_id = ?';
            $params[] = (int)$meta['produto_id'];
        }

        if (!empty($meta['setor'])) {
            $join .= ' JOIN ordem_servico_etapas ose ON ose.ordem_servico_id = os.id';
            $where[] = 'ose.setor = ?';
            $params[] = $meta['setor'];
        }

        return (float)$this->scalar('SELECT COUNT(DISTINCT os.id) FROM ordem_servicos os ' . $join . ' WHERE ' . implode(' AND ', $where), $params);
    }

    private function realizadoFinanceiro(array $meta, string $inicio, string $fim): float
    {
        $params = [$inicio, $fim];
        $join = '';
        $where = ["cr.status IN ('pago','parcial')", 'cr.data_pagamento BETWEEN ? AND ?'];

        if (!empty($meta['usuario_id'])) {
            $join = 'LEFT JOIN orcamentos o ON o.id = cr.orcamento_id';
            $where[] = 'o.vendedor_id = ?';
            $params[] = (int)$meta['usuario_id'];
        }

        return (float)$this->scalar('SELECT COALESCE(SUM(cr.valor_pago), 0) FROM contas_receber cr ' . $join . ' WHERE ' . implode(' AND ', $where), $params);
    }

    private function realizadoMargem(array $meta, string $inicio, string $fim): float
    {
        $params = [$inicio . ' 00:00:00', $fim . ' 23:59:59'];
        $where = ["o.status IN ('enviado','aprovado')", 'o.created_at BETWEEN ? AND ?'];

        if (!empty($meta['usuario_id'])) {
            $where[] = 'o.vendedor_id = ?';
            $params[] = (int)$meta['usuario_id'];
        }

        return (float)$this->scalar('SELECT COALESCE(AVG(NULLIF(o.margem_percent, 0)), 0) FROM orcamentos o WHERE ' . implode(' AND ', $where), $params);
    }

    private function statusPorProgresso(float $valorAtual, float $valorMeta, ?string $dataFim): string
    {
        $progresso = $this->progresso($valorAtual, $valorMeta);
        if ($progresso >= 100) {
            return 'atingida';
        }

        if ($dataFim && $dataFim < date('Y-m-d') && $progresso < 100) {
            return 'risco';
        }

        if ($progresso > 0) {
            return 'em_andamento';
        }

        return 'planejada';
    }

    private function progresso(float $valorAtual, float $valorMeta): float
    {
        if ($valorMeta <= 0) {
            return 0.0;
        }

        return min(999.0, round(($valorAtual / $valorMeta) * 100, 1));
    }

    private function meta(string $id): ?array
    {
        return $this->buscarPorId('planejamento_metas', $id);
    }

    private function acao(string $id): ?array
    {
        return $this->buscarPorId('planejamento_acoes', $id);
    }

    private function buscarPorId(string $tabela, string $id): ?array
    {
        try {
            $stmt = db()->prepare("SELECT * FROM $tabela WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function csrfValido(string $redirect): bool
    {
        if (Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            return true;
        }

        $_SESSION['flash_error'] = 'Token inválido.';
        header('Location: ' . APP_URL . $redirect);
        exit;
    }

    private function decimal(string $valor): float
    {
        $valor = trim($valor);
        if ($valor === '') {
            return 0.0;
        }

        if (strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return is_numeric($valor) ? (float)$valor : 0.0;
    }

    private function gerarCodigo(string $tabela, string $sigla): string
    {
        $prefixo = $sigla . '-' . date('Ym') . '-';

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

    private function scalar(string $sql, array $params = []): mixed
    {
        try {
            $stmt = db()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() ?: 0;
        } catch (\Exception $e) {
            return 0;
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
