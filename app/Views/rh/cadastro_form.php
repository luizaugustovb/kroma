<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$isEdicao = !empty($registro['id']);
$base = $tipo === 'setor' ? 'setores' : 'cargos';
$action = $isEdicao ? APP_URL . '/rh/' . $base . '/' . $registro['id'] . '/editar' : APP_URL . '/rh/' . $base . '/novo';
function cadMoney($value): string { return number_format((float)($value ?? 0), 2, ',', '.'); }
?>

<form action="<?= $action ?>" method="POST" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-diagram-3 me-2 text-primary-kroma"></i><?= $tipo === 'setor' ? 'Setor' : 'Cargo' ?></h6>
                    <span class="badge badge-info"><?= $isEdicao ? 'Edição' : 'Novo cadastro' ?></span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-8"><label class="form-label">Nome *</label><input class="form-control" name="nome" required value="<?= htmlspecialchars($registro['nome'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status"><option value="ativo" <?= ($registro['status'] ?? 'ativo') === 'ativo' ? 'selected' : '' ?>>Ativo</option><option value="inativo" <?= ($registro['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option></select></div>
                        <?php if ($tipo === 'setor'): ?>
                        <div class="col-md-6">
                            <label class="form-label">Responsável</label>
                            <select class="form-select" name="responsavel_id">
                                <option value="">-- Sem responsável --</option>
                                <?php foreach ($contexto['colaboradores'] as $colaborador): ?>
                                <option value="<?= $colaborador['id'] ?>" <?= (string)($registro['responsavel_id'] ?? '') === (string)$colaborador['id'] ? 'selected' : '' ?>><?= htmlspecialchars($colaborador['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php else: ?>
                        <div class="col-md-4">
                            <label class="form-label">Setor</label>
                            <select class="form-select" name="setor_id">
                                <option value="">-- Sem setor --</option>
                                <?php foreach ($contexto['setores'] as $setor): ?>
                                <option value="<?= $setor['id'] ?>" <?= (string)($registro['setor_id'] ?? '') === (string)$setor['id'] ? 'selected' : '' ?>><?= htmlspecialchars($setor['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Salário base</label><input class="form-control money" name="salario_base" value="<?= cadMoney($registro['salario_base'] ?? 0) ?>"></div>
                        <div class="col-md-4"><label class="form-label">Custo/hora padrão</label><input class="form-control money" name="custo_hora_padrao" value="<?= cadMoney($registro['custo_hora_padrao'] ?? 0) ?>"></div>
                        <?php endif; ?>
                        <div class="col-12"><label class="form-label">Descrição</label><textarea class="form-control" name="descricao" rows="4"><?= htmlspecialchars($registro['descricao'] ?? '') ?></textarea></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h6 class="card-title"><i class="bi bi-check2-circle me-2 text-success-kroma"></i>Ações</h6></div>
                <div class="p-3 d-flex flex-column gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Salvar</button>
                    <a class="btn btn-secondary" href="<?= APP_URL ?>/rh"><i class="bi bi-arrow-left"></i> Voltar</a>
                    <span class="badge badge-secondary align-self-start"><?= $tipo === 'setor' ? 'Organiza produção e atendimento' : 'Padroniza custos e funções' ?></span>
                </div>
            </div>
        </div>
    </div>
</form>
