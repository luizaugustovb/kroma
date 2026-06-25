<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Auth;

class IntegracaoController
{
    private const VIICIO_ENDPOINT = 'https://api.viicio.com.br/api/messages/send';

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
            'endpoint_whatsapp' => trim($_POST['endpoint_whatsapp'] ?? '') ?: self::VIICIO_ENDPOINT,
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
        if (!is_array($json)) {
            parse_str($payload, $formPayload);
            $json = !empty($formPayload) ? $formPayload : null;
        }

        if ($tokenEsperado !== '' && !hash_equals($tokenEsperado, (string)$tokenRecebido)) {
            $this->registrarWebhook($origem, $payload, $headers, 'ignorado', 'Token inválido.', $json);
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'ignorado', 'erro' => 'Token inválido']);
            exit;
        }

        $processamento = $origem === 'viicio' ? $this->processarRespostaOrcamentoViicio($json, $payload) : null;
        $status = $processamento['status_webhook'] ?? 'recebido';
        $erro = $processamento['erro'] ?? null;

        $this->registrarWebhook($origem, $payload, $headers, $status, $erro, $json);
        header('Content-Type: application/json');
        echo json_encode(['status' => $status, 'processamento' => $processamento], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function processarRespostaOrcamentoViicio(?array $json, string $payload): ?array
    {
        $dados = is_array($json) ? $json : [];
        $telefone = $this->normalizarTelefone($this->valorWebhook($dados, [
            'number', 'phone', 'telefone', 'from', 'remoteJid', 'sender', 'contact.phone',
            'data.number', 'data.phone', 'data.from', 'data.remoteJid', 'message.from',
        ]));
        $mensagem = trim($this->valorWebhook($dados, [
            'body', 'message', 'text', 'mensagem', 'content', 'data.body', 'data.message',
            'data.text', 'message.body', 'message.text',
        ]));

        if ($mensagem === '' && $payload !== '') {
            $mensagem = trim($payload);
        }

        $intencao = $this->intencaoOrcamento($mensagem);
        if (!$intencao) {
            return ['status_webhook' => 'recebido', 'mensagem' => 'Mensagem recebida sem comando de aprovacao ou recusa.'];
        }

        $orcamento = $this->orcamentoPorResposta($mensagem, $telefone);
        if (!$orcamento) {
            return [
                'status_webhook' => 'erro',
                'erro' => 'Nenhum orçamento enviado encontrado para a resposta recebida.',
                'telefone' => $telefone,
                'intencao' => $intencao,
            ];
        }

        try {
            if ($intencao === 'aprovado') {
                $ordemId = $this->aprovarOrcamentoPorWebhook($orcamento);
                return [
                    'status_webhook' => 'processado',
                    'acao' => 'orcamento_aprovado',
                    'orcamento_id' => (int)$orcamento['id'],
                    'codigo' => $orcamento['codigo'],
                    'ordem_servico_id' => $ordemId,
                ];
            }

            $this->recusarOrcamentoPorWebhook($orcamento);
            return [
                'status_webhook' => 'processado',
                'acao' => 'orcamento_recusado',
                'orcamento_id' => (int)$orcamento['id'],
                'codigo' => $orcamento['codigo'],
            ];
        } catch (\Exception $e) {
            return [
                'status_webhook' => 'erro',
                'erro' => $e->getMessage(),
                'orcamento_id' => (int)$orcamento['id'],
                'codigo' => $orcamento['codigo'],
            ];
        }
    }

    private function valorWebhook(array $dados, array $caminhos): string
    {
        foreach ($caminhos as $caminho) {
            $valor = $dados;
            foreach (explode('.', $caminho) as $parte) {
                if (!is_array($valor) || !array_key_exists($parte, $valor)) {
                    $valor = null;
                    break;
                }
                $valor = $valor[$parte];
            }
            if (is_scalar($valor) && trim((string)$valor) !== '') {
                return (string)$valor;
            }
        }
        return '';
    }

    private function intencaoOrcamento(string $mensagem): ?string
    {
        $texto = strtolower($this->semAcento($mensagem));
        if (preg_match('/\b(recusado|recuso|reprovado|reprovo|nao aprovo|nao aprovado|nao|cancelar|cancela)\b/u', $texto)) {
            return 'recusado';
        }
        if (preg_match('/\b(aprovado|aprovo|aprovada|aceito|aceita|sim|ok|fechado|pode fazer|autorizo)\b/u', $texto)) {
            return 'aprovado';
        }
        return null;
    }

    private function semAcento(string $texto): string
    {
        $convertido = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        return $convertido !== false ? $convertido : $texto;
    }

    private function orcamentoPorResposta(string $mensagem, string $telefone): ?array
    {
        if (preg_match('/\bORC-\d{6}-\d{4}\b/i', $mensagem, $match)) {
            $stmt = db()->prepare(
                "SELECT o.*, c.nome AS cliente_nome, c.whatsapp AS cliente_whatsapp, c.telefone AS cliente_telefone
                 FROM orcamentos o
                 LEFT JOIN clientes c ON c.id = o.cliente_id
                 WHERE o.codigo = ?
                 ORDER BY o.id DESC
                 LIMIT 1"
            );
            $stmt->execute([strtoupper($match[0])]);
            $orcamento = $stmt->fetch();
            if ($orcamento) {
                return $orcamento;
            }
        }

        if ($telefone === '') {
            return null;
        }

        $stmt = db()->query(
            "SELECT o.*, c.nome AS cliente_nome, c.whatsapp AS cliente_whatsapp, c.telefone AS cliente_telefone
             FROM orcamentos o
             JOIN clientes c ON c.id = o.cliente_id
             WHERE o.status = 'enviado'
             ORDER BY o.updated_at DESC, o.created_at DESC
             LIMIT 80"
        );
        foreach ($stmt->fetchAll() as $orcamento) {
            $clienteTelefone = $this->normalizarTelefone($orcamento['cliente_whatsapp'] ?: ($orcamento['cliente_telefone'] ?? ''));
            if ($clienteTelefone !== '' && $clienteTelefone === $telefone) {
                return $orcamento;
            }
        }
        return null;
    }

    private function testarViicio(?array $empresa): array
    {
        if (($empresa['modo_whatsapp'] ?? 'simulado') !== 'producao') {
            return ['ok' => true, 'simulado' => true, 'mensagem' => 'Viicio está em modo simulado. HTTP externo não foi chamado.'];
        }
        if (empty($empresa['token_whatsapp'])) {
            return ['ok' => false, 'mensagem' => 'Configure o token do Viicio antes do teste.'];
        }

        $numeroTeste = $this->normalizarTelefone($empresa['whatsapp'] ?? ($empresa['telefone'] ?? ''));
        if ($numeroTeste === '') {
            return ['ok' => false, 'mensagem' => 'Configure o WhatsApp da empresa para enviar a mensagem de teste Viicio.'];
        }

        $endpoint = trim($empresa['endpoint_whatsapp'] ?? '') ?: self::VIICIO_ENDPOINT;
        $res = $this->http('POST', $endpoint, [
            'number' => $numeroTeste,
            'body' => 'Teste de integracao Kroma ERP via Viicio.',
        ], [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $empresa['token_whatsapp'],
            'Accept: application/json',
        ]);

        return [
            'ok' => $res['http_status'] >= 200 && $res['http_status'] < 300,
            'mensagem' => 'Teste HTTP Viicio enviou POST e retornou HTTP ' . ($res['http_status'] ?: 0) . '.',
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

    private function aprovarOrcamentoPorWebhook(array $orcamento): int
    {
        if ($orcamento['status'] === 'recusado') {
            throw new \Exception('Orcamento ja esta recusado.');
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE orcamentos SET status = 'aprovado', aprovado_at = COALESCE(aprovado_at, NOW()), updated_at = NOW() WHERE id = ?")
                ->execute([$orcamento['id']]);

            if (!empty($orcamento['lead_id'])) {
                $pdo->prepare("UPDATE leads SET estagio = 'aprovado', updated_at = NOW() WHERE id = ?")
                    ->execute([$orcamento['lead_id']]);
            }

            $this->gerarComissaoWebhook($orcamento);
            $ordemId = $this->criarOrdemServicoWebhook($orcamento);
            $this->gerarContaReceberWebhook($orcamento, $ordemId);

            Auth::registrarAuditoria('orcamentos', 'aprovar_viicio', (int)$orcamento['id'], null, [
                'codigo' => $orcamento['codigo'],
                'origem' => 'webhook_viicio',
            ]);
            $pdo->commit();
            return $ordemId;
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    private function recusarOrcamentoPorWebhook(array $orcamento): void
    {
        if ($orcamento['status'] === 'aprovado') {
            throw new \Exception('Orcamento ja esta aprovado.');
        }

        db()->prepare("UPDATE orcamentos SET status = 'recusado', updated_at = NOW() WHERE id = ?")
            ->execute([$orcamento['id']]);

        if (!empty($orcamento['lead_id'])) {
            db()->prepare("UPDATE leads SET estagio = 'perdido', updated_at = NOW() WHERE id = ?")
                ->execute([$orcamento['lead_id']]);
        }

        Auth::registrarAuditoria('orcamentos', 'recusar_viicio', (int)$orcamento['id'], null, [
            'codigo' => $orcamento['codigo'],
            'origem' => 'webhook_viicio',
        ]);
    }

    private function gerarComissaoWebhook(array $orcamento): void
    {
        db()->prepare("DELETE FROM comissoes WHERE orcamento_id = ?")->execute([$orcamento['id']]);
        $base = (float)$orcamento['total'];
        $percentual = (float)$orcamento['comissao_percent'];
        $valor = round($base * ($percentual / 100), 2);
        $margemReal = $base > 0 ? (((float)$orcamento['lucro_previsto'] / $base) * 100) : 0;
        $status = $margemReal < $this->margemMinima() ? 'bloqueada' : 'prevista';
        $observacao = $status === 'bloqueada'
            ? 'Comissao bloqueada: margem abaixo do minimo configurado.'
            : 'Comissao gerada na aprovacao via webhook Viicio.';

        db()->prepare(
            "INSERT INTO comissoes (orcamento_id, usuario_id, base_calculo, percentual, valor, status, observacoes, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        )->execute([$orcamento['id'], $orcamento['vendedor_id'], $base, $percentual, $valor, $status, $observacao]);
    }

    private function criarOrdemServicoWebhook(array $orcamento): int
    {
        $existente = $this->ordemDoOrcamentoWebhook((int)$orcamento['id']);
        if ($existente) {
            return (int)$existente['id'];
        }

        $codigo = $this->gerarCodigoWebhook('OS', 'ordem_servicos');
        db()->prepare(
            "INSERT INTO ordem_servicos
             (codigo, orcamento_id, cliente_id, responsavel_id, titulo, descricao, prioridade, status, data_entrada, data_prometida, observacoes, created_at)
             VALUES (?, ?, ?, NULL, ?, ?, 'media', 'aberta', ?, ?, ?, NOW())"
        )->execute([
            $codigo,
            $orcamento['id'],
            $orcamento['cliente_id'],
            'OS - ' . $orcamento['titulo'],
            $orcamento['descricao'] ?? '',
            date('Y-m-d'),
            $this->prazoParaDataWebhook($orcamento['prazo_entrega'] ?? ''),
            'Gerada automaticamente pela aprovacao do cliente via Viicio no orcamento ' . $orcamento['codigo'] . '.',
        ]);
        $ordemId = (int)db()->lastInsertId();

        $itens = $this->itensOrcamentoWebhook((int)$orcamento['id']);
        $itemStmt = db()->prepare(
            "INSERT INTO ordem_servico_itens
             (ordem_servico_id, produto_id, orcamento_item_id, produto_nome, descricao, quantidade, unidade, largura, altura, area_m2, material, acabamento, arquivo_ref, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '', 'pendente', NOW())"
        );

        foreach ($itens as $item) {
            $materiais = $this->materiaisDoItemWebhook((int)$item['id']);
            $materialResumo = implode(', ', array_map(fn($m) => $m['material_nome'], $materiais));
            $itemStmt->execute([
                $ordemId,
                $item['produto_id'] ?: null,
                $item['id'],
                $item['produto_nome'],
                $item['descricao'],
                $item['quantidade'],
                $item['unidade'],
                $item['largura'],
                $item['altura'],
                $item['area_m2'],
                $materialResumo,
                '',
            ]);
        }

        $this->salvarEtapasOsWebhook($ordemId, $itens, $this->prazoParaDataWebhook($orcamento['prazo_entrega'] ?? ''));
        $this->reservarMateriaisWebhook($ordemId, (int)$orcamento['id'], $codigo);

        return $ordemId;
    }

    private function gerarContaReceberWebhook(array $orcamento, int $ordemId): void
    {
        if ((float)$orcamento['total'] <= 0 || $this->contaReceberDoOrcamentoWebhook((int)$orcamento['id'])) {
            return;
        }

        db()->prepare(
            "INSERT INTO contas_receber
             (codigo, cliente_id, orcamento_id, ordem_servico_id, descricao, origem, valor, valor_pago, vencimento, status, observacoes, created_at)
             VALUES (?, ?, ?, ?, ?, 'orcamento', ?, 0, ?, 'aberto', ?, NOW())"
        )->execute([
            $this->gerarCodigoWebhook('REC', 'contas_receber'),
            $orcamento['cliente_id'],
            $orcamento['id'],
            $ordemId,
            'Recebimento ' . $orcamento['codigo'] . ' - ' . $orcamento['titulo'],
            $orcamento['total'],
            date('Y-m-d', strtotime('+7 days')),
            'Conta gerada automaticamente pela aprovacao via webhook Viicio.',
        ]);
    }

    private function salvarEtapasOsWebhook(int $ordemId, array $itens, ?string $dataPrometida): void
    {
        $produtoIds = array_filter(array_unique(array_column($itens, 'produto_id')));
        $processos = [];
        if ($produtoIds) {
            $placeholders = implode(',', array_fill(0, count($produtoIds), '?'));
            $stmt = db()->prepare(
                "SELECT DISTINCT pr.*
                 FROM produto_processos pp
                 JOIN processos_produtivos pr ON pr.id = pp.processo_id
                 WHERE pp.produto_id IN ($placeholders) AND pr.ativo = 1
                 ORDER BY pp.ordem, pr.nome"
            );
            $stmt->execute(array_values($produtoIds));
            $processos = $stmt->fetchAll();
        }
        if (!$processos) {
            $processos = [['id' => null, 'nome' => 'Producao', 'setor' => 'Producao', 'checklist' => null]];
        }

        $stmt = db()->prepare(
            "INSERT INTO ordem_servico_etapas
             (ordem_servico_id, processo_id, nome, setor, ordem, status, prazo, checklist, created_at)
             VALUES (?, ?, ?, ?, ?, 'pendente', ?, ?, NOW())"
        );
        $prazo = $dataPrometida ? $dataPrometida . ' 18:00:00' : null;
        $ordem = 1;
        foreach ($processos as $processo) {
            $stmt->execute([
                $ordemId,
                $processo['id'] ?? null,
                $processo['nome'],
                $processo['setor'] ?? 'Producao',
                $ordem++,
                $prazo,
                $processo['checklist'] ?? null,
            ]);
        }
    }

    private function reservarMateriaisWebhook(int $ordemId, int $orcamentoId, string $codigoOs): void
    {
        $materiais = $this->queryPreparadaWebhook(
            "SELECT oim.*, m.nome AS material_nome
             FROM orcamento_item_materiais oim
             JOIN orcamento_itens oi ON oi.id = oim.orcamento_item_id
             JOIN materiais m ON m.id = oim.material_id
             WHERE oi.orcamento_id = ?",
            [$orcamentoId]
        );

        foreach ($materiais as $item) {
            $stmt = db()->prepare("SELECT * FROM materiais WHERE id = ? FOR UPDATE");
            $stmt->execute([$item['material_id']]);
            $material = $stmt->fetch();
            if (!$material) {
                continue;
            }

            $quantidade = (float)$item['quantidade'];
            $saldoAnterior = (float)$material['estoque_atual'];
            $reservadoAnterior = (float)$material['estoque_reservado'];
            $reservadoPosterior = $reservadoAnterior + $quantidade;
            if ($reservadoPosterior > $saldoAnterior) {
                throw new \Exception('Estoque insuficiente para reservar ' . $material['nome'] . '.');
            }

            db()->prepare("UPDATE materiais SET estoque_reservado = ?, updated_at = NOW() WHERE id = ?")
                ->execute([$reservadoPosterior, $item['material_id']]);
            db()->prepare(
                "INSERT INTO estoque_movimentacoes
                 (material_id, ordem_servico_id, usuario_id, tipo, origem, quantidade, custo_unitario, saldo_anterior, saldo_posterior, reservado_anterior, reservado_posterior, observacao, created_at)
                 VALUES (?, ?, NULL, 'reserva', ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            )->execute([
                $item['material_id'],
                $ordemId,
                'OS ' . $codigoOs,
                $quantidade,
                $item['custo_unitario'],
                $saldoAnterior,
                $saldoAnterior,
                $reservadoAnterior,
                $reservadoPosterior,
                'Reserva automatica do orcamento aprovado via Viicio.',
            ]);
        }
    }

    private function itensOrcamentoWebhook(int $orcamentoId): array
    {
        return $this->queryPreparadaWebhook(
            "SELECT oi.*, p.codigo AS produto_codigo
             FROM orcamento_itens oi
             LEFT JOIN produtos p ON p.id = oi.produto_id
             WHERE oi.orcamento_id = ?
             ORDER BY oi.id",
            [$orcamentoId]
        );
    }

    private function materiaisDoItemWebhook(int $itemId): array
    {
        return $this->queryPreparadaWebhook(
            "SELECT oim.*, m.nome AS material_nome, m.codigo AS material_codigo
             FROM orcamento_item_materiais oim
             JOIN materiais m ON m.id = oim.material_id
             WHERE oim.orcamento_item_id = ?
             ORDER BY m.nome",
            [$itemId]
        );
    }

    private function ordemDoOrcamentoWebhook(int $orcamentoId): ?array
    {
        $stmt = db()->prepare("SELECT * FROM ordem_servicos WHERE orcamento_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$orcamentoId]);
        return $stmt->fetch() ?: null;
    }

    private function contaReceberDoOrcamentoWebhook(int $orcamentoId): ?array
    {
        $stmt = db()->prepare("SELECT * FROM contas_receber WHERE orcamento_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$orcamentoId]);
        return $stmt->fetch() ?: null;
    }

    private function margemMinima(): float
    {
        try {
            $stmt = db()->prepare("SELECT valor FROM configuracoes WHERE chave = 'margem_minima' LIMIT 1");
            $stmt->execute();
            return (float)($stmt->fetchColumn() ?: 30);
        } catch (\Exception $e) {
            return 30.0;
        }
    }

    private function prazoParaDataWebhook(?string $prazo): ?string
    {
        if (!$prazo) {
            return date('Y-m-d', strtotime('+7 days'));
        }
        if (preg_match('/(\d+)/', $prazo, $m)) {
            return date('Y-m-d', strtotime('+' . (int)$m[1] . ' days'));
        }
        return date('Y-m-d', strtotime('+7 days'));
    }

    private function gerarCodigoWebhook(string $prefixoBase, string $tabela): string
    {
        $prefixo = $prefixoBase . '-' . date('Ym') . '-';
        try {
            $stmt = db()->prepare("SELECT COUNT(*) FROM $tabela WHERE codigo LIKE ?");
            $stmt->execute([$prefixo . '%']);
            $seq = (int)$stmt->fetchColumn() + 1;
        } catch (\Exception $e) {
            $seq = 1;
        }
        return $prefixo . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    private function queryPreparadaWebhook(string $sql, array $params): array
    {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
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
            'viicio' => $this->statusConfig(!empty($empresa['token_whatsapp']), $empresa['modo_whatsapp'] ?? 'simulado'),
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
