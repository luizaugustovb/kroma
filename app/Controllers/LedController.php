<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class LedController
{
    private array $painelStatusLabels = [
        'disponivel' => 'Disponível',
        'reservado' => 'Reservado',
        'instalado' => 'Instalado',
        'manutencao' => 'Em manutenção',
        'retirado' => 'Retirado',
        'cancelado' => 'Cancelado',
    ];

    private array $locacaoStatusLabels = [
        'reservado' => 'Reservado',
        'instalado' => 'Instalado',
        'manutencao' => 'Em manutenção',
        'retirado' => 'Retirado',
        'cancelado' => 'Cancelado',
    ];

    public function __construct()
    {
        AuthMiddleware::requer('led');
    }

    public function index(): void
    {
        $filtros = [
            'status' => $_GET['status'] ?? '',
            'painel_id' => $_GET['painel_id'] ?? '',
            'cliente_id' => $_GET['cliente_id'] ?? '',
        ];

        $paineis = $this->paineis();
        $locacoes = $this->locacoes($filtros);
        $resumo = $this->resumo();
        $contexto = $this->contexto();
        $painelStatusLabels = $this->painelStatusLabels;
        $locacaoStatusLabels = $this->locacaoStatusLabels;

        $titulo = 'Painéis de LED';
        $subtitulo = 'Controle de disponibilidade, locações, conteúdo e instalação';
        $breadcrumbs = [['label' => 'Operacional', 'url' => '/producao'], ['label' => 'Painéis de LED', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/led/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function criarPainel(): void
    {
        if (!$this->csrfValido('/led')) {
            return;
        }

        if (!Auth::pode('led.criar')) {
            $_SESSION['flash_error'] = 'Você não tem permissão para cadastrar painéis.';
            header('Location: ' . APP_URL . '/led');
            exit;
        }

        $dados = $this->extrairPainel();
        if ($dados['nome'] === '') {
            $_SESSION['flash_warning'] = 'Informe o nome do painel.';
            header('Location: ' . APP_URL . '/led');
            exit;
        }

        try {
            $dados['codigo'] = $this->gerarCodigo('led_paineis', 'LED');
            $this->insert('led_paineis', $dados);
            $id = (int)db()->lastInsertId();
            Auth::registrarAuditoria('led_paineis', 'criar', $id);
            $_SESSION['flash_success'] = 'Painel de LED cadastrado.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao cadastrar painel: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/led');
        exit;
    }

    public function criarLocacao(): void
    {
        if (!$this->csrfValido('/led')) {
            return;
        }

        if (!Auth::pode('led.criar')) {
            $_SESSION['flash_error'] = 'Você não tem permissão para cadastrar locações.';
            header('Location: ' . APP_URL . '/led');
            exit;
        }

        $dados = $this->extrairLocacao();
        if (empty($dados['painel_id']) || $dados['titulo'] === '' || empty($dados['data_inicio']) || empty($dados['data_fim'])) {
            $_SESSION['flash_warning'] = 'Informe painel, título, início e fim da locação.';
            header('Location: ' . APP_URL . '/led');
            exit;
        }

        if (strtotime($dados['data_fim']) < strtotime($dados['data_inicio'])) {
            $_SESSION['flash_warning'] = 'A data final deve ser maior que a data inicial.';
            header('Location: ' . APP_URL . '/led');
            exit;
        }

        if ($this->painelTemConflito($dados['painel_id'], $dados['data_inicio'], $dados['data_fim'])) {
            $_SESSION['flash_warning'] = 'Painel indisponível para o período informado.';
            header('Location: ' . APP_URL . '/led');
            exit;
        }

        try {
            $dados['codigo'] = $this->gerarCodigo('led_locacoes', 'LOC');
            $this->insert('led_locacoes', $dados);
            $id = (int)db()->lastInsertId();
            db()->prepare("UPDATE led_paineis SET status = 'reservado', updated_at = NOW() WHERE id = ?")->execute([$dados['painel_id']]);
            Auth::registrarAuditoria('led_locacoes', 'criar', $id);
            $_SESSION['flash_success'] = 'Locação de LED cadastrada.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao cadastrar locação: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/led');
        exit;
    }

    public function statusPainel(string $id): void
    {
        if (!$this->csrfValido('/led')) {
            return;
        }

        if (!Auth::pode('led.editar')) {
            $_SESSION['flash_error'] = 'Você não tem permissão para alterar painéis.';
            header('Location: ' . APP_URL . '/led');
            exit;
        }

        $status = $_POST['status'] ?? '';
        if (!array_key_exists($status, $this->painelStatusLabels)) {
            $_SESSION['flash_error'] = 'Status de painel inválido.';
            header('Location: ' . APP_URL . '/led');
            exit;
        }

        $painel = $this->painel($id);
        if (!$painel) {
            $_SESSION['flash_error'] = 'Painel não encontrado.';
            header('Location: ' . APP_URL . '/led');
            exit;
        }

        try {
            db()->prepare('UPDATE led_paineis SET status = ?, updated_at = NOW() WHERE id = ?')->execute([$status, $id]);
            Auth::registrarAuditoria('led_paineis', 'status_' . $status, (int)$id, $painel, ['status' => $status]);
            $_SESSION['flash_success'] = 'Status do painel atualizado.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao atualizar painel: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/led');
        exit;
    }

    public function statusLocacao(string $id): void
    {
        if (!$this->csrfValido('/led')) {
            return;
        }

        if (!Auth::pode('led.editar')) {
            $_SESSION['flash_error'] = 'Você não tem permissão para alterar locações.';
            header('Location: ' . APP_URL . '/led');
            exit;
        }

        $status = $_POST['status'] ?? '';
        if (!array_key_exists($status, $this->locacaoStatusLabels)) {
            $_SESSION['flash_error'] = 'Status de locação inválido.';
            header('Location: ' . APP_URL . '/led');
            exit;
        }

        $locacao = $this->locacao($id);
        if (!$locacao) {
            $_SESSION['flash_error'] = 'Locação não encontrada.';
            header('Location: ' . APP_URL . '/led');
            exit;
        }

        try {
            db()->beginTransaction();
            db()->prepare('UPDATE led_locacoes SET status = ?, updated_at = NOW() WHERE id = ?')->execute([$status, $id]);
            db()->prepare('UPDATE led_paineis SET status = ?, updated_at = NOW() WHERE id = ?')->execute([
                $this->statusPainelAposLocacao((int)$locacao['painel_id'], $status),
                $locacao['painel_id'],
            ]);
            db()->commit();

            Auth::registrarAuditoria('led_locacoes', 'status_' . $status, (int)$id, $locacao, ['status' => $status]);
            $_SESSION['flash_success'] = 'Status da locação atualizado.';
        } catch (\Exception $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            $_SESSION['flash_error'] = 'Erro ao atualizar locação: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/led');
        exit;
    }

    private function paineis(): array
    {
        return $this->query(
            "SELECT p.*,
                    (SELECT COUNT(*) FROM led_locacoes l WHERE l.painel_id = p.id AND l.status IN ('reservado','instalado','manutencao')) AS locacoes_ativas
             FROM led_paineis p
             ORDER BY FIELD(p.status, 'disponivel','reservado','instalado','manutencao','retirado','cancelado'), p.nome"
        );
    }

    private function locacoes(array $filtros): array
    {
        $where = ['1=1'];
        $params = [];

        if ($filtros['status'] !== '') {
            $where[] = 'l.status = ?';
            $params[] = $filtros['status'];
        }

        if ($filtros['painel_id'] !== '') {
            $where[] = 'l.painel_id = ?';
            $params[] = (int)$filtros['painel_id'];
        }

        if ($filtros['cliente_id'] !== '') {
            $where[] = 'l.cliente_id = ?';
            $params[] = (int)$filtros['cliente_id'];
        }

        return $this->queryPreparada(
            "SELECT l.*, p.codigo AS painel_codigo, p.nome AS painel_nome, p.tamanho AS painel_tamanho,
                    c.nome AS cliente_nome, u.nome AS responsavel_nome, a.codigo AS agenda_codigo
             FROM led_locacoes l
             INNER JOIN led_paineis p ON p.id = l.painel_id
             LEFT JOIN clientes c ON c.id = l.cliente_id
             LEFT JOIN usuarios u ON u.id = l.responsavel_id
             LEFT JOIN agenda_instalacoes a ON a.id = l.agenda_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY FIELD(l.status, 'instalado','reservado','manutencao','retirado','cancelado'), l.data_inicio DESC, l.id DESC
             LIMIT 500",
            $params
        );
    }

    private function resumo(): array
    {
        $resumo = [
            'paineis' => 0,
            'disponiveis' => 0,
            'locacoes_ativas' => 0,
            'faturamento_previsto' => 0.0,
        ];

        try {
            $resumo['paineis'] = (int)db()->query('SELECT COUNT(*) FROM led_paineis')->fetchColumn();
            $resumo['disponiveis'] = (int)db()->query("SELECT COUNT(*) FROM led_paineis WHERE status = 'disponivel'")->fetchColumn();
            $resumo['locacoes_ativas'] = (int)db()->query("SELECT COUNT(*) FROM led_locacoes WHERE status IN ('reservado','instalado','manutencao')")->fetchColumn();
            $resumo['faturamento_previsto'] = (float)db()->query("SELECT COALESCE(SUM(valor_total), 0) FROM led_locacoes WHERE status IN ('reservado','instalado')")->fetchColumn();
        } catch (\Exception $e) {
        }

        return $resumo;
    }

    private function contexto(): array
    {
        return [
            'clientes' => $this->query("SELECT id, nome FROM clientes WHERE status = 'ativo' ORDER BY nome LIMIT 500"),
            'responsaveis' => $this->query('SELECT id, nome FROM usuarios WHERE ativo = 1 ORDER BY nome'),
            'agenda' => $this->query("SELECT id, codigo, titulo, data_inicio FROM agenda_instalacoes WHERE status NOT IN ('concluida','cancelada') ORDER BY data_inicio DESC LIMIT 300"),
            'paineis_disponiveis' => $this->query("SELECT id, codigo, nome, tamanho, valor_diaria FROM led_paineis WHERE status IN ('disponivel','retirado') ORDER BY nome"),
        ];
    }

    private function extrairPainel(): array
    {
        $largura = $this->decimal($_POST['largura_m'] ?? '0');
        $altura = $this->decimal($_POST['altura_m'] ?? '0');

        return [
            'nome' => trim($_POST['nome'] ?? ''),
            'tamanho' => trim($_POST['tamanho'] ?? ''),
            'resolucao' => trim($_POST['resolucao'] ?? ''),
            'localizacao' => trim($_POST['localizacao'] ?? ''),
            'largura_m' => $largura,
            'altura_m' => $altura,
            'area_m2' => $largura > 0 && $altura > 0 ? round($largura * $altura, 2) : 0,
            'valor_diaria' => $this->decimal($_POST['valor_diaria'] ?? '0'),
            'status' => array_key_exists($_POST['status'] ?? '', $this->painelStatusLabels) ? $_POST['status'] : 'disponivel',
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];
    }

    private function extrairLocacao(): array
    {
        return [
            'painel_id' => !empty($_POST['painel_id']) ? (int)$_POST['painel_id'] : null,
            'cliente_id' => !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null,
            'agenda_id' => !empty($_POST['agenda_id']) ? (int)$_POST['agenda_id'] : null,
            'responsavel_id' => !empty($_POST['responsavel_id']) ? (int)$_POST['responsavel_id'] : null,
            'titulo' => trim($_POST['titulo'] ?? ''),
            'contrato' => trim($_POST['contrato'] ?? ''),
            'local_instalacao' => trim($_POST['local_instalacao'] ?? ''),
            'data_inicio' => $this->datetime($_POST['data_inicio'] ?? ''),
            'data_fim' => $this->datetime($_POST['data_fim'] ?? ''),
            'valor_total' => $this->decimal($_POST['valor_total'] ?? '0'),
            'playlist' => trim($_POST['playlist'] ?? ''),
            'arquivos' => trim($_POST['arquivos'] ?? ''),
            'fotos' => trim($_POST['fotos'] ?? ''),
            'comprovantes' => trim($_POST['comprovantes'] ?? ''),
            'status' => 'reservado',
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];
    }

    private function painelTemConflito(int $painelId, string $inicio, string $fim): bool
    {
        try {
            $stmt = db()->prepare(
                "SELECT COUNT(*)
                 FROM led_locacoes
                 WHERE painel_id = ?
                   AND status IN ('reservado','instalado','manutencao')
                   AND data_inicio <= ?
                   AND data_fim >= ?"
            );
            $stmt->execute([$painelId, $fim, $inicio]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function statusPainelAposLocacao(int $painelId, string $statusAtual): string
    {
        if (in_array($statusAtual, ['reservado', 'instalado', 'manutencao'], true)) {
            return $statusAtual;
        }

        try {
            $stmt = db()->prepare(
                "SELECT status
                 FROM led_locacoes
                 WHERE painel_id = ?
                   AND status IN ('instalado','manutencao','reservado')
                 ORDER BY FIELD(status, 'instalado','manutencao','reservado'), data_inicio
                 LIMIT 1"
            );
            $stmt->execute([$painelId]);
            $statusPendente = $stmt->fetchColumn();

            if (in_array($statusPendente, ['reservado', 'instalado', 'manutencao'], true)) {
                return (string)$statusPendente;
            }
        } catch (\Exception $e) {
        }

        return 'disponivel';
    }

    private function painel(string $id): ?array
    {
        return $this->buscarPorId('led_paineis', $id);
    }

    private function locacao(string $id): ?array
    {
        return $this->buscarPorId('led_locacoes', $id);
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

    private function datetime(string $valor): ?string
    {
        if ($valor === '') {
            return null;
        }

        return str_replace('T', ' ', $valor) . (strlen($valor) === 16 ? ':00' : '');
    }

    private function decimal(string $valor): float
    {
        $valor = trim($valor);
        if ($valor === '') {
            return 0.0;
        }

        if (strpos($valor, ',') !== false) {
            $normalizado = str_replace('.', '', $valor);
            $normalizado = str_replace(',', '.', $normalizado);
        } else {
            $normalizado = $valor;
        }

        return is_numeric($normalizado) ? (float)$normalizado : 0.0;
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
