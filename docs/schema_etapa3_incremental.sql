-- =============================================================================
-- SCHEMA INCREMENTAL — ETAPA 3 (caixa / sessão / vínculo em movimentações)
-- =============================================================================
-- Execute após docs/schema_etapa2_alinhado.sql
-- Idempotente na medida do possível (verifica coluna antes de ALTER).
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS caixa_sessoes (
  id_sessao INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuarios_id INT UNSIGNED DEFAULT NULL,
  aberto_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fechado_em DATETIME DEFAULT NULL,
  saldo_informado_abertura DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  saldo_informado_fechamento DECIMAL(12,2) DEFAULT NULL,
  observacao_abertura VARCHAR(500) DEFAULT NULL,
  observacao_fechamento VARCHAR(500) DEFAULT NULL,
  PRIMARY KEY (id_sessao),
  KEY idx_caixa_aberto (fechado_em),
  KEY fk_caixa_sess_usuario (usuarios_id),
  CONSTRAINT fk_caixa_sess_usuario FOREIGN KEY (usuarios_id) REFERENCES usuarios_admin (id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Coluna opcional em movimentações (vínculo com sessão de caixa aberta)
SET @db := DATABASE();
SET @col_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'movimentacoes_caixa' AND COLUMN_NAME = 'caixa_sessao_id'
);
SET @sql := IF(
  @col_exists = 0,
  'ALTER TABLE movimentacoes_caixa ADD COLUMN caixa_sessao_id INT UNSIGNED NULL DEFAULT NULL AFTER usuarios_id, ADD KEY idx_mov_caixa_sess (caixa_sessao_id)',
  'SELECT 1 AS coluna_caixa_sessao_id_ja_existe'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- FK opcional (pode falhar se dados órfãos; comente se necessário)
SET @fk_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = @db AND TABLE_NAME = 'movimentacoes_caixa' AND CONSTRAINT_NAME = 'fk_mov_caixa_sessao'
);
SET @sqlfk := IF(
  @fk_exists = 0,
  'ALTER TABLE movimentacoes_caixa ADD CONSTRAINT fk_mov_caixa_sessao FOREIGN KEY (caixa_sessao_id) REFERENCES caixa_sessoes (id_sessao) ON DELETE SET NULL',
  'SELECT 1 AS fk_mov_caixa_sessao_ja_existe'
);
PREPARE stmt2 FROM @sqlfk;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- FIM
-- =============================================================================
