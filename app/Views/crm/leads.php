<?php
$estagioLabels = [
    'nova_solicitacao' => 'Nova Solicitação',
    'orcamento' => 'Orçamento',
    'orcamento_enviado' => 'Orçamento Enviado',
    'aprovado' => 'Aprovado',
    'em_producao' => 'Em Produção',
    'entregue' => 'Entregue',
    'pos_venda' => 'Pós-venda',
    'perdido' => 'Perdido',
];
?>

<div class="table-wrapper">
    <table class="table datatable">
        <thead>
            <tr>
                <th>Lead</th>
                <th>Cliente</th>
                <th>Produto</th>
                <th>Estágio</th>
                <th>Prioridade</th>
                <th>Valor</th>
                <th>Vendedor</th>
                <th width="110">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leads as $lead): ?>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($lead['nome']) ?></strong>
                    <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($lead['empresa'] ?? '') ?></div>
                </td>
                <td><?= htmlspecialchars($lead['cliente_nome'] ?? '-') ?></td>
                <td><?= htmlspecialchars($lead['produto_interesse'] ?? '-') ?></td>
                <td><span class="badge badge-primary"><?= $estagioLabels[$lead['estagio']] ?? $lead['estagio'] ?></span></td>
                <td>
                    <?php
                    $prioClass = ['baixa' => 'badge-secondary', 'media' => 'badge-info', 'alta' => 'badge-warning', 'urgente' => 'badge-danger'][$lead['prioridade']] ?? 'badge-info';
                    ?>
                    <span class="badge <?= $prioClass ?>"><?= ucfirst($lead['prioridade']) ?></span>
                </td>
                <td>R$ <?= number_format((float)$lead['valor_estimado'], 2, ',', '.') ?></td>
                <td><?= htmlspecialchars($lead['vendedor_nome'] ?? '-') ?></td>
                <td>
                    <div class="d-flex gap-1">
                        <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/crm/leads/<?= $lead['id'] ?>"><i class="bi bi-eye"></i></a>
                        <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/crm/leads/<?= $lead['id'] ?>/editar"><i class="bi bi-pencil"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
