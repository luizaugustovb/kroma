<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class EmpresaController
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    public function configuracoes(): void
    {
        $empresa = $this->carregarEmpresa();
        $titulo = 'Dados da Empresa';
        $subtitulo = 'Configurações comerciais, fiscais e integrações';
        $breadcrumbs = [['label' => 'Empresa', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/empresa/configuracoes.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function salvar(): void
    {
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/empresa');
            exit;
        }

        $campos = [
            'razao_social' => trim($_POST['razao_social'] ?? ''),
            'nome_fantasia' => trim($_POST['nome_fantasia'] ?? ''),
            'cnpj' => trim($_POST['cnpj'] ?? ''),
            'ie' => trim($_POST['ie'] ?? ''),
            'telefone' => trim($_POST['telefone'] ?? ''),
            'whatsapp' => trim($_POST['whatsapp'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'site' => trim($_POST['site'] ?? ''),
            'endereco' => trim($_POST['endereco'] ?? ''),
            'numero' => trim($_POST['numero'] ?? ''),
            'bairro' => trim($_POST['bairro'] ?? ''),
            'cidade' => trim($_POST['cidade'] ?? ''),
            'estado' => trim($_POST['estado'] ?? ''),
            'cep' => trim($_POST['cep'] ?? ''),
            'slogan' => trim($_POST['slogan'] ?? ''),
            'condicoes_orcamento' => trim($_POST['condicoes_orcamento'] ?? ''),
            'token_whatsapp' => trim($_POST['token_whatsapp'] ?? ''),
            'endpoint_whatsapp' => trim($_POST['endpoint_whatsapp'] ?? ''),
            'modo_whatsapp' => in_array($_POST['modo_whatsapp'] ?? 'simulado', ['simulado','producao'], true) ? $_POST['modo_whatsapp'] : 'simulado',
            'chave_openai' => trim($_POST['chave_openai'] ?? ''),
            'chave_gemini' => trim($_POST['chave_gemini'] ?? ''),
            'modo_ia' => in_array($_POST['modo_ia'] ?? 'simulado', ['simulado','producao'], true) ? $_POST['modo_ia'] : 'simulado',
            'provedor_ia' => in_array($_POST['provedor_ia'] ?? 'openai', ['openai','gemini'], true) ? $_POST['provedor_ia'] : 'openai',
            'modelo_ia' => trim($_POST['modelo_ia'] ?? 'gpt-5.5') ?: 'gpt-5.5',
            'prompt_padrao_ia' => trim($_POST['prompt_padrao_ia'] ?? ''),
            'limite_ia_diario' => max(1, (int)($_POST['limite_ia_diario'] ?? 100)),
            'chave_asaas' => trim($_POST['chave_asaas'] ?? ''),
            'ambiente_asaas' => $_POST['ambiente_asaas'] ?? 'sandbox',
        ];

        if ($campos['razao_social'] === '') {
            $_SESSION['flash_error'] = 'Razão social é obrigatória.';
            header('Location: ' . APP_URL . '/empresa');
            exit;
        }

        try {
            $empresa = $this->carregarEmpresa();
            if ($empresa) {
                $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($campos)));
                $campos['id'] = $empresa['id'];
                db()->prepare("UPDATE empresas SET $sets, updated_at = NOW() WHERE id = :id")->execute($campos);
            } else {
                $colunas = implode(', ', array_keys($campos));
                $placeholders = ':' . implode(', :', array_keys($campos));
                db()->prepare("INSERT INTO empresas ($colunas, created_at) VALUES ($placeholders, NOW())")->execute($campos);
            }
            $_SESSION['flash_success'] = 'Dados da empresa atualizados.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao salvar empresa: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/empresa');
        exit;
    }

    private function carregarEmpresa(): ?array
    {
        try {
            return db()->query("SELECT * FROM empresas ORDER BY id LIMIT 1")->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
