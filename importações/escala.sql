-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           8.4.7 - MySQL Community Server - GPL
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Copiando estrutura do banco de dados para cuidar_no_lar
CREATE DATABASE IF NOT EXISTS `cuidar_no_lar` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `cuidar_no_lar`;

-- Copiando estrutura para tabela cuidar_no_lar.tb_contratos_paciente
CREATE TABLE IF NOT EXISTS `tb_contratos_paciente` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `paciente_id` int NOT NULL,
  `tipo_servico` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor_mensal` decimal(12,2) NOT NULL,
  `dia_vencimento` tinyint unsigned NOT NULL DEFAULT '10',
  `forma_pagamento` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vigencia_inicio` date NOT NULL,
  `vigencia_fim` date DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Ativo',
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_contratos_paciente_uuid` (`uuid`),
  KEY `idx_contratos_paciente` (`paciente_id`),
  KEY `idx_contratos_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela cuidar_no_lar.tb_contratos_paciente: ~1 rows (aproximadamente)
INSERT INTO `tb_contratos_paciente` (`id`, `uuid`, `paciente_id`, `tipo_servico`, `valor_mensal`, `dia_vencimento`, `forma_pagamento`, `vigencia_inicio`, `vigencia_fim`, `status`, `observacoes`, `created_at`) VALUES
	(1, '6ae2639f-52ac-11f1-a16a-089798669242', 2, 'Cuidados 24h', 3000.00, 10, 'PIX', '2026-01-01', '2026-12-31', 'Ativo', NULL, '2026-05-13 23:50:40');

-- Copiando estrutura para tabela cuidar_no_lar.tb_escala_ocorrencias
CREATE TABLE IF NOT EXISTS `tb_escala_ocorrencias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `escala_base_id` int DEFAULT NULL,
  `paciente_id` int NOT NULL,
  `cuidador_id` int NOT NULL,
  `data_plantao` date NOT NULL,
  `inicio` datetime NOT NULL,
  `fim` datetime NOT NULL,
  `tipo_plantao` enum('24h','12h','8h','6h') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `status` enum('previsto','confirmado','em_andamento','finalizado','faltou','cancelado','substituido') DEFAULT 'previsto',
  `conflito` tinyint(1) DEFAULT '0',
  `cobertura_incompleta` tinyint(1) DEFAULT '0',
  `observacoes` text,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `origem` enum('Automatica','Manual','Substituicao') DEFAULT 'Automatica',
  PRIMARY KEY (`id`),
  KEY `fk_tb_ocorrencia_escala` (`escala_base_id`),
  KEY `fk_tb_ocorrencia_paciente` (`paciente_id`),
  KEY `idx_tb_ocorrencia_data` (`data_plantao`),
  KEY `idx_tb_ocorrencia_cuidador` (`cuidador_id`),
  KEY `idx_tb_ocorrencia_inicio_fim` (`inicio`,`fim`),
  KEY `idx_tb_ocorrencia_status` (`status`),
  CONSTRAINT `fk_tb_ocorrencia_cuidador` FOREIGN KEY (`cuidador_id`) REFERENCES `tb_cuidador` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tb_ocorrencia_escala` FOREIGN KEY (`escala_base_id`) REFERENCES `tb_escala_base` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tb_ocorrencia_paciente` FOREIGN KEY (`paciente_id`) REFERENCES `tb_pacientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_escala_ocorrencias: ~9 rows (aproximadamente)
