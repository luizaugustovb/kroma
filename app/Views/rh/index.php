<?php
$statusClasses = [
    'ativo' => 'badge-success',
    'ferias' => 'badge-warning',
    'afastado' => 'badge-info',
    'demitido' => 'badge-secondary',
    'manutencao' => 'badge-warning',
    'inativo' => 'badge-secondary',
    'baixado' => 'badge-danger',
];
$ativos = array_filter($colaboradores, fn($c) => $c['status'] === 'ativo');
$custoHoraMedio = count($ativos) ? array_sum(array_map(fn($c) => (float)$c['custo_hora'], $ativos)) / count($ativos) : 0;
$equipamentosAtivos = array_filter($equipamentos, fn($e) => $e['status'] === 'ativo');
$manutencoes = array_filter(array_merge($equipamentos, $veiculos), fn($r) => !empty($r['manutencao_prevista']) && $r['manutencao_prevista'] <= date('Y-m-d', strtotime('+15 days')));
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-person-badge"></i></div>
            <div class="kpi-value"><?= count($colaboradores) ?></div>
            <div class="kpi-label">Colaboradores</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-check2-circle"></i></div>
            <div class="kpi-value"><?= count($ativos) ?></div>
            <div class="kpi-label">Ativos</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-clock-history"></i></div>
            <div class="kpi-value" style="font-size:22px">R$ <?= number_format($custoHoraMedio, 2, ',', '.') ?></div>
            <div class="kpi-label">Custo/h médio</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-tools"></i></div>
            <div class="kpi-value"><?= count($manutencoes) ?></div>
            <div class="kpi-label">Manutenções</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-person-badge me-2 text-primary-kroma"></i>Colaboradores</h6>
                <a class="btn btn-secondary btn-sm" href="<?= APP_URL ?>/rh/colaboradores/novo"><i class="bi bi-plus"></i> Novo</a>
            </div>
            <div class="table-wrapper">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Cargo</th>
                            <th>Setor</th>
                            <th>Custo/h</th>
                            <th>Contato</th>
                            <th>Status</th>
                            <th width="90">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($colaboradores as $colaborador): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($colaborador['nome']) ?></strong>
                                <?php if (!empty($colaborador['usuario_email'])): ?>
                                    <div><span class="badge <?= !empty($colaborador['usuario_ativo']) ? 'badge-success' : 'badge-secondary' ?>">Usuário vinculado</span></div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($colaborador['cargo'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($colaborador['setor'] ?: '-') ?></td>
                            <td><span class="badge badge-info">R$ <?= number_format((float)$colaborador['custo_hora'], 2, ',', '.') ?></span></td>
                            <td><?= htmlspecialchars($colaborador['whatsapp'] ?: ($colaborador['telefone'] ?: '-')) ?></td>
                            <td><span class="badge <?= $statusClasses[$colaborador['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($statusLabels[$colaborador['status']] ?? $colaborador['status']) ?></span></td>
                            <td><a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/rh/colaboradores/<?= $colaborador['id'] ?>/editar" title="Editar"><i class="bi bi-pencil"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($colaboradores)): ?>
                        <tr><td colspan="7"><span class="badge badge-secondary">Sem colaboradores cadastrados</span></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-diagram-3 me-2 text-success-kroma"></i>Setores e Cargos</h6>
                <div class="d-flex gap-2">
                    <a class="btn btn-secondary btn-sm" href="<?= APP_URL ?>/rh/setores/novo"><i class="bi bi-plus"></i> Setor</a>
                    <a class="btn btn-secondary btn-sm" href="<?= APP_URL ?>/rh/cargos/novo"><i class="bi bi-plus"></i> Cargo</a>
                </div>
            </div>
            <div class="p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($setores as $setor): ?>
                            <div class="border-kroma rounded-kroma p-2">
                                <div class="d-flex justify-content-between gap-2">
                                    <strong><?= htmlspecialchars($setor['nome']) ?></strong>
                                    <a class="badge badge-primary text-decoration-none" href="<?= APP_URL ?>/rh/setores/<?= $setor['id'] ?>/editar">Editar</a>
                                </div>
                                <div class="small text-muted"><?= htmlspecialchars($setor['responsavel_nome'] ?: 'Sem responsável') ?></div>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($setores)): ?><span class="badge badge-secondary align-self-start">Sem setores</span><?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($cargos as $cargo): ?>
                            <div class="border-kroma rounded-kroma p-2">
                                <div class="d-flex justify-content-between gap-2">
                                    <strong><?= htmlspecialchars($cargo['nome']) ?></strong>
                                    <a class="badge badge-primary text-decoration-none" href="<?= APP_URL ?>/rh/cargos/<?= $cargo['id'] ?>/editar">Editar</a>
                                </div>
                                <div class="small text-muted"><?= htmlspecialchars($cargo['setor_nome'] ?: 'Sem setor') ?> · R$ <?= number_format((float)$cargo['custo_hora_padrao'], 2, ',', '.') ?>/h</div>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($cargos)): ?><span class="badge badge-secondary align-self-start">Sem cargos</span><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-tools me-2 text-warning"></i>Equipamentos</h6>
                <a class="btn btn-secondary btn-sm" href="<?= APP_URL ?>/rh/equipamentos/novo"><i class="bi bi-plus"></i> Novo</a>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach ($equipamentos as $equipamento): ?>
                <div class="border-kroma rounded-kroma p-2">
                    <div class="d-flex justify-content-between gap-2">
                        <strong><?= htmlspecialchars($equipamento['nome']) ?></strong>
                        <span class="badge <?= $statusClasses[$equipamento['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($equipamentoStatus[$equipamento['status']] ?? $equipamento['status']) ?></span>
                    </div>
                    <div class="small text-muted"><?= htmlspecialchars($equipamento['setor_nome'] ?: '-') ?> · R$ <?= number_format((float)$equipamento['custo_hora'], 2, ',', '.') ?>/h</div>
                    <a class="badge badge-primary text-decoration-none" href="<?= APP_URL ?>/rh/equipamentos/<?= $equipamento['id'] ?>/editar">Editar</a>
                </div>
                <?php endforeach; ?>
                <?php if (empty($equipamentos)): ?><span class="badge badge-secondary align-self-start">Sem equipamentos</span><?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-truck me-2 text-info"></i>Veículos</h6>
                <a class="btn btn-secondary btn-sm" href="<?= APP_URL ?>/rh/veiculos/novo"><i class="bi bi-plus"></i> Novo</a>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach ($veiculos as $veiculo): ?>
                <div class="border-kroma rounded-kroma p-2">
                    <div class="d-flex justify-content-between gap-2">
                        <strong><?= htmlspecialchars($veiculo['nome']) ?></strong>
                        <span class="badge <?= $statusClasses[$veiculo['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($equipamentoStatus[$veiculo['status']] ?? $veiculo['status']) ?></span>
                    </div>
                    <div class="small text-muted"><?= htmlspecialchars($veiculo['placa'] ?: 'Sem placa') ?> · R$ <?= number_format((float)$veiculo['custo_km'], 2, ',', '.') ?>/km</div>
                    <a class="badge badge-primary text-decoration-none" href="<?= APP_URL ?>/rh/veiculos/<?= $veiculo['id'] ?>/editar">Editar</a>
                </div>
                <?php endforeach; ?>
                <?php if (empty($veiculos)): ?><span class="badge badge-secondary align-self-start">Sem veículos</span><?php endif; ?>
            </div>
        </div>
    </div>
</div>
