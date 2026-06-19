<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class IntegracaoController
{
    public function index(): void
    {
        AuthMiddleware::requer('integracoes');

        $empresa = $this->empresa();
        $webhooks = $this->webhooks();
        $status = $this->statusIntegracoes($empresa);
        $webhookUrls = [
            'viicio' => APP_URL . '/webhooks/viicio',
            'asaas' => APP_URL . '/webhooks/asaas',
        ];

        $titulo = 'Integrações';
        $subtitulo = 'Configuração de Viicio, IA, Asaas, testes HTTP e webhooks';
        $breadcrumbs = [['label' => 'Inteligência', 'url' => '/bi'], ['label' => 'Integrações', 'url' => '']];

        ob_start();
        require APP_PATH . '/Views/integracoes/index.php';
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }

    public function salvar(): void
    {
        AuthMiddleware::requer('integracoes.editar');
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/integracoes');
            exit;
        }

        $campos = [
            'token_whatsapp' => trim($_POST['token_whatsapp'] ?? ''),
            'endpoint_whatsapp' => trim($_POST['endpoint_whatsapp'] ?? ''),
            'modo_whatsapp' => in_array($_POST['modo_whatsapp'] ?? 'simulado', ['simulado','producao'], true) ? $_POST['modo_whatsapp'] : 'simulado',
            'webhook_viicio_token' => trim($_POST['webhook_viicio_token'] ?? ''),
            'chave_openai' => trim($_POST['chave_openai'] ?? ''),
            'chave_gemini' => trim($_POST['chave_gemini'] ?? ''),
            'modo_ia' => in_array($_POST['modo_ia'] ?? 'simulado', ['simulado','producao'], true) ? $_POST['modo_ia'] : 'simulado',
            'provedor_ia' => in_array($_POST['provedor_ia'] ?? 'openai', ['openai','gemini'], true) ? $_POST['provedor_ia'] : 'openai',
            'modelo_ia' => trim($_POST['modelo_ia'] ?? 'gpt-5.5') ?: 'gpt-5.5',
            'prompt_padrao_ia' => trim($_POST['prompt_padrao_ia'] ?? ''),
            'limite_ia_diario' => max(1, (int)($_POST['limite_ia_diario'] ?? 100)),
            'chave_asaas' => trim($_POST['chave_asaas'] ?? ''),
            'ambiente_asaas' => in_array($_POST['ambiente_asaas'] ?? 'sandbox', ['sandbox','producao'], true) ? $_POST['ambiente_asaas'] : 'sandbox',
            'webhook_asaas_token' => trim($_POST['webhook_asaas_token'] ?? ''),
        ];

        try {
            $empresa = $this->empresa();
            if ($empresa) {
                $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($campos)));
                $campos['id'] = $empresa['id'];
                db()->prepare("UPDATE empresas SET $sets, updated_at = NOW() WHERE id = :id")->execute($campos);
            } else {
                $campos['razao_social'] = APP_NAME;
                $campos['nome_fantasia'] = APP_NAME;
                $colunas = implode(', ', array_keys($campos));
                $placeholders = ':' . implode(', :', array_keys($campos));
                db()->prepare("INSERT INTO empresas ($colunas, created_at) VALUES ($placeholders, NOW())")->execute($campos);
            }

            Auth::registrarAuditoria('integracoes', 'salvar', 0, $this->mascararAuditoria($empresa ?? []), $this->mascararAuditoria($campos));
            $_SESSION['flash_success'] = 'Integrações atualizadas.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao salvar integrações: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/integracoes');
        exit;
    }

    public function testar(): void
    {
        AuthMiddleware::requer('integracoes.editar');
        if (!Auth::verificarCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: ' . APP_URL . '/integracoes');
            exit;
        }

        $tipo = $_POST['tipo'] ?? 'viicio';
        $empresa = $this->empresa();
        $resultado = match ($tipo) {
            'openai' => $this->testarOpenAi($empresa),
            'gemini' => $this->testarGemini($empresa),
            'asaas' => $this->testarAsaas($empresa),
            default => $this->testarViicio($empresa),
        };

        $classe = ($resultado['ok'] ?? false) ? 'flash_success' : (($resultado['simulado'] ?? false) ? 'flash_info' : 'flash_warning');
        $_SESSION[$classe] = $resultado['mensagem'] ?? 'Teste finalizado.';
        Auth::registrarAuditoria('integracoes', 'testar_' . $tipo, 0, null, $resultado);

        header('Location: ' . APP_URL . '/integracoes');
        exit;
    }

    public function webhookViicio(): void
    {
        $this->receberWebhook('viicio');
    }

    public function webhookAsaas(): void
    {
        $this->receberWebhook('asaas');
    }

    private function receberWebhook(string $origem): void
    {
        $empresa = $this->empresa();
        $tokenEsperado = $origem === 'viicio' ? ($empresa['webhook_viicio_token'] ?? '') : ($empresa['webhook_asaas_token'] ?? '');
        $tokenRecebido = $_GET['token'] ?? ($_SERVER['HTTP_X_WEBHOOK_TOKEN'] ?? $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '');
        $payload = file_get_contents('php://input') ?: '';
        $headers = $this->headers();
        $json = json_decode($payload, true);

        if ($tokenEsperado !== '' && !hash_equals($tokenEsperado, (string)$tokenRecebido)) {
            $this->registrarWebhook($origem, $payload, $headers, 'ignorado', 'Token inválido.', $json);
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'ignorado', 'erro' => 'Token inválido']);
            exit;
        }

        $this->registrarWebhook($origem, $payload, $headers, 'recebido', null, $json);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'recebido']);
        exit;
    }

    private function testarViicio(?array $empresa): array
    {
        if (($empresa['modo_whatsapp'] ?? 'simulado') !== 'producao') {
            return ['ok' => true, 'simulado' => true, 'mensagem' => 'Viicio está em modo simulado. HTTP externo não foi chamado.'];
        }
        if (empty($empresa['endpoint_whatsapp']) || empty($empresa['token_whatsapp'])) {
            return ['ok' => false, 'mensagem' => 'Configure endpoint e token do Viicio antes do teste.'];
        }

        $res = $this->http('GET', $empresa['endpoint_whatsapp'], null, [
            'Authorization: Bearer ' . $empresa['token_whatsapp'],
            'Accept: application/json',
        ]);

        return [
            'ok' => $res['http_status'] >= 200 && $res['http_status'] < 500,
            'mensagem' => 'Teste HTTP Viicio retornou HTTP ' . ($res['http_status'] ?: 0) . '.',
            'retorno' => $res,
        ];
    }

    private function testarOpenAi(?array $empresa): array
    {
        if (($empresa['modo_ia'] ?? 'simulado') !== 'producao') {
            return ['ok' => true, 'simulado' => true, 'mensagem' => 'IA está em modo simulado. HTTP externo não foi chamado.'];
        }
        if (empty($empresa['chave_openai'])) {
            return ['ok' => false, 'mensagem' => 'Configure a chave OpenAI antes do teste.'];
        }

        $res = $this->http('GET', 'https://api.openai.com/v1/models', null, [
            'Authorization: Bearer ' . $empresa['chave_openai'],
            'Accept: application/json',
        ]);

        return [
            'ok' => $res['http_status'] >= 200 && $res['http_status'] < 300,
            'mensagem' => 'Teste HTTP OpenAI retornou HTTP ' . ($res['http_status'] ?: 0) . '.',
            'retorno' => $res,
        ];
    }

    private function testarGemini(?array $empresa): array
    {
        if (($empresa['modo_ia'] ?? 'simulado') !== 'producao') {
            return ['ok' => true, 'simulado' => true, 'mensagem' => 'Gemini está em modo simulado. HTTP externo não foi chamado.'];
        }
        if (empty($empresa['chave_gemini'])) {
            return ['ok' => false, 'mensagem' => 'Configure a chave Gemini antes do teste.'];
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . urlencode($empresa['chave_gemini']);
        $res = $this->http('GET', $url, null, ['Accept: application/json']);

        return [
            'ok' => $res['http_status'] >= 200 && $res['http_status'] < 300,
            'mensagem' => 'Teste HTTP Gemini retornou HTTP ' . ($res['http_status'] ?: 0) . '.',
            'retorno' => $res,
        ];
    }

    private function testarAsaas(?array $empresa): array
    {
        if (empty($empresa['chave_asaas'])) {
            return ['ok' => false, 'mensagem' => 'Configure a chave Asaas antes do teste.'];
        }

        $base = ($empresa['ambiente_asaas'] ?? 'sandbox') === 'producao'
            ? 'https://api.asaas.com'
            : 'https://sandbox.asaas.com/api';
        $res = $this->http('GET', $base . '/v3/myAccount', null, [
            'Accept: application/json',
            'access_token: ' . $empresa['chave_asaas'],
        ]);

        return [
            'ok' => $res['http_status'] >= 200 && $res['http_status'] < 300,
            'mensagem' => 'Teste HTTP Asaas retornou HTTP ' . ($res['http_status'] ?: 0) . '.',
            'retorno' => $res,
        ];
    }

    private function http(string $method, string $url, ?array $payload = null, array $headers = []): array
    {
        if (!function_exists('curl_init')) {
            return ['http_status' => 0, 'erro' => 'Extensão cURL não está habilitada.', 'body' => ''];
        }

        $ch = curl_init($url);
        $headers[] = 'User-Agent: KromaERP/1.0';
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        }

        $body = curl_exec($ch);
        $erro = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        return [
            'http_status' => $status,
            'erro' => $erro,
            'body' => substr((string)$body, 0, 1000),
        ];
    }

    private function statusIntegracoes(?array $empresa): array
    {
        return [
            'viicio' => $this->statusConfig(!empty($empresa['token_whatsapp']) && !empty($empresa['endpoint_whatsapp']), $empresa['modo_whatsapp'] ?? 'simulado'),
            'openai' => $this->statusConfig(!empty($empresa['chave_openai']), $empresa['modo_ia'] ?? 'simulado'),
            'gemini' => $this->statusConfig(!empty($empresa['chave_gemini']), $empresa['modo_ia'] ?? 'simulado'),
            'asaas' => $this->statusConfig(!empty($empresa['chave_asaas']), $empresa['ambiente_asaas'] ?? 'sandbox'),
            'webhook_viicio' => $this->statusConfig(!empty($empresa['webhook_viicio_token']), 'webhook'),
            'webhook_asaas' => $this->statusConfig(!empty($empresa['webhook_asaas_token']), 'webhook'),
        ];
    }

    private function statusConfig(bool $configurado, string $modo): array
    {
        if (!$configurado) {
            return ['label' => 'Pendente', 'class' => 'badge-warning'];
        }
        if (in_array($modo, ['simulado', 'sandbox'], true)) {
            return ['label' => ucfirst($modo), 'class' => 'badge-info'];
        }
        return ['label' => 'Configurado', 'class' => 'badge-success'];
    }

    private function registrarWebhook(string $origem, string $payload, array $headers, string $status, ?string $erro, ?array $json): void
    {
        $evento = $json['event'] ?? $json['evento'] ?? $json['type'] ?? $json['status'] ?? null;
        $externalId = $json['id'] ?? $json['messageId'] ?? $json['payment']['id'] ?? $json['data']['id'] ?? null;

        try {
            db()->prepare(
                "INSERT INTO integracao_webhooks (origem, evento, external_id, payload, headers, status, erro, ip_origem, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            )->execute([
                $origem,
                is_scalar($evento) ? (string)$evento : null,
                is_scalar($externalId) ? (string)$externalId : null,
                $payload,
                json_encode($headers, JSON_UNESCAPED_UNICODE),
                $status,
                $erro,
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Exception $e) {
        }
    }

    private function empresa(): ?array
    {
        try {
            return db()->query('SELECT * FROM empresas ORDER BY id LIMIT 1')->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function webhooks(): array
    {
        try {
            return db()->query("SELECT * FROM integracao_webhooks ORDER BY created_at DESC LIMIT 80")->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function headers(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders() ?: [];
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headers[str_replace('_', '-', substr($key, 5))] = $value;
            }
        }
        return $headers;
    }

    private function mascararAuditoria(array $dados): array
    {
        foreach (['token_whatsapp', 'chave_openai', 'chave_gemini', 'chave_asaas', 'webhook_viicio_token', 'webhook_asaas_token'] as $campo) {
            if (array_key_exists($campo, $dados) && (string)$dados[$campo] !== '') {
                $dados[$campo] = '[configurado]';
            }
        }
        return $dados;
    }
}
