-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 04/11/2025 às 14:22
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
-- Banco de dados: `if0_40131532_bdcarona`
--
CREATE DATABASE IF NOT EXISTS `if0_40131532_bdcarona` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `if0_40131532_bdcarona`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `carona`
--

DROP TABLE IF EXISTS `carona`;
CREATE TABLE `carona` (
  `motorista_id` varchar(255) NOT NULL,
  `motorista_nome` varchar(255) NOT NULL,
  `origem` varchar(255) NOT NULL,
  `destino` varchar(255) NOT NULL,
  `horario` varchar(50) NOT NULL,
  `data` date NOT NULL,
  `vagas_total` int(11) NOT NULL,
  `vagas_disponiveis` int(11) DEFAULT NULL,
  `passageiros` text DEFAULT NULL,
  `carro` varchar(100) NOT NULL,
  `observacoes` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ativa',
  `codigo_carona` varchar(30) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `carona`
--

INSERT INTO `carona` (`motorista_id`, `motorista_nome`, `origem`, `destino`, `horario`, `data`, `vagas_total`, `vagas_disponiveis`, `passageiros`, `carro`, `observacoes`, `status`, `codigo_carona`, `criado_em`) VALUES
('10', '', 'ConÃªgo Nazareno', 'ETEC Ipaussu', '07:00', '2025-11-02', 4, 3, NULL, 'Ônibus Convencional', '0', 'ativo', 'CRN-FPRG51', '2025-11-02 06:54:39');

-- --------------------------------------------------------

--
-- Estrutura para tabela `solicitacao_carona`
--

DROP TABLE IF EXISTS `solicitacao_carona`;
CREATE TABLE `solicitacao_carona` (
  `id` int(11) NOT NULL,
  `codigo_carona` varchar(30) NOT NULL,
  `passageiro_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'ativa',
  `data_solicitacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `ID` int(11) NOT NULL,
  `nome` varchar(30) NOT NULL,
  `email` varchar(30) NOT NULL,
  `senha` varchar(16) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `carro` varchar(30) NOT NULL,
  `tipo` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`ID`, `nome`, `email`, `senha`, `telefone`, `carro`, `tipo`) VALUES
(2, 'Visitante ETEC', 'visitanteetec@gmail.com', 'motorista', '14999999999', '', 'Motorista'),
(1, 'Visitante ETEC', 'visitanteetec@gmail.com', 'visitante', '14999999999', '', 'Aluno'),
(2, 'Motorista ETEC', 'motoristaetec@gmail.com', 'motorista', '14999999999', 'Ônibus Convencional', 'Motorista'),
(0, 'isa', 'isa.@isa', '$2y$10$nNXtrkhFs', '111111111111', '', 'aluno'),
(0, 'isa', 'qqq@q', '$2y$10$9OfIiwmB1', '11111111111111111111', '', 'aluno'),
(10, 'Motorista ETEC', 'motorista@gmail.com', 'motorista', '14999999999', 'Ônibus Convencional', 'motorista'),
(0, 'Sophia Teixeira', 'Sophiateixeira247@gmail.com', '$2y$10$7/u7bc6Vb', '(14) 99703-0465', '', 'aluno'),
(0, 'aaa', 'a@gmail.com', '$2y$10$Hl7WxO1Yo', '(14) 99703-0465', '', 'aluno'),
(0, '123', '123@gamil.ads', '$2y$10$I1.oQGdaE', '123', '', 'aluno'),
(0, '123', '123@gmail.com', '$2y$10$ag9NpKTDX', '123', '', 'aluno'),
(0, '1', '1@gmail.com', '$2y$10$A/UfVY7aW', '1', '', 'aluno'),
(0, 'Gabriel', 'moongab@gmail.com', '$2y$10$Oku1dWtQv', '14998765451', '', 'aluno');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
