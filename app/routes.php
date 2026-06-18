<?php
/**
 * Definição de todas as rotas — KROMA PRINT ERP
 */

// ===========================
// ROTAS PÚBLICAS
// ===========================

// Landing Page
$router->get('/', 'LandingController@index');
$router->post('/orcamento-rapido', 'LandingController@orcamentoRapido');
$router->post('/upload-arquivo', 'LandingController@uploadArquivo');
$router->post('/contato', 'LandingController@contato');

// Autenticação
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->get('/esqueci-senha', 'AuthController@showEsqueciSenha');
$router->post('/esqueci-senha', 'AuthController@esqueciSenha');

// Instalação do sistema
$router->get('/install', 'InstallController@index');
$router->post('/install', 'InstallController@instalar');

// ===========================
// ROTAS PROTEGIDAS (requerem login)
// ===========================

// Dashboard
$router->get('/dashboard', 'DashboardController@index');
$router->get('/dashboard/dados', 'DashboardController@dados');
$router->get('/alertas', 'AlertaController@index');

// Portal do Cliente
$router->get('/portal', 'PortalController@index');
$router->post('/portal/solicitar-orcamento', 'PortalController@solicitarOrcamento');

// BI Executivo
$router->get('/bi', 'BiController@index');
$router->get('/ia', 'IaController@index');
$router->post('/ia/gerar', 'IaController@gerar');

// Usuários
$router->get('/usuarios', 'UsuarioController@index');
$router->get('/usuarios/novo', 'UsuarioController@novo');
$router->post('/usuarios/novo', 'UsuarioController@criar');
$router->get('/usuarios/:id', 'UsuarioController@ver');
$router->get('/usuarios/:id/editar', 'UsuarioController@editar');
$router->post('/usuarios/:id/editar', 'UsuarioController@atualizar');
$router->post('/usuarios/:id/excluir', 'UsuarioController@excluir');
$router->post('/usuarios/:id/toggle-status', 'UsuarioController@toggleStatus');

// Perfis e permissões
$router->get('/perfis', 'PerfilController@index');
$router->get('/perfis/:id/permissoes', 'PerfilController@permissoes');
$router->post('/perfis/:id/permissoes', 'PerfilController@salvarPermissoes');
$router->get('/auditoria', 'AuditoriaController@index');

// Clientes
$router->get('/clientes', 'ClienteController@index');
$router->get('/clientes/novo', 'ClienteController@novo');
$router->post('/clientes/novo', 'ClienteController@criar');
$router->get('/clientes/busca', 'ClienteController@busca');
$router->get('/clientes/:id', 'ClienteController@ver');
$router->get('/clientes/:id/editar', 'ClienteController@editar');
$router->post('/clientes/:id/editar', 'ClienteController@atualizar');
$router->post('/clientes/:id/excluir', 'ClienteController@excluir');

// CRM
$router->get('/crm', 'CrmController@kanban');
$router->get('/crm/leads', 'CrmController@leads');
$router->get('/crm/leads/novo', 'CrmController@novoLead');
$router->post('/crm/leads/novo', 'CrmController@criarLead');
$router->get('/crm/leads/:id/json', 'CrmController@leadJson');
$router->get('/crm/leads/:id', 'CrmController@verLead');
$router->get('/crm/leads/:id/editar', 'CrmController@editarLead');
$router->post('/crm/leads/:id/editar', 'CrmController@atualizarLead');
$router->post('/crm/leads/:id/mover', 'CrmController@moverLead');
$router->post('/crm/leads/:id/excluir', 'CrmController@excluirLead');

// Orçamentos
$router->get('/orcamentos', 'OrcamentoController@index');
$router->get('/orcamentos/novo', 'OrcamentoController@novo');
$router->post('/orcamentos/novo', 'OrcamentoController@criar');
$router->get('/orcamentos/:id', 'OrcamentoController@ver');
$router->get('/orcamentos/:id/editar', 'OrcamentoController@editar');
$router->post('/orcamentos/:id/editar', 'OrcamentoController@atualizar');
$router->post('/orcamentos/:id/enviar', 'OrcamentoController@enviar');
$router->post('/orcamentos/:id/aprovar', 'OrcamentoController@aprovar');
$router->post('/orcamentos/:id/cancelar', 'OrcamentoController@cancelar');

