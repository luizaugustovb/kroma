<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$statusClasses = [
    'simulado' => 'badge-info',
    'concluido' => 'badge-success',
    'erro' => 'badge-danger',
];
$contextoLabels = [
    'atendimento' => 'Atendimento',
    'orcamento' => 'Orçamento',
    'produto' => 'Produto',
    'margem' => 'Margem',
    'followup' => 'Follow-up',
    'operacional' => 'Operacional',
    'livre' => 'Livre',
];

function iaData(?string $data): string
{
    return $data ? date('d/m/Y H:i', strtotime($data)) : '-';
}

function iaResumoTexto(?string $texto, int $limite = 220): string
{
    $texto = trim((string)$texto);
    return strlen($texto) > $limite ? substr($texto, 0, $limite) . '...' : $texto;
}
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-stars"></i></div>
            <div class="kpi-value"><?= number_format($resumo['total']) ?></div>
            <div class="kpi-label">Gerações registradas</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-check2-circle"></i></div>
            <div class="kpi-value"><?= number_format($resumo['concluidas']) ?></div>
            <div class="kpi-label">Concluídas</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-broadcast"></i></div>
            <div class="kpi-value"><?= number_format($resumo['simuladas']) ?></div>
            <div class="kpi-label">Simuladas</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon danger"><i class="bi bi-x-circle"></i></div>
            <div class="kpi-value"><?= number_format($resumo['erros']) ?></div>
            <div class="kpi-label">Erros</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-magic me-2 text-primary-kroma"></i>Nova Geração</h6>
                <span class="badge <?= ($empresa['modo_ia'] ?? 'simulado') === 'producao' ? 'badge-success' : 'badge-info' ?>">
                    <?= ($empresa['modo_ia'] ?? 'simulado') === 'producao' ? 'Produção' : 'Simulado' ?>
                </span>
            </div>
            <form action="<?= APP_URL ?>/ia/gerar" method="POST" class="p-3" data-loading>
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <label class="form-label">Cliente</label>
                <select class="form-select mb-3" name="cliente_id" id="iaCliente">
                    <option value="">-- Sem cliente --</option>
                    <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= $cliente['id'] ?>" data-nome="<?= htmlspecialchars($cliente['nome']) ?>">
                        <?= htmlspecialchars($cliente['nome']) ?><?= !empty($cliente['whatsapp']) ? ' - ' . htmlspecialchars($cliente['whatsapp']) : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <label class="form-label">Contexto</label>
                <select class="form-select mb-3" name="contexto" id="iaContexto">
                    <?php foreach ($contextoLabels as $value => $label): ?>
                    <option value="<?= $value ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="form-label">Template</label>
                <select class="form-select mb-3" id="iaTemplate">
                    <option value="">-- Sem template --</option>
                    <?php foreach ($templates as $value => $template): ?>
                    <option value="<?= htmlspecialchars($value) ?>" data-template="<?= htmlspecialchars($template['texto']) ?>"><?= htmlspecialchars($template['label']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="form-label">Prompt</label>
                <textarea class="form-control mb-3" name="prompt" id="iaPrompt" rows="8" required placeholder="Descreva o que a IA deve gerar, analisar ou organizar."></textarea>

                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-stars"></i> Gerar Resposta</button>
            </form>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-cpu me-2 text-info"></i>Configuração da IA</h6>
                <a class="badge badge-primary text-decoration-none" href="<?= APP_URL ?>/empresa">Editar configuração</a>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <div class="d-flex justify-content-between">
                    <span>Modo</span>
                    <span class="badge <?= ($empresa['modo_ia'] ?? 'simulado') === 'producao' ? 'badge-success' : 'badge-info' ?>"><?= htmlspecialchars($empresa['modo_ia'] ?? 'simulado') ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Provedor</span>
                    <span class="badge badge-primary"><?= htmlspecialchars($empresa['provedor_ia'] ?? 'openai') ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Modelo</span>
                    <span class="badge badge-secondary"><?= htmlspecialchars($empresa['modelo_ia'] ?? 'gpt-5.5') ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Chave OpenAI</span>
                    <span class="badge <?= !empty($empresa['chave_openai']) ? 'badge-success' : 'badge-warning' ?>"><?= !empty($empresa['chave_openai']) ? 'Configurada' : 'Pendente' ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Limite diário</span>
                    <span class="badge badge-info"><?= number_format((int)($empresa['limite_ia_diario'] ?? 100)) ?> gerações</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Usadas hoje</span>
                    <span class="badge <?= $resumo['hoje'] >= (int)($empresa['limite_ia_diario'] ?? 100) ? 'badge-danger' : 'badge-success' ?>"><?= number_format($resumo['hoje']) ?></span>
                </div>
                <span class="badge badge-secondary align-self-start">Produção OpenAI usa Responses API</span>
                <span class="badge badge-info align-self-start">Modo simulado registra sem consumo externo</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-clock-history me-2 text-info"></i>Histórico de Respostas</h6>
        <span class="badge badge-secondary"><?= count($logs) ?> recentes</span>
    </div>
    <div class="table-wrapper">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Contexto</th>
                    <th>Prompt / Resposta</th>
                    <th>Modelo</th>
                    <th>Status</th>
                    <th>Tokens</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><span class="badge badge-secondary"><?= iaData($log['created_at']) ?></span></td>
                    <td>
                        <span class="badge badge-primary"><?= htmlspecialchars($contextoLabels[$log['contexto']] ?? $log['contexto']) ?></span>
                        <div class="small text-muted"><?= htmlspecialchars($log['cliente_nome'] ?: '-') ?></div>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars(iaResumoTexto($log['prompt'], 120)) ?></strong>
                        <div class="small text-muted"><?= nl2br(htmlspecialchars(iaResumoTexto($log['resposta'] ?: $log['erro'], 240))) ?></div>
                    </td>
                    <td>
                        <span class="badge badge-info"><?= htmlspecialchars($log['provedor']) ?></span>
                        <span class="badge badge-secondary"><?= htmlspecialchars($log['modelo'] ?: '-') ?></span>
                    </td>
                    <td><span class="badge <?= $statusClasses[$log['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars(ucfirst($log['status'])) ?></span></td>
                    <td><span class="badge badge-secondary"><?= number_format((int)$log['tokens_entrada'] + (int)$log['tokens_saida']) ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                <tr><td colspan="6"><span class="badge badge-secondary">Sem respostas registradas</span></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const template = document.getElementById('iaTemplate');
    const contexto = document.getElementById('iaContexto');
    const prompt = document.getElementById('iaPrompt');
    const cliente = document.getElementById('iaCliente');

    template?.addEventListener('change', function() {
        const option = this.selectedOptions[0];
        if (!option?.dataset.template) {
            return;
        }
        contexto.value = option.value || 'livre';
        const nome = cliente?.selectedOptions[0]?.dataset.nome || 'cliente';
        prompt.value = option.dataset.template + "\n\nCliente: " + nome + "\nContexto: ";
        prompt.focus();
    });
});
</script>
