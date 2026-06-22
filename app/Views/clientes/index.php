<?php

/**
 * View: Lista de Clientes — KROMA PRINT ERP
 */

use App\Services\Auth;

$tipoLabels = [
    'cliente_final' => ['label' => 'Cliente Final', 'class' => 'badge-secondary'],
    'revenda'       => ['label' => 'Revenda',        'class' => 'badge-primary'],
    'parceiro'      => ['label' => 'Parceiro',       'class' => 'badge-info'],
    'corporativo'   => ['label' => 'Corporativo',    'class' => 'badge-warning'],
    'orgao_publico' => ['label' => 'Órgão Público',  'class' => 'badge-success'],
];

$classifLabels = [
    'bronze'   => ['label' => '🥉 Bronze',   'cor' => '#CD7F32'],
    'prata'    => ['label' => '🥈 Prata',    'cor' => '#C0C0C0'],
    'ouro'     => ['label' => '🥇 Ouro',     'cor' => '#FFD700'],
    'diamante' => ['label' => '💎 Diamante', 'cor' => '#00B0FF'],
];
?>

<!-- Stats rápidos -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-people"></i></div>
            <div class="kpi-value"><?= count($clientes) ?></div>
            <div class="kpi-label">Total de Clientes</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-building"></i></div>
            <div class="kpi-value"><?= count(array_filter($clientes, fn($c) => $c['tipo_cliente'] === 'corporativo')) ?></div>
            <div class="kpi-label">Corporativos</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-shop"></i></div>
            <div class="kpi-value"><?= count(array_filter($clientes, fn($c) => $c['tipo_cliente'] === 'revenda')) ?></div>
            <div class="kpi-label">Revendas</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-star"></i></div>
            <div class="kpi-value"><?= count(array_filter($clientes, fn($c) => in_array($c['classificacao'], ['ouro', 'diamante']))) ?></div>
            <div class="kpi-label">Ouro + Diamante</div>
        </div>
    </div>
</div>

<!-- Tabela de clientes -->
<div class="table-wrapper">
    <table class="table datatable" id="tabelaClientes">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>CPF/CNPJ</th>
                <th>Tipo</th>
                <th>Classificação</th>
                <th>Cidade/UF</th>
                <th>Vendedor</th>
                <th>WhatsApp</th>
                <th>Status</th>
                <th width="100">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clientes as $c): ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="avatar avatar-sm" style="background: var(--gradient-primary)">
                                <?= strtoupper(substr($c['nome'], 0, 1)) ?>
                            </div>
                            <div>
                                <div style="font-weight:600; color:var(--text-primary); font-size:13px">
                                    <?= htmlspecialchars($c['nome']) ?>
                                </div>
                                <?php if (!empty($c['nome_fantasia'])): ?>
                                    <div style="font-size:11px; color:var(--text-muted)">
                                        <?= htmlspecialchars($c['nome_fantasia']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:12px; color:var(--text-secondary)"><?= htmlspecialchars($c['cpf_cnpj'] ?? '—') ?></td>
                    <td>
                        <?php $tipo = $tipoLabels[$c['tipo_cliente']] ?? ['label' => $c['tipo_cliente'], 'class' => 'badge-secondary']; ?>
                        <span class="badge <?= $tipo['class'] ?>"><?= $tipo['label'] ?></span>
                    </td>
                    <td>
                        <?php $cl = $classifLabels[$c['classificacao']] ?? null; ?>
                        <?php if ($cl): ?>
                            <span style="color:<?= $cl['cor'] ?>; font-size:12px; font-weight:600"><?= $cl['label'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px; color:var(--text-secondary)">
                        <?= htmlspecialchars($c['cidade'] ?? '') ?>
                        <?= !empty($c['estado']) ? '/ ' . $c['estado'] : '' ?>
                    </td>
                    <td style="font-size:12px; color:var(--text-secondary)"><?= htmlspecialchars($c['vendedor_nome'] ?? '—') ?></td>
                    <td>
                        <?php if (!empty($c['whatsapp'])): ?>
                            <button type="button" class="btn btn-sm" style="color:#25D366; padding:2px 6px; font-size:12px"
                                onclick="abrirEnvioWpp(<?= $c['id'] ?>, '<?= htmlspecialchars($c['nome']) ?>', '<?= htmlspecialchars($c['whatsapp']) ?>')">
                                <i class="bi bi-whatsapp"></i> <?= htmlspecialchars($c['whatsapp']) ?>
                            </button>
                        <?php else: ?>
                            <span style="color:var(--text-muted); font-size:12px">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $statusCores = ['ativo' => 'badge-success', 'inativo' => 'badge-secondary', 'bloqueado' => 'badge-danger'];
                        ?>
                        <span class="badge <?= $statusCores[$c['status']] ?? 'badge-secondary' ?>">
                            <?= ucfirst($c['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?= APP_URL ?>/clientes/<?= $c['id'] ?>"
                                class="btn btn-icon btn-secondary btn-sm"
                                data-bs-toggle="tooltip" title="Ver ficha">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="<?= APP_URL ?>/clientes/<?= $c['id'] ?>/editar"
                                class="btn btn-icon btn-secondary btn-sm"
                                data-bs-toggle="tooltip" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button"
                                class="btn btn-icon btn-sm"
                                style="background:rgba(37,211,102,0.1); color:#25D366; border:1px solid rgba(37,211,102,0.2)"
                                data-bs-toggle="tooltip" title="Enviar mensagem WhatsApp"
                                onclick="abrirEnvioWpp(<?= $c['id'] ?>, '<?= htmlspecialchars($c['nome']) ?>', '<?= htmlspecialchars($c['whatsapp'] ?? '') ?>')">
                                <i class="bi bi-whatsapp"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal envio WhatsApp via sistema -->
<div class="modal fade" id="modalWpp" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-whatsapp me-2" style="color:#25D366"></i>Enviar Mensagem</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formWpp" action="<?= APP_URL ?>/whatsapp/enviar" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                <input type="hidden" name="cliente_id" id="wppClienteId">
                <input type="hidden" name="tipo" value="manual">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Para</label>
                        <input class="form-control" id="wppClienteNome" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telefone</label>
                        <input class="form-control" id="wppTelefone" name="telefone" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mensagem *</label>
                        <textarea class="form-control" name="mensagem" rows="5" required placeholder="Digite a mensagem..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-send"></i> Enviar via Sistema</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function abrirEnvioWpp(id, nome, telefone) {
        document.getElementById('wppClienteId').value = id;
        document.getElementById('wppClienteNome').value = nome;
        document.getElementById('wppTelefone').value = telefone;
        var modal = new bootstrap.Modal(document.getElementById('modalWpp'));
        modal.show();
    }
</script>