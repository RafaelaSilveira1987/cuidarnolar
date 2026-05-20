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

-- Exportação de dados foi desmarcado.

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
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

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

-- Exportação de dados foi desmarcado.

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
  `motivo_inativacao` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf` (`cpf`),
  UNIQUE KEY `uq_cuidador_uuid` (`uuid`)
) ENGINE=MyISAM AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb3;

-- Exportação de dados foi desmarcado.

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

-- Exportação de dados foi desmarcado.

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

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela cuidar_no_lar.tb_eventos
CREATE TABLE IF NOT EXISTS `tb_eventos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL DEFAULT (uuid()),
  `paciente_id` int NOT NULL DEFAULT '0',
  `titulo` varchar(255) NOT NULL,
  `descricao` text,
  `data_evento` datetime NOT NULL,
  `cuidador_id` int DEFAULT NULL,
  `status` enum('Pendente','Concluído') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_eventos_uuid` (`uuid`),
  KEY `paciente_id` (`paciente_id`)
) ENGINE=MyISAM AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb3;

-- Exportação de dados foi desmarcado.

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

-- Exportação de dados foi desmarcado.

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

-- Exportação de dados foi desmarcado.

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

-- Exportação de dados foi desmarcado.

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

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela cuidar_no_lar.tb_medicacoes_paciente
CREATE TABLE IF NOT EXISTS `tb_medicacoes_paciente` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL DEFAULT (uuid()),
  `paciente_id` int NOT NULL,
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_medicacoes_uuid` (`uuid`),
  KEY `idx_medicacoes_paciente` (`paciente_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela cuidar_no_lar.tb_medicacoes_plantao
CREATE TABLE IF NOT EXISTS `tb_medicacoes_plantao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `plantao_id` int NOT NULL,
  `medicamento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `via` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horario` time DEFAULT NULL,
  `status` enum('administrado','pendente') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_medicacoes_plantao_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela cuidar_no_lar.tb_pacientes
CREATE TABLE IF NOT EXISTS `tb_pacientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL DEFAULT (uuid()),
  `nome_completo` varchar(100) NOT NULL,
  `diagnostico` varchar(255) DEFAULT NULL,
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pacientes_uuid` (`uuid`),
  KEY `responsavel_id` (`responsavel_id`),
  KEY `cuidador_id` (`cuidador_id`),
  KEY `anamnese_id` (`anamnese_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;

-- Exportação de dados foi desmarcado.

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

-- Exportação de dados foi desmarcado.

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

-- Exportação de dados foi desmarcado.

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

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela cuidar_no_lar.tb_relatorio_plantao
CREATE TABLE IF NOT EXISTS `tb_relatorio_plantao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `paciente_id` int NOT NULL,
  `cuidador_id` int DEFAULT NULL,
  `data_inicio` datetime DEFAULT NULL,
  `data_fim` datetime DEFAULT NULL,
  `evolucao` longtext COLLATE utf8mb4_unicode_ci,
  `assinado` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `estado_paciente` longtext COLLATE utf8mb4_unicode_ci,
  `alimentacao` longtext COLLATE utf8mb4_unicode_ci,
  `eliminacoes` longtext COLLATE utf8mb4_unicode_ci,
  `medicacoes` longtext COLLATE utf8mb4_unicode_ci,
  `intercorrencias` longtext COLLATE utf8mb4_unicode_ci,
  `status` enum('rascunho','finalizado','assinado') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes_gerais` longtext COLLATE utf8mb4_unicode_ci,
  `consciencia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nivel_dor` int DEFAULT NULL,
  `hidratacao_ml` int DEFAULT NULL,
  `higiene` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decubito` text COLLATE utf8mb4_unicode_ci,
  `pa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fc` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `temperatura` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spo2` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hgt` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_relatorio_plantao_uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

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

-- Exportação de dados foi desmarcado.

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
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb3;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela cuidar_no_lar.tb_sinais_vitais
CREATE TABLE IF NOT EXISTS `tb_sinais_vitais` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `relatorio_id` int NOT NULL,
  `pa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fc` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `temperatura` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spo2` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hgt` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sinais_vitais_uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

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

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela cuidar_no_lar.tb_tipos_usuarios_permissoes
CREATE TABLE IF NOT EXISTS `tb_tipos_usuarios_permissoes` (
  `tipo_usuario_id` int NOT NULL,
  `permissao_id` int NOT NULL,
  PRIMARY KEY (`tipo_usuario_id`,`permissao_id`),
  KEY `permissao_id` (`permissao_id`),
  KEY `tipo_usuario_id` (`tipo_usuario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Exportação de dados foi desmarcado.

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
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela cuidar_no_lar.tb_usuarios_permissoes
CREATE TABLE IF NOT EXISTS `tb_usuarios_permissoes` (
  `usuario_id` int NOT NULL,
  `permissao_id` int NOT NULL,
  `data_atribuicao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`usuario_id`,`permissao_id`),
  KEY `permissao_id` (`permissao_id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Exportação de dados foi desmarcado.

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