INSERT INTO `tb_escala_ocorrencias` (`id`, `escala_base_id`, `paciente_id`, `cuidador_id`, `data_plantao`, `inicio`, `fim`, `tipo_plantao`, `status`, `conflito`, `cobertura_incompleta`, `observacoes`, `criado_em`, `atualizado_em`, `origem`) VALUES
	(1, 1, 1, 1, '2026-05-25', '2026-05-25 07:00:00', '2026-05-26 07:00:00', '24h', 'previsto', 0, 0, NULL, '2026-05-21 16:00:34', '2026-05-21 16:00:34', 'Automatica'),
	(2, 1, 1, 2, '2026-05-26', '2026-05-26 07:00:00', '2026-05-27 07:00:00', '24h', 'previsto', 0, 0, NULL, '2026-05-21 16:00:34', '2026-05-21 16:00:34', 'Automatica'),
	(3, 1, 1, 1, '2026-05-27', '2026-05-27 07:00:00', '2026-05-28 07:00:00', '24h', 'previsto', 0, 0, NULL, '2026-05-21 16:00:34', '2026-05-21 16:00:34', 'Automatica'),
	(4, 1, 1, 2, '2026-05-28', '2026-05-28 07:00:00', '2026-05-29 07:00:00', '24h', 'previsto', 0, 0, NULL, '2026-05-21 16:00:34', '2026-05-21 16:00:34', 'Automatica'),
	(5, 1, 1, 1, '2026-05-29', '2026-05-29 07:00:00', '2026-05-30 07:00:00', '24h', 'previsto', 0, 0, NULL, '2026-05-21 16:00:34', '2026-05-21 16:00:34', 'Automatica'),
	(6, 1, 1, 2, '2026-05-30', '2026-05-30 07:00:00', '2026-05-31 07:00:00', '24h', 'previsto', 0, 0, NULL, '2026-05-21 16:00:34', '2026-05-21 16:00:34', 'Automatica'),
	(7, 1, 1, 1, '2026-05-31', '2026-05-31 07:00:00', '2026-06-01 07:00:00', '24h', 'previsto', 0, 0, NULL, '2026-05-21 16:00:34', '2026-05-21 16:00:34', 'Automatica'),
	(106, NULL, 8, 8, '2026-05-23', '2026-05-23 07:00:00', '2026-05-23 19:00:00', '12h', 'previsto', 0, 0, NULL, '2026-05-22 11:47:12', '2026-05-22 11:47:12', 'Manual'),
	(107, NULL, 9, 67, '2026-05-23', '2026-05-23 07:00:00', '2026-05-23 19:00:00', '12h', 'previsto', 0, 0, NULL, '2026-05-22 12:09:04', '2026-05-22 12:09:04', 'Manual'),
	(108, NULL, 8, 67, '2026-05-23', '2026-05-23 19:00:00', '2026-05-24 07:00:00', '12h', 'previsto', 0, 0, NULL, '2026-05-22 15:28:23', '2026-05-22 15:28:23', 'Manual');

