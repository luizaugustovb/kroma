# KROMA PRINT — Backlog de Atualizações

> Arquivo de controle de mudanças, migrações e funcionalidades pendentes.

---

## ✅ Migrações SQL Já Executadas

```sql
-- Adicionado em 2026-06-20 — fornecedor no produto
ALTER TABLE produtos
  ADD COLUMN fornecedor_id INT UNSIGNED DEFAULT NULL,
  ADD CONSTRAINT fk_produtos_fornecedor FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE SET NULL,
  ADD INDEX idx_fornecedor (fornecedor_id);

-- Adicionado em 2026-06-20 — tipo_item e material_tipo no orçamento
ALTER TABLE orcamento_itens
  ADD COLUMN tipo_item ENUM('pronto','personalizado') DEFAULT 'personalizado' AFTER produto_id,
  ADD COLUMN material_tipo VARCHAR(80) DEFAULT NULL AFTER area_m2;

-- Adicionado em 2026-06-20 — custo real por item de OS
ALTER TABLE ordem_servico_itens
  ADD COLUMN material_real VARCHAR(200) DEFAULT NULL,
  ADD COLUMN area_real DECIMAL(12,3) DEFAULT NULL,
  ADD COLUMN custo_real DECIMAL(12,2) DEFAULT NULL;

-- Adicionado em 2026-06-20 — custo real e otimização na OS
ALTER TABLE ordem_servicos
  ADD COLUMN custo_real DECIMAL(12,2) DEFAULT NULL,
  ADD COLUMN obs_otimizacao TEXT DEFAULT NULL;
```

```sql
-- Adicionado em 2026-06-24 — tabela de preços por tipo de cliente no produto
ALTER TABLE produtos
  ADD COLUMN desc_revenda_percent      DECIMAL(6,2)  DEFAULT 15.00  AFTER preco_base,
  ADD COLUMN desc_terceirizado_percent DECIMAL(6,2)  DEFAULT 25.00  AFTER desc_revenda_percent,
  ADD COLUMN preco_cliente_final       DECIMAL(12,2) DEFAULT 0.00   AFTER desc_terceirizado_percent,
  ADD COLUMN preco_revenda             DECIMAL(12,2) DEFAULT 0.00   AFTER preco_cliente_final,
  ADD COLUMN preco_terceirizado        DECIMAL(12,2) DEFAULT 0.00   AFTER preco_revenda;
```

```sql
-- Adicionado em 2026-06-24 — tabela de preços por tipo de cliente no orçamento
ALTER TABLE orcamentos
  ADD COLUMN tipo_preco ENUM('cliente_final','revenda','terceirizado') DEFAULT 'cliente_final' AFTER cliente_id;
```

---

## 🔧 Funcionalidades Pendentes

---

### 1. Orçamento: Produto Pronto vs Personalizado

**Contexto:** Na criação de um orçamento, o usuário deve poder escolher entre dois modos por item:

#### Modo "Produto Pronto"
- Exibe select com produtos do cadastro (`/produtos`)
- Ao selecionar o produto, preenche automaticamente nome, unidade, preço base e demais campos
- Calcula e exibe o estoque disponível do produto selecionado
- Ao aprovar/finalizar a OS, debita automaticamente do estoque

#### Modo "Personalizado"
- Exibe campos de metragem: largura (m) × altura (m) = m² calculado
- Material: select com tipos (Lona, Papel, Adesivo, Tecido, etc.)
- Itens manuais adicionais (acabamentos, estrutura, ilhós, suporte, instalação, etc.)
- Cada item adicional tem: descrição, quantidade, valor unitário
- Cálculo automático de m² × custo/m² do material

**Arquivos a alterar:**
- `app/Views/orcamentos/form.php` — toggle por item (radio: pronto/personalizado), campos condicionais
- `app/Controllers/OrcamentoController.php` — salvar tipo de item, calcular subtotais
- `database/install.sql` — adicionar campos em `orcamento_itens`:

```sql
ALTER TABLE orcamento_itens
  ADD COLUMN tipo_item ENUM('pronto','personalizado') DEFAULT 'personalizado',
  ADD COLUMN produto_id INT UNSIGNED DEFAULT NULL,
  ADD COLUMN largura DECIMAL(10,3) DEFAULT 0,
  ADD COLUMN altura DECIMAL(10,3) DEFAULT 0,
  ADD COLUMN metros_quadrados DECIMAL(10,3) DEFAULT 0,
  ADD COLUMN material VARCHAR(80) DEFAULT NULL,
  ADD CONSTRAINT fk_orc_itens_produto FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL;
```

---

### 2. Otimização de Material na Produção (Custo Real vs Orçado)

**Contexto:** Na produção, o operador pode aproveitar sobras de lona/adesivo, encaixando peças menores em folhas maiores já em uso. Isso reduz o custo real sem alterar o preço do orçamento, aumentando a margem de lucro.