// Produtos, categorias e processos
$router->get('/produtos', 'ProdutoController@index');
$router->get('/produtos/novo', 'ProdutoController@novo');
$router->post('/produtos/novo', 'ProdutoController@criar');
$router->get('/produtos/:id', 'ProdutoController@ver');
$router->get('/produtos/:id/editar', 'ProdutoController@editar');
$router->post('/produtos/:id/editar', 'ProdutoController@atualizar');
$router->post('/produtos/:id/excluir', 'ProdutoController@excluir');
$router->post('/produtos/:id/duplicar', 'ProdutoController@duplicar');

// Produção e ordens de serviço
$router->get('/producao', 'ProducaoController@index');
$router->get('/producao/novo', 'ProducaoController@novo');
$router->post('/producao/novo', 'ProducaoController@criar');
$router->post('/producao/etapas/:id/status', 'ProducaoController@etapaStatus');
$router->get('/producao/:id', 'ProducaoController@ver');
$router->get('/producao/:id/editar', 'ProducaoController@editar');
$router->post('/producao/:id/editar', 'ProducaoController@atualizar');
$router->post('/producao/:id/status', 'ProducaoController@alterarStatus');

// Agenda de equipes e instalações
$router->get('/agenda', 'AgendaController@index');
$router->post('/agenda/novo', 'AgendaController@criar');
$router->post('/agenda/:id/status', 'AgendaController@status');
$router->post('/agenda/:id/excluir', 'AgendaController@excluir');

// Estoque e materiais
$router->get('/estoque', 'EstoqueController@index');
$router->get('/estoque/novo', 'EstoqueController@novo');
$router->post('/estoque/novo', 'EstoqueController@criar');
$router->get('/estoque/:id', 'EstoqueController@ver');
$router->get('/estoque/:id/editar', 'EstoqueController@editar');
$router->post('/estoque/:id/editar', 'EstoqueController@atualizar');
$router->post('/estoque/:id/movimentar', 'EstoqueController@movimentar');

// Financeiro
$router->get('/financeiro', 'FinanceiroController@index');
$router->get('/financeiro/receber/novo', 'FinanceiroController@novoReceber');
$router->post('/financeiro/receber/novo', 'FinanceiroController@criarReceber');
$router->get('/financeiro/receber/:id', 'FinanceiroController@verReceber');
$router->post('/financeiro/receber/:id/baixar', 'FinanceiroController@baixarReceber');
$router->post('/financeiro/receber/:id/cancelar', 'FinanceiroController@cancelarReceber');
$router->get('/financeiro/pagar/novo', 'FinanceiroController@novoPagar');
$router->post('/financeiro/pagar/novo', 'FinanceiroController@criarPagar');
$router->post('/financeiro/pagar/:id/baixar', 'FinanceiroController@baixarPagar');
$router->post('/financeiro/pagar/:id/cancelar', 'FinanceiroController@cancelarPagar');

// Comissões
$router->get('/comissoes', 'ComissaoController@index');
$router->post('/comissoes/sincronizar', 'ComissaoController@sincronizar');
$router->post('/comissoes/:id/liberar', 'ComissaoController@liberar');
$router->post('/comissoes/:id/pagar', 'ComissaoController@pagar');
$router->post('/comissoes/:id/bloquear', 'ComissaoController@bloquear');
$router->post('/comissoes/:id/cancelar', 'ComissaoController@cancelar');

// Compras e fornecedores
$router->get('/compras', 'ComprasController@index');
$router->get('/compras/novo', 'ComprasController@novo');
$router->post('/compras/novo', 'ComprasController@criar');
$router->get('/compras/fornecedores', 'ComprasController@fornecedores');
$router->get('/compras/fornecedores/novo', 'ComprasController@novoFornecedor');
$router->post('/compras/fornecedores/novo', 'ComprasController@criarFornecedor');
$router->get('/compras/fornecedores/:id/editar', 'ComprasController@editarFornecedor');
$router->post('/compras/fornecedores/:id/editar', 'ComprasController@atualizarFornecedor');
$router->get('/compras/:id', 'ComprasController@ver');
$router->get('/compras/:id/editar', 'ComprasController@editar');
$router->post('/compras/:id/editar', 'ComprasController@atualizar');
$router->post('/compras/:id/status', 'ComprasController@status');
$router->post('/compras/:id/receber', 'ComprasController@receber');

