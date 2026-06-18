<?php

namespace App\Services;

class IaService
{
    public function gerar(array $dados): array
    {
        $config = $this->config();
        $prompt = trim($dados['prompt'] ?? '');
        $contexto = $dados['contexto'] ?? 'livre';
        $clienteId = !empty($dados['cliente_id']) ? (int)$dados['cliente_id'] : null;
        $provedor = $config['provedor_ia'] ?? 'openai';
        $modelo = $config['modelo_ia'] ?? 'gpt-5.5';

        if ($prompt === '') {
            return $this->registrar([
                'cliente_id' => $clienteId,
                'provedor' => $provedor,
                'modelo' => $modelo,
                'contexto' => $contexto,
                'prompt' => '',
                'status' => 'erro',
                'erro' => 'Informe um prompt para gerar a resposta.',
            ]);
        }

        if (!$this->dentroDoLimite($config)) {
            return $this->registrar([
                'cliente_id' => $clienteId,
                'provedor' => $provedor,
                'modelo' => $modelo,
                'contexto' => $contexto,
                'prompt' => $prompt,
                'status' => 'erro',
                'erro' => 'Limite diário de IA atingido.',
            ]);
        }

        if (($config['modo_ia'] ?? 'simulado') !== 'producao') {
            return $this->registrar(array_merge($this->baseLog($config, $dados), [
                'status' => 'simulado',
                'resposta' => $this->respostaSimulada($contexto, $prompt),
            ]));
        }

        if ($provedor === 'gemini') {
            return $this->registrar(array_merge($this->baseLog($config, $dados), [
                'status' => 'erro',
                'erro' => 'Gemini está preparado na configuração, mas o conector de produção ainda não foi habilitado.',
            ]));
        }

        if (empty($config['chave_openai'])) {
            return $this->registrar(array_merge($this->baseLog($config, $dados), [
                'status' => 'erro',
                'erro' => 'Configure a chave OpenAI em Dados da Empresa.',
            ]));
        }

        return $this->openai($config, $dados);
    }

    public function templates(): array
    {
        return [
            'atendimento' => [
                'label' => 'Atendimento inicial',
                'texto' => 'Crie uma resposta curta e educada para o cliente com base nesta solicitação: ',
            ],
            'orcamento' => [
                'label' => 'Resumo de orçamento',
                'texto' => 'Organize esta solicitação de orçamento em itens, dúvidas pendentes, riscos e próximos passos: ',
            ],
            'produto' => [
                'label' => 'Descrição de produto',
                'texto' => 'Crie uma descrição comercial em português para este produto gráfico: ',
            ],
            'margem' => [
                'label' => 'Análise de margem',
                'texto' => 'Analise a margem, riscos de desconto e pontos de atenção deste caso: ',
            ],
            'followup' => [
                'label' => 'Follow-up',
                'texto' => 'Crie uma mensagem de follow-up comercial objetiva para este contexto: ',
            ],
            'operacional' => [
                'label' => 'Análise operacional',
                'texto' => 'Liste gargalos, prioridades e ações operacionais para este contexto: ',
            ],
        ];
    }

    private function openai(array $config, array $dados): array
    {
        if (!function_exists('curl_init')) {
            return $this->registrar(array_merge($this->baseLog($config, $dados), [
                'status' => 'erro',
                'erro' => 'Extensão cURL não está habilitada no PHP.',
            ]));
        }

        $payload = json_encode([
            'model' => $config['modelo_ia'] ?: 'gpt-5.5',
            'instructions' => $this->instrucoes($config, $dados['contexto'] ?? 'livre'),
            'input' => trim($dados['prompt'] ?? ''),
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init('https://api.openai.com/v1/responses');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $config['chave_openai'],
            ],
        ]);

        $raw = curl_exec($ch);
        $erro = curl_error($ch);
        $httpStatus = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $json = json_decode((string)$raw, true);
        $resposta = $json['output_text'] ?? $this->extrairTexto($json);
        $usage = $json['usage'] ?? [];
        $status = ($erro === '' && $httpStatus >= 200 && $httpStatus < 300 && $resposta !== '') ? 'concluido' : 'erro';

