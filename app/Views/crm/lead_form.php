<?php
/**
 * View: Formulário de Lead — KROMA PRINT ERP
 */

use App\Services\Auth;
$csrfToken  = Auth::csrfToken();
$isEdicao   = !empty($lead['id']);
$action     = $isEdicao ? (APP_URL . '/crm/leads/' . $lead['id'] . '/editar') : (APP_URL . '/crm/leads/novo');
$titulo     = $isEdicao ? 'Editar Lead' : 'Novo Lead';

// Pré-seleciona estágio pelo GET
$estagioInicial = $_GET['estagio'] ?? ($lead['estagio'] ?? 'nova_solicitacao');
?>

<form action="<?= $action ?>" method="POST" data-loading>
<input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

<div class="row g-3">

    <!-- Dados principais -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-person-lines-fill me-2" style="color:var(--kroma-primary)"></i>Dados do Lead</h6>
            </div>
            <div style="padding:20px">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="nome">Nome Completo *</label>
                        <input type="text" class="form-control" id="nome" name="nome"
                               value="<?= htmlspecialchars($lead['nome'] ?? '') ?>" required
                               placeholder="Nome do contato">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="empresa">Empresa</label>
                        <input type="text" class="form-control" id="empresa" name="empresa"
                               value="<?= htmlspecialchars($lead['empresa'] ?? '') ?>"
                               placeholder="Razão social ou nome fantasia">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">E-mail</label>
                        <div class="input-group">
                            <i class="bi bi-envelope input-group-icon"></i>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= htmlspecialchars($lead['email'] ?? '') ?>"
                                   placeholder="contato@email.com">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="telefone">Telefone</label>
                        <input type="text" class="form-control" id="telefone" name="telefone"
                               data-mask="telefone"
                               value="<?= htmlspecialchars($lead['telefone'] ?? '') ?>"
                               placeholder="(00) 0000-0000">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="whatsapp">WhatsApp</label>
                        <div class="input-group">
                            <i class="bi bi-whatsapp input-group-icon" style="color:#25D366"></i>
                            <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                                   data-mask="telefone"
                                   value="<?= htmlspecialchars($lead['whatsapp'] ?? '') ?>"
                                   placeholder="(00) 00000-0000">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label" for="produto_interesse">Produto de Interesse</label>
                        <input type="text" class="form-control" id="produto_interesse" name="produto_interesse"
                               value="<?= htmlspecialchars($lead['produto_interesse'] ?? '') ?>"
                               placeholder="Ex: Banner, Fachada ACM, DTF...">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label" for="descricao">Descrição da Necessidade</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"
                                  placeholder="Descreva o que o cliente precisa..."><?= htmlspecialchars($lead['descricao'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label" for="observacoes">Observações Internas</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="2"
                                  placeholder="Notas para a equipe..."><?= htmlspecialchars($lead['observacoes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Painel lateral -->
    <div class="col-md-4">

        <!-- Estágio e Qualificação -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-funnel me-2" style="color:var(--kroma-primary)"></i>Funil e Qualificação</h6>
            </div>
            <div style="padding:16px">
                <div class="mb-3">
                    <label class="form-label" for="estagio">Estágio</label>
                <select class="form-select" id="estagio" name="estagio">
                    <?php
                    $estagios = [
                        'nova_solicitacao'  => 'Nova Solicitação',
                        'orcamento'         => 'Orçamento',
                        'orcamento_enviado' => 'Orçamento Enviado',
                        'aprovado'          => 'Aprovado',
                        'em_producao'       => 'Em Produção',
                        'entregue'          => 'Entregue',
                        'pos_venda'         => 'Pós-venda',
                        'perdido'           => 'Perdido',
                    ];
                    foreach ($estagios as $val => $label):
                    ?>
                    <option value="<?= $val ?>" <?= $estagioInicial === $val ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="temperatura">Temperatura</label>
                    <select class="form-select" id="temperatura" name="temperatura">
                        <option value="frio"   <?= ($lead['temperatura'] ?? '') === 'frio'   ? 'selected' : '' ?>>🔵 Frio</option>
                        <option value="morno"  <?= ($lead['temperatura'] ?? 'morno') === 'morno' ? 'selected' : '' ?>>🟡 Morno</option>
                        <option value="quente" <?= ($lead['temperatura'] ?? '') === 'quente' ? 'selected' : '' ?>>🔴 Quente</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="prioridade">Prioridade</label>
                    <select class="form-select" id="prioridade" name="prioridade">
                        <option value="baixa"   <?= ($lead['prioridade'] ?? '') === 'baixa'   ? 'selected' : '' ?>>Baixa</option>
                        <option value="media"   <?= ($lead['prioridade'] ?? 'media') === 'media' ? 'selected' : '' ?>>Média</option>
                        <option value="alta"    <?= ($lead['prioridade'] ?? '') === 'alta'    ? 'selected' : '' ?>>Alta</option>
                        <option value="urgente" <?= ($lead['prioridade'] ?? '') === 'urgente' ? 'selected' : '' ?>>Urgente</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="probabilidade">Probabilidade: <span id="probLabel"><?= $lead['probabilidade'] ?? 50 ?>%</span></label>
                    <input type="range" class="form-range" id="probabilidade" name="probabilidade"
                           min="0" max="100" step="5"
                           value="<?= $lead['probabilidade'] ?? 50 ?>"
                           style="accent-color: var(--kroma-primary)">
                </div>
                <div>
                    <label class="form-label" for="valor_estimado">Valor Estimado (R$)</label>
                    <input type="text" class="form-control" id="valor_estimado" name="valor_estimado"
                           value="<?= !empty($lead['valor_estimado']) ? number_format($lead['valor_estimado'], 2, ',', '.') : '' ?>"
                           placeholder="0,00">
                </div>
            </div>
        </div>

        <!-- Origem e Atribuição -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-person-gear me-2" style="color:var(--kroma-accent)"></i>Atribuição</h6>
            </div>
            <div style="padding:16px">
                <div class="mb-3">
                    <label class="form-label" for="origem">Origem do Lead</label>
                    <select class="form-select" id="origem" name="origem">
                        <?php
                        $origens = [
                            'landing_page' => 'Landing Page',
                            'whatsapp'     => 'WhatsApp',
                            'indicacao'    => 'Indicação',
                            'visita'       => 'Visita',
                            'ligacao'      => 'Ligação',
                            'email'        => 'E-mail',
                            'instagram'    => 'Instagram',
                            'facebook'     => 'Facebook',
                            'google'       => 'Google',
                            'outro'        => 'Outro',
                        ];
                        foreach ($origens as $val => $label):
                        ?>
                        <option value="<?= $val ?>" <?= ($lead['origem'] ?? 'outro') === $val ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="vendedor_id">Vendedor Responsável</label>
                    <select class="form-select" id="vendedor_id" name="vendedor_id">
                        <option value="">-- Selecione --</option>
                        <?php foreach ($vendedores as $v): ?>
                        <option value="<?= $v['id'] ?>" <?= ($lead['vendedor_id'] ?? Auth::id()) == $v['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v['nome']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="data_follow_up">Data Follow-up</label>
                    <input type="date" class="form-control" id="data_follow_up" name="data_follow_up"
                           value="<?= $lead['data_follow_up'] ?? '' ?>">
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Botões de ação -->
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check2-circle"></i>
        <?= $isEdicao ? 'Atualizar Lead' : 'Criar Lead' ?>
    </button>
    <a href="<?= APP_URL ?>/crm" class="btn btn-secondary">
        <i class="bi bi-x"></i> Cancelar
    </a>
</div>

</form>

<script>
// Atualiza label da probabilidade
document.getElementById('probabilidade')?.addEventListener('input', function() {
    document.getElementById('probLabel').textContent = this.value + '%';
});
</script>
