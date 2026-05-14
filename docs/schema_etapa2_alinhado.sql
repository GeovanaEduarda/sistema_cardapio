-- =============================================================================
-- SCHEMA ALINHADO — ETAPA 2 (sistema_cardapio / banco: cardapio)
-- =============================================================================
-- IMPORTANTE:
-- 1) Faça BACKUP completo antes de executar em base com dados.
-- 2) Em base NOVA ou após backup, pode executar o bloco inteiro.
-- 3) Se já existirem tabelas antigas com nomes iguais e estrutura diferente,
--    renomeie-as manualmente (ex.: pedidos -> pedidos_legado) antes de criar.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- Usuários (painel / login) — password_hash obrigatório na coluna senha
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios_admin (
  id_usuario INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(120) NOT NULL,
  login VARCHAR(120) NOT NULL,
  senha VARCHAR(255) NOT NULL,
  nivel VARCHAR(32) NOT NULL DEFAULT 'comum',
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  imagem_url VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id_usuario),
  UNIQUE KEY uq_usuarios_login (login)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Senha inicial documentada: "password" (troque em produção imediatamente).
-- Hash bcrypt (PASSWORD_DEFAULT / PHP).
INSERT IGNORE INTO usuarios_admin (nome, login, senha, nivel, ativo)
VALUES ('Administrador', 'admin@cardapio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- ---------------------------------------------------------------------------
-- Cardápio
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias (
  id_categoria INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome_categoria VARCHAR(120) NOT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- ---------------------------------------------------------------------------
-- Clientes e pedidos
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes (
  id_cliente INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(120) NOT NULL,
  telefone VARCHAR(45) DEFAULT NULL,
  rua VARCHAR(120) DEFAULT NULL,
  numero VARCHAR(45) DEFAULT NULL,
  bairro VARCHAR(120) DEFAULT NULL,
  PRIMARY KEY (id_cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedidos (
  id_pedido INT UNSIGNED NOT NULL AUTO_INCREMENT,
  clientes_id INT UNSIGNED DEFAULT NULL,
  valor_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  origem VARCHAR(32) NOT NULL DEFAULT 'balcao',
  identificador_mesa VARCHAR(60) DEFAULT NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'pendente',
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

-- ---------------------------------------------------------------------------
-- Financeiro / caixa (base para relatórios e gráficos nas próximas etapas)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS despesas (
  id_despesa INT UNSIGNED NOT NULL AUTO_INCREMENT,
  fornecedor VARCHAR(160) NOT NULL,
  valor DECIMAL(10,2) NOT NULL,
  data_lancamento DATE NOT NULL,
  tipo ENUM('fixa','variavel') NOT NULL DEFAULT 'variavel',
  observacao TEXT,
  usuarios_id INT UNSIGNED DEFAULT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_despesa),
  KEY fk_despesa_usuario (usuarios_id),
  CONSTRAINT fk_despesa_usuario FOREIGN KEY (usuarios_id) REFERENCES usuarios_admin (id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS movimentacoes_caixa (
  id_movimentacao INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tipo ENUM('entrada','saida') NOT NULL,
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

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- FIM — Após importar categorias/produtos de legado, relacione FKs conforme IDs.
-- =============================================================================
