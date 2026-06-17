<?php
use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$isEdicao = !empty($pop['id']);
$action = $isEdicao ? APP_URL . '/qualidade/pops/' . $pop['id'] . '/editar' : APP_URL . '/qualidade/pops/novo';
?>

<form action="<?= $action ?>" method="POST" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-clipboard-check me-2 text-primary-kroma"></i>Dados do POP</h6>
                    <span class="badge badge-info"><?= $isEdicao ? 'Edição' : 'Novo cadastro' ?></span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Código</label>
                            <input class="form-control" name="codigo" value="<?= htmlspecialchars($pop['codigo'] ?? '') ?>" placeholder="Automático">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Título *</label>
                            <input class="form-control" name="titulo" required value="<?= htmlspecialchars($pop['titulo'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Versão</label>
                            <input class="form-control" type="number" min="1" name="versao" value="<?= htmlspecialchars($pop['versao'] ?? 1) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Setor</label>
                            <input class="form-control" name="setor" value="<?= htmlspecialchars($pop['setor'] ?? '') ?>" placeholder="Produção, Acabamento, Instalação...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categoria</label>
                            <input class="form-control" name="categoria" value="<?= htmlspecialchars($pop['categoria'] ?? '') ?>" placeholder="Impressão, LED, Fachada...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <?php foreach ($contexto['statusLabels'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= ($pop['status'] ?? 'rascunho') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Processo vinculado</label>
                            <select class="form-select" name="processo_id">
                                <option value="">-- Sem vínculo --</option>
                                <?php foreach ($contexto['processos'] as $processo): ?>
                                <option value="<?= $processo['id'] ?>" <?= (string)($pop['processo_id'] ?? '') === (string)$processo['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(($processo['setor'] ? $processo['setor'] . ' - ' : '') . $processo['nome']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Responsável</label>
                            <select class="form-select" name="responsavel_id">
                                <option value="">-- Sem responsável --</option>
                                <?php foreach ($contexto['usuarios'] as $usuario): ?>
                                <option value="<?= $usuario['id'] ?>" <?= (string)($pop['responsavel_id'] ?? '') === (string)$usuario['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($usuario['nome']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Vigência inicial</label>
                            <input class="form-control" type="date" name="vigencia_inicio" value="<?= htmlspecialchars($pop['vigencia_inicio'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Revisão prevista</label>
                            <input class="form-control" type="date" name="revisao_prevista" value="<?= htmlspecialchars($pop['revisao_prevista'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Anexo / link</label>
                            <input class="form-control" name="anexo_url" value="<?= htmlspecialchars($pop['anexo_url'] ?? '') ?>" placeholder="https://...">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Objetivo</label>
                            <textarea class="form-control" name="objetivo" rows="3"><?= htmlspecialchars($pop['objetivo'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Procedimento</label>
                            <textarea class="form-control" name="procedimento" rows="8" placeholder="Descreva o passo a passo operacional..."><?= htmlspecialchars($pop['procedimento'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Checklist</label>
                            <textarea class="form-control" name="checklist" rows="6" placeholder="- Conferir arquivo&#10;- Validar material&#10;- Registrar aprovação"><?= htmlspecialchars($pop['checklist'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" name="observacoes" rows="3"><?= htmlspecialchars($pop['observacoes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-shield-check me-2 text-success-kroma"></i>Controle de Qualidade</h6>
                </div>
                <div class="p-3 d-flex flex-column gap-2">
                    <span class="badge badge-primary align-self-start">Versão atual: v<?= (int)($pop['versao'] ?? 1) ?></span>
                    <span class="badge badge-info align-self-start">Checklist alimenta processos produtivos vinculados</span>
                    <span class="badge badge-warning align-self-start">POP aprovado atualiza o processo automaticamente</span>
                    <?php if (!empty($pop['aprovador_nome'])): ?>
                    <span class="badge badge-success align-self-start">Aprovado por <?= htmlspecialchars($pop['aprovador_nome']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Salvar POP</button>
                <a class="btn btn-secondary" href="<?= $isEdicao ? APP_URL . '/qualidade/pops/' . $pop['id'] : APP_URL . '/qualidade' ?>"><i class="bi bi-arrow-left"></i> Voltar</a>
            </div>
        </div>
    </div>
</form>
