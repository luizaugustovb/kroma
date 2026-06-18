<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$statusClasses = [
    'aberto' => 'badge-info',
    'em_andamento' => 'badge-primary',
    'aguardando' => 'badge-warning',
    'concluido' => 'badge-success',
    'cancelado' => 'badge-danger',
];
$prioridadeClasses = [
    'baixa' => 'badge-secondary',
    'media' => 'badge-info',
    'alta' => 'badge-warning',
    'urgente' => 'badge-danger',
];
$tipoClasses = [
    'comentario' => 'badge-info',
    'status' => 'badge-warning',
    'sistema' => 'badge-secondary',
];
$tipoLabels = [
    'comentario' => 'Comentário',
    'status' => 'Status',
    'sistema' => 'Sistema',
];
$atrasado = !empty($chamado['prazo']) && strtotime($chamado['prazo']) < time() && !in_array($chamado['status'], ['concluido','cancelado'], true);

if (!function_exists('chamadoShowDataHora')) {
    function chamadoShowDataHora(?string $data): string
    {
        return $data ? date('d/m/Y H:i', strtotime($data)) : '-';
    }
}
?>

<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-ticket-detailed me-2 text-primary-kroma"></i>Resumo do Chamado</h6>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge <?= $statusClasses[$chamado['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($statusLabels[$chamado['status']] ?? $chamado['status']) ?></span>
                    <span class="badge <?= $prioridadeClasses[$chamado['prioridade']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($prioridadeLabels[$chamado['prioridade']] ?? $chamado['prioridade']) ?></span>
                    <span class="badge <?= $atrasado ? 'badge-danger' : 'badge-info' ?>"><?= $atrasado ? 'Atrasado ' : 'Prazo ' ?><?= chamadoShowDataHora($chamado['prazo']) ?></span>
                </div>
            </div>
            <div class="p-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <span class="form-label">Código</span>
                        <div class="fw-bold"><?= htmlspecialchars($chamado['codigo']) ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Setor</span>
                        <div><span class="badge badge-info"><?= htmlspecialchars($chamado['setor'] ?: 'Sem setor') ?></span></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Criado em</span>
                        <div><?= chamadoShowDataHora($chamado['created_at']) ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Solicitante</span>
                        <div><?= htmlspecialchars($chamado['solicitante_nome'] ?: '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Responsável</span>
                        <div><?= htmlspecialchars($chamado['responsavel_nome'] ?: '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="form-label">Conclusão</span>
                        <div><?= chamadoShowDataHora($chamado['concluido_at']) ?></div>
                    </div>
                    <div class="col-12">
                        <span class="form-label">Descrição</span>
                        <div><?= nl2br(htmlspecialchars($chamado['descricao'] ?: '-')) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-check2-circle me-2 text-success-kroma"></i>Ações</h6>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php if (Auth::pode('chamados.editar')): ?>
                <form action="<?= APP_URL ?>/chamados/<?= $chamado['id'] ?>/status" method="POST" class="d-flex gap-2" data-loading>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <select class="form-select" name="status">
                        <?php foreach ($statusLabels as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $chamado['status'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary" type="submit" title="Atualizar status"><i class="bi bi-arrow-repeat"></i></button>
                </form>
                <a class="btn btn-secondary" href="<?= APP_URL ?>/chamados/<?= $chamado['id'] ?>/editar"><i class="bi bi-pencil"></i> Editar Chamado</a>
                <?php else: ?>
                <span class="badge badge-secondary align-self-start">Somente leitura</span>
                <?php endif; ?>
                <a class="btn btn-secondary" href="<?= APP_URL ?>/chamados"><i class="bi bi-arrow-left"></i> Voltar</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-link-45deg me-2 text-info"></i>Vínculos</h6>
                <span class="badge badge-secondary">Opcional</span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php if (!empty($chamado['cliente_id'])): ?>
                <a class="badge badge-primary text-decoration-none align-self-start" href="<?= APP_URL ?>/clientes/<?= $chamado['cliente_id'] ?>">Cliente: <?= htmlspecialchars($chamado['cliente_nome']) ?></a>
                <?php else: ?>
                <span class="badge badge-secondary align-self-start">Sem cliente vinculado</span>
                <?php endif; ?>

                <?php if (!empty($chamado['orcamento_id'])): ?>
                <a class="badge badge-info text-decoration-none align-self-start" href="<?= APP_URL ?>/orcamentos/<?= $chamado['orcamento_id'] ?>">Orçamento: <?= htmlspecialchars($chamado['orcamento_codigo']) ?></a>
                <?php else: ?>
                <span class="badge badge-secondary align-self-start">Sem orçamento vinculado</span>
                <?php endif; ?>

                <?php if (!empty($chamado['ordem_servico_id'])): ?>
                <a class="badge badge-warning text-decoration-none align-self-start" href="<?= APP_URL ?>/producao/<?= $chamado['ordem_servico_id'] ?>">OS: <?= htmlspecialchars($chamado['os_codigo']) ?></a>
                <?php else: ?>
                <span class="badge badge-secondary align-self-start">Sem OS vinculada</span>
                <?php endif; ?>

                <?php if (!empty($chamado['compra_id'])): ?>
                <a class="badge badge-success text-decoration-none align-self-start" href="<?= APP_URL ?>/compras/<?= $chamado['compra_id'] ?>">Compra: <?= htmlspecialchars($chamado['compra_codigo']) ?></a>
                <?php else: ?>
                <span class="badge badge-secondary align-self-start">Sem compra vinculada</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-chat-left-text me-2 text-primary-kroma"></i>Novo Comentário</h6>
                <span class="badge badge-info">Histórico</span>
            </div>
            <form action="<?= APP_URL ?>/chamados/<?= $chamado['id'] ?>/comentarios" method="POST" class="p-3" data-loading>
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <textarea class="form-control mb-3" name="comentario" rows="4" required placeholder="Registre andamento, bloqueio, retorno do setor ou conclusão."></textarea>
                <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Adicionar Comentário</button>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-clock-history me-2 text-info"></i>Comentários e Histórico</h6>
        <span class="badge badge-secondary"><?= count($comentarios) ?> registros</span>
    </div>
    <div class="p-3 d-flex flex-column gap-3">
        <?php foreach ($comentarios as $comentario): ?>
        <div class="border-kroma rounded-kroma p-3">
            <div class="d-flex justify-content-between gap-2 flex-wrap mb-2">
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge <?= $tipoClasses[$comentario['tipo']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($tipoLabels[$comentario['tipo']] ?? $comentario['tipo']) ?></span>
                    <span class="badge badge-secondary"><?= htmlspecialchars($comentario['usuario_nome'] ?: 'Sistema') ?></span>
                </div>
                <span class="badge badge-info"><?= chamadoShowDataHora($comentario['created_at']) ?></span>
            </div>
            <div><?= nl2br(htmlspecialchars($comentario['comentario'])) ?></div>
            <?php if (!empty($comentario['status_anterior']) || !empty($comentario['status_novo'])): ?>
            <div class="d-flex gap-2 flex-wrap mt-2">
                <span class="badge badge-secondary"><?= htmlspecialchars($statusLabels[$comentario['status_anterior']] ?? $comentario['status_anterior']) ?></span>
                <span class="badge badge-primary">para</span>
                <span class="badge <?= $statusClasses[$comentario['status_novo']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($statusLabels[$comentario['status_novo']] ?? $comentario['status_novo']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if (empty($comentarios)): ?>
        <span class="badge badge-secondary align-self-start">Sem comentários registrados</span>
        <?php endif; ?>
    </div>
</div>
