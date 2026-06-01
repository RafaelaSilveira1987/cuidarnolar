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

-- Copiando estrutura para tabela cuidar_no_lar.tb_anamnese
CREATE TABLE IF NOT EXISTS `tb_anamnese` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL DEFAULT (uuid()),
  `paciente_id` int NOT NULL,
  `data_anamnese` date NOT NULL,
  `patologia` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `diagnostico_principal` varchar(255) DEFAULT NULL,
  `cid_principal` varchar(20) DEFAULT NULL,
  `motivo_homecare` text,
  `observacoes_clinicas` text,
  `usa_sonda` enum('Sim','Não') DEFAULT 'Não',
  `tipo_sonda` varchar(100) DEFAULT NULL,
  `usa_gastrostomia` enum('Sim','Não') DEFAULT 'Não',
  `usa_traqueostomia` enum('Sim','Não') DEFAULT 'Não',
  `usa_oxigenio` enum('Sim','Não') DEFAULT 'Não',
  `fluxo_oxigenio` varchar(50) DEFAULT NULL,
  `usa_colostomia` enum('Sim','Não') DEFAULT 'Não',
  `usa_cateter_vesical` enum('Sim','Não') DEFAULT 'Não',
  `usa_picc` enum('Sim','Não') DEFAULT 'Não',
  `sintomas` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `sequelas` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `historia_medica` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `cirurgia` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `protese` enum('Sim','Não') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `acamado` enum('Sim','Não') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `hipertensao` enum('Sim','Não') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `diabetes` enum('Sim','Não') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `alergia` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `estilo_de_vida` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `dieta` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `medicacao_continua` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `sono` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `visao` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `audicao` varchar(50) DEFAULT NULL,
  `incontinencia` enum('Sim','Não') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `demencia` enum('Sim','Não') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `cognicao` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `coordenacao_motora` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `humor` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `problemas_locomocao` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `medico` varchar(30) DEFAULT NULL,
  `status` enum('completa','pendente','em revisão') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_anamnese_uuid` (`uuid`),
  KEY `paciente_id` (`paciente_id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_anamnese: 5 rows
