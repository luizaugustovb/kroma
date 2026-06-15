# PROMPT MASTER — KROMA PRINT ERP/CRM

Desenvolver a plataforma **KROMA PRINT**, um sistema completo para gráfica rápida, comunicação visual, impressão digital, DTF, brindes, uniformes, fachadas, produtos personalizados e aluguel de painéis de LED.

O sistema deve ser desenvolvido em:

* PHP 8+
* MySQL
* PDO
* HTML5
* CSS3
* Bootstrap 5
* JavaScript
* AJAX
* Chart.js
* DataTables
* SweetAlert
* API REST interna

A solução deve ser 100% responsiva para celular, tablet, notebook, desktop, monitores grandes e TVs.

---

# 1. ESTRUTURA GERAL

O projeto terá dois ambientes:

## 1.1 Landing Page Pública KROMA PRINT

Voltada para clientes.

Funções:

* Apresentar serviços
* Solicitar orçamento
* Simulador rápido
* Upload de arquivos até 100MB
* Contato via WhatsApp
* Galeria/portfólio
* Captação automática de leads para o CRM

Serviços exibidos:

* Fachadas
* ACM
* Letras caixa
* Totens
* Banners
* Lonas
* Adesivos
* DTF
* Uniformes
* Brindes
* Camisetas
* Eventos
* Sinalização
* Painéis de LED
* Comunicação visual completa

## 1.2 Sistema Interno KROMA ERP

Sistema protegido por login, com permissões por perfil e controle completo da operação.

---

# 2. PERFIS DE ACESSO

Criar usuários/colaboradores com permissões por perfil:

* Administrador
* Diretor
* Gerente
* Comercial
* Vendedor
* Recepção
* Designer
* Produção
* Estoque
* Financeiro
* RH/Departamento Pessoal
* Instalador
* Cliente

Cada perfil deve acessar apenas os módulos autorizados.

---

# 3. CRM E COMERCIAL

Criar CRM com Kanban e funil de vendas.

Etapas:

* Novo lead
* Primeiro contato
* Orçamento rápido
* Orçamento com IA
* Orçamento enviado
* Negociação
* Aprovado
* Em produção
* Entregue
* Pós-venda
* Recorrência
* Perdido

Funcionalidades:

* Mini CRM por vendedor
* Histórico completo do cliente
* Follow-up pós-venda de 0 a 90 dias
* Classificação de clientes
* Tipos de cliente:

  * Cliente final
  * Revenda
  * Parceiro
  * Corporativo
  * Órgão público
* Controle de origem do lead
* Tarefas comerciais
* Alertas automáticos
* Motivo de perda
* Taxa de conversão
* Ticket médio
* Carteira por vendedor

---

# 4. ORÇAMENTOS

Criar módulo avançado de orçamento.

Tipos:

* Orçamento rápido
* Orçamento completo
* Orçamento com IA
* Orçamento por produto
* Orçamento por item
* Orçamento por setor
* Orçamento para revenda
* Orçamento para cliente final

O orçamento deve gerar OS automaticamente após aprovação.

## 4.1 Cálculo de preço

Implementar:

* Precificação RKW
* Custeio direto
* Margem desejada
* Comissão do vendedor
* Impostos
* Desperdício previsto
* Custo de material
* Custo de tinta
* Custo de acabamento
* Custo de mão de obra
* Custo hora/máquina
* Depreciação
* Energia
* Frete
* Instalação
* Desconto autorizado
* Preço mínimo
* Lucro previsto

Permitir alteração da comissão do vendedor no orçamento, com permissão específica, para ajuste comercial.

---

# 5. INTELIGÊNCIA ARTIFICIAL

Criar estrutura pronta para integração com:

* OpenAI
* Gemini
* OpenCode
* Outras APIs futuras

Usar IA para:

* Atendimento inicial
* Sugestão de orçamento
* Descrição automática de produtos
* Campanhas de WhatsApp
* Resumo de cliente
* Sugestão de preço
* Análise de margem
* Análise de risco
* Sugestão de follow-up
* Criação de mensagens comerciais
* Análise de eficiência operacional

Criar painel para configurar:

* Chave da API
* Modelo utilizado
* Prompt padrão
* Limite de uso
* Log de consumo
* Histórico das respostas

---

# 6. WHATSAPP — API VIICIO

Integrar com API Viicio.

Permitir configurar token no painel.

Cada cliente deve ter opção:

* Receber WhatsApp: Sim/Não
* Receber campanhas: Sim/Não
* Receber status de produção: Sim/Não
* Receber financeiro/cobrança: Sim/Não

Usar WhatsApp para:

* Novo lead
* Confirmação de upload
* Envio de orçamento
* Aprovação de orçamento
* Aprovação de arte
* Início de produção
* Conclusão de produção
* Pedido pronto
* Cobrança
* Pós-venda
* Campanhas

Registrar logs de todos os envios.

---

# 7. ORDEM DE SERVIÇO / PRODUÇÃO

A OS deve nascer a partir do orçamento aprovado.

Implementar:

## Níveis de controle da OS

* OS a partir do orçamento
* Quebra em subordens
* Painel com cronogramas
* Entrega final da OS
* Nível 2 por item da OS
* Nível 3 por setor
* Nível 4 por etapa do processo
* Nível 5 hora-máquina/homem
* Processos produtivos
* Modo produção impresso
* Modo cliente
* Observações separadas por setor
* Tempo previsto por setor
* Acompanhamento de retrabalho
* Melhoria contínua

## Painel de produção

Criar painel para produção com:

* OS abertas
* OS em produção
* OS concluídas
* OS atrasadas
* OS do dia
* Filtro para mostrar concluídas somente se selecionar
* Filtro por setor
* Filtro por máquina
* Filtro por operador
* Filtro por prioridade
* Filtro por prazo
* Painel para TV na produção

Status da OS:

* Aguardando arte
* Arte em criação
* Arte aprovada
* Aguardando produção
* Em impressão
* Em corte
* Em laminação
* Em acabamento
* Em separação
* Pronto para entrega
* Entregue
* Finalizado
* Retrabalho
* Cancelado

---

# 8. PROCESSOS PRODUTIVOS

Criar cadastro de processos produtivos.

Exemplos:

* Impressão ecosolvente
* Impressão DTF
* Laminação
* Recorte
* Acabamento
* Ilhós
* Solda
* Refile
* Aplicação de máscara
* Instalação
* Corte CNC
* Produção de placa
* Fachada
* Adesivação
* Envelopamento
* Montagem
* Expedição

Cada processo deve permitir:

* Tempo previsto
* Setor responsável
* Máquina
* Operador
* Custo hora
* Checklist
* POP vinculado
* Consumo previsto
* Desperdício previsto

---

# 9. PRODUTOS, CATEGORIAS E VARIAÇÕES

Criar cadastro completo de produtos.

## Produtos base

Exemplos:

* Banner
* Lona
* Adesivo
* Adesivo recortado
* Fachada
* Placa ACM
* Letra caixa
* DTF
* Camiseta
* Uniforme
* Totem
* Painel
* Brinde
* Cartão
* Panfleto
* Etiqueta
* Rótulo

## Funcionalidades

* Categorias completas
* Produtos base
* Variações
* Tabelas de variações
* Composição completa
* Acabamentos
* Processos vinculados
* Duplicação de produto
* Duplicação de variações
* Descritivo automático
* Questionário por produto
* Campos obrigatórios por produto
* Validade de produto perecível
* Priorização 80/20

---

# 10. ESTOQUE

Criar controle de estoque completo.

Conceito:

“Estoque tem que dar lucro. Estoque não é custo — é uma empresa dentro da empresa.”

## Controle por níveis

* Nível 1: Unitário / primeiro estágio
* Nível 2: Fracionado + segundo unitário
* Nível 3: Completo
* Primeiro estágio: chapas, mídias, perfis
* Segundo estágio: lâminas e consumíveis
* Almoxarifado x fracionado
* Categorias pré-definidas
* Cotação de fornecedores
* Ordem de compra + PCP

Materiais:

* Lona
* Adesivo
* Máscara
* Laminação
* Tinta
* PVC
* ACM
* Acrílico
* Chapas
* Perfis
* Ilhós
* Parafusos
* Papel
* DTF film
* Pó DTF
* Consumíveis

Controle:

* Estoque mínimo
* Estoque crítico
* Validade
* Lote
* Fornecedor
* Custo médio
* Localização
* Entrada
* Saída
* Perdas
* Inventário
* Fracionamento
* Consumo por OS
* Baixa automática
* Desperdício

---

# 11. COMPRAS E FORNECEDORES

Criar módulo de compras.

Funcionalidades:

* Cadastro de fornecedores
* Cotação
* Pedido de compra
* Recebimento
* Histórico de preços
* Comparativo de fornecedores
* Prazo de entrega
* Condições de pagamento
* Ordem de compra
* Integração com estoque

---

# 12. FINANCEIRO

Criar módulo financeiro completo.

Funcionalidades:

* Contas a pagar
* Contas a receber
* Fluxo de caixa
* Conciliação bancária
* Formas de pagamento
* Condições de pagamento
* Vínculos financeiros
* Plano de contas
* Centro de custos
* Alíquotas de impostos
* DRE
* Relatórios personalizados
* Inadimplência
* Comissões
* Recebimentos por vendedor
* Previsão financeira
* Pagamentos recorrentes

