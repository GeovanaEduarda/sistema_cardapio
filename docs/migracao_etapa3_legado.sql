-- =============================================================================
-- MIGRAÇÃO ETAPA 3 — BANCO LEGADO (dump tipo docs/user_errado_cardapio.sql)
--          → SCHEMA ALINHADO (docs/schema_etapa2_alinhado.sql + Etapa 3)
-- =============================================================================
--
-- PRÉ-REQUISITOS:
--   1) BACKUP completo do banco.
--   2) Este script foi pensado para base ainda no MODELO ANTIGO (ex.: coluna
--      pedidos.id_pedidos). Se você já usa pedidos.id_pedido (Etapa 2), NÃO
--      execute o bloco de RENAME + CREATE — apenas ajuste dados manualmente.
--   3) Recomendado: executar antes docs/schema_etapa3_incremental.sql (caixa).
--
-- O QUE FAZ:
--   A) Detecta modelo legado (tabela pedidos com coluna id_pedidos).
--   B) Renomeia tabelas legadas para *_legado (preserva dados).
--   C) Cria tabelas novas (Etapa 2 + financeiro + auditoria) se ainda não existirem.
--   D) Copia dados (IDs preservados quando possível).
--   E) Cria entradas em movimentacoes_caixa para pedidos concluídos migrados.
--   F) Ajusta senhas: texto curto legado vira hash bcrypt da senha "password"
--      (hash Laravel de testes — troque em produção).
--
-- SENHA PÓS-MIGRAÇÃO (usuários copiados de usuarios legado com senha em texto):
--      Use login = email_usuario legado e senha: password
--      (mesmo hash do seed admin em schema_etapa2, até redefinir no sistema).
--
-- EXECUTE UMA VEZ. Reexecução pode duplicar movimentações se não houver guard.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- Antes de continuar: importe docs/schema_etapa3_incremental.sql (tabela
-- caixa_sessoes + coluna movimentacoes_caixa.caixa_sessao_id). O phpMyAdmin
-- não executa SOURCE de forma confiável; este arquivo não usa SOURCE.
-- ---------------------------------------------------------------------------

-- =============================================================================
-- Bloco dinâmico: renomear legado + criar schema novo
-- =============================================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS sp_etapa3_migrar$$
CREATE PROCEDURE sp_etapa3_migrar()
proc: BEGIN
  DECLARE v_legacy INT DEFAULT 0;
  DECLARE v_legado_tbl INT DEFAULT 0;

  SELECT COUNT(*) INTO v_legacy
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'pedidos'
    AND COLUMN_NAME = 'id_pedidos';

  SELECT COUNT(*) INTO v_legado_tbl
  FROM INFORMATION_SCHEMA.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pedidos_legado';

  IF v_legacy > 0 THEN
    -- Ordem: filhos antes dos pais referenciados (MySQL atualiza metadados FK)
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'itens_pedidos') THEN
      RENAME TABLE itens_pedidos TO itens_pedidos_legado;
    END IF;
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pedidos') THEN
      RENAME TABLE pedidos TO pedidos_legado;
    END IF;
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'itens_cardapio') THEN
      RENAME TABLE itens_cardapio TO itens_cardapio_legado;
    END IF;
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'adicionais') THEN
      RENAME TABLE adicionais TO adicionais_legado;
    END IF;
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categorias') THEN
      RENAME TABLE categorias TO categorias_legado;
    END IF;
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'clientes') THEN
      RENAME TABLE clientes TO clientes_legado;
    END IF;
    IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios') THEN
      RENAME TABLE usuarios TO usuarios_legado;
    END IF;
  END IF;

  -- Cria tabelas novas (IF NOT EXISTS) — alinhado à Etapa 2
  SET @ddl := '
CREATE TABLE IF NOT EXISTS usuarios_admin (
  id_usuario INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(120) NOT NULL,
  login VARCHAR(120) NOT NULL,
  senha VARCHAR(255) NOT NULL,
  nivel VARCHAR(32) NOT NULL DEFAULT ''comum'',
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  imagem_url VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id_usuario),
  UNIQUE KEY uq_usuarios_login (login)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
';
  PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

  SET @ddl := '
