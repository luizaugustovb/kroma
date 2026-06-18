<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$isEdicao = !empty($chamado['id']);
$action = $isEdicao ? APP_URL . '/chamados/' . $chamado['id'] . '/editar' : APP_URL . '/chamados/novo';
$prazoValue = '';
if (!empty($chamado['prazo'])) {
    $prazoValue = date('Y-m-d\TH:i', strtotime($chamado['prazo']));
}
?>

<form action="<?= $action ?>" method="POST" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-ticket-detailed me-2 text-primary-kroma"></i>Dados do Chamado</h6>
                    <span class="badge badge-info"><?= $isEdicao ? 'Edição' : 'Novo chamado' ?></span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Título *</label>
                            <input class="form-control" name="titulo" required value="<?= htmlspecialchars($chamado['titulo'] ?? '') ?>" placeholder="Ex: Ajustar arte antes da produção">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Setor</label>
                            <input class="form-control" name="setor" list="setoresChamado" value="<?= htmlspecialchars($chamado['setor'] ?? '') ?>" placeholder="Ex: Produção">
                            <datalist id="setoresChamado">
                                <?php foreach ($contexto['setores'] as $setor): ?>
                                <option value="<?= htmlspecialchars($setor) ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Prioridade</label>
                            <select class="form-select" name="prioridade">
                                <?php foreach ($contexto['prioridadeLabels'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= ($chamado['prioridade'] ?? 'media') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <?php foreach ($contexto['statusLabels'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= ($chamado['status'] ?? 'aberto') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Solicitante</label>
                            <select class="form-select" name="solicitante_id">
                                <?php foreach ($contexto['usuarios'] as $usuario): ?>
                                <option value="<?= $usuario['id'] ?>" <?= (string)($chamado['solicitante_id'] ?? Auth::id()) === (string)$usuario['id'] ? 'selected' : '' ?>><?= htmlspecialchars($usuario['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Responsável</label>
                            <select class="form-select" name="responsavel_id">
                                <option value="">-- Sem responsável --</option>
                                <?php foreach ($contexto['usuarios'] as $usuario): ?>
                                <option value="<?= $usuario['id'] ?>" <?= (string)($chamado['responsavel_id'] ?? '') === (string)$usuario['id'] ? 'selected' : '' ?>><?= htmlspecialchars($usuario['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Prazo</label>
                            <input class="form-control" type="datetime-local" name="prazo" value="<?= htmlspecialchars($prazoValue) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <textarea class="form-control" name="descricao" rows="5" placeholder="Descreva a demanda, critérios de conclusão e qualquer restrição importante."><?= htmlspecialchars($chamado['descricao'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-link-45deg me-2 text-info"></i>Vínculos Opcionais</h6>
                    <span class="badge badge-secondary">Opcional</span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Cliente</label>
                            <select class="form-select" name="cliente_id">
                                <option value="">-- Sem cliente --</option>
                                <?php foreach ($contexto['clientes'] as $cliente): ?>
                                <option value="<?= $cliente['id'] ?>" <?= (string)($chamado['cliente_id'] ?? '') === (string)$cliente['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cliente['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Orçamento</label>
                            <select class="form-select" name="orcamento_id">
                                <option value="">-- Sem orçamento --</option>
                                <?php foreach ($contexto['orcamentos'] as $orcamento): ?>
                                <?php $label = trim($orcamento['codigo'] . ' - ' . $orcamento['titulo'] . (!empty($orcamento['cliente_nome']) ? ' / ' . $orcamento['cliente_nome'] : '')); ?>
                                <option value="<?= $orcamento['id'] ?>" <?= (string)($chamado['orcamento_id'] ?? '') === (string)$orcamento['id'] ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ordem de Serviço</label>
                            <select class="form-select" name="ordem_servico_id">
                                <option value="">-- Sem OS --</option>
                                <?php foreach ($contexto['ordens'] as $ordem): ?>
                                <?php $label = trim($ordem['codigo'] . ' - ' . $ordem['titulo'] . (!empty($ordem['cliente_nome']) ? ' / ' . $ordem['cliente_nome'] : '')); ?>
                                <option value="<?= $ordem['id'] ?>" <?= (string)($chamado['ordem_servico_id'] ?? '') === (string)$ordem['id'] ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Compra</label>
                            <select class="form-select" name="compra_id">
                                <option value="">-- Sem compra --</option>
                                <?php foreach ($contexto['compras'] as $compra): ?>
                                <?php $label = trim($compra['codigo'] . ' - ' . $compra['titulo'] . (!empty($compra['fornecedor_nome']) ? ' / ' . $compra['fornecedor_nome'] : '')); ?>
                                <option value="<?= $compra['id'] ?>" <?= (string)($chamado['compra_id'] ?? '') === (string)$compra['id'] ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-check2-circle me-2 text-success-kroma"></i>Ações</h6>
                </div>
                <div class="p-3 d-flex flex-column gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> <?= $isEdicao ? 'Atualizar Chamado' : 'Criar Chamado' ?></button>
                    <a class="btn btn-secondary" href="<?= APP_URL ?>/chamados"><i class="bi bi-arrow-left"></i> Voltar</a>
                    <?php if ($isEdicao): ?>
                    <a class="btn btn-secondary" href="<?= APP_URL ?>/chamados/<?= $chamado['id'] ?>"><i class="bi bi-eye"></i> Ver Chamado</a>
                    <?php endif; ?>
                    <span class="badge badge-warning align-self-start">Prazos vencidos aparecem como badge de atraso</span>
                </div>
            </div>
        </div>
    </div>
</form>
