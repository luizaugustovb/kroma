<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$acoes = [
    'ver' => ['coluna' => 'pode_ver', 'label' => 'Ver', 'class' => 'badge-primary'],
    'criar' => ['coluna' => 'pode_criar', 'label' => 'Criar', 'class' => 'badge-success'],
    'editar' => ['coluna' => 'pode_editar', 'label' => 'Editar', 'class' => 'badge-warning'],
    'excluir' => ['coluna' => 'pode_excluir', 'label' => 'Excluir', 'class' => 'badge-danger'],
];
?>

<form action="<?= APP_URL ?>/perfis/<?= $perfil['id'] ?>/permissoes" method="POST" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon primary"><i class="bi bi-eye"></i></div>
                <div class="kpi-value"><?= number_format($resumo['ver']) ?></div>
                <div class="kpi-label">Permissões para ver</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon success"><i class="bi bi-plus-circle"></i></div>
                <div class="kpi-value"><?= number_format($resumo['criar']) ?></div>
                <div class="kpi-label">Permissões para criar</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon warning"><i class="bi bi-pencil-square"></i></div>
                <div class="kpi-value"><?= number_format($resumo['editar']) ?></div>
                <div class="kpi-label">Permissões para editar</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon danger"><i class="bi bi-trash"></i></div>
                <div class="kpi-value"><?= number_format($resumo['excluir']) ?></div>
                <div class="kpi-label">Permissões para excluir</div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <span class="badge badge-primary">Perfil</span>
                <strong class="ms-2"><?= htmlspecialchars($perfil['label']) ?></strong>
            </div>
            <div class="d-flex flex-wrap gap-1">
                <span class="badge badge-info">Nível <?= (int)$perfil['nivel'] ?></span>
                <span class="badge <?= $perfil['ativo'] ? 'badge-success' : 'badge-secondary' ?>"><?= $perfil['ativo'] ? 'Ativo' : 'Inativo' ?></span>
            </div>
        </div>
    </div>

    <?php foreach ($grupos as $grupo => $modulosGrupo): ?>
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="card-title"><i class="bi bi-diagram-3 me-2 text-primary-kroma"></i><?= htmlspecialchars($grupo) ?></h6>
            <span class="badge badge-secondary"><?= count($modulosGrupo) ?> módulos</span>
        </div>
        <div class="table-wrapper">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Módulo</th>
                        <?php foreach ($acoes as $acao): ?>
                        <th><span class="badge <?= $acao['class'] ?>"><?= $acao['label'] ?></span></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modulosGrupo as $modulo): ?>
                    <?php $perm = $permissoes[$modulo['slug']] ?? []; ?>
                    <tr>
                        <td>
                            <i class="bi <?= htmlspecialchars($modulo['icone'] ?? 'bi-box') ?> me-2 text-primary-kroma"></i>
                            <strong><?= htmlspecialchars($modulo['nome']) ?></strong>
                            <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($modulo['slug']) ?></div>
                        </td>
                        <?php foreach ($acoes as $campo => $acao): ?>
                        <td>
                            <input class="form-check-input permission-toggle"
                                   type="checkbox"
                                   data-slug="<?= htmlspecialchars($modulo['slug']) ?>"
                                   data-acao="<?= $campo ?>"
                                   name="permissoes[<?= htmlspecialchars($modulo['slug']) ?>][<?= $campo ?>]"
                                   <?= !empty($perm[$acao['coluna']]) ? 'checked' : '' ?>>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($grupos)): ?>
    <div class="card mb-3">
        <span class="badge badge-secondary align-self-start">Sem módulos cadastrados</span>
    </div>
    <?php endif; ?>

    <div class="d-flex gap-2 mt-3">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Salvar Permissões</button>
        <a href="<?= APP_URL ?>/perfis" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.permission-toggle[data-acao]').forEach(function(input) {
        input.addEventListener('change', function() {
            const slug = input.dataset.slug;
            const acao = input.dataset.acao;
            const ver = document.querySelector('.permission-toggle[data-slug="' + slug + '"][data-acao="ver"]');
            if (!ver) return;
            if (input.checked && ['criar', 'editar', 'excluir'].includes(acao)) {
                ver.checked = true;
            }
            if (!input.checked && acao === 'ver') {
                document.querySelectorAll('.permission-toggle[data-slug="' + slug + '"]').forEach(function(other) {
                    other.checked = false;
                });
            }
        });
    });
});
</script>
