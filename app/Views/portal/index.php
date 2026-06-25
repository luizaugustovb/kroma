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
$ordemClasses = [
    'aberta' => 'badge-secondary',
    'em_producao' => 'badge-primary',
    'aguardando' => 'badge-warning',
    'finalizada' => 'badge-success',
    'cancelada' => 'badge-danger',
];
$prioridadeClasses = [
    'baixa' => 'badge-secondary',
    'media' => 'badge-info',
    'alta' => 'badge-warning',
    'urgente' => 'badge-danger',
];
$financeiroClasses = [
    'aberto' => 'badge-info',
    'parcial' => 'badge-warning',
    'pago' => 'badge-success',
    'cancelado' => 'badge-danger',
];
$leadClasses = [
    'novo_lead' => 'badge-info',
    'primeiro_contato' => 'badge-primary',
    'orcamento_rapido' => 'badge-warning',
    'orcamento_ia' => 'badge-primary',
    'orcamento_enviado' => 'badge-info',
    'negociacao' => 'badge-warning',
    'aprovado' => 'badge-success',
    'em_producao' => 'badge-primary',
    'entregue' => 'badge-success',
    'pos_venda' => 'badge-info',
    'recorrencia' => 'badge-primary',
    'perdido' => 'badge-danger',
];
$leadLabels = [
    'novo_lead' => 'Novo lead',
    'primeiro_contato' => 'Primeiro contato',
    'orcamento_rapido' => 'Orçamento rápido',
    'orcamento_ia' => 'Orçamento IA',
    'orcamento_enviado' => 'Orçamento enviado',
    'negociacao' => 'Negociação',
    'aprovado' => 'Aprovado',
    'em_producao' => 'Em produção',
    'entregue' => 'Entregue',
    'pos_venda' => 'Pós-venda',
    'recorrencia' => 'Recorrência',
    'perdido' => 'Perdido',
];

