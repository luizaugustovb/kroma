-- ============================================================
-- KROMA PRINT ERP/CRM — Script de Instalação do Banco de Dados
-- Versão: 1.0.0 | Data: 2026
-- ============================================================

CREATE DATABASE IF NOT EXISTS kroma_print
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE kroma_print;

-- ============================================================
-- TABELA: empresas
-- ============================================================
CREATE TABLE IF NOT EXISTS empresas (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    razao_social    VARCHAR(200) NOT NULL,
    nome_fantasia   VARCHAR(200),
    cnpj            VARCHAR(18),
    ie              VARCHAR(30),
    im              VARCHAR(30),
    endereco        VARCHAR(300),
    numero          VARCHAR(20),
    complemento     VARCHAR(100),
    bairro          VARCHAR(100),
    cidade          VARCHAR(100),
    estado          CHAR(2),
    cep             VARCHAR(9),
    telefone        VARCHAR(20),
    whatsapp        VARCHAR(20),
    email           VARCHAR(150),
    site            VARCHAR(200),
    logo            VARCHAR(300),
    slogan          VARCHAR(300),
    descricao       TEXT,
    cabecalho_orcamento TEXT,
    rodape_orcamento    TEXT,
    condicoes_orcamento TEXT,
    validade_orcamento  INT DEFAULT 7,
    token_whatsapp  VARCHAR(300),
    chave_openai    VARCHAR(300),
    chave_gemini    VARCHAR(300),
    chave_asaas     VARCHAR(300),
    ambiente_asaas  ENUM('sandbox','producao') DEFAULT 'sandbox',
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: perfis
-- ============================================================
CREATE TABLE IF NOT EXISTS perfis (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(50) NOT NULL UNIQUE,
    label       VARCHAR(100) NOT NULL,
    descricao   TEXT,
    nivel       TINYINT DEFAULT 5 COMMENT 'Nível hierárquico: 1=mais alto',
    ativo       TINYINT(1) DEFAULT 1,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: modulos
-- ============================================================
CREATE TABLE IF NOT EXISTS modulos (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(100) NOT NULL,
    slug        VARCHAR(100) NOT NULL UNIQUE,
    icone       VARCHAR(100),
    grupo       VARCHAR(100),
    ordem       INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: permissoes
-- ============================================================
CREATE TABLE IF NOT EXISTS permissoes (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    perfil_id   INT UNSIGNED NOT NULL,
    modulo_slug VARCHAR(100) NOT NULL,
    pode_ver    TINYINT(1) DEFAULT 0,
    pode_criar  TINYINT(1) DEFAULT 0,
    pode_editar TINYINT(1) DEFAULT 0,
    pode_excluir TINYINT(1) DEFAULT 0,
    FOREIGN KEY (perfil_id) REFERENCES perfis(id) ON DELETE CASCADE,
    UNIQUE KEY uk_perfil_modulo (perfil_id, modulo_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: usuarios
-- ============================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    perfil_id       INT UNSIGNED NOT NULL,
    nome            VARCHAR(150) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    senha           VARCHAR(255) NOT NULL,
    telefone        VARCHAR(20),
    whatsapp        VARCHAR(20),
    foto            VARCHAR(300),
    cargo           VARCHAR(100),
    setor           VARCHAR(100),
    ativo           TINYINT(1) DEFAULT 1,
    primeiro_acesso TINYINT(1) DEFAULT 1,
    ultimo_acesso   DATETIME,
    ip_ultimo       VARCHAR(45),
    token_reset     VARCHAR(100),
    token_expira    DATETIME,
    observacoes     TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (perfil_id) REFERENCES perfis(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: login_tentativas
-- ============================================================
CREATE TABLE IF NOT EXISTS login_tentativas (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(150) NOT NULL UNIQUE,
    tentativas      INT DEFAULT 0,
    ultima_tentativa DATETIME,
    bloqueado_ate   DATETIME,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: logs_acesso
-- ============================================================
CREATE TABLE IF NOT EXISTS logs_acesso (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT UNSIGNED,
    acao        ENUM('login','logout','falha') DEFAULT 'login',
    ip          VARCHAR(45),
    user_agent  VARCHAR(500),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: logs_acoes (auditoria)
-- ============================================================
CREATE TABLE IF NOT EXISTS logs_acoes (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id   INT UNSIGNED,
    tabela       VARCHAR(100),
    acao         ENUM('criar','editar','excluir','visualizar') DEFAULT 'criar',
    registro_id  INT UNSIGNED,
    dados_antigos JSON,
    dados_novos  JSON,
    ip           VARCHAR(45),
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id),
    INDEX idx_tabela (tabela),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: clientes
-- ============================================================
CREATE TABLE IF NOT EXISTS clientes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo_pessoa     ENUM('fisica','juridica') DEFAULT 'juridica',
    tipo_cliente    ENUM('cliente_final','revenda','parceiro','corporativo','orgao_publico') DEFAULT 'cliente_final',
    nome            VARCHAR(200) NOT NULL,
    nome_fantasia   VARCHAR(200),
    cpf_cnpj        VARCHAR(18),
    rg_ie           VARCHAR(30),
    email           VARCHAR(150),
    telefone        VARCHAR(20),
    whatsapp        VARCHAR(20),
    celular         VARCHAR(20),
    endereco        VARCHAR(300),
    numero          VARCHAR(20),
    complemento     VARCHAR(100),
    bairro          VARCHAR(100),
    cidade          VARCHAR(100),
    estado          CHAR(2),
    cep             VARCHAR(9),
    origem_lead     VARCHAR(100),
    vendedor_id     INT UNSIGNED,
    classificacao   ENUM('bronze','prata','ouro','diamante') DEFAULT 'bronze',
    status          ENUM('ativo','inativo','bloqueado') DEFAULT 'ativo',
    recebe_whatsapp TINYINT(1) DEFAULT 1,
    recebe_campanha TINYINT(1) DEFAULT 1,
    recebe_producao TINYINT(1) DEFAULT 1,
    recebe_financeiro TINYINT(1) DEFAULT 1,
    limite_credito  DECIMAL(10,2) DEFAULT 0,
    observacoes     TEXT,
    observacoes_internas TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendedor_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_nome (nome),
    INDEX idx_cpf_cnpj (cpf_cnpj),
    INDEX idx_tipo (tipo_cliente),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: contatos_clientes
-- ============================================================
CREATE TABLE IF NOT EXISTS contatos_clientes (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id  INT UNSIGNED NOT NULL,
    nome        VARCHAR(150) NOT NULL,
    cargo       VARCHAR(100),
    email       VARCHAR(150),
    telefone    VARCHAR(20),
    whatsapp    VARCHAR(20),
    principal   TINYINT(1) DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: leads (CRM)
-- ============================================================
CREATE TABLE IF NOT EXISTS leads (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id      INT UNSIGNED,
    vendedor_id     INT UNSIGNED,
    nome            VARCHAR(200) NOT NULL,
    email           VARCHAR(150),
    telefone        VARCHAR(20),
    whatsapp        VARCHAR(20),
    empresa         VARCHAR(200),
    produto_interesse VARCHAR(300),
    descricao       TEXT,
    origem          ENUM('landing_page','whatsapp','indicacao','visita','ligacao','email','instagram','facebook','google','outro') DEFAULT 'outro',
    estagio         ENUM('novo_lead','primeiro_contato','orcamento_rapido','orcamento_ia','orcamento_enviado','negociacao','aprovado','em_producao','entregue','pos_venda','recorrencia','perdido') DEFAULT 'novo_lead',
    motivo_perda    VARCHAR(300),
    valor_estimado  DECIMAL(10,2),
    probabilidade   TINYINT DEFAULT 50,
    data_follow_up  DATE,
    prioridade      ENUM('baixa','media','alta','urgente') DEFAULT 'media',
    temperatura     ENUM('frio','morno','quente') DEFAULT 'morno',
    observacoes     TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (vendedor_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_estagio (estagio),
    INDEX idx_vendedor (vendedor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: historico_leads
-- ============================================================
CREATE TABLE IF NOT EXISTS historico_leads (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id     INT UNSIGNED NOT NULL,
    usuario_id  INT UNSIGNED,
    tipo        ENUM('anotacao','ligacao','email','whatsapp','visita','estagio','sistema') DEFAULT 'anotacao',
    descricao   TEXT NOT NULL,
    estagio_anterior VARCHAR(50),
    estagio_novo     VARCHAR(50),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: colaboradores
-- ============================================================
CREATE TABLE IF NOT EXISTS colaboradores (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT UNSIGNED,
    nome            VARCHAR(150) NOT NULL,
    cpf             VARCHAR(14),
    rg              VARCHAR(20),
    data_nascimento DATE,
    sexo            ENUM('M','F','O'),
    email           VARCHAR(150),
    telefone        VARCHAR(20),
    whatsapp        VARCHAR(20),
    cargo           VARCHAR(100),
    setor           VARCHAR(100),
    salario         DECIMAL(10,2),
    data_admissao   DATE,
    data_demissao   DATE,
    tipo_contrato   ENUM('clt','pj','autonomo','estagio') DEFAULT 'clt',
    banco           VARCHAR(100),
    agencia         VARCHAR(20),
    conta           VARCHAR(30),
    tipo_conta      ENUM('corrente','poupanca') DEFAULT 'corrente',
    pix             VARCHAR(150),
    status          ENUM('ativo','ferias','afastado','demitido') DEFAULT 'ativo',
    foto            VARCHAR(300),
    observacoes     TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: configuracoes
-- ============================================================
CREATE TABLE IF NOT EXISTS configuracoes (
    id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chave   VARCHAR(100) NOT NULL UNIQUE,
    valor   TEXT,
    tipo    ENUM('texto','numero','booleano','json') DEFAULT 'texto',
    grupo   VARCHAR(100),
    label   VARCHAR(200),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: notificacoes
-- ============================================================
CREATE TABLE IF NOT EXISTS notificacoes (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT UNSIGNED NOT NULL,
    tipo        VARCHAR(50),
    titulo      VARCHAR(200) NOT NULL,
    mensagem    TEXT,
    link        VARCHAR(300),
    icone       VARCHAR(100),
    lida        TINYINT(1) DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_lida (usuario_id, lida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: orcamentos
-- ============================================================
CREATE TABLE IF NOT EXISTS orcamentos (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo              VARCHAR(30) NOT NULL UNIQUE,
    cliente_id          INT UNSIGNED,
    lead_id             INT UNSIGNED,
    vendedor_id         INT UNSIGNED,
    tipo                ENUM('rapido','completo','ia','produto','item','setor','revenda','cliente_final') DEFAULT 'rapido',
    status              ENUM('rascunho','em_calculo','enviado','aprovado','recusado','cancelado','expirado') DEFAULT 'rascunho',
    titulo              VARCHAR(200) NOT NULL,
    descricao           TEXT,
    validade            DATE,
    condicao_pagamento  VARCHAR(200),
    prazo_entrega       VARCHAR(120),
    observacoes         TEXT,
    subtotal_custo      DECIMAL(12,2) DEFAULT 0,
    subtotal_venda      DECIMAL(12,2) DEFAULT 0,
    desperdicio_percent DECIMAL(6,2) DEFAULT 0,
    impostos_percent    DECIMAL(6,2) DEFAULT 0,
    comissao_percent    DECIMAL(6,2) DEFAULT 0,
    margem_percent      DECIMAL(6,2) DEFAULT 0,
    desconto_percent    DECIMAL(6,2) DEFAULT 0,
    desconto_valor      DECIMAL(12,2) DEFAULT 0,
    preco_minimo        DECIMAL(12,2) DEFAULT 0,
    lucro_previsto      DECIMAL(12,2) DEFAULT 0,
    total               DECIMAL(12,2) DEFAULT 0,
    aprovado_at         DATETIME,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    FOREIGN KEY (vendedor_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo),
    INDEX idx_status (status),
    INDEX idx_cliente (cliente_id),
    INDEX idx_vendedor (vendedor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: orcamento_itens
-- ============================================================
CREATE TABLE IF NOT EXISTS orcamento_itens (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    orcamento_id        INT UNSIGNED NOT NULL,
    produto_nome        VARCHAR(200) NOT NULL,
    descricao           TEXT,
    quantidade          DECIMAL(12,3) DEFAULT 1,
    unidade             VARCHAR(20) DEFAULT 'un',
    largura             DECIMAL(10,3) DEFAULT 0,
    altura              DECIMAL(10,3) DEFAULT 0,
    area_m2             DECIMAL(12,3) DEFAULT 0,
    custo_material      DECIMAL(12,2) DEFAULT 0,
    custo_tinta         DECIMAL(12,2) DEFAULT 0,
    custo_acabamento    DECIMAL(12,2) DEFAULT 0,
    custo_mao_obra      DECIMAL(12,2) DEFAULT 0,
    custo_maquina       DECIMAL(12,2) DEFAULT 0,
    custo_terceiros     DECIMAL(12,2) DEFAULT 0,
    desperdicio_percent DECIMAL(6,2) DEFAULT 0,
    margem_percent      DECIMAL(6,2) DEFAULT 0,
    impostos_percent    DECIMAL(6,2) DEFAULT 0,
    comissao_percent    DECIMAL(6,2) DEFAULT 0,
    desconto_percent    DECIMAL(6,2) DEFAULT 0,
    custo_total         DECIMAL(12,2) DEFAULT 0,
    preco_unitario      DECIMAL(12,2) DEFAULT 0,
    total               DECIMAL(12,2) DEFAULT 0,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE CASCADE,
    INDEX idx_orcamento (orcamento_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: comissoes
-- ============================================================
CREATE TABLE IF NOT EXISTS comissoes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    orcamento_id    INT UNSIGNED NOT NULL,
    usuario_id      INT UNSIGNED,
    base_calculo    DECIMAL(12,2) DEFAULT 0,
    percentual      DECIMAL(6,2) DEFAULT 0,
    valor           DECIMAL(12,2) DEFAULT 0,
    status          ENUM('prevista','liberada','paga','cancelada') DEFAULT 'prevista',
    observacoes     TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: categorias_produtos
-- ============================================================
CREATE TABLE IF NOT EXISTS categorias_produtos (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(120) NOT NULL,
    slug        VARCHAR(140) NOT NULL UNIQUE,
    descricao   TEXT,
    ativo       TINYINT(1) DEFAULT 1,
    ordem       INT DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: processos_produtivos
-- ============================================================
CREATE TABLE IF NOT EXISTS processos_produtivos (
    id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome                  VARCHAR(150) NOT NULL,
    setor                 VARCHAR(100),
    maquina               VARCHAR(120),
    operador_padrao       VARCHAR(120),
    tempo_previsto_min    INT DEFAULT 0,
    custo_hora            DECIMAL(12,2) DEFAULT 0,
    desperdicio_percent   DECIMAL(6,2) DEFAULT 0,
    checklist             TEXT,
    pop                   TEXT,
    ativo                 TINYINT(1) DEFAULT 1,
    created_at            DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at            DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ativo (ativo),
    INDEX idx_setor (setor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: acabamentos
-- ============================================================
CREATE TABLE IF NOT EXISTS acabamentos (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(150) NOT NULL,
    descricao   TEXT,
    custo_base  DECIMAL(12,2) DEFAULT 0,
    ativo       TINYINT(1) DEFAULT 1,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: produtos
-- ============================================================
CREATE TABLE IF NOT EXISTS produtos (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_id            INT UNSIGNED,
    codigo                  VARCHAR(40) UNIQUE,
    nome                    VARCHAR(200) NOT NULL,
    tipo                    ENUM('produto','servico','composto','locacao') DEFAULT 'produto',
    unidade                 VARCHAR(20) DEFAULT 'un',
    descricao               TEXT,
    descricao_ia            TEXT,
    questionario            TEXT,
    campos_obrigatorios     TEXT,
    largura_padrao          DECIMAL(10,3) DEFAULT 0,
    altura_padrao           DECIMAL(10,3) DEFAULT 0,
    custo_material          DECIMAL(12,2) DEFAULT 0,
    custo_tinta             DECIMAL(12,2) DEFAULT 0,
    custo_acabamento        DECIMAL(12,2) DEFAULT 0,
    custo_mao_obra          DECIMAL(12,2) DEFAULT 0,
    custo_maquina           DECIMAL(12,2) DEFAULT 0,
    custo_terceiros         DECIMAL(12,2) DEFAULT 0,
    desperdicio_percent     DECIMAL(6,2) DEFAULT 5,
    margem_percent          DECIMAL(6,2) DEFAULT 35,
    impostos_percent        DECIMAL(6,2) DEFAULT 8,
    comissao_percent        DECIMAL(6,2) DEFAULT 5,
    preco_minimo            DECIMAL(12,2) DEFAULT 0,
    preco_base              DECIMAL(12,2) DEFAULT 0,
    prioridade_8020         TINYINT(1) DEFAULT 0,
    perecivel               TINYINT(1) DEFAULT 0,
    validade_dias           INT DEFAULT 0,
    status                  ENUM('ativo','inativo','em_revisao') DEFAULT 'ativo',
    created_at              DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at              DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias_produtos(id) ON DELETE SET NULL,
    INDEX idx_nome (nome),
    INDEX idx_codigo (codigo),
    INDEX idx_status (status),
    INDEX idx_categoria (categoria_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: produto_variacoes
-- ============================================================
CREATE TABLE IF NOT EXISTS produto_variacoes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id      INT UNSIGNED NOT NULL,
    nome            VARCHAR(150) NOT NULL,
    sku             VARCHAR(80),
    unidade         VARCHAR(20),
    largura         DECIMAL(10,3) DEFAULT 0,
    altura          DECIMAL(10,3) DEFAULT 0,
    custo_extra     DECIMAL(12,2) DEFAULT 0,
    preco_extra     DECIMAL(12,2) DEFAULT 0,
    ativo           TINYINT(1) DEFAULT 1,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    INDEX idx_produto (produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: produto_processos
-- ============================================================
CREATE TABLE IF NOT EXISTS produto_processos (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id  INT UNSIGNED NOT NULL,
    processo_id INT UNSIGNED NOT NULL,
    ordem       INT DEFAULT 0,
    tempo_min   INT DEFAULT 0,
    observacao  VARCHAR(300),
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (processo_id) REFERENCES processos_produtivos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_produto_processo (produto_id, processo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: produto_acabamentos
-- ============================================================
CREATE TABLE IF NOT EXISTS produto_acabamentos (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id     INT UNSIGNED NOT NULL,
    acabamento_id  INT UNSIGNED NOT NULL,
    obrigatorio    TINYINT(1) DEFAULT 0,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (acabamento_id) REFERENCES acabamentos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_produto_acabamento (produto_id, acabamento_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEEDS: Dados iniciais
-- ============================================================

-- Perfis de acesso
INSERT INTO perfis (nome, label, descricao, nivel) VALUES
('administrador', 'Administrador', 'Acesso total ao sistema', 1),
('diretor', 'Diretor', 'Acesso gerencial completo', 2),
('gerente', 'Gerente', 'Gestão de equipes e relatórios', 3),
('comercial', 'Comercial', 'Gestão da área comercial', 4),
('vendedor', 'Vendedor', 'Atendimento e orçamentos', 5),
('recepcao', 'Recepção', 'Atendimento inicial e cadastros', 6),
('designer', 'Designer', 'Criação de artes e aprovações', 6),
('producao', 'Produção', 'Ordens de serviço e produção', 6),
('estoque', 'Estoque', 'Controle de materiais e estoque', 6),
('financeiro', 'Financeiro', 'Contas, faturamento e cobranças', 5),
('rh', 'RH / Dep. Pessoal', 'Gestão de colaboradores', 5),
('instalador', 'Instalador', 'Instalações externas e agenda', 7),
('cliente', 'Cliente', 'Portal do cliente', 9);

-- Módulos do sistema
INSERT INTO modulos (nome, slug, icone, grupo, ordem) VALUES
('Dashboard', 'dashboard', 'bi-speedometer2', 'Principal', 1),
('CRM / Kanban', 'crm', 'bi-kanban', 'Comercial', 10),
('Clientes', 'clientes', 'bi-people', 'Comercial', 11),
('Orçamentos', 'orcamentos', 'bi-file-earmark-text', 'Comercial', 12),
('Usuários', 'usuarios', 'bi-person-gear', 'Administrativo', 20),
('Perfis e Permissões', 'perfis', 'bi-shield-check', 'Administrativo', 21),
('Empresa', 'empresa', 'bi-building', 'Administrativo', 22),
('Produtos', 'produtos', 'bi-box', 'Operacional', 30),
('OS / Produção', 'producao', 'bi-gear', 'Operacional', 31),
('Estoque', 'estoque', 'bi-archive', 'Operacional', 32),
('Compras', 'compras', 'bi-cart', 'Operacional', 33),
('Financeiro', 'financeiro', 'bi-cash-stack', 'Financeiro', 40),
('Comissões', 'comissoes', 'bi-percent', 'Financeiro', 41),
('Colaboradores', 'colaboradores', 'bi-person-badge', 'RH', 50),
('Equipamentos', 'equipamentos', 'bi-tools', 'RH', 51),
('BI Executivo', 'bi', 'bi-bar-chart-line', 'Inteligência', 60),
('POPs e Qualidade', 'pops', 'bi-clipboard-check', 'Qualidade', 70),
('Painéis de LED', 'led', 'bi-display', 'LED', 80),
('Chat Interno', 'chat', 'bi-chat-dots', 'Comunicação', 90),
('WhatsApp', 'whatsapp', 'bi-whatsapp', 'Comunicação', 91);

-- Usuário master (Administrador)
-- Permissões padrão
INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug, 1, 1, 1, 1
FROM perfis p
JOIN modulos m
WHERE p.nome = 'administrador'
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug, 1, 1, 1, 0
FROM perfis p
JOIN modulos m ON m.slug IN ('dashboard','crm','clientes','orcamentos')
WHERE p.nome IN ('diretor','gerente','comercial','vendedor','recepcao')
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO usuarios (perfil_id, nome, email, senha, cargo, ativo, primeiro_acesso) VALUES
(1, 'Administrador Master', 'contato@luizaugusto.me', '$2y$12$IvKtOxZGLUPbhmufrx749.bnmOgeCg565.5aoX/Lx1Ob1jV5.2D2O', 'Administrador', 1, 0);
-- Senha: Luiz2012@

-- Configurações padrão
INSERT INTO configuracoes (chave, valor, tipo, grupo, label) VALUES
('tema_padrao', 'dark', 'texto', 'interface', 'Tema padrão'),
('moeda', 'BRL', 'texto', 'financeiro', 'Moeda'),
('simbolo_moeda', 'R$', 'texto', 'financeiro', 'Símbolo da moeda'),
('fuso_horario', 'America/Sao_Paulo', 'texto', 'sistema', 'Fuso horário'),
('itens_por_pagina', '25', 'numero', 'interface', 'Itens por página'),
('prazo_orcamento', '7', 'numero', 'comercial', 'Validade padrão do orçamento (dias)'),
('comissao_padrao', '5', 'numero', 'comercial', 'Comissão padrão do vendedor (%)'),
('margem_minima', '30', 'numero', 'comercial', 'Margem mínima de lucro (%)'),
('estoque_critico', '10', 'numero', 'estoque', 'Percentual crítico de estoque (%)');

-- Empresa padrão
INSERT INTO empresas (razao_social, nome_fantasia, email) VALUES
('KROMA PRINT LTDA', 'KROMA PRINT', 'contato@kromaprint.com.br');
