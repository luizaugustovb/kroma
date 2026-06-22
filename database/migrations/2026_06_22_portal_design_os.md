# Migração: portal do cliente, designer e OS

Rode no banco `kroma_print` quando o MySQL estiver ativo.

```sql
ALTER TABLE orcamentos
  ADD COLUMN IF NOT EXISTS arquivo_projeto VARCHAR(300) NULL AFTER total;

ALTER TABLE ordem_servicos
  ADD COLUMN IF NOT EXISTS custo_real DECIMAL(12,2) NULL,
  ADD COLUMN IF NOT EXISTS obs_otimizacao TEXT NULL;

ALTER TABLE ordem_servico_itens
  ADD COLUMN IF NOT EXISTS material_real VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS area_real DECIMAL(12,3) NULL,
  ADD COLUMN IF NOT EXISTS custo_real DECIMAL(12,2) NULL;

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, 'portal', 1, 1, 0, 0
FROM perfis p
WHERE p.nome = 'cliente'
ON DUPLICATE KEY UPDATE
  pode_ver = VALUES(pode_ver),
  pode_criar = VALUES(pode_criar),
  pode_editar = VALUES(pode_editar),
  pode_excluir = VALUES(pode_excluir);

UPDATE permissoes pe
JOIN perfis p ON p.id = pe.perfil_id
SET pe.pode_ver = 0,
    pe.pode_criar = 0,
    pe.pode_editar = 0,
    pe.pode_excluir = 0
WHERE pe.modulo_slug = 'portal'
  AND p.nome <> 'cliente';

INSERT INTO permissoes (perfil_id, modulo_slug, pode_ver, pode_criar, pode_editar, pode_excluir)
SELECT p.id, 'producao', 1, 0, 1, 0
FROM perfis p
WHERE p.nome = 'designer'
ON DUPLICATE KEY UPDATE
  pode_ver = VALUES(pode_ver),
  pode_criar = VALUES(pode_criar),
  pode_editar = VALUES(pode_editar),
  pode_excluir = VALUES(pode_excluir);
```