-- Copiando estrutura para tabela cuidar_no_lar.tb_escala_profissionais
CREATE TABLE IF NOT EXISTS `tb_escala_profissionais` (
  `id` int NOT NULL AUTO_INCREMENT,
  `escala_base_id` int NOT NULL,
  `cuidador_id` int NOT NULL,
  `ordem_revezamento` int DEFAULT '1',
  `principal_escala` tinyint(1) DEFAULT '1',
  `ativo` tinyint(1) DEFAULT '1',
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_tb_escala_profissionais_escala` (`escala_base_id`),
  KEY `fk_tb_escala_profissionais_cuidador` (`cuidador_id`),
  CONSTRAINT `fk_tb_escala_profissionais_cuidador` FOREIGN KEY (`cuidador_id`) REFERENCES `tb_cuidador` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tb_escala_profissionais_escala` FOREIGN KEY (`escala_base_id`) REFERENCES `tb_escala_base` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_escala_profissionais: ~4 rows (aproximadamente)
INSERT INTO `tb_escala_profissionais` (`id`, `escala_base_id`, `cuidador_id`, `ordem_revezamento`, `principal_escala`, `ativo`, `criado_em`) VALUES
	(1, 1, 1, 1, 1, 1, '2026-05-21 15:15:07'),
	(2, 1, 2, 2, 1, 1, '2026-05-21 15:15:07'),
	(3, 1, 1, 1, 1, 1, '2026-05-21 15:44:36'),
	(4, 1, 2, 2, 1, 1, '2026-05-21 15:44:36');

-- Copiando estrutura para tabela cuidar_no_lar.tb_escala_substituicoes
CREATE TABLE IF NOT EXISTS `tb_escala_substituicoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ocorrencia_id` int NOT NULL,
  `cuidador_original_id` int NOT NULL,
  `cuidador_substituto_id` int NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `observacoes` text,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_plantao` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_tb_sub_ocorrencia` (`ocorrencia_id`),
  KEY `fk_tb_sub_original` (`cuidador_original_id`),
  KEY `fk_tb_sub_substituto` (`cuidador_substituto_id`),
  CONSTRAINT `fk_tb_sub_ocorrencia` FOREIGN KEY (`ocorrencia_id`) REFERENCES `tb_escala_ocorrencias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tb_sub_original` FOREIGN KEY (`cuidador_original_id`) REFERENCES `tb_cuidador` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tb_sub_substituto` FOREIGN KEY (`cuidador_substituto_id`) REFERENCES `tb_cuidador` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_escala_substituicoes: ~2 rows (aproximadamente)
INSERT INTO `tb_escala_substituicoes` (`id`, `ocorrencia_id`, `cuidador_original_id`, `cuidador_substituto_id`, `motivo`, `observacoes`, `criado_em`, `data_plantao`) VALUES
	(1, 108, 67, 62, 'falta', 'Não veio para o plantão', '2026-05-22 18:40:37', NULL),
	(2, 106, 8, 33, 'emergencia', NULL, '2026-05-23 00:03:25', NULL);

-- Copiando estrutura para tabela cuidar_no_lar.tb_pacientes
CREATE TABLE IF NOT EXISTS `tb_pacientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL DEFAULT (uuid()),
  `prontuario` varchar(30) DEFAULT NULL,
  `nome_completo` varchar(100) NOT NULL,
  `diagnostico` varchar(255) DEFAULT NULL,
  `cid_principal` varchar(20) DEFAULT NULL,
  `diagnostico_principal` text,
  `motivo_homecare` text,
  `usa_sonda` enum('Sim','Não') DEFAULT 'Não',
  `usa_oxigenio` enum('Sim','Não') DEFAULT 'Não',
  `traqueostomia` enum('Sim','Não') DEFAULT 'Não',
  `gastrostomia` enum('Sim','Não') DEFAULT 'Não',
  `colostomia` enum('Sim','Não') DEFAULT 'Não',
  `cateter_vesical` enum('Sim','Não') DEFAULT 'Não',
  `observacoes_clinicas` text,
  `data_nascimento` date DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `rg` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `cartao_nac_sus` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `plano_saude` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `responsavel_id` int DEFAULT NULL,
  `cuidador_id` int DEFAULT NULL,
  `anamnese_id` int DEFAULT NULL,
  `status` enum('Ativo','Inativo') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'Ativo',
  `motivo_inativacao` text,
  `sexo` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `endereco_completo` varchar(255) DEFAULT NULL,
  `telefone_principal` varchar(30) DEFAULT NULL,
  `telefone_secundario` varchar(30) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `responsavel_nome_texto` varchar(120) DEFAULT NULL,
  `responsavel_parentesco` varchar(60) DEFAULT NULL,
  `responsavel_telefone` varchar(30) DEFAULT NULL,
  `responsavel_email` varchar(120) DEFAULT NULL,
  `comorbidades` text,
  `alergias` text,
  `historico_cirurgico` text,
  `tipo_sanguineo` varchar(5) DEFAULT NULL,
  `peso` decimal(6,2) DEFAULT NULL,
  `altura` decimal(4,2) DEFAULT NULL,
  `dieta_tipo` varchar(120) DEFAULT NULL,
  `dieta_restricao` text,
  `alimentacao_via` varchar(80) DEFAULT NULL,
  `sonda_vesical` varchar(10) DEFAULT NULL,
  `incontinencia` varchar(120) DEFAULT NULL,
  `mobilidade` varchar(80) DEFAULT NULL,
  `estado_cognitivo_base` varchar(80) DEFAULT NULL,
  `gtt` varchar(10) DEFAULT NULL,
  `sne` varchar(10) DEFAULT NULL,
  `cateter_venoso` varchar(10) DEFAULT NULL,
  `picc` varchar(10) DEFAULT NULL,
  `lesao_pressao` varchar(10) DEFAULT NULL,
  `curativos` text,
  `areas_risco` text,
  `condutas_permanentes` text,
  `convenio` varchar(120) DEFAULT NULL,
  `numero_carteirinha` varchar(80) DEFAULT NULL,
  `prescricao_medica` text,
  `termos_assinados` text,
  `cor_avatar` varchar(20) DEFAULT NULL,
  `cor_avatar_t` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pacientes_uuid` (`uuid`),
  UNIQUE KEY `uk_pacientes_prontuario` (`prontuario`),
  KEY `responsavel_id` (`responsavel_id`),
  KEY `cuidador_id` (`cuidador_id`),
  KEY `anamnese_id` (`anamnese_id`),
  KEY `idx_pacientes_responsavel_id` (`responsavel_id`),
  KEY `idx_tb_pacientes_responsavel_id` (`responsavel_id`),
  CONSTRAINT `fk_pacientes_responsavel` FOREIGN KEY (`responsavel_id`) REFERENCES `tb_responsavel` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_pacientes: ~27 rows (aproximadamente)
INSERT INTO `tb_pacientes` (`id`, `uuid`, `prontuario`, `nome_completo`, `diagnostico`, `cid_principal`, `diagnostico_principal`, `motivo_homecare`, `usa_sonda`, `usa_oxigenio`, `traqueostomia`, `gastrostomia`, `colostomia`, `cateter_vesical`, `observacoes_clinicas`, `data_nascimento`, `cpf`, `rg`, `cartao_nac_sus`, `plano_saude`, `responsavel_id`, `cuidador_id`, `anamnese_id`, `status`, `motivo_inativacao`, `sexo`, `foto`, `endereco_completo`, `telefone_principal`, `telefone_secundario`, `email`, `responsavel_nome_texto`, `responsavel_parentesco`, `responsavel_telefone`, `responsavel_email`, `comorbidades`, `alergias`, `historico_cirurgico`, `tipo_sanguineo`, `peso`, `altura`, `dieta_tipo`, `dieta_restricao`, `alimentacao_via`, `sonda_vesical`, `incontinencia`, `mobilidade`, `estado_cognitivo_base`, `gtt`, `sne`, `cateter_venoso`, `picc`, `lesao_pressao`, `curativos`, `areas_risco`, `condutas_permanentes`, `convenio`, `numero_carteirinha`, `prescricao_medica`, `termos_assinados`, `cor_avatar`, `cor_avatar_t`) VALUES
	(1, '6c0ec678-52ac-11f1-a16a-089798669242', 'PRT-2026-1.0000', 'Ormezinda Peres de Carvalho', 'Alzheimer', 'CID-10 G30', 'Doença neurológica degenerativa de evolução lenta e progressiva', 'Não pode ficar sozinha', 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', NULL, '1926-03-30', '423.836.506-25', NULL, NULL, 'Unimed', 1, 3, NULL, 'Ativo', NULL, 'Feminino', NULL, 'Rua João Dornelas, 100 - Dornelas', '32 98888- 0000', NULL, 'teste@ormezinda.com.br', 'Lúcia', 'Filha', '32 99999 - 0001', 'lucia@teste.com.br', 'Diabetes, pressão alta, incontinência urinária', 'Nenhuma', 'Nenhum', 'O+', 47.00, 1.40, 'Normal', NULL, 'VO', 'Nao', 'Sim', 'Deambula com auxilio', 'Demencia', 'Nao', 'Nao', 'Nao', 'Nao', 'Nao', NULL, NULL, '["Controle glicemico"]', NULL, NULL, NULL, NULL, NULL, NULL),
	(2, '6c0ed381-52ac-11f1-a16a-089798669242', 'PRT-2026-2.0000', 'Maria da Penha Martins', NULL, NULL, NULL, NULL, 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', NULL, '1951-01-28', '335.489.947-68', NULL, NULL, NULL, 2, 8, NULL, 'Ativo', NULL, 'Feminino', NULL, 'Av Constantino Pinto', '32 99999-0002', NULL, NULL, 'Márcia', 'Filha', '32 99999-0002', 'marcia@teste.com.br', NULL, NULL, NULL, NULL, 59.00, 1.61, NULL, NULL, NULL, 'Nao', 'VO', 'Acamado', NULL, 'Nao', 'Nao', 'Nao', 'Nao', 'Nao', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(8, '6c0ed764-52ac-11f1-a16a-089798669242', 'PRT-2026-3.0000', 'Rafaela', 'Demência', 'I63.9', 'Paciente com demência', 'Auxílio no cotidiano no cuidado', 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', NULL, '1987-04-12', '118.034.737-46', '20.131.858-1', '532 9871 4346', '12154677', 36, 8, NULL, 'Ativo', NULL, 'Feminino', NULL, 'Rua Cornelio Henriques de Almeida, 103 - apto 102', '32 998416669', '32 988575765', 'teste@rafa.com', 'Neide Maria', 'Mãe', NULL, NULL, 'Nenhuma', 'A pessoas', 'Nenhuma', 'O+', 86.00, 1.74, 'Come de tudo', 'Fechar a boca', 'VO', 'Nao', 'Urinária', 'Independente', 'Orientado', 'Nao', 'Nao', 'Nao', 'Nao', 'Nao', NULL, NULL, '["Aspiracao de vias aereas"]', NULL, NULL, 'Doidinha por natureza - cuidado que morde', NULL, NULL, NULL),
	(9, '6c0edb0d-52ac-11f1-a16a-089798669242', 'PRT-2026-4.0000', 'Teste MVC Paciente Editado', NULL, NULL, NULL, NULL, 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', NULL, '2026-05-13', '0000000000', '00000', '00000000000000000000', 'Unimed', 36, 59, NULL, 'Inativo', 'Teste rollback', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(10, '6c0ee96d-52ac-11f1-a16a-089798669242', 'PRT-2026-5.0000', 'Teste Paciente MVC Rel', NULL, NULL, NULL, NULL, 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', NULL, NULL, NULL, NULL, NULL, NULL, 42, 67, NULL, 'Ativo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(11, '8133a753-561c-11f1-a16a-089798669242', 'PRT-2026-6.0000', 'Sonia das Graças', 'Alzheimer', 'I63.9', 'Alzheimer', 'Não pode ficar sozinha, está fazendo arte.', 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', NULL, '1950-06-20', '00011122233344', '2020020030', '1234567890', 'Não tem', 42, 8, NULL, 'Ativo', NULL, 'Feminino', NULL, 'Avenida JK, 431 - Centro - Muriaé', '32988414106', NULL, 'vovosoninha@gmail.com', 'Vanusa', 'Filha', '32988575765', 'vansua@teste.com.br', 'Pressão alta', 'Dipirona', 'Nenhum', 'O+', 50.00, 1.59, NULL, NULL, NULL, 'Nao', NULL, NULL, NULL, 'Nao', 'Nao', 'Nao', 'Nao', 'Nao', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(12, '7ea9f297-59cc-11f1-b6c0-089798669242', 'PRT-2026-7.0000', 'Ana Luiza Martins', 'Bronquiolite', 'J21', 'Bronquiolite aguda', 'Necessidade de monitoramento respiratório', 'Não', 'Sim', 'Não', 'Não', 'Não', 'Não', 'Paciente estável', '2021-09-15', '222.222.222-01', NULL, NULL, NULL, 43, 1, NULL, 'Ativo', NULL, 'F', NULL, 'Rua Alpha, 10', '32988880001', NULL, 'ana@email.com', 'Carla Martins', 'Mãe', '32988881111', NULL, NULL, NULL, NULL, NULL, 14.20, 1.00, NULL, NULL, 'VO', NULL, NULL, 'Assistida', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Unimed', NULL, NULL, NULL, NULL, NULL),
	(13, '7eaa931e-59cc-11f1-b6c0-089798669242', 'PRT-2026-8.0000', 'Lucas Gabriel', 'Pneumonia', 'J18', 'Pneumonia bacteriana', 'Uso contínuo de oxigênio', 'Não', 'Sim', 'Não', 'Não', 'Não', 'Não', 'Tosse produtiva', '2020-11-02', '222.222.222-02', NULL, NULL, NULL, 44, NULL, NULL, 'Ativo', NULL, 'M', NULL, 'Rua Beta, 20', '32988880002', NULL, 'lucas@email.com', 'Fernanda', 'Mãe', '32988882222', NULL, NULL, NULL, NULL, NULL, 18.50, 1.10, NULL, NULL, 'VO', NULL, NULL, 'Assistida', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SUS', NULL, NULL, NULL, NULL, NULL),
	(14, '7eaa9966-59cc-11f1-b6c0-089798669242', 'PRT-2026-9.0000', 'Helena Souza', 'Pós-operatório cardíaco', 'I51', 'Cirurgia cardíaca recente', 'Recuperação pós-operatória', 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', 'Monitoramento cardíaco', '2019-01-12', '222.222.222-03', NULL, NULL, NULL, 45, NULL, NULL, 'Ativo', NULL, 'F', NULL, 'Rua Gama, 30', '32988880003', NULL, 'helena@email.com', 'Marcelo Souza', 'Pai', '32988883333', NULL, NULL, NULL, NULL, NULL, 22.00, 1.20, NULL, NULL, 'VO', NULL, NULL, 'Parcial', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Unimed', NULL, NULL, NULL, NULL, NULL),
	(15, '7eaa9dbb-59cc-11f1-b6c0-089798669242', 'PRT-2026-10.000', 'Miguel Oliveira', 'Gastroenterite', 'A09', 'Gastroenterite infecciosa', 'Hidratação e observação', 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', 'Episódios de vômito', '2024-03-30', '222.222.222-04', NULL, NULL, NULL, 46, NULL, NULL, 'Ativo', NULL, 'M', NULL, 'Rua Delta, 40', '32988880004', NULL, 'miguel@email.com', 'Tatiane', 'Mãe', '32988884444', NULL, NULL, NULL, NULL, NULL, 11.30, 0.85, NULL, NULL, 'VO', NULL, NULL, 'Dependente', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Particular', NULL, NULL, NULL, NULL, NULL),
	(16, '7eaaa1f5-59cc-11f1-b6c0-089798669242', 'PRT-2026-11.000', 'Arthur Mendes', 'Crise asmática', 'J45', 'Asma moderada', 'Controle respiratório', 'Não', 'Sim', 'Não', 'Não', 'Não', 'Não', 'Uso frequente de nebulização', '2018-12-10', '222.222.222-05', NULL, NULL, NULL, 47, NULL, NULL, 'Ativo', NULL, 'M', NULL, 'Rua Épsilon, 50', '32988880005', NULL, 'arthur@email.com', 'Simone Mendes', 'Mãe', '32988885555', NULL, NULL, NULL, NULL, NULL, 24.00, 1.28, NULL, NULL, 'VO', NULL, NULL, 'Independente', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SUS', NULL, NULL, NULL, NULL, NULL),
	(17, 'f13e2d18-59cc-11f1-b6c0-089798669242', 'PRT-2026-12.000', 'Ana Luiza Martins', 'Bronquiolite', 'J21', 'Bronquiolite aguda', 'Necessidade de monitoramento respiratório', 'Não', 'Sim', 'Não', 'Não', 'Não', 'Não', 'Paciente estável', '2021-09-15', '222.222.222-01', NULL, NULL, NULL, 43, NULL, NULL, 'Ativo', NULL, 'F', NULL, 'Rua Alpha, 10', '32988880001', NULL, 'ana@email.com', 'Carla Martins', 'Mãe', '32988881111', NULL, NULL, NULL, NULL, NULL, 14.20, 1.00, NULL, NULL, 'VO', NULL, NULL, 'Assistida', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Unimed', NULL, NULL, NULL, NULL, NULL),
	(18, 'f13e356e-59cc-11f1-b6c0-089798669242', 'PRT-2026-13.000', 'Lucas Gabriel', 'Pneumonia', 'J18', 'Pneumonia bacteriana', 'Uso contínuo de oxigênio', 'Não', 'Sim', 'Não', 'Não', 'Não', 'Não', 'Tosse produtiva', '2020-11-02', '222.222.222-02', NULL, NULL, NULL, 44, NULL, NULL, 'Ativo', NULL, 'M', NULL, 'Rua Beta, 20', '32988880002', NULL, 'lucas@email.com', 'Fernanda', 'Mãe', '32988882222', NULL, NULL, NULL, NULL, NULL, 18.50, 1.10, NULL, NULL, 'VO', NULL, NULL, 'Assistida', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SUS', NULL, NULL, NULL, NULL, NULL),
	(19, 'f13e390a-59cc-11f1-b6c0-089798669242', 'PRT-2026-14.000', 'Helena Souza', 'Pós-operatório cardíaco', 'I51', 'Cirurgia cardíaca recente', 'Recuperação pós-operatória', 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', 'Monitoramento cardíaco', '2019-01-12', '222.222.222-03', NULL, NULL, NULL, 45, NULL, NULL, 'Ativo', NULL, 'F', NULL, 'Rua Gama, 30', '32988880003', NULL, 'helena@email.com', 'Marcelo Souza', 'Pai', '32988883333', NULL, NULL, NULL, NULL, NULL, 22.00, 1.20, NULL, NULL, 'VO', NULL, NULL, 'Parcial', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Unimed', NULL, NULL, NULL, NULL, NULL),
	(20, 'f13e3c4b-59cc-11f1-b6c0-089798669242', 'PRT-2026-15.000', 'Miguel Oliveira', 'Gastroenterite', 'A09', 'Gastroenterite infecciosa', 'Hidratação e observação', 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', 'Episódios de vômito', '2024-03-30', '222.222.222-04', NULL, NULL, NULL, 46, NULL, NULL, 'Ativo', NULL, 'M', NULL, 'Rua Delta, 40', '32988880004', NULL, 'miguel@email.com', 'Tatiane', 'Mãe', '32988884444', NULL, NULL, NULL, NULL, NULL, 11.30, 0.85, NULL, NULL, 'VO', NULL, NULL, 'Dependente', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Particular', NULL, NULL, NULL, NULL, NULL),
	(21, 'f13e409b-59cc-11f1-b6c0-089798669242', 'PRT-2026-16.000', 'Arthur Mendes', 'Crise asmática', 'J45', 'Asma moderada', 'Controle respiratório', 'Não', 'Sim', 'Não', 'Não', 'Não', 'Não', 'Uso frequente de nebulização', '2018-12-10', '222.222.222-05', NULL, NULL, NULL, 47, NULL, NULL, 'Ativo', NULL, 'M', NULL, 'Rua Épsilon, 50', '32988880005', NULL, 'arthur@email.com', 'Simone Mendes', 'Mãe', '32988885555', NULL, NULL, NULL, NULL, NULL, 24.00, 1.28, NULL, NULL, 'VO', NULL, NULL, 'Independente', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SUS', NULL, NULL, NULL, NULL, NULL),
	(22, 'f7a74bd9-59cc-11f1-b6c0-089798669242', 'PRT-2026-17.000', 'Ana Luiza Martins', 'Bronquiolite', 'J21', 'Bronquiolite aguda', 'Necessidade de monitoramento respiratório', 'Não', 'Sim', 'Não', 'Não', 'Não', 'Não', 'Paciente estável', '2021-09-15', '222.222.222-01', NULL, NULL, NULL, 43, NULL, NULL, 'Ativo', NULL, 'F', NULL, 'Rua Alpha, 10', '32988880001', NULL, 'ana@email.com', 'Carla Martins', 'Mãe', '32988881111', NULL, NULL, NULL, NULL, NULL, 14.20, 1.00, NULL, NULL, 'VO', NULL, NULL, 'Assistida', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Unimed', NULL, NULL, NULL, NULL, NULL),
	(23, 'f7a754a3-59cc-11f1-b6c0-089798669242', 'PRT-2026-18.000', 'Lucas Gabriel', 'Pneumonia', 'J18', 'Pneumonia bacteriana', 'Uso contínuo de oxigênio', 'Não', 'Sim', 'Não', 'Não', 'Não', 'Não', 'Tosse produtiva', '2020-11-02', '222.222.222-02', NULL, NULL, NULL, 44, NULL, NULL, 'Ativo', NULL, 'M', NULL, 'Rua Beta, 20', '32988880002', NULL, 'lucas@email.com', 'Fernanda', 'Mãe', '32988882222', NULL, NULL, NULL, NULL, NULL, 18.50, 1.10, NULL, NULL, 'VO', NULL, NULL, 'Assistida', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SUS', NULL, NULL, NULL, NULL, NULL),
	(24, 'f7a7578c-59cc-11f1-b6c0-089798669242', 'PRT-2026-19.000', 'Helena Souza', 'Pós-operatório cardíaco', 'I51', 'Cirurgia cardíaca recente', 'Recuperação pós-operatória', 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', 'Monitoramento cardíaco', '2019-01-12', '222.222.222-03', NULL, NULL, NULL, 45, NULL, NULL, 'Ativo', NULL, 'F', NULL, 'Rua Gama, 30', '32988880003', NULL, 'helena@email.com', 'Marcelo Souza', 'Pai', '32988883333', NULL, NULL, NULL, NULL, NULL, 22.00, 1.20, NULL, NULL, 'VO', NULL, NULL, 'Parcial', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Unimed', NULL, NULL, NULL, NULL, NULL),
	(25, 'f7a75a8d-59cc-11f1-b6c0-089798669242', 'PRT-2026-20.000', 'Miguel Oliveira', 'Gastroenterite', 'A09', 'Gastroenterite infecciosa', 'Hidratação e observação', 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', 'Episódios de vômito', '2024-03-30', '222.222.222-04', NULL, NULL, NULL, 46, NULL, NULL, 'Ativo', NULL, 'M', NULL, 'Rua Delta, 40', '32988880004', NULL, 'miguel@email.com', 'Tatiane', 'Mãe', '32988884444', NULL, NULL, NULL, NULL, NULL, 11.30, 0.85, NULL, NULL, 'VO', NULL, NULL, 'Dependente', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Particular', NULL, NULL, NULL, NULL, NULL),
	(26, 'f7a75d63-59cc-11f1-b6c0-089798669242', 'PRT-2026-21.000', 'Arthur Mendes', 'Crise asmática', 'J45', 'Asma moderada', 'Controle respiratório', 'Não', 'Sim', 'Não', 'Não', 'Não', 'Não', 'Uso frequente de nebulização', '2018-12-10', '222.222.222-05', NULL, NULL, NULL, 47, NULL, NULL, 'Ativo', NULL, 'M', NULL, 'Rua Épsilon, 50', '32988880005', NULL, 'arthur@email.com', 'Simone Mendes', 'Mãe', '32988885555', NULL, NULL, NULL, NULL, NULL, 24.00, 1.28, NULL, NULL, 'VO', NULL, NULL, 'Independente', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SUS', NULL, NULL, NULL, NULL, NULL),
	(27, '0e344e18-59cd-11f1-b6c0-089798669242', 'PRT-2026-22.000', 'Ana Luiza Martins', 'Bronquiolite', 'J21', 'Bronquiolite aguda', 'Necessidade de monitoramento respiratório', 'Não', 'Sim', 'Não', 'Não', 'Não', 'Não', 'Paciente estável', '2021-09-15', '222.222.222-01', NULL, NULL, NULL, 43, NULL, NULL, 'Ativo', NULL, 'F', NULL, 'Rua Alpha, 10', '32988880001', NULL, 'ana@email.com', 'Carla Martins', 'Mãe', '32988881111', NULL, NULL, NULL, NULL, NULL, 14.20, 1.00, NULL, NULL, 'VO', NULL, NULL, 'Assistida', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Unimed', NULL, NULL, NULL, NULL, NULL),
	(28, '0e3455fa-59cd-11f1-b6c0-089798669242', 'PRT-2026-23.000', 'Lucas Gabriel', 'Pneumonia', 'J18', 'Pneumonia bacteriana', 'Uso contínuo de oxigênio', 'Não', 'Sim', 'Não', 'Não', 'Não', 'Não', 'Tosse produtiva', '2020-11-02', '222.222.222-02', NULL, NULL, NULL, 44, NULL, NULL, 'Ativo', NULL, 'M', NULL, 'Rua Beta, 20', '32988880002', NULL, 'lucas@email.com', 'Fernanda', 'Mãe', '32988882222', NULL, NULL, NULL, NULL, NULL, 18.50, 1.10, NULL, NULL, 'VO', NULL, NULL, 'Assistida', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SUS', NULL, NULL, NULL, NULL, NULL),
	(29, '0e345861-59cd-11f1-b6c0-089798669242', 'PRT-2026-24.000', 'Helena Souza', 'Pós-operatório cardíaco', 'I51', 'Cirurgia cardíaca recente', 'Recuperação pós-operatória', 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', 'Monitoramento cardíaco', '2019-01-12', '222.222.222-03', NULL, NULL, NULL, 45, NULL, NULL, 'Ativo', NULL, 'F', NULL, 'Rua Gama, 30', '32988880003', NULL, 'helena@email.com', 'Marcelo Souza', 'Pai', '32988883333', NULL, NULL, NULL, NULL, NULL, 22.00, 1.20, NULL, NULL, 'VO', NULL, NULL, 'Parcial', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Unimed', NULL, NULL, NULL, NULL, NULL),
	(30, '0e345ac7-59cd-11f1-b6c0-089798669242', 'PRT-2026-25.000', 'Miguel Oliveira', 'Gastroenterite', 'A09', 'Gastroenterite infecciosa', 'Hidratação e observação', 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', 'Episódios de vômito', '2024-03-30', '222.222.222-04', NULL, NULL, NULL, 46, NULL, NULL, 'Ativo', NULL, 'M', NULL, 'Rua Delta, 40', '32988880004', NULL, 'miguel@email.com', 'Tatiane', 'Mãe', '32988884444', NULL, NULL, NULL, NULL, NULL, 11.30, 0.85, NULL, NULL, 'VO', NULL, NULL, 'Dependente', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Particular', NULL, NULL, NULL, NULL, NULL),
	(31, '0e345daa-59cd-11f1-b6c0-089798669242', 'PRT-2026-26.000', 'Arthur Mendes', 'Crise asmática', 'J45', 'Asma moderada', 'Controle respiratório', 'Não', 'Sim', 'Não', 'Não', 'Não', 'Não', 'Uso frequente de nebulização', '2018-12-10', '222.222.222-05', NULL, NULL, NULL, 47, NULL, NULL, 'Ativo', NULL, 'M', NULL, 'Rua Épsilon, 50', '32988880005', NULL, 'arthur@email.com', 'Simone Mendes', 'Mãe', '32988885555', NULL, NULL, NULL, NULL, NULL, 24.00, 1.28, NULL, NULL, 'VO', NULL, NULL, 'Independente', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SUS', NULL, NULL, NULL, NULL, NULL),
	(32, '0e2e80f2-5aa1-11f1-b6c0-089798669242', 'PRT-2026-000001', 'Ana Luiza Martins', 'Bronquiolite', 'J21', 'Bronquiolite aguda', 'Necessidade de monitoramento respiratório', 'Não', 'Sim', 'Não', 'Não', 'Não', 'Não', 'Paciente estável', '2021-09-15', '222.222.222-01', NULL, NULL, NULL, 43, 5, NULL, 'Ativo', NULL, 'Feminino', NULL, 'Rua Alpha, 10', '32988880001', NULL, 'ana@email.com', 'Carla Martins', 'Mãe', '32988881111', NULL, NULL, NULL, NULL, NULL, 14.20, 1.00, NULL, NULL, 'VO', 'Nao', NULL, NULL, NULL, 'Nao', 'Nao', 'Nao', 'Nao', 'Nao', NULL, NULL, '["Controle glicemico"]', 'Unimed', NULL, NULL, NULL, NULL, NULL);

-- Copiando estrutura para tabela cuidar_no_lar.tb_paciente_detalhes
CREATE TABLE IF NOT EXISTS `tb_paciente_detalhes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `paciente_id` int NOT NULL,
  `endereco` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_contrato` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cor_avatar` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cor_avatar_t` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_paciente_detalhes` (`paciente_id`),
  CONSTRAINT `fk_paciente_detalhes` FOREIGN KEY (`paciente_id`) REFERENCES `tb_pacientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela cuidar_no_lar.tb_paciente_detalhes: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela cuidar_no_lar.tb_plantoes
CREATE TABLE IF NOT EXISTS `tb_plantoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `paciente_id` int NOT NULL,
  `cuidador_id` int NOT NULL,
  `data_inicio` datetime NOT NULL,
  `data_fim` datetime NOT NULL,
  `tipo_plantao` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('em_andamento','finalizado','intercorrencia') COLLATE utf8mb4_unicode_ci DEFAULT 'em_andamento',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_plantoes_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela cuidar_no_lar.tb_plantoes: ~0 rows (aproximadamente)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
