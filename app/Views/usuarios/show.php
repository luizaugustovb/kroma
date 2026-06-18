<div class="row g-3">
    <div class="col-lg-4">
        <div class="card text-center">
            <div class="avatar avatar-xl mx-auto mb-3"><?= strtoupper(substr($usuario['nome'], 0, 1)) ?></div>
            <h2 class="h5 mb-1"><?= htmlspecialchars($usuario['nome']) ?></h2>
            <p class="text-secondary mb-2"><?= htmlspecialchars($usuario['email']) ?></p>
            <span class="badge <?= $usuario['ativo'] ? 'badge-success' : 'badge-secondary' ?>">
                <?= $usuario['ativo'] ? 'Ativo' : 'Inativo' ?>
            </span>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-info-circle me-2 text-primary-kroma"></i>Informações</h6>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <tbody>
                        <tr><th>Telefone</th><td><?= htmlspecialchars($usuario['telefone'] ?? '-') ?></td></tr>
                        <tr><th>WhatsApp</th><td><?= htmlspecialchars($usuario['whatsapp'] ?? '-') ?></td></tr>
                        <tr><th>Cargo</th><td><?= htmlspecialchars($usuario['cargo'] ?? '-') ?></td></tr>
                        <tr><th>Setor</th><td><?= htmlspecialchars($usuario['setor'] ?? '-') ?></td></tr>
                        <tr>
                            <th>Cliente vinculado</th>
                            <td>
                                <?php if (!empty($usuario['cliente_nome'])): ?>
                                    <span class="badge badge-info"><?= htmlspecialchars($usuario['cliente_nome']) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Sem vínculo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr><th>Último acesso</th><td><?= !empty($usuario['ultimo_acesso']) ? date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) : '-' ?></td></tr>
                        <tr><th>Observações</th><td><?= nl2br(htmlspecialchars($usuario['observacoes'] ?? '-')) ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
