<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$tipoClasses = [
    'geral' => 'badge-primary',
    'setor' => 'badge-info',
    'cliente' => 'badge-success',
    'ordem_servico' => 'badge-warning',
    'privado' => 'badge-secondary',
];

function chatData(?string $data): string
{
    return $data ? date('d/m/Y H:i', strtotime($data)) : '-';
}
?>

<div class="row g-3">
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-chat-dots me-2 text-primary-kroma"></i>Canais</h6>
                <span class="badge badge-secondary"><?= count($canais) ?> ativos</span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach ($canais as $canal): ?>
                <?php $ativo = !empty($canalAtual['id']) && (int)$canalAtual['id'] === (int)$canal['id']; ?>
                <a href="<?= APP_URL ?>/chat/canais/<?= $canal['id'] ?>" class="border-kroma rounded-kroma p-2 text-decoration-none" style="<?= $ativo ? 'background:rgba(108,99,255,.12)' : '' ?>">
                    <div class="d-flex justify-content-between gap-2 align-items-start">
                        <strong><?= htmlspecialchars($canal['nome']) ?></strong>
                        <span class="badge <?= $tipoClasses[$canal['tipo']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($tipoLabels[$canal['tipo']] ?? $canal['tipo']) ?></span>
                    </div>
                    <div class="small text-muted">
                        <?= htmlspecialchars($canal['cliente_nome'] ?: ($canal['os_codigo'] ?: ($canal['setor'] ?: 'Equipe interna'))) ?>
                    </div>
                    <div class="d-flex gap-2 flex-wrap mt-2">
                        <span class="badge badge-info"><?= (int)$canal['total_mensagens'] ?> mensagens</span>
                        <span class="badge badge-secondary"><?= chatData($canal['ultima_mensagem'] ?: $canal['created_at']) ?></span>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php if (empty($canais)): ?>
                <span class="badge badge-secondary align-self-start">Sem canais ativos</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <div>
                    <h6 class="card-title"><i class="bi bi-chat-left-text me-2 text-info"></i><?= htmlspecialchars($canalAtual['nome'] ?? 'Chat') ?></h6>
                    <?php if ($canalAtual): ?>
                    <div class="d-flex gap-2 flex-wrap mt-1">
                        <span class="badge <?= $tipoClasses[$canalAtual['tipo']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($tipoLabels[$canalAtual['tipo']] ?? $canalAtual['tipo']) ?></span>
                        <span class="badge <?= ($canalAtual['status'] ?? '') === 'ativo' ? 'badge-success' : 'badge-secondary' ?>"><?= htmlspecialchars(ucfirst($canalAtual['status'] ?? 'ativo')) ?></span>
                        <?php if (!empty($canalAtual['cliente_nome'])): ?><span class="badge badge-success"><?= htmlspecialchars($canalAtual['cliente_nome']) ?></span><?php endif; ?>
                        <?php if (!empty($canalAtual['os_codigo'])): ?><span class="badge badge-warning"><?= htmlspecialchars($canalAtual['os_codigo']) ?></span><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <span class="badge badge-secondary"><?= count($mensagens) ?> mensagens</span>
            </div>

            <div class="p-3 d-flex flex-column gap-3" style="min-height:420px; max-height:60vh; overflow:auto" id="chatMensagens">
                <?php foreach ($mensagens as $mensagem): ?>
                <?php $minha = (int)($mensagem['usuario_id'] ?? 0) === (int)Auth::id(); ?>
                <div class="d-flex <?= $minha ? 'justify-content-end' : 'justify-content-start' ?>">
                    <div class="border-kroma rounded-kroma p-3" style="max-width:82%; <?= $minha ? 'background:rgba(108,99,255,.12)' : '' ?>">
                        <div class="d-flex gap-2 flex-wrap mb-2">
                            <span class="badge <?= $minha ? 'badge-primary' : 'badge-secondary' ?>"><?= htmlspecialchars($mensagem['usuario_nome'] ?: 'Sistema') ?></span>
                            <?php if (!empty($mensagem['usuario_setor'])): ?><span class="badge badge-info"><?= htmlspecialchars($mensagem['usuario_setor']) ?></span><?php endif; ?>
                            <span class="badge badge-secondary"><?= chatData($mensagem['created_at']) ?></span>
                        </div>
                        <div><?= nl2br(htmlspecialchars($mensagem['mensagem'])) ?></div>
                        <?php if (!empty($mensagem['mencoes'])): ?>
                        <div class="mt-2"><span class="badge badge-warning">Menções: <?= htmlspecialchars($mensagem['mencoes']) ?></span></div>
                        <?php endif; ?>
                        <?php if (!empty($mensagem['anexo_url'])): ?>
                        <div class="mt-2"><a class="badge badge-primary text-decoration-none" href="<?= htmlspecialchars($mensagem['anexo_url']) ?>" target="_blank">Abrir anexo</a></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($mensagens)): ?>
                <span class="badge badge-secondary align-self-start">Sem mensagens neste canal</span>
                <?php endif; ?>
            </div>

            <?php if ($canalAtual && Auth::pode('chat.criar')): ?>
            <form action="<?= APP_URL ?>/chat/canais/<?= $canalAtual['id'] ?>/mensagens" method="POST" class="border-top p-3" data-loading>
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="row g-2">
                    <div class="col-lg-8">
                        <textarea class="form-control" name="mensagem" rows="3" required placeholder="Digite a mensagem. Use @nome para menções."></textarea>
                    </div>
                    <div class="col-lg-3">
                        <input class="form-control mb-2" name="anexo_url" placeholder="URL do anexo">
                        <span class="badge badge-secondary">Anexo opcional</span>
                    </div>
                    <div class="col-lg-1 d-grid">
                        <button class="btn btn-primary" type="submit" title="Enviar"><i class="bi bi-send"></i></button>
                    </div>
                </div>
            </form>
            <?php elseif ($canalAtual): ?>
            <div class="p-3 border-top"><span class="badge badge-secondary">Somente leitura</span></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (Auth::pode('chat.criar')): ?>
<div class="modal fade" id="modalCanal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= APP_URL ?>/chat/canais/novo" method="POST" data-loading>
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Canal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label">Nome *</label>
                            <input class="form-control" name="nome" required placeholder="Ex: Produção - Prioridades do dia">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" name="tipo">
                                <?php foreach ($tipoLabels as $value => $label): ?>
                                <option value="<?= $value ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Setor</label>
                            <input class="form-control" name="setor" list="chatSetores" placeholder="Opcional">
                            <datalist id="chatSetores">
                                <?php foreach ($contexto['setores'] as $setor): ?>
                                <option value="<?= htmlspecialchars($setor) ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cliente</label>
                            <select class="form-select" name="cliente_id">
                                <option value="">-- Sem cliente --</option>
                                <?php foreach ($contexto['clientes'] as $cliente): ?>
                                <option value="<?= $cliente['id'] ?>"><?= htmlspecialchars($cliente['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">OS</label>
                            <select class="form-select" name="ordem_servico_id">
                                <option value="">-- Sem OS --</option>
                                <?php foreach ($contexto['ordens'] as $ordem): ?>
                                <option value="<?= $ordem['id'] ?>"><?= htmlspecialchars($ordem['codigo'] . ' - ' . $ordem['titulo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle"></i> Criar Canal</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mensagens = document.getElementById('chatMensagens');
    if (mensagens) {
        mensagens.scrollTop = mensagens.scrollHeight;
    }
});
</script>
