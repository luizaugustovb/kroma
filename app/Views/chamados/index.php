<?php
use App\Services\Auth;

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

if (!function_exists('chamadoDataHora')) {
    function chamadoDataHora(?string $data): string
    {
        return $data ? date('d/m/Y H:i', strtotime($data)) : '-';
    }
}
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-ticket-detailed"></i></div>
            <div class="kpi-value"><?= number_format($resumo['total']) ?></div>
            <div class="kpi-label">Chamados cadastrados</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-value"><?= number_format($resumo['abertos']) ?></div>
            <div class="kpi-label">Em atendimento</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon danger"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="kpi-value"><?= number_format($resumo['atrasados']) ?></div>
            <div class="kpi-label">Atrasados</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-check2-circle"></i></div>
            <div class="kpi-value"><?= number_format($resumo['concluidos']) ?></div>
            <div class="kpi-label">Concluídos</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-list-task me-2 text-primary-kroma"></i>Chamados</h6>
                <span class="badge <?= $resumo['atrasados'] > 0 ? 'badge-danger' : 'badge-success' ?>">
                    <?= number_format($resumo['atrasados']) ?> atrasados
                </span>
            </div>
            <div class="table-wrapper">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Setor</th>
                            <th>Responsável</th>
                            <th>Prioridade</th>
                            <th>Prazo</th>
                            <th>Status</th>
                            <th width="120">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chamados as $chamado): ?>
                        <?php
                        $atrasado = !empty($chamado['prazo']) && strtotime($chamado['prazo']) < time() && !in_array($chamado['status'], ['concluido','cancelado'], true);
                        $prazoClass = $atrasado ? 'badge-danger' : (!empty($chamado['prazo']) ? 'badge-info' : 'badge-secondary');
                        ?>
                        <tr>
                            <td>
                                <a href="<?= APP_URL ?>/chamados/<?= $chamado['id'] ?>"><strong><?= htmlspecialchars($chamado['codigo']) ?></strong></a>
                                <div class="small text-muted"><?= htmlspecialchars($chamado['titulo']) ?></div>
                                <?php if (!empty($chamado['cliente_nome'])): ?>
                                <span class="badge badge-secondary"><?= htmlspecialchars($chamado['cliente_nome']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($chamado['setor'] ?: 'Sem setor') ?></span></td>
                            <td><span class="badge badge-secondary"><?= htmlspecialchars($chamado['responsavel_nome'] ?: 'Sem responsável') ?></span></td>
                            <td><span class="badge <?= $prioridadeClasses[$chamado['prioridade']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($prioridadeLabels[$chamado['prioridade']] ?? $chamado['prioridade']) ?></span></td>
                            <td><span class="badge <?= $prazoClass ?>"><?= $atrasado ? 'Atrasado ' : '' ?><?= chamadoDataHora($chamado['prazo']) ?></span></td>
                            <td><span class="badge <?= $statusClasses[$chamado['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($statusLabels[$chamado['status']] ?? $chamado['status']) ?></span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/chamados/<?= $chamado['id'] ?>" title="Ver"><i class="bi bi-eye"></i></a>
                                    <?php if (Auth::pode('chamados.editar')): ?>
                                    <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/chamados/<?= $chamado['id'] ?>/editar" title="Editar"><i class="bi bi-pencil"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($chamados)): ?>
                        <tr><td colspan="7"><span class="badge badge-secondary">Sem chamados cadastrados</span></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-diagram-3 me-2 text-info"></i>Dashboard por Setor</h6>
                <span class="badge badge-secondary"><?= count($porSetor) ?> setores</span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach ($porSetor as $item): ?>
                <div class="d-flex justify-content-between align-items-center">
                    <span><?= htmlspecialchars($item['label']) ?></span>
                    <span class="badge badge-primary"><?= (int)$item['total'] ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($porSetor)): ?>
                <span class="badge badge-success align-self-start">Sem pendências por setor</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-person-check me-2 text-success-kroma"></i>Dashboard por Responsável</h6>
                <span class="badge badge-secondary"><?= count($porResponsavel) ?> responsáveis</span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach ($porResponsavel as $item): ?>
                <div class="d-flex justify-content-between align-items-center">
                    <span><?= htmlspecialchars($item['label']) ?></span>
                    <span class="badge badge-info"><?= (int)$item['total'] ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($porResponsavel)): ?>
                <span class="badge badge-success align-self-start">Sem pendências por responsável</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
