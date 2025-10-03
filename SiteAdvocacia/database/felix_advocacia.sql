-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 02/10/2025 às 20:38
-- Versão do servidor: 9.1.0
-- Versão do PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `felix_advocacia`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `advogados`
--

DROP TABLE IF EXISTS `advogados`;
CREATE TABLE IF NOT EXISTS `advogados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `oab` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `especialidade` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `advogados`
--

INSERT INTO `advogados` (`id`, `nome`, `email`, `oab`, `password`, `telefone`, `especialidade`, `created_at`, `updated_at`) VALUES
(1, 'Dra. Eliene Felix', 'felixrodriguesadv@gmail.com', 'OAB/SP 399633', '$2y$10$MbzTqvuq8nd9/80VmjKfwej78BEDZuRfSiYlnP2y9p92o7I1zxuT.', '(11) 9999-9999', 'Direito Civil, Tributário, Imobiliário e Trabalhista.', '2025-10-02 17:46:46', '2025-10-02 18:18:15'),
(2, 'Dra. Ana Silva', 'ana.silva@felixadvocacia.com.br', '0', '$2y$10$MbzTqvuq8nd9/80VmjKfwej78BEDZuRfSiYlnP2y9p92o7I1zxuT.', '(11) 8888-8888', 'Direito Trabalhista', '2025-10-02 17:46:46', '2025-10-02 17:49:29'),
(3, 'Dr. Carlos Oliveira', 'carlos.oliveira@felixadvocacia.com.br', '0', '$2y$10$MbzTqvuq8nd9/80VmjKfwej78BEDZuRfSiYlnP2y9p92o7I1zxuT.', '(11) 7777-7777', 'Direito de Família', '2025-10-02 17:46:46', '2025-10-02 17:49:29'),
(4, 'Dra. Mariana Santos', 'mariana.santos@felixadvocacia.com.br', '0', '$2y$10$MbzTqvuq8nd9/80VmjKfwej78BEDZuRfSiYlnP2y9p92o7I1zxuT.', '(11) 6666-6666', 'Direito Criminal', '2025-10-02 17:46:46', '2025-10-02 17:49:29');

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

DROP TABLE IF EXISTS `agendamentos`;
CREATE TABLE IF NOT EXISTS `agendamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int DEFAULT NULL,
  `advogado_id` int DEFAULT NULL,
  `data_agendamento` datetime DEFAULT NULL,
  `tipo_consulta` enum('presencial','online','telefonica') DEFAULT NULL,
  `descricao` text,
  `status` enum('agendado','confirmado','cancelado','concluido') DEFAULT 'agendado',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `advogado_id` (`advogado_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `agendamentos`
--

INSERT INTO `agendamentos` (`id`, `cliente_id`, `advogado_id`, `data_agendamento`, `tipo_consulta`, `descricao`, `status`, `created_at`) VALUES
(1, 1, 1, '2025-10-30 09:00:00', 'telefonica', 'Testando o agendamento', 'agendado', '2025-10-02 18:25:40');

-- --------------------------------------------------------

--
-- Estrutura para tabela `audiencias`
--

DROP TABLE IF EXISTS `audiencias`;
CREATE TABLE IF NOT EXISTS `audiencias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_caso` int NOT NULL,
  `data_audiencia` datetime NOT NULL,
  `local` varchar(200) DEFAULT NULL,
  `descricao` text,
  `resultado` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_caso` (`id_caso`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `audiencias`
--

INSERT INTO `audiencias` (`id`, `id_caso`, `data_audiencia`, `local`, `descricao`, `resultado`, `created_at`) VALUES
(1, 1, '2023-11-15 09:00:00', 'Fórum Trabalhista Central - Sala 205', 'Audiência de conciliação inicial', NULL, '2025-10-02 17:46:46'),
(2, 2, '2023-11-20 14:30:00', 'Fórum da Família - Sala 103', 'Audiência de instrução e julgamento', NULL, '2025-10-02 17:46:46');

-- --------------------------------------------------------

--
-- Estrutura para tabela `casos`
--

DROP TABLE IF EXISTS `casos`;
CREATE TABLE IF NOT EXISTS `casos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) NOT NULL,
  `descricao` text,
  `numero_processo` varchar(50) DEFAULT NULL,
  `status` enum('aberto','em_andamento','concluido','arquivado') DEFAULT 'aberto',
  `id_cliente` int NOT NULL,
  `id_advogado` int NOT NULL,
  `data_abertura` date DEFAULT NULL,
  `data_vencimento` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_processo` (`numero_processo`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_advogado` (`id_advogado`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `casos`
--

INSERT INTO `casos` (`id`, `titulo`, `descricao`, `numero_processo`, `status`, `id_cliente`, `id_advogado`, `data_abertura`, `data_vencimento`, `created_at`, `updated_at`) VALUES
(1, 'Processo Trabalhista - Horas Extras', 'Processo relacionado ao não pagamento de horas extras trabalhadas', '0012345-68.2023.5.02.0001', 'em_andamento', 1, 2, '2023-01-15', '2024-01-15', '2025-10-02 17:46:46', '2025-10-02 17:46:46'),
(2, 'Divórcio Consensual', 'Processo de divórcio consensual com partilha de bens', '0023456-79.2023.8.26.0001', 'aberto', 2, 3, '2023-02-20', '2023-12-20', '2025-10-02 17:46:46', '2025-10-02 17:46:46'),
(3, 'Defesa Criminal - Roubo', 'Processo de defesa em caso de acusação de roubo', '0034567-89.2023.7.02.0001', 'em_andamento', 3, 4, '2023-03-10', '2024-03-10', '2025-10-02 17:46:46', '2025-10-02 17:46:46');

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullName` varchar(255) NOT NULL,
  `birthDate` date NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `city` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `secondaryPhone` varchar(20) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `howFound` varchar(50) DEFAULT NULL,
  `newsletter` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf` (`cpf`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `fullName`, `birthDate`, `cpf`, `city`, `phone`, `secondaryPhone`, `email`, `password`, `howFound`, `newsletter`, `created_at`) VALUES
(1, 'Christian Rodrigues da Silva', '1997-03-21', '45196506829', 'São Paulo', '31980550613', '31980550613', 'christianrodrgs@gmail.com', '$2y$10$IoYTf8MDCWq6cnJujctuguXBcsK/zZ2x87SrGLrCsfX6VXnUuA.sW', 'search', 1, '2025-10-02 15:59:18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `conversas`
--

DROP TABLE IF EXISTS `conversas`;
CREATE TABLE IF NOT EXISTS `conversas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int NOT NULL,
  `advogado_id` int NOT NULL,
  `assunto` varchar(255) DEFAULT NULL,
  `status` enum('aberta','fechada') DEFAULT 'aberta',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `advogado_id` (`advogado_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `conversas`
--

INSERT INTO `conversas` (`id`, `cliente_id`, `advogado_id`, `assunto`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Testando o chat', 'aberta', '2025-10-02 17:22:58', '2025-10-02 18:26:10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `documentos`
--

DROP TABLE IF EXISTS `documentos`;
CREATE TABLE IF NOT EXISTS `documentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_caso` int NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descricao` text,
  `arquivo` varchar(255) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `data_upload` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_caso` (`id_caso`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `documentos`
--

INSERT INTO `documentos` (`id`, `id_caso`, `titulo`, `descricao`, `arquivo`, `tipo`, `data_upload`) VALUES
(1, 1, 'Petição Inicial', 'Petição inicial do processo trabalhista', NULL, 'peticao', '2025-10-02 17:46:46'),
(2, 1, 'Contrato de Trabalho', 'Cópia do contrato de trabalho do reclamante', NULL, 'contrato', '2025-10-02 17:46:46');

-- --------------------------------------------------------

--
-- Estrutura para tabela `mensagens`
--

DROP TABLE IF EXISTS `mensagens`;
CREATE TABLE IF NOT EXISTS `mensagens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `conversa_id` int NOT NULL,
  `remetente` enum('cliente','advogado') NOT NULL,
  `mensagem` text NOT NULL,
  `lida` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `conversa_id` (`conversa_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `mensagens`
--

INSERT INTO `mensagens` (`id`, `conversa_id`, `remetente`, `mensagem`, `lida`, `created_at`) VALUES
(1, 1, 'cliente', 't', 1, '2025-10-02 17:23:29'),
(2, 1, 'cliente', 'estou testando agora para ver se não vai dar refresh', 1, '2025-10-02 17:31:18'),
(3, 1, 'cliente', 'teste', 1, '2025-10-02 18:06:13'),
(4, 1, 'advogado', 'a', 1, '2025-10-02 18:09:04'),
(5, 1, 'advogado', 'teste', 1, '2025-10-02 18:26:06'),
(6, 1, 'advogado', 'testeee', 1, '2025-10-02 18:26:10'),
(7, 1, 'advogado', 'ola', 1, '2025-10-02 20:07:18'),
(8, 1, 'advogado', 'oi', 1, '2025-10-02 20:22:57');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `endereco` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `telefone`, `cpf`, `endereco`, `created_at`) VALUES
(1, 'João da Silva', 'joao.silva@email.com', '(11) 1111-1111', '123.456.789-00', 'Rua das Flores, 123 - Centro - São Paulo/SP', '2025-10-02 17:46:46'),
(2, 'Ana Oliveira', 'ana.oliveira@email.com', '(11) 2222-2222', '987.654.321-00', 'Av. Paulista, 1000 - Bela Vista - São Paulo/SP', '2025-10-02 17:46:46'),
(3, 'Pedro Costa', 'pedro.costa@email.com', '(11) 3333-3333', '456.789.123-00', 'Rua Augusta, 500 - Consolação - São Paulo/SP', '2025-10-02 17:46:46');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
