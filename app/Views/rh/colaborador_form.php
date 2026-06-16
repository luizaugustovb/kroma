<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$isEdicao = !empty($colaborador['id']);
$action = $isEdicao ? APP_URL . '/rh/colaboradores/' . $colaborador['id'] . '/editar' : APP_URL . '/rh/colaboradores/novo';
function rhMoney($value): string { return number_format((float)($value ?? 0), 2, ',', '.'); }
?>

<form action="<?= $action ?>" method="POST" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-person-badge me-2 text-primary-kroma"></i>Dados do Colaborador</h6>
                    <span class="badge badge-info"><?= $isEdicao ? 'Edição' : 'Novo cadastro' ?></span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Nome *</label><input class="form-control" name="nome" required value="<?= htmlspecialchars($colaborador['nome'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">CPF</label><input class="form-control" name="cpf" value="<?= htmlspecialchars($colaborador['cpf'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">RG</label><input class="form-control" name="rg" value="<?= htmlspecialchars($colaborador['rg'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Nascimento</label><input class="form-control" type="date" name="data_nascimento" value="<?= htmlspecialchars($colaborador['data_nascimento'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Sexo</label><select class="form-select" name="sexo"><option value="">-</option><option value="M" <?= ($colaborador['sexo'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option><option value="F" <?= ($colaborador['sexo'] ?? '') === 'F' ? 'selected' : '' ?>>Feminino</option><option value="O" <?= ($colaborador['sexo'] ?? '') === 'O' ? 'selected' : '' ?>>Outro</option></select></div>
                        <div class="col-md-6"><label class="form-label">E-mail</label><input class="form-control" type="email" name="email" value="<?= htmlspecialchars($colaborador['email'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Telefone</label><input class="form-control" name="telefone" value="<?= htmlspecialchars($colaborador['telefone'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">WhatsApp</label><input class="form-control" name="whatsapp" value="<?= htmlspecialchars($colaborador['whatsapp'] ?? '') ?>"></div>
                        <div class="col-md-6">
                            <label class="form-label">Usuário vinculado</label>
                            <select class="form-select" name="usuario_id">
                                <option value="">-- Sem usuário --</option>
                                <?php foreach ($contexto['usuarios'] as $usuario): ?>
                                <option value="<?= $usuario['id'] ?>" <?= (string)($colaborador['usuario_id'] ?? '') === (string)$usuario['id'] ? 'selected' : '' ?>><?= htmlspecialchars($usuario['nome'] . ' - ' . $usuario['email']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><?php foreach ($contexto['colaboradorStatus'] as $value => $label): ?><option value="<?= $value ?>" <?= ($colaborador['status'] ?? 'ativo') === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-3"><label class="form-label">Contrato</label><select class="form-select" name="tipo_contrato"><?php foreach ($contexto['contratoLabels'] as $value => $label): ?><option value="<?= $value ?>" <?= ($colaborador['tipo_contrato'] ?? 'clt') === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="card-title"><i class="bi bi-cash-stack me-2 text-success-kroma"></i>Cargo e Custo</h6></div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Cargo</label><input class="form-control" name="cargo" list="cargosList" value="<?= htmlspecialchars($colaborador['cargo'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Setor</label><input class="form-control" name="setor" list="setoresList" value="<?= htmlspecialchars($colaborador['setor'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Admissão</label><input class="form-control" type="date" name="data_admissao" value="<?= htmlspecialchars($colaborador['data_admissao'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Salário</label><input class="form-control money" name="salario" value="<?= rhMoney($colaborador['salario'] ?? 0) ?>"></div>
                        <div class="col-md-3"><label class="form-label">Jornada mensal</label><input class="form-control" name="jornada_mensal" value="<?= htmlspecialchars($colaborador['jornada_mensal'] ?? 220) ?>"></div>
                        <div class="col-md-3"><label class="form-label">Custo/hora</label><input class="form-control money" name="custo_hora" value="<?= rhMoney($colaborador['custo_hora'] ?? 0) ?>"></div>
                        <div class="col-md-3"><label class="form-label">Demissão</label><input class="form-control" type="date" name="data_demissao" value="<?= htmlspecialchars($colaborador['data_demissao'] ?? '') ?>"></div>
                        <div class="col-12"><label class="form-label">Habilidades</label><textarea class="form-control" name="habilidades" rows="2" placeholder="Impressão, acabamento, instalação, atendimento..."><?= htmlspecialchars($colaborador['habilidades'] ?? '') ?></textarea></div>
                        <div class="col-12"><label class="form-label">Observações</label><textarea class="form-control" name="observacoes" rows="3"><?= htmlspecialchars($colaborador['observacoes'] ?? '') ?></textarea></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-3">
                <div class="card-header"><h6 class="card-title"><i class="bi bi-bank me-2 text-info"></i>Dados Bancários</h6></div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label">Banco</label><input class="form-control" name="banco" value="<?= htmlspecialchars($colaborador['banco'] ?? '') ?>"></div>
                        <div class="col-6"><label class="form-label">Agência</label><input class="form-control" name="agencia" value="<?= htmlspecialchars($colaborador['agencia'] ?? '') ?>"></div>
                        <div class="col-6"><label class="form-label">Conta</label><input class="form-control" name="conta" value="<?= htmlspecialchars($colaborador['conta'] ?? '') ?>"></div>
                        <div class="col-12"><label class="form-label">Tipo de conta</label><select class="form-select" name="tipo_conta"><option value="corrente" <?= ($colaborador['tipo_conta'] ?? 'corrente') === 'corrente' ? 'selected' : '' ?>>Corrente</option><option value="poupanca" <?= ($colaborador['tipo_conta'] ?? '') === 'poupanca' ? 'selected' : '' ?>>Poupança</option></select></div>
                        <div class="col-12"><label class="form-label">Pix</label><input class="form-control" name="pix" value="<?= htmlspecialchars($colaborador['pix'] ?? '') ?>"></div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h6 class="card-title"><i class="bi bi-check2-circle me-2 text-success-kroma"></i>Ações</h6></div>
                <div class="p-3 d-flex flex-column gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> <?= $isEdicao ? 'Atualizar Colaborador' : 'Cadastrar Colaborador' ?></button>
                    <a class="btn btn-secondary" href="<?= APP_URL ?>/rh"><i class="bi bi-arrow-left"></i> Voltar</a>
                    <span class="badge badge-warning align-self-start">Custo/hora alimenta análises operacionais</span>
                </div>
            </div>
        </div>
    </div>
</form>

<datalist id="cargosList"><?php foreach ($contexto['cargos'] as $cargo): ?><option value="<?= htmlspecialchars($cargo['nome']) ?>"></option><?php endforeach; ?></datalist>
<datalist id="setoresList"><?php foreach ($contexto['setores'] as $setor): ?><option value="<?= htmlspecialchars($setor['nome']) ?>"></option><?php endforeach; ?></datalist>
