<?php
use App\Services\Auth;
$csrfToken = Auth::csrfToken();
function finMoney($value): string { return number_format((float)($value ?? 0), 2, ',', '.'); }
?>

<form action="<?= APP_URL ?>/financeiro/receber/novo" method="POST" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-arrow-down-circle me-2 text-success-kroma"></i>Conta a Receber</h6>
                    <span class="badge badge-success">Receita</span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Cliente</label>
                            <select class="form-select" name="cliente_id">
                                <option value="">-- Sem cliente --</option>
                                <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= $cliente['id'] ?>" <?= (string)($conta['cliente_id'] ?? '') === (string)$cliente['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cliente['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Origem</label>
                            <select class="form-select" name="origem">
                                <option value="manual" <?= ($conta['origem'] ?? 'manual') === 'manual' ? 'selected' : '' ?>>Manual</option>
                                <option value="orcamento" <?= ($conta['origem'] ?? '') === 'orcamento' ? 'selected' : '' ?>>Orçamento</option>
                                <option value="ordem_servico" <?= ($conta['origem'] ?? '') === 'ordem_servico' ? 'selected' : '' ?>>OS</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Vencimento</label>
                            <input class="form-control" type="date" name="vencimento" value="<?= htmlspecialchars($conta['vencimento'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Orçamento</label>
                            <select class="form-select" name="orcamento_id">
                                <option value="">-- Sem orçamento --</option>
                                <?php foreach ($orcamentos as $orcamento): ?>
                                <option value="<?= $orcamento['id'] ?>" <?= (string)($conta['orcamento_id'] ?? '') === (string)$orcamento['id'] ? 'selected' : '' ?>><?= htmlspecialchars($orcamento['codigo'] . ' - ' . $orcamento['titulo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">OS</label>
                            <select class="form-select" name="ordem_servico_id">
                                <option value="">-- Sem OS --</option>
                                <?php foreach ($ordens as $ordem): ?>
                                <option value="<?= $ordem['id'] ?>" <?= (string)($conta['ordem_servico_id'] ?? '') === (string)$ordem['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ordem['codigo'] . ' - ' . $ordem['titulo']) ?></option>
                                <?php endforeach; ?>
                                <?php if (!empty($conta['ordem_servico_id']) && empty(array_filter($ordens, fn($o) => (string)$o['id'] === (string)$conta['ordem_servico_id']))): ?>
                                <option value="<?= htmlspecialchars((string)$conta['ordem_servico_id']) ?>" selected>OS vinculada #<?= htmlspecialchars((string)$conta['ordem_servico_id']) ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Descrição *</label>
                            <input class="form-control" name="descricao" required value="<?= htmlspecialchars($conta['descricao'] ?? '') ?>" placeholder="Ex: Recebimento OS-202606-0001">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Valor *</label>
                            <input class="form-control money" name="valor" required value="<?= finMoney($conta['valor'] ?? 0) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" name="observacoes" rows="4" placeholder="Condições, parcelas, dados de cobrança"><?= htmlspecialchars($conta['observacoes'] ?? '') ?></textarea>
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
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Criar Conta</button>
                    <a class="btn btn-secondary" href="<?= APP_URL ?>/financeiro"><i class="bi bi-arrow-left"></i> Voltar</a>
                    <span class="badge badge-info align-self-start">Baixa será registrada na ficha</span>
                </div>
            </div>
        </div>
    </div>
</form>
