<?php
use App\Services\Auth;
$csrfToken = Auth::csrfToken();
?>

<div class="table-wrapper">
    <table class="table datatable">
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Perfil</th>
                <th>Setor</th>
                <th>Contato</th>
                <th>Último Acesso</th>
                <th>Status</th>
                <th width="150">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm"><?= strtoupper(substr($u['nome'], 0, 1)) ?></span>
                        <div>
                            <div style="font-weight:700"><?= htmlspecialchars($u['nome']) ?></div>
                            <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($u['email']) ?></div>
                        </div>
                    </div>
                </td>
                <td><span class="badge badge-primary"><?= htmlspecialchars($u['perfil_label'] ?? '-') ?></span></td>
                <td><?= htmlspecialchars($u['setor'] ?? '-') ?></td>
                <td><?= htmlspecialchars($u['whatsapp'] ?: ($u['telefone'] ?? '-')) ?></td>
                <td><?= !empty($u['ultimo_acesso']) ? date('d/m/Y H:i', strtotime($u['ultimo_acesso'])) : '-' ?></td>
                <td>
                    <span class="badge <?= $u['ativo'] ? 'badge-success' : 'badge-secondary' ?>">
                        <?= $u['ativo'] ? 'Ativo' : 'Inativo' ?>
                    </span>
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/usuarios/<?= $u['id'] ?>" title="Ver"><i class="bi bi-eye"></i></a>
                        <?php if (Auth::temPerfil(['administrador', 'diretor'])): ?>
                        <a class="btn btn-icon btn-secondary btn-sm" href="<?= APP_URL ?>/usuarios/<?= $u['id'] ?>/editar" title="Editar"><i class="bi bi-pencil"></i></a>
                        <form action="<?= APP_URL ?>/usuarios/<?= $u['id'] ?>/toggle-status" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <button class="btn btn-icon btn-secondary btn-sm" title="Alternar status"><i class="bi bi-power"></i></button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