function portalMoeda(float $valor): string {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function portalData(?string $data): string {
    return $data ? date('d/m/Y', strtotime($data)) : '-';
}

function portalPrazoBadge(?string $data, string $status): array {
    if (!$data) {
        return ['badge-secondary', 'Sem prazo'];
    }
    if (!in_array($status, ['finalizada', 'cancelada', 'pago'], true) && $data < date('Y-m-d')) {
        return ['badge-danger', 'Vencido'];
    }
    if ($data === date('Y-m-d')) {
        return ['badge-warning', 'Hoje'];
    }
    return ['badge-info', portalData($data)];
}
?>

<?php if (!$cliente): ?>
<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title"><i class="bi bi-person-workspace me-2 text-primary-kroma"></i>Vínculo do cliente</h6>
        <span class="badge badge-warning">Cliente não vinculado</span>
    </div>
    <div class="p-3">
        <span class="badge badge-warning">Solicite ao administrador o vínculo do seu usuário a um cadastro de cliente.</span>
    </div>
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="bi bi-file-earmark-text"></i></div>
            <div class="kpi-value"><?= (int)$resumo['orcamentos'] ?></div>
            <div class="kpi-label">Orçamentos</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="bi bi-gear"></i></div>
            <div class="kpi-value"><?= (int)$resumo['os_abertas'] ?></div>
            <div class="kpi-label">OS Abertas</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon warning"><i class="bi bi-cash-stack"></i></div>
            <div class="kpi-value" style="font-size:22px"><?= portalMoeda((float)$resumo['financeiro_aberto']) ?></div>
            <div class="kpi-label">Financeiro Aberto</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="bi bi-send"></i></div>
            <div class="kpi-value"><?= (int)$resumo['solicitacoes'] ?></div>
            <div class="kpi-label">Solicitações</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-building me-2 text-primary-kroma"></i>Dados do cliente</h6>
                <span class="badge <?= $cliente ? 'badge-success' : 'badge-secondary' ?>"><?= $cliente ? 'Vinculado' : 'Pendente' ?></span>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <tbody>
                        <tr><th>Nome</th><td><?= htmlspecialchars($cliente['nome'] ?? '-') ?></td></tr>
                        <tr><th>E-mail</th><td><?= htmlspecialchars($cliente['email'] ?? '-') ?></td></tr>
                        <tr><th>WhatsApp</th><td><?= htmlspecialchars($cliente['whatsapp'] ?? '-') ?></td></tr>
                        <tr><th>Cidade</th><td><?= htmlspecialchars(trim(($cliente['cidade'] ?? '') . ' ' . ($cliente['estado'] ?? '')) ?: '-') ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-plus-circle me-2 text-success-kroma"></i>Solicitar orçamento</h6>
                <span class="badge <?= $cliente ? 'badge-info' : 'badge-secondary' ?>"><?= $cliente ? 'Disponível' : 'Bloqueado' ?></span>
            </div>
            <form action="<?= APP_URL ?>/portal/solicitar-orcamento" method="POST" class="p-3" enctype="multipart/form-data" data-loading>
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Produto ou serviço</label>
                        <input class="form-control" name="produto_interesse" maxlength="300" required <?= $cliente ? '' : 'disabled' ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Prazo desejado</label>
                        <input class="form-control" name="prazo_desejado" maxlength="120" placeholder="Ex.: próxima semana" <?= $cliente ? '' : 'disabled' ?>>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" name="descricao" rows="4" required <?= $cliente ? '' : 'disabled' ?>></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Arquivos do projeto</label>
                        <input class="form-control" type="file" name="arquivos[]" multiple <?= $cliente ? '' : 'disabled' ?>
                               accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.tif,.tiff,.svg,.pdf,.cdr,.psd,.ai,.eps,.zip,.rar">
                        <div class="small text-secondary mt-1">Aceita fotos, PDF, CDR, PSD, imagens, vetores e arquivos compactados.</div>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit" <?= $cliente ? '' : 'disabled' ?>><i class="bi bi-send"></i> Enviar solicitação</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-6">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-file-earmark-text me-2 text-primary-kroma"></i>Orçamentos</h6>
                <span class="badge badge-info"><?= count($orcamentos) ?> registros</span>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Valor</th>
                            <th>Validade</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orcamentos as $orcamento): ?>
                        <?php [$validadeClass, $validadeLabel] = portalPrazoBadge($orcamento['validade'], $orcamento['status']); ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($orcamento['codigo']) ?></strong><div class="small text-muted"><?= htmlspecialchars($orcamento['titulo']) ?></div></td>
                            <td><strong><?= portalMoeda((float)$orcamento['total']) ?></strong></td>
                            <td><span class="badge <?= $validadeClass ?>"><?= $validadeLabel ?></span></td>
                            <td><span class="badge <?= $orcamentoClasses[$orcamento['status']] ?? 'badge-secondary' ?>"><?= $statusOrcamento[$orcamento['status']] ?? $orcamento['status'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($orcamentos)): ?>
                        <tr><td colspan="4"><span class="badge badge-secondary">Sem orçamentos</span></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-send me-2 text-info"></i>Solicitações recentes</h6>
                <span class="badge badge-info"><?= count($solicitacoes) ?> registros</span>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <?php foreach ($solicitacoes as $solicitacao): ?>
                <div class="border-kroma rounded-kroma p-2">
                    <div class="d-flex justify-content-between gap-2">
                        <strong><?= htmlspecialchars($solicitacao['produto_interesse'] ?: 'Solicitação') ?></strong>
                        <span class="badge <?= $leadClasses[$solicitacao['estagio']] ?? 'badge-secondary' ?>"><?= $leadLabels[$solicitacao['estagio']] ?? $solicitacao['estagio'] ?></span>
                    </div>
                    <span class="badge badge-secondary"><?= portalData($solicitacao['created_at']) ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($solicitacoes)): ?>
                    <span class="badge badge-secondary align-self-start">Sem solicitações</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-gear me-2 text-primary-kroma"></i>Ordens de serviço</h6>
                <span class="badge badge-info"><?= count($ordens) ?> registros</span>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>OS</th>
                            <th>Prazo</th>
                            <th>Progresso</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordens as $ordem): ?>
                        <?php
                            $totalEtapas = max(1, (int)$ordem['total_etapas']);
                            $progresso = round(((int)$ordem['etapas_concluidas'] / $totalEtapas) * 100);
                            [$prazoClass, $prazoLabel] = portalPrazoBadge($ordem['data_prometida'], $ordem['status']);
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($ordem['codigo']) ?></strong>
                                <div class="small text-muted"><?= htmlspecialchars($ordem['titulo']) ?></div>
                                <span class="badge <?= $prioridadeClasses[$ordem['prioridade']] ?? 'badge-secondary' ?>"><?= $prioridadeLabels[$ordem['prioridade']] ?? $ordem['prioridade'] ?></span>
                            </td>
                            <td><span class="badge <?= $prazoClass ?>"><?= $prazoLabel ?></span></td>
                            <td><span class="badge badge-info"><?= $progresso ?>%</span></td>
                            <td><span class="badge <?= $ordemClasses[$ordem['status']] ?? 'badge-secondary' ?>"><?= $statusOrdem[$ordem['status']] ?? $ordem['status'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($ordens)): ?>
                        <tr><td colspan="4"><span class="badge badge-secondary">Sem ordens de serviço</span></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-cash-stack me-2 text-warning"></i>Financeiro</h6>
                <span class="badge badge-info"><?= count($financeiro) ?> registros</span>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Saldo</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($financeiro as $conta): ?>
                        <?php
                            $saldo = max(0, (float)$conta['valor'] - (float)$conta['valor_pago']);
                            [$vencimentoClass, $vencimentoLabel] = portalPrazoBadge($conta['vencimento'], $conta['status']);
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($conta['codigo']) ?></strong><div class="small text-muted"><?= htmlspecialchars($conta['descricao']) ?></div></td>
                            <td><span class="badge <?= $saldo > 0 ? 'badge-warning' : 'badge-success' ?>"><?= portalMoeda($saldo) ?></span></td>
                            <td><span class="badge <?= $vencimentoClass ?>"><?= $vencimentoLabel ?></span></td>
                            <td><span class="badge <?= $financeiroClasses[$conta['status']] ?? 'badge-secondary' ?>"><?= $statusFinanceiro[$conta['status']] ?? $conta['status'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($financeiro)): ?>
                        <tr><td colspan="4"><span class="badge badge-secondary">Sem financeiro aberto</span></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
