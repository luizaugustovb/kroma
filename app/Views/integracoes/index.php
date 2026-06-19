<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$empresa = $empresa ?? [];
$webhookStatusClasses = [
    'recebido' => 'badge-info',
    'processado' => 'badge-success',
    'erro' => 'badge-danger',
    'ignorado' => 'badge-warning',
];

if (!function_exists('integracaoData')) {
    function integracaoData(?string $data): string {
        return $data ? date('d/m/Y H:i', strtotime($data)) : '-';
    }
}

if (!function_exists('mascararSegredo')) {
    function mascararSegredo(?string $valor): string {
        $valor = (string)$valor;
        if ($valor === '') {
            return 'Pendente';
        }
        return strlen($valor) <= 8 ? 'Configurado' : substr($valor, 0, 4) . '...' . substr($valor, -4);
    }
}
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-whatsapp"></i></div>
            <div class="kpi-value"><span class="badge <?= $status['viicio']['class'] ?>"><?= htmlspecialchars($status['viicio']['label']) ?></span></div>
            <div class="kpi-label">Viicio WhatsApp</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-stars"></i></div>
            <div class="kpi-value"><span class="badge <?= $status['openai']['class'] ?>"><?= htmlspecialchars($status['openai']['label']) ?></span></div>
            <div class="kpi-label">OpenAI</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-gem"></i></div>
            <div class="kpi-value"><span class="badge <?= $status['gemini']['class'] ?>"><?= htmlspecialchars($status['gemini']['label']) ?></span></div>
            <div class="kpi-label">Gemini</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-bank"></i></div>
            <div class="kpi-value"><span class="badge <?= $status['asaas']['class'] ?>"><?= htmlspecialchars($status['asaas']['label']) ?></span></div>
            <div class="kpi-label">Asaas</div>
        </div>
    </div>
</div>

