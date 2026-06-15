<?php
/**
 * View: Lista de Clientes — KROMA PRINT ERP
 */

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
            <div class="kpi-value"><?= count(array_filter($clientes, fn($c) => in_array($c['classificacao'], ['ouro','diamante']))) ?></div>
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
                    <a href="https://wa.me/55<?= preg_replace('/\D/', '', $c['whatsapp']) ?>" target="_blank"
                       class="btn btn-sm" style="color:#25D366; padding:2px 6px; font-size:12px">
                        <i class="bi bi-whatsapp"></i> <?= htmlspecialchars($c['whatsapp']) ?>
                    </a>
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
                        <a href="https://wa.me/55<?= preg_replace('/\D/', '', $c['whatsapp'] ?? '') ?>"
                           target="_blank"
                           class="btn btn-icon btn-sm"
                           style="background:rgba(37,211,102,0.1); color:#25D366; border:1px solid rgba(37,211,102,0.2)"
                           data-bs-toggle="tooltip" title="WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