**Comportamento esperado:**
- A OS exibe o material orçado (ex: 2,0 m² de lona)
- O operador pode registrar o **material real utilizado** (ex: 1,4 m²) com justificativa
- O custo da OS é recalculado com base no material real
- O orçamento/valor cobrado **não é alterado**
- O sistema calcula e exibe a diferença de margem (lucro real vs lucro orçado)
- Apenas usuários com perfil `producao` ou `administrador` podem editar o material real

**Arquivos a alterar:**
- `app/Views/producao/show.php` — seção "Material Utilizado" com campos editáveis
- `app/Controllers/ProducaoController.php` — método `salvarMaterialReal()`
- `database/install.sql` — adicionar campos em `ordem_servico_itens` (ou `ordens_servico`):

```sql
ALTER TABLE ordem_servicos
  ADD COLUMN custo_real DECIMAL(12,2) DEFAULT NULL COMMENT 'Custo real após otimização na produção',
  ADD COLUMN custo_material_real DECIMAL(12,2) DEFAULT NULL,
  ADD COLUMN observacao_material TEXT DEFAULT NULL;
```

---

### 3. Portal do Cliente — Restrições e Funcionalidades

**Contexto:** O cliente acessa o portal (`/portal`) com login próprio e deve ver apenas o que lhe pertence.

**O que o cliente pode ver:**
- Seus orçamentos (status: enviado, aprovado, reprovado, concluído)
- Orçamentos **aprovados**: pode visualizar o arquivo de modelo/mockup (se houver upload)
- Débitos/faturas no Asaas — valor a pagar, status, link de pagamento
- **Não vê**: custos internos, margens, processos de produção, dados de outros clientes

**O que o cliente NÃO pode ver:**
- Valores de custo interno dos itens
- Margens, impostos, comissão interna
- OS/produção (status, etapas internas)
- Dados de outros clientes

**Arquivos a alterar:**
- `app/Controllers/PortalController.php` — filtrar dados pelo `cliente_id` do usuário logado
- `app/Views/portal/` — criar/ajustar views:
  - `index.php` — dashboard do cliente: resumo de orçamentos e débitos
  - `orcamento_show.php` — visualizar orçamento aprovado com arquivo de modelo
  - `financeiro.php` — listar cobranças Asaas com link de pagamento

---

### 4. Filtros por Período (Dia / Semana / Mês)

**Contexto:** Em todas as listagens principais, adicionar barra de filtro rápido de período.

**Telas afetadas:**
- `/orcamentos` — lista de orçamentos
- `/producao` — lista de OS
- Qualquer outra listagem com `created_at`

**Comportamento:**
- Botões rápidos: `Hoje` | `Esta semana` | `Este mês` | `Todos`
- Input de período personalizado (data início / data fim) opcional
- Filtro aplicado via GET params (`?periodo=hoje`, `?de=2026-06-01&ate=2026-06-30`)
- Persiste na URL para compartilhamento

**Arquivos a alterar:**
- `app/Views/orcamentos/index.php` — adicionar barra de filtros no topo da tabela
- `app/Controllers/OrcamentoController.php` — método `index()` aplicar filtro de data
- `app/Views/producao/index.php` — idem
- `app/Controllers/ProducaoController.php` — idem
- `app/Views/layouts/` — considerar componente reutilizável de filtro de período

---

### 5. Itens Manuais Adicionais no Orçamento Personalizado

**Contexto:** Para o modo personalizado, além do material principal (lona/papel/adesivo), o usuário precisa adicionar itens extras com descrição livre.

**Exemplos de itens extras:**
- Acabamento (ilhós, bainhas, cantoneiras)
- Estrutura (perfil de alumínio, suporte)
- Instalação / mão de obra
- Frete / logística
- Qualquer item avulso

**Comportamento:**
- Botão "+ Adicionar item" inclui nova linha na tabela
- Cada linha: descrição (texto livre), quantidade, valor unitário, subtotal
- Itens somam ao total do orçamento
- Linha pode ser removida com botão "×"
- Implementado em JS puro (sem reload)

---

## 📋 Ordem de Prioridade Sugerida

1. **Filtros de período** (menor esforço, maior impacto imediato)
2. **Orçamento: Produto Pronto vs Personalizado** (core do negócio)
3. **Itens manuais no orçamento personalizado** (complementar ao item 2)
4. **Otimização de material na produção** (controle de margem)
5. **Portal do cliente refinado** (Asaas + modelo aprovado)

---

## 🗒️ Notas Técnicas

- WhatsApp: usar `App\Services\WhatsAppService::enviar()` para notificações
- Auth: `Auth::temPerfil('administrador')`, `Auth::pode('permissao')`
- DB: helper `db()` retorna PDO; usar prepared statements sempre
- Uploads: salvar em `public/uploads/` com validação MIME via `finfo`
- CSRF: `Auth::verificarCsrf($_POST['csrf_token'] ?? '')` em todo POST
- Asaas: integração via `app/Controllers/IntegracaoController.php` e `config/app.php`
