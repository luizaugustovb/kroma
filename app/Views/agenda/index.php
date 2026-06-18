<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$statusClasses = [
    'agendada' => 'badge-info',
    'em_rota' => 'badge-primary',
    'em_execucao' => 'badge-warning',
    'concluida' => 'badge-success',
    'cancelada' => 'badge-danger',
];
$prioridadeClasses = [
    'baixa' => 'badge-secondary',
    'media' => 'badge-info',
    'alta' => 'badge-warning',
    'urgente' => 'badge-danger',
];

function agendaDataHora(?string $data): string {
    return $data ? date('d/m/Y H:i', strtotime($data)) : '-';
}

function agendaHora(?string $data): string {
    return $data ? date('H:i', strtotime($data)) : '-';
}
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-calendar-check"></i></div>
            <div class="kpi-value"><?= number_format((int)$resumo['total']) ?></div>
            <div class="kpi-label">Agendamentos</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-truck"></i></div>
            <div class="kpi-value"><?= number_format((int)$resumo['em_rota']) ?></div>
            <div class="kpi-label">Em rota</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-tools"></i></div>
            <div class="kpi-value"><?= number_format((int)$resumo['em_execucao']) ?></div>
            <div class="kpi-label">Em execução</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-check2-circle"></i></div>
            <div class="kpi-value"><?= number_format((int)$resumo['concluida']) ?></div>
            <div class="kpi-label">Concluídas</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-funnel me-2 text-primary-kroma"></i>Filtros</h6>
                <span class="badge badge-info"><?= date('d/m/Y', strtotime($filtros['data'] ?: date('Y-m-d'))) ?></span>
            </div>
            <form method="GET" action="<?= APP_URL ?>/agenda" class="p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Data</label>
                        <input class="form-control" type="date" name="data" value="<?= htmlspecialchars($filtros['data']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Todos</option>
                            <?php foreach ($statusLabels as $value => $label): ?>
                            <option value="<?= $value ?>" <?= ($filtros['status'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Responsável</label>
                        <select class="form-select" name="responsavel_id">
                            <option value="">Todos</option>
                            <?php foreach ($contexto['responsaveis'] as $responsavel): ?>
                            <option value="<?= $responsavel['id'] ?>" <?= (string)($filtros['responsavel_id'] ?? '') === (string)$responsavel['id'] ? 'selected' : '' ?>><?= htmlspecialchars($responsavel['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-secondary w-100" type="submit"><i class="bi bi-search"></i> Filtrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-speedometer2 me-2 text-info"></i>Status do dia</h6>
                <span class="badge badge-secondary"><?= (int)$resumo['cancelada'] ?> canceladas</span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach ($statusLabels as $status => $label): ?>
                <div class="d-flex justify-content-between align-items-center">
                    <span><?= htmlspecialchars($label) ?></span>
                    <span class="badge <?= $statusClasses[$status] ?? 'badge-secondary' ?>"><?= (int)($resumo[$status] ?? 0) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php if (Auth::pode('agenda.criar')): ?>
<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-plus-circle me-2 text-success-kroma"></i>Novo agendamento</h6>
        <span class="badge badge-info">Instalação / entrega externa</span>
    </div>
    <form action="<?= APP_URL ?>/agenda/novo" method="POST" class="p-3" data-loading>
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Título *</label>
                <input class="form-control" name="titulo" required placeholder="Instalação de fachada">
            </div>
            <div class="col-md-4">
                <label class="form-label">Cliente</label>
                <select class="form-select" name="cliente_id">
                    <option value="">-- Sem cliente --</option>
                    <?php foreach ($contexto['clientes'] as $cliente): ?>
                    <option value="<?= $cliente['id'] ?>"><?= htmlspecialchars($cliente['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Responsável</label>
                <select class="form-select" name="responsavel_id">
                    <option value="">-- Sem responsável --</option>
                    <?php foreach ($contexto['responsaveis'] as $responsavel): ?>
                    <option value="<?= $responsavel['id'] ?>"><?= htmlspecialchars($responsavel['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Início *</label>
                <input class="form-control" type="datetime-local" name="data_inicio" required value="<?= date('Y-m-d\TH:00', strtotime($filtros['data'] . ' 08:00')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fim</label>
                <input class="form-control" type="datetime-local" name="data_fim" value="<?= date('Y-m-d\TH:00', strtotime($filtros['data'] . ' 10:00')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Prioridade</label>
                <select class="form-select" name="prioridade">
                    <?php foreach ($prioridadeLabels as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $value === 'media' ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Equipe</label>
                <input class="form-control" name="equipe" placeholder="Equipe externa">
            </div>
            <div class="col-md-4">
                <label class="form-label">OS</label>
                <select class="form-select" name="ordem_servico_id">
                    <option value="">-- Sem OS --</option>
                    <?php foreach ($contexto['ordens'] as $ordem): ?>
                    <option value="<?= $ordem['id'] ?>"><?= htmlspecialchars($ordem['codigo'] . ' - ' . $ordem['titulo']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Orçamento</label>
                <select class="form-select" name="orcamento_id">
                    <option value="">-- Sem orçamento --</option>
                    <?php foreach ($contexto['orcamentos'] as $orcamento): ?>
                    <option value="<?= $orcamento['id'] ?>"><?= htmlspecialchars($orcamento['codigo'] . ' - ' . $orcamento['titulo']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Cidade / UF</label>
                <div class="d-flex gap-2">
                    <input class="form-control" name="cidade" placeholder="Cidade">
                    <input class="form-control" name="estado" maxlength="2" placeholder="UF" style="max-width:90px">
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Endereço</label>
                <input class="form-control" name="endereco" placeholder="Rua, número, bairro e referência">
            </div>
            <div class="col-md-6">
                <label class="form-label">Checklist</label>
                <textarea class="form-control" name="checklist" rows="3" placeholder="- Conferir medidas&#10;- Separar ferramentas&#10;- Registrar fotos"></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Observações</label>
                <textarea class="form-control" name="observacoes" rows="3"></textarea>
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button class="btn btn-primary" type="submit"><i class="bi bi-calendar-plus"></i> Agendar</button>
            </div>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-clock-history me-2 text-primary-kroma"></i>Linha do tempo</h6>
                <span class="badge badge-info"><?= count($agendamentos) ?> itens</span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach ($agendamentos as $agenda): ?>
                <div class="border-kroma rounded-kroma p-2">
                    <div class="d-flex justify-content-between gap-2">
                        <strong><?= agendaHora($agenda['data_inicio']) ?> · <?= htmlspecialchars($agenda['codigo']) ?></strong>
                        <span class="badge <?= $statusClasses[$agenda['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($statusLabels[$agenda['status']] ?? $agenda['status']) ?></span>
                    </div>
                    <div class="fw-bold"><?= htmlspecialchars($agenda['titulo']) ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($agenda['cliente_nome'] ?? 'Sem cliente') ?> · <?= htmlspecialchars($agenda['responsavel_nome'] ?? 'Sem responsável') ?></div>
                    <div class="mt-2 d-flex gap-1 flex-wrap">
                        <span class="badge <?= $prioridadeClasses[$agenda['prioridade']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($prioridadeLabels[$agenda['prioridade']] ?? $agenda['prioridade']) ?></span>
                        <?php if (!empty($agenda['cidade']) || !empty($agenda['estado'])): ?>
                        <span class="badge badge-secondary"><?= htmlspecialchars(trim(($agenda['cidade'] ?? '') . ' ' . ($agenda['estado'] ?? ''))) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($agendamentos)): ?>
                <span class="badge badge-secondary align-self-start">Sem agendamentos para este filtro</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Agenda</th>
                        <th>Cliente</th>
                        <th>Local</th>
                        <th>Vínculos</th>
                        <th>Status</th>
                        <th width="260">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agendamentos as $agenda): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($agenda['codigo']) ?></strong>
                            <div class="fw-bold"><?= htmlspecialchars($agenda['titulo']) ?></div>
                            <span class="badge badge-info"><?= agendaDataHora($agenda['data_inicio']) ?></span>
                            <?php if (!empty($agenda['data_fim'])): ?>
                            <span class="badge badge-secondary">Fim <?= agendaHora($agenda['data_fim']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($agenda['cliente_nome'] ?? '-') ?>
                            <?php if (!empty($agenda['cliente_whatsapp'])): ?>
                            <div><span class="badge badge-success"><?= htmlspecialchars($agenda['cliente_whatsapp']) ?></span></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($agenda['endereco'] ?: '-') ?></div>
                            <span class="badge badge-secondary"><?= htmlspecialchars(trim(($agenda['cidade'] ?? '') . ' ' . ($agenda['estado'] ?? '')) ?: '-') ?></span>
                        </td>
                        <td>
                            <?php if (!empty($agenda['ordem_servico_id'])): ?>
                            <a class="badge badge-primary text-decoration-none" href="<?= APP_URL ?>/producao/<?= $agenda['ordem_servico_id'] ?>">OS <?= htmlspecialchars($agenda['os_codigo'] ?? $agenda['ordem_servico_id']) ?></a>
                            <?php endif; ?>
                            <?php if (!empty($agenda['orcamento_id'])): ?>
                            <a class="badge badge-info text-decoration-none" href="<?= APP_URL ?>/orcamentos/<?= $agenda['orcamento_id'] ?>">Orçamento <?= htmlspecialchars($agenda['orcamento_codigo'] ?? $agenda['orcamento_id']) ?></a>
                            <?php endif; ?>
                            <?php if (empty($agenda['ordem_servico_id']) && empty($agenda['orcamento_id'])): ?>
                            <span class="badge badge-secondary">Sem vínculo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $statusClasses[$agenda['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($statusLabels[$agenda['status']] ?? $agenda['status']) ?></span>
                            <div><span class="badge <?= $prioridadeClasses[$agenda['prioridade']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($prioridadeLabels[$agenda['prioridade']] ?? $agenda['prioridade']) ?></span></div>
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <?php if (Auth::pode('agenda.editar') && !in_array($agenda['status'], ['concluida','cancelada'], true)): ?>
                                <?php foreach (['em_rota' => 'Rota', 'em_execucao' => 'Executar', 'concluida' => 'Concluir'] as $status => $label): ?>
                                <?php if ($agenda['status'] !== $status): ?>
                                <form action="<?= APP_URL ?>/agenda/<?= $agenda['id'] ?>/status" method="POST" data-loading>
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="status" value="<?= $status ?>">
                                    <button class="btn btn-secondary btn-sm" type="submit"><?= $label ?></button>
                                </form>
                                <?php endif; ?>
                                <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if (Auth::pode('agenda.excluir') && $agenda['status'] !== 'cancelada'): ?>
                                <form action="<?= APP_URL ?>/agenda/<?= $agenda['id'] ?>/excluir" method="POST" data-loading>
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <button class="btn btn-danger btn-sm" type="submit"><i class="bi bi-x-circle"></i></button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php if (!empty($agenda['checklist']) || !empty($agenda['observacoes'])): ?>
                    <tr>
                        <td colspan="6">
                            <?php if (!empty($agenda['checklist'])): ?>
                            <span class="badge badge-info">Checklist</span>
                            <span class="small text-muted"><?= nl2br(htmlspecialchars($agenda['checklist'])) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($agenda['observacoes'])): ?>
                            <span class="badge badge-secondary">Observações</span>
                            <span class="small text-muted"><?= nl2br(htmlspecialchars($agenda['observacoes'])) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (empty($agendamentos)): ?>
                    <tr><td colspan="6"><span class="badge badge-secondary">Sem agendamentos</span></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
