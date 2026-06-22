<?php

use App\Services\Auth;
?>

<div class="card mb-3">
    <div class="p-3 d-flex align-items-center gap-3">
        <i class="bi bi-shield-exclamation fs-4 text-warning-kroma"></i>
        <div>
            <div class="fw-bold">Correção administrativa de estoque</div>
            <div class="small text-muted">Preencha apenas os campos que precisam ser corrigidos. Deixe em branco para manter o valor atual. Cada correção gera uma movimentação de ajuste rastreável.</div>
        </div>
    </div>
</div>

<form action="<?= APP_URL ?>/estoque/correcao" method="POST" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="card">
        <div class="card-header">
            <h6 class="card-title"><i class="bi bi-pencil-square me-2 text-warning-kroma"></i>Ajuste de Saldo e Custo</h6>
            <span class="badge badge-warning"><?= count($materiais) ?> materiais ativos</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Saldo atual</th>
                        <th style="width:150px">Saldo correto</th>
                        <th>Custo atual</th>
                        <th style="width:150px">Custo correto</th>
                        <th style="width:220px">Justificativa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materiais as $m): ?>
                        <tr>
                            <td>
                                <input type="hidden" name="material_id[]" value="<?= $m['id'] ?>">
                                <div class="fw-bold"><?= htmlspecialchars($m['nome']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($m['codigo'] ?? '') ?> · <?= htmlspecialchars($m['unidade']) ?></div>
                            </td>
                            <td><?= number_format((float)$m['estoque_atual'], 3, ',', '.') ?> <?= htmlspecialchars($m['unidade']) ?></td>
                            <td>
                                <input type="text" class="form-control form-control-sm"
                                    name="estoque_novo[]"
                                    placeholder="Ex: <?= number_format((float)$m['estoque_atual'], 3, ',', '.') ?>"
                                    autocomplete="off">
                            </td>
                            <td>R$ <?= number_format((float)$m['custo_atual'], 2, ',', '.') ?></td>
                            <td>
                                <input type="text" class="form-control form-control-sm"
                                    name="custo_novo[]"
                                    placeholder="Ex: <?= number_format((float)$m['custo_atual'], 2, ',', '.') ?>"
                                    autocomplete="off">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm"
                                    name="justificativa[]"
                                    placeholder="Motivo da correção"
                                    value="Correção administrativa">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($materiais)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Nenhum material ativo encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="p-3 d-flex gap-2">
            <button class="btn btn-warning" type="submit"><i class="bi bi-check2-circle"></i> Aplicar Correções</button>
            <a class="btn btn-secondary" href="<?= APP_URL ?>/estoque"><i class="bi bi-arrow-left"></i> Voltar ao Estoque</a>
        </div>
    </div>
</form>