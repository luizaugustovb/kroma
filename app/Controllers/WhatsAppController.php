<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;
use App\Services\WhatsAppService;

class WhatsAppController
{
    private WhatsAppService $service;

    public function __construct()
    {
        AuthMiddleware::requer('whatsapp');
        $this->service = new WhatsAppService();
    }

    public function index(): void
    {
        $clientes = $this->clientes();
        $logs = $this->logs();
        $resumo = $this->resumo();
        $templates = $this->service->templates();
        $empresa = $this->empresa();

        $titulo = 'WhatsApp';
        $subtitulo = 'Envios manuais, configuração Viicio e histórico de mensagens';
        $breadcrumbs = [['label' => 'WhatsApp', 'url' => '']];
        $headerActions = '<a href="' . APP_URL . '/empresa" class="btn btn-secondary btn-sm"><i class="bi bi-gear"></i> Configurar API</a>';

        ob_start();
        require APP_PATH . '/Views/whatsapp/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function enviar(): void
    {
        $this->requerAcao('whatsapp.criar');
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/whatsapp');
            exit;
        }

        $cliente = !empty($_POST['cliente_id']) ? $this->cliente((string)$_POST['cliente_id']) : null;
        $telefone = trim($_POST['telefone'] ?? '');
        if ($cliente && $telefone === '') {
            $telefone = $cliente['whatsapp'] ?: $cliente['telefone'];
        }
        $mensagem = trim($_POST['mensagem'] ?? '');
        if (!empty($_POST['template']) && $mensagem === '') {
            $templates = $this->service->templates();
            $mensagem = $this->service->aplicarTemplate($templates[$_POST['template']] ?? '', $cliente ?: []);
        }

        $resultado = $this->service->enviar([
            'cliente_id' => $cliente['id'] ?? null,
            'telefone' => $telefone,
            'mensagem' => $mensagem,
            'tipo' => $_POST['tipo'] ?? 'manual',
            'origem' => 'Envio manual',
        ]);

        if (($resultado['status'] ?? '') === 'enviado') {
            $_SESSION['flash_success'] = 'Mensagem enviada pelo WhatsApp.';
        } elseif (($resultado['status'] ?? '') === 'simulado') {
            $_SESSION['flash_info'] = 'Mensagem registrada em modo simulado.';
        } else {
            $_SESSION['flash_error'] = $resultado['erro'] ?? 'Erro ao enviar mensagem.';
        }

        Auth::registrarAuditoria('whatsapp_envios', 'enviar_' . ($resultado['status'] ?? 'erro'), (int)($resultado['id'] ?? 0), null, $resultado);
        header('Location: ' . APP_URL . '/whatsapp');
        exit;
    }

    private function clientes(): array
    {
        return $this->query(
            "SELECT id, nome, telefone, whatsapp, recebe_whatsapp, recebe_campanha, recebe_producao, recebe_financeiro
             FROM clientes
             WHERE status = 'ativo'
             ORDER BY nome
             LIMIT 300"
        );
    }

    private function logs(): array
    {
        return $this->query(
            "SELECT w.*, c.nome AS cliente_nome, u.nome AS usuario_nome
             FROM whatsapp_envios w
             LEFT JOIN clientes c ON c.id = w.cliente_id
             LEFT JOIN usuarios u ON u.id = w.usuario_id
             ORDER BY w.created_at DESC
             LIMIT 80"
        );
    }

    private function resumo(): array
    {
        return [
            'total' => (int)$this->scalar("SELECT COUNT(*) FROM whatsapp_envios"),
            'hoje' => (int)$this->scalar("SELECT COUNT(*) FROM whatsapp_envios WHERE DATE(created_at) = CURDATE()"),
            'enviados' => (int)$this->scalar("SELECT COUNT(*) FROM whatsapp_envios WHERE status = 'enviado'"),
            'erros' => (int)$this->scalar("SELECT COUNT(*) FROM whatsapp_envios WHERE status = 'erro'"),
            'simulados' => (int)$this->scalar("SELECT COUNT(*) FROM whatsapp_envios WHERE status = 'simulado'"),
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

    private function cliente(string $id): ?array
    {
        try {
            $stmt = db()->prepare("SELECT * FROM clientes WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function requerAcao(string $permissao): void
    {
        if (!Auth::pode($permissao)) {
            $_SESSION['flash_error'] = 'Você não tem permissão para executar esta ação.';
            header('Location: ' . APP_URL . '/whatsapp');
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
