-- Etapa 2 - Contrato e Escala Base
-- Rode apenas se sua tabela tb_escala_base ainda não existir.
-- Se ela já existir igual ao modelo abaixo, pode ignorar este arquivo.

CREATE TABLE IF NOT EXISTS `tb_escala_base` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `paciente_id` INT NOT NULL,
    `nome` VARCHAR(150) NULL DEFAULT NULL,
    `tipo_cobertura` ENUM('24h','12h','8h','6h') NOT NULL,
    `hora_inicio` TIME NOT NULL,
    `hora_fim` TIME NOT NULL,
    `tipo_atendimento` ENUM('domiciliar','hospitalar') NULL DEFAULT 'domiciliar',
    `local` VARCHAR(255) NULL DEFAULT NULL,
    `recorrente` ENUM('sim','nao') NULL DEFAULT 'sim',
    `domingo` TINYINT(1) NULL DEFAULT 1,
    `segunda` TINYINT(1) NULL DEFAULT 1,
    `terca` TINYINT(1) NULL DEFAULT 1,
    `quarta` TINYINT(1) NULL DEFAULT 1,
    `quinta` TINYINT(1) NULL DEFAULT 1,
    `sexta` TINYINT(1) NULL DEFAULT 1,
    `sabado` TINYINT(1) NULL DEFAULT 1,
    `revezamento_automatico` TINYINT(1) NULL DEFAULT 1,
    `ativo` TINYINT(1) NULL DEFAULT 1,
    `observacoes` TEXT NULL DEFAULT NULL,
    `criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_tb_escala_base_paciente_ativo` (`paciente_id`, `ativo`),
    CONSTRAINT `fk_tb_escala_base_paciente`
        FOREIGN KEY (`paciente_id`) REFERENCES `tb_pacientes` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE IF NOT EXISTS `tb_escala_profissionais` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `escala_base_id` INT NOT NULL,
    `cuidador_id` INT NOT NULL,
    `ordem_revezamento` INT DEFAULT 1,
    `principal_escala` TINYINT(1) DEFAULT 1,
    `ativo` TINYINT(1) DEFAULT 1,
    `criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_tb_escala_profissionais_base` (`escala_base_id`, `ativo`),
    INDEX `idx_tb_escala_profissionais_cuidador` (`cuidador_id`),
    CONSTRAINT `fk_tb_escala_profissionais_escala`
        FOREIGN KEY (`escala_base_id`) REFERENCES `tb_escala_base` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `fk_tb_escala_profissionais_cuidador`
        FOREIGN KEY (`cuidador_id`) REFERENCES `tb_cuidador` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Índices úteis, caso ainda não existam. Se der erro de índice duplicado, ignore.
-- ALTER TABLE `tb_contratos_paciente` ADD INDEX `idx_contrato_paciente_status` (`paciente_id`, `status`);
-- ALTER TABLE `tb_escala_ocorrencias` ADD INDEX `idx_ocorrencias_paciente_data` (`paciente_id`, `data_plantao`);
