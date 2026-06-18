<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;
use App\Services\IaService;

class IaController
{
    private IaService $service;

    public function __construct()
    {
        AuthMiddleware::requer('ia');
        $this->service = new IaService();
    }

    public function index(): void
    {
        $clientes = $this->clientes();
        $logs = $this->logs();
        $resumo = $this->resumo();
        $templates = $this->service->templates();
        $empresa = $this->empresa();

        $titulo = 'Central de IA';
        $subtitulo = 'Geração assistida para atendimento, orçamento, produtos, margem e operação';
        $breadcrumbs = [['label' => 'IA', 'url' => '']];
        $headerActions = '<a href="' . APP_URL . '/empresa" class="btn btn-secondary btn-sm"><i class="bi bi-gear"></i> Configurar IA</a>';

        ob_start();
        require APP_PATH . '/Views/ia/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function gerar(): void
    {
        $this->requerAcao('ia.criar');
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/ia');
            exit;
        }

        $resultado = $this->service->gerar([
            'cliente_id' => !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null,
            'contexto' => $_POST['contexto'] ?? 'livre',
            'prompt' => trim($_POST['prompt'] ?? ''),
        ]);

        if (($resultado['status'] ?? '') === 'concluido') {
            $_SESSION['flash_success'] = 'Resposta gerada pela IA.';
        } elseif (($resultado['status'] ?? '') === 'simulado') {
            $_SESSION['flash_info'] = 'Resposta gerada em modo simulado.';
        } else {
            $_SESSION['flash_error'] = $resultado['erro'] ?? 'Erro ao gerar resposta.';
        }

        Auth::registrarAuditoria('ia_respostas', 'gerar_' . ($resultado['status'] ?? 'erro'), (int)($resultado['id'] ?? 0), null, $resultado);
        header('Location: ' . APP_URL . '/ia');
        exit;
    }

    private function clientes(): array
    {
        return $this->query("SELECT id, nome, whatsapp, email FROM clientes WHERE status = 'ativo' ORDER BY nome LIMIT 300");
    }

    private function logs(): array
    {
        return $this->query(
            "SELECT r.*, c.nome AS cliente_nome, u.nome AS usuario_nome
             FROM ia_respostas r
             LEFT JOIN clientes c ON c.id = r.cliente_id
             LEFT JOIN usuarios u ON u.id = r.usuario_id
             ORDER BY r.created_at DESC
             LIMIT 60"
        );
    }

    private function resumo(): array
    {
        return [
            'total' => (int)$this->scalar("SELECT COUNT(*) FROM ia_respostas"),
            'hoje' => (int)$this->scalar("SELECT COUNT(*) FROM ia_respostas WHERE DATE(created_at) = CURDATE()"),
            'concluidas' => (int)$this->scalar("SELECT COUNT(*) FROM ia_respostas WHERE status = 'concluido'"),
            'simuladas' => (int)$this->scalar("SELECT COUNT(*) FROM ia_respostas WHERE status = 'simulado'"),
            'erros' => (int)$this->scalar("SELECT COUNT(*) FROM ia_respostas WHERE status = 'erro'"),
            'tokens' => (int)$this->scalar("SELECT COALESCE(SUM(tokens_entrada + tokens_saida), 0) FROM ia_respostas"),
        ];
    }

    private function empresa(): array
    {
        try {
            return db()->query("SELECT * FROM empresas ORDER BY id LIMIT 1")->fetch() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function requerAcao(string $permissao): void
    {
        if (!Auth::pode($permissao)) {
            $_SESSION['flash_error'] = 'Você não tem permissão para executar esta ação.';
            header('Location: ' . APP_URL . '/ia');
            exit;
        }
    }

    private function scalar(string $sql): mixed
    {
        try {
            return db()->query($sql)->fetchColumn() ?: 0;
        } catch (\Exception $e) {
            return 0;
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
}