        return $this->registrar(array_merge($this->baseLog($config, $dados), [
            'status' => $status,
            'resposta' => $resposta,
            'tokens_entrada' => (int)($usage['input_tokens'] ?? 0),
            'tokens_saida' => (int)($usage['output_tokens'] ?? 0),
            'erro' => $status === 'erro' ? ($erro ?: ('OpenAI HTTP ' . $httpStatus . ': ' . substr((string)$raw, 0, 500))) : null,
        ]));
    }

    private function respostaSimulada(string $contexto, string $prompt): string
    {
        $prefixos = [
            'atendimento' => 'Resposta sugerida para atendimento:',
            'orcamento' => 'Resumo sugerido para orçamento:',
            'produto' => 'Descrição sugerida de produto:',
            'margem' => 'Análise preliminar de margem:',
            'followup' => 'Mensagem sugerida de follow-up:',
            'operacional' => 'Ações operacionais sugeridas:',
            'livre' => 'Resposta simulada:',
        ];

        return ($prefixos[$contexto] ?? $prefixos['livre']) . "\n\n"
            . "1. Confirme os dados principais com o cliente.\n"
            . "2. Registre medidas, quantidade, prazo e acabamento.\n"
            . "3. Valide custo, margem e capacidade operacional antes de prometer entrega.\n\n"
            . "Contexto recebido: " . substr($prompt, 0, 600);
    }

    private function instrucoes(array $config, string $contexto): string
    {
        $base = trim($config['prompt_padrao_ia'] ?? '');
        if ($base === '') {
            $base = 'Você é a IA interna da KROMA PRINT. Responda sempre em português do Brasil, com clareza, objetividade e foco em gráfica rápida, comunicação visual, produção, orçamento, margem e atendimento.';
        }
        return $base . "\nContexto do pedido: " . $contexto . ". Use listas curtas quando ajudar e não invente dados que não foram fornecidos.";
    }

    private function baseLog(array $config, array $dados): array
    {
        return [
            'cliente_id' => !empty($dados['cliente_id']) ? (int)$dados['cliente_id'] : null,
            'provedor' => $config['provedor_ia'] ?? 'openai',
            'modelo' => $config['modelo_ia'] ?? 'gpt-5.5',
            'contexto' => $dados['contexto'] ?? 'livre',
            'prompt' => trim($dados['prompt'] ?? ''),
        ];
    }

    private function registrar(array $dados): array
    {
        try {
            db()->prepare(
                "INSERT INTO ia_respostas
                 (usuario_id, cliente_id, provedor, modelo, contexto, prompt, resposta, status, tokens_entrada, tokens_saida, erro, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            )->execute([
                Auth::id(),
                $dados['cliente_id'] ?? null,
                $dados['provedor'] ?? 'openai',
                $dados['modelo'] ?? 'gpt-5.5',
                $dados['contexto'] ?? 'livre',
                $dados['prompt'] ?? '',
                $dados['resposta'] ?? null,
                $dados['status'] ?? 'simulado',
                (int)($dados['tokens_entrada'] ?? 0),
                (int)($dados['tokens_saida'] ?? 0),
                $dados['erro'] ?? null,
            ]);
            $dados['id'] = (int)db()->lastInsertId();
        } catch (\Exception $e) {
            $dados['status'] = 'erro';
            $dados['erro'] = 'Erro ao registrar IA: ' . $e->getMessage();
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

    private function dentroDoLimite(array $config): bool
    {
        $limite = max(1, (int)($config['limite_ia_diario'] ?? 100));
        try {
            $usados = (int)db()->query("SELECT COUNT(*) FROM ia_respostas WHERE DATE(created_at) = CURDATE()")->fetchColumn();
            return $usados < $limite;
        } catch (\Exception $e) {
            return true;
        }
    }

    private function extrairTexto(?array $json): string
    {
        if (!$json || empty($json['output']) || !is_array($json['output'])) {
            return '';
        }
        $partes = [];
        foreach ($json['output'] as $item) {
            foreach (($item['content'] ?? []) as $content) {
                if (!empty($content['text'])) {
                    $partes[] = $content['text'];
                }
            }
        }
        return trim(implode("\n", $partes));
    }
}
