<?php
use App\Services\Auth;
$csrfToken = Auth::csrfToken();
?>

<form action="<?= APP_URL ?>/perfis/<?= $perfil['id'] ?>/permissoes" method="POST" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="card mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <span class="badge badge-primary">Perfil</span>
                <strong class="ms-2"><?= htmlspecialchars($perfil['label']) ?></strong>
            </div>
            <span class="badge badge-info">Nível <?= (int)$perfil['nivel'] ?></span>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Módulo</th>
                    <th>Grupo</th>
                    <th>Ver</th>
                    <th>Criar</th>
                    <th>Editar</th>
                    <th>Excluir</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modulos as $modulo): ?>
                <?php $perm = $permissoes[$modulo['slug']] ?? []; ?>
                <tr>
                    <td>
                        <i class="bi <?= htmlspecialchars($modulo['icone'] ?? 'bi-box') ?> me-2 text-primary-kroma"></i>
                        <strong><?= htmlspecialchars($modulo['nome']) ?></strong>
                        <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($modulo['slug']) ?></div>
                    </td>
                    <td><span class="badge badge-secondary"><?= htmlspecialchars($modulo['grupo']) ?></span></td>
                    <?php foreach (['ver' => 'pode_ver', 'criar' => 'pode_criar', 'editar' => 'pode_editar', 'excluir' => 'pode_excluir'] as $campo => $coluna): ?>
                    <td>
                        <input class="form-check-input" type="checkbox"
                               name="permissoes[<?= htmlspecialchars($modulo['slug']) ?>][<?= $campo ?>]"
                               <?= !empty($perm[$coluna]) ? 'checked' : '' ?>>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Salvar Permissões</button>
        <a href="<?= APP_URL ?>/perfis" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
    </div>
</form>
