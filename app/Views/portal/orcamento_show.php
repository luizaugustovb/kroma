<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();

$orcamentoClasses = [
    'rascunho' => 'badge-secondary',
    'em_calculo' => 'badge-warning',
    'enviado' => 'badge-info',
    'aprovado' => 'badge-success',
    'recusado' => 'badge-danger',
    'cancelado' => 'badge-danger',
    'expirado' => 'badge-secondary',
];

function portalMoeda(float $valor): string {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function portalData(?string $data): string {
    return $data ? date('d/m/Y', strtotime($data)) : '-';
}
?>

<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title">
            <i class="bi bi-file-earmark-text me-2 text-primary-kroma"></i>
            <?= htmlspecialchars($orcamento['codigo']) ?> — <?= htmlspecialchars($orcamento['titulo'] ?: 'Sem título') ?>
        </h6>
        <span class="badge <?= $orcamentoClasses[$orcamento['status']] ?? 'badge-secondary' ?>">
            <?= $statusOrcamento[$orcamento['status']] ?? $orcamento['status'] ?>
        </span>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <strong class="text-muted small">Cliente</strong>
                <p class="mb-0"><?= htmlspecialchars($orcamento['cliente_nome'] ?? '-') ?></p>
            </div>
            <div class="col-md-3">
                <strong class="text-muted small">Vendedor</strong>
                <p class="mb-0"><?= htmlspecialchars($orcamento['vendedor_nome'] ?? '-') ?></p>
            </div>
            <div class="col-md-3">
                <strong class="text-muted small">Validade</strong>
                <p class="mb-0"><?= portalData($orcamento['validade']) ?></p>
            </div>
        </div>

        <?php if ($orcamento['condicao_pagamento'] ?? false): ?>
        <div class="mb-3">
            <strong class="text-muted small">Condições de pagamento</strong>
            <p class="mb-0"><?= nl2br(htmlspecialchars($orcamento['condicao_pagamento'])) ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($orcamento['arquivo_projeto'])): ?>
<?php
$fileBasename = htmlspecialchars(basename($orcamento['arquivo_projeto']));
$ext = strtolower(pathinfo($fileBasename, PATHINFO_EXTENSION));
$imageExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$isImage = in_array($ext, $imageExts, true);
$fileUrl = APP_URL . '/public/uploads/orcamentos/' . $fileBasename;
?>
<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-paperclip me-2 text-primary-kroma"></i>Arquivo do Projeto</h6>
        <span class="badge badge-info">Mockup / Exemplo</span>
    </div>
    <div class="card-body">
        <?php if ($isImage): ?>
            <a href="<?= $fileUrl ?>" target="_blank">
                <img src="<?= $fileUrl ?>" alt="Arquivo do projeto" class="img-fluid rounded" style="max-width:100%;max-height:400px;">
            </a>
        <?php else: ?>
            <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-outline-primary">
                <i class="bi bi-eye"></i> Visualizar arquivo
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-list-check me-2 text-info"></i>Itens do orçamento</h6>
        <span class="badge badge-info"><?= count($itens) ?> itens</span>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th width="60">Qtd</th>
                    <th>Descrição</th>
                    <th width="140" class="text-end">Valor Unit.</th>
                    <th width="140" class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                <tr>
                    <td><?= number_format((float)$item['quantidade'], 2, ',', '.') ?></td>
                    <td>
                        <strong><?= htmlspecialchars($item['descricao']) ?></strong>
                        <?php if ($item['produto_nome'] ?? false): ?>
                            <div class="small text-muted"><?= htmlspecialchars($item['produto_nome']) ?></div>
                        <?php endif; ?>
                        <?php if ((float)($item['area_m2'] ?? 0) > 0): ?>
                            <div class="small text-muted"><?= number_format((float)$item['area_m2'], 3, ',', '.') ?> m²</div>
                        <?php endif; ?>
                    </td>
                    <td class="text-end"><?= portalMoeda((float)($item['preco_unitario'] ?? 0)) ?></td>
                    <td class="text-end"><strong><?= portalMoeda((float)($item['total'] ?? 0)) ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($itens)): ?>
                <tr><td colspan="4"><span class="badge badge-secondary">Sem itens</span></td></tr>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($itens)): ?>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end fw-semibold">Total</td>
                    <td class="text-end fw-bold"><?= portalMoeda((float)$orcamento['total']) ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php if (in_array($orcamento['status'], ['enviado'], true)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-check-circle me-2 text-success-kroma"></i>Responder orçamento</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <form action="<?= APP_URL ?>/portal/orcamentos/<?= $orcamento['id'] ?>/aprovar" method="POST" data-loading>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <p class="mb-2">Ao aprovar, você confirma que concorda com os valores e condições apresentados.</p>
                    <button type="submit" class="btn btn-success"
                            onclick="return confirm('Confirmar aprovação do orçamento?')">
                        <i class="bi bi-check-lg"></i> Aprovar orçamento
                    </button>
                </form>
            </div>
            <div class="col-md-6">
                <form action="<?= APP_URL ?>/portal/orcamentos/<?= $orcamento['id'] ?>/recusar" method="POST" data-loading>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <p class="mb-2">Caso não concorde, informe o motivo para nos ajudar a melhorar.</p>
                    <div class="input-group">
                        <input class="form-control" name="motivo_recusa" placeholder="Motivo (opcional)" maxlength="500">
                        <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Confirmar recusa do orçamento?')">
                            <i class="bi bi-x-lg"></i> Recusar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php elseif ($orcamento['status'] === 'aprovado'): ?>
<div class="alert alert-success d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill fs-4"></i>
    <span>Orçamento aprovado em <?= portalData($orcamento['aprovado_at']) ?>. Entraremos em contato em breve.</span>
</div>
<?php elseif ($orcamento['status'] === 'recusado'): ?>
<div class="alert alert-secondary d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-info-circle-fill fs-4"></i>
    <span>Orçamento recusado. Se mudar de ideia, entre em contato conosco.</span>
</div>
<?php endif; ?>

<div class="d-flex">
    <a href="<?= APP_URL ?>/portal" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar ao portal</a>
</div>
