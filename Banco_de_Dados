-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 07/08/2026 às 19:57
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `legends_games_1`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `biblioteca`
--

CREATE TABLE `biblioteca` (
  `ID_Biblioteca` int(11) NOT NULL,
  `ID_usuario` int(11) NOT NULL,
  `ID_jogo` int(11) NOT NULL,
  `Data_Aquisicao` date DEFAULT NULL,
  `Horas_Jogadas` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categoria`
--

CREATE TABLE `categoria` (
  `ID_Categoria` int(11) NOT NULL,
  `Nome_Categoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categoria`
--

INSERT INTO `categoria` (`ID_Categoria`, `Nome_Categoria`) VALUES
(1, 'Ação');

-- --------------------------------------------------------

--
-- Estrutura para tabela `classificacao`
--

CREATE TABLE `classificacao` (
  `ID_Classificacao` int(11) NOT NULL,
  `ID_usuario` int(11) DEFAULT NULL,
  `ID_jogo` int(11) DEFAULT NULL,
  `Avaliacao` int(11) DEFAULT NULL,
  `Resenha` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `compra`
--

CREATE TABLE `compra` (
  `ID_Compra` int(11) NOT NULL,
  `ID_usuario` int(11) DEFAULT NULL,
  `ID_pagamento` int(11) DEFAULT NULL,
  `Data_Compra` date DEFAULT NULL,
  `Valor_total` decimal(10,2) DEFAULT NULL,
  `Desconto` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuração`
--

CREATE TABLE `configuração` (
  `ID_usuario` int(11) NOT NULL,
  `ID_emal` int(11) NOT NULL,
  `ID_senha` int(11) NOT NULL,
  `ID_cpf` varchar(11) NOT NULL,
  `ID_celular` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_compra`
--

CREATE TABLE `itens_compra` (
  `ID_item` int(11) NOT NULL,
  `ID_Compra` int(11) DEFAULT NULL,
  `ID_jogo` int(11) DEFAULT NULL,
  `Quantidade` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `jogo`
--

CREATE TABLE `jogo` (
  `ID_jogo` int(11) NOT NULL,
  `Nome` varchar(100) NOT NULL,
  `Descricao` varchar(1000) DEFAULT NULL,
  `Video_Demonstrativo` varchar(255) DEFAULT NULL,
  `Capa` varchar(255) DEFAULT NULL,
  `Preco_Unitario` decimal(10,2) NOT NULL,
  `Classificacao_Etaria` int(11) DEFAULT NULL,
  `ID_Categoria` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `jogo`
--

INSERT INTO `jogo` (`ID_jogo`, `Nome`, `Descricao`, `Video_Demonstrativo`, `Capa`, `Preco_Unitario`, `Classificacao_Etaria`, `ID_Categoria`) VALUES
(1, 'Cyber Adventure', 'Jogo de aventura futurista', 'link_video', 'capa1.jpg', 199.90, 16, 1),
(2, 'Cyberpunk 2077', '', '0', 'https://cdn.cloudflare.steamstatic.com/steam/apps/1091500/header.jpg', 0.00, 0, 1),
(3, 'GTA V', '', '0', 'https://cdn.cloudflare.steamstatic.com/steam/apps/271590/header.jpg', 0.00, 0, 1),
(4, 'Red Dead Redemption 2', '', '0', 'https://cdn.cloudflare.steamstatic.com/steam/apps/1174180/header.jpg', 0.00, 0, 1),
(5, 'The Witcher 3', '', '0', 'https://cdn.cloudflare.steamstatic.com/steam/apps/292030/header.jpg', 0.00, 0, 1),
(6, 'Elden Ring', '', '0', 'https://cdn.cloudflare.steamstatic.com/steam/apps/1245620/header.jpg', 0.00, 0, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamento`
--

CREATE TABLE `pagamento` (
  `ID_pagamento` int(11) NOT NULL,
  `Forma_Pagamento` varchar(50) DEFAULT NULL,
  `Autenticacao` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `ID_usuario` int(11) NOT NULL,
  `Nome` varchar(60) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Senha` varchar(255) NOT NULL,
  `Perfil` varchar(60) DEFAULT NULL,
  `Amigos` int(11) DEFAULT 0,
  `cpf` varchar(11) NOT NULL,
  `celular` varchar(20) NOT NULL,
  `Nome_Exibicao` varchar(100) DEFAULT NULL,
  `Biografia` text DEFAULT NULL,
  `Foto` varchar(255) DEFAULT NULL,
  `Tema` varchar(20) DEFAULT 'escuro',
  `Idioma` varchar(20) DEFAULT 'pt-BR',
  `Notificacoes` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`ID_usuario`, `Nome`, `Email`, `Senha`, `Perfil`, `Amigos`, `cpf`, `celular`, `Nome_Exibicao`, `Biografia`, `Foto`, `Tema`, `Idioma`, `Notificacoes`) VALUES
(29, 'Usuário', 'cristhianferreiraleide@gmail.com', '$2y$10$jwP7HtN.zphbzhMjGnPF7e2gFCmxC4fkiX.f5jouKSrBV571bIrzy', NULL, 0, '', '', NULL, NULL, NULL, 'escuro', 'pt-BR', 1);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `biblioteca`
--
ALTER TABLE `biblioteca`
  ADD PRIMARY KEY (`ID_Biblioteca`),
  ADD KEY `ID_usuario` (`ID_usuario`),
  ADD KEY `ID_jogo` (`ID_jogo`);

--
-- Índices de tabela `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`ID_Categoria`);

--
-- Índices de tabela `classificacao`
--
ALTER TABLE `classificacao`
  ADD PRIMARY KEY (`ID_Classificacao`),
  ADD KEY `ID_usuario` (`ID_usuario`),
  ADD KEY `ID_jogo` (`ID_jogo`);

--
-- Índices de tabela `compra`
--
ALTER TABLE `compra`
  ADD PRIMARY KEY (`ID_Compra`),
  ADD KEY `ID_usuario` (`ID_usuario`),
  ADD KEY `ID_pagamento` (`ID_pagamento`);

--
-- Índices de tabela `itens_compra`
--
ALTER TABLE `itens_compra`
  ADD PRIMARY KEY (`ID_item`),
  ADD KEY `ID_Compra` (`ID_Compra`),
  ADD KEY `ID_jogo` (`ID_jogo`);

--
-- Índices de tabela `jogo`
--
ALTER TABLE `jogo`
  ADD PRIMARY KEY (`ID_jogo`),
  ADD KEY `ID_Categoria` (`ID_Categoria`);

--
-- Índices de tabela `pagamento`
--
ALTER TABLE `pagamento`
  ADD PRIMARY KEY (`ID_pagamento`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`ID_usuario`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `biblioteca`
--
ALTER TABLE `biblioteca`
  MODIFY `ID_Biblioteca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `categoria`
--
ALTER TABLE `categoria`
  MODIFY `ID_Categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `classificacao`
--
ALTER TABLE `classificacao`
  MODIFY `ID_Classificacao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `compra`
--
ALTER TABLE `compra`
  MODIFY `ID_Compra` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `itens_compra`
--
ALTER TABLE `itens_compra`
  MODIFY `ID_item` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `jogo`
--
ALTER TABLE `jogo`
  MODIFY `ID_jogo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `pagamento`
--
ALTER TABLE `pagamento`
  MODIFY `ID_pagamento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `ID_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `biblioteca`
--
ALTER TABLE `biblioteca`
  ADD CONSTRAINT `biblioteca_ibfk_1` FOREIGN KEY (`ID_usuario`) REFERENCES `usuario` (`ID_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `biblioteca_ibfk_2` FOREIGN KEY (`ID_jogo`) REFERENCES `jogo` (`ID_jogo`) ON DELETE CASCADE;

--
-- Restrições para tabelas `classificacao`
--
ALTER TABLE `classificacao`
  ADD CONSTRAINT `classificacao_ibfk_1` FOREIGN KEY (`ID_usuario`) REFERENCES `usuario` (`ID_usuario`),
  ADD CONSTRAINT `classificacao_ibfk_2` FOREIGN KEY (`ID_jogo`) REFERENCES `jogo` (`ID_jogo`);

--
-- Restrições para tabelas `compra`
--
ALTER TABLE `compra`
  ADD CONSTRAINT `compra_ibfk_1` FOREIGN KEY (`ID_usuario`) REFERENCES `usuario` (`ID_usuario`),
  ADD CONSTRAINT `compra_ibfk_2` FOREIGN KEY (`ID_pagamento`) REFERENCES `pagamento` (`ID_pagamento`);

--
-- Restrições para tabelas `itens_compra`
--
ALTER TABLE `itens_compra`
  ADD CONSTRAINT `itens_compra_ibfk_1` FOREIGN KEY (`ID_Compra`) REFERENCES `compra` (`ID_Compra`),
  ADD CONSTRAINT `itens_compra_ibfk_2` FOREIGN KEY (`ID_jogo`) REFERENCES `jogo` (`ID_jogo`);

--
-- Restrições para tabelas `jogo`
--
ALTER TABLE `jogo`
  ADD CONSTRAINT `jogo_ibfk_1` FOREIGN KEY (`ID_Categoria`) REFERENCES `categoria` (`ID_Categoria`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
