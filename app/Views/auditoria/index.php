<?php
$acaoClasses = [
    'criar' => 'badge-success',
    'editar' => 'badge-primary',
    'excluir' => 'badge-danger',
    'baixar' => 'badge-success',
    'cancelar' => 'badge-danger',
    'receber' => 'badge-info',
    'aprovar_integrado' => 'badge-success',
];

function auditoriaAcaoLabel(string $acao): string
{
    $labels = [
        'criar' => 'Criar',
        'editar' => 'Editar',
        'excluir' => 'Excluir',
        'visualizar' => 'Visualizar',
        'baixar' => 'Baixar',
        'cancelar' => 'Cancelar',
        'receber' => 'Receber',
        'aprovar_integrado' => 'Aprovar integrado',
        'editar_perfil' => 'Editar perfil',
    ];
    if (isset($labels[$acao])) {
        return $labels[$acao];
    }
    if (str_starts_with($acao, 'status_')) {
        return 'Status: ' . ucfirst(str_replace('_', ' ', substr($acao, 7)));
    }
    return ucfirst(str_replace('_', ' ', $acao));
}

function auditoriaTabelaLabel(?string $tabela): string
{
    $labels = [
        'clientes' => 'Clientes',
        'leads' => 'Leads',
        'orcamentos' => 'Orçamentos',
        'produtos' => 'Produtos',
        'ordem_servicos' => 'OS / Produção',
        'ordem_servico_etapas' => 'Etapas da OS',
        'materiais' => 'Estoque',
        'estoque_movimentacoes' => 'Movimentações de Estoque',
        'contas_receber' => 'Contas a Receber',
        'contas_pagar' => 'Contas a Pagar',
        'compras' => 'Compras',
        'fornecedores' => 'Fornecedores',
        'usuarios' => 'Usuários',
        'permissoes' => 'Permissões',
        'colaboradores' => 'Colaboradores',
        'equipamentos' => 'Equipamentos',
        'veiculos' => 'Veículos',
    ];
    return $labels[$tabela ?? ''] ?? ($tabela ?: '-');
}

function auditoriaJsonResumo(?string $json): string
{
    if (!$json) {
        return '-';
    }
    $dados = json_decode($json, true);
    if (!is_array($dados)) {
        return strlen($json) > 180 ? substr($json, 0, 180) . '...' : $json;
    }
    $partes = [];
    foreach (array_slice($dados, 0, 6, true) as $campo => $valor) {
        if (is_array($valor)) {
            $valor = '[' . count($valor) . ' itens]';
        } elseif (is_bool($valor)) {
            $valor = $valor ? 'sim' : 'não';
        } elseif ($valor === null || $valor === '') {
            $valor = '-';
        }
        $partes[] = $campo . ': ' . (string)$valor;
    }
    return implode(' | ', $partes);
}

$temFiltros = array_filter($filtros, fn($valor) => $valor !== '');
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-clipboard-data"></i></div>
            <div class="kpi-value"><?= number_format($resumo['total']) ?></div>
            <div class="kpi-label">Ações registradas</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-calendar-check"></i></div>
            <div class="kpi-value"><?= number_format($resumo['hoje']) ?></div>
            <div class="kpi-label">Ações hoje</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-people"></i></div>
            <div class="kpi-value"><?= number_format($resumo['usuarios']) ?></div>
            <div class="kpi-label">Usuários auditados</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-diagram-3"></i></div>
            <div class="kpi-value"><?= number_format($resumo['areas']) ?></div>
            <div class="kpi-label">Áreas com log</div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-funnel me-2 text-primary-kroma"></i>Filtros da Auditoria</h6>
        <span class="badge <?= $temFiltros ? 'badge-warning' : 'badge-secondary' ?>"><?= $temFiltros ? 'Filtros ativos' : 'Sem filtros' ?></span>
    </div>
    <form method="GET" action="<?= APP_URL ?>/auditoria" class="p-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Usuário</label>
                <select class="form-select" name="usuario_id">
                    <option value="">Todos</option>
                    <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= $usuario['id'] ?>" <?= (string)$filtros['usuario_id'] === (string)$usuario['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($usuario['nome']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Área / tabela</label>
                <select class="form-select" name="tabela">
                    <option value="">Todas</option>
                    <?php foreach ($tabelas as $tabela): ?>
                    <option value="<?= htmlspecialchars($tabela['tabela']) ?>" <?= $filtros['tabela'] === $tabela['tabela'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars(auditoriaTabelaLabel($tabela['tabela'])) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Ação</label>
                <select class="form-select" name="acao">
                    <option value="">Todas</option>
                    <?php foreach ($acoes as $acao): ?>
                    <option value="<?= htmlspecialchars($acao['acao']) ?>" <?= $filtros['acao'] === $acao['acao'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars(auditoriaAcaoLabel($acao['acao'])) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">De</label>
                <input class="form-control" type="date" name="data_inicio" value="<?= htmlspecialchars($filtros['data_inicio']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Até</label>
                <input class="form-control" type="date" name="data_fim" value="<?= htmlspecialchars($filtros['data_fim']) ?>">
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Filtrar</button>
                <a class="btn btn-secondary" href="<?= APP_URL ?>/auditoria"><i class="bi bi-x-circle"></i> Limpar</a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-list-check me-2 text-info"></i>Eventos auditados</h6>
        <span class="badge badge-info"><?= count($logs) ?> exibidos</span>
    </div>
    <div class="table-wrapper">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Usuário</th>
                    <th>Área</th>
                    <th>Ação</th>
                    <th>Registro</th>
                    <th>IP</th>
                    <th>Dados</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <?php
                $acao = $log['acao'] ?: '-';
                $acaoClass = $acaoClasses[$acao] ?? (str_starts_with($acao, 'status_') ? 'badge-warning' : 'badge-secondary');
                ?>
                <tr>
                    <td>
                        <strong><?= date('d/m/Y', strtotime($log['created_at'])) ?></strong>
                        <div class="small text-muted"><?= date('H:i:s', strtotime($log['created_at'])) ?></div>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($log['usuario_nome'] ?? 'Sistema') ?></strong>
                        <div class="small text-muted"><?= htmlspecialchars($log['usuario_email'] ?? '-') ?></div>
                    </td>
                    <td><span class="badge badge-primary"><?= htmlspecialchars(auditoriaTabelaLabel($log['tabela'])) ?></span></td>
                    <td><span class="badge <?= $acaoClass ?>"><?= htmlspecialchars(auditoriaAcaoLabel($acao)) ?></span></td>
                    <td><span class="badge badge-secondary">#<?= (int)$log['registro_id'] ?></span></td>
                    <td><span class="badge badge-info"><?= htmlspecialchars($log['ip'] ?: '-') ?></span></td>
                    <td style="min-width:260px">
                        <?php if (!empty($log['dados_antigos']) || !empty($log['dados_novos'])): ?>
                        <details>
                            <summary><span class="badge badge-warning">Ver dados</span></summary>
                            <div class="small mt-2">
                                <div><strong>Antes:</strong> <?= htmlspecialchars(auditoriaJsonResumo($log['dados_antigos'])) ?></div>
                                <div><strong>Depois:</strong> <?= htmlspecialchars(auditoriaJsonResumo($log['dados_novos'])) ?></div>
                            </div>
                        </details>
                        <?php else: ?>
                            <span class="badge badge-secondary">Sem payload</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                <tr><td colspan="7"><span class="badge badge-secondary">Sem eventos para os filtros selecionados</span></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
