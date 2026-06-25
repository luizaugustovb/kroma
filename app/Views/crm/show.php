<?php
$estagioLabels = [
    'novo_lead' => 'Novo Lead',
    'primeiro_contato' => 'Primeiro Contato',
    'orcamento_rapido' => 'Orçamento Rápido',
    'orcamento_ia' => 'Orçamento IA',
    'orcamento_enviado' => 'Orçamento Enviado',
    'negociacao' => 'Negociação',
    'aprovado' => 'Aprovado',
    'em_producao' => 'Em Produção',
    'entregue' => 'Entregue',
    'pos_venda' => 'Pós-venda',
    'recorrencia' => 'Recorrência',
    'perdido' => 'Perdido',
];
?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-person-lines-fill me-2 text-primary-kroma"></i>Dados do Lead</h6>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <tbody>
                        <tr><th>Nome</th><td><?= htmlspecialchars($lead['nome']) ?></td></tr>
                        <tr><th>Empresa</th><td><?= htmlspecialchars($lead['empresa'] ?? '-') ?></td></tr>
                        <tr><th>E-mail</th><td><?= htmlspecialchars($lead['email'] ?? '-') ?></td></tr>
                        <tr><th>Telefone</th><td><?= htmlspecialchars($lead['telefone'] ?? '-') ?></td></tr>
                        <tr><th>WhatsApp</th><td><?= htmlspecialchars($lead['whatsapp'] ?? '-') ?></td></tr>
                        <tr><th>Produto</th><td><?= htmlspecialchars($lead['produto_interesse'] ?? '-') ?></td></tr>
                        <tr><th>Descrição</th><td><?= nl2br(htmlspecialchars($lead['descricao'] ?? '-')) ?></td></tr>
                        <tr><th>Observações</th><td><?= nl2br(htmlspecialchars($lead['observacoes'] ?? '-')) ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (!empty($arquivosLead)): ?>
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-paperclip me-2 text-success-kroma"></i>Arquivos enviados</h6>
                <span class="badge badge-info"><?= count($arquivosLead) ?> arquivo(s)</span>
            </div>
            <div class="p-3 d-flex gap-2 flex-wrap">
                <?php foreach ($arquivosLead as $arquivo): ?>
                <a class="btn btn-secondary btn-sm" href="<?= htmlspecialchars($arquivo['url']) ?>" target="_blank" rel="noopener">
                    <i class="bi bi-box-arrow-up-right"></i> <?= htmlspecialchars($arquivo['nome']) ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <h6 class="card-title mb-3">Qualificação</h6>
            <div class="d-flex flex-column gap-2">
                <div class="d-flex justify-content-between"><span>Estágio</span><span class="badge badge-primary"><?= $estagioLabels[$lead['estagio']] ?? $lead['estagio'] ?></span></div>
                <div class="d-flex justify-content-between"><span>Prioridade</span><span class="badge badge-warning"><?= ucfirst($lead['prioridade']) ?></span></div>
                <div class="d-flex justify-content-between"><span>Temperatura</span><span class="badge badge-info"><?= ucfirst($lead['temperatura']) ?></span></div>
                <div class="d-flex justify-content-between"><span>Probabilidade</span><span class="badge badge-success"><?= (int)$lead['probabilidade'] ?>%</span></div>
                <div class="d-flex justify-content-between"><span>Valor</span><strong>R$ <?= number_format((float)$lead['valor_estimado'], 2, ',', '.') ?></strong></div>
            </div>
        </div>
        <div class="card">
            <h6 class="card-title mb-3">Responsáveis</h6>
            <p class="mb-2"><span class="badge badge-secondary">Vendedor</span> <?= htmlspecialchars($lead['vendedor_nome'] ?? '-') ?></p>
            <p class="mb-0"><span class="badge badge-secondary">Cliente</span> <?= htmlspecialchars($lead['cliente_nome'] ?? '-') ?></p>
        </div>
    </div>
</div>
