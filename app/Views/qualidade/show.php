<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$statusClasses = [
    'rascunho' => 'badge-secondary',
    'em_revisao' => 'badge-warning',
    'aprovado' => 'badge-success',
    'obsoleto' => 'badge-danger',
];
$revisaoVencida = !empty($pop['revisao_prevista']) && $pop['revisao_prevista'] < date('Y-m-d') && $pop['status'] !== 'obsoleto';
?>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-clipboard-check me-2 text-primary-kroma"></i><?= htmlspecialchars($pop['titulo']) ?></h6>
                <span class="badge <?= $statusClasses[$pop['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($statusLabels[$pop['status']] ?? $pop['status']) ?></span>
            </div>
            <div class="p-3">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge badge-primary"><?= htmlspecialchars($pop['codigo']) ?></span>
                    <span class="badge badge-info">Versão <?= (int)$pop['versao'] ?></span>
                    <span class="badge badge-secondary"><?= htmlspecialchars($pop['setor'] ?: 'Sem setor') ?></span>
                    <span class="badge <?= $revisaoVencida ? 'badge-danger' : 'badge-warning' ?>">Revisão <?= $pop['revisao_prevista'] ? date('d/m/Y', strtotime($pop['revisao_prevista'])) : 'sem data' ?></span>
                </div>

                <h6 class="mb-2">Objetivo</h6>
                <div class="border-kroma rounded-kroma p-3 mb-3"><?= nl2br(htmlspecialchars($pop['objetivo'] ?: 'Sem objetivo informado.')) ?></div>

                <h6 class="mb-2">Procedimento</h6>
                <div class="border-kroma rounded-kroma p-3 mb-3"><?= nl2br(htmlspecialchars($pop['procedimento'] ?: 'Sem procedimento informado.')) ?></div>

                <h6 class="mb-2">Checklist</h6>
                <div class="border-kroma rounded-kroma p-3 mb-3"><?= nl2br(htmlspecialchars($pop['checklist'] ?: 'Sem checklist informado.')) ?></div>

                <?php if (!empty($pop['observacoes'])): ?>
                <h6 class="mb-2">Observações</h6>
                <div class="border-kroma rounded-kroma p-3"><?= nl2br(htmlspecialchars($pop['observacoes'])) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-clock-history me-2 text-info"></i>Histórico de Revisões</h6>
                <span class="badge badge-info"><?= count($revisoes) ?> registros</span>
            </div>
            <div class="table-wrapper">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Versão</th>
                            <th>Status</th>
                            <th>Resumo</th>
                            <th>Usuário</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($revisoes as $revisao): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($revisao['created_at'])) ?></td>
                            <td><span class="badge badge-primary">v<?= (int)$revisao['versao'] ?></span></td>
                            <td><span class="badge <?= $statusClasses[$revisao['status']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($statusLabels[$revisao['status']] ?? $revisao['status']) ?></span></td>
                            <td><?= htmlspecialchars($revisao['resumo'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($revisao['usuario_nome'] ?? 'Sistema') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($revisoes)): ?>
                        <tr><td colspan="5"><span class="badge badge-secondary">Sem revisões registradas</span></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-info-circle me-2 text-primary-kroma"></i>Dados de Controle</h6>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <div class="d-flex justify-content-between border-kroma rounded-kroma p-2">
                    <span>Categoria</span>
                    <span class="badge badge-secondary"><?= htmlspecialchars($pop['categoria'] ?: '-') ?></span>
                </div>
                <div class="d-flex justify-content-between border-kroma rounded-kroma p-2">
                    <span>Processo</span>
                    <span class="badge badge-info"><?= htmlspecialchars($pop['processo_nome'] ?? '-') ?></span>
                </div>
                <div class="d-flex justify-content-between border-kroma rounded-kroma p-2">
                    <span>Responsável</span>
                    <span class="badge badge-primary"><?= htmlspecialchars($pop['responsavel_nome'] ?? '-') ?></span>
                </div>
                <div class="d-flex justify-content-between border-kroma rounded-kroma p-2">
                    <span>Aprovador</span>
                    <span class="badge <?= !empty($pop['aprovador_nome']) ? 'badge-success' : 'badge-secondary' ?>"><?= htmlspecialchars($pop['aprovador_nome'] ?? '-') ?></span>
                </div>
                <div class="d-flex justify-content-between border-kroma rounded-kroma p-2">
                    <span>Vigência</span>
                    <span class="badge badge-info"><?= $pop['vigencia_inicio'] ? date('d/m/Y', strtotime($pop['vigencia_inicio'])) : '-' ?></span>
                </div>
                <?php if (!empty($pop['anexo_url'])): ?>
                <a class="badge badge-primary text-decoration-none align-self-start" href="<?= htmlspecialchars($pop['anexo_url']) ?>" target="_blank" rel="noopener">Abrir anexo</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (Auth::pode('pops.editar')): ?>
        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-shield-check me-2 text-warning"></i>Ações do POP</h6>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <form action="<?= APP_URL ?>/qualidade/pops/<?= $pop['id'] ?>/status" method="POST" data-loading>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <div class="d-flex gap-2">
                        <select class="form-select" name="status">
                            <?php foreach ($statusLabels as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $pop['status'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i></button>
                    </div>
                </form>
                <form action="<?= APP_URL ?>/qualidade/pops/<?= $pop['id'] ?>/revisar" method="POST" data-confirm="Abrir nova revisão deste POP?" data-loading>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <button class="btn btn-secondary w-100" type="submit"><i class="bi bi-arrow-repeat"></i> Abrir nova revisão</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
