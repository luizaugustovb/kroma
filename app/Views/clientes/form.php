<?php

/**
 * View: Formulário de Cliente — KROMA PRINT ERP
 */

use App\Services\Auth;

$csrfToken = Auth::csrfToken();
$isEdicao  = !empty($cliente['id']);
$action    = $isEdicao ? (APP_URL . '/clientes/' . $cliente['id'] . '/editar') : (APP_URL . '/clientes/novo');
?>

<form action="<?= $action ?>" method="POST" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="row g-3">

        <!-- Coluna principal -->
        <div class="col-md-8">

            <!-- Tipo de Pessoa (abas) -->
            <div class="card mb-3">
                <div class="card-header" style="padding-bottom:0; border-bottom:none;">
                    <h6 class="card-title"><i class="bi bi-person-vcard me-2" style="color:var(--kroma-primary)"></i>Dados Cadastrais</h6>
                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-sm tipo-btn <?= ($cliente['tipo_pessoa'] ?? 'juridica') === 'juridica' ? 'btn-primary' : 'btn-secondary' ?>" data-tipo="juridica">
                            <i class="bi bi-building"></i> Pessoa Jurídica
                        </button>
                        <button type="button" class="btn btn-sm tipo-btn <?= ($cliente['tipo_pessoa'] ?? '') === 'fisica' ? 'btn-primary' : 'btn-secondary' ?>" data-tipo="fisica">
                            <i class="bi bi-person"></i> Pessoa Física
                        </button>
                    </div>
                    <input type="hidden" name="tipo_pessoa" id="tipo_pessoa" value="<?= $cliente['tipo_pessoa'] ?? 'juridica' ?>">
                </div>
                <div style="padding:20px">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="nome">
                                <span id="labelNome">Razão Social</span> *
                            </label>
                            <input type="text" class="form-control" id="nome" name="nome"
                                value="<?= htmlspecialchars($cliente['nome'] ?? '') ?>" required
                                placeholder="Nome completo ou Razão Social">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="cpf_cnpj"><span id="labelDoc">CNPJ</span></label>
                            <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj"
                                data-mask="cnpj"
                                value="<?= htmlspecialchars($cliente['cpf_cnpj'] ?? '') ?>"
                                placeholder="00.000.000/0000-00">
                        </div>
                        <div class="col-md-6" id="campoNomeFantasia">
                            <label class="form-label" for="nome_fantasia">Nome Fantasia</label>
                            <input type="text" class="form-control" id="nome_fantasia" name="nome_fantasia"
                                value="<?= htmlspecialchars($cliente['nome_fantasia'] ?? '') ?>"
                                placeholder="Nome fantasia da empresa">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="rg_ie"><span id="labelRgIe">Inscrição Estadual</span></label>
                            <input type="text" class="form-control" id="rg_ie" name="rg_ie"
                                value="<?= htmlspecialchars($cliente['rg_ie'] ?? '') ?>"
                                placeholder="Inscrição Estadual ou RG">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contatos -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-telephone me-2" style="color:var(--kroma-accent)"></i>Contatos</h6>
                </div>
                <div style="padding:20px">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="email">E-mail</label>
                            <div class="input-group">
                                <i class="bi bi-envelope input-group-icon"></i>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= htmlspecialchars($cliente['email'] ?? '') ?>"
                                    placeholder="email@empresa.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="whatsapp">WhatsApp</label>
                            <div class="input-group">
                                <i class="bi bi-whatsapp input-group-icon" style="color:#25D366"></i>
                                <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                                    data-mask="telefone"
                                    value="<?= htmlspecialchars($cliente['whatsapp'] ?? '') ?>"
                                    placeholder="(00) 00000-0000">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="telefone">Telefone Fixo</label>
                            <input type="text" class="form-control" id="telefone" name="telefone"
                                data-mask="telefone"
                                value="<?= htmlspecialchars($cliente['telefone'] ?? '') ?>"
                                placeholder="(00) 0000-0000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="celular">Celular</label>
                            <input type="text" class="form-control" id="celular" name="celular"
                                data-mask="telefone"
                                value="<?= htmlspecialchars($cliente['celular'] ?? '') ?>"
                                placeholder="(00) 00000-0000">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Endereço -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-geo-alt me-2" style="color:var(--kroma-warning)"></i>Endereço</h6>
                </div>
                <div style="padding:20px">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label" for="cep">CEP</label>
                            <input type="text" class="form-control" id="cep" name="cep"
                                data-mask="cep"
                                value="<?= htmlspecialchars($cliente['cep'] ?? '') ?>"
                                placeholder="00000-000">
                            <div class="form-text">Preenchimento automático</div>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label" for="endereco">Logradouro</label>
                            <input type="text" class="form-control" id="endereco" name="endereco"
                                value="<?= htmlspecialchars($cliente['endereco'] ?? '') ?>"
                                placeholder="Rua, Av, Alameda...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="numero">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero"
                                value="<?= htmlspecialchars($cliente['numero'] ?? '') ?>"
                                placeholder="Nº">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="complemento">Complemento</label>
                            <input type="text" class="form-control" id="complemento" name="complemento"
                                value="<?= htmlspecialchars($cliente['complemento'] ?? '') ?>"
                                placeholder="Sala, Andar...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="bairro">Bairro</label>
                            <input type="text" class="form-control" id="bairro" name="bairro"
                                value="<?= htmlspecialchars($cliente['bairro'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="cidade">Cidade</label>
                            <input type="text" class="form-control" id="cidade" name="cidade"
                                value="<?= htmlspecialchars($cliente['cidade'] ?? '') ?>">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label" for="estado">UF</label>
                            <input type="text" class="form-control" id="estado" name="estado"
                                maxlength="2"
                                value="<?= htmlspecialchars($cliente['estado'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-chat-left-text me-2" style="color:var(--text-muted)"></i>Observações</h6>
                </div>
                <div style="padding:20px">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="observacoes">Observações Gerais</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="2"
                                placeholder="Notas visíveis para todos..."><?= htmlspecialchars($cliente['observacoes'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="observacoes_internas">Observações Internas</label>
                            <textarea class="form-control" id="observacoes_internas" name="observacoes_internas" rows="2"
                                placeholder="Notas internas da equipe (não aparece para o cliente)..."><?= htmlspecialchars($cliente['observacoes_internas'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Coluna lateral -->
        <div class="col-md-4">

            <!-- Classificação e Tipo -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-tag me-2" style="color:var(--kroma-primary)"></i>Classificação</h6>
                </div>
                <div style="padding:16px">
                    <div class="mb-3">
                        <label class="form-label" for="tipo_cliente">Tipo de Cliente</label>
                        <select class="form-select" id="tipo_cliente" name="tipo_cliente">
                            <option value="cliente_final" <?= ($cliente['tipo_cliente'] ?? '') === 'cliente_final' ? 'selected' : '' ?>>Cliente Final</option>
                            <option value="revenda" <?= in_array($cliente['tipo_cliente'] ?? '', ['revenda', 'parceiro']) ? 'selected' : '' ?>>Revenda / Parceiro</option>
                            <option value="terceirizado" <?= in_array($cliente['tipo_cliente'] ?? '', ['terceirizado', 'corporativo', 'orgao_publico']) ? 'selected' : '' ?>>Terceirizado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="classificacao">Classificação</label>
                        <select class="form-select" id="classificacao" name="classificacao">
                            <option value="bronze" <?= ($cliente['classificacao'] ?? 'bronze') === 'bronze'   ? 'selected' : '' ?>>🥉 Bronze</option>
                            <option value="prata" <?= ($cliente['classificacao'] ?? '') === 'prata'    ? 'selected' : '' ?>>🥈 Prata</option>
                            <option value="ouro" <?= ($cliente['classificacao'] ?? '') === 'ouro'     ? 'selected' : '' ?>>🥇 Ouro</option>
                            <option value="diamante" <?= ($cliente['classificacao'] ?? '') === 'diamante' ? 'selected' : '' ?>>💎 Diamante</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="ativo" <?= ($cliente['status'] ?? 'ativo') === 'ativo'     ? 'selected' : '' ?>>Ativo</option>
                            <option value="inativo" <?= ($cliente['status'] ?? '') === 'inativo'   ? 'selected' : '' ?>>Inativo</option>
                            <option value="bloqueado" <?= ($cliente['status'] ?? '') === 'bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="origem_lead">Origem</label>
                        <input type="text" class="form-control" id="origem_lead" name="origem_lead"
                            value="<?= htmlspecialchars($cliente['origem_lead'] ?? '') ?>"
                            placeholder="Como chegou até nós?">
                    </div>
                    <div>
                        <label class="form-label" for="limite_credito">Limite de Crédito (R$)</label>
                        <input type="text" class="form-control" id="limite_credito" name="limite_credito"
                            value="<?= !empty($cliente['limite_credito']) ? number_format($cliente['limite_credito'], 2, ',', '.') : '' ?>"
                            placeholder="0,00">
                    </div>
                </div>
            </div>

            <!-- Vendedor -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-person-check me-2" style="color:var(--kroma-accent)"></i>Responsável</h6>
                </div>
                <div style="padding:16px">
                    <label class="form-label" for="vendedor_id">Vendedor Responsável</label>
                    <select class="form-select" id="vendedor_id" name="vendedor_id">
                        <option value="">-- Selecione --</option>
                        <?php foreach ($vendedores as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= ($cliente['vendedor_id'] ?? '') == $v['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($v['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Preferências WhatsApp -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-whatsapp me-2" style="color:#25D366"></i>WhatsApp</h6>
                </div>
                <div style="padding:16px">
                    <p style="font-size:12px; color:var(--text-muted); margin-bottom:12px">Preferências de comunicação</p>
                    <?php
                    $whatsPrefs = [
                        'recebe_whatsapp'   => 'Receber mensagens gerais',
                        'recebe_campanha'   => 'Receber campanhas',
                        'recebe_producao'   => 'Status de produção',
                        'recebe_financeiro' => 'Avisos financeiros',
                    ];
                    foreach ($whatsPrefs as $name => $label):
                        $checked = ($cliente[$name] ?? 1) ? 'checked' : '';
                    ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="<?= $name ?>" id="<?= $name ?>" value="1" <?= $checked ?>>
                            <label class="form-check-label" for="<?= $name ?>" style="font-size:13px; color:var(--text-secondary)">
                                <?= $label ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- Botões -->
    <div class="d-flex gap-2 mt-3 align-items-center flex-wrap">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check2-circle"></i>
            <?= $isEdicao ? 'Atualizar Cliente' : 'Cadastrar Cliente' ?>
        </button>
        <a href="<?= APP_URL ?>/clientes" class="btn btn-secondary">
            <i class="bi bi-x"></i> Cancelar
        </a>
        <?php if (!$isEdicao): ?>
            <span class="badge badge-info">
                <i class="bi bi-whatsapp" style="color:#25D366"></i> Com e-mail informado, o portal e as boas-vindas são criados automaticamente
            </span>
        <?php endif; ?>
    </div>

</form>

<script>
    // Toggle entre PJ e PF
    document.querySelectorAll('.tipo-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tipo = this.dataset.tipo;
            document.getElementById('tipo_pessoa').value = tipo;

            document.querySelectorAll('.tipo-btn').forEach(b => b.className = b.className.replace('btn-primary', 'btn-secondary'));
            this.classList.replace('btn-secondary', 'btn-primary');

            if (tipo === 'fisica') {
                document.getElementById('labelNome').textContent = 'Nome Completo';
                document.getElementById('labelDoc').textContent = 'CPF';
                document.getElementById('labelRgIe').textContent = 'RG';
                document.getElementById('cpf_cnpj').placeholder = '000.000.000-00';
                document.getElementById('campoNomeFantasia').style.display = 'none';
            } else {
                document.getElementById('labelNome').textContent = 'Razão Social';
                document.getElementById('labelDoc').textContent = 'CNPJ';
                document.getElementById('labelRgIe').textContent = 'Inscrição Estadual';
                document.getElementById('cpf_cnpj').placeholder = '00.000.000/0000-00';
                document.getElementById('campoNomeFantasia').style.display = '';
            }
        });
    });

    // Inicializa estado correto
    document.addEventListener('DOMContentLoaded', () => {
        const tipo = document.getElementById('tipo_pessoa').value;
        if (tipo === 'fisica') {
            document.querySelector('[data-tipo="fisica"]').click();
        }
    });
</script>