// RH operacional
$router->get('/rh', 'RhController@index');
$router->get('/rh/colaboradores/novo', 'RhController@novoColaborador');
$router->post('/rh/colaboradores/novo', 'RhController@criarColaborador');
$router->get('/rh/colaboradores/:id/editar', 'RhController@editarColaborador');
$router->post('/rh/colaboradores/:id/editar', 'RhController@atualizarColaborador');
$router->get('/rh/setores/novo', 'RhController@novoSetor');
$router->post('/rh/setores/novo', 'RhController@criarSetor');
$router->get('/rh/setores/:id/editar', 'RhController@editarSetor');
$router->post('/rh/setores/:id/editar', 'RhController@atualizarSetor');
$router->get('/rh/cargos/novo', 'RhController@novoCargo');
$router->post('/rh/cargos/novo', 'RhController@criarCargo');
$router->get('/rh/cargos/:id/editar', 'RhController@editarCargo');
$router->post('/rh/cargos/:id/editar', 'RhController@atualizarCargo');
$router->get('/rh/equipamentos/novo', 'RhController@novoEquipamento');
$router->post('/rh/equipamentos/novo', 'RhController@criarEquipamento');
$router->get('/rh/equipamentos/:id/editar', 'RhController@editarEquipamento');
$router->post('/rh/equipamentos/:id/editar', 'RhController@atualizarEquipamento');
$router->get('/rh/veiculos/novo', 'RhController@novoVeiculo');
$router->post('/rh/veiculos/novo', 'RhController@criarVeiculo');
$router->get('/rh/veiculos/:id/editar', 'RhController@editarVeiculo');
$router->post('/rh/veiculos/:id/editar', 'RhController@atualizarVeiculo');

// Qualidade e POPs
$router->get('/qualidade', 'QualidadeController@index');
$router->get('/qualidade/pops/novo', 'QualidadeController@novo');
$router->post('/qualidade/pops/novo', 'QualidadeController@criar');
$router->get('/qualidade/pops/:id', 'QualidadeController@ver');
$router->get('/qualidade/pops/:id/editar', 'QualidadeController@editar');
$router->post('/qualidade/pops/:id/editar', 'QualidadeController@atualizar');
$router->post('/qualidade/pops/:id/status', 'QualidadeController@status');
$router->post('/qualidade/pops/:id/revisar', 'QualidadeController@revisar');

// Chamados internos
$router->get('/chamados', 'ChamadoController@index');
$router->get('/chamados/novo', 'ChamadoController@novo');
$router->post('/chamados/novo', 'ChamadoController@criar');
$router->get('/chamados/:id', 'ChamadoController@ver');
$router->get('/chamados/:id/editar', 'ChamadoController@editar');
$router->post('/chamados/:id/editar', 'ChamadoController@atualizar');
$router->post('/chamados/:id/status', 'ChamadoController@status');
$router->post('/chamados/:id/comentarios', 'ChamadoController@comentar');

// WhatsApp
$router->get('/whatsapp', 'WhatsAppController@index');
$router->post('/whatsapp/enviar', 'WhatsAppController@enviar');

// Chat interno
$router->get('/chat', 'ChatController@index');
$router->get('/chat/canais/:id', 'ChatController@index');
$router->post('/chat/canais/novo', 'ChatController@criarCanal');
$router->post('/chat/canais/:id/mensagens', 'ChatController@enviarMensagem');

// Empresa (configurações)
$router->get('/empresa', 'EmpresaController@configuracoes');
$router->post('/empresa', 'EmpresaController@salvar');

// Meu perfil
$router->get('/perfil', 'UsuarioController@meuPerfil');
$router->post('/perfil', 'UsuarioController@atualizarMeuPerfil');

// API interna (retorno JSON)
$router->get('/api/clientes', 'ApiController@clientes');
$router->get('/api/leads', 'ApiController@leads');
$router->get('/api/dashboard', 'ApiController@dashboard');
$router->get('/api/notificacoes/count', 'ApiController@notificacoesCount');
