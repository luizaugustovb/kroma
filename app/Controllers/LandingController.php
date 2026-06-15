<?php

namespace App\Controllers;

class LandingController
{
    public function index(): void
    {
        $titulo = 'KROMA PRINT - Comunicação Visual e Impressão';
        ob_start();
        require APP_PATH . '/Views/landing/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/landing.php';
    }

    public function orcamentoRapido(): void
    {
        $this->capturarLead('Orçamento rápido');
    }

    public function contato(): void
    {
        $this->capturarLead('Contato pelo site');
    }

    public function uploadArquivo(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_FILES['arquivo'])) {
            echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $arquivo = $_FILES['arquivo'];
        if ($arquivo['size'] > UPLOAD_MAX_SIZE) {
            echo json_encode(['success' => false, 'message' => 'Arquivo acima do limite de 100MB.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $dir = PUBLIC_PATH . '/uploads/landing';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $nomeSeguro = date('YmdHis') . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '-', $arquivo['name']);
        $destino = $dir . '/' . $nomeSeguro;

        if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
            echo json_encode(['success' => false, 'message' => 'Não foi possível salvar o arquivo.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Arquivo recebido com sucesso.',
            'arquivo' => APP_URL . '/public/uploads/landing/' . $nomeSeguro,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function capturarLead(string $origemDescricao): void
    {
        $nome = trim($_POST['nome'] ?? '');
        $whatsapp = trim($_POST['whatsapp'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $servico = trim($_POST['servico'] ?? ($_POST['produto_interesse'] ?? ''));
        $mensagem = trim($_POST['mensagem'] ?? ($_POST['descricao'] ?? ''));

        if ($nome === '' || $whatsapp === '') {
            $_SESSION['flash_error'] = 'Informe nome e WhatsApp para solicitar atendimento.';
            header('Location: ' . APP_URL . '/#orcamento');
            exit;
        }

        try {
            $stmt = db()->prepare(
                "INSERT INTO leads (nome, email, whatsapp, produto_interesse, descricao, origem, estagio, prioridade, temperatura, created_at)
                 VALUES (?, ?, ?, ?, ?, 'landing_page', 'novo_lead', 'media', 'morno', NOW())"
            );
            $stmt->execute([$nome, $email, $whatsapp, $servico, $origemDescricao . ': ' . $mensagem]);
            $_SESSION['flash_success'] = 'Solicitação recebida. Nossa equipe entrará em contato pelo WhatsApp.';
        } catch (\Exception $e) {
            $_SESSION['flash_warning'] = 'Solicitação registrada localmente, mas o banco ainda não está instalado.';
        }

        header('Location: ' . APP_URL . '/#orcamento');
        exit;
    }
}
