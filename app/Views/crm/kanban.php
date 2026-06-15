<?php
/**
 * View: Kanban CRM — KROMA PRINT ERP
 */

$estagios = $estagios ?? [];
$leads    = $leads ?? [];

$prioridadeIcons = [
    'baixa'   => ['cor' => 'var(--text-muted)', 'icon' => 'bi-arrow-down'],
    'media'   => ['cor' => 'var(--kroma-info)', 'icon' => 'bi-arrow-right'],
    'alta'    => ['cor' => 'var(--kroma-warning)', 'icon' => 'bi-arrow-up'],
    'urgente' => ['cor' => 'var(--kroma-danger)', 'icon' => 'bi-exclamation-triangle-fill'],
];
?>

<style>
.kanban-board { padding-bottom: 24px; }
.kanban-column-header { border-left: 3px solid var(--col-color, var(--kroma-primary)); }
</style>

<!-- Filtros -->
<div class="card mb-3" style="padding: 14px 20px;">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div class="input-group" style="max-width:220px;">
            <i class="bi bi-search input-group-icon"></i>
            <input type="text" class="form-control form-control-sm" id="buscaKanban" placeholder="Buscar lead...">
        </div>
        <select class="form-select form-select-sm" style="max-width:160px" id="filtroVendedor">
            <option value="">Todos os vendedores</option>
        </select>
        <select class="form-select form-select-sm" style="max-width:140px" id="filtroTemperatura">
            <option value="">Temperatura</option>
            <option value="frio">Frio</option>
            <option value="morno">Morno</option>
            <option value="quente">Quente</option>
        </select>
        <div class="ms-auto d-flex gap-2">
            <a href="<?= APP_URL ?>/crm/leads" class="btn btn-secondary btn-sm">
                <i class="bi bi-list"></i> Lista
            </a>
            <a href="<?= APP_URL ?>/crm/leads/novo" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Novo Lead
            </a>
        </div>
    </div>
</div>

<!-- Kanban Board -->
<div class="kanban-board" id="kanbanBoard">
    <?php foreach ($estagios as $slug => $estagio): ?>
    <?php $colLeads = $leads[$slug] ?? []; ?>
    <div class="kanban-column" data-estagio="<?= $slug ?>">

        <!-- Cabeçalho da coluna -->
        <div class="kanban-column-header" style="--col-color: <?= $estagio['cor'] ?>">
            <span class="kanban-column-title" style="color: <?= $estagio['cor'] ?>">
                <?= $estagio['label'] ?>
            </span>
            <span class="kanban-count" id="count-<?= $slug ?>"><?= count($colLeads) ?></span>
        </div>

        <!-- Cards da coluna -->
        <div class="kanban-cards" data-estagio="<?= $slug ?>" id="col-<?= $slug ?>">
            <?php foreach ($colLeads as $lead): ?>
            <div class="kanban-card"
                 draggable="true"
                 data-id="<?= $lead['id'] ?>"
                 data-temperatura="<?= $lead['temperatura'] ?>"
                 data-vendedor="<?= htmlspecialchars($lead['vendedor_nome'] ?? '') ?>"
                 onclick="abrirLead(<?= $lead['id'] ?>)"
                 title="<?= htmlspecialchars($lead['nome']) ?>">

                <!-- Prioridade + Temperatura -->
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                    <?php $prio = $prioridadeIcons[$lead['prioridade']] ?? $prioridadeIcons['media']; ?>
                    <i class="bi <?= $prio['icon'] ?>" style="color:<?= $prio['cor'] ?>; font-size:12px;" title="Prioridade: <?= $lead['prioridade'] ?>"></i>
                    <span class="kanban-temp <?= $lead['temperatura'] ?>" title="<?= ucfirst($lead['temperatura']) ?>"></span>
                </div>

                <!-- Nome -->
                <div class="kanban-card-name"><?= htmlspecialchars($lead['nome']) ?></div>

                <!-- Empresa -->
                <?php if (!empty($lead['empresa'])): ?>
                <div class="kanban-card-meta">
                    <i class="bi bi-building"></i>
                    <?= htmlspecialchars($lead['empresa']) ?>
                </div>
                <?php endif; ?>

                <!-- Produto de interesse -->
                <?php if (!empty($lead['produto_interesse'])): ?>
                <div class="kanban-card-meta" style="margin-top:3px">
                    <i class="bi bi-tag"></i>
                    <?= htmlspecialchars(mb_strtrim($lead['produto_interesse'], 30)) ?>
                </div>
                <?php endif; ?>

                <!-- Valor estimado -->
                <?php if (!empty($lead['valor_estimado'])): ?>
                <div class="kanban-card-value">
                    R$ <?= number_format($lead['valor_estimado'], 2, ',', '.') ?>
                </div>
                <?php endif; ?>

                <!-- Vendedor + data follow-up -->
                <div class="kanban-card-meta" style="margin-top:8px; justify-content:space-between;">
                    <span>
                        <?php if (!empty($lead['vendedor_nome'])): ?>
                        <i class="bi bi-person"></i> <?= htmlspecialchars(explode(' ', $lead['vendedor_nome'])[0]) ?>
                        <?php endif; ?>
                    </span>
                    <?php if (!empty($lead['data_follow_up'])): ?>
                    <span style="color:<?= strtotime($lead['data_follow_up']) < time() ? 'var(--kroma-danger)' : 'var(--kroma-warning)' ?>">
                        <i class="bi bi-calendar"></i>
                        <?= date('d/m', strtotime($lead['data_follow_up'])) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Botão adicionar -->
        <div style="padding: 8px 10px; border-top: 1px solid var(--border-color);">
            <a href="<?= APP_URL ?>/crm/leads/novo?estagio=<?= $slug ?>"
               class="btn btn-sm w-100"
               style="background:rgba(108,99,255,0.08); color:var(--text-muted); font-size:12px; border: 1px dashed var(--border-color);">
                <i class="bi bi-plus"></i> Adicionar
            </a>
        </div>

    </div>
    <?php endforeach; ?>
