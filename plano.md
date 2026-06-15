KROMA PRINT ERP/CRM — Plano de Implementação
Visão Geral
O KROMA PRINT ERP/CRM é uma plataforma completa para gráfica rápida, comunicação visual, impressão digital, DTF, brindes, uniformes, fachadas, produtos personalizados e aluguel de painéis de LED.

Stack tecnológico: PHP 8+, MySQL, PDO, HTML5, CSS3, Bootstrap 5, JavaScript, AJAX, Chart.js, DataTables, SweetAlert, API REST interna.

User Review Required
IMPORTANT

Este é um sistema de grande porte com 9 fases de desenvolvimento. Recomendo fortemente iniciarmos pela Fase 1 (Base do sistema) e avançarmos fase por fase com validação do usuário entre cada etapa.

WARNING

O sistema completo exige estrutura de banco de dados robusta. O banco de dados será criado automaticamente via script de instalação (install.php).

CAUTION

Integrações externas (WhatsApp API Viicio, OpenAI, Gemini, Asaas) requerem chaves de API externas que precisam ser configuradas pelo usuário após a instalação.

Arquitetura do Projeto

kroma/
├── app/
│   ├── Controllers/       # Controladores MVC
│   ├── Models/            # Modelos de dados (PDO)
│   ├── Views/             # Views PHP
│   ├── Services/          # Serviços (Auth, WhatsApp, IA, etc.)
│   ├── Middleware/        # Middleware (auth, permissões, CSRF)
│   └── Helpers/           # Funções auxiliares
├── public/                # Pasta pública (DocumentRoot)
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
│   └── uploads/           # Uploads públicos (landing page)
├── config/                # Configurações (DB, app)
├── database/              # Migrations e seeds SQL
├── storage/               # Uploads internos (fora do public)
├── logs/                  # Logs de acesso e ações
├── .htaccess
└── index.php              # Entry point
Fase 1 — Base do Sistema (INÍCIO)
Esta é a fase que vamos implementar primeiro.

1.1 Estrutura base MVC
[NEW] index.php — Entry point com roteador
[NEW] .htaccess — Rewrite rules
[NEW] config/database.php — Configuração PDO
[NEW] config/app.php — Configurações gerais
[NEW] app/Services/Router.php — Sistema de rotas
[NEW] app/Services/Auth.php — Autenticação e sessão
[NEW] app/Middleware/AuthMiddleware.php — Proteção de rotas
[NEW] app/Middleware/CsrfMiddleware.php — Proteção CSRF
1.2 Banco de Dados
[NEW] database/install.sql — Script de instalação completo
Tabelas da Fase 1:

empresas — Dados da empresa
usuarios — Usuários do sistema
perfis — Perfis de acesso
permissoes — Permissões por módulo
usuarios_perfis — Relação usuário-perfil
logs_acesso — Log de login/logout
logs_acoes — Auditoria de ações
sessoes — Controle de sessões ativas
clientes — Cadastro de clientes
colaboradores — Cadastro de colaboradores
leads — Leads do CRM (da landing page)
configuracoes — Configurações gerais do sistema
1.3 Layout e Design
Design: Moderno, dark mode como padrão, sidebar colapsável, inspirado em ERPs como Monday.com e Odoo.

Paleta de cores:

Primária: #6C63FF (violeta moderno)
Secundária: #FF6584 (rosa/coral)
Dark background: #0F1117
Surface: #1A1D2E
Surface Light: #252840
Texto: #E8EAED
Sucesso: #00D68F
Alerta: #FFAA00
Perigo: #FF3D71
[NEW] public/assets/css/kroma.css — CSS customizado do sistema
[NEW] public/assets/js/kroma.js — JavaScript global
[NEW] app/Views/layouts/main.php — Layout principal do sistema
[NEW] app/Views/layouts/auth.php — Layout de autenticação
[NEW] app/Views/layouts/landing.php — Layout da landing page
1.4 Autenticação
[NEW] app/Controllers/AuthController.php
[NEW] app/Views/auth/login.php — Tela de login moderna
[NEW] app/Views/auth/forgot_password.php
[NEW] app/Models/UsuarioModel.php
Funcionalidades:

Login com e-mail + senha (password_hash)
Bloqueio após 5 tentativas por 15 minutos
CSRF Token em todos os formulários
Registro de IP e user-agent
Sessão segura com regeneração de ID
"Lembrar de mim" (30 dias)
1.5 Dashboard Principal
[NEW] app/Controllers/DashboardController.php
[NEW] app/Views/dashboard/index.php
KPIs visíveis no dashboard (dados mockados inicialmente, real após módulos):

