<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$isEdicao = !empty($registro['id']);
$base = $tipo === 'equipamento' ? 'equipamentos' : 'veiculos';
$action = $isEdicao ? APP_URL . '/rh/' . $base . '/' . $registro['id'] . '/editar' : APP_URL . '/rh/' . $base . '/novo';
function recMoney($value): string { return number_format((float)($value ?? 0), 2, ',', '.'); }
$tiposEquipamento = ['maquina' => 'Máquina', 'ferramenta' => 'Ferramenta', 'computador' => 'Computador', 'impressora' => 'Impressora', 'acabamento' => 'Acabamento', 'instalacao' => 'Instalação', 'outro' => 'Outro'];
$tiposVeiculo = ['carro' => 'Carro', 'moto' => 'Moto', 'van' => 'Van', 'caminhao' => 'Caminhão', 'outro' => 'Outro'];
?>

<form action="<?= $action ?>" method="POST" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi <?= $tipo === 'equipamento' ? 'bi-tools' : 'bi-truck' ?> me-2 text-primary-kroma"></i><?= $tipo === 'equipamento' ? 'Equipamento' : 'Veículo' ?></h6>
                    <span class="badge badge-info"><?= $isEdicao ? 'Edição' : 'Novo cadastro' ?></span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Código</label><input class="form-control" name="codigo" value="<?= htmlspecialchars($registro['codigo'] ?? '') ?>" placeholder="Automático"></div>
                        <div class="col-md-6"><label class="form-label">Nome *</label><input class="form-control" name="nome" required value="<?= htmlspecialchars($registro['nome'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><?php foreach ($contexto['equipamentoStatus'] as $value => $label): ?><option value="<?= $value ?>" <?= ($registro['status'] ?? 'ativo') === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" name="tipo">
                                <?php foreach (($tipo === 'equipamento' ? $tiposEquipamento : $tiposVeiculo) as $value => $label): ?>
                                <option value="<?= $value ?>" <?= ($registro['tipo'] ?? '') === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if ($tipo === 'equipamento'): ?>
                        <div class="col-md-4"><label class="form-label">Setor</label><select class="form-select" name="setor_id"><option value="">-- Sem setor --</option><?php foreach ($contexto['setores'] as $setor): ?><option value="<?= $setor['id'] ?>" <?= (string)($registro['setor_id'] ?? '') === (string)$setor['id'] ? 'selected' : '' ?>><?= htmlspecialchars($setor['nome']) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-4"><label class="form-label">Patrimônio</label><input class="form-control" name="patrimonio" value="<?= htmlspecialchars($registro['patrimonio'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Marca</label><input class="form-control" name="marca" value="<?= htmlspecialchars($registro['marca'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Modelo</label><input class="form-control" name="modelo" value="<?= htmlspecialchars($registro['modelo'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Valor de aquisição</label><input class="form-control money" name="valor_aquisicao" value="<?= recMoney($registro['valor_aquisicao'] ?? 0) ?>"></div>
                        <?php else: ?>
                        <div class="col-md-4"><label class="form-label">Placa</label><input class="form-control" name="placa" value="<?= htmlspecialchars($registro['placa'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Km atual</label><input class="form-control" name="km_atual" value="<?= htmlspecialchars($registro['km_atual'] ?? 0) ?>"></div>
                        <div class="col-md-4"><label class="form-label">Custo/km</label><input class="form-control money" name="custo_km" value="<?= recMoney($registro['custo_km'] ?? 0) ?>"></div>
                        <?php endif; ?>
                        <div class="col-md-4">
                            <label class="form-label">Responsável</label>
                            <select class="form-select" name="responsavel_id">
                                <option value="">-- Sem responsável --</option>
                                <?php foreach ($contexto['colaboradores'] as $colaborador): ?>
                                <option value="<?= $colaborador['id'] ?>" <?= (string)($registro['responsavel_id'] ?? '') === (string)$colaborador['id'] ? 'selected' : '' ?>><?= htmlspecialchars($colaborador['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Custo/hora</label><input class="form-control money" name="custo_hora" value="<?= recMoney($registro['custo_hora'] ?? 0) ?>"></div>
                        <div class="col-md-4"><label class="form-label">Aquisição</label><input class="form-control" type="date" name="data_aquisicao" value="<?= htmlspecialchars($registro['data_aquisicao'] ?? '') ?>" <?= $tipo === 'veiculo' ? 'disabled' : '' ?>></div>
                        <div class="col-md-4"><label class="form-label">Manutenção prevista</label><input class="form-control" type="date" name="manutencao_prevista" value="<?= htmlspecialchars($registro['manutencao_prevista'] ?? '') ?>"></div>
                        <div class="col-12"><label class="form-label">Observações</label><textarea class="form-control" name="observacoes" rows="4"><?= htmlspecialchars($registro['observacoes'] ?? '') ?></textarea></div>
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
                    <span class="badge badge-warning align-self-start"><?= $tipo === 'equipamento' ? 'Custo/hora ajuda na precificação' : 'Custo/km ajuda na instalação' ?></span>
                </div>
            </div>
        </div>
    </div>
</form>
