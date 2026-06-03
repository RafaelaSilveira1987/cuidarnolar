-- =============================================================
-- Cuidar no Lar — Configurações gerais v8
-- Dados da empresa + tabela de valores de plantões
-- Rode este arquivo antes de acessar o menu Configurações.
-- =============================================================

CREATE TABLE IF NOT EXISTS `tb_empresa_config` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `razao_social` VARCHAR(180) NOT NULL DEFAULT 'Cuidar no Lar',
  `nome_fantasia` VARCHAR(180) DEFAULT 'Cuidar no Lar',
  `cnpj` VARCHAR(20) DEFAULT NULL,
  `inscricao_estadual` VARCHAR(40) DEFAULT NULL,
  `endereco` VARCHAR(255) DEFAULT NULL,
  `cidade` VARCHAR(120) DEFAULT NULL,
  `estado` CHAR(2) DEFAULT NULL,
  `cep` VARCHAR(15) DEFAULT NULL,
  `telefone` VARCHAR(40) DEFAULT NULL,
  `email` VARCHAR(160) DEFAULT NULL,
  `responsavel_contrato` VARCHAR(160) DEFAULT NULL,
  `observacoes_contrato` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tb_empresa_config` (`id`, `razao_social`, `nome_fantasia`)
SELECT 1, 'Cuidar no Lar', 'Cuidar no Lar'
WHERE NOT EXISTS (SELECT 1 FROM `tb_empresa_config` WHERE `id` = 1);

CREATE TABLE IF NOT EXISTS `tb_tabela_plantoes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(120) NOT NULL,
  `tipo_plantao` ENUM('6h','8h','12h','24h','Personalizado') NOT NULL DEFAULT '12h',
  `periodo` ENUM('Diurno','Noturno','24h','Personalizado') NOT NULL DEFAULT 'Diurno',
  `hora_inicio` TIME DEFAULT NULL,
  `hora_fim` TIME DEFAULT NULL,
  `valor_cuidador` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `valor_extra` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `descricao` TEXT DEFAULT NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `ordem` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tabela_plantoes_tipo_periodo` (`tipo_plantao`, `periodo`, `ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tb_tabela_plantoes`
(`titulo`, `tipo_plantao`, `periodo`, `hora_inicio`, `hora_fim`, `valor_cuidador`, `valor_extra`, `descricao`, `ativo`, `ordem`)
SELECT 'Plantão 12h diurno', '12h', 'Diurno', '07:00:00', '19:00:00', 120.00, 0.00, 'Valor padrão inicial. Ajuste conforme a tabela real da empresa.', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM `tb_tabela_plantoes` WHERE `titulo` = 'Plantão 12h diurno');

INSERT INTO `tb_tabela_plantoes`
(`titulo`, `tipo_plantao`, `periodo`, `hora_inicio`, `hora_fim`, `valor_cuidador`, `valor_extra`, `descricao`, `ativo`, `ordem`)
SELECT 'Plantão 12h noturno', '12h', 'Noturno', '19:00:00', '07:00:00', 140.00, 0.00, 'Valor padrão inicial. Ajuste conforme a tabela real da empresa.', 1, 20
WHERE NOT EXISTS (SELECT 1 FROM `tb_tabela_plantoes` WHERE `titulo` = 'Plantão 12h noturno');

INSERT INTO `tb_tabela_plantoes`
(`titulo`, `tipo_plantao`, `periodo`, `hora_inicio`, `hora_fim`, `valor_cuidador`, `valor_extra`, `descricao`, `ativo`, `ordem`)
SELECT 'Plantão 24h', '24h', '24h', '07:00:00', '07:00:00', 240.00, 0.00, 'Valor padrão inicial. Ajuste conforme a tabela real da empresa.', 1, 30
WHERE NOT EXISTS (SELECT 1 FROM `tb_tabela_plantoes` WHERE `titulo` = 'Plantão 24h');
