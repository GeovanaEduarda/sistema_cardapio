-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 17/03/2026 às 00:48
-- Versão do servidor: 8.0.45
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `cardapio`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `adicionais`
--

CREATE TABLE `adicionais` (
  `id_adicional` int NOT NULL,
  `nome_adicional` varchar(45) DEFAULT NULL,
  `valor_adicional` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `adicionais`
--

INSERT INTO `adicionais` (`id_adicional`, `nome_adicional`, `valor_adicional`) VALUES
(1, 'Bacon Extra', 4.50),
(2, 'Cheddar Fatiado', 3.00),
(3, 'Ovo Frito', 2.50),
(4, 'Cebola Caramelizada', 3.50),
(5, 'Maionese da Casa', 2.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int NOT NULL,
  `nome_categoria` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emoji_categoria` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_categoria` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo_categoria` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nome_categoria`, `emoji_categoria`, `foto_categoria`, `ativo_categoria`) VALUES
(1, 'Hambúrgueres', '🍔', 'burger.jpg', 1),
(2, 'Bebidas', '🥤', 'drinks.jpg', 1),
(3, 'Sobremesas', '🍰', 'dessert.jpg', 1),
(4, 'Porções', '🍟', 'fries.jpg', 1),
(5, 'Pizzas', '🍕', 'pizza.jpg', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int NOT NULL,
  `nome_cliente` varchar(90) NOT NULL,
  `telefone_cliente` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nome_cliente`, `telefone_cliente`) VALUES
(1, 'João Silva', '11999998888'),
(2, 'Maria Oliveira', '11988887777'),
(3, 'Pedro Santos', '11977776666'),
(4, 'Ana Costa', '11966665555'),
(5, 'Lucas Souza', '11955554444');

-- --------------------------------------------------------

--
-- Estrutura para tabela `enderecos`
--

CREATE TABLE `enderecos` (
  `id_endereco` int NOT NULL,
  `numero_endereco` varchar(45) DEFAULT NULL,
  `rua_endereco` varchar(45) DEFAULT NULL,
  `bairro_endereco` varchar(45) DEFAULT NULL,
  `cidade_endereco` varchar(45) DEFAULT NULL,
  `caracteristica_eendereco` varchar(45) DEFAULT NULL,
  `ponto_ref_endereco` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `enderecos`
--

INSERT INTO `enderecos` (`id_endereco`, `numero_endereco`, `rua_endereco`, `bairro_endereco`, `cidade_endereco`, `caracteristica_eendereco`, `ponto_ref_endereco`) VALUES
(1, '123', 'Rua das Flores', 'Centro', 'São Paulo', 'Casa', 'Próximo ao Mercado'),
(2, '45', 'Av. Paulista', 'Bela Vista', 'São Paulo', 'Apto 402', 'Prédio Azul'),
(3, '789', 'Rua XV de Novembro', 'Vila Nova', 'Curitiba', 'Sobrado', 'Esquina com Farmácia'),
(4, '10', 'Alameda Santos', 'Jardins', 'São Paulo', 'Conjunto 5', 'Ao lado do Metrô'),
(5, '55', 'Rua da Paz', 'Interior', 'Campinas', 'Casa de Fundos', 'Portão Branco');

-- --------------------------------------------------------

--
-- Estrutura para tabela `formas_entrega`
--

CREATE TABLE `formas_entrega` (
  `id_forma` int NOT NULL,
  `nome_forma` varchar(90) DEFAULT NULL,
  `adicional_forma` decimal(10,2) DEFAULT NULL,
  `ativo_forma` tinyint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `formas_entrega`
--

INSERT INTO `formas_entrega` (`id_forma`, `nome_forma`, `adicional_forma`, `ativo_forma`) VALUES
(1, 'Retirada no Balcão', 0.00, 1),
(2, 'Entrega de Moto (Raio 5km)', 7.00, 1),
(3, 'Entrega de Moto (Raio 10km)', 12.00, 1),
(4, 'iFood Delivery', 15.00, 1),
(5, 'Drive-Thru', 0.00, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `formas_pagameto`
--

CREATE TABLE `formas_pagameto` (
  `id_pagamento` int NOT NULL,
  `forma_pagameto` varchar(45) DEFAULT NULL,
  `acrecimo_pagamento` decimal(10,2) DEFAULT NULL,
  `desconto_pagamento` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `formas_pagameto`
--

INSERT INTO `formas_pagameto` (`id_pagamento`, `forma_pagameto`, `acrecimo_pagamento`, `desconto_pagamento`) VALUES
(1, 'Dinheiro', 0.00, 5.00),
(2, 'Cartão de Débito', 0.00, 0.00),
(3, 'Cartão de Crédito', 2.50, 0.00),
(4, 'Pix', 0.00, 10.00),
(5, 'Vale Refeição', 1.50, 0.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_cardapio`
--

CREATE TABLE `itens_cardapio` (
  `id_item_cardapio` int NOT NULL,
  `adicionais_id_adicional` int NOT NULL,
  `categorias_id_categoria` int NOT NULL,
  `nome_item` varchar(45) DEFAULT NULL,
  `foto_item` varchar(255) DEFAULT NULL,
  `valor_item` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `itens_cardapio`
--

INSERT INTO `itens_cardapio` (`id_item_cardapio`, `adicionais_id_adicional`, `categorias_id_categoria`, `nome_item`, `foto_item`, `valor_item`) VALUES
(1, 5, 1, 'X-Burger Especial', 'xburger.jpg', 25.90),
(2, 1, 1, 'Bacon Monster', 'bacon.jpg', 32.00),
(3, 5, 2, 'Suco de Laranja 500ml', 'suco.jpg', 12.00),
(4, 5, 4, 'Batata Frita Grande', 'fritas.jpg', 18.50),
(5, 5, 3, 'Petit Gateau', 'petit.jpg', 22.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_pedidos`
--

CREATE TABLE `itens_pedidos` (
  `id_item_pedido` int NOT NULL,
  `itens_cardapio_id_item_cardapio` int NOT NULL,
  `pedidos_id_pedidos` int NOT NULL,
  `adicionais_id_adicional` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `itens_pedidos`
--

INSERT INTO `itens_pedidos` (`id_item_pedido`, `itens_cardapio_id_item_cardapio`, `pedidos_id_pedidos`, `adicionais_id_adicional`) VALUES
(1, 1, 1, 1),
(2, 2, 2, 2),
(3, 3, 3, 5),
(4, 4, 4, 2),
(5, 5, 5, 5);

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedidos` int NOT NULL,
  `clientes_id_cliente` int NOT NULL,
  `status_pedido_id_status_pedido` int NOT NULL,
  `formas_pagameto_id_pagamento` int NOT NULL,
  `formas_entrega_id_forma` int NOT NULL,
  `enderecos_id_endereco` int DEFAULT NULL,
  `usuarios_id_usuario` int DEFAULT NULL,
  `valor_pedido` decimal(10,2) DEFAULT NULL,
  `acrecimo_pedido` decimal(10,2) DEFAULT NULL,
  `desconto_pedido` decimal(10,2) DEFAULT NULL,
  `lancamento_pedido` datetime DEFAULT NULL,
  `conclusao_pedido` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `pedidos`
--

INSERT INTO `pedidos` (`id_pedidos`, `clientes_id_cliente`, `status_pedido_id_status_pedido`, `formas_pagameto_id_pagamento`, `formas_entrega_id_forma`, `enderecos_id_endereco`, `usuarios_id_usuario`, `valor_pedido`, `acrecimo_pedido`, `desconto_pedido`, `lancamento_pedido`, `conclusao_pedido`) VALUES
(1, 1, 4, 4, 2, 1, 2, 32.90, 7.00, 3.29, '2026-03-17 19:00:00', '2026-03-17 19:40:00'),
(2, 2, 4, 1, 1, NULL, 3, 25.90, 0.00, 1.30, '2026-03-17 19:15:00', '2026-03-17 19:30:00'),
(3, 3, 1, 2, 2, 3, 2, 50.50, 7.00, 0.00, '2026-03-17 20:00:00', NULL),
(4, 4, 2, 3, 4, 4, 5, 44.00, 17.50, 0.00, '2026-03-17 20:10:00', NULL),
(5, 5, 5, 4, 2, 5, 3, 18.50, 7.00, 1.85, '2026-03-17 18:00:00', '2026-03-17 18:10:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `status_pedido`
--

CREATE TABLE `status_pedido` (
  `id_status_pedido` int NOT NULL,
  `nome_status_pedido` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `status_pedido`
--

INSERT INTO `status_pedido` (`id_status_pedido`, `nome_status_pedido`) VALUES
(1, 'Pendente'),
(2, 'Em Preparo'),
(3, 'Saiu para Entrega'),
(4, 'Concluído'),
(5, 'Cancelado');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int NOT NULL,
  `nome_usuario` varchar(90) NOT NULL,
  `email_usuario` varchar(90) DEFAULT NULL,
  `senha_usuario` varchar(255) NOT NULL,
  `cargo_usuario` enum('0','1') NOT NULL,
  `ativo_usuario` tinyint DEFAULT NULL,
  `foto_usuario` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nome_usuario`, `email_usuario`, `senha_usuario`, `cargo_usuario`, `ativo_usuario`, `foto_usuario`) VALUES
(1, 'Admin Sistema', 'admin@cardapio.com', 'senha123', '1', 1, 'admin.jpg'),
(2, 'Carlos Garçom', 'carlos@cardapio.com', 'senha123', '0', 1, 'carlos.jpg'),
(3, 'Julia Atendente', 'julia@cardapio.com', 'senha123', '0', 1, 'julia.jpg'),
(4, 'Roberto Gerente', 'roberto@cardapio.com', 'senha123', '1', 1, 'roberto.jpg'),
(5, 'Fernanda Caixa', 'fernanda@cardapio.com', 'senha123', '0', 1, 'fernanda.jpg');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `adicionais`
--
ALTER TABLE `adicionais`
  ADD PRIMARY KEY (`id_adicional`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`);

--
-- Índices de tabela `enderecos`
--
ALTER TABLE `enderecos`
  ADD PRIMARY KEY (`id_endereco`);

--
-- Índices de tabela `formas_entrega`
--
ALTER TABLE `formas_entrega`
  ADD PRIMARY KEY (`id_forma`);

--
-- Índices de tabela `formas_pagameto`
--
ALTER TABLE `formas_pagameto`
  ADD PRIMARY KEY (`id_pagamento`);

--
-- Índices de tabela `itens_cardapio`
--
ALTER TABLE `itens_cardapio`
  ADD PRIMARY KEY (`id_item_cardapio`),
  ADD KEY `fk_itens_cardapio_categorias1_idx` (`categorias_id_categoria`),
  ADD KEY `fk_itens_cardapio_adicionais1_idx` (`adicionais_id_adicional`);

--
-- Índices de tabela `itens_pedidos`
--
ALTER TABLE `itens_pedidos`
  ADD PRIMARY KEY (`id_item_pedido`),
  ADD KEY `fk_itens_cardapio_has_pedidos_pedidos1_idx` (`pedidos_id_pedidos`),
  ADD KEY `fk_itens_cardapio_has_pedidos_itens_cardapio1_idx` (`itens_cardapio_id_item_cardapio`),
  ADD KEY `fk_itens_pedidos_adicionais1_idx` (`adicionais_id_adicional`);

--
-- Índices de tabela `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedidos`),
  ADD KEY `fk_clientes_has_itens_cardapio_clientes_idx` (`clientes_id_cliente`),
  ADD KEY `fk_pedidos_formas_pagameto1_idx` (`formas_pagameto_id_pagamento`),
  ADD KEY `fk_pedidos_formas_entrega1_idx` (`formas_entrega_id_forma`),
  ADD KEY `fk_pedidos_enderecos1_idx` (`enderecos_id_endereco`),
  ADD KEY `fk_pedidos_usuarios1_idx` (`usuarios_id_usuario`),
  ADD KEY `fk_pedidos_status_pedido1_idx` (`status_pedido_id_status_pedido`);

--
-- Índices de tabela `status_pedido`
--
ALTER TABLE `status_pedido`
  ADD PRIMARY KEY (`id_status_pedido`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email_usuario_UNIQUE` (`email_usuario`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `adicionais`
--
ALTER TABLE `adicionais`
  MODIFY `id_adicional` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `enderecos`
--
ALTER TABLE `enderecos`
  MODIFY `id_endereco` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `formas_entrega`
--
ALTER TABLE `formas_entrega`
  MODIFY `id_forma` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `formas_pagameto`
--
ALTER TABLE `formas_pagameto`
  MODIFY `id_pagamento` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `itens_cardapio`
--
ALTER TABLE `itens_cardapio`
  MODIFY `id_item_cardapio` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `itens_pedidos`
--
ALTER TABLE `itens_pedidos`
  MODIFY `id_item_pedido` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedidos` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `itens_cardapio`
--
ALTER TABLE `itens_cardapio`
  ADD CONSTRAINT `fk_itens_cardapio_adicionais1` FOREIGN KEY (`adicionais_id_adicional`) REFERENCES `adicionais` (`id_adicional`),
  ADD CONSTRAINT `fk_itens_cardapio_categorias1` FOREIGN KEY (`categorias_id_categoria`) REFERENCES `categorias` (`id_categoria`);

--
-- Restrições para tabelas `itens_pedidos`
--
ALTER TABLE `itens_pedidos`
  ADD CONSTRAINT `fk_itens_cardapio_has_pedidos_itens_cardapio1` FOREIGN KEY (`itens_cardapio_id_item_cardapio`) REFERENCES `itens_cardapio` (`id_item_cardapio`),
  ADD CONSTRAINT `fk_itens_cardapio_has_pedidos_pedidos1` FOREIGN KEY (`pedidos_id_pedidos`) REFERENCES `pedidos` (`id_pedidos`),
  ADD CONSTRAINT `fk_itens_pedidos_adicionais1` FOREIGN KEY (`adicionais_id_adicional`) REFERENCES `adicionais` (`id_adicional`);

--
-- Restrições para tabelas `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_clientes_has_itens_cardapio_clientes` FOREIGN KEY (`clientes_id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `fk_pedidos_enderecos1` FOREIGN KEY (`enderecos_id_endereco`) REFERENCES `enderecos` (`id_endereco`),
  ADD CONSTRAINT `fk_pedidos_formas_entrega1` FOREIGN KEY (`formas_entrega_id_forma`) REFERENCES `formas_entrega` (`id_forma`),
  ADD CONSTRAINT `fk_pedidos_formas_pagameto1` FOREIGN KEY (`formas_pagameto_id_pagamento`) REFERENCES `formas_pagameto` (`id_pagamento`),
  ADD CONSTRAINT `fk_pedidos_status_pedido1` FOREIGN KEY (`status_pedido_id_status_pedido`) REFERENCES `status_pedido` (`id_status_pedido`),
  ADD CONSTRAINT `fk_pedidos_usuarios1` FOREIGN KEY (`usuarios_id_usuario`) REFERENCES `usuarios` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