Pedidos hoje / Pedidos atrasados
Faturamento do dia / mês
OS abertas / em produção
Leads novos / Taxa de conversão
Chart.js: faturamento últimos 30 dias
Chart.js: status das OSs
1.6 Gerenciamento de Usuários e Perfis
[NEW] app/Controllers/UsuarioController.php
[NEW] app/Controllers/PerfilController.php
[NEW] app/Views/usuarios/index.php
[NEW] app/Views/usuarios/form.php
[NEW] app/Views/perfis/index.php
[NEW] app/Views/perfis/permissoes.php
Perfis implementados:

Administrador, Diretor, Gerente, Comercial, Vendedor, Recepção, Designer, Produção, Estoque, Financeiro, RH, Instalador, Cliente
1.7 Cadastro de Clientes
[NEW] app/Controllers/ClienteController.php
[NEW] app/Models/ClienteModel.php
[NEW] app/Views/clientes/index.php — DataTable completo
[NEW] app/Views/clientes/form.php — Formulário completo
[NEW] app/Views/clientes/show.php — Ficha completa do cliente
Campos:

Dados pessoais/empresa, CNPJ/CPF, endereço, contatos
Tipo de cliente (Final, Revenda, Parceiro, Corporativo, Órgão público)
Preferências WhatsApp (receber mensagens, campanhas, produção, financeiro)
Histórico de pedidos, orçamentos, financeiro
1.8 CRM Básico (Kanban)
[NEW] app/Controllers/CrmController.php
[NEW] app/Models/LeadModel.php
[NEW] app/Views/crm/kanban.php — Board Kanban com drag-and-drop
[NEW] app/Views/crm/lead_form.php
Colunas do Kanban: Novo lead → Primeiro contato → Orçamento rápido → Orçamento com IA → Orçamento enviado → Negociação → Aprovado → Em produção → Entregue → Pós-venda → Recorrência → Perdido

1.9 Landing Page Pública
[NEW] app/Views/landing/index.php — Landing page completa
[NEW] app/Views/landing/orcamento.php — Formulário de orçamento público
[NEW] app/Controllers/LandingController.php
Seções:

Hero com CTA (WhatsApp + Orçamento)
Serviços (grid com ícones)
Portfólio/galeria
Simulador rápido
Upload de arquivos (até 100MB)
Formulário de contato → captura lead no CRM
Rodapé com contatos
1.10 Dados da Empresa
[NEW] app/Controllers/EmpresaController.php
[NEW] app/Views/empresa/configuracoes.php
Campos: nome, CNPJ, endereço, telefone, e-mail, site, logo, slogan, condições de orçamento, dados fiscais, certificado digital.

Fase 2 a 9 — Módulos futuros
Fase	Módulos
2	Orçamentos completos, precificação RKW, comissões básicas
3	Produtos, categorias, variações, composição, processos
4	OS, subordens, painel de produção, cronogramas
5	Estoque, compras, fornecedores, cotação
6	Financeiro, fluxo de caixa, DRE, integração Asaas
7	RH, cargos, equipamentos, veículos, imobilizado
8	BI executivo, planejamento estratégico, IA
9	Painéis de LED, contratos, playlist, agenda
Open Questions
IMPORTANT

Pergunta 1: Quer começar pela Fase 1 completa ou prefere que eu comece pela landing page pública primeiro?

IMPORTANT

Pergunta 2: O sistema rodará em localhost (XAMPP) durante desenvolvimento e depois vai para hospedagem? Se sim, qual o domínio previsto?

IMPORTANT

Pergunta 3: Já tem o banco de dados MySQL criado? Se sim, qual o nome? Se não, posso criar o script de instalação.

Plano de Execução — Fase 1
Criar banco de dados + script SQL de instalação
Estrutura de pastas MVC + config
Sistema de rotas e autenticação
Layout moderno (CSS customizado + Bootstrap 5)
Tela de login
Dashboard principal
Gerenciamento de usuários e perfis
Cadastro de clientes
CRM Kanban básico
Landing page pública
Configurações da empresa
Verificação
Testes manuais
Login com usuário master (contato@luizaugusto.me / Luiz2012@)
Criação de usuários com diferentes perfis
Bloqueio de acesso por perfil
Cadastro de cliente e lead
Kanban drag-and-drop
Upload de arquivo na landing page
Responsividade em mobile