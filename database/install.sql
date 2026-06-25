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
    endpoint_whatsapp VARCHAR(300),
    modo_whatsapp   ENUM('simulado','producao') DEFAULT 'simulado',
    chave_openai    VARCHAR(300),
    chave_gemini    VARCHAR(300),
    modo_ia         ENUM('simulado','producao') DEFAULT 'simulado',
    provedor_ia     ENUM('openai','gemini') DEFAULT 'openai',
    modelo_ia       VARCHAR(100) DEFAULT 'gpt-5.5',
    prompt_padrao_ia TEXT,
    limite_ia_diario INT DEFAULT 100,
    chave_asaas     VARCHAR(300),
    ambiente_asaas  ENUM('sandbox','producao') DEFAULT 'sandbox',
    webhook_viicio_token VARCHAR(160),
    webhook_asaas_token  VARCHAR(160),
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
    cliente_id      INT UNSIGNED,
    ativo           TINYINT(1) DEFAULT 1,
    primeiro_acesso TINYINT(1) DEFAULT 1,
    ultimo_acesso   DATETIME,
    ip_ultimo       VARCHAR(45),
    token_reset     VARCHAR(100),
    token_expira    DATETIME,
    observacoes     TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (perfil_id) REFERENCES perfis(id),
    INDEX idx_cliente (cliente_id)
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
    acao         VARCHAR(80) DEFAULT 'criar',
    registro_id  INT UNSIGNED,
    dados_antigos JSON,
    dados_novos  JSON,
    ip           VARCHAR(45),
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id),
    INDEX idx_tabela (tabela),
    INDEX idx_acao (acao),
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
-- TABELAS: site publico / landing page
-- ============================================================
CREATE TABLE IF NOT EXISTS site_configuracoes (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    google_analytics_id VARCHAR(80),
    seo_titulo          VARCHAR(180),
    seo_descricao       VARCHAR(320),
    seo_keywords        TEXT,
    canonical_url       VARCHAR(300),
    hero_badge          VARCHAR(120),
    hero_titulo         VARCHAR(220),
    hero_subtitulo      TEXT,
    hero_cta_texto      VARCHAR(80),
    hero_cta_secundario VARCHAR(80),
    hero_image_url      VARCHAR(500),
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_servicos (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo      VARCHAR(150) NOT NULL,
    descricao   TEXT,
    icone       VARCHAR(80) DEFAULT 'bi-stars',
    ordem       INT DEFAULT 0,
    ativo       TINYINT(1) DEFAULT 1,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ativo_ordem (ativo, ordem)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_portfolio (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo      VARCHAR(150) NOT NULL,
    categoria   VARCHAR(100),
    descricao   TEXT,
    imagem_url  VARCHAR(500),
    ordem       INT DEFAULT 0,
    ativo       TINYINT(1) DEFAULT 1,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ativo_ordem (ativo, ordem)
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
    custo_hora      DECIMAL(12,2) DEFAULT 0,
    jornada_mensal  INT DEFAULT 220,
    habilidades     TEXT,
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
-- TABELA: rh_setores
-- ============================================================
CREATE TABLE IF NOT EXISTS rh_setores (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(120) NOT NULL,
    slug            VARCHAR(140) UNIQUE,
    descricao       TEXT,
    responsavel_id  INT UNSIGNED,
    status          ENUM('ativo','inativo') DEFAULT 'ativo',
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (responsavel_id) REFERENCES colaboradores(id) ON DELETE SET NULL,
    INDEX idx_nome (nome),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: rh_cargos
-- ============================================================
CREATE TABLE IF NOT EXISTS rh_cargos (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setor_id            INT UNSIGNED,
    nome                VARCHAR(120) NOT NULL,
    descricao           TEXT,
    salario_base        DECIMAL(12,2) DEFAULT 0,
    custo_hora_padrao   DECIMAL(12,2) DEFAULT 0,
    status              ENUM('ativo','inativo') DEFAULT 'ativo',
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (setor_id) REFERENCES rh_setores(id) ON DELETE SET NULL,
    INDEX idx_nome (nome),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: equipamentos
-- ============================================================
CREATE TABLE IF NOT EXISTS equipamentos (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo                  VARCHAR(40) UNIQUE,
    nome                    VARCHAR(160) NOT NULL,
    tipo                    ENUM('maquina','ferramenta','computador','impressora','acabamento','instalacao','outro') DEFAULT 'maquina',
    setor_id                INT UNSIGNED,
    responsavel_id          INT UNSIGNED,
    marca                   VARCHAR(100),
    modelo                  VARCHAR(120),
    patrimonio              VARCHAR(80),
    status                  ENUM('ativo','manutencao','inativo','baixado') DEFAULT 'ativo',
    custo_hora              DECIMAL(12,2) DEFAULT 0,
    data_aquisicao          DATE,
    valor_aquisicao         DECIMAL(12,2) DEFAULT 0,
    manutencao_prevista     DATE,
    observacoes             TEXT,
    created_at              DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at              DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (setor_id) REFERENCES rh_setores(id) ON DELETE SET NULL,
    FOREIGN KEY (responsavel_id) REFERENCES colaboradores(id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo),
    INDEX idx_status (status),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: veiculos
-- ============================================================
CREATE TABLE IF NOT EXISTS veiculos (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo                  VARCHAR(40) UNIQUE,
    nome                    VARCHAR(160) NOT NULL,
    tipo                    ENUM('carro','moto','van','caminhao','outro') DEFAULT 'carro',
    placa                   VARCHAR(20),
    responsavel_id          INT UNSIGNED,
    status                  ENUM('ativo','manutencao','inativo','baixado') DEFAULT 'ativo',
    custo_km                DECIMAL(12,2) DEFAULT 0,
    custo_hora              DECIMAL(12,2) DEFAULT 0,
    km_atual                DECIMAL(12,1) DEFAULT 0,
    manutencao_prevista     DATE,
    observacoes             TEXT,
    created_at              DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at              DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (responsavel_id) REFERENCES colaboradores(id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo),
    INDEX idx_placa (placa),
    INDEX idx_status (status)
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
    tipo_preco          ENUM('cliente_final','revenda','terceirizado') DEFAULT 'cliente_final',
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
    arquivo_projeto     VARCHAR(300),
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
    produto_id          INT UNSIGNED,
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
    INDEX idx_orcamento (orcamento_id),
    INDEX idx_produto (produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: orcamento_item_materiais
-- ============================================================
CREATE TABLE IF NOT EXISTS orcamento_item_materiais (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    orcamento_item_id   INT UNSIGNED NOT NULL,
    material_id         INT UNSIGNED NOT NULL,
    quantidade          DECIMAL(12,3) DEFAULT 0,
    unidade             VARCHAR(20) DEFAULT 'un',
    custo_unitario      DECIMAL(12,2) DEFAULT 0,
    custo_total         DECIMAL(12,2) DEFAULT 0,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (orcamento_item_id) REFERENCES orcamento_itens(id) ON DELETE CASCADE,
    INDEX idx_item (orcamento_item_id),
    INDEX idx_material (material_id)
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
    status          ENUM('prevista','liberada','paga','bloqueada','cancelada') DEFAULT 'prevista',
    data_liberacao  DATETIME,
    data_pagamento  DATETIME,
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
-- TABELA: qualidade_pops
-- ============================================================
CREATE TABLE IF NOT EXISTS qualidade_pops (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo              VARCHAR(40) NOT NULL UNIQUE,
    titulo              VARCHAR(200) NOT NULL,
    setor               VARCHAR(100),
    categoria           VARCHAR(120),
    processo_id         INT UNSIGNED,
    versao              INT DEFAULT 1,
    status              ENUM('rascunho','em_revisao','aprovado','obsoleto') DEFAULT 'rascunho',
    objetivo            TEXT,
    procedimento        TEXT,
    checklist           TEXT,
    anexo_url           VARCHAR(300),
    responsavel_id      INT UNSIGNED,
    aprovador_id        INT UNSIGNED,
    aprovado_at         DATETIME,
    vigencia_inicio     DATE,
    revisao_prevista    DATE,
    observacoes         TEXT,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (processo_id) REFERENCES processos_produtivos(id) ON DELETE SET NULL,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (aprovador_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo),
    INDEX idx_status (status),
    INDEX idx_setor (setor),
    INDEX idx_revisao (revisao_prevista)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: qualidade_pop_revisoes
-- ============================================================
CREATE TABLE IF NOT EXISTS qualidade_pop_revisoes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pop_id          INT UNSIGNED NOT NULL,
    versao          INT NOT NULL,
    status          VARCHAR(40),
    resumo          VARCHAR(255),
    procedimento    TEXT,
    checklist       TEXT,
    usuario_id      INT UNSIGNED,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pop_id) REFERENCES qualidade_pops(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_pop (pop_id),
    INDEX idx_versao (versao)
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
    fornecedor_id           INT UNSIGNED DEFAULT NULL,
    created_at              DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at              DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias_produtos(id) ON DELETE SET NULL,
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE SET NULL,
    INDEX idx_nome (nome),
    INDEX idx_codigo (codigo),
    INDEX idx_status (status),
    INDEX idx_categoria (categoria_id),
    INDEX idx_fornecedor (fornecedor_id)
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
-- TABELA: ordem_servicos
-- ============================================================
CREATE TABLE IF NOT EXISTS ordem_servicos (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo              VARCHAR(40) NOT NULL UNIQUE,
    orcamento_id        INT UNSIGNED,
    cliente_id          INT UNSIGNED,
    responsavel_id      INT UNSIGNED,
    titulo              VARCHAR(200) NOT NULL,
    descricao           TEXT,
    prioridade          ENUM('baixa','media','alta','urgente') DEFAULT 'media',
    status              ENUM('aberta','em_producao','aguardando','finalizada','cancelada') DEFAULT 'aberta',
    data_entrada        DATE,
    data_prometida      DATE,
    data_inicio         DATETIME,
    data_finalizacao    DATETIME,
    observacoes         TEXT,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE SET NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo),
    INDEX idx_status (status),
    INDEX idx_cliente (cliente_id),
    INDEX idx_prometida (data_prometida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: ordem_servico_itens
-- ============================================================
CREATE TABLE IF NOT EXISTS ordem_servico_itens (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ordem_servico_id    INT UNSIGNED NOT NULL,
    produto_id          INT UNSIGNED,
    orcamento_item_id   INT UNSIGNED,
    produto_nome        VARCHAR(200) NOT NULL,
    descricao           TEXT,
    quantidade          DECIMAL(12,3) DEFAULT 1,
    unidade             VARCHAR(20) DEFAULT 'un',
    largura             DECIMAL(10,3) DEFAULT 0,
    altura              DECIMAL(10,3) DEFAULT 0,
    area_m2             DECIMAL(12,3) DEFAULT 0,
    material            VARCHAR(200),
    acabamento          VARCHAR(200),
    arquivo_ref         VARCHAR(300),
    status              ENUM('pendente','em_producao','concluido','cancelado') DEFAULT 'pendente',
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ordem_servico_id) REFERENCES ordem_servicos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL,
    FOREIGN KEY (orcamento_item_id) REFERENCES orcamento_itens(id) ON DELETE SET NULL,
    INDEX idx_os (ordem_servico_id),
    INDEX idx_produto (produto_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: ordem_servico_etapas
-- ============================================================
CREATE TABLE IF NOT EXISTS ordem_servico_etapas (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ordem_servico_id    INT UNSIGNED NOT NULL,
    item_id             INT UNSIGNED,
    processo_id         INT UNSIGNED,
    responsavel_id      INT UNSIGNED,
    nome                VARCHAR(150) NOT NULL,
    setor               VARCHAR(100),
    ordem               INT DEFAULT 0,
    status              ENUM('pendente','em_producao','pausada','concluida','cancelada') DEFAULT 'pendente',
    prazo               DATETIME,
    data_inicio         DATETIME,
    data_fim            DATETIME,
    observacao          TEXT,
    checklist           TEXT,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ordem_servico_id) REFERENCES ordem_servicos(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES ordem_servico_itens(id) ON DELETE CASCADE,
    FOREIGN KEY (processo_id) REFERENCES processos_produtivos(id) ON DELETE SET NULL,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_os (ordem_servico_id),
    INDEX idx_status (status),
    INDEX idx_setor (setor),
    INDEX idx_prazo (prazo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: agenda_instalacoes
-- ============================================================
CREATE TABLE IF NOT EXISTS agenda_instalacoes (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo              VARCHAR(40) NOT NULL UNIQUE,
    cliente_id          INT UNSIGNED,
    orcamento_id        INT UNSIGNED,
    ordem_servico_id    INT UNSIGNED,
    responsavel_id      INT UNSIGNED,
    titulo              VARCHAR(180) NOT NULL,
    equipe              VARCHAR(180),
    endereco            VARCHAR(250),
    cidade              VARCHAR(100),
    estado              CHAR(2),
    data_inicio         DATETIME NOT NULL,
    data_fim            DATETIME,
    prioridade          ENUM('baixa','media','alta','urgente') DEFAULT 'media',
    status              ENUM('agendada','em_rota','em_execucao','concluida','cancelada') DEFAULT 'agendada',
    checklist           TEXT,
    observacoes         TEXT,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE SET NULL,
    FOREIGN KEY (ordem_servico_id) REFERENCES ordem_servicos(id) ON DELETE SET NULL,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_inicio (data_inicio),
    INDEX idx_responsavel (responsavel_id),
    INDEX idx_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: led_paineis
-- ============================================================
CREATE TABLE IF NOT EXISTS led_paineis (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo              VARCHAR(40) NOT NULL UNIQUE,
    nome                VARCHAR(180) NOT NULL,
    tamanho             VARCHAR(80),
    resolucao           VARCHAR(80),
    localizacao         VARCHAR(180),
    largura_m           DECIMAL(10,2) DEFAULT 0,
    altura_m            DECIMAL(10,2) DEFAULT 0,
    area_m2             DECIMAL(10,2) DEFAULT 0,
    valor_diaria        DECIMAL(12,2) DEFAULT 0,
    status              ENUM('disponivel','reservado','instalado','manutencao','retirado','cancelado') DEFAULT 'disponivel',
    observacoes         TEXT,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo),
    INDEX idx_status (status),
    INDEX idx_localizacao (localizacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: led_locacoes
-- ============================================================
CREATE TABLE IF NOT EXISTS led_locacoes (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo              VARCHAR(40) NOT NULL UNIQUE,
    painel_id           INT UNSIGNED NOT NULL,
    cliente_id          INT UNSIGNED,
    agenda_id           INT UNSIGNED,
    responsavel_id      INT UNSIGNED,
    titulo              VARCHAR(180) NOT NULL,
    contrato            VARCHAR(120),
    local_instalacao    VARCHAR(250),
    data_inicio         DATETIME NOT NULL,
    data_fim            DATETIME NOT NULL,
    valor_total         DECIMAL(12,2) DEFAULT 0,
    playlist            TEXT,
    arquivos            TEXT,
    fotos               TEXT,
    comprovantes        TEXT,
    status              ENUM('reservado','instalado','manutencao','retirado','cancelado') DEFAULT 'reservado',
    observacoes         TEXT,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (painel_id) REFERENCES led_paineis(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (agenda_id) REFERENCES agenda_instalacoes(id) ON DELETE SET NULL,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_painel (painel_id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_agenda (agenda_id),
    INDEX idx_status (status),
    INDEX idx_periodo (data_inicio, data_fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: materiais
-- ============================================================
CREATE TABLE IF NOT EXISTS materiais (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo              VARCHAR(40) UNIQUE,
    nome                VARCHAR(180) NOT NULL,
    categoria           VARCHAR(120),
    unidade             VARCHAR(20) DEFAULT 'un',
    fornecedor          VARCHAR(180),
    custo_atual         DECIMAL(12,2) DEFAULT 0,
    estoque_atual       DECIMAL(12,3) DEFAULT 0,
    estoque_minimo      DECIMAL(12,3) DEFAULT 0,
    estoque_reservado   DECIMAL(12,3) DEFAULT 0,
    localizacao         VARCHAR(120),
    status              ENUM('ativo','inativo') DEFAULT 'ativo',
    observacoes         TEXT,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo),
    INDEX idx_nome (nome),
    INDEX idx_categoria (categoria),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: estoque_movimentacoes
-- ============================================================
CREATE TABLE IF NOT EXISTS estoque_movimentacoes (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    material_id         INT UNSIGNED NOT NULL,
    ordem_servico_id    INT UNSIGNED,
    usuario_id          INT UNSIGNED,
    tipo                ENUM('entrada','saida','ajuste','reserva','baixa_reserva','cancelamento_reserva') NOT NULL,
    origem              VARCHAR(120),
    quantidade          DECIMAL(12,3) NOT NULL,
    custo_unitario      DECIMAL(12,2) DEFAULT 0,
    saldo_anterior      DECIMAL(12,3) DEFAULT 0,
    saldo_posterior     DECIMAL(12,3) DEFAULT 0,
    reservado_anterior  DECIMAL(12,3) DEFAULT 0,
    reservado_posterior DECIMAL(12,3) DEFAULT 0,
    observacao          TEXT,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES materiais(id) ON DELETE CASCADE,
    FOREIGN KEY (ordem_servico_id) REFERENCES ordem_servicos(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_material (material_id),
    INDEX idx_os (ordem_servico_id),
    INDEX idx_tipo (tipo),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: contas_receber
-- ============================================================
CREATE TABLE IF NOT EXISTS contas_receber (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo              VARCHAR(40) NOT NULL UNIQUE,
    cliente_id          INT UNSIGNED,
    orcamento_id        INT UNSIGNED,
    ordem_servico_id    INT UNSIGNED,
    descricao           VARCHAR(220) NOT NULL,
    origem              ENUM('manual','orcamento','ordem_servico') DEFAULT 'manual',
    valor               DECIMAL(12,2) DEFAULT 0,
    valor_pago          DECIMAL(12,2) DEFAULT 0,
    vencimento          DATE,
    data_pagamento      DATE,
    forma_pagamento     VARCHAR(80),
    status              ENUM('aberto','parcial','pago','cancelado') DEFAULT 'aberto',
    observacoes         TEXT,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE SET NULL,
    FOREIGN KEY (ordem_servico_id) REFERENCES ordem_servicos(id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo),
    INDEX idx_status (status),
    INDEX idx_vencimento (vencimento),
    INDEX idx_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: contas_pagar
-- ============================================================
CREATE TABLE IF NOT EXISTS contas_pagar (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo              VARCHAR(40) NOT NULL UNIQUE,
    fornecedor          VARCHAR(180),
    categoria           VARCHAR(120),
    descricao           VARCHAR(220) NOT NULL,
    valor               DECIMAL(12,2) DEFAULT 0,
    valor_pago          DECIMAL(12,2) DEFAULT 0,
    vencimento          DATE,
    data_pagamento      DATE,
    forma_pagamento     VARCHAR(80),
    status              ENUM('aberto','parcial','pago','cancelado') DEFAULT 'aberto',
    observacoes         TEXT,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo),
    INDEX idx_status (status),
    INDEX idx_vencimento (vencimento),
    INDEX idx_fornecedor (fornecedor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: caixa_movimentacoes
-- ============================================================
CREATE TABLE IF NOT EXISTS caixa_movimentacoes (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conta_receber_id    INT UNSIGNED,
    conta_pagar_id      INT UNSIGNED,
    usuario_id          INT UNSIGNED,
    tipo                ENUM('entrada','saida') NOT NULL,
    descricao           VARCHAR(220) NOT NULL,
    valor               DECIMAL(12,2) DEFAULT 0,
    forma_pagamento     VARCHAR(80),
    data_movimento      DATE,
    observacoes         TEXT,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conta_receber_id) REFERENCES contas_receber(id) ON DELETE SET NULL,
    FOREIGN KEY (conta_pagar_id) REFERENCES contas_pagar(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_tipo (tipo),
    INDEX idx_data (data_movimento),
    INDEX idx_receber (conta_receber_id),
    INDEX idx_pagar (conta_pagar_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: fornecedores
-- ============================================================
CREATE TABLE IF NOT EXISTS fornecedores (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo              VARCHAR(40) UNIQUE,
    nome                VARCHAR(180) NOT NULL,
    tipo_pessoa         ENUM('juridica','fisica') DEFAULT 'juridica',
    cpf_cnpj            VARCHAR(20),
    contato             VARCHAR(120),
    email               VARCHAR(150),
    telefone            VARCHAR(30),
    whatsapp            VARCHAR(30),
    endereco            VARCHAR(250),
    cidade              VARCHAR(100),
    estado              CHAR(2),
    status              ENUM('ativo','inativo') DEFAULT 'ativo',
    observacoes         TEXT,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo),
    INDEX idx_nome (nome),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: fornecedor_materiais
-- ============================================================
CREATE TABLE IF NOT EXISTS fornecedor_materiais (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fornecedor_id   INT UNSIGNED NOT NULL,
    material_id     INT UNSIGNED NOT NULL,
    observacao      VARCHAR(250),
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materiais(id) ON DELETE CASCADE,
    UNIQUE KEY uk_fornecedor_material (fornecedor_id, material_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: compras
-- ============================================================
CREATE TABLE IF NOT EXISTS compras (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo              VARCHAR(40) NOT NULL UNIQUE,
    fornecedor_id       INT UNSIGNED,
    solicitante_id      INT UNSIGNED,
    aprovado_por_id     INT UNSIGNED,
    status              ENUM('rascunho','solicitada','aprovada','recebida','cancelada') DEFAULT 'rascunho',
    origem              ENUM('manual','estoque_critico') DEFAULT 'manual',
    titulo              VARCHAR(200) NOT NULL,
    data_solicitacao    DATE,
    data_aprovacao      DATETIME,
    data_recebimento    DATETIME,
    previsao_entrega    DATE,
    total               DECIMAL(12,2) DEFAULT 0,
    gerar_conta_pagar   TINYINT(1) DEFAULT 1,
    observacoes         TEXT,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE SET NULL,
    FOREIGN KEY (solicitante_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (aprovado_por_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo),
    INDEX idx_status (status),
    INDEX idx_fornecedor (fornecedor_id),
    INDEX idx_previsao (previsao_entrega)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: compra_itens
-- ============================================================
CREATE TABLE IF NOT EXISTS compra_itens (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    compra_id       INT UNSIGNED NOT NULL,
    material_id     INT UNSIGNED,
    descricao       VARCHAR(220) NOT NULL,
    quantidade      DECIMAL(12,3) DEFAULT 1,
    unidade         VARCHAR(20) DEFAULT 'un',
    custo_unitario  DECIMAL(12,2) DEFAULT 0,
    total           DECIMAL(12,2) DEFAULT 0,
    recebido        TINYINT(1) DEFAULT 0,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (compra_id) REFERENCES compras(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materiais(id) ON DELETE SET NULL,
    INDEX idx_compra (compra_id),
    INDEX idx_material (material_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: chamados
-- ============================================================
CREATE TABLE IF NOT EXISTS chamados (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo              VARCHAR(40) NOT NULL UNIQUE,
    titulo              VARCHAR(200) NOT NULL,
    descricao           TEXT,
    setor               VARCHAR(100),
    prioridade          ENUM('baixa','media','alta','urgente') DEFAULT 'media',
    status              ENUM('aberto','em_andamento','aguardando','concluido','cancelado') DEFAULT 'aberto',
    solicitante_id      INT UNSIGNED,
    responsavel_id      INT UNSIGNED,
    cliente_id          INT UNSIGNED,
    orcamento_id        INT UNSIGNED,
    ordem_servico_id    INT UNSIGNED,
    compra_id           INT UNSIGNED,
    prazo               DATETIME,
    concluido_at        DATETIME,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (solicitante_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE SET NULL,
    FOREIGN KEY (ordem_servico_id) REFERENCES ordem_servicos(id) ON DELETE SET NULL,
    FOREIGN KEY (compra_id) REFERENCES compras(id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo),
    INDEX idx_status (status),
    INDEX idx_prioridade (prioridade),
    INDEX idx_setor (setor),
    INDEX idx_responsavel (responsavel_id),
    INDEX idx_prazo (prazo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: chamado_comentarios
-- ============================================================
CREATE TABLE IF NOT EXISTS chamado_comentarios (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chamado_id          INT UNSIGNED NOT NULL,
    usuario_id          INT UNSIGNED,
    tipo                ENUM('comentario','status','sistema') DEFAULT 'comentario',
    comentario          TEXT NOT NULL,
    status_anterior     VARCHAR(40),
    status_novo         VARCHAR(40),
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chamado_id) REFERENCES chamados(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_chamado (chamado_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: whatsapp_envios
-- ============================================================
CREATE TABLE IF NOT EXISTS whatsapp_envios (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id      INT UNSIGNED,
    usuario_id      INT UNSIGNED,
    telefone        VARCHAR(30) NOT NULL,
    mensagem        TEXT NOT NULL,
    tipo            ENUM('manual','orcamento','producao','financeiro','campanha','sistema') DEFAULT 'manual',
    origem          VARCHAR(120),
    status          ENUM('pendente','enviado','erro','simulado') DEFAULT 'pendente',
    http_status     INT,
    resposta        TEXT,
    erro            TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_cliente (cliente_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_status (status),
    INDEX idx_tipo (tipo),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: integracao_webhooks
-- ============================================================
CREATE TABLE IF NOT EXISTS integracao_webhooks (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    origem          ENUM('viicio','asaas','outro') DEFAULT 'outro',
    evento          VARCHAR(120),
    external_id     VARCHAR(160),
    payload         MEDIUMTEXT,
    headers         TEXT,
    status          ENUM('recebido','processado','erro','ignorado') DEFAULT 'recebido',
    erro            TEXT,
    ip_origem       VARCHAR(80),
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_origem (origem),
    INDEX idx_evento (evento),
    INDEX idx_external (external_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: ia_respostas
-- ============================================================
CREATE TABLE IF NOT EXISTS ia_respostas (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT UNSIGNED,
    cliente_id      INT UNSIGNED,
    provedor        ENUM('openai','gemini') DEFAULT 'openai',
    modelo          VARCHAR(100),
    contexto        ENUM('atendimento','orcamento','produto','margem','followup','operacional','livre') DEFAULT 'livre',
    prompt          TEXT NOT NULL,
    resposta        MEDIUMTEXT,
    status          ENUM('simulado','concluido','erro') DEFAULT 'simulado',
    tokens_entrada  INT DEFAULT 0,
    tokens_saida    INT DEFAULT 0,
    erro            TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_status (status),
    INDEX idx_contexto (contexto),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: planejamento_metas
-- ============================================================
CREATE TABLE IF NOT EXISTS planejamento_metas (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo              VARCHAR(40) NOT NULL UNIQUE,
    titulo              VARCHAR(180) NOT NULL,
    tipo                ENUM('geral','vendedor','setor','produto') DEFAULT 'geral',
    indicador           ENUM('vendas','orcamentos','producao','financeiro','margem','personalizado') DEFAULT 'vendas',
    periodo_mes         CHAR(7) NOT NULL,
    usuario_id          INT UNSIGNED,
    produto_id          INT UNSIGNED,
    setor               VARCHAR(120),
    unidade             ENUM('valor','quantidade','percentual') DEFAULT 'valor',
    valor_meta          DECIMAL(14,2) DEFAULT 0,
    valor_atual         DECIMAL(14,2) DEFAULT 0,
    data_inicio         DATE,
    data_fim            DATE,
    status              ENUM('planejada','em_andamento','atingida','risco','cancelada') DEFAULT 'planejada',
    observacoes         TEXT,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo),
    INDEX idx_periodo (periodo_mes),
    INDEX idx_status (status),
    INDEX idx_tipo (tipo),
    INDEX idx_usuario (usuario_id),
    INDEX idx_produto (produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: planejamento_acoes
-- ============================================================
CREATE TABLE IF NOT EXISTS planejamento_acoes (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    meta_id             INT UNSIGNED,
    responsavel_id      INT UNSIGNED,
    titulo              VARCHAR(180) NOT NULL,
    descricao           TEXT,
    prazo               DATE,
    prioridade          ENUM('baixa','media','alta','urgente') DEFAULT 'media',
    status              ENUM('pendente','em_execucao','concluida','cancelada') DEFAULT 'pendente',
    resultado           TEXT,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (meta_id) REFERENCES planejamento_metas(id) ON DELETE SET NULL,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_meta (meta_id),
    INDEX idx_responsavel (responsavel_id),
    INDEX idx_status (status),
    INDEX idx_prazo (prazo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: chat_canais
-- ============================================================
CREATE TABLE IF NOT EXISTS chat_canais (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome                VARCHAR(160) NOT NULL,
    tipo                ENUM('geral','setor','cliente','ordem_servico','privado') DEFAULT 'geral',
    setor               VARCHAR(100),
    cliente_id          INT UNSIGNED,
    ordem_servico_id    INT UNSIGNED,
    criado_por_id       INT UNSIGNED,
    status              ENUM('ativo','arquivado') DEFAULT 'ativo',
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (ordem_servico_id) REFERENCES ordem_servicos(id) ON DELETE SET NULL,
    FOREIGN KEY (criado_por_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_tipo (tipo),
    INDEX idx_status (status),
    INDEX idx_cliente (cliente_id),
    INDEX idx_os (ordem_servico_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: chat_mensagens
-- ============================================================
CREATE TABLE IF NOT EXISTS chat_mensagens (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    canal_id        INT UNSIGNED NOT NULL,
    usuario_id      INT UNSIGNED,
    mensagem        TEXT NOT NULL,
    anexo_url       VARCHAR(300),
    mencoes         VARCHAR(300),
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    edited_at       DATETIME,
    FOREIGN KEY (canal_id) REFERENCES chat_canais(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_canal (canal_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_created (created_at)
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
('Portal do Cliente', 'portal', 'bi-person-workspace', 'Cliente', 13),
('Usuários', 'usuarios', 'bi-person-gear', 'Administrativo', 20),
('Perfis e Permissões', 'perfis', 'bi-shield-check', 'Administrativo', 21),
('Auditoria', 'auditoria', 'bi-clipboard-data', 'Administrativo', 22),
('Empresa', 'empresa', 'bi-building', 'Administrativo', 23),
('Produtos', 'produtos', 'bi-box', 'Operacional', 30),
('OS / Produção', 'producao', 'bi-gear', 'Operacional', 31),
('Estoque', 'estoque', 'bi-archive', 'Operacional', 32),
('Compras', 'compras', 'bi-cart', 'Operacional', 33),
('Agenda de Instalações', 'agenda', 'bi-calendar-check', 'Operacional', 34),
('Financeiro', 'financeiro', 'bi-cash-stack', 'Financeiro', 40),
('Comissões', 'comissoes', 'bi-percent', 'Financeiro', 41),
('Colaboradores', 'colaboradores', 'bi-person-badge', 'RH', 50),
('Equipamentos', 'equipamentos', 'bi-tools', 'RH', 51),
('Central de Alertas', 'alertas', 'bi-bell', 'Inteligência', 59),
('BI Executivo', 'bi', 'bi-bar-chart-line', 'Inteligência', 60),
('Central de IA', 'ia', 'bi-stars', 'Inteligência', 61),
('Planejamento', 'planejamento', 'bi-bullseye', 'Inteligência', 62),
('Relatórios', 'relatorios', 'bi-file-earmark-bar-graph', 'Inteligência', 63),
('Integrações', 'integracoes', 'bi-plug', 'Inteligência', 64),
('POPs e Qualidade', 'pops', 'bi-clipboard-check', 'Qualidade', 70),
('Painéis de LED', 'led', 'bi-display', 'LED', 80),
('Chamados Internos', 'chamados', 'bi-ticket-detailed', 'Comunicação', 85),
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

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug,
       1,
       1,
       CASE WHEN p.nome IN ('diretor','gerente','comercial','vendedor','recepcao') THEN 1 ELSE 0 END,
       0
FROM perfis p
JOIN modulos m ON m.slug IN ('portal')
WHERE p.nome IN ('cliente')
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug, 1, 1, 1, CASE WHEN p.nome IN ('diretor','gerente') THEN 1 ELSE 0 END
FROM perfis p
JOIN modulos m ON m.slug IN ('produtos','producao','estoque','compras')
WHERE p.nome IN ('diretor','gerente','comercial','vendedor','producao','estoque','financeiro')
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, 'producao', 1, 0, 1, 0
FROM perfis p
WHERE p.nome = 'designer'
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug,
       1,
       CASE WHEN p.nome IN ('diretor','gerente','comercial','vendedor','recepcao','producao') THEN 1 ELSE 0 END,
       CASE WHEN p.nome IN ('diretor','gerente','comercial','vendedor','recepcao','producao','instalador') THEN 1 ELSE 0 END,
       CASE WHEN p.nome IN ('diretor','gerente') THEN 1 ELSE 0 END
FROM perfis p
JOIN modulos m ON m.slug IN ('agenda')
WHERE p.nome IN ('diretor','gerente','comercial','vendedor','recepcao','producao','instalador')
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug,
       1,
       CASE WHEN p.nome IN ('diretor','gerente','comercial','vendedor','recepcao') THEN 1 ELSE 0 END,
       CASE WHEN p.nome IN ('diretor','gerente','comercial','vendedor','recepcao','producao','instalador') THEN 1 ELSE 0 END,
       CASE WHEN p.nome IN ('diretor','gerente') THEN 1 ELSE 0 END
FROM perfis p
JOIN modulos m ON m.slug IN ('led')
WHERE p.nome IN ('diretor','gerente','comercial','vendedor','recepcao','producao','instalador')
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug, 1, 1, 1, CASE WHEN p.nome IN ('diretor','gerente','financeiro') THEN 1 ELSE 0 END
FROM perfis p
JOIN modulos m ON m.slug IN ('financeiro','comissoes')
WHERE p.nome IN ('diretor','gerente','financeiro','comercial')
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug, 1, 1, 1, CASE WHEN p.nome IN ('diretor','gerente','rh') THEN 1 ELSE 0 END
FROM perfis p
JOIN modulos m ON m.slug IN ('colaboradores','equipamentos')
WHERE p.nome IN ('diretor','gerente','rh','producao','estoque')
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug, 1, 0, 0, 0
FROM perfis p
JOIN modulos m ON m.slug IN ('bi')
WHERE p.nome IN ('diretor','gerente','financeiro')
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug,
       1,
       CASE WHEN p.nome IN ('diretor','gerente') THEN 1 ELSE 0 END,
       CASE WHEN p.nome IN ('diretor','gerente','comercial','financeiro','producao') THEN 1 ELSE 0 END,
       CASE WHEN p.nome IN ('diretor','gerente') THEN 1 ELSE 0 END
FROM perfis p
JOIN modulos m ON m.slug IN ('planejamento')
WHERE p.nome IN ('diretor','gerente','comercial','financeiro','producao')
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug, 1, 0, 0, 0
FROM perfis p
JOIN modulos m ON m.slug IN ('relatorios')
WHERE p.nome IN ('diretor','gerente','comercial','financeiro','producao','estoque','rh')
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug,
       1,
       CASE WHEN p.nome IN ('diretor','gerente') THEN 1 ELSE 0 END,
       CASE WHEN p.nome IN ('diretor','gerente') THEN 1 ELSE 0 END,
       CASE WHEN p.nome IN ('diretor') THEN 1 ELSE 0 END
FROM perfis p
JOIN modulos m ON m.slug IN ('integracoes')
WHERE p.nome IN ('diretor','gerente','financeiro')
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug, 1, 0, 0, 0
FROM perfis p
JOIN modulos m ON m.slug IN ('auditoria')
WHERE p.nome IN ('diretor','gerente')
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug, 1, 0, 0, 0
FROM perfis p
JOIN modulos m ON m.slug IN ('alertas')
WHERE p.nome IN ('diretor','gerente','comercial','vendedor','recepcao','designer','producao','estoque','financeiro','rh','instalador')
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug,
       1,
       CASE WHEN p.nome IN ('diretor','gerente','comercial','vendedor','recepcao','designer','producao','financeiro','rh') THEN 1 ELSE 0 END,
       CASE WHEN p.nome IN ('diretor','gerente') THEN 1 ELSE 0 END,
       CASE WHEN p.nome IN ('diretor','gerente') THEN 1 ELSE 0 END
FROM perfis p
JOIN modulos m ON m.slug IN ('ia')
WHERE p.nome IN ('diretor','gerente','comercial','vendedor','recepcao','designer','producao','financeiro','rh')
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug,
       1,
       CASE WHEN p.nome IN ('diretor','gerente','comercial','vendedor','recepcao','designer','producao','estoque','financeiro','rh','instalador') THEN 1 ELSE 0 END,
       CASE WHEN p.nome IN ('diretor','gerente') THEN 1 ELSE 0 END,
       CASE WHEN p.nome IN ('diretor','gerente') THEN 1 ELSE 0 END
FROM perfis p
JOIN modulos m ON m.slug IN ('chat')
WHERE p.nome IN ('diretor','gerente','comercial','vendedor','recepcao','designer','producao','estoque','financeiro','rh','instalador')
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug,
       1,
       CASE WHEN p.nome IN ('diretor','gerente','comercial','vendedor','recepcao','financeiro','producao') THEN 1 ELSE 0 END,
       CASE WHEN p.nome IN ('diretor','gerente','comercial','vendedor','recepcao','financeiro','producao') THEN 1 ELSE 0 END,
       CASE WHEN p.nome IN ('diretor','gerente') THEN 1 ELSE 0 END
FROM perfis p
JOIN modulos m ON m.slug IN ('whatsapp')
WHERE p.nome IN ('diretor','gerente','comercial','vendedor','recepcao','financeiro','producao')
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug,
       1,
       CASE WHEN p.nome IN ('diretor','gerente','producao','designer','rh') THEN 1 ELSE 0 END,
       CASE WHEN p.nome IN ('diretor','gerente','producao','designer','rh') THEN 1 ELSE 0 END,
       CASE WHEN p.nome IN ('diretor','gerente') THEN 1 ELSE 0 END
FROM perfis p
JOIN modulos m ON m.slug IN ('pops')
WHERE p.nome IN ('diretor','gerente','producao','designer','estoque','rh')
ON DUPLICATE KEY UPDATE
    pode_ver = VALUES(pode_ver),
    pode_criar = VALUES(pode_criar),
    pode_editar = VALUES(pode_editar),
    pode_excluir = VALUES(pode_excluir);

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, m.slug,
       1,
       CASE WHEN p.nome NOT IN ('instalador') THEN 1 ELSE 0 END,
       CASE WHEN p.nome IN ('diretor','gerente','comercial','vendedor','recepcao','designer','producao','estoque','financeiro','rh') THEN 1 ELSE 0 END,
       CASE WHEN p.nome IN ('diretor','gerente') THEN 1 ELSE 0 END
FROM perfis p
JOIN modulos m ON m.slug IN ('chamados')
WHERE p.nome IN ('diretor','gerente','comercial','vendedor','recepcao','designer','producao','estoque','financeiro','rh','instalador')
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

-- Conteudo inicial da landing page
INSERT INTO site_configuracoes
    (google_analytics_id, seo_titulo, seo_descricao, seo_keywords, canonical_url, hero_badge, hero_titulo, hero_subtitulo, hero_cta_texto, hero_cta_secundario, hero_image_url, created_at)
SELECT
    '',
    'KROMA PRINT - Comunicação visual, fachadas, DTF e impressão digital',
    'Comunicação visual completa para empresas: fachadas em ACM, banners, lonas, adesivos, DTF, uniformes, brindes e painéis de LED.',
    'comunicação visual, fachada acm, impressão digital, banners, lonas, adesivos, dtf, uniformes personalizados, brindes personalizados, painel de led',
    '',
    'Comunicação visual completa',
    'Comunicação visual que sai bonita no layout e impecável na produção.',
    'Fachadas, lonas, adesivos, DTF, uniformes, brindes e painéis de LED com atendimento rápido e acompanhamento comercial pelo CRM.',
    'Solicitar Orçamento',
    'Falar no WhatsApp',
    '',
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM site_configuracoes);

INSERT INTO site_servicos (titulo, icone, descricao, ordem, ativo, created_at)
SELECT * FROM (
    SELECT 'Fachadas e ACM', 'bi-shop', 'ACM, letras caixa, totens e sinalização para destacar sua marca.', 10, 1, NOW()
    UNION ALL SELECT 'Banners e Lonas', 'bi-image', 'Impressão em grandes formatos para eventos, obras e pontos de venda.', 20, 1, NOW()
    UNION ALL SELECT 'DTF e Uniformes', 'bi-printer', 'Personalização têxtil para equipes, campanhas e revendas.', 30, 1, NOW()
    UNION ALL SELECT 'Adesivos e Envelopamento', 'bi-layers', 'Recorte, impressão, laminação e aplicação profissional.', 40, 1, NOW()
    UNION ALL SELECT 'Brindes Personalizados', 'bi-gift', 'Produtos promocionais sob demanda para sua empresa.', 50, 1, NOW()
    UNION ALL SELECT 'Paineis de LED', 'bi-display', 'Locação, operação e conteúdo para eventos e mídia indoor.', 60, 1, NOW()
) AS seed_site_servicos
WHERE NOT EXISTS (SELECT 1 FROM site_servicos);

INSERT INTO site_portfolio (titulo, categoria, descricao, imagem_url, ordem, ativo, created_at)
SELECT * FROM (
    SELECT 'Fachadas comerciais', 'Fachadas', 'ACM, letras e iluminação para lojas.', '', 10, 1, NOW()
    UNION ALL SELECT 'Eventos e campanhas', 'Eventos', 'Lonas, banners e sinalização promocional.', '', 20, 1, NOW()
    UNION ALL SELECT 'Frotas e adesivos', 'Adesivos', 'Envelopamento e identidade visual veicular.', '', 30, 1, NOW()
    UNION ALL SELECT 'Uniformes DTF', 'DTF', 'Personalização têxtil com acabamento profissional.', '', 40, 1, NOW()
) AS seed_site_portfolio
WHERE NOT EXISTS (SELECT 1 FROM site_portfolio);