## Integração Asaas

Preparar integração com Banco Asaas para:

* Cobranças
* PIX
* Boletos
* Links de pagamento
* Webhooks de pagamento
* Baixa automática
* Histórico financeiro

---

# 13. FISCAL

Criar base para dados fiscais.

Funcionalidades:

* Dados fiscais da empresa
* Certificado digital
* Alíquotas
* Dados para emissão futura
* Integração fiscal futura
* Controle de documentos fiscais
* Configuração de impostos por produto/serviço

---

# 14. RH E DEPARTAMENTO PESSOAL

Criar base administrativa de RH.

Funcionários/colaboradores serão também usuários do sistema.

Funcionalidades:

* Cadastro de colaboradores
* Dados pessoais
* Dados bancários
* Pix
* Cargo
* Setor
* Perfil de acesso
* Salário
* Plano de cargos e salários
* Pagamentos
* Adiantamentos
* Histórico financeiro do colaborador
* Comissões
* Controle de documentos
* Status do colaborador
* Observações internas

---

# 15. COMISSÕES

Criar regras configuráveis de comissão.

Permitir comissão por:

* Vendedor
* Produto
* Categoria
* Cliente
* Tipo de cliente
* Margem mínima
* Valor vendido
* Valor recebido
* Pedido faturado
* Pedido pago
* Meta atingida

Permitir:

* Comissão fixa
* Comissão percentual
* Comissão por faixa
* Comissão reduzida com desconto
* Comissão bloqueada se margem mínima não for atingida

---

# 16. CHAT INTERNO

Criar chat interno para equipe.

Funcionalidades:

* Conversa por setor
* Conversa por OS
* Conversa por cliente
* Menções
* Anexos
* Notificações internas
* Histórico

---

# 17. POPs, CHECKLISTS E QUALIDADE

Criar biblioteca de POPs.

Funcionalidades:

* POP por setor
* POP por processo
* POP por produto
* Checklist de operação
* Checklist de qualidade
* Checklist de entrega
* Anexos
* Vídeos
* Fotos
* Revisões
* Controle de versão

---

# 18. BI EXECUTIVO E CENTRAL DE INTELIGÊNCIA

Criar uma Central de Inteligência com dados, KPIs e relatórios.

## Painéis

* Dashboard executivo
* Painel de gestão de vendas
* Painel de produção
* Painel financeiro
* Painel de estoque
* Painel de RH
* Painel de campanhas
* Painel de eficiência operacional

## Indicadores

* Pedidos hoje
* Pedidos atrasados
* Pedidos em produção
* Faturamento diário
* Faturamento mensal
* Lucro bruto
* Lucro líquido estimado
* Margem média
* Ticket médio
* Conversão comercial
* Eficiência operacional
* Custo hora produtivo
* Hora máquina
* Hora homem
* Alocação de mão de obra 60/40
* Produção por colaborador
* Produção por máquina
* Desperdício em m²
* Desperdício em R$
* Retrabalho
* Estoque parado
* Estoque crítico
* Produtos mais vendidos
* Clientes mais lucrativos
* Vendedores mais produtivos

---

# 19. PLANEJAMENTO ESTRATÉGICO

Criar módulo de planejamento estratégico.

Funcionalidades:

* Metas mensais
* Metas por vendedor
* Metas por setor
* Metas por produto
* Plano de ação
* Indicadores estratégicos
* Evolução mensal
* Relatórios gerenciais
* Progresso de metas
* Painel de gestão

---

# 20. ADMINISTRATIVO

Criar módulo administrativo base.

Funcionalidades:

* Dados da empresa
* Cabeçalho
* Fundo
* Rodapé
* Texto e slogan
* Condições de orçamento
* Equipamentos
* Depreciação
* Produtividade hora/m²
* Imobilizado
* Dados fiscais
* Certificado digital
* Veículos
* Depreciação
* Cortes CNC
* Configurações gerais

---

# 21. EQUIPAMENTOS, VEÍCULOS E IMOBILIZADO

Criar controle de ativos.

Funcionalidades:

* Cadastro de equipamentos
* Valor de compra
* Vida útil
* Depreciação
* Custo hora
* Produtividade
* Manutenção
* Histórico
* Veículos
* Custos dos veículos
* Depreciação dos veículos
* Imobilizado geral

---

# 22. AGENDA DE EQUIPES E INSTALAÇÕES

Criar agenda operacional.

Funcionalidades:

* Agenda de instalação
* Equipes externas
* Técnicos
* Veículos
* Rotas
* Localização do cliente
* Horário previsto
* Status da instalação
* Checklist de instalação
* Fotos antes/depois
* Assinatura de entrega
* Notificação para cliente

