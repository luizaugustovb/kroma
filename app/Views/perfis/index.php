<div class="table-wrapper">
    <table class="table">
        <thead>
            <tr>
                <th>Perfil</th>
                <th>Nível</th>
                <th>Permissões</th>
                <th>Status</th>
                <th width="170">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($perfis as $perfil): ?>
            <tr>
                <td>
                    <div style="font-weight:700"><?= htmlspecialchars($perfil['label']) ?></div>
                    <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($perfil['descricao'] ?? '') ?></div>
                </td>
                <td><span class="badge badge-info">Nível <?= (int)$perfil['nivel'] ?></span></td>
                <td>
                    <div class="d-flex flex-wrap gap-1">
                        <span class="badge badge-primary"><?= (int)$perfil['total_ver'] ?> ver</span>
                        <span class="badge badge-success"><?= (int)$perfil['total_criar'] ?> criar</span>
                        <span class="badge badge-warning"><?= (int)$perfil['total_editar'] ?> editar</span>
                        <span class="badge badge-danger"><?= (int)$perfil['total_excluir'] ?> excluir</span>
                    </div>
                </td>
                <td>
                    <span class="badge <?= $perfil['ativo'] ? 'badge-success' : 'badge-secondary' ?>">
                        <?= $perfil['ativo'] ? 'Ativo' : 'Inativo' ?>
                    </span>
                </td>
                <td>
                    <a class="btn btn-secondary btn-sm" href="<?= APP_URL ?>/perfis/<?= $perfil['id'] ?>/permissoes">
                        <i class="bi bi-shield-check"></i> Permissões
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($perfis)): ?>
            <tr><td colspan="5"><span class="badge badge-secondary">Sem perfis cadastrados</span></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
