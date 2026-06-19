<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$painelStatusClasses = [
    'disponivel' => 'badge-success',
    'reservado' => 'badge-info',
    'instalado' => 'badge-primary',
    'manutencao' => 'badge-warning',
    'retirado' => 'badge-secondary',
    'cancelado' => 'badge-danger',
];
$locacaoStatusClasses = [
    'reservado' => 'badge-info',
    'instalado' => 'badge-primary',
    'manutencao' => 'badge-warning',
    'retirado' => 'badge-success',
    'cancelado' => 'badge-danger',
];

function ledDataHora(?string $data): string {
    return $data ? date('d/m/Y H:i', strtotime($data)) : '-';
}

function ledMoeda($valor): string {
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-display"></i></div>
            <div class="kpi-value"><?= number_format((int)$resumo['paineis']) ?></div>
            <div class="kpi-label">Painéis</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-check2-circle"></i></div>
            <div class="kpi-value"><?= number_format((int)$resumo['disponiveis']) ?></div>
            <div class="kpi-label">Disponíveis</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-calendar-range"></i></div>
            <div class="kpi-value"><?= number_format((int)$resumo['locacoes_ativas']) ?></div>
            <div class="kpi-label">Locações ativas</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-cash-coin"></i></div>
            <div class="kpi-value"><?= ledMoeda($resumo['faturamento_previsto']) ?></div>
            <div class="kpi-label">Faturamento previsto</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-funnel me-2 text-primary-kroma"></i>Filtros de locação</h6>
                <span class="badge badge-info"><?= count($locacoes) ?> registros</span>
            </div>
            <form method="GET" action="<?= APP_URL ?>/led" class="p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Todos</option>
                            <?php foreach ($locacaoStatusLabels as $value => $label): ?>
                            <option value="<?= $value ?>" <?= ($filtros['status'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Painel</label>
                        <select class="form-select" name="painel_id">
                            <option value="">Todos</option>
                            <?php foreach ($paineis as $painel): ?>
                            <option value="<?= $painel['id'] ?>" <?= (string)($filtros['painel_id'] ?? '') === (string)$painel['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($painel['codigo'] . ' - ' . $painel['nome']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cliente</label>
                        <select class="form-select" name="cliente_id">
                            <option value="">Todos</option>
                            <?php foreach ($contexto['clientes'] as $cliente): ?>
                            <option value="<?= $cliente['id'] ?>" <?= (string)($filtros['cliente_id'] ?? '') === (string)$cliente['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cliente['nome']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-secondary w-100" type="submit"><i class="bi bi-search"></i> Filtrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-speedometer2 me-2 text-info"></i>Status dos painéis</h6>
                <span class="badge badge-secondary"><?= count($paineis) ?> itens</span>
            </div>
            <div class="p-3 d-flex flex-wrap gap-2">
                <?php
                $totaisStatus = array_fill_keys(array_keys($painelStatusLabels), 0);
                foreach ($paineis as $painel) {
                    $totaisStatus[$painel['status']] = ($totaisStatus[$painel['status']] ?? 0) + 1;
                }
                ?>
                <?php foreach ($painelStatusLabels as $status => $label): ?>
                <span class="badge <?= $painelStatusClasses[$status] ?? 'badge-secondary' ?>"><?= htmlspecialchars($label) ?>: <?= (int)($totaisStatus[$status] ?? 0) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php if (Auth::pode('led.criar')): ?>
<div class="row g-3 mb-4">
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-plus-circle me-2 text-success-kroma"></i>Novo painel</h6>
                <span class="badge badge-success">Cadastro</span>
            </div>
            <form action="<?= APP_URL ?>/led/paineis/novo" method="POST" class="p-3" data-loading>
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Nome *</label>
                        <input class="form-control" name="nome" required placeholder="Painel P3 outdoor">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <?php foreach ($painelStatusLabels as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $value === 'disponivel' ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tamanho</label>
                        <input class="form-control" name="tamanho" placeholder="4x2 m">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Resolução</label>
                        <input class="form-control" name="resolucao" placeholder="P3 / 1920x960">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Valor diária</label>
                        <input class="form-control" type="number" step="0.01" min="0" name="valor_diaria" placeholder="0,00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Largura (m)</label>
                        <input class="form-control" type="number" step="0.01" min="0" name="largura_m">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Altura (m)</label>
                        <input class="form-control" type="number" step="0.01" min="0" name="altura_m">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Localização</label>
                        <input class="form-control" name="localizacao" placeholder="Estoque / cliente">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Observações</label>
                        <textarea class="form-control" name="observacoes" rows="3"></textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Cadastrar painel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-calendar-plus me-2 text-success-kroma"></i>Nova locação</h6>
                <span class="badge badge-info">Agenda / contrato / conteúdo</span>
            </div>
            <form action="<?= APP_URL ?>/led/locacoes/novo" method="POST" class="p-3" data-loading>
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Título *</label>
                        <input class="form-control" name="titulo" required placeholder="Locação para evento">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Painel *</label>
                        <select class="form-select" name="painel_id" required>
                            <option value="">-- Selecione --</option>
                            <?php foreach ($contexto['paineis_disponiveis'] as $painel): ?>
                            <option value="<?= $painel['id'] ?>">
                                <?= htmlspecialchars($painel['codigo'] . ' - ' . $painel['nome'] . ($painel['tamanho'] ? ' / ' . $painel['tamanho'] : '')) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Contrato</label>
                        <input class="form-control" name="contrato" placeholder="CTR-001">
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
                    <div class="col-md-4">
                        <label class="form-label">Agenda</label>
                        <select class="form-select" name="agenda_id">
                            <option value="">-- Sem agenda --</option>
                            <?php foreach ($contexto['agenda'] as $agenda): ?>
                            <option value="<?= $agenda['id'] ?>"><?= htmlspecialchars($agenda['codigo'] . ' - ' . $agenda['titulo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Início *</label>
                        <input class="form-control" type="datetime-local" name="data_inicio" required value="<?= date('Y-m-d\TH:00', strtotime('+1 day 08:00')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fim *</label>
                        <input class="form-control" type="datetime-local" name="data_fim" required value="<?= date('Y-m-d\TH:00', strtotime('+2 day 18:00')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Valor total</label>
                        <input class="form-control" type="number" step="0.01" min="0" name="valor_total" placeholder="0,00">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Local de instalação</label>
                        <input class="form-control" name="local_instalacao" placeholder="Endereço ou referência do local">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Playlist / conteúdo</label>
                        <textarea class="form-control" name="playlist" rows="3" placeholder="Vídeos, ordem de exibição, duração"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Arquivos e comprovantes</label>
                        <textarea class="form-control" name="arquivos" rows="3" placeholder="Links de arquivos, vídeos ou pasta compartilhada"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fotos</label>
                        <textarea class="form-control" name="fotos" rows="2" placeholder="Links de fotos da instalação"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Comprovantes</label>
                        <textarea class="form-control" name="comprovantes" rows="2" placeholder="Links de comprovantes e aceite"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Observações</label>
                        <textarea class="form-control" name="observacoes" rows="2"></textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-calendar-plus"></i> Reservar painel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-xl-5">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Painel</th>
                        <th>Status</th>
                        <th width="160">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paineis as $painel): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($painel['codigo']) ?></strong>
                            <div class="fw-bold"><?= htmlspecialchars($painel['nome']) ?></div>
                            <div class="d-flex gap-1 flex-wrap mt-1">
                                <?php if (!empty($painel['tamanho'])): ?>
                                <span class="badge badge-info"><?= htmlspecialchars($painel['tamanho']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($painel['resolucao'])): ?>
                                <span class="badge badge-secondary"><?= htmlspecialchars($painel['resolucao']) ?></span>
                                <?php endif; ?>
                                <?php if ((float)$painel['area_m2'] > 0): ?>
                                <span class="badge badge-secondary"><?= number_format((float)$painel['area_m2'], 2, ',', '.') ?> m²</span>
                                <?php endif; ?>
                            </div>
                            <div class="small text-muted mt-1"><?= htmlspecialchars($painel['localizacao'] ?: 'Sem localização') ?></div>
                            <span class="badge badge-success"><?= ledMoeda($painel['valor_diaria']) ?> / diária</span>
                        </td>
                        <td>
                            <span class="badge <?= $painelStatusClasses[$painel['status']] ?? 'badge-secondary' ?>">
                                <?= htmlspecialchars($painelStatusLabels[$painel['status']] ?? $painel['status']) ?>
                            </span>
                            <div><span class="badge badge-secondary"><?= (int)$painel['locacoes_ativas'] ?> locações ativas</span></div>
                        </td>
                        <td>
                            <?php if (Auth::pode('led.editar')): ?>
                            <form action="<?= APP_URL ?>/led/paineis/<?= $painel['id'] ?>/status" method="POST" data-loading>
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <select class="form-select form-select-sm mb-2" name="status">
                                    <?php foreach ($painelStatusLabels as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $painel['status'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-secondary btn-sm w-100" type="submit"><i class="bi bi-arrow-repeat"></i> Atualizar</button>
                            </form>
                            <?php else: ?>
                            <span class="badge badge-secondary">Sem permissão</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($paineis)): ?>
                    <tr><td colspan="3"><span class="badge badge-secondary">Nenhum painel cadastrado</span></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Locação</th>
                        <th>Cliente / local</th>
                        <th>Conteúdo</th>
                        <th>Status</th>
                        <th width="230">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($locacoes as $locacao): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($locacao['codigo']) ?></strong>
                            <div class="fw-bold"><?= htmlspecialchars($locacao['titulo']) ?></div>
                            <div class="d-flex gap-1 flex-wrap mt-1">
                                <span class="badge badge-primary"><?= htmlspecialchars($locacao['painel_codigo'] . ' - ' . $locacao['painel_nome']) ?></span>
                                <?php if (!empty($locacao['painel_tamanho'])): ?>
                                <span class="badge badge-info"><?= htmlspecialchars($locacao['painel_tamanho']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($locacao['contrato'])): ?>
                                <span class="badge badge-secondary"><?= htmlspecialchars($locacao['contrato']) ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="badge badge-info"><?= ledDataHora($locacao['data_inicio']) ?></span>
                            <span class="badge badge-secondary">Fim <?= ledDataHora($locacao['data_fim']) ?></span>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($locacao['cliente_nome'] ?? 'Sem cliente') ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($locacao['local_instalacao'] ?: '-') ?></div>
                            <div class="d-flex gap-1 flex-wrap mt-1">
                                <?php if (!empty($locacao['responsavel_nome'])): ?>
                                <span class="badge badge-secondary"><?= htmlspecialchars($locacao['responsavel_nome']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($locacao['agenda_id'])): ?>
                                <a class="badge badge-info text-decoration-none" href="<?= APP_URL ?>/agenda"><?= htmlspecialchars($locacao['agenda_codigo'] ?? 'Agenda') ?></a>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($locacao['playlist'])): ?>
                            <span class="badge badge-primary">Playlist</span>
                            <?php endif; ?>
                            <?php if (!empty($locacao['arquivos'])): ?>
                            <span class="badge badge-info">Arquivos</span>
                            <?php endif; ?>
                            <?php if (!empty($locacao['fotos'])): ?>
                            <span class="badge badge-success">Fotos</span>
                            <?php endif; ?>
                            <?php if (!empty($locacao['comprovantes'])): ?>
                            <span class="badge badge-secondary">Comprovantes</span>
                            <?php endif; ?>
                            <?php if (empty($locacao['playlist']) && empty($locacao['arquivos']) && empty($locacao['fotos']) && empty($locacao['comprovantes'])): ?>
                            <span class="badge badge-secondary">Sem anexos</span>
                            <?php endif; ?>
                            <div class="mt-1"><span class="badge badge-success"><?= ledMoeda($locacao['valor_total']) ?></span></div>
                        </td>
                        <td>
                            <span class="badge <?= $locacaoStatusClasses[$locacao['status']] ?? 'badge-secondary' ?>">
                                <?= htmlspecialchars($locacaoStatusLabels[$locacao['status']] ?? $locacao['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <?php if (Auth::pode('led.editar')): ?>
                                <?php foreach (['reservado' => 'Reservar', 'instalado' => 'Instalar', 'manutencao' => 'Manutenção', 'retirado' => 'Retirar', 'cancelado' => 'Cancelar'] as $status => $label): ?>
                                <?php if ($locacao['status'] !== $status): ?>
                                <form action="<?= APP_URL ?>/led/locacoes/<?= $locacao['id'] ?>/status" method="POST" data-loading>
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="status" value="<?= $status ?>">
                                    <button class="btn btn-secondary btn-sm" type="submit"><?= htmlspecialchars($label) ?></button>
                                </form>
                                <?php endif; ?>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <span class="badge badge-secondary">Sem permissão</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php if (!empty($locacao['observacoes']) || !empty($locacao['playlist']) || !empty($locacao['arquivos'])): ?>
                    <tr>
                        <td colspan="5">
                            <?php if (!empty($locacao['playlist'])): ?>
                            <span class="badge badge-primary">Playlist</span>
                            <span class="small text-muted"><?= nl2br(htmlspecialchars($locacao['playlist'])) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($locacao['arquivos'])): ?>
                            <span class="badge badge-info">Arquivos</span>
                            <span class="small text-muted"><?= nl2br(htmlspecialchars($locacao['arquivos'])) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($locacao['observacoes'])): ?>
                            <span class="badge badge-secondary">Observações</span>
                            <span class="small text-muted"><?= nl2br(htmlspecialchars($locacao['observacoes'])) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (empty($locacoes)): ?>
                    <tr><td colspan="5"><span class="badge badge-secondary">Nenhuma locação encontrada</span></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