---

# 23. ALUGUEL DE PAINÉIS DE LED

Criar módulo específico para aluguel de painéis de LED.

Funcionalidades:

* Cadastro de painéis
* Tamanho
* Resolução
* Localização
* Disponibilidade
* Cliente
* Contrato
* Valor de aluguel
* Período
* Data início
* Data fim
* Local de instalação
* Arquivos/vídeos exibidos
* Playlist
* Responsável
* Status
* Fotos
* Comprovantes
* Manutenção
* Renovação de contrato
* Relatório por cliente
* Relatório por localização
* Relatório de faturamento
* Controle de agenda dos painéis

Status:

* Disponível
* Reservado
* Instalado
* Em manutenção
* Retirado
* Cancelado

---

# 24. PORTAL DO CLIENTE

Cliente deve acessar área própria.

Funcionalidades:

* Ver orçamentos
* Aprovar orçamento
* Enviar arquivos
* Aprovar arte
* Acompanhar OS
* Ver status da produção
* Ver financeiro
* Baixar arquivos
* Solicitar novo orçamento
* Ver histórico
* Comunicação com equipe

---

# 25. SEGURANÇA

Implementar:

* Login seguro
* Senha com password_hash
* PDO
* Proteção SQL Injection
* CSRF
* Controle de sessão
* Logs de acesso
* Logs de ações
* Auditoria
* Permissões granulares
* LGPD
* Backup
* Registro de IP
* Bloqueio por tentativa de login
* Upload seguro
* Limite de arquivo
* Pastas sensíveis fora do public

---

# 26. LAYOUT

O sistema deve ter layout moderno, inspirado em ERPs e CRMs atuais.

Características:

* Sidebar moderna
* Dashboard limpo
* Cards de KPI
* Kanban
* Tabelas inteligentes
* Filtros rápidos
* Tema claro e escuro
* Responsivo
* Painel para TV
* Interface intuitiva
* Ícones modernos
* Notificações internas

---

# 27. FASES DE DESENVOLVIMENTO

## Fase 1 — Base do sistema

* Login
* Usuários
* Perfis
* Permissões
* Dados da empresa
* Clientes
* Colaboradores
* CRM básico
* Landing page
* Upload público
* API Viicio

## Fase 2 — Comercial

* Orçamento rápido
* Orçamento completo
* Orçamento com IA
* Kanban comercial
* Follow-up 0 a 90 dias
* Tipos de cliente
* Comissões básicas

## Fase 3 — Produtos e precificação

* Produtos base
* Categorias
* Variações
* Composição
* Processos
* Acabamentos
* RKW
* Custeio direto
* Custo hora máquina/homem

## Fase 4 — Produção

* OS a partir do orçamento
* Subordens
* Setores
* Etapas
* Painel de produção
* Cronogramas
* Notificações de início/conclusão
* Retrabalho

## Fase 5 — Estoque e compras

* Materiais
* Bobinas
* Fracionamento
* Validade
* Desperdício
* Compras
* Fornecedores
* Cotação
* Ordem de compra

## Fase 6 — Financeiro

* Contas a pagar
* Contas a receber
* Fluxo de caixa
* Plano de contas
* Centro de custos
* Comissões avançadas
* Conciliação bancária
* Asaas

## Fase 7 — RH e administrativo

* Colaboradores
* Cargos e salários
* Pagamentos
* Adiantamentos
* Dados bancários
* Equipamentos
* Veículos
* Imobilizado
* Depreciação

## Fase 8 — BI e inteligência

* Dashboard executivo
* Eficiência operacional
* Gestão de vendas
* Planejamento estratégico
* Relatórios gerenciais
* IA para análise

## Fase 9 — Painéis de LED

* Clientes
* Contratos
* Localizações
* Vídeos
* Agenda
* Valores
* Manutenção
* Relatórios

---

# 28. OBJETIVO FINAL

A KROMA PRINT ERP deve ser uma central completa de inteligência, operação e gestão para gráfica e comunicação visual.

O sistema deve controlar:

* Cliente
* Atendimento
* Vendas
* Orçamento
* IA
* Produção
* OS
* Equipe
* Estoque
* Desperdício
* Compras
* Financeiro
* RH
* Comissões
* Painéis de LED
* BI
* Indicadores
* Estratégia

A lógica principal do sistema é:

Vender melhor, produzir com controle, reduzir desperdício, proteger margem, automatizar comunicação e mostrar em tempo real onde a empresa ganha ou perde dinheiro.

Usuarios Master
contato@luizaugusto.me
Senha
Luiz2012@