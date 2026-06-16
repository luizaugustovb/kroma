<?php
use App\Services\Auth;
$csrfToken = Auth::csrfToken();
if (!function_exists('finMoney')) {
    function finMoney($value): string { return number_format((float)($value ?? 0), 2, ',', '.'); }
}
?>

<form action="<?= APP_URL ?>/financeiro/pagar/novo" method="POST" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-arrow-up-circle me-2 text-danger"></i>Conta a Pagar</h6>
                    <span class="badge badge-danger">Despesa</span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Fornecedor</label>
                            <input class="form-control" name="fornecedor" value="<?= htmlspecialchars($conta['fornecedor'] ?? '') ?>" placeholder="Fornecedor ou prestador">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Categoria</label>
                            <input class="form-control" name="categoria" value="<?= htmlspecialchars($conta['categoria'] ?? '') ?>" placeholder="Material, fixo, frete...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Vencimento</label>
                            <input class="form-control" type="date" name="vencimento" value="<?= htmlspecialchars($conta['vencimento'] ?? '') ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Descrição *</label>
                            <input class="form-control" name="descricao" required value="<?= htmlspecialchars($conta['descricao'] ?? '') ?>" placeholder="Ex: Compra de lona 440g">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Valor *</label>
                            <input class="form-control money" name="valor" required value="<?= finMoney($conta['valor'] ?? 0) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" name="observacoes" rows="4" placeholder="Nota fiscal, condição de pagamento, centro de custo"><?= htmlspecialchars($conta['observacoes'] ?? '') ?></textarea>
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
                    <span class="badge badge-warning align-self-start">Pagamento gera saída de caixa</span>
                </div>
            </div>
        </div>
    </div>
</form>
