<?php
use App\Services\Auth;
$csrfToken = Auth::csrfToken();
$empresa = $empresa ?? [];
?>

<form action="<?= APP_URL ?>/empresa" method="POST" data-loading>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-building me-2 text-primary-kroma"></i>Dados Cadastrais</h6>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label">Razão Social *</label>
                            <input class="form-control" name="razao_social" required value="<?= htmlspecialchars($empresa['razao_social'] ?? '') ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Nome Fantasia</label>
                            <input class="form-control" name="nome_fantasia" value="<?= htmlspecialchars($empresa['nome_fantasia'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">CNPJ</label>
                            <input class="form-control" name="cnpj" data-mask="cnpj" value="<?= htmlspecialchars($empresa['cnpj'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Inscrição Estadual</label>
                            <input class="form-control" name="ie" value="<?= htmlspecialchars($empresa['ie'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Slogan</label>
                            <input class="form-control" name="slogan" value="<?= htmlspecialchars($empresa['slogan'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-geo-alt me-2 text-warning-kroma"></i>Endereço</h6>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">CEP</label><input class="form-control" name="cep" data-mask="cep" value="<?= htmlspecialchars($empresa['cep'] ?? '') ?>"></div>
                        <div class="col-md-7"><label class="form-label">Endereço</label><input class="form-control" name="endereco" value="<?= htmlspecialchars($empresa['endereco'] ?? '') ?>"></div>
                        <div class="col-md-2"><label class="form-label">Número</label><input class="form-control" name="numero" value="<?= htmlspecialchars($empresa['numero'] ?? '') ?>"></div>
                        <div class="col-md-5"><label class="form-label">Bairro</label><input class="form-control" name="bairro" value="<?= htmlspecialchars($empresa['bairro'] ?? '') ?>"></div>
                        <div class="col-md-5"><label class="form-label">Cidade</label><input class="form-control" name="cidade" value="<?= htmlspecialchars($empresa['cidade'] ?? '') ?>"></div>
                        <div class="col-md-2"><label class="form-label">UF</label><input class="form-control" maxlength="2" name="estado" value="<?= htmlspecialchars($empresa['estado'] ?? '') ?>"></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-file-text me-2 text-primary-kroma"></i>Condições de Orçamento</h6>
                </div>
                <div class="p-3">
                    <textarea class="form-control" name="condicoes_orcamento" rows="5" placeholder="Validade, pagamento, produção, retirada e instalação..."><?= htmlspecialchars($empresa['condicoes_orcamento'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-telephone me-2 text-success-kroma"></i>Contato</h6>
                </div>
                <div class="p-3">
                    <label class="form-label">Telefone</label>
                    <input class="form-control mb-3" name="telefone" data-mask="telefone" value="<?= htmlspecialchars($empresa['telefone'] ?? '') ?>">
                    <label class="form-label">WhatsApp</label>
                    <input class="form-control mb-3" name="whatsapp" data-mask="telefone" value="<?= htmlspecialchars($empresa['whatsapp'] ?? '') ?>">
                    <label class="form-label">E-mail</label>
                    <input class="form-control mb-3" type="email" name="email" value="<?= htmlspecialchars($empresa['email'] ?? '') ?>">
                    <label class="form-label">Site</label>
                    <input class="form-control" name="site" value="<?= htmlspecialchars($empresa['site'] ?? '') ?>">
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title"><i class="bi bi-plug me-2 text-primary-kroma"></i>Integrações</h6>
                </div>
                <div class="p-3">
                    <label class="form-label">Token WhatsApp Viicio</label>
                    <input class="form-control mb-3" name="token_whatsapp" value="<?= htmlspecialchars($empresa['token_whatsapp'] ?? '') ?>">
                    <label class="form-label">Endpoint WhatsApp Viicio</label>
                    <input class="form-control mb-3" name="endpoint_whatsapp" value="<?= htmlspecialchars($empresa['endpoint_whatsapp'] ?? '') ?>" placeholder="https://api.viicio.com/...">
                    <label class="form-label">Modo WhatsApp</label>
                    <select class="form-select mb-3" name="modo_whatsapp">
                        <option value="simulado" <?= ($empresa['modo_whatsapp'] ?? 'simulado') === 'simulado' ? 'selected' : '' ?>>Simulado</option>
                        <option value="producao" <?= ($empresa['modo_whatsapp'] ?? '') === 'producao' ? 'selected' : '' ?>>Produção</option>
                    </select>
                    <label class="form-label">Chave OpenAI</label>
                    <input class="form-control mb-3" name="chave_openai" value="<?= htmlspecialchars($empresa['chave_openai'] ?? '') ?>">
                    <label class="form-label">Chave Gemini</label>
                    <input class="form-control mb-3" name="chave_gemini" value="<?= htmlspecialchars($empresa['chave_gemini'] ?? '') ?>">
                    <label class="form-label">Modo IA</label>
                    <select class="form-select mb-3" name="modo_ia">
                        <option value="simulado" <?= ($empresa['modo_ia'] ?? 'simulado') === 'simulado' ? 'selected' : '' ?>>Simulado</option>
                        <option value="producao" <?= ($empresa['modo_ia'] ?? '') === 'producao' ? 'selected' : '' ?>>Produção</option>
                    </select>
                    <label class="form-label">Provedor IA</label>
                    <select class="form-select mb-3" name="provedor_ia">
                        <option value="openai" <?= ($empresa['provedor_ia'] ?? 'openai') === 'openai' ? 'selected' : '' ?>>OpenAI</option>
                        <option value="gemini" <?= ($empresa['provedor_ia'] ?? '') === 'gemini' ? 'selected' : '' ?>>Gemini</option>
                    </select>
                    <label class="form-label">Modelo IA</label>
                    <input class="form-control mb-3" name="modelo_ia" value="<?= htmlspecialchars($empresa['modelo_ia'] ?? 'gpt-5.5') ?>">
                    <label class="form-label">Limite diário IA</label>
                    <input class="form-control mb-3" type="number" min="1" name="limite_ia_diario" value="<?= (int)($empresa['limite_ia_diario'] ?? 100) ?>">
                    <label class="form-label">Prompt padrão IA</label>
                    <textarea class="form-control mb-3" name="prompt_padrao_ia" rows="3" placeholder="Tom, regras comerciais e limites da IA"><?= htmlspecialchars($empresa['prompt_padrao_ia'] ?? '') ?></textarea>
                    <label class="form-label">Chave Asaas</label>
                    <input class="form-control mb-3" name="chave_asaas" value="<?= htmlspecialchars($empresa['chave_asaas'] ?? '') ?>">
                    <label class="form-label">Ambiente Asaas</label>
                    <select class="form-select" name="ambiente_asaas">
                        <option value="sandbox" <?= ($empresa['ambiente_asaas'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' ?>>Sandbox</option>
                        <option value="producao" <?= ($empresa['ambiente_asaas'] ?? '') === 'producao' ? 'selected' : '' ?>>Produção</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Salvar Configurações</button>
        <a class="btn btn-secondary" href="<?= APP_URL ?>/dashboard"><i class="bi bi-x"></i> Cancelar</a>
    </div>
</form>