CREATE TABLE IF NOT EXISTS categorias (
  id_categoria INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome_categoria VARCHAR(120) NOT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
';
  PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

  SET @ddl := '
CREATE TABLE IF NOT EXISTS adicionais (
  id_adicional INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome_adicional VARCHAR(120) NOT NULL,
  valor_adicional DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  categorias_id INT UNSIGNED NOT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id_adicional),
  KEY fk_adicionais_categoria (categorias_id),
  CONSTRAINT fk_adicionais_categoria FOREIGN KEY (categorias_id) REFERENCES categorias (id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
';
  PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

  SET @ddl := '
CREATE TABLE IF NOT EXISTS itens_cardapio (
  id_item INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(120) NOT NULL,
  descricao TEXT,
  preco DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  imagem_url VARCHAR(255) DEFAULT NULL,
  categorias_id INT UNSIGNED NOT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id_item),
  KEY fk_item_categoria (categorias_id),
  CONSTRAINT fk_item_categoria FOREIGN KEY (categorias_id) REFERENCES categorias (id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
';
  PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

  SET @ddl := '
CREATE TABLE IF NOT EXISTS clientes (
  id_cliente INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(120) NOT NULL,
  telefone VARCHAR(45) DEFAULT NULL,
  rua VARCHAR(120) DEFAULT NULL,
  numero VARCHAR(45) DEFAULT NULL,
  bairro VARCHAR(120) DEFAULT NULL,
  PRIMARY KEY (id_cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
';
  PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

  SET @ddl := '
CREATE TABLE IF NOT EXISTS pedidos (
  id_pedido INT UNSIGNED NOT NULL AUTO_INCREMENT,
  clientes_id INT UNSIGNED DEFAULT NULL,
  valor_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  origem VARCHAR(32) NOT NULL DEFAULT ''balcao'',
  identificador_mesa VARCHAR(60) DEFAULT NULL,
  status VARCHAR(32) NOT NULL DEFAULT ''pendente'',
  data_pedido DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_atualizacao DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  usuarios_id INT UNSIGNED DEFAULT NULL,
  taxa_entrega DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  desconto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  observacoes VARCHAR(500) DEFAULT NULL,
  PRIMARY KEY (id_pedido),
  KEY fk_pedido_cliente (clientes_id),
  KEY fk_pedido_usuario (usuarios_id),
  KEY idx_pedido_status (status),
  KEY idx_pedido_data (data_pedido),
  CONSTRAINT fk_pedido_cliente FOREIGN KEY (clientes_id) REFERENCES clientes (id_cliente) ON DELETE SET NULL,
  CONSTRAINT fk_pedido_usuario FOREIGN KEY (usuarios_id) REFERENCES usuarios_admin (id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
';
  PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

  SET @ddl := '
CREATE TABLE IF NOT EXISTS itens_pedido (
  id_item_pedido INT UNSIGNED NOT NULL AUTO_INCREMENT,
  pedido_id INT UNSIGNED NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  quantidade INT UNSIGNED NOT NULL DEFAULT 1,
  preco_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (id_item_pedido),
  KEY fk_ip_pedido (pedido_id),
  KEY fk_ip_item (item_id),
  CONSTRAINT fk_ip_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos (id_pedido) ON DELETE CASCADE,
  CONSTRAINT fk_ip_item FOREIGN KEY (item_id) REFERENCES itens_cardapio (id_item)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
';
  PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

  SET @ddl := '
CREATE TABLE IF NOT EXISTS itens_pedido_adicionais (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  item_pedido_id INT UNSIGNED NOT NULL,
  adicional_id INT UNSIGNED NOT NULL,
  quantidade INT UNSIGNED NOT NULL DEFAULT 1,
  valor_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (id),
  KEY fk_ipa_item_pedido (item_pedido_id),
  KEY fk_ipa_adicional (adicional_id),
  CONSTRAINT fk_ipa_item_pedido FOREIGN KEY (item_pedido_id) REFERENCES itens_pedido (id_item_pedido) ON DELETE CASCADE,
  CONSTRAINT fk_ipa_adicional FOREIGN KEY (adicional_id) REFERENCES adicionais (id_adicional)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
';
  PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

  SET @ddl := '
CREATE TABLE IF NOT EXISTS despesas (
  id_despesa INT UNSIGNED NOT NULL AUTO_INCREMENT,
  fornecedor VARCHAR(160) NOT NULL,
  valor DECIMAL(10,2) NOT NULL,
  data_lancamento DATE NOT NULL,
  tipo ENUM(''fixa'',''variavel'') NOT NULL DEFAULT ''variavel'',
  observacao TEXT,
  usuarios_id INT UNSIGNED DEFAULT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_despesa),
  KEY fk_despesa_usuario (usuarios_id),
  CONSTRAINT fk_despesa_usuario FOREIGN KEY (usuarios_id) REFERENCES usuarios_admin (id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
';
  PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

  SET @ddl := '
CREATE TABLE IF NOT EXISTS movimentacoes_caixa (
  id_movimentacao INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tipo ENUM(''entrada'',''saida'') NOT NULL,
  origem VARCHAR(64) DEFAULT NULL,
  valor DECIMAL(10,2) NOT NULL,
  descricao VARCHAR(255) DEFAULT NULL,
  pedido_id INT UNSIGNED DEFAULT NULL,
  despesa_id INT UNSIGNED DEFAULT NULL,
  usuarios_id INT UNSIGNED DEFAULT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_movimentacao),
  KEY fk_mov_pedido (pedido_id),
  KEY fk_mov_despesa (despesa_id),
  KEY fk_mov_usuario (usuarios_id),
  CONSTRAINT fk_mov_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos (id_pedido) ON DELETE SET NULL,
  CONSTRAINT fk_mov_despesa FOREIGN KEY (despesa_id) REFERENCES despesas (id_despesa) ON DELETE SET NULL,
  CONSTRAINT fk_mov_usuario FOREIGN KEY (usuarios_id) REFERENCES usuarios_admin (id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
';
  PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

  SET @ddl := '
CREATE TABLE IF NOT EXISTS auditoria_exclusoes (
  id_auditoria BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  entidade VARCHAR(64) NOT NULL,
  registro_id INT UNSIGNED NOT NULL,
  usuarios_id INT UNSIGNED DEFAULT NULL,
  motivo VARCHAR(255) DEFAULT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_auditoria),
  KEY fk_aud_usuario (usuarios_id),
  CONSTRAINT fk_aud_usuario FOREIGN KEY (usuarios_id) REFERENCES usuarios_admin (id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
';
  PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

  IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pedidos_legado') THEN
    SELECT 'Etapa3: pedidos_legado não encontrado; criação de tabelas ok, cópia ignorada.' AS msg;
    LEAVE proc;
  END IF;

  -- Copiar somente se pedidos novo está vazio
  IF (SELECT COUNT(*) FROM pedidos) > 0 THEN
    SELECT 'Etapa3: tabela pedidos já contém dados; não copio do legado (evita duplicação).' AS msg;
    LEAVE proc;
  END IF;

  -- Hash bcrypt para senha curta legado (equivale à string "password" — troque após login)
  SET @hash_reset := '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

  INSERT IGNORE INTO usuarios_admin (id_usuario, nome, login, senha, nivel, ativo, imagem_url)
  SELECT
    u.id_usuario,
    u.nome_usuario,
    COALESCE(NULLIF(TRIM(u.email_usuario), ''), CONCAT('usuario_', u.id_usuario, '@migrado.local')),
    CASE WHEN CHAR_LENGTH(u.senha_usuario) >= 60 THEN u.senha_usuario ELSE @hash_reset END,
    CASE WHEN u.cargo_usuario = '1' THEN 'admin' ELSE 'comum' END,
    COALESCE(u.ativo_usuario, 1),
    u.foto_usuario
  FROM usuarios_legado u;

  INSERT IGNORE INTO clientes (id_cliente, nome, telefone, rua, numero, bairro)
  SELECT c.id_cliente, c.nome_cliente, c.telefone_cliente, NULL, NULL, NULL
  FROM clientes_legado c;

  INSERT IGNORE INTO categorias (id_categoria, nome_categoria, ativo)
  SELECT c.id_categoria, c.nome_categoria, COALESCE(c.ativo_categoria, 1)
  FROM categorias_legado c;

  -- Adicionais legados sem categoria: amarra na primeira categoria existente
  INSERT IGNORE INTO adicionais (id_adicional, nome_adicional, valor_adicional, categorias_id, ativo)
  SELECT a.id_adicional,
         COALESCE(NULLIF(TRIM(a.nome_adicional), ''), 'Adicional'),
         COALESCE(a.valor_adicional, 0),
         (SELECT MIN(id_categoria) FROM categorias),
         1
  FROM adicionais_legado a;

  INSERT IGNORE INTO itens_cardapio (id_item, nome, descricao, preco, imagem_url, categorias_id, ativo)
  SELECT ic.id_item_cardapio,
         COALESCE(NULLIF(TRIM(ic.nome_item), ''), 'Item'),
         NULL,
         COALESCE(ic.valor_item, 0),
         ic.foto_item,
         ic.categorias_id_categoria,
         1
  FROM itens_cardapio_legado ic;

  INSERT IGNORE INTO pedidos (
    id_pedido, clientes_id, valor_total, origem, identificador_mesa, status, data_pedido,
    usuarios_id, taxa_entrega, desconto, observacoes
  )
  SELECT
    p.id_pedidos,
    p.clientes_id_cliente,
    GREATEST(0,
      COALESCE(p.valor_pedido, 0) + COALESCE(p.acrecimo_pedido, 0) - COALESCE(p.desconto_pedido, 0)
    ),
    CASE WHEN COALESCE(p.formas_entrega_id_forma, 1) = 1 THEN 'balcao' ELSE 'delivery' END,
    NULL,
    CASE p.status_pedido_id_status_pedido
      WHEN 1 THEN 'pendente'
      WHEN 2 THEN 'em_preparo'
      WHEN 3 THEN 'em_entrega'
      WHEN 4 THEN 'concluido'
      WHEN 5 THEN 'cancelado'
      ELSE 'pendente'
    END,
    COALESCE(p.lancamento_pedido, NOW()),
    p.usuarios_id_usuario,
    COALESCE(p.acrecimo_pedido, 0),
    COALESCE(p.desconto_pedido, 0),
    NULL
  FROM pedidos_legado p;

  INSERT IGNORE INTO itens_pedido (id_item_pedido, pedido_id, item_id, quantidade, preco_unitario, subtotal)
  SELECT
    ip.id_item_pedido,
    ip.pedidos_id_pedidos,
    ip.itens_cardapio_id_item_cardapio,
    1,
    COALESCE(ic.valor_item, 0),
    COALESCE(ic.valor_item, 0)
  FROM itens_pedidos_legado ip
  INNER JOIN itens_cardapio ic ON ic.id_item = ip.itens_cardapio_id_item_cardapio;

  INSERT IGNORE INTO itens_pedido_adicionais (item_pedido_id, adicional_id, quantidade, valor_unitario, subtotal)
  SELECT
    ip.id_item_pedido,
    ip.adicionais_id_adicional,
    1,
    COALESCE(a.valor_adicional, 0),
    COALESCE(a.valor_adicional, 0)
  FROM itens_pedidos_legado ip
  INNER JOIN adicionais a ON a.id_adicional = ip.adicionais_id_adicional;

  -- Recalcular totais (itens + adicionais + taxa - desconto) — alinha com helper PHP
  UPDATE pedidos p
  SET valor_total = GREATEST(0,
    COALESCE((SELECT SUM(ip.subtotal) FROM itens_pedido ip WHERE ip.pedido_id = p.id_pedido), 0)
    + COALESCE((SELECT SUM(ipa.subtotal)
                FROM itens_pedido_adicionais ipa
                INNER JOIN itens_pedido ip2 ON ip2.id_item_pedido = ipa.item_pedido_id
                WHERE ip2.pedido_id = p.id_pedido), 0)
    + COALESCE(p.taxa_entrega, 0) - COALESCE(p.desconto, 0)
  );

  -- Entradas de caixa para pedidos concluídos (origem pedido) — evita duplicar
  INSERT INTO movimentacoes_caixa (tipo, origem, valor, descricao, pedido_id, usuarios_id, criado_em)
  SELECT
    'entrada',
    'pedido',
    p.valor_total,
    CONCAT('Migração Etapa 3 — pedido #', p.id_pedido),
    p.id_pedido,
    p.usuarios_id,
    COALESCE(pl.conclusao_pedido, pl.lancamento_pedido, NOW())
  FROM pedidos p
  INNER JOIN pedidos_legado pl ON pl.id_pedidos = p.id_pedido
  WHERE p.status = 'concluido'
    AND NOT EXISTS (
      SELECT 1 FROM movimentacoes_caixa m
      WHERE m.pedido_id = p.id_pedido AND m.tipo = 'entrada' AND m.origem = 'pedido'
    );

  SELECT 'Etapa3: migração de dados concluída. Revise pedidos e movimentacoes_caixa.' AS msg;
END$$

DELIMITER ;

CALL sp_etapa3_migrar();
DROP PROCEDURE IF EXISTS sp_etapa3_migrar;

-- Ajuste AUTO_INCREMENT (best effort)
SET @m := (SELECT IFNULL(MAX(id_pedido), 0) + 1 FROM pedidos);
SET @sql := CONCAT('ALTER TABLE pedidos AUTO_INCREMENT = ', @m);
PREPARE ai FROM @sql; EXECUTE ai; DEALLOCATE PREPARE ai;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- FIM
-- =============================================================================