<form action="<?= APP_URL ?>/integracoes" method="POST" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="row g-3 mb-4">
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-whatsapp me-2 text-success-kroma"></i>WhatsApp Viicio</h6>
                    <span class="badge <?= $status['viicio']['class'] ?>"><?= htmlspecialchars($status['viicio']['label']) ?></span>
                </div>
                <div class="p-3">
                    <label class="form-label">Endpoint HTTP request</label>
                    <input class="form-control mb-3" name="endpoint_whatsapp" value="<?= htmlspecialchars($empresa['endpoint_whatsapp'] ?? '') ?>" placeholder="https://api.viicio.com/...">
                    <label class="form-label">Token Viicio</label>
                    <input class="form-control mb-3" name="token_whatsapp" value="<?= htmlspecialchars($empresa['token_whatsapp'] ?? '') ?>">
                    <label class="form-label">Modo WhatsApp</label>
                    <select class="form-select mb-3" name="modo_whatsapp">
                        <option value="simulado" <?= ($empresa['modo_whatsapp'] ?? 'simulado') === 'simulado' ? 'selected' : '' ?>>Simulado</option>
                        <option value="producao" <?= ($empresa['modo_whatsapp'] ?? '') === 'producao' ? 'selected' : '' ?>>Produção</option>
                    </select>
                    <label class="form-label">Token do webhook Viicio</label>
                    <input class="form-control mb-3" name="webhook_viicio_token" value="<?= htmlspecialchars($empresa['webhook_viicio_token'] ?? '') ?>" placeholder="Token opcional para validar entrada">
                    <label class="form-label">URL do webhook Viicio</label>
                    <input class="form-control mb-2" readonly value="<?= htmlspecialchars($webhookUrls['viicio']) ?>">
                    <span class="badge badge-info">Aceita token por query string ?token=... ou header X-Webhook-Token</span>
                    <span class="badge badge-secondary">Envio atual usa JSON com phone e message</span>
                </div>
                <div class="px-3 pb-3">
                    <button class="btn btn-secondary btn-sm" formaction="<?= APP_URL ?>/integracoes/testar" formmethod="POST" name="tipo" value="viicio" type="submit">
                        <i class="bi bi-plug"></i> Testar HTTP Viicio
                    </button>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-stars me-2 text-primary-kroma"></i>Inteligência Artificial</h6>
                    <span class="badge <?= ($empresa['modo_ia'] ?? 'simulado') === 'producao' ? 'badge-success' : 'badge-info' ?>"><?= htmlspecialchars($empresa['modo_ia'] ?? 'simulado') ?></span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Provedor IA</label>
                            <select class="form-select" name="provedor_ia">
                                <option value="openai" <?= ($empresa['provedor_ia'] ?? 'openai') === 'openai' ? 'selected' : '' ?>>OpenAI</option>
                                <option value="gemini" <?= ($empresa['provedor_ia'] ?? '') === 'gemini' ? 'selected' : '' ?>>Gemini</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Modo IA</label>
                            <select class="form-select" name="modo_ia">
                                <option value="simulado" <?= ($empresa['modo_ia'] ?? 'simulado') === 'simulado' ? 'selected' : '' ?>>Simulado</option>
                                <option value="producao" <?= ($empresa['modo_ia'] ?? '') === 'producao' ? 'selected' : '' ?>>Produção</option>
                            </select>
                        </div>
                    </div>
                    <label class="form-label mt-3">Chave OpenAI</label>
                    <input class="form-control mb-3" name="chave_openai" value="<?= htmlspecialchars($empresa['chave_openai'] ?? '') ?>">
                    <label class="form-label">Chave Gemini</label>
                    <input class="form-control mb-3" name="chave_gemini" value="<?= htmlspecialchars($empresa['chave_gemini'] ?? '') ?>">
                    <label class="form-label">Modelo IA</label>
                    <input class="form-control mb-3" name="modelo_ia" value="<?= htmlspecialchars($empresa['modelo_ia'] ?? 'gpt-5.5') ?>">
                    <label class="form-label">Limite diário IA</label>
                    <input class="form-control mb-3" type="number" min="1" name="limite_ia_diario" value="<?= (int)($empresa['limite_ia_diario'] ?? 100) ?>">
                    <label class="form-label">Prompt padrão IA</label>
                    <textarea class="form-control" name="prompt_padrao_ia" rows="3"><?= htmlspecialchars($empresa['prompt_padrao_ia'] ?? '') ?></textarea>
                </div>
                <div class="px-3 pb-3 d-flex gap-2 flex-wrap">
                    <button class="btn btn-secondary btn-sm" formaction="<?= APP_URL ?>/integracoes/testar" formmethod="POST" name="tipo" value="openai" type="submit">
                        <i class="bi bi-plug"></i> Testar OpenAI
                    </button>
                    <button class="btn btn-secondary btn-sm" formaction="<?= APP_URL ?>/integracoes/testar" formmethod="POST" name="tipo" value="gemini" type="submit">
                        <i class="bi bi-plug"></i> Testar Gemini
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-bank me-2 text-warning"></i>Asaas</h6>
                    <span class="badge <?= $status['asaas']['class'] ?>"><?= htmlspecialchars($status['asaas']['label']) ?></span>
                </div>
                <div class="p-3">
                    <label class="form-label">Chave Asaas</label>
                    <input class="form-control mb-3" name="chave_asaas" value="<?= htmlspecialchars($empresa['chave_asaas'] ?? '') ?>">
                    <label class="form-label">Ambiente Asaas</label>
                    <select class="form-select mb-3" name="ambiente_asaas">
                        <option value="sandbox" <?= ($empresa['ambiente_asaas'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' ?>>Sandbox</option>
                        <option value="producao" <?= ($empresa['ambiente_asaas'] ?? '') === 'producao' ? 'selected' : '' ?>>Produção</option>
                    </select>
                    <label class="form-label">Token do webhook Asaas</label>
                    <input class="form-control mb-3" name="webhook_asaas_token" value="<?= htmlspecialchars($empresa['webhook_asaas_token'] ?? '') ?>">
                    <label class="form-label">URL do webhook Asaas</label>
                    <input class="form-control mb-2" readonly value="<?= htmlspecialchars($webhookUrls['asaas']) ?>">
                    <span class="badge badge-secondary">Preparado para receber eventos de cobrança e pagamento</span>
                </div>
                <div class="px-3 pb-3">
                    <button class="btn btn-secondary btn-sm" formaction="<?= APP_URL ?>/integracoes/testar" formmethod="POST" name="tipo" value="asaas" type="submit">
                        <i class="bi bi-plug"></i> Testar HTTP Asaas
                    </button>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-shield-check me-2 text-info"></i>Resumo técnico</h6>
                    <span class="badge badge-info">HTTP request + webhook</span>
                </div>
                <div class="p-3 d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between border-kroma rounded-kroma p-2">
                        <span>Token Viicio</span>
                        <span class="badge <?= !empty($empresa['token_whatsapp']) ? 'badge-success' : 'badge-warning' ?>"><?= mascararSegredo($empresa['token_whatsapp'] ?? '') ?></span>
                    </div>
                    <div class="d-flex justify-content-between border-kroma rounded-kroma p-2">
                        <span>Webhook Viicio</span>
                        <span class="badge <?= $status['webhook_viicio']['class'] ?>"><?= htmlspecialchars($status['webhook_viicio']['label']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between border-kroma rounded-kroma p-2">
                        <span>Webhook Asaas</span>
                        <span class="badge <?= $status['webhook_asaas']['class'] ?>"><?= htmlspecialchars($status['webhook_asaas']['label']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between border-kroma rounded-kroma p-2">
                        <span>Registros recebidos</span>
                        <span class="badge badge-primary"><?= count($webhooks) ?></span>
                    </div>
                    <span class="badge badge-success align-self-start">Viicio pode enviar eventos para o webhook e o ERP pode enviar mensagens por HTTP request.</span>
                    <span class="badge badge-secondary align-self-start">Sem token configurado, o webhook aceita entrada para facilitar testes locais.</span>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Salvar integrações</button>
        <a class="btn btn-secondary" href="<?= APP_URL ?>/whatsapp"><i class="bi bi-whatsapp"></i> WhatsApp</a>
    </div>
</form>

<div class="card">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-clock-history me-2 text-info"></i>Webhooks recebidos</h6>
        <span class="badge badge-secondary"><?= count($webhooks) ?> recentes</span>
    </div>
    <div class="table-wrapper">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Origem</th>
                    <th>Evento</th>
                    <th>ID externo</th>
                    <th>Status</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($webhooks as $webhook): ?>
                <tr>
                    <td><span class="badge badge-secondary"><?= integracaoData($webhook['created_at']) ?></span></td>
                    <td><span class="badge badge-primary"><?= htmlspecialchars(strtoupper($webhook['origem'])) ?></span></td>
                    <td><span class="badge badge-info"><?= htmlspecialchars($webhook['evento'] ?: '-') ?></span></td>
                    <td><span class="badge badge-secondary"><?= htmlspecialchars($webhook['external_id'] ?: '-') ?></span></td>
                    <td>
                        <span class="badge <?= $webhookStatusClasses[$webhook['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($webhook['status']) ?></span>
                        <?php if (!empty($webhook['erro'])): ?>
                        <div><span class="badge badge-danger"><?= htmlspecialchars($webhook['erro']) ?></span></div>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge badge-secondary"><?= htmlspecialchars($webhook['ip_origem'] ?: '-') ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($webhooks)): ?>
                <tr><td colspan="6"><span class="badge badge-secondary">Nenhum webhook recebido</span></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
