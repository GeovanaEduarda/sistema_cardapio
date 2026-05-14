-- =============================================================================
-- MIGRAÇÃO — Esquema legado (Etapa 2/3) → português snake_case (schema completo)
-- =============================================================================
-- PRÉ-REQUISITOS: backup completo. Execute em ordem, seção por seção, e corrija
-- erros de FK (dados órfãos) antes de continuar, se necessário.
-- Desative aplicações durante a migração.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

USE cardapio;

-- ---------------------------------------------------------------------------
-- 1) Renomear tabelas principais (ajuste nomes se sua base já estiver migrada)
-- ---------------------------------------------------------------------------
-- Descomente apenas o que ainda não foi aplicado:

-- RENAME TABLE usuarios_admin TO usuarios_administradores;
-- RENAME TABLE itens_cardapio TO itens_do_cardapio;
-- RENAME TABLE itens_pedido TO itens_do_pedido;
-- RENAME TABLE itens_pedido_adicionais TO adicionais_do_item_do_pedido;
-- RENAME TABLE movimentacoes_caixa TO movimentacoes_do_caixa;
-- RENAME TABLE caixa_sessoes TO sessoes_do_caixa;
-- RENAME TABLE auditoria_exclusoes TO auditoria_de_exclusoes;

-- ---------------------------------------------------------------------------
-- 2) Colunas — usuários e imagem
-- ---------------------------------------------------------------------------
-- ALTER TABLE usuarios_administradores CHANGE imagem_url url_imagem VARCHAR(255) DEFAULT NULL;

-- ---------------------------------------------------------------------------
-- 3) Cardápio — FK categoria
-- ---------------------------------------------------------------------------
-- ALTER TABLE adicionais CHANGE categorias_id id_categoria INT UNSIGNED NOT NULL;
-- ALTER TABLE itens_do_cardapio CHANGE categorias_id id_categoria INT UNSIGNED NOT NULL;
-- ALTER TABLE itens_do_cardapio CHANGE imagem_url url_imagem VARCHAR(255) DEFAULT NULL;

-- ---------------------------------------------------------------------------
-- 4) Pedidos e linhas
-- ---------------------------------------------------------------------------
-- ALTER TABLE pedidos CHANGE clientes_id id_cliente INT UNSIGNED DEFAULT NULL;
-- ALTER TABLE pedidos CHANGE usuarios_id id_usuario INT UNSIGNED DEFAULT NULL;

-- ALTER TABLE itens_do_pedido CHANGE pedido_id id_pedido INT UNSIGNED NOT NULL;
-- ALTER TABLE itens_do_pedido CHANGE item_id id_item INT UNSIGNED NOT NULL;

-- ALTER TABLE adicionais_do_item_do_pedido CHANGE id id_adicional_item_pedido INT UNSIGNED NOT NULL AUTO_INCREMENT;
-- ALTER TABLE adicionais_do_item_do_pedido CHANGE item_pedido_id id_item_pedido INT UNSIGNED NOT NULL;
-- ALTER TABLE adicionais_do_item_do_pedido CHANGE adicional_id id_adicional INT UNSIGNED NOT NULL;

-- ---------------------------------------------------------------------------
-- 5) Despesas e movimentações
-- ---------------------------------------------------------------------------
-- ALTER TABLE despesas CHANGE usuarios_id id_usuario INT UNSIGNED DEFAULT NULL;

-- ALTER TABLE movimentacoes_do_caixa CHANGE pedido_id id_pedido INT UNSIGNED DEFAULT NULL;
-- ALTER TABLE movimentacoes_do_caixa CHANGE despesa_id id_despesa INT UNSIGNED DEFAULT NULL;
-- ALTER TABLE movimentacoes_do_caixa CHANGE usuarios_id id_usuario INT UNSIGNED DEFAULT NULL;
-- ALTER TABLE movimentacoes_do_caixa CHANGE caixa_sessao_id id_sessao_do_caixa INT UNSIGNED DEFAULT NULL;

-- ---------------------------------------------------------------------------
-- 6) Sessões de caixa
-- ---------------------------------------------------------------------------
-- ALTER TABLE sessoes_do_caixa CHANGE usuarios_id id_usuario INT UNSIGNED DEFAULT NULL;

-- ---------------------------------------------------------------------------
-- 7) Auditoria
-- ---------------------------------------------------------------------------
-- ALTER TABLE auditoria_de_exclusoes CHANGE entidade nome_entidade VARCHAR(64) NOT NULL;
-- ALTER TABLE auditoria_de_exclusoes CHANGE registro_id id_registro INT UNSIGNED NOT NULL;
-- ALTER TABLE auditoria_de_exclusoes CHANGE usuarios_id id_usuario INT UNSIGNED DEFAULT NULL;

-- ---------------------------------------------------------------------------
-- 8) Recriar FKs com novos nomes (exemplo — valide com information_schema)
-- ---------------------------------------------------------------------------
-- Em caso de nomes de constraints antigos, remova antes:
-- ALTER TABLE adicionais DROP FOREIGN KEY fk_adicionais_categoria;
-- ALTER TABLE adicionais ADD CONSTRAINT fk_adicional_categoria FOREIGN KEY (id_categoria) REFERENCES categorias (id_categoria);

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- NOTA: Em bases novas, prefira importar apenas schema_banco_portugues_completo.sql
-- =============================================================================
