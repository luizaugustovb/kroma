<?php

namespace App\Services;

class WhatsAppService
{
    private const VIICIO_ENDPOINT = 'https://api.viicio.com.br/api/messages/send';

    public function enviar(array $dados): array
    {
        $telefone = $this->normalizarTelefone($dados['telefone'] ?? '');
        $mensagem = trim($dados['mensagem'] ?? '');
        $tipo = $dados['tipo'] ?? 'manual';
        $origem = trim($dados['origem'] ?? '');
        $clienteId = !empty($dados['cliente_id']) ? (int)$dados['cliente_id'] : null;

        if ($telefone === '' || $mensagem === '') {
            return $this->registrar([
                'cliente_id' => $clienteId,
                'telefone' => $telefone ?: (string)($dados['telefone'] ?? ''),
                'mensagem' => $mensagem,
                'tipo' => $tipo,
                'origem' => $origem,
                'status' => 'erro',
                'erro' => 'Telefone e mensagem são obrigatórios.',
            ]);
        }

        $config = $this->config();
        if (($config['modo_whatsapp'] ?? 'simulado') !== 'producao') {
            return $this->registrar([
                'cliente_id' => $clienteId,
                'telefone' => $telefone,
                'mensagem' => $mensagem,
                'tipo' => $tipo,
                'origem' => $origem ?: 'Simulação',
                'status' => 'simulado',
                'resposta' => 'Envio registrado em modo simulado.',
            ]);
        }

        $config['endpoint_whatsapp'] = trim($config['endpoint_whatsapp'] ?? '') ?: self::VIICIO_ENDPOINT;

        if (empty($config['token_whatsapp'])) {
            return $this->registrar([
                'cliente_id' => $clienteId,
                'telefone' => $telefone,
                'mensagem' => $mensagem,
                'tipo' => $tipo,
                'origem' => $origem,
                'status' => 'erro',
                'erro' => 'Configure o token da API Viicio em Integracoes.',
            ]);
        }

        return $this->enviarApi($config, [
            'cliente_id' => $clienteId,
            'telefone' => $telefone,
            'mensagem' => $mensagem,
            'tipo' => $tipo,
            'origem' => $origem ?: 'API Viicio',
        ]);
    }

    public function templates(): array
    {
        return [
            'atendimento' => 'Olá, {cliente}! Recebemos sua solicitação e nossa equipe já vai te atender.',
            'orcamento' => 'Olá, {cliente}! Seu orçamento está em análise. Em breve enviaremos os detalhes por aqui.',
            'producao' => 'Olá, {cliente}! Seu pedido entrou em produção. Avisaremos assim que houver atualização.',
            'pronto' => 'Olá, {cliente}! Seu pedido está pronto para retirada/entrega.',
            'financeiro' => 'Olá, {cliente}! Temos uma atualização financeira referente ao seu atendimento.',
        ];
    }

    public function aplicarTemplate(string $template, array $cliente = []): string
    {
        return strtr($template, [
            '{cliente}' => $cliente['nome'] ?? 'cliente',
            '{empresa}' => $this->config()['nome_fantasia'] ?? APP_NAME,
        ]);
    }

    private function enviarApi(array $config, array $dados): array
    {
        if (!function_exists('curl_init')) {
            return $this->registrar(array_merge($dados, [
                'status' => 'erro',
                'erro' => 'Extensão cURL não está habilitada no PHP.',
            ]));
        }

        $payload = json_encode([
            'number' => $dados['telefone'],
            'body' => $dados['mensagem'],
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init($config['endpoint_whatsapp']);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $config['token_whatsapp'],
            ],
        ]);

        $resposta = curl_exec($ch);
        $erro = curl_error($ch);
        $httpStatus = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $status = ($erro === '' && $httpStatus >= 200 && $httpStatus < 300) ? 'enviado' : 'erro';

        return $this->registrar(array_merge($dados, [
            'status' => $status,
            'http_status' => $httpStatus ?: null,
            'resposta' => $resposta,
            'erro' => $erro ?: ($status === 'erro' ? 'API retornou HTTP ' . $httpStatus : null),
        ]));
    }

    private function registrar(array $dados): array
    {
        try {
            db()->prepare(
                "INSERT INTO whatsapp_envios
                 (cliente_id, usuario_id, telefone, mensagem, tipo, origem, status, http_status, resposta, erro, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            )->execute([
                $dados['cliente_id'] ?? null,
                Auth::id(),
                $dados['telefone'] ?? '',
                $dados['mensagem'] ?? '',
                $dados['tipo'] ?? 'manual',
                $dados['origem'] ?? null,
                $dados['status'] ?? 'pendente',
                $dados['http_status'] ?? null,
                $dados['resposta'] ?? null,
                $dados['erro'] ?? null,
            ]);
            $dados['id'] = (int)db()->lastInsertId();
        } catch (\Exception $e) {
            $dados['status'] = 'erro';
            $dados['erro'] = 'Erro ao registrar envio: ' . $e->getMessage();
        }

        return $dados;
    }

    private function config(): array
    {
        try {
            return db()->query("SELECT * FROM empresas ORDER BY id LIMIT 1")->fetch() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function normalizarTelefone(string $telefone): string
    {
        $digits = preg_replace('/\D/', '', $telefone);
        if ($digits === '') {
            return '';
        }
        if (str_starts_with($digits, '55')) {
            return $digits;
        }
        if (strlen($digits) >= 10 && strlen($digits) <= 11) {
            return '55' . $digits;
        }
        return $digits;
    }
}