/*!40000 ALTER TABLE `tb_anamnese` DISABLE KEYS */;
INSERT INTO `tb_anamnese` (`id`, `uuid`, `paciente_id`, `data_anamnese`, `patologia`, `diagnostico_principal`, `cid_principal`, `motivo_homecare`, `observacoes_clinicas`, `usa_sonda`, `tipo_sonda`, `usa_gastrostomia`, `usa_traqueostomia`, `usa_oxigenio`, `fluxo_oxigenio`, `usa_colostomia`, `usa_cateter_vesical`, `usa_picc`, `sintomas`, `sequelas`, `historia_medica`, `cirurgia`, `protese`, `acamado`, `hipertensao`, `diabetes`, `alergia`, `estilo_de_vida`, `dieta`, `medicacao_continua`, `sono`, `visao`, `audicao`, `incontinencia`, `demencia`, `cognicao`, `coordenacao_motora`, `humor`, `problemas_locomocao`, `medico`, `status`) VALUES
	(2, '6abcad6a-52ac-11f1-a16a-089798669242', 1, '2024-10-15', 'Diabetes', NULL, NULL, NULL, NULL, 'Não', NULL, 'Não', 'Não', 'Não', NULL, 'Não', 'Não', 'Não', 'Nenhum', 'Nenhum', 'Nenhum', 'Não', 'Não', 'Não', 'Sim', 'Sim', 'Não', 'Nenhum', 'Nenhum', 'Nenhum', 'Nenhum', 'Nenhum', 'Nenhum', 'Sim', 'Sim', 'Nenhum', 'Nenhum', 'Nenhum', 'Não', 'Dra Thais', 'pendente'),
	(3, '6abcbd24-52ac-11f1-a16a-089798669242', 2, '2024-10-20', 'Saúde', NULL, NULL, NULL, NULL, 'Não', NULL, 'Não', 'Não', 'Não', NULL, 'Não', 'Não', 'Não', 'Gripe', 'Não anda', 'Nenhum', 'Não', 'Não', 'Sim', 'Sim', 'Sim', 'Nenhuma', 'Normal', 'Normal', 'Nenhuma', 'Desrregulado', 'Normal', 'Normal', 'Sim', 'Sim', 'Normal', 'Normal', 'Normal', 'Normal', 'Dr. Ricardo', 'em revisão'),
	(6, '6abcc067-52ac-11f1-a16a-089798669242', 2, '2024-12-16', 'Corrigida', NULL, NULL, NULL, NULL, 'Não', NULL, 'Não', 'Não', 'Não', NULL, 'Não', 'Não', 'Não', 'Teste', 'Funcionando', 'Teste', 'Teste', 'Não', 'Sim', 'Não', 'Sim', 'Teste', 'Teste', 'Teste', 'Teste', 'Teste', 'Teste', 'Teste', 'Sim', 'Sim', 'Teste', 'Teste', 'Teste', 'não', 'Dr. Carlos', 'em revisão'),
	(25, '6abccdaf-52ac-11f1-a16a-089798669242', 10, '2026-05-13', NULL, NULL, NULL, NULL, NULL, 'Não', NULL, 'Não', 'Não', 'Não', NULL, 'Não', 'Não', 'Não', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pendente'),
	(26, 'df4e5669-53e2-11f1-a16a-089798669242', 8, '2026-05-14', 'Infecção urinária', NULL, NULL, NULL, NULL, 'Não', NULL, 'Não', 'Não', 'Não', NULL, 'Não', 'Não', 'Não', 'Febre, sonolência, dor ao urinar', 'Sepse', 'Paciente com histórico normal', 'Nenhuma', 'Não', 'Não', 'Sim', 'Não', 'Nenhuma', 'Sedentária', 'Nenhuma', 'Sinvastatina 20mg - 1x a noite', 'Normal', 'Normal', 'Prejudicada em 30%', 'Sim', 'Não', 'Normal', 'Normal', 'Relativo', 'Nenhum', 'Dra Vanusa', 'completa');
/*!40000 ALTER TABLE `tb_anamnese` ENABLE KEYS */;

-- Copiando estrutura para tabela cuidar_no_lar.tb_categorias_financeiro
CREATE TABLE IF NOT EXISTS `tb_categorias_financeiro` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `nome` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('entrada','saida') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categorias_nome_tipo` (`nome`,`tipo`),
  UNIQUE KEY `uq_categorias_financeiro_uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela cuidar_no_lar.tb_categorias_financeiro: ~8 rows (aproximadamente)
INSERT INTO `tb_categorias_financeiro` (`id`, `uuid`, `nome`, `tipo`, `ativo`) VALUES
	(1, '6accedda-52ac-11f1-a16a-089798669242', 'Mensalidade', 'entrada', 1),
	(2, '6accf261-52ac-11f1-a16a-089798669242', 'Plantão / extra', 'entrada', 1),
	(3, '6accf3d4-52ac-11f1-a16a-089798669242', 'Reembolso insumo', 'entrada', 1),
	(4, '6accf4ed-52ac-11f1-a16a-089798669242', 'Salário cuidador', 'saida', 1),
	(5, '6accf5d7-52ac-11f1-a16a-089798669242', 'Encargos trabalhistas', 'saida', 1),
	(6, '6accf6b6-52ac-11f1-a16a-089798669242', 'Insumos e materiais', 'saida', 1),
	(7, '6accf7cf-52ac-11f1-a16a-089798669242', 'Transporte', 'saida', 1),
	(8, '6accf927-52ac-11f1-a16a-089798669242', 'Despesas administrativas', 'saida', 1);

-- Copiando estrutura para tabela cuidar_no_lar.tb_contratos_paciente
CREATE TABLE IF NOT EXISTS `tb_contratos_paciente` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `paciente_id` int NOT NULL,
  `tipo_servico` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_plantao` enum('6h','8h','12h','24h') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hora_inicio_padrao` time DEFAULT NULL,
  `hora_fim_padrao` time DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela cuidar_no_lar.tb_contratos_paciente: ~4 rows (aproximadamente)
INSERT INTO `tb_contratos_paciente` (`id`, `uuid`, `paciente_id`, `tipo_servico`, `tipo_plantao`, `hora_inicio_padrao`, `hora_fim_padrao`, `valor_mensal`, `dia_vencimento`, `forma_pagamento`, `vigencia_inicio`, `vigencia_fim`, `status`, `observacoes`, `created_at`) VALUES
	(1, '6ae2639f-52ac-11f1-a16a-089798669242', 2, 'Cuidados 24h', '24h', '07:00:00', '07:00:00', 3000.00, 10, 'PIX', '2026-01-01', '2026-12-31', 'Ativo', NULL, '2026-05-13 23:50:40'),
	(2, 'cd1aa9a4-5b8e-11f1-b6c0-089798669242', 12, 'Home care 12h', NULL, NULL, NULL, 2500.00, 10, NULL, '2026-05-29', '2026-06-01', 'Ativo', NULL, '2026-05-29 18:47:10'),
	(3, 'f1852ba1-5b8e-11f1-b6c0-089798669242', 1, 'Home care 24h', NULL, NULL, NULL, 3000.00, 10, NULL, '2026-05-29', NULL, 'Ativo', NULL, '2026-05-29 18:48:12'),
	(4, '146e27eb-5b9d-11f1-b6c0-089798669242', 16, 'Home care 12h', NULL, NULL, NULL, 2000.00, 10, NULL, '2026-05-29', NULL, 'Ativo', NULL, '2026-05-29 20:29:23');

-- Copiando estrutura para tabela cuidar_no_lar.tb_cuidador
CREATE TABLE IF NOT EXISTS `tb_cuidador` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL DEFAULT (uuid()),
  `nome_completo` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `endereco` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `numero` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `bairro` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `cidade` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `estado` char(2) NOT NULL,
  `cep` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `cpf` varchar(14) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `rg` varchar(20) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `telefone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `pix` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `especialidade` enum('Técnico de Enfermagem','Enfermeira','Cuidador','Acompanhante') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `status` enum('Ativo','Inativo','Standby') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'Ativo',
  `contrato_horas` enum('6h','8h','12h','24h') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `cor_avatar` varchar(20) DEFAULT NULL,
  `cor_escala` varchar(7) DEFAULT NULL,
  `motivo_inativacao` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf` (`cpf`),
  UNIQUE KEY `uq_cuidador_uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_cuidador: ~25 rows (aproximadamente)
INSERT INTO `tb_cuidador` (`id`, `uuid`, `nome_completo`, `endereco`, `numero`, `bairro`, `cidade`, `estado`, `cep`, `cpf`, `email`, `rg`, `data_nascimento`, `telefone`, `pix`, `especialidade`, `status`, `contrato_horas`, `cor_avatar`, `cor_escala`, `motivo_inativacao`) VALUES
	(1, '6aff6b8a-52ac-11f1-a16a-089798669242', 'Kelly Cristina Raimundo', 'Rua Onofre de Barros', '162', 'Nossa Senhora de Fátima', 'Eugenópolis', 'MG', '36855-000', '076.470.487-75', '32999759589Kelly@gmail.com', '0000000', '1976-10-20', '(32) 99975-9589', '32999759589', 'Cuidador', 'Ativo', '24h', NULL, '#2563eb', NULL),
	(2, '6aff75c9-52ac-11f1-a16a-089798669242', 'Tereza Raquel Nonato Viana ', 'Rua Nova Muriaé bloco 8', '202', 'Nova Muriáe', 'Muriaé', 'MG', '36880-000', '131.225.716-41', 'terezaraquelnonatoviana@gmail.com', '0000000', '1995-12-26', '(22) 98136-5364', '13122571641', 'Cuidador', 'Ativo', '24h', NULL, '#7c3aed', NULL),
	(3, '6aff7782-52ac-11f1-a16a-089798669242', 'Patrícia Lopes da Silva Fernante', 'Rua Lopo Cardoso', '341', 'Cardoso de Melo', 'Muriaé', 'MG', '36887-209', '036.718.376-59', 'patricialopessilva021@gmail.com', '0000000', '1980-02-21', '(32) 9830-0650', '03671837659', 'Cuidador', 'Standby', '24h', NULL, '#dc2626', NULL),
	(4, '6aff78dd-52ac-11f1-a16a-089798669242', 'Izabela Silva Souza', 'Rua Vereador Jacy Vargas', 'Lote 7', 'Centro', 'Pirapanema', 'MG', '36880-000', '106.602.956-36', 'izabelasilvasouza338@gmail.com', '0000000', '1994-03-14', '(32) 99833-6754', '32998336754', 'Cuidador', 'Ativo', '24h', NULL, '#ea580c', NULL),
	(5, '6aff7fe1-52ac-11f1-a16a-089798669242', 'Patrícia de Sales Silva ', 'Rua coronel pereira sobrinho', '405', 'Porto', 'Muriaé', 'MG', '36880-000', '023.086.676-05', 'patriciasales2105@gmail.com', '0000000', '1998-05-21', '(32) 99860-6301', '32998606301 ', 'Técnico de Enfermagem', 'Ativo', '24h', NULL, '#0891b2', NULL),
	(6, '6aff8577-52ac-11f1-a16a-089798669242', 'Mayara Cristina Gomes Santos', 'Rua Onofre de Barros - Fundos', '306', 'Centro', 'Eugenópolis', 'MG', '36880-000', '111.802.926-77', 'msgcristina@gmail.com', '0000000', '1992-02-17', '(32) 99999-9999', 'msgcristina@gmail.com', 'Técnico de Enfermagem', 'Ativo', '24h', NULL, '#be123c', NULL),
	(7, '6aff8b1e-52ac-11f1-a16a-089798669242', 'Maria Carolina Tavares da Cunha', 'Rua: Professor Florestan Fernandes', '46', 'Chalé', 'Muriaé', 'MG', '36880-000', '152.312.427-05', 'mtavarescampbell@gmail.com', '0000000', '1994-08-09', '(32)98897-6793', '32988976793', 'Técnico de Enfermagem', 'Ativo', '24h', NULL, '#16a34a', NULL),
	(8, '6aff907b-52ac-11f1-a16a-089798669242', 'Vanusa Alves ', 'Rua Cornelio de Henriques de Almeida', '103 - 102', 'José Cirilo', 'Muriaé', 'MG', '36886-002', '056.571.246.26', 'vanusaag@gmail.com', '00.001.001-01', '1981-04-04', '(32) 98857-5765', '05657124626', 'Cuidador', 'Ativo', '24h', NULL, '#9333ea', NULL),
	(11, '6aff9261-52ac-11f1-a16a-089798669242', 'Silma Ana de Freitas', 'Rua Capitão Felisberto', '97', 'Barra', 'Muriaé', 'MG', '36886-000', '641.047.026-68', 'silmaanadefreitas23@gmail.com', '8-560.579', '1965-08-19', '(32) 00000-0000', 'silmaanadefreitas23@gmail.com', 'Cuidador', 'Inativo', '24h', NULL, '#2563eb', 'Irresponsabilidade com comunicação e horário. Deixando paciente sozinha sem aviso em tempo hábil.'),
	(12, '6aff9428-52ac-11f1-a16a-089798669242', 'Elisabeti Neves Filgueiras Batista', 'Rua Astrogildo Figueiredo Barros', '140', 'João XXIII', 'Muriaé', 'MG', '36886-000', '783.286.686-49', 'betineves699@gmail.com', '13.680.211', '1961-12-09', '(32) 99818-2560', '32998182560', 'Cuidador', 'Ativo', '24h', NULL, '#7c3aed', NULL),
	(13, '6aff959b-52ac-11f1-a16a-089798669242', 'Crislayne Rafaela Filipe', 'Rua Itália ', 'S/N', 'São Cristóvão', 'Muriaé', 'MG', '36886-000', '062.186.676-85', 'crislayne.96@hotmail.com', 'MG-19.715.854', '1996-01-04', '(32) 00000-0000', '447fd681-ec80-45b5-871c-bddc6a599415', 'Cuidador', 'Ativo', '6h', NULL, '#dc2626', NULL),
	(31, '6aff9a31-52ac-11f1-a16a-089798669242', 'Maria Aparecida Oliveira Paiva', 'Av Juscelino Kubitschek - Apto 101', '199', 'Centro', 'Muriaé', 'MG', '36880-000', '684.978.906-63', 'mariaapaiva21@gmail.com', 'MG-6.956.381', '1967-04-16', '(32) 98853-4604', '(32) 98853-4604', 'Cuidador', 'Ativo', '24h', NULL, '#2563eb', NULL),
	(32, '6aff988f-52ac-11f1-a16a-089798669242', 'Lais Vicente da Silva', 'Fazenda Paula Neves', 'SN', 'Divisório', 'Muriaé', 'MG', '36880-000', '082.246.496-95', 'laisvicente93@gmail.com', '19.099.614', '1993-10-06', '(32) 99999-9999', 'laisvicente93@gmail.com', 'Cuidador', 'Ativo', '6h', NULL, '#7c3aed', NULL),
	(33, '6aff96ec-52ac-11f1-a16a-089798669242', 'Simone Cristina da Silva', 'Rua Simeão Féres', '50', 'Barra', 'Muriaé', 'MG', '36886-000', '026.969-766-78', 'simone.thebestvip@gmail.com', 'MG-7.306.747', '1977-05-25', '(32) 98859-2572', '(32) 98859-2572', 'Acompanhante', 'Ativo', '24h', NULL, '#dc2626', NULL),
	(59, '6affa36d-52ac-11f1-a16a-089798669242', 'Rafaela Silveira', 'R Cornélio Henriques de Almeida', '103 ', 'José Cirilo', 'Muriaé', 'MG', '36886002', '11803473746', 'rafaela.frontend@gmail.com', '20.131.858-1', '1987-04-12', '32999906354', 'rafaelasilveira1987@gmail.com', 'Acompanhante', 'Inativo', '8h', NULL, '#0d9488', 'Teste'),
	(61, '6aff9e9b-52ac-11f1-a16a-089798669242', 'Gessimar Cristiane Cerqueira de Oliveira ', 'Rua Aberto José Ferreira', '12', 'São Vicente de Paula', 'Muriaé', 'MG', '36880080', '03674829673', 'cerqueiragessimar@gmail.com', '10.136.145', '1978-07-24', '32998017584', '(32) 99801-7584', 'Técnico de Enfermagem', 'Ativo', '12h', NULL, '#2563eb', NULL),
	(62, '6affa69f-52ac-11f1-a16a-089798669242', 'Thalyta Vitória de Souza Silva ', 'Rua Independência', '373', 'Santo Antônio', 'Muriaé', 'MG', '36881126', '70363839682', 'vtrthalyta4@gmail.com', '22.238.589', '2005-03-23', '32999999999', 'vtrthalyta4@gmail.com', 'Cuidador', 'Ativo', '12h', NULL, '#7c3aed', NULL),
	(63, '6affa932-52ac-11f1-a16a-089798669242', 'Myrian da Conceição Pinto de Almeida', 'Rua Santo Antônio', '301', 'Santo Antônio', 'Muriaé', 'MG', '36881110', '06026233679', 'Almeidamyriam24@gmail.com', '13.400.727', '1982-11-20', '32988888888', '06026233679', 'Cuidador', 'Ativo', '12h', NULL, '#dc2626', NULL),
	(64, '6affabce-52ac-11f1-a16a-089798669242', 'Joelma Marques de Campos', 'Rua Mário Martins', '65', 'Cardoso de Melo', 'Muriaé', 'MG', '36887243', '07120610708', 'joelmamarquesandrade1@gmail.com', '16.082.260', '1977-12-02', '32977777777', '79a5fc3d-75c2-4eda-9e36-f96441eb54f5', 'Cuidador', 'Ativo', '12h', NULL, '#ea580c', NULL),
	(66, '6affaebe-52ac-11f1-a16a-089798669242', 'Hyllary Carvalho e Paiva', 'Rua Maria José da Rocha', '23', 'Planalto', 'Muriaé', 'MG', '36883157', '12958678695', 'paivahyllary@gmail.com', '19.957.877', '2002-05-09', '32998713122', '32998713122', 'Cuidador', 'Ativo', '12h', NULL, '#be123c', NULL),
	(67, '6affb19d-52ac-11f1-a16a-089798669242', 'este Cuidador MVC', 'Rua  este', '001', NULL, 'Juiz de Fora', 'MG', '36000-000', '000.000.000-02', NULL, NULL, '1987-04-12', '32988008800', '32988008800', 'Cuidador', 'Standby', '12h', NULL, '#16a34a', NULL),
	(68, '7ea24127-59cc-11f1-b6c0-089798669242', 'Juliana Castro', 'Rua das Flores', '120', 'Centro', 'Muriaé', 'MG', '36880-000', '111.111.111-01', 'juliana@email.com', 'MG1111111', '1990-05-10', '32999990001', 'juliana@email.com', 'Técnico de Enfermagem', 'Ativo', '12h', NULL, '#eb3dcb', NULL),
	(69, '7ea311ea-59cc-11f1-b6c0-089798669242', 'Pedro Henrique', 'Rua A', '45', 'Safira', 'Muriaé', 'MG', '36880-001', '111.111.111-02', 'pedro@email.com', 'MG1111112', '1988-08-15', '32999990002', '32999990002', 'Cuidador', 'Ativo', '12h', NULL, '#3d18f7', NULL),
	(70, '7ea333b7-59cc-11f1-b6c0-089798669242', 'Camila Souza', 'Rua B', '88', 'Barra', 'Muriaé', 'MG', '36880-002', '111.111.111-03', 'camila@email.com', 'MG1111113', '1992-11-20', '32999990003', 'camila@email.com', 'Enfermeira', 'Ativo', '12h', NULL, '#0f766e', NULL),
	(71, '7ea33ad4-59cc-11f1-b6c0-089798669242', 'Bianca Lima', 'Rua C', '301', 'Centro', 'Muriaé', 'MG', '36880-003', '111.111.111-04', 'bianca@email.com', 'MG1111114', '1995-03-12', '32999990004', '32999990004', 'Cuidador', 'Ativo', '24h', NULL, '#ff5b24', NULL);

-- Copiando estrutura para tabela cuidar_no_lar.tb_diarioidoso
CREATE TABLE IF NOT EXISTS `tb_diarioidoso` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL DEFAULT (uuid()),
  `paciente_id` int NOT NULL,
  `visita_mensal` datetime DEFAULT (now()),
  `oxigenio` int DEFAULT NULL,
  `frequencia_cardiaca` int DEFAULT NULL,
  `temperatura` decimal(3,1) DEFAULT NULL,
  `pressao_arterial` varchar(10) DEFAULT NULL,
  `frequencia_respiratoria` int DEFAULT NULL,
  `hgt` decimal(6,2) DEFAULT NULL,
  `dor` int DEFAULT NULL,
  `peso` decimal(5,1) DEFAULT NULL,
  `altura` decimal(3,2) DEFAULT NULL,
  `historico_id` int DEFAULT NULL,
  `observacao` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_diarioidoso_uuid` (`uuid`),
  KEY `paciente_id` (`paciente_id`),
  KEY `historico_id` (`historico_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_diarioidoso: 8 rows
/*!40000 ALTER TABLE `tb_diarioidoso` DISABLE KEYS */;
INSERT INTO `tb_diarioidoso` (`id`, `uuid`, `paciente_id`, `visita_mensal`, `oxigenio`, `frequencia_cardiaca`, `temperatura`, `pressao_arterial`, `frequencia_respiratoria`, `hgt`, `dor`, `peso`, `altura`, `historico_id`, `observacao`) VALUES
	(1, '6b1486ba-52ac-11f1-a16a-089798669242', 1, '2024-12-02 00:00:00', 98, 90, 35.0, '130/98', 50, 120.00, 5, 47.0, 1.60, 1, NULL),
	(3, '6b1492e1-52ac-11f1-a16a-089798669242', 2, '2024-12-10 00:00:00', 99, 78, 36.1, '149/87', 19, 127.00, 2, 52.0, 1.50, 2, 'Paciente no dia da medição havia acabado de tomar café da tarde'),
	(4, '6b14a0fd-52ac-11f1-a16a-089798669242', 2, '2024-11-25 00:00:00', 96, 91, 35.4, '1.59', 2, 1.59, 2, 52.0, 1.50, NULL, NULL),
	(6, '6b14a3ad-52ac-11f1-a16a-089798669242', 1, '2024-12-19 00:00:00', 97, 72, 35.3, '110/70', 16, 144.00, 6, 52.0, 1.50, NULL, NULL),
	(7, '6b14a628-52ac-11f1-a16a-089798669242', 1, '2025-02-07 00:00:00', 96, 75, 36.5, '120/70', 16, 143.00, 8, 52.0, 1.50, NULL, NULL),
	(8, '6b14a8d3-52ac-11f1-a16a-089798669242', 8, '2025-04-25 00:00:00', 98, 87, 36.4, '12/8', 15, 119.00, 7, 83.0, 9.99, NULL, 'Rotina'),
	(9, '6b14ab87-52ac-11f1-a16a-089798669242', 1, '2025-04-30 00:00:00', 97, 72, 35.3, '120/70', 14, 120.00, 9, 40.0, 9.99, NULL, 'N/A'),
	(10, '6b14ae1a-52ac-11f1-a16a-089798669242', 10, '2026-05-13 10:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, ' este');
/*!40000 ALTER TABLE `tb_diarioidoso` ENABLE KEYS */;

-- Copiando estrutura para tabela cuidar_no_lar.tb_dispositivos_paciente
CREATE TABLE IF NOT EXISTS `tb_dispositivos_paciente` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL DEFAULT (uuid()),
  `paciente_id` int NOT NULL,
  `tipo` enum('Sonda Nasogástrica','Sonda Nasoenteral','Gastrostomia','Traqueostomia','Oxigênio','Cateter Vesical','Colostomia','PICC','Port-a-Cath','Outros') NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `data_insercao` date DEFAULT NULL,
  `data_retirada` date DEFAULT NULL,
  `protocolo_cuidado` text,
  `status` enum('Ativo','Inativo') DEFAULT 'Ativo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dispositivos_uuid` (`uuid`),
  KEY `idx_dispositivos_paciente` (`paciente_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_dispositivos_paciente: 0 rows
/*!40000 ALTER TABLE `tb_dispositivos_paciente` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_dispositivos_paciente` ENABLE KEYS */;

-- Copiando estrutura para tabela cuidar_no_lar.tb_escala_aprovacoes
CREATE TABLE IF NOT EXISTS `tb_escala_aprovacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `paciente_id` int NOT NULL,
  `escala_base_id` int NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `status` enum('em_edicao','aprovada','reaberta','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'em_edicao',
  `aprovado_por` int DEFAULT NULL,
  `aprovado_em` datetime DEFAULT NULL,
  `reaberto_por` int DEFAULT NULL,
  `reaberto_em` datetime DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_escala_aprovacao_periodo` (`escala_base_id`,`paciente_id`,`data_inicio`,`data_fim`),
  KEY `idx_escala_aprovacao_paciente_periodo` (`paciente_id`,`data_inicio`,`data_fim`),
  KEY `idx_escala_aprovacao_base` (`escala_base_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela cuidar_no_lar.tb_escala_aprovacoes: ~0 rows (aproximadamente)
INSERT INTO `tb_escala_aprovacoes` (`id`, `paciente_id`, `escala_base_id`, `data_inicio`, `data_fim`, `status`, `aprovado_por`, `aprovado_em`, `reaberto_por`, `reaberto_em`, `observacoes`, `criado_em`, `atualizado_em`) VALUES
	(1, 12, 3, '2026-05-31', '2026-06-06', 'aprovada', 1, '2026-05-31 05:07:06', NULL, NULL, 'Aprovação da escala do período. Plantões novos confirmados: 2', '2026-05-31 08:06:54', '2026-05-31 08:07:06');

-- Copiando estrutura para tabela cuidar_no_lar.tb_escala_base
CREATE TABLE IF NOT EXISTS `tb_escala_base` (
  `id` int NOT NULL AUTO_INCREMENT,
  `paciente_id` int NOT NULL,
  `nome` varchar(150) DEFAULT NULL,
  `tipo_cobertura` enum('24h','12h','8h','6h') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `tipo_atendimento` enum('domiciliar','hospitalar') DEFAULT 'domiciliar',
  `local` varchar(255) DEFAULT NULL,
  `recorrente` enum('sim','nao') DEFAULT 'sim',
  `domingo` tinyint(1) DEFAULT '1',
  `segunda` tinyint(1) DEFAULT '1',
  `terca` tinyint(1) DEFAULT '1',
  `quarta` tinyint(1) DEFAULT '1',
  `quinta` tinyint(1) DEFAULT '1',
  `sexta` tinyint(1) DEFAULT '1',
  `sabado` tinyint(1) DEFAULT '1',
  `revezamento_automatico` tinyint(1) DEFAULT '1',
  `ativo` tinyint(1) DEFAULT '1',
  `observacoes` text,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_tb_escala_base_paciente` (`paciente_id`),
  CONSTRAINT `fk_tb_escala_base_paciente` FOREIGN KEY (`paciente_id`) REFERENCES `tb_pacientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_escala_base: ~4 rows (aproximadamente)
INSERT INTO `tb_escala_base` (`id`, `paciente_id`, `nome`, `tipo_cobertura`, `hora_inicio`, `hora_fim`, `tipo_atendimento`, `local`, `recorrente`, `domingo`, `segunda`, `terca`, `quarta`, `quinta`, `sexta`, `sabado`, `revezamento_automatico`, `ativo`, `observacoes`, `criado_em`, `atualizado_em`) VALUES
	(1, 1, 'Cobertura João', '24h', '07:00:00', '07:00:00', 'domiciliar', NULL, 'sim', 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, '2026-05-21 15:14:56', '2026-05-21 15:14:56'),
	(2, 1, 'Cobertura João', '24h', '07:00:00', '07:00:00', 'domiciliar', 'Rua João Dornelas, 100 - Dornelas', 'sim', 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, '2026-05-21 15:44:28', '2026-05-29 18:48:12'),
	(3, 12, 'Escala base', '24h', '07:00:00', '19:00:00', 'domiciliar', 'Rua Alpha, 10', 'sim', 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, '2026-05-29 17:47:53', '2026-05-31 03:11:28'),
	(4, 16, 'Escala base', '12h', '07:00:00', '19:00:00', 'domiciliar', 'Rua Épsilon, 50', 'sim', 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, '2026-05-29 20:29:23', '2026-05-29 20:29:23');

-- Copiando estrutura para tabela cuidar_no_lar.tb_escala_ocorrencias
CREATE TABLE IF NOT EXISTS `tb_escala_ocorrencias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `escala_base_id` int DEFAULT NULL,
  `paciente_id` int NOT NULL,
  `cuidador_id` int DEFAULT NULL,
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
  UNIQUE KEY `uq_escala_slot` (`escala_base_id`,`paciente_id`,`data_plantao`,`inicio`,`fim`),
  KEY `fk_tb_ocorrencia_paciente` (`paciente_id`),
  KEY `idx_tb_ocorrencia_data` (`data_plantao`),
  KEY `idx_tb_ocorrencia_cuidador` (`cuidador_id`),
  KEY `idx_tb_ocorrencia_inicio_fim` (`inicio`,`fim`),
  KEY `idx_tb_ocorrencia_status` (`status`),
  CONSTRAINT `fk_tb_ocorrencia_cuidador` FOREIGN KEY (`cuidador_id`) REFERENCES `tb_cuidador` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tb_ocorrencia_escala` FOREIGN KEY (`escala_base_id`) REFERENCES `tb_escala_base` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tb_ocorrencia_paciente` FOREIGN KEY (`paciente_id`) REFERENCES `tb_pacientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_escala_ocorrencias: ~24 rows (aproximadamente)
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
	(108, NULL, 8, 67, '2026-05-23', '2026-05-23 19:00:00', '2026-05-24 07:00:00', '12h', 'previsto', 0, 0, NULL, '2026-05-22 15:28:23', '2026-05-22 15:28:23', 'Manual'),
	(109, NULL, 12, 68, '2026-05-29', '2026-05-29 07:00:00', '2026-05-29 19:00:00', '12h', 'previsto', 0, 0, NULL, '2026-05-29 19:28:05', '2026-05-29 19:28:05', 'Manual'),
	(110, NULL, 16, 69, '2026-05-30', '2026-05-30 07:00:00', '2026-05-31 07:00:00', '24h', 'previsto', 0, 0, NULL, '2026-05-29 19:28:39', '2026-05-29 19:28:39', 'Manual'),
	(111, NULL, 1, 7, '2026-06-01', '2026-06-01 19:00:00', '2026-06-02 07:00:00', '12h', 'previsto', 0, 0, NULL, '2026-05-30 01:06:41', '2026-05-30 01:06:41', 'Manual'),
	(112, 3, 12, 71, '2026-04-26', '2026-04-26 07:00:00', '2026-04-26 19:00:00', '24h', 'confirmado', 0, 0, NULL, '2026-05-31 00:26:37', '2026-05-31 00:26:37', 'Manual'),
	(113, 3, 12, 70, '2026-04-28', '2026-04-28 07:00:00', '2026-04-28 19:00:00', '24h', 'confirmado', 0, 0, NULL, '2026-05-31 00:27:09', '2026-05-31 00:29:26', 'Manual'),
	(114, 3, 12, 71, '2026-04-30', '2026-04-30 07:00:00', '2026-04-30 19:00:00', '24h', 'confirmado', 0, 0, NULL, '2026-05-31 00:28:33', '2026-05-31 00:29:26', 'Manual'),
	(115, 3, 12, 71, '2026-05-29', '2026-05-29 07:00:00', '2026-05-29 19:00:00', '24h', 'confirmado', 0, 0, NULL, '2026-05-31 00:32:38', '2026-05-31 00:33:09', 'Manual'),
	(116, 3, 12, 69, '2026-05-29', '2026-05-29 19:00:00', '2026-05-30 07:00:00', '24h', 'confirmado', 0, 0, NULL, '2026-05-31 00:33:06', '2026-05-31 00:33:09', 'Manual'),
	(117, 3, 12, 12, '2026-05-30', '2026-05-30 07:00:00', '2026-05-30 19:00:00', '24h', 'confirmado', 0, 0, NULL, '2026-05-31 00:33:16', '2026-05-31 00:58:29', 'Manual'),
	(118, 3, 12, 66, '2026-05-28', '2026-05-28 07:00:00', '2026-05-28 19:00:00', '24h', 'confirmado', 0, 0, NULL, '2026-05-31 01:00:30', '2026-05-31 01:00:30', 'Manual'),
	(125, 3, 12, 70, '2026-05-31', '2026-05-31 07:00:00', '2026-05-31 19:00:00', '12h', 'confirmado', 0, 0, NULL, '2026-05-31 03:13:01', '2026-05-31 03:13:01', 'Manual'),
	(126, 3, 12, 69, '2026-06-01', '2026-06-01 07:00:00', '2026-06-01 19:00:00', '12h', 'confirmado', 0, 0, NULL, '2026-05-31 03:13:01', '2026-05-31 03:13:01', 'Manual'),
	(127, 3, 12, 70, '2026-05-31', '2026-05-31 19:00:00', '2026-06-01 07:00:00', '12h', 'confirmado', 0, 0, NULL, '2026-05-31 03:13:01', '2026-05-31 03:13:01', 'Manual'),
	(128, 3, 12, 69, '2026-06-01', '2026-06-01 19:00:00', '2026-06-02 07:00:00', '12h', 'confirmado', 0, 0, NULL, '2026-05-31 03:13:01', '2026-05-31 03:13:01', 'Manual');

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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_escala_profissionais: ~11 rows (aproximadamente)
INSERT INTO `tb_escala_profissionais` (`id`, `escala_base_id`, `cuidador_id`, `ordem_revezamento`, `principal_escala`, `ativo`, `criado_em`) VALUES
	(1, 1, 1, 1, 1, 1, '2026-05-21 15:15:07'),
	(2, 1, 2, 2, 1, 1, '2026-05-21 15:15:07'),
	(3, 1, 1, 1, 1, 1, '2026-05-21 15:44:36'),
	(4, 1, 2, 2, 1, 1, '2026-05-21 15:44:36'),
	(5, 3, 2, 1, 1, 0, '2026-05-29 17:47:53'),
	(6, 3, 8, 2, 0, 0, '2026-05-29 17:47:53'),
	(7, 3, 2, 1, 1, 0, '2026-05-29 18:47:10'),
	(8, 3, 8, 2, 1, 0, '2026-05-29 18:47:10'),
	(9, 2, 3, 1, 1, 0, '2026-05-29 18:48:12'),
	(10, 2, 33, 2, 1, 0, '2026-05-29 18:48:12'),
	(11, 2, 3, 1, 1, 1, '2026-05-29 18:48:47'),
	(12, 3, 67, 1, 1, 0, '2026-05-29 19:44:05'),
	(13, 3, 69, 2, 1, 0, '2026-05-29 19:44:05'),
	(14, 4, 71, 1, 1, 1, '2026-05-29 20:29:23'),
	(15, 4, 70, 2, 1, 1, '2026-05-29 20:29:23'),
	(16, 3, 70, 1, 1, 1, '2026-05-31 03:11:28'),
	(17, 3, 69, 2, 1, 1, '2026-05-31 03:11:28');

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_escala_substituicoes: ~2 rows (aproximadamente)
INSERT INTO `tb_escala_substituicoes` (`id`, `ocorrencia_id`, `cuidador_original_id`, `cuidador_substituto_id`, `motivo`, `observacoes`, `criado_em`, `data_plantao`) VALUES
	(1, 108, 67, 62, 'falta', 'Não veio para o plantão', '2026-05-22 18:40:37', NULL),
	(2, 106, 8, 33, 'emergencia', NULL, '2026-05-23 00:03:25', NULL),
	(3, 109, 68, 70, 'atestado', NULL, '2026-05-30 22:13:41', NULL),
	(4, 114, 70, 70, 'Substituição operacional', NULL, '2026-05-31 00:29:17', '2026-04-30');

-- Copiando estrutura para tabela cuidar_no_lar.tb_eventos
CREATE TABLE IF NOT EXISTS `tb_eventos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL DEFAULT (uuid()),
  `paciente_id` int DEFAULT NULL,
  `tipo_evento` varchar(60) DEFAULT 'Outro',
  `titulo` varchar(255) NOT NULL,
  `descricao` text,
  `data_inicio` datetime DEFAULT NULL,
  `data_fim` datetime DEFAULT NULL,
  `local` varchar(180) DEFAULT NULL,
  `prioridade` enum('Baixa','Normal','Alta','Urgente') DEFAULT 'Normal',
  `data_evento` datetime NOT NULL,
  `cuidador_id` int DEFAULT NULL,
  `status` enum('Pendente','Agendado','Em andamento','Concluído','Cancelado') DEFAULT 'Pendente',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_eventos_uuid` (`uuid`),
  KEY `paciente_id` (`paciente_id`)
) ENGINE=MyISAM AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_eventos: 2 rows
/*!40000 ALTER TABLE `tb_eventos` DISABLE KEYS */;
INSERT INTO `tb_eventos` (`id`, `uuid`, `paciente_id`, `tipo_evento`, `titulo`, `descricao`, `data_inicio`, `data_fim`, `local`, `prioridade`, `data_evento`, `cuidador_id`, `status`) VALUES
	(80, '6b27364c-52ac-11f1-a16a-089798669242', 10, 'Outro', 'Visita técnica', 'Hospital São Paulo', '2026-05-20 10:00:00', NULL, NULL, 'Normal', '2026-05-20 10:00:00', 67, 'Pendente'),
	(82, 'e7c98eb2-5b58-11f1-b6c0-089798669242', NULL, 'Avaliação inicial', 'Avaliação novo paciente', 'novo paciente para inclusão', '2026-05-30 08:00:00', '2026-05-30 10:00:00', 'Domicilio', 'Alta', '2026-05-30 08:00:00', NULL, 'Agendado');
/*!40000 ALTER TABLE `tb_eventos` ENABLE KEYS */;

-- Copiando estrutura para tabela cuidar_no_lar.tb_financeiro
CREATE TABLE IF NOT EXISTS `tb_financeiro` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL DEFAULT (uuid()),
  `responsavel_id` int DEFAULT NULL,
  `cuidador_id` int DEFAULT NULL,
  `paciente_id` int DEFAULT NULL,
  `plano_id` varchar(50) DEFAULT NULL,
  `data` datetime NOT NULL DEFAULT (now()),
  `data_vencimento` date DEFAULT NULL,
  `data_pagamento` date DEFAULT NULL,
  `tipo_transacao` enum('Entrada','Saída') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `categoria_id` int unsigned DEFAULT NULL,
  `moeda` enum('Pix','Depósito','Boleto','Dinheiro') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `detalhes` text,
  `status` enum('Pendente','Pago','Transporte') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `observacoes` tinytext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_financeiro_uuid` (`uuid`),
  KEY `responsavel_id` (`responsavel_id`),
  KEY `cuidador_id` (`cuidador_id`),
  KEY `fk_plano` (`plano_id`),
  KEY `idx_financeiro_categoria` (`categoria_id`),
  KEY `idx_financeiro_vencimento` (`data_vencimento`)
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_financeiro: 27 rows
/*!40000 ALTER TABLE `tb_financeiro` DISABLE KEYS */;
INSERT INTO `tb_financeiro` (`id`, `uuid`, `responsavel_id`, `cuidador_id`, `paciente_id`, `plano_id`, `data`, `data_vencimento`, `data_pagamento`, `tipo_transacao`, `categoria_id`, `moeda`, `valor`, `descricao`, `detalhes`, `status`, `observacoes`) VALUES
	(1, '6b9be76c-52ac-11f1-a16a-089798669242', 1, NULL, NULL, '1', '2024-11-19 00:00:00', '2026-05-14', NULL, 'Entrada', NULL, 'Pix', 1000.00, 'Valor referente a serviço de cuidados final de semana mês 10', NULL, 'Pago', 'Valor referente a serviço de cuidados final de semana mês 10'),
	(2, '6b9cd1e4-52ac-11f1-a16a-089798669242', NULL, 3, NULL, '24h', '2024-11-19 00:00:00', '2026-05-14', NULL, 'Saída', NULL, 'Pix', 1000.00, 'valor pago a cuidador por prestação de cuidados mês 10', NULL, 'Pago', 'valor pago a cuidador por prestação de cuidados mês 10'),
	(4, '6b9cda5f-52ac-11f1-a16a-089798669242', 2, NULL, NULL, '24h', '2024-11-10 00:00:00', '2026-05-14', NULL, 'Entrada', NULL, 'Pix', 2000.00, 'Referente a serviço 10/2024', NULL, 'Pago', 'Referente a serviço 10/2024'),
	(5, '6b9cdeff-52ac-11f1-a16a-089798669242', NULL, 4, NULL, '24h', '2024-11-10 00:00:00', '2026-05-14', NULL, 'Saída', NULL, 'Pix', 2000.00, 'Valor referente a plantões 10/2024', NULL, 'Pago', 'Valor referente a plantões 10/2024'),
	(6, '6b9ce40e-52ac-11f1-a16a-089798669242', 2, NULL, NULL, '24h', '2024-11-10 00:00:00', '2026-05-14', NULL, 'Entrada', NULL, 'Pix', 3000.00, 'Referente a serviços de cuidados ao idoso prestados no mês 10/2024', NULL, 'Pago', 'Referente a serviços de cuidados ao idoso prestados no mês 10/2024'),
	(7, '6b9ce839-52ac-11f1-a16a-089798669242', NULL, 8, NULL, '24h', '2024-12-19 00:00:00', '2026-05-14', NULL, 'Saída', NULL, 'Pix', 3000.00, 'Referente a serviço de cuidados ao idoso referente ao mês 10/2024', NULL, 'Pago', 'Referente a serviço de cuidados ao idoso referente ao mês 10/2024'),
	(11, '6b9cf070-52ac-11f1-a16a-089798669242', NULL, 11, NULL, '24h', '2025-01-06 00:00:00', '2026-05-14', NULL, 'Saída', NULL, 'Pix', 434.00, 'Acerto de cuidados a paciente Ormezinda em 2 finais de semana (21 e 22/12 // 28 e 29/12 ano de 2024). Com desconto de vale transporte referente a esses dias (R$ 16,00) + desconto de 10% (R$ 50,00).', NULL, 'Pago', 'Acerto de cuidados a paciente Ormezinda em 2 finais de semana (21 e 22/12 // 28 e 29/12 ano de 2024). Com desconto de vale transporte referente a esses dias (R$ 16,00) + desconto de 10% (R$ 50,00).'),
	(12, '6b9cec3c-52ac-11f1-a16a-089798669242', 1, NULL, NULL, '24h', '2025-01-17 00:00:00', '2026-05-14', NULL, 'Entrada', NULL, 'Dinheiro', 1000.00, 'Referente a serviço de cuidados a dona Ormezinda mês 12/2024', NULL, 'Pago', 'Referente a serviço de cuidados a dona Ormezinda mês 12/2024'),
	(14, '6b9cf452-52ac-11f1-a16a-089798669242', 2, NULL, NULL, '24h', '2025-02-10 00:00:00', '2026-05-14', NULL, 'Entrada', NULL, 'Pix', 3000.00, 'Referente aos cuidados da paciente Maria da Penha no mês 01/2025', NULL, 'Pago', 'Referente aos cuidados da paciente Maria da Penha no mês 01/2025'),
	(15, '6b9cf890-52ac-11f1-a16a-089798669242', NULL, 1, NULL, '1', '2025-01-19 00:00:00', '2026-05-14', NULL, 'Saída', NULL, 'Pix', 200.00, 'Serviço de cuidados prestados a Maria da Penha, plantão domingo.', NULL, 'Pago', 'Serviço de cuidados prestados a Maria da Penha, plantão domingo.'),
	(16, '6b9cfc74-52ac-11f1-a16a-089798669242', NULL, 1, NULL, '4', '2025-01-24 00:00:00', '2026-05-14', NULL, 'Saída', NULL, 'Pix', 500.00, 'Serviços de cuidados paciente Ormezinda, aos finais de semana. ', NULL, 'Pago', 'Serviços de cuidados paciente Ormezinda, aos finais de semana. '),
	(17, '6b9d00d7-52ac-11f1-a16a-089798669242', NULL, 1, NULL, '4', '2025-01-25 00:00:00', '2026-05-14', NULL, 'Saída', NULL, 'Pix', 200.00, '', NULL, 'Pago', ''),
	(18, '6b9d050c-52ac-11f1-a16a-089798669242', NULL, 1, NULL, '4', '2025-01-31 20:49:00', '2026-05-14', NULL, 'Saída', 2, 'Pix', 37.00, 'Adiantamento de vale transporte', NULL, 'Pago', 'Adiantamento de vale transporte'),
	(19, '6b9d0d2e-52ac-11f1-a16a-089798669242', NULL, 1, NULL, '4', '2025-01-24 20:54:25', '2026-05-14', NULL, 'Saída', NULL, 'Pix', 37.00, 'Adiantamento vale transporte', NULL, 'Pago', 'Adiantamento vale transporte'),
	(20, '6b9d117e-52ac-11f1-a16a-089798669242', NULL, 1, NULL, '4', '2025-02-02 00:00:00', '2026-05-14', NULL, 'Saída', NULL, 'Pix', 200.00, 'Serviço de cuidados a paciente Maria da Penha, plantão extra de domingo.', NULL, 'Pago', 'Serviço de cuidados a paciente Maria da Penha, plantão extra de domingo.'),
	(21, '6b9d159e-52ac-11f1-a16a-089798669242', 1, NULL, NULL, '4', '2025-02-19 00:00:00', '2026-05-14', NULL, 'Entrada', NULL, 'Pix', 1000.00, 'Serviços de cuidados a paciente Ormezinda mês 01/2025', NULL, 'Pago', 'Serviços de cuidados a paciente Ormezinda mês 01/2025'),
	(22, '6b9d199e-52ac-11f1-a16a-089798669242', 1, 1, NULL, '4', '2025-02-15 00:00:00', '2026-05-14', NULL, 'Saída', NULL, 'Pix', 37.00, 'Adiantamento para transporte de 15 a 17/02/25', NULL, 'Pago', 'Adiantamento para transporte de 15 a 17/02/25'),
	(34, '6b9d2d56-52ac-11f1-a16a-089798669242', 1, 1, NULL, '4', '2025-02-15 00:00:00', '2026-05-14', NULL, 'Saída', NULL, 'Pix', 200.00, 'Plantão extra', NULL, 'Pago', 'Plantão extra'),
	(35, '6b9d31e8-52ac-11f1-a16a-089798669242', 1, 1, NULL, '4', '2025-03-02 00:00:00', '2026-05-14', NULL, 'Saída', NULL, 'Pix', 200.00, 'Plantão extra', NULL, 'Pago', 'Plantão extra'),
	(38, '6b9d1dc4-52ac-11f1-a16a-089798669242', 1, 1, NULL, '4', '2025-03-19 00:00:00', '2026-05-14', NULL, 'Entrada', NULL, 'Pix', 1000.00, 'Mês 03/2025', NULL, 'Pago', 'Mês 03/2025'),
	(46, '6b9d21aa-52ac-11f1-a16a-089798669242', 2, 8, 2, '4', '2026-03-10 00:00:00', '2026-03-10', '2026-03-10', 'Entrada', 1, 'Pix', 3000.00, 'Mês 02/2025', NULL, 'Pago', 'Mês 02/2025'),
	(47, '6b9d2596-52ac-11f1-a16a-089798669242', NULL, 66, NULL, '3', '2025-05-09 00:00:00', '2026-05-14', NULL, 'Saída', NULL, 'Pix', 120.00, 'Plantão Fundação', NULL, 'Pago', 'Plantão Fundação'),
	(48, '6b9d297b-52ac-11f1-a16a-089798669242', NULL, 64, NULL, '3', '2025-05-09 00:00:00', '2026-05-14', NULL, 'Saída', NULL, 'Pix', 510.00, 'Serviços de cuidador de idoso - 05/05/2025  09/05/2025', NULL, 'Pago', 'Serviços de cuidador de idoso - 05/05/2025  09/05/2025'),
	(49, '6b9d35e4-52ac-11f1-a16a-089798669242', NULL, 61, NULL, '3', '2025-05-10 00:00:00', '2026-05-14', NULL, 'Saída', NULL, 'Pix', 40.00, 'Plantão Fundação', NULL, 'Pago', 'Plantão Fundação'),
	(52, '6b9d39d3-52ac-11f1-a16a-089798669242', NULL, 1, NULL, '4', '2025-04-20 00:00:00', '2026-01-15', '2026-01-15', 'Saída', 2, 'Pix', 250.00, 'Serviços de cuidados aos finais de semana', NULL, 'Pago', 'Serviços de cuidados aos finais de semana'),
	(53, '6b9d3db5-52ac-11f1-a16a-089798669242', 42, 67, 10, NULL, '2026-05-13 10:00:00', '2026-05-10', NULL, 'Entrada', NULL, NULL, 10.00, ' este', NULL, 'Pendente', ' este'),
	(54, '6b9d4189-52ac-11f1-a16a-089798669242', NULL, NULL, NULL, NULL, '2026-05-13 10:00:00', '2026-05-10', NULL, 'Saída', NULL, NULL, 0.00, ' este saida', NULL, 'Pendente', ' este saida');
/*!40000 ALTER TABLE `tb_financeiro` ENABLE KEYS */;

-- Copiando estrutura para tabela cuidar_no_lar.tb_historico
CREATE TABLE IF NOT EXISTS `tb_historico` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL DEFAULT (uuid()),
  `paciente_id` int NOT NULL,
  `historico_familiar` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `historico_profissional` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `historico_medico` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `internacoes` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `necessidades` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `limitacoes` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `status` enum('Finalizado','Pendente') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'Pendente',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uq_historico_uuid` (`uuid`),
  KEY `paciente_id` (`paciente_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_historico: 3 rows
/*!40000 ALTER TABLE `tb_historico` DISABLE KEYS */;
INSERT INTO `tb_historico` (`id`, `uuid`, `paciente_id`, `historico_familiar`, `historico_profissional`, `historico_medico`, `internacoes`, `necessidades`, `limitacoes`, `status`) VALUES
	(1, '6bb19be6-52ac-11f1-a16a-089798669242', 1, 'Nenhum', 'Consulta de rotina', 'Padrão', 'Exames', 'Nenhuma', 'Nenhuma', 'Pendente'),
	(2, '6bb1a48f-52ac-11f1-a16a-089798669242', 2, 'Paciente com alto ídice de disposição para doenças mentais.', 'Paciente sem histórico profissional, apenas hobbie de crochê.', 'Paciente com esquisofrenia, recém acometida por um AVC com sequelas no lado direito.', 'Sem detalhes sobre internações, apena registro de quando teve AVC.', 'Transferência de cama para cadeira, banho na cadei', 'Locomoção prejudicada devido AVC.', 'Finalizado'),
	(3, '6bb1a6f9-52ac-11f1-a16a-089798669242', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendente');
/*!40000 ALTER TABLE `tb_historico` ENABLE KEYS */;

-- Copiando estrutura para tabela cuidar_no_lar.tb_intercorrencias
CREATE TABLE IF NOT EXISTS `tb_intercorrencias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `plantao_id` int NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `gravidade` enum('leve','media','grave') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horario` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_intercorrencias_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela cuidar_no_lar.tb_intercorrencias: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela cuidar_no_lar.tb_lancamentos
CREATE TABLE IF NOT EXISTS `tb_lancamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `tipo_transacao` enum('Entrada','Saida') COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `status` enum('Pendente','Pago','Cancelado') COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_vencimento` date DEFAULT NULL,
  `data_pagamento` date DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `detalhes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_lancamentos_uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela cuidar_no_lar.tb_lancamentos: ~108 rows (aproximadamente)
INSERT INTO `tb_lancamentos` (`id`, `uuid`, `tipo_transacao`, `valor`, `status`, `data_vencimento`, `data_pagamento`, `descricao`, `detalhes`, `created_at`, `updated_at`) VALUES
	(1, '6bdba00a-52ac-11f1-a16a-089798669242', 'Entrada', 1000.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(2, '6bdba833-52ac-11f1-a16a-089798669242', 'Saida', 1000.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(3, '6bdbaad9-52ac-11f1-a16a-089798669242', 'Entrada', 2000.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(4, '6bdbaf40-52ac-11f1-a16a-089798669242', 'Entrada', 2000.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(5, '6bdbb1d2-52ac-11f1-a16a-089798669242', 'Saida', 2000.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(6, '6bdbb4f6-52ac-11f1-a16a-089798669242', 'Entrada', 3000.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(7, '6bdbb80f-52ac-11f1-a16a-089798669242', 'Saida', 3000.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(8, '6bdbba60-52ac-11f1-a16a-089798669242', 'Saida', 434.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(9, '6bdbbcd6-52ac-11f1-a16a-089798669242', 'Entrada', 3000.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(10, '6bdbc064-52ac-11f1-a16a-089798669242', 'Saida', 200.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(11, '6bdbc3f7-52ac-11f1-a16a-089798669242', 'Saida', 434.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(12, '6bdbc7cb-52ac-11f1-a16a-089798669242', 'Entrada', 1000.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(13, '6bdbcb0f-52ac-11f1-a16a-089798669242', 'Saida', 37.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(14, '6bdbcd43-52ac-11f1-a16a-089798669242', 'Entrada', 3000.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(15, '6bdbcfcb-52ac-11f1-a16a-089798669242', 'Saida', 200.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(16, '6bdbd34d-52ac-11f1-a16a-089798669242', 'Saida', 500.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(17, '6bdbd66b-52ac-11f1-a16a-089798669242', 'Saida', 200.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(18, '6bdbda1d-52ac-11f1-a16a-089798669242', 'Saida', 37.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(19, '6bdbdfa4-52ac-11f1-a16a-089798669242', 'Saida', 37.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(20, '6bdbe34b-52ac-11f1-a16a-089798669242', 'Saida', 200.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(21, '6bdbe657-52ac-11f1-a16a-089798669242', 'Entrada', 1000.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(22, '6bdbe94c-52ac-11f1-a16a-089798669242', 'Saida', 37.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(23, '6bdbec8a-52ac-11f1-a16a-089798669242', 'Saida', 200.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(24, '6bdbef8e-52ac-11f1-a16a-089798669242', 'Saida', 40.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(25, '6bdbf3c8-52ac-11f1-a16a-089798669242', 'Saida', 250.00, 'Pago', '2026-01-15', '2026-01-15', NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(26, '6bdbf5a2-52ac-11f1-a16a-089798669242', 'Entrada', 10.00, 'Pendente', NULL, NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(27, '6bdbf75e-52ac-11f1-a16a-089798669242', 'Saida', 0.00, 'Pendente', NULL, NULL, NULL, NULL, '2026-05-14 17:32:23', '2026-05-18 11:26:32'),
	(32, '6bdbf891-52ac-11f1-a16a-089798669242', 'Entrada', 1000.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(33, '6bdbf9c6-52ac-11f1-a16a-089798669242', 'Saida', 1000.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(34, '6bdbfcb2-52ac-11f1-a16a-089798669242', 'Saida', 200.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(35, '6bdc0031-52ac-11f1-a16a-089798669242', 'Saida', 200.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(36, '6bdc03fc-52ac-11f1-a16a-089798669242', 'Entrada', 3000.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(37, '6bdc08f8-52ac-11f1-a16a-089798669242', 'Saida', 3000.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(38, '6bdc0d12-52ac-11f1-a16a-089798669242', 'Entrada', 1000.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(39, '6bdc0f19-52ac-11f1-a16a-089798669242', 'Saida', 434.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(40, '6bdc11bd-52ac-11f1-a16a-089798669242', 'Entrada', 3000.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(41, '6bdc138a-52ac-11f1-a16a-089798669242', 'Saida', 200.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(42, '6bdc151f-52ac-11f1-a16a-089798669242', 'Saida', 500.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(43, '6bdc165c-52ac-11f1-a16a-089798669242', 'Saida', 200.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(44, '6bdc1780-52ac-11f1-a16a-089798669242', 'Saida', 37.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(45, '6bdc18da-52ac-11f1-a16a-089798669242', 'Saida', 37.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(46, '6bdc1a00-52ac-11f1-a16a-089798669242', 'Entrada', 3000.00, 'Pago', '2026-03-10', '2026-03-10', NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(47, '6bdc1b27-52ac-11f1-a16a-089798669242', 'Saida', 120.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(48, '6bdc1c4a-52ac-11f1-a16a-089798669242', 'Saida', 510.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(49, '6bdc1d65-52ac-11f1-a16a-089798669242', 'Saida', 40.00, 'Pago', '2026-05-14', NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(50, '6bdc1f0e-52ac-11f1-a16a-089798669242', 'Entrada', 3000.00, 'Pago', '2026-03-10', '2026-03-10', NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(51, '6bdc2037-52ac-11f1-a16a-089798669242', 'Saida', 120.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(52, '6bdc2170-52ac-11f1-a16a-089798669242', 'Saida', 250.00, 'Pago', '2026-01-15', '2026-01-15', NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(53, '6bdc2292-52ac-11f1-a16a-089798669242', 'Entrada', 10.00, 'Pendente', '2026-05-10', NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(54, '6bdc23a6-52ac-11f1-a16a-089798669242', 'Saida', 0.00, 'Pendente', '2026-05-10', NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(55, '6bdc24c3-52ac-11f1-a16a-089798669242', 'Saida', 40.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(56, '6bdc25d9-52ac-11f1-a16a-089798669242', 'Saida', 250.00, 'Pago', '2026-01-15', '2026-01-15', NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(57, '6bdc26f2-52ac-11f1-a16a-089798669242', 'Entrada', 10.00, 'Pendente', NULL, NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(58, '6bdc2837-52ac-11f1-a16a-089798669242', 'Saida', 0.00, 'Pendente', NULL, NULL, NULL, NULL, '2026-05-14 17:34:50', '2026-05-18 11:26:32'),
	(63, '6bdc2958-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:46', '2026-05-18 11:26:32'),
	(64, '6bdc2a69-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:46', '2026-05-18 11:26:32'),
	(65, '6bdc2b79-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:46', '2026-05-18 11:26:32'),
	(66, '6bdc2c8b-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:46', '2026-05-18 11:26:32'),
	(67, '6bdc2d9d-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:46', '2026-05-18 11:26:32'),
	(68, '6bdc2eca-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:46', '2026-05-18 11:26:32'),
	(69, '6bdc3009-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:46', '2026-05-18 11:26:32'),
	(70, '6bdc311c-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(71, '6bdc3230-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(72, '6bdc3347-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(73, '6bdc34ba-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(74, '6bdc3606-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(75, '6bdc6f94-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(76, '6bdc742d-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(77, '6bdc7733-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(78, '6bdc79c2-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(79, '6bdc7c28-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(80, '6bdc7ecf-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(81, '6bdc8136-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(82, '6bdc8451-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(83, '6bdc86c8-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(84, '6bdc896f-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(85, '6bdc8d12-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(86, '6bdc8f8c-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(87, '6bdc91a2-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(88, '6bdc93c9-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(89, '6bdc9648-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:47', '2026-05-18 11:26:32'),
	(90, '6bdc982f-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(91, '6bdc9a5d-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(92, '6bdc9c93-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(93, '6bdc9ef3-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(94, '6bdca1eb-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(95, '6bdca471-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(96, '6bdca6de-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(97, '6bdca9a0-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(98, '6bdcabbb-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(99, '6bdcadf8-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(100, '6bdcb0db-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(101, '6bdcb35e-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(102, '6bdcb5ba-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(103, '6bdcb7e0-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(104, '6bdcba3b-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(105, '6bdcbcad-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(106, '6bdcbf0f-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(107, '6bdcc160-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(108, '6bdcc36c-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(109, '6bdcc573-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:48', '2026-05-18 11:26:32'),
	(110, '6bdcc79e-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:49', '2026-05-18 11:26:32'),
	(111, '6bdcc9c9-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:49', '2026-05-18 11:26:32'),
	(112, '6bdccc12-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pago', NULL, NULL, NULL, NULL, '2026-05-14 18:24:49', '2026-05-18 11:26:32'),
	(113, '6bdccf29-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pendente', NULL, NULL, NULL, NULL, '2026-05-14 18:24:49', '2026-05-18 11:26:32'),
	(114, '6bdcd1c3-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pendente', NULL, NULL, NULL, NULL, '2026-05-14 18:24:49', '2026-05-18 11:26:32'),
	(115, '6bdcd3af-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pendente', NULL, NULL, NULL, NULL, '2026-05-14 18:24:49', '2026-05-18 11:26:32'),
	(116, '6bdcd58e-52ac-11f1-a16a-089798669242', 'Entrada', 0.00, 'Pendente', NULL, NULL, NULL, NULL, '2026-05-14 18:24:49', '2026-05-18 11:26:32');

-- Copiando estrutura para tabela cuidar_no_lar.tb_medicacoes_paciente
CREATE TABLE IF NOT EXISTS `tb_medicacoes_paciente` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL DEFAULT (uuid()),
  `paciente_id` int NOT NULL,
  `created_by` int DEFAULT NULL,
  `nome_medicamento` varchar(150) NOT NULL,
  `apresentacao` varchar(100) DEFAULT NULL,
  `dosagem` varchar(100) DEFAULT NULL,
  `via` enum('VO','EV','IM','SC','SL','Retal','Tópica','Inalatória','GTT','SNE','Gastrostomia') DEFAULT NULL,
  `horarios` varchar(255) DEFAULT NULL,
  `frequencia` varchar(100) DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `observacoes` text,
  `status` enum('Ativo','Inativo') DEFAULT 'Ativo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_medicacoes_uuid` (`uuid`),
  KEY `idx_medicacoes_paciente` (`paciente_id`),
  KEY `fk_medicacao_usuario` (`created_by`),
  CONSTRAINT `fk_medicacao_usuario` FOREIGN KEY (`created_by`) REFERENCES `tb_usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_medicacoes_paciente: ~5 rows (aproximadamente)
INSERT INTO `tb_medicacoes_paciente` (`id`, `uuid`, `paciente_id`, `created_by`, `nome_medicamento`, `apresentacao`, `dosagem`, `via`, `horarios`, `frequencia`, `data_inicio`, `data_fim`, `observacoes`, `status`, `created_at`, `updated_at`, `updated_by`) VALUES
	(1, '4fdb434e-544a-11f1-a16a-089798669242', 8, 1, 'Desve', NULL, '200mg', 'VO', '09:00', '1x ao dia', NULL, NULL, NULL, 'Ativo', '2026-05-20 12:49:16', '2026-05-20 20:31:41', NULL),
	(2, '4fdb986e-544a-11f1-a16a-089798669242', 8, 1, 'Lisden', NULL, '30mg', 'VO', '07:00', '1x ao dia', NULL, NULL, NULL, 'Ativo', '2026-05-20 12:49:16', '2026-05-20 20:31:41', NULL),
	(3, '8135f11b-561c-11f1-a16a-089798669242', 11, 1, 'Simeticona', NULL, '1', 'VO', '20:00', 'Diário', NULL, NULL, NULL, 'Ativo', '2026-05-22 20:26:25', '2026-05-22 20:26:25', NULL),
	(4, 'c53c5417-5ac5-11f1-b6c0-089798669242', 12, 1, 'Venvance', NULL, '30mg', 'VO', '07:00', '1x ao dia', NULL, NULL, NULL, 'Ativo', '2026-05-28 18:48:08', '2026-05-28 18:48:08', NULL),
	(6, '21aa071b-5aca-11f1-b6c0-089798669242', 1, 1, 'Sinvastatina', NULL, '20mg', 'VO', '19:00', '1x ao dia', NULL, NULL, NULL, 'Ativo', '2026-05-28 19:19:21', '2026-05-28 19:19:21', NULL);

-- Copiando estrutura para tabela cuidar_no_lar.tb_medicacoes_plantao
CREATE TABLE IF NOT EXISTS `tb_medicacoes_plantao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `plantao_id` int NOT NULL,
  `medicacao_paciente_id` int DEFAULT NULL,
  `medicamento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `via` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horario` time DEFAULT NULL,
  `status` enum('administrado','pendente','recusado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `observacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_medicacoes_plantao_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela cuidar_no_lar.tb_medicacoes_plantao: ~0 rows (aproximadamente)

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

-- Copiando dados para a tabela cuidar_no_lar.tb_pacientes: ~11 rows (aproximadamente)
INSERT INTO `tb_pacientes` (`id`, `uuid`, `prontuario`, `nome_completo`, `diagnostico`, `cid_principal`, `diagnostico_principal`, `motivo_homecare`, `usa_sonda`, `usa_oxigenio`, `traqueostomia`, `gastrostomia`, `colostomia`, `cateter_vesical`, `observacoes_clinicas`, `data_nascimento`, `cpf`, `rg`, `cartao_nac_sus`, `plano_saude`, `responsavel_id`, `cuidador_id`, `anamnese_id`, `status`, `motivo_inativacao`, `sexo`, `foto`, `endereco_completo`, `telefone_principal`, `telefone_secundario`, `email`, `responsavel_nome_texto`, `responsavel_parentesco`, `responsavel_telefone`, `responsavel_email`, `comorbidades`, `alergias`, `historico_cirurgico`, `tipo_sanguineo`, `peso`, `altura`, `dieta_tipo`, `dieta_restricao`, `alimentacao_via`, `sonda_vesical`, `incontinencia`, `mobilidade`, `estado_cognitivo_base`, `gtt`, `sne`, `cateter_venoso`, `picc`, `lesao_pressao`, `curativos`, `areas_risco`, `condutas_permanentes`, `convenio`, `numero_carteirinha`, `prescricao_medica`, `termos_assinados`, `cor_avatar`, `cor_avatar_t`) VALUES
	(1, '6c0ec678-52ac-11f1-a16a-089798669242', 'PRT-2026-1.0000', 'Ormezinda Peres de Carvalho', 'Alzheimer', 'CID-10 G30', 'Doença neurológica degenerativa de evolução lenta e progressiva', 'Não pode ficar sozinha', 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', NULL, '1926-03-30', '423.836.506-25', NULL, NULL, 'Unimed', 1, 3, NULL, 'Ativo', NULL, 'Feminino', NULL, 'Rua João Dornelas, 100 - Dornelas', '32 98888- 0000', NULL, 'teste@ormezinda.com.br', 'Lúcia', 'Filha', '32 99999 - 0001', 'lucia@teste.com.br', 'Diabetes, pressão alta, incontinência urinária', 'Nenhuma', 'Nenhum', 'O+', 47.00, 1.40, 'Normal', NULL, 'VO', 'Nao', 'Sim', 'Deambula com auxilio', 'Demencia', 'Nao', 'Nao', 'Nao', 'Nao', 'Nao', NULL, NULL, '["Controle glicemico"]', NULL, NULL, NULL, NULL, NULL, NULL),
	(2, '6c0ed381-52ac-11f1-a16a-089798669242', 'PRT-2026-2.0000', 'Maria da Penha Martins', NULL, NULL, NULL, NULL, 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', NULL, '1951-01-28', '335.489.947-68', NULL, NULL, NULL, 2, 8, NULL, 'Ativo', NULL, 'Feminino', NULL, 'Av Constantino Pinto', '32 99999-0002', NULL, NULL, 'Márcia', 'Filha', '32 99999-0002', 'marcia@teste.com.br', NULL, NULL, NULL, NULL, 59.00, 1.61, NULL, NULL, NULL, 'Nao', 'VO', 'Acamado', NULL, 'Nao', 'Nao', 'Nao', 'Nao', 'Nao', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(8, '6c0ed764-52ac-11f1-a16a-089798669242', 'PRT-2026-3.0000', 'Rafaela', 'Demência', 'I63.9', 'Paciente com demência', 'Auxílio no cotidiano no cuidado', 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', NULL, '1987-04-12', '118.034.737-46', '20.131.858-1', '532 9871 4346', '12154677', 36, 8, NULL, 'Ativo', NULL, 'Feminino', NULL, 'Rua Cornelio Henriques de Almeida, 103 - apto 102', '32 998416669', '32 988575765', 'teste@rafa.com', 'Neide Maria', 'Mãe', NULL, NULL, 'Nenhuma', 'A pessoas', 'Nenhuma', 'O+', 86.00, 1.74, 'Come de tudo', 'Fechar a boca', 'VO', 'Nao', 'Urinária', 'Independente', 'Orientado', 'Nao', 'Nao', 'Nao', 'Nao', 'Nao', NULL, NULL, '["Aspiracao de vias aereas"]', NULL, NULL, 'Doidinha por natureza - cuidado que morde', NULL, NULL, NULL),
	(9, '6c0edb0d-52ac-11f1-a16a-089798669242', 'PRT-2026-4.0000', 'Teste MVC Paciente Editado', NULL, NULL, NULL, NULL, 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', NULL, '2026-05-13', '0000000000', '00000', '00000000000000000000', 'Unimed', 36, 59, NULL, 'Inativo', 'Teste rollback', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(10, '6c0ee96d-52ac-11f1-a16a-089798669242', 'PRT-2026-5.0000', 'Teste Paciente MVC Rel', NULL, NULL, NULL, NULL, 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', NULL, NULL, NULL, NULL, NULL, NULL, 42, 67, NULL, 'Ativo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(11, '8133a753-561c-11f1-a16a-089798669242', 'PRT-2026-6.0000', 'Sonia das Graças', 'Alzheimer', 'I63.9', 'Alzheimer', 'Não pode ficar sozinha, está fazendo arte.', 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', NULL, '1950-06-20', '00011122233344', '2020020030', '1234567890', 'Não tem', 42, 8, NULL, 'Ativo', NULL, 'Feminino', NULL, 'Avenida JK, 431 - Centro - Muriaé', '32988414106', NULL, 'vovosoninha@gmail.com', 'Vanusa', 'Filha', '32988575765', 'vansua@teste.com.br', 'Pressão alta', 'Dipirona', 'Nenhum', 'O+', 50.00, 1.59, NULL, NULL, NULL, 'Nao', NULL, NULL, NULL, 'Nao', 'Nao', 'Nao', 'Nao', 'Nao', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(12, '7ea9f297-59cc-11f1-b6c0-089798669242', 'PRT-2026-7.0000', 'Ana Luiza Martins', 'Bronquiolite', 'J21', 'Bronquiolite aguda', 'Necessidade de monitoramento respiratório', 'Não', 'Sim', 'Não', 'Não', 'Não', 'Não', 'Paciente estável', '2021-09-15', '222.222.222-01', NULL, NULL, NULL, 49, 1, NULL, 'Ativo', NULL, 'Feminino', NULL, 'Rua Alpha, 10', '32988880001', NULL, 'ana@email.com', 'Carla Martins', 'Mãe', '32988881111', NULL, NULL, NULL, NULL, NULL, 14.20, 1.00, NULL, NULL, 'VO', 'Nao', NULL, NULL, NULL, 'Nao', 'Nao', 'Nao', 'Nao', 'Nao', NULL, NULL, NULL, 'Unimed', NULL, NULL, NULL, NULL, NULL),
	(13, '7eaa931e-59cc-11f1-b6c0-089798669242', 'PRT-2026-8.0000', 'Lucas Gabriel', 'Pneumonia', 'J18', 'Pneumonia bacteriana', 'Uso contínuo de oxigênio', 'Não', 'Sim', 'Não', 'Não', 'Não', 'Não', 'Tosse produtiva', '2020-11-02', '222.222.222-02', NULL, NULL, NULL, 44, NULL, NULL, 'Ativo', NULL, 'M', NULL, 'Rua Beta, 20', '32988880002', NULL, 'lucas@email.com', 'Fernanda', 'Mãe', '32988882222', NULL, NULL, NULL, NULL, NULL, 18.50, 1.10, NULL, NULL, 'VO', NULL, NULL, 'Assistida', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SUS', NULL, NULL, NULL, NULL, NULL),
	(14, '7eaa9966-59cc-11f1-b6c0-089798669242', 'PRT-2026-9.0000', 'Helena Souza', 'Pós-operatório cardíaco', 'I51', 'Cirurgia cardíaca recente', 'Recuperação pós-operatória', 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', 'Monitoramento cardíaco', '2019-01-12', '222.222.222-03', NULL, NULL, NULL, 45, NULL, NULL, 'Ativo', NULL, 'F', NULL, 'Rua Gama, 30', '32988880003', NULL, 'helena@email.com', 'Marcelo Souza', 'Pai', '32988883333', NULL, NULL, NULL, NULL, NULL, 22.00, 1.20, NULL, NULL, 'VO', NULL, NULL, 'Parcial', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Unimed', NULL, NULL, NULL, NULL, NULL),
	(15, '7eaa9dbb-59cc-11f1-b6c0-089798669242', 'PRT-2026-10.000', 'Miguel Oliveira', 'Gastroenterite', 'A09', 'Gastroenterite infecciosa', 'Hidratação e observação', 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', 'Episódios de vômito', '2024-03-30', '222.222.222-04', NULL, NULL, NULL, 46, NULL, NULL, 'Ativo', NULL, 'M', NULL, 'Rua Delta, 40', '32988880004', NULL, 'miguel@email.com', 'Tatiane', 'Mãe', '32988884444', NULL, NULL, NULL, NULL, NULL, 11.30, 0.85, NULL, NULL, 'VO', NULL, NULL, 'Dependente', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Particular', NULL, NULL, NULL, NULL, NULL),
	(16, '7eaaa1f5-59cc-11f1-b6c0-089798669242', 'PRT-2026-11.000', 'Arthur Mendes', 'Crise asmática', 'J45', 'Asma moderada', 'Controle respiratório', 'Não', 'Sim', 'Não', 'Não', 'Não', 'Não', 'Uso frequente de nebulização', '2018-12-10', '222.222.222-05', NULL, NULL, NULL, 47, NULL, NULL, 'Ativo', NULL, 'M', NULL, 'Rua Épsilon, 50', '32988880005', NULL, 'arthur@email.com', 'Simone Mendes', 'Mãe', '32988885555', NULL, NULL, NULL, NULL, NULL, 24.00, 1.28, NULL, NULL, 'VO', NULL, NULL, 'Independente', 'Preservado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SUS', NULL, NULL, NULL, NULL, NULL);

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

-- Copiando estrutura para tabela cuidar_no_lar.tb_permissoes
CREATE TABLE IF NOT EXISTS `tb_permissoes` (
  `permissao_id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL DEFAULT (uuid()),
  `usuario_id` int NOT NULL,
  `incluir` tinyint(1) NOT NULL DEFAULT '0',
  `editar` tinyint(1) NOT NULL DEFAULT '0',
  `visualizar` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`permissao_id`),
  UNIQUE KEY `uq_permissoes_uuid` (`uuid`),
  KEY `fk_usuario_id` (`usuario_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_permissoes: 3 rows
/*!40000 ALTER TABLE `tb_permissoes` DISABLE KEYS */;
INSERT INTO `tb_permissoes` (`permissao_id`, `uuid`, `usuario_id`, `incluir`, `editar`, `visualizar`) VALUES
	(1, '6c20863d-52ac-11f1-a16a-089798669242', 1, 1, 1, 1),
	(2, '6c2091b1-52ac-11f1-a16a-089798669242', 2, 0, 0, 1),
	(3, '6c20934e-52ac-11f1-a16a-089798669242', 3, 0, 0, 0);
/*!40000 ALTER TABLE `tb_permissoes` ENABLE KEYS */;

-- Copiando estrutura para tabela cuidar_no_lar.tb_planos
CREATE TABLE IF NOT EXISTS `tb_planos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL DEFAULT (uuid()),
  `descricao` varchar(255) DEFAULT NULL,
  `horas` enum('6h','8h','12h','24h') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_planos_uuid` (`uuid`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_planos: 4 rows
/*!40000 ALTER TABLE `tb_planos` DISABLE KEYS */;
INSERT INTO `tb_planos` (`id`, `uuid`, `descricao`, `horas`, `valor`) VALUES
	(1, '6c32713a-52ac-11f1-a16a-089798669242', '6 horas', '6h', 70.00),
	(2, '6c3279a2-52ac-11f1-a16a-089798669242', '8 horas', '8h', 100.00),
	(3, '6c327b93-52ac-11f1-a16a-089798669242', '12 horas', '12h', 150.00),
	(4, '6c327d56-52ac-11f1-a16a-089798669242', '24 horas', '24h', 200.00);
/*!40000 ALTER TABLE `tb_planos` ENABLE KEYS */;

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

-- Copiando estrutura para tabela cuidar_no_lar.tb_prontuario_sequencia
CREATE TABLE IF NOT EXISTS `tb_prontuario_sequencia` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ano` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela cuidar_no_lar.tb_prontuario_sequencia: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela cuidar_no_lar.tb_relatorio_plantao
CREATE TABLE IF NOT EXISTS `tb_relatorio_plantao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `numero_relatorio` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paciente_id` int NOT NULL,
  `cuidador_id` int DEFAULT NULL,
  `turno` enum('plantao_6h','plantao_8h','plantao_12h','plantao_24h') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_inicio` datetime DEFAULT NULL,
  `data_fim` datetime DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `internacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_local` enum('hospital','domiciliar') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quarto` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome_acompanhante` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `evolucao` longtext COLLATE utf8mb4_unicode_ci,
  `assinado` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `estado_paciente` longtext COLLATE utf8mb4_unicode_ci,
  `estado_geral` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `queixas_referidas` longtext COLLATE utf8mb4_unicode_ci,
  `exame_fisico` longtext COLLATE utf8mb4_unicode_ci,
  `pele_mucosas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visita_medica` longtext COLLATE utf8mb4_unicode_ci,
  `entrada_saida_profissionais` longtext COLLATE utf8mb4_unicode_ci,
  `entrada_saida_familiares` longtext COLLATE utf8mb4_unicode_ci,
  `plantao_entregue_para` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `peso` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alimentacao` longtext COLLATE utf8mb4_unicode_ci,
  `alimentacao_via` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `eliminacoes` longtext COLLATE utf8mb4_unicode_ci,
  `diurese` json DEFAULT NULL,
  `urina_horarios` json DEFAULT NULL,
  `evacuacao` json DEFAULT NULL,
  `fezes_horarios` json DEFAULT NULL,
  `medicacoes` longtext COLLATE utf8mb4_unicode_ci,
  `intercorrencias` longtext COLLATE utf8mb4_unicode_ci,
  `status` enum('rascunho','finalizado','assinado') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes_gerais` longtext COLLATE utf8mb4_unicode_ci,
  `consciencia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nivel_dor` int DEFAULT NULL,
  `hidratacao_ml` int DEFAULT NULL,
  `hidratacao_registros` json DEFAULT NULL,
  `higiene` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decubito` text COLLATE utf8mb4_unicode_ci,
  `dispositivos` json DEFAULT NULL,
  `pa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fc` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `temperatura` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spo2` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `frequencia_respiratoria` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hgt` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_relatorio_plantao_uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela cuidar_no_lar.tb_relatorio_plantao: ~15 rows (aproximadamente)
INSERT INTO `tb_relatorio_plantao` (`id`, `uuid`, `numero_relatorio`, `paciente_id`, `cuidador_id`, `turno`, `data_inicio`, `data_fim`, `data_nascimento`, `internacao`, `tipo_local`, `quarto`, `nome_acompanhante`, `evolucao`, `assinado`, `created_at`, `updated_at`, `estado_paciente`, `estado_geral`, `queixas_referidas`, `exame_fisico`, `pele_mucosas`, `visita_medica`, `entrada_saida_profissionais`, `entrada_saida_familiares`, `plantao_entregue_para`, `peso`, `alimentacao`, `alimentacao_via`, `eliminacoes`, `diurese`, `urina_horarios`, `evacuacao`, `fezes_horarios`, `medicacoes`, `intercorrencias`, `status`, `observacoes_gerais`, `consciencia`, `nivel_dor`, `hidratacao_ml`, `hidratacao_registros`, `higiene`, `sono`, `decubito`, `dispositivos`, `pa`, `fc`, `temperatura`, `spo2`, `frequencia_respiratoria`, `hgt`) VALUES
	(1, '6c5b04ee-52ac-11f1-a16a-089798669242', NULL, 8, 8, NULL, '2026-05-16 07:00:00', '2026-05-16 19:00:00', NULL, NULL, NULL, NULL, NULL, 'dalçsjdsjfoej', 1, '2026-05-17 19:40:39', '2026-05-18 11:26:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'kljsajdjpo', NULL, NULL, NULL, NULL, 'kldsklak\r\nknaskfnp\r\nmldmejpo', 'Sem intercorrências.', 'finalizado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(8, '6c5b0a11-52ac-11f1-a16a-089798669242', NULL, 8, 8, NULL, '2026-05-16 19:00:00', '2026-05-16 07:00:00', NULL, NULL, NULL, NULL, NULL, 'sftreyety', 1, '2026-05-17 23:29:23', '2026-05-18 18:21:11', 'djfgidjgIAFGWER', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Aceitou bem todas as refeições', NULL, '["Diurese normal","Evacuação normal"]', NULL, NULL, NULL, NULL, '[]', '[]', 'assinado', 'SHARREAHGREAH', NULL, 5, 2000, NULL, 'Troca de fraldas', 'Sono fragmentado', '[]', NULL, '12/8', '93', '39', '99', NULL, '150'),
	(9, '6c5b0cb9-52ac-11f1-a16a-089798669242', NULL, 8, 8, NULL, '2026-05-17 07:00:00', '2026-05-17 19:00:00', NULL, NULL, NULL, NULL, NULL, 'Paciente em acompanhamento domiciliar por Demência.\r\nNível de consciência: Sonolento.\r\nSinais vitais: PA 139x82 mmHg, FC 83 bpm, Temp 36.7°C, SpO₂ 99%.\r\nRelata dor de intensidade moderada (4/10).\r\nHidratação oferecida: 2000 mL.\r\nMudanças de decúbito realizadas: D.D. para D.L.D., D.L.D. para D.L.E., Semi-fowler.\r\nSono: sono fragmentado.', 1, '2026-05-18 11:13:09', '2026-05-21 01:12:41', 'Paciente em recuperação AVC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '["Incontinencia urinaria"]', NULL, NULL, NULL, NULL, '[{"id":"1","status":"pendente","medicamento":"Desve","horario":"09:00"},{"id":"2","status":"pendente","medicamento":"Lisden","horario":"07:00"}]', NULL, 'finalizado', NULL, 'Sonolento', 4, 2000, NULL, 'Banho no leito', 'Sono fragmentado', '["D.D. para D.L.D.","D.L.D. para D.L.E.","Semi-fowler"]', NULL, '139x82', '83', '36.7', '99', NULL, '129'),
	(10, 'c69afad9-59cc-11f1-b6c0-089798669242', NULL, 1, 1, 'plantao_12h', '2026-05-21 07:00:00', '2026-05-21 19:00:00', NULL, NULL, NULL, NULL, NULL, 'Paciente consciente, calmo e responsivo durante todo o plantão.', 1, '2026-05-27 13:05:46', '2026-05-27 13:05:46', 'Estável', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '08:00 - 120ml', 'VO', 'Diurese presente.', NULL, NULL, NULL, NULL, 'Dipirona 10:00', 'Sem intercorrências.', 'finalizado', 'Paciente permaneceu estável.', 'Consciente', 0, 370, NULL, 'Banho realizado.', 'Tranquilo', NULL, '["Acesso venoso periférico"]', '95/60', '118', '36.5', '97', NULL, '98'),
	(11, 'd3a05ce0-59cd-11f1-b6c0-089798669242', NULL, 12, 5, 'plantao_12h', '2026-05-21 07:00:00', '2026-05-21 19:00:00', NULL, NULL, NULL, NULL, NULL, 'Paciente consciente, calmo e responsivo durante todo o plantão. Mantido monitoramento contínuo e cuidados gerais.', 1, '2026-05-27 13:13:17', '2026-05-27 13:13:17', 'Estável', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '08:00 - 120ml | 11:30 - 150ml | 15:00 - 100ml', 'VO', 'Diurese presente. Evacuação às 14:20.', NULL, NULL, NULL, NULL, 'Dipirona 10:00 | Amoxicilina 14:00', 'Sem intercorrências.', 'finalizado', 'Paciente permaneceu estável durante o período.', 'Consciente', 0, 370, NULL, 'Troca de fralda e banho realizado.', 'Tranquilo', NULL, '["Acesso venoso periférico"]', '95/60', '118', '36.5', '97', NULL, '98'),
	(12, 'd3a068d2-59cd-11f1-b6c0-089798669242', NULL, 16, 6, 'plantao_12h', '2026-05-22 19:00:00', '2026-05-23 07:00:00', NULL, NULL, NULL, NULL, NULL, 'Paciente apresentou irritabilidade leve no período noturno, mantendo estabilidade hemodinâmica.', 1, '2026-05-27 13:13:17', '2026-05-27 13:13:17', 'Regular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '20:30 - 180ml | 00:00 - 100ml | 04:30 - 120ml', 'VO', 'Diurese presente. Evacuação às 06:10.', NULL, NULL, NULL, NULL, 'Ceftriaxona 22:00 | Nebulização 03:00', 'Episódio febril às 02:15.', 'finalizado', 'Realizado controle de temperatura.', 'Consciente', 2, 400, NULL, 'Troca de fraldas frequente.', 'Agitado', NULL, '["Oxigênio em cateter nasal"]', '98/59', '124', '37.1', '95', NULL, '102'),
	(13, 'd3a06b8f-59cd-11f1-b6c0-089798669242', NULL, 19, 7, 'plantao_12h', '2026-05-23 07:00:00', '2026-05-23 19:00:00', NULL, NULL, NULL, NULL, NULL, 'Paciente em pós-operatório cardíaco, mantendo boa evolução clínica.', 1, '2026-05-27 13:13:17', '2026-05-27 13:13:17', 'Bom', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '09:00 - dieta líquida 150ml | 13:00 - sopa 200ml | 17:00 - suco 100ml', 'VO', 'Diurese espontânea. Sem evacuação.', NULL, NULL, NULL, NULL, 'Paracetamol 08:00 | Furosemida 12:00', 'Sem intercorrências.', 'finalizado', 'Mantido repouso relativo.', 'Consciente', 1, 450, NULL, 'Banho no leito realizado.', 'Tranquilo', NULL, '["Dreno torácico"]', '100/64', '110', '36.2', '98', NULL, '96'),
	(14, 'd3a06d60-59cd-11f1-b6c0-089798669242', NULL, 13, 8, 'plantao_12h', '2026-05-24 19:00:00', '2026-05-25 07:00:00', NULL, NULL, NULL, NULL, NULL, 'Paciente apresentou episódios de vômito isolados durante a madrugada.', 1, '2026-05-27 13:13:17', '2026-05-27 13:13:17', 'Regular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '21:00 - 80ml | 01:00 - 100ml | 05:00 - 90ml', 'VO', 'Diurese presente. Episódios diarreicos.', NULL, NULL, NULL, NULL, 'Ondansetrona 22:00 | Soro venoso contínuo', 'Vômito às 02:40.', 'finalizado', 'Mantida hidratação venosa.', 'Sonolento', 3, 270, NULL, 'Troca de fraldas realizada.', 'Intercalado', NULL, '["Acesso venoso periférico"]', '88/52', '130', '37.8', '98', NULL, '90'),
	(15, 'd3a077e7-59cd-11f1-b6c0-089798669242', NULL, 15, 5, 'plantao_12h', '2026-05-25 07:00:00', '2026-05-25 19:00:00', NULL, NULL, NULL, NULL, NULL, 'Paciente apresentou melhora respiratória ao longo do plantão.', 1, '2026-05-27 13:13:17', '2026-05-27 13:13:17', 'Melhorando', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '08:30 - café 150ml | 12:00 - almoço parcial | 16:00 - suco 120ml', 'VO', 'Diurese espontânea.', NULL, NULL, NULL, NULL, 'Salbutamol 08:00 e 14:00 | Hidrocortisona 10:00', 'Sem intercorrências.', 'finalizado', 'Mantida nebulização conforme prescrição.', 'Consciente', 1, 270, NULL, 'Banho realizado às 09:00.', 'Tranquilo', NULL, '["Nebulização intermitente"]', '102/61', '119', '36.7', '96', NULL, '97'),
	(16, 'd3a07bd1-59cd-11f1-b6c0-089798669242', NULL, 26, 6, 'plantao_12h', '2026-05-26 19:00:00', '2026-05-27 07:00:00', NULL, NULL, NULL, NULL, NULL, 'Paciente dormiu bem durante a madrugada e sem alterações clínicas relevantes.', 1, '2026-05-27 13:13:17', '2026-05-27 13:13:17', 'Estável', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '20:00 - 100ml | 00:00 - 120ml | 04:00 - 90ml', 'VO', 'Diurese presente.', NULL, NULL, NULL, NULL, 'Omeprazol 21:00', 'Sem intercorrências.', 'finalizado', 'Plantão tranquilo.', 'Consciente', 0, 310, NULL, 'Troca de fraldas.', 'Tranquilo', NULL, '["Nenhum"]', '94/58', '116', '36.4', '98', NULL, '95'),
	(17, 'd3a07ea5-59cd-11f1-b6c0-089798669242', NULL, 14, 7, 'plantao_12h', '2026-05-27 07:00:00', '2026-05-27 19:00:00', NULL, NULL, NULL, NULL, NULL, 'Paciente apresentou tosse produtiva leve e aceitou bem alimentação.', 1, '2026-05-27 13:13:17', '2026-05-27 13:13:17', 'Regular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '08:00 - 150ml | 12:00 - 180ml | 16:00 - 100ml', 'VO', 'Diurese e evacuação presentes.', NULL, NULL, NULL, NULL, 'Amoxicilina 09:00 | Nebulização 15:00', 'Sem intercorrências.', 'finalizado', 'Paciente responsivo e comunicativo.', 'Consciente', 2, 430, NULL, 'Banho assistido.', 'Intercalado', NULL, '["Cateter nasal"]', '96/60', '120', '37.0', '96', NULL, '101'),
	(18, 'd3a081cf-59cd-11f1-b6c0-089798669242', NULL, 23, 8, 'plantao_12h', '2026-05-28 19:00:00', '2026-05-29 07:00:00', NULL, NULL, NULL, NULL, NULL, 'Paciente em repouso, sem queixas álgicas e sinais vitais preservados.', 1, '2026-05-27 13:13:17', '2026-05-27 13:13:17', 'Bom', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '21:00 - sopa 200ml | 02:00 - água 150ml', 'VO', 'Diurese espontânea.', NULL, NULL, NULL, NULL, 'Paracetamol 22:00', 'Sem intercorrências.', 'finalizado', 'Boa aceitação da dieta.', 'Consciente', 0, 350, NULL, 'Banho no leito.', 'Tranquilo', NULL, '["Monitor cardíaco"]', '101/65', '108', '36.3', '99', NULL, '92'),
	(19, 'd3a08503-59cd-11f1-b6c0-089798669242', NULL, 17, 1, 'plantao_12h', '2026-05-29 07:00:00', '2026-05-29 19:00:00', NULL, NULL, NULL, NULL, NULL, 'Paciente apresentou melhora do quadro gastrointestinal.', 1, '2026-05-27 13:13:17', '2026-05-27 13:13:17', 'Melhorando', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '09:00 - 120ml | 13:00 - sopa 150ml | 17:00 - suco 80ml', 'VO', 'Diurese presente. Sem episódios de vômito.', NULL, NULL, NULL, NULL, 'Ondansetrona 08:00', 'Sem intercorrências.', 'finalizado', 'Boa aceitação hídrica.', 'Consciente', 1, 350, NULL, 'Troca de fraldas e banho.', 'Tranquilo', NULL, '["Acesso venoso periférico"]', '90/56', '118', '36.8', '98', NULL, '93'),
	(20, 'd3a0872f-59cd-11f1-b6c0-089798669242', NULL, 8, 6, 'plantao_12h', '2026-05-30 19:00:00', '2026-05-31 07:00:00', NULL, NULL, NULL, NULL, NULL, 'Paciente manteve padrão respiratório estável durante o período noturno.', 1, '2026-05-27 13:13:17', '2026-05-27 13:13:17', 'Estável', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '20:00 - jantar parcial | 23:30 - água 100ml | 05:00 - leite 120ml', 'VO', 'Diurese presente.', NULL, NULL, NULL, NULL, 'Salbutamol 22:00', 'Sem intercorrências.', 'finalizado', 'Paciente colaborativo aos cuidados.', 'Consciente', 0, 220, NULL, 'Higiene oral realizada.', 'Tranquilo', NULL, '["Nebulização"]', '100/60', '115', '36.6', '97', NULL, '99'),
	(22, 'e8e62fc7-59fa-11f1-b6c0-089798669242', NULL, 10, 8, 'plantao_24h', '2026-05-17 07:00:00', '2026-05-17 19:00:00', '1987-04-12', 'cardiopatia/síndrome Hemofagocítica ', 'hospital', 'P1.4 (UTI neo', 'Beatriz  ', 'Paciente em acompanhamento domiciliar por Demência.\r\nNível de consciência: Sonolento.\r\nSinais vitais: PA 139x82 mmHg, FC 83 bpm, Temp 36.7°C, SpO₂ 99%.\r\nRelata dor de intensidade moderada (4/10).\r\nHidratação oferecida: 2000 mL.\r\nMudanças de decúbito realizadas: D.D. para D.L.D., D.L.D. para D.L.E., Semi-fowler.\r\nSono: sono fragmentado.', 1, '2026-05-18 11:13:09', '2026-05-27 19:14:17', 'Paciente em recuperação AVC', 'Consciente', NULL, 'Bom', 'Integras', 'Dra Fernanda', NULL, NULL, 'Patricia', '7,076', '20:00 - jantar parcial | 23:30 - água 100ml | 05:00 - leite 120ml', 'VO', 'Urina', NULL, NULL, NULL, NULL, 'omeprazol/espironolactona/captopril/furosemida ', NULL, 'finalizado', NULL, 'Sonolento', 4, 2000, NULL, 'Banho no leito', 'Sono fragmentado', NULL, NULL, '91/47', '121', '35.4', '99', '32', '129');

-- Copiando estrutura para tabela cuidar_no_lar.tb_relatorio_plantao_eventos
CREATE TABLE IF NOT EXISTS `tb_relatorio_plantao_eventos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `relatorio_id` int NOT NULL,
  `hora_evento` time NOT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao` longtext COLLATE utf8mb4_unicode_ci,
  `sinais_vitais` json DEFAULT NULL,
  `medicacoes` json DEFAULT NULL,
  `intercorrencia` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_relatorio_plantao_eventos_uuid` (`uuid`),
  KEY `fk_relatorio_evento` (`relatorio_id`),
  CONSTRAINT `fk_relatorio_evento` FOREIGN KEY (`relatorio_id`) REFERENCES `tb_relatorio_plantao` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela cuidar_no_lar.tb_relatorio_plantao_eventos: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela cuidar_no_lar.tb_responsavel
CREATE TABLE IF NOT EXISTS `tb_responsavel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL DEFAULT (uuid()),
  `nome_completo` varchar(100) NOT NULL,
  `endereco` varchar(255) NOT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `bairro` varchar(50) DEFAULT NULL,
  `cidade` varchar(50) NOT NULL,
  `estado` char(2) NOT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `cpf` varchar(14) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `grau_parentesco` varchar(50) DEFAULT NULL,
  `status` enum('Ativo','Inativo') DEFAULT 'Ativo',
  `motivo_inativacao` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf` (`cpf`),
  UNIQUE KEY `uq_responsavel_uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_responsavel: ~25 rows (aproximadamente)
INSERT INTO `tb_responsavel` (`id`, `uuid`, `nome_completo`, `endereco`, `numero`, `bairro`, `cidade`, `estado`, `cep`, `cpf`, `email`, `data_nascimento`, `telefone`, `grau_parentesco`, `status`, `motivo_inativacao`) VALUES
	(1, '6c91506e-52ac-11f1-a16a-089798669242', 'Lucia Maria de Carvalho', 'Rua Dr. Wilson Amaral', '223', 'Dornelas', 'Muriaé', 'MG', '36880-000', '629.152.586-00', 'lulumcarvalhobani@gmail.com', '1949-06-06', '(32) 99830-6429 ', 'Filha', 'Ativo', NULL),
	(2, '6c9159a6-52ac-11f1-a16a-089798669242', 'Márcia Cristina Martins Pereira Santos', 'Av Constantino Pinto', '171', 'Armação', 'Muriaé', 'MG', '36880-000', '783.300.846-04', 'marciamartins@gmail.com', '1969-11-23', '(32) 98879-2992', 'Filha', 'Ativo', NULL),
	(36, '6c9165fb-52ac-11f1-a16a-089798669242', 'Rafa', 'Rua G', '002', 'Centro', 'Itaperuna', 'RJ', '36.886-002', '118.034.737-46', 'teste@teste.com', '1987-04-12', '(32) 9999-9999', 'Tia', 'Ativo', 'Teste'),
	(42, '6c916956-52ac-11f1-a16a-089798669242', 'este Responsavel MVC', 'Rua  este', NULL, NULL, 'Juiz de Fora', 'MG', NULL, '000.000.000-01', NULL, '1987-04-12', NULL, NULL, 'Ativo', NULL),
	(43, '2d52442b-5afe-11f1-b6c0-089798669242', 'Carla Martins', 'Rua Alpha, 10', NULL, 'Dornelas', 'Muriaé', 'MG', NULL, 'MIG-0000000012', 'alterouvinculo@teste.com.br', '1979-04-05', '32988881111', 'Mãe', 'Inativo', NULL),
	(44, '2d52492c-5afe-11f1-b6c0-089798669242', 'Fernanda', 'Rua Beta, 20', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000013', NULL, NULL, '32988882222', 'Mãe', 'Ativo', NULL),
	(45, '2d524ad2-5afe-11f1-b6c0-089798669242', 'Marcelo Souza', 'Rua Gama, 30', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000014', NULL, NULL, '32988883333', 'Pai', 'Ativo', NULL),
	(46, '2d524bd3-5afe-11f1-b6c0-089798669242', 'Tatiane', 'Rua Delta, 40', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000015', NULL, NULL, '32988884444', 'Mãe', 'Ativo', NULL),
	(47, '2d524d10-5afe-11f1-b6c0-089798669242', 'Simone Mendes', 'Rua Épsilon, 50', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000016', NULL, NULL, '32988885555', 'Mãe', 'Ativo', NULL),
	(48, '2d524e07-5afe-11f1-b6c0-089798669242', 'Carla Martins', 'Rua Alpha, 10', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000017', NULL, NULL, '32988881111', 'Mãe', 'Ativo', NULL),
	(49, '2d524fcd-5afe-11f1-b6c0-089798669242', 'Fernanda', 'Rua Beta, 20', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000018', NULL, NULL, '32988882222', 'Mãe', 'Ativo', NULL),
	(50, '2d525100-5afe-11f1-b6c0-089798669242', 'Marcelo Souza', 'Rua Gama, 30', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000019', NULL, NULL, '32988883333', 'Pai', 'Ativo', NULL),
	(51, '2d52539f-5afe-11f1-b6c0-089798669242', 'Tatiane', 'Rua Delta, 40', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000020', NULL, NULL, '32988884444', 'Mãe', 'Ativo', NULL),
	(52, '2d5254a6-5afe-11f1-b6c0-089798669242', 'Simone Mendes', 'Rua Épsilon, 50', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000021', NULL, NULL, '32988885555', 'Mãe', 'Ativo', NULL),
	(53, '2d525574-5afe-11f1-b6c0-089798669242', 'Carla Martins', 'Rua Alpha, 10', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000022', NULL, NULL, '32988881111', 'Mãe', 'Ativo', NULL),
	(54, '2d52563e-5afe-11f1-b6c0-089798669242', 'Fernanda', 'Rua Beta, 20', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000023', NULL, NULL, '32988882222', 'Mãe', 'Ativo', NULL),
	(55, '2d52570a-5afe-11f1-b6c0-089798669242', 'Marcelo Souza', 'Rua Gama, 30', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000024', NULL, NULL, '32988883333', 'Pai', 'Ativo', NULL),
	(56, '2d5257dc-5afe-11f1-b6c0-089798669242', 'Tatiane', 'Rua Delta, 40', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000025', NULL, NULL, '32988884444', 'Mãe', 'Ativo', NULL),
	(57, '2d5258ee-5afe-11f1-b6c0-089798669242', 'Simone Mendes', 'Rua Épsilon, 50', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000026', NULL, NULL, '32988885555', 'Mãe', 'Ativo', NULL),
	(58, '2d5259bd-5afe-11f1-b6c0-089798669242', 'Carla Martins', 'Rua Alpha, 10', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000027', NULL, NULL, '32988881111', 'Mãe', 'Ativo', NULL),
	(59, '2d525ae5-5afe-11f1-b6c0-089798669242', 'Fernanda', 'Rua Beta, 20', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000028', NULL, NULL, '32988882222', 'Mãe', 'Ativo', NULL),
	(60, '2d525bc7-5afe-11f1-b6c0-089798669242', 'Marcelo Souza', 'Rua Gama, 30', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000029', NULL, NULL, '32988883333', 'Pai', 'Ativo', NULL),
	(61, '2d525cce-5afe-11f1-b6c0-089798669242', 'Tatiane', 'Rua Delta, 40', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000030', NULL, NULL, '32988884444', 'Mãe', 'Ativo', NULL),
	(62, '2d525db2-5afe-11f1-b6c0-089798669242', 'Simone Mendes', 'Rua Épsilon, 50', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000031', NULL, NULL, '32988885555', 'Mãe', 'Ativo', NULL),
	(63, '2d525e83-5afe-11f1-b6c0-089798669242', 'Carla Martins', 'Rua Alpha, 10', NULL, NULL, 'Muriaé', 'MG', NULL, 'MIG-0000000032', NULL, NULL, '32988881111', 'Mãe', 'Ativo', NULL);

-- Copiando estrutura para tabela cuidar_no_lar.tb_sinais_vitais
CREATE TABLE IF NOT EXISTS `tb_sinais_vitais` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `relatorio_id` int NOT NULL,
  `pa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fc` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `temperatura` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spo2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `frequencia_respiratoria` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hgt` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sinais_vitais_uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela cuidar_no_lar.tb_sinais_vitais: ~2 rows (aproximadamente)
INSERT INTO `tb_sinais_vitais` (`id`, `uuid`, `relatorio_id`, `pa`, `fc`, `temperatura`, `spo2`, `frequencia_respiratoria`, `hgt`, `observacao`, `created_at`) VALUES
	(1, '6c9fe732-52ac-11f1-a16a-089798669242', 8, '12/8', '93', '39', '99', NULL, '150', NULL, '2026-05-17 23:29:23'),
	(2, '6c9fea77-52ac-11f1-a16a-089798669242', 9, '139x82', '83', '36.7', '99', NULL, '129', NULL, '2026-05-18 11:13:09');

-- Copiando estrutura para tabela cuidar_no_lar.tb_tipos_usuarios
CREATE TABLE IF NOT EXISTS `tb_tipos_usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL DEFAULT (uuid()),
  `nome_tipo` varchar(50) NOT NULL,
  `descricao` text,
  `prioridade` int DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tipos_usuarios_uuid` (`uuid`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_tipos_usuarios: 3 rows
/*!40000 ALTER TABLE `tb_tipos_usuarios` DISABLE KEYS */;
INSERT INTO `tb_tipos_usuarios` (`id`, `uuid`, `nome_tipo`, `descricao`, `prioridade`) VALUES
	(1, '6cb7988d-52ac-11f1-a16a-089798669242', 'Administrador', 'Usuário com permissões completas para gerenciar todos os recursos do sistema', 1),
	(2, '6cb7a189-52ac-11f1-a16a-089798669242', 'Editor', 'Usuário com permissões para editar e gerenciar conteúdo', 2),
	(3, '6cb7a414-52ac-11f1-a16a-089798669242', 'Visualizador', 'Usuário apenas para visualizar conteúdo', 3);
/*!40000 ALTER TABLE `tb_tipos_usuarios` ENABLE KEYS */;

-- Copiando estrutura para tabela cuidar_no_lar.tb_tipos_usuarios_permissoes
CREATE TABLE IF NOT EXISTS `tb_tipos_usuarios_permissoes` (
  `tipo_usuario_id` int NOT NULL,
  `permissao_id` int NOT NULL,
  PRIMARY KEY (`tipo_usuario_id`,`permissao_id`),
  KEY `permissao_id` (`permissao_id`),
  KEY `tipo_usuario_id` (`tipo_usuario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_tipos_usuarios_permissoes: 6 rows
/*!40000 ALTER TABLE `tb_tipos_usuarios_permissoes` DISABLE KEYS */;
INSERT INTO `tb_tipos_usuarios_permissoes` (`tipo_usuario_id`, `permissao_id`) VALUES
	(1, 1),
	(1, 2),
	(1, 3),
	(2, 2),
	(2, 3),
	(3, 3);
/*!40000 ALTER TABLE `tb_tipos_usuarios_permissoes` ENABLE KEYS */;

-- Copiando estrutura para tabela cuidar_no_lar.tb_usuarios
CREATE TABLE IF NOT EXISTS `tb_usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL DEFAULT (uuid()),
  `nome_completo` varchar(50) NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `senha` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '1234',
  `username` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `ultimo_login` datetime NOT NULL,
  `status` enum('ativo','inativo') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'ativo',
  `tipo_usuario_id` int DEFAULT NULL,
  `token_recuperacao` varchar(64) DEFAULT NULL,
  `token_expiracao` datetime DEFAULT NULL,
  `codigo_sms` varchar(6) DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  `last_password_change` datetime DEFAULT NULL,
  `precisa_alterar_senha` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `uq_usuarios_uuid` (`uuid`),
  KEY `fk_tipo_usuario` (`tipo_usuario_id`),
  KEY `idx_token_recuperacao` (`token_recuperacao`),
  KEY `idx_email` (`email`),
  KEY `idx_telefone` (`telefone`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_usuarios: ~5 rows (aproximadamente)
INSERT INTO `tb_usuarios` (`id`, `uuid`, `nome_completo`, `email`, `telefone`, `senha`, `username`, `ultimo_login`, `status`, `tipo_usuario_id`, `token_recuperacao`, `token_expiracao`, `codigo_sms`, `remember_token`, `password_reset_token`, `password_reset_expires`, `last_password_change`, `precisa_alterar_senha`) VALUES
	(1, '6ccb62ee-52ac-11f1-a16a-089798669242', 'Rafaela Silveira', 'rafaelasilveira1987@gmail.com', '032998416669', '$2y$10$bwb6qTiWiDP.nrILvH6g5eRNy7XGI8ppBloxXsgBzhCg6w3vVeW5W', 'rafaela', '2026-05-30 21:40:25', 'ativo', 1, '53e390fe40521b8b3358addfdf762e922443a80c938366a5db9a0b6882d00eed', '2025-05-21 23:35:55', NULL, '$2y$10$Ep4vj.MTTPEIxu63tJs90uLUifM8TXqAq9ShZxIH3FNbi/MHOjQPm', NULL, NULL, NULL, 0),
	(2, '6ccb6f08-52ac-11f1-a16a-089798669242', 'Vanusa Alves', 'vanusaag@gmail.com', '032988575765', '$2y$10$1SOeyBMXxUXIidnTmjvD7.CW7owqekQm18mo8NOenPJKSzBL5.We.', 'vanusa', '2025-05-23 10:46:31', 'ativo', 1, '88ffe88a2fa779c629018346be29b95716351dd67ab983a567c2ce3da1fcbb12', '2025-05-21 23:35:39', '077802', '$2y$10$eK9PH5bE1ETedpvnnCnDDuFAFfgxFAQyxcjsDzxHM4urJ6xti.yc.', NULL, NULL, NULL, 0),
	(3, '6ccb71f4-52ac-11f1-a16a-089798669242', 'Teste', 'teste@gmail.com', NULL, '$2y$10$dCEKzeU36R07DX19n8x35Ow25FdyJLQKkFk7G7X5Pu3gst7FpqIAu', 'teste', '2025-01-21 20:52:33', 'ativo', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
	(6, '6ccb74d9-52ac-11f1-a16a-089798669242', 'Maria Silva', 'maria@email.com', NULL, '$2y$10$YWlhQ91EO533DdkX57XY6O7Q6ExbsTmYx7YulNcbECvtiiDNupsha', 'maria123', '0000-00-00 00:00:00', 'ativo', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
	(7, '6ccb7a82-52ac-11f1-a16a-089798669242', 'Cuidar no Lar', 'cuidarnolar@gmail.com', NULL, '$2y$10$4Cbm0Z3GDP6fcJcIhlIY6eKZ7lndOM4CpuUJD5rfeGuJHxWX1KVvK', 'cuidar', '0000-00-00 00:00:00', 'ativo', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);

-- Copiando estrutura para tabela cuidar_no_lar.tb_usuarios_permissoes
CREATE TABLE IF NOT EXISTS `tb_usuarios_permissoes` (
  `usuario_id` int NOT NULL,
  `permissao_id` int NOT NULL,
  `data_atribuicao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`usuario_id`,`permissao_id`),
  KEY `permissao_id` (`permissao_id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Copiando dados para a tabela cuidar_no_lar.tb_usuarios_permissoes: 3 rows
/*!40000 ALTER TABLE `tb_usuarios_permissoes` DISABLE KEYS */;
INSERT INTO `tb_usuarios_permissoes` (`usuario_id`, `permissao_id`, `data_atribuicao`) VALUES
	(1, 2, '2025-01-29 21:23:29'),
	(1, 3, '2025-01-29 21:23:29'),
	(2, 3, '2025-01-29 21:28:18');
/*!40000 ALTER TABLE `tb_usuarios_permissoes` ENABLE KEYS */;

-- Copiando estrutura para trigger cuidar_no_lar.after_insert_tb_financeiro
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `after_insert_tb_financeiro` AFTER INSERT ON `tb_financeiro` FOR EACH ROW BEGIN
    INSERT INTO tb_lancamentos (tipo_transacao, valor, status, data_vencimento, data_pagamento)
    VALUES (NEW.tipo_transacao, NEW.valor, NEW.status, NEW.data_vencimento, NEW.data_pagamento);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Copiando estrutura para trigger cuidar_no_lar.after_update_tb_financeiro
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `after_update_tb_financeiro` AFTER UPDATE ON `tb_financeiro` FOR EACH ROW BEGIN
    UPDATE tb_lancamentos
    SET tipo_transacao = NEW.tipo_transacao,
        valor = NEW.valor,
        status = NEW.status,
        data_vencimento = NEW.data_vencimento,
        data_pagamento = NEW.data_pagamento
    WHERE id = NEW.id;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
