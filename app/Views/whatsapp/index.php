<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$statusClasses = [
    'pendente' => 'badge-warning',
    'enviado' => 'badge-success',
    'erro' => 'badge-danger',
    'simulado' => 'badge-info',
];
$tipoLabels = [
    'manual' => 'Manual',
    'orcamento' => 'Orçamento',
    'producao' => 'Produção',
    'financeiro' => 'Financeiro',
    'campanha' => 'Campanha',
    'sistema' => 'Sistema',
];

function whatsappData(?string $data): string
{
    return $data ? date('d/m/Y H:i', strtotime($data)) : '-';
}
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-whatsapp"></i></div>
            <div class="kpi-value"><?= number_format($resumo['total']) ?></div>
            <div class="kpi-label">Envios registrados</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-check2-circle"></i></div>
            <div class="kpi-value"><?= number_format($resumo['enviados']) ?></div>
            <div class="kpi-label">Enviados</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-broadcast"></i></div>
            <div class="kpi-value"><?= number_format($resumo['simulados']) ?></div>
            <div class="kpi-label">Simulados</div>
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
                <h6 class="card-title"><i class="bi bi-send me-2 text-success-kroma"></i>Novo Envio</h6>
                <span class="badge <?= ($empresa['modo_whatsapp'] ?? 'simulado') === 'producao' ? 'badge-success' : 'badge-info' ?>">
                    <?= ($empresa['modo_whatsapp'] ?? 'simulado') === 'producao' ? 'Produção' : 'Simulado' ?>
                </span>
            </div>
            <form action="<?= APP_URL ?>/whatsapp/enviar" method="POST" class="p-3" data-loading>
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <label class="form-label">Cliente</label>
                <select class="form-select mb-3" name="cliente_id" id="whatsappCliente">
                    <option value="">-- Envio avulso --</option>
                    <?php foreach ($clientes as $cliente): ?>
                    <?php
                    $telefone = $cliente['whatsapp'] ?: $cliente['telefone'];
                    $prefs = [];
                    if (!empty($cliente['recebe_whatsapp'])) $prefs[] = 'geral';
                    if (!empty($cliente['recebe_campanha'])) $prefs[] = 'campanha';
                    if (!empty($cliente['recebe_producao'])) $prefs[] = 'produção';
                    if (!empty($cliente['recebe_financeiro'])) $prefs[] = 'financeiro';
                    ?>
                    <option value="<?= $cliente['id'] ?>" data-telefone="<?= htmlspecialchars($telefone) ?>" data-nome="<?= htmlspecialchars($cliente['nome']) ?>">
                        <?= htmlspecialchars($cliente['nome']) ?><?= $telefone ? ' - ' . htmlspecialchars($telefone) : '' ?><?= $prefs ? ' / ' . htmlspecialchars(implode(', ', $prefs)) : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <label class="form-label">Telefone</label>
                <input class="form-control mb-3" name="telefone" id="whatsappTelefone" data-mask="telefone" placeholder="(00) 00000-0000">

                <label class="form-label">Tipo</label>
                <select class="form-select mb-3" name="tipo">
                    <?php foreach ($tipoLabels as $value => $label): ?>
                    <option value="<?= $value ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="form-label">Template</label>
                <select class="form-select mb-3" name="template" id="whatsappTemplate">
                    <option value="">-- Sem template --</option>
                    <?php foreach ($templates as $value => $texto): ?>
                    <option value="<?= htmlspecialchars($value) ?>" data-template="<?= htmlspecialchars($texto) ?>"><?= htmlspecialchars(ucfirst($value)) ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="form-label">Mensagem</label>
                <textarea class="form-control mb-3" name="mensagem" id="whatsappMensagem" rows="6" required placeholder="Digite a mensagem ou selecione um template."></textarea>

                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-send"></i> Enviar / Registrar</button>
            </form>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-plug me-2 text-primary-kroma"></i>Configuração Viicio</h6>
                <a class="badge badge-primary text-decoration-none" href="<?= APP_URL ?>/empresa">Editar configuração</a>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <div class="d-flex justify-content-between">
                    <span>Modo</span>
                    <span class="badge <?= ($empresa['modo_whatsapp'] ?? 'simulado') === 'producao' ? 'badge-success' : 'badge-info' ?>"><?= htmlspecialchars($empresa['modo_whatsapp'] ?? 'simulado') ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Token</span>
                    <span class="badge <?= !empty($empresa['token_whatsapp']) ? 'badge-success' : 'badge-warning' ?>"><?= !empty($empresa['token_whatsapp']) ? 'Configurado' : 'Pendente' ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Endpoint</span>
                    <span class="badge <?= !empty($empresa['endpoint_whatsapp']) ? 'badge-success' : 'badge-warning' ?>"><?= !empty($empresa['endpoint_whatsapp']) ? 'Configurado' : 'Pendente' ?></span>
                </div>
                <span class="badge badge-secondary align-self-start">Payload enviado: phone + message</span>
                <span class="badge badge-info align-self-start">Modo simulado registra o envio sem chamar API externa</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-clock-history me-2 text-info"></i>Histórico de Envios</h6>
        <span class="badge badge-secondary"><?= count($logs) ?> recentes</span>
    </div>
    <div class="table-wrapper">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Cliente</th>
                    <th>Telefone</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th>Retorno</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><span class="badge badge-secondary"><?= whatsappData($log['created_at']) ?></span></td>
                    <td><?= htmlspecialchars($log['cliente_nome'] ?: '-') ?><div class="small text-muted"><?= htmlspecialchars($log['usuario_nome'] ?: '-') ?></div></td>
                    <td><span class="badge badge-info"><?= htmlspecialchars($log['telefone']) ?></span></td>
                    <td><span class="badge badge-primary"><?= htmlspecialchars($tipoLabels[$log['tipo']] ?? $log['tipo']) ?></span></td>
                    <td><span class="badge <?= $statusClasses[$log['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars(ucfirst($log['status'])) ?></span></td>
                    <td>
                        <?php if (!empty($log['erro'])): ?>
                        <span class="badge badge-danger"><?= htmlspecialchars($log['erro']) ?></span>
                        <?php elseif (!empty($log['http_status'])): ?>
                        <span class="badge badge-info">HTTP <?= (int)$log['http_status'] ?></span>
                        <?php elseif (!empty($log['resposta'])): ?>
                        <span class="badge badge-secondary"><?= htmlspecialchars(strlen($log['resposta']) > 80 ? substr($log['resposta'], 0, 80) . '...' : $log['resposta']) ?></span>
                        <?php else: ?>
                        <span class="badge badge-secondary">Sem retorno</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                <tr><td colspan="6"><span class="badge badge-secondary">Sem envios registrados</span></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cliente = document.getElementById('whatsappCliente');
    const telefone = document.getElementById('whatsappTelefone');
    const template = document.getElementById('whatsappTemplate');
    const mensagem = document.getElementById('whatsappMensagem');

    cliente?.addEventListener('change', function() {
        const option = this.selectedOptions[0];
        telefone.value = option?.dataset.telefone || '';
    });

    template?.addEventListener('change', function() {
        const option = this.selectedOptions[0];
        const clienteOption = cliente?.selectedOptions[0];
        const nome = clienteOption?.dataset.nome || 'cliente';
        if (option?.dataset.template) {
            mensagem.value = option.dataset.template.replaceAll('{cliente}', nome).replaceAll('{empresa}', '<?= htmlspecialchars($empresa['nome_fantasia'] ?? APP_NAME, ENT_QUOTES) ?>');
        }
    });
});
</script>