</div>

<!-- Modal detalhes do lead -->
<div class="modal fade" id="modalLead" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLeadTitle">Detalhes do Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalLeadBody">
                <div class="text-center py-4">
                    <span class="spinner"></span>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="btnEditarLead" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Inicializa Kanban drag-and-drop
document.addEventListener('DOMContentLoaded', function() {
    KROMA.kanban.init();

    // Atualiza contadores por coluna
    document.querySelectorAll('.kanban-column').forEach(col => {
        const slug  = col.dataset.estagio;
        const count = col.querySelectorAll('.kanban-card').length;
        const badge = document.getElementById('count-' + slug);
        if (badge) badge.textContent = count;
    });
});

// Abre modal com detalhes do lead
function abrirLead(id) {
    const modal = new bootstrap.Modal(document.getElementById('modalLead'));
    document.getElementById('modalLeadBody').innerHTML = '<div class="text-center py-4"><span class="spinner"></span></div>';
    document.getElementById('btnEditarLead').href = KROMA.baseUrl + '/crm/leads/' + id + '/editar';
    modal.show();

    KROMA.ajax.get('/crm/leads/' + id + '/json')
        .then(data => {
            if (data.lead) {
                const lead = data.lead;
                document.getElementById('modalLeadTitle').textContent = lead.nome;
                document.getElementById('modalLeadBody').innerHTML = `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="form-label">Empresa</small>
                            <div style="color:var(--text-primary)">${lead.empresa || '—'}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="form-label">WhatsApp</small>
                            <div style="color:var(--text-primary)">${lead.whatsapp || lead.telefone || '—'}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="form-label">Produto de Interesse</small>
                            <div style="color:var(--text-primary)">${lead.produto_interesse || '—'}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="form-label">Valor Estimado</small>
                            <div style="color:var(--kroma-accent);font-weight:700">${lead.valor_estimado ? 'R$ ' + parseFloat(lead.valor_estimado).toLocaleString('pt-BR', {minimumFractionDigits:2}) : '—'}</div>
                        </div>
                        <div class="col-12">
                            <small class="form-label">Observações</small>
                            <div style="color:var(--text-primary)">${lead.observacoes || '—'}</div>
                        </div>
                    </div>
                `;
            }
        })
        .catch(() => {
            document.getElementById('modalLeadBody').innerHTML = `
                <div class="text-center py-4" style="color:var(--text-muted)">
                    <p>Não foi possível carregar os detalhes do lead.</p>
                    <a href="${KROMA.baseUrl}/crm/leads/${id}/editar" class="btn btn-primary btn-sm">Abrir Lead</a>
                </div>
            `;
        });
}

// Busca no kanban
document.getElementById('buscaKanban')?.addEventListener('input', function() {
    const termo = this.value.toLowerCase();
    document.querySelectorAll('.kanban-card').forEach(card => {
        const nome = card.querySelector('.kanban-card-name')?.textContent.toLowerCase() || '';
        card.style.display = nome.includes(termo) ? '' : 'none';
    });
});
</script>

<?php
// Helper para truncar string
function mb_strtrim(string $str, int $len): string {
    return mb_strlen($str) > $len ? mb_substr($str, 0, $len) . '...' : $str;
}
?>
