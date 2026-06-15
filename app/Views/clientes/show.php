<?php
$tipoLabels = [
    'cliente_final' => 'Cliente Final',
    'revenda' => 'Revenda',
    'parceiro' => 'Parceiro',
    'corporativo' => 'Corporativo',
    'orgao_publico' => 'Órgão Público',
];
?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card text-center">
            <div class="avatar avatar-xl mx-auto mb-3"><?= strtoupper(substr($cliente['nome'], 0, 1)) ?></div>
            <h2 class="h5 mb-1"><?= htmlspecialchars($cliente['nome']) ?></h2>
            <p class="text-secondary mb-2"><?= htmlspecialchars($cliente['nome_fantasia'] ?? '') ?></p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <span class="badge badge-primary"><?= $tipoLabels[$cliente['tipo_cliente']] ?? $cliente['tipo_cliente'] ?></span>
                <span class="badge <?= $cliente['status'] === 'ativo' ? 'badge-success' : 'badge-secondary' ?>"><?= ucfirst($cliente['status']) ?></span>
            </div>
        </div>
        <div class="card mt-3">
            <h6 class="card-title mb-3"><i class="bi bi-whatsapp me-2 text-success-kroma"></i>Preferências WhatsApp</h6>
            <?php
            $prefs = [
                'recebe_whatsapp' => 'Mensagens gerais',
                'recebe_campanha' => 'Campanhas',
                'recebe_producao' => 'Status de produção',
                'recebe_financeiro' => 'Financeiro',
            ];
            foreach ($prefs as $campo => $label):
            ?>
            <div class="d-flex justify-content-between mb-2">
                <span><?= $label ?></span>
                <span class="badge <?= !empty($cliente[$campo]) ? 'badge-success' : 'badge-secondary' ?>">
                    <?= !empty($cliente[$campo]) ? 'Sim' : 'Não' ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><h6 class="card-title">Dados do Cliente</h6></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <tbody>
                        <tr><th>CPF/CNPJ</th><td><?= htmlspecialchars($cliente['cpf_cnpj'] ?? '-') ?></td></tr>
                        <tr><th>E-mail</th><td><?= htmlspecialchars($cliente['email'] ?? '-') ?></td></tr>
                        <tr><th>WhatsApp</th><td><?= htmlspecialchars($cliente['whatsapp'] ?? '-') ?></td></tr>
                        <tr><th>Endereço</th><td><?= htmlspecialchars(trim(($cliente['endereco'] ?? '') . ', ' . ($cliente['numero'] ?? '') . ' - ' . ($cliente['cidade'] ?? '') . '/' . ($cliente['estado'] ?? ''), ' ,-\/')) ?></td></tr>
                        <tr><th>Vendedor</th><td><?= htmlspecialchars($cliente['vendedor_nome'] ?? '-') ?></td></tr>
                        <tr><th>Limite de Crédito</th><td>R$ <?= number_format((float)($cliente['limite_credito'] ?? 0), 2, ',', '.') ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-funnel me-2 text-primary-kroma"></i>Leads Relacionados</h6>
            </div>
            <?php if (empty($leads)): ?>
                <div class="p-4 text-center text-secondary">Nenhum lead vinculado a este cliente.</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Lead</th><th>Estágio</th><th>Valor</th><th>Data</th></tr></thead>
                    <tbody>
                        <?php foreach ($leads as $lead): ?>
                        <tr>
                            <td><a href="<?= APP_URL ?>/crm/leads/<?= $lead['id'] ?>"><?= htmlspecialchars($lead['nome']) ?></a></td>
                            <td><span class="badge badge-info"><?= htmlspecialchars(str_replace('_', ' ', $lead['estagio'])) ?></span></td>
                            <td>R$ <?= number_format((float)$lead['valor_estimado'], 2, ',', '.') ?></td>
                            <td><?= date('d/m/Y', strtotime($lead['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
