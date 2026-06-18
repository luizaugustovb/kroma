<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class ChatController
{
    private array $tipoLabels = [
        'geral' => 'Geral',
        'setor' => 'Setor',
        'cliente' => 'Cliente',
        'ordem_servico' => 'OS',
        'privado' => 'Privado',
    ];

    public function __construct()
    {
        AuthMiddleware::requer('chat');
    }

    public function index(?string $id = null): void
    {
        $this->garantirCanalGeral();
        $canais = $this->canais();
        $canalAtual = $id ? $this->canal($id) : ($canais[0] ?? null);
        if (!$canalAtual && !empty($canais)) {
            $canalAtual = $canais[0];
        }
        $mensagens = $canalAtual ? $this->mensagens((string)$canalAtual['id']) : [];
        $contexto = $this->contextoFormulario();
        $tipoLabels = $this->tipoLabels;

        $titulo = 'Chat Interno';
        $subtitulo = 'Conversas por equipe, setor, cliente e ordem de serviço';
        $breadcrumbs = [['label' => 'Chat', 'url' => '']];
        $headerActions = Auth::pode('chat.criar')
            ? '<button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalCanal"><i class="bi bi-plus-circle"></i> Novo Canal</button>'
            : '';

        ob_start();
        require APP_PATH . '/Views/chat/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function canal(string $id): ?array
    {
        try {
            $stmt = db()->prepare(
                "SELECT cc.*, c.nome AS cliente_nome, os.codigo AS os_codigo, os.titulo AS os_titulo, u.nome AS criado_por_nome
                 FROM chat_canais cc
                 LEFT JOIN clientes c ON c.id = cc.cliente_id
                 LEFT JOIN ordem_servicos os ON os.id = cc.ordem_servico_id
                 LEFT JOIN usuarios u ON u.id = cc.criado_por_id
                 WHERE cc.id = ?"
            );
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function criarCanal(): void
    {
        $this->requerAcao('chat.criar');
        $this->validarCsrf('/chat');
        $dados = [
            'nome' => trim($_POST['nome'] ?? ''),
            'tipo' => $_POST['tipo'] ?? 'geral',
            'setor' => trim($_POST['setor'] ?? ''),
            'cliente_id' => !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null,
            'ordem_servico_id' => !empty($_POST['ordem_servico_id']) ? (int)$_POST['ordem_servico_id'] : null,
            'criado_por_id' => Auth::id(),
            'status' => 'ativo',
        ];
        if ($dados['nome'] === '') {
            $_SESSION['flash_error'] = 'Nome do canal é obrigatório.';
            header('Location: ' . APP_URL . '/chat');
            exit;
        }
        if (!isset($this->tipoLabels[$dados['tipo']])) {
            $dados['tipo'] = 'geral';
        }

        try {
            $colunas = implode(', ', array_keys($dados));
            $placeholders = ':' . implode(', :', array_keys($dados));
            db()->prepare("INSERT INTO chat_canais ($colunas, created_at) VALUES ($placeholders, NOW())")->execute($dados);
            $canalId = (int)db()->lastInsertId();
            Auth::registrarAuditoria('chat_canais', 'criar', $canalId, null, $dados);
            $_SESSION['flash_success'] = 'Canal criado.';
            header('Location: ' . APP_URL . '/chat/canais/' . $canalId);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao criar canal: ' . $e->getMessage();
            header('Location: ' . APP_URL . '/chat');
        }
        exit;
    }

    public function enviarMensagem(string $id): void
    {
        $this->requerAcao('chat.criar');
        $this->validarCsrf('/chat/canais/' . $id);
        $canal = $this->canal($id);
        $mensagem = trim($_POST['mensagem'] ?? '');
        if (!$canal || $mensagem === '') {
            $_SESSION['flash_error'] = 'Informe uma mensagem válida.';
            header('Location: ' . APP_URL . '/chat');
            exit;
        }

        $dados = [
            'canal_id' => (int)$id,
            'usuario_id' => Auth::id(),
            'mensagem' => $mensagem,
            'anexo_url' => trim($_POST['anexo_url'] ?? '') ?: null,
            'mencoes' => $this->extrairMencoes($mensagem),
        ];

        try {
            db()->prepare(
                "INSERT INTO chat_mensagens (canal_id, usuario_id, mensagem, anexo_url, mencoes, created_at)
                 VALUES (:canal_id, :usuario_id, :mensagem, :anexo_url, :mencoes, NOW())"
            )->execute($dados);
            $mensagemId = (int)db()->lastInsertId();
            Auth::registrarAuditoria('chat_mensagens', 'criar', $mensagemId, null, $dados);
            $_SESSION['flash_success'] = 'Mensagem enviada.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao enviar mensagem: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/chat/canais/' . $id);
        exit;
    }

    private function canais(): array
    {
        return $this->query(
            "SELECT cc.*, c.nome AS cliente_nome, os.codigo AS os_codigo,
                    (SELECT COUNT(*) FROM chat_mensagens cm WHERE cm.canal_id = cc.id) AS total_mensagens,
                    (SELECT MAX(created_at) FROM chat_mensagens cm WHERE cm.canal_id = cc.id) AS ultima_mensagem
             FROM chat_canais cc
             LEFT JOIN clientes c ON c.id = cc.cliente_id
             LEFT JOIN ordem_servicos os ON os.id = cc.ordem_servico_id
             WHERE cc.status = 'ativo'
             ORDER BY COALESCE((SELECT MAX(created_at) FROM chat_mensagens cm WHERE cm.canal_id = cc.id), cc.created_at) DESC, cc.nome"
        );
    }

    private function mensagens(string $canalId): array
    {
        return $this->queryPreparada(
            "SELECT cm.*, u.nome AS usuario_nome, u.setor AS usuario_setor
             FROM chat_mensagens cm
             LEFT JOIN usuarios u ON u.id = cm.usuario_id
             WHERE cm.canal_id = ?
             ORDER BY cm.created_at ASC, cm.id ASC",
            [$canalId]
        );
    }

    private function contextoFormulario(): array
    {
        return [
            'clientes' => $this->query("SELECT id, nome FROM clientes WHERE status = 'ativo' ORDER BY nome LIMIT 300"),
            'ordens' => $this->query("SELECT id, codigo, titulo FROM ordem_servicos WHERE status <> 'cancelada' ORDER BY created_at DESC LIMIT 300"),
            'setores' => $this->setores(),
        ];
    }

    private function setores(): array
    {
        $setores = array_column($this->query("SELECT nome FROM rh_setores WHERE status = 'ativo' ORDER BY nome"), 'nome');
        $fixos = ['Comercial', 'Design', 'Produção', 'Instalação', 'Financeiro', 'Compras', 'Estoque', 'RH', 'Qualidade', 'Diretoria'];
        return array_values(array_unique(array_filter(array_merge($setores, $fixos))));
    }

    private function garantirCanalGeral(): void
    {
        try {
            $existe = (int)db()->query("SELECT COUNT(*) FROM chat_canais WHERE tipo = 'geral' AND nome = 'Geral'")->fetchColumn();
            if ($existe === 0) {
                db()->prepare(
                    "INSERT INTO chat_canais (nome, tipo, criado_por_id, status, created_at)
                     VALUES ('Geral', 'geral', ?, 'ativo', NOW())"
                )->execute([Auth::id()]);
            }
        } catch (\Exception $e) {}
    }

    private function extrairMencoes(string $mensagem): ?string
    {
        preg_match_all('/@([A-Za-z0-9._-]+)/', $mensagem, $matches);
        $mencoes = array_unique($matches[1] ?? []);
        return $mencoes ? implode(',', $mencoes) : null;
    }

    private function requerAcao(string $permissao): void
    {
        if (!Auth::pode($permissao)) {
            $_SESSION['flash_error'] = 'Você não tem permissão para executar esta ação.';
            header('Location: ' . APP_URL . '/chat');
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
