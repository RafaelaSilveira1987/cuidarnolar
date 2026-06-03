-- Patch v3 - Contrato do paciente dentro do cadastro do Paciente
-- Fluxo: Paciente > Contratos > Gerar financeiro.
-- Rode uma vez no banco cuidar_no_lar antes de substituir os arquivos PHP.

CREATE TABLE IF NOT EXISTS tb_contratos_paciente (
  id INT NOT NULL AUTO_INCREMENT,
  paciente_id INT NOT NULL,
  responsavel_legal_id INT NULL,
  responsavel_financeiro_id INT NULL,
  tipo_servico VARCHAR(120) NOT NULL DEFAULT 'Contrato home care',
  servicos_contratados TEXT NULL,
  escala_contratada VARCHAR(80) NULL,
  tipo_plantao VARCHAR(20) NULL,
  hora_inicio_padrao TIME NULL,
  hora_fim_padrao TIME NULL,
  tipo_prazo VARCHAR(20) NULL DEFAULT 'Indeterminado',
  tipo_cobranca VARCHAR(30) NULL DEFAULT 'Mensal',
  valor_contrato DECIMAL(12,2) NULL,
  valor_mensal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  valor_semanal DECIMAL(12,2) NULL,
  valor_plantao DECIMAL(12,2) NULL,
  dia_vencimento TINYINT NOT NULL DEFAULT 10,
  forma_pagamento VARCHAR(60) NULL,
  multa_percentual DECIMAL(8,4) NULL,
  juros_percentual DECIMAL(8,4) NULL,
  vigencia_inicio DATE NOT NULL,
  vigencia_fim DATE NULL,
  empresa_razao_social VARCHAR(180) NULL,
  empresa_cnpj VARCHAR(20) NULL,
  empresa_endereco VARCHAR(255) NULL,
  empresa_responsavel_contrato VARCHAR(160) NULL,
  paciente_snapshot LONGTEXT NULL,
  responsavel_legal_snapshot LONGTEXT NULL,
  responsavel_financeiro_snapshot LONGTEXT NULL,
  empresa_snapshot LONGTEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Ativo',
  observacoes TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_contrato_paciente_status (paciente_id, status),
  KEY idx_contrato_vigencia (vigencia_inicio, vigencia_fim),
  KEY idx_contrato_resp_legal (responsavel_legal_id),
  KEY idx_contrato_resp_financeiro (responsavel_financeiro_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN responsavel_legal_id INT NULL AFTER paciente_id',
    'SELECT "tb_contratos_paciente.responsavel_legal_id já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'responsavel_legal_id'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN responsavel_financeiro_id INT NULL AFTER responsavel_legal_id',
    'SELECT "tb_contratos_paciente.responsavel_financeiro_id já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'responsavel_financeiro_id'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN servicos_contratados TEXT NULL AFTER tipo_servico',
    'SELECT "tb_contratos_paciente.servicos_contratados já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'servicos_contratados'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN escala_contratada VARCHAR(80) NULL AFTER servicos_contratados',
    'SELECT "tb_contratos_paciente.escala_contratada já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'escala_contratada'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN tipo_plantao VARCHAR(20) NULL AFTER escala_contratada',
    'SELECT "tb_contratos_paciente.tipo_plantao já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'tipo_plantao'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN hora_inicio_padrao TIME NULL AFTER tipo_plantao',
    'SELECT "tb_contratos_paciente.hora_inicio_padrao já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'hora_inicio_padrao'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN hora_fim_padrao TIME NULL AFTER hora_inicio_padrao',
    'SELECT "tb_contratos_paciente.hora_fim_padrao já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'hora_fim_padrao'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN tipo_prazo VARCHAR(20) NULL DEFAULT 'Indeterminado' AFTER hora_fim_padrao',
    'SELECT "tb_contratos_paciente.tipo_prazo já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'tipo_prazo'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN tipo_cobranca VARCHAR(30) NULL DEFAULT 'Mensal' AFTER tipo_prazo',
    'SELECT "tb_contratos_paciente.tipo_cobranca já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'tipo_cobranca'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN valor_contrato DECIMAL(12,2) NULL AFTER tipo_cobranca',
    'SELECT "tb_contratos_paciente.valor_contrato já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'valor_contrato'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN valor_semanal DECIMAL(12,2) NULL AFTER valor_mensal',
    'SELECT "tb_contratos_paciente.valor_semanal já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'valor_semanal'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN valor_plantao DECIMAL(12,2) NULL AFTER valor_semanal',
    'SELECT "tb_contratos_paciente.valor_plantao já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'valor_plantao'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN multa_percentual DECIMAL(8,4) NULL AFTER forma_pagamento',
    'SELECT "tb_contratos_paciente.multa_percentual já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'multa_percentual'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN juros_percentual DECIMAL(8,4) NULL AFTER multa_percentual',
    'SELECT "tb_contratos_paciente.juros_percentual já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'juros_percentual'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN empresa_razao_social VARCHAR(180) NULL AFTER vigencia_fim',
    'SELECT "tb_contratos_paciente.empresa_razao_social já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'empresa_razao_social'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN empresa_cnpj VARCHAR(20) NULL AFTER empresa_razao_social',
    'SELECT "tb_contratos_paciente.empresa_cnpj já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'empresa_cnpj'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN empresa_endereco VARCHAR(255) NULL AFTER empresa_cnpj',
    'SELECT "tb_contratos_paciente.empresa_endereco já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'empresa_endereco'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN empresa_responsavel_contrato VARCHAR(160) NULL AFTER empresa_endereco',
    'SELECT "tb_contratos_paciente.empresa_responsavel_contrato já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'empresa_responsavel_contrato'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN paciente_snapshot LONGTEXT NULL AFTER empresa_responsavel_contrato',
    'SELECT "tb_contratos_paciente.paciente_snapshot já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'paciente_snapshot'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN responsavel_legal_snapshot LONGTEXT NULL AFTER paciente_snapshot',
    'SELECT "tb_contratos_paciente.responsavel_legal_snapshot já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'responsavel_legal_snapshot'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN responsavel_financeiro_snapshot LONGTEXT NULL AFTER responsavel_legal_snapshot',
    'SELECT "tb_contratos_paciente.responsavel_financeiro_snapshot já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'responsavel_financeiro_snapshot'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN empresa_snapshot LONGTEXT NULL AFTER responsavel_financeiro_snapshot',
    'SELECT "tb_contratos_paciente.empresa_snapshot já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'empresa_snapshot'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_contratos_paciente ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at',
    'SELECT "tb_contratos_paciente.updated_at já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_contratos_paciente'
    AND COLUMN_NAME = 'updated_at'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Garante colunas usadas pela geração financeira do contrato.
CREATE TABLE IF NOT EXISTS tb_financeiro (
  id INT NOT NULL AUTO_INCREMENT,
  responsavel_id INT NULL,
  cuidador_id INT NULL,
  paciente_id INT NULL,
  contrato_paciente_id INT NULL,
  plano_id INT NULL,
  data DATETIME NULL,
  tipo_transacao VARCHAR(20) NOT NULL DEFAULT 'Entrada',
  categoria_id INT NULL,
  moeda VARCHAR(30) NULL,
  valor DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status VARCHAR(30) NOT NULL DEFAULT 'Pendente',
  data_vencimento DATE NULL,
  mes_referencia CHAR(7) NULL,
  data_pagamento DATE NULL,
  descricao VARCHAR(255) NULL,
  detalhes TEXT NULL,
  origem VARCHAR(50) NULL,
  observacoes TEXT NULL,
  PRIMARY KEY (id),
  KEY idx_financeiro_contrato_mes (contrato_paciente_id, mes_referencia, tipo_transacao),
  KEY idx_financeiro_paciente (paciente_id),
  KEY idx_financeiro_vencimento (data_vencimento, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_financeiro ADD COLUMN paciente_id INT NULL AFTER cuidador_id',
    'SELECT "tb_financeiro.paciente_id já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_financeiro'
    AND COLUMN_NAME = 'paciente_id'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_financeiro ADD COLUMN contrato_paciente_id INT NULL AFTER paciente_id',
    'SELECT "tb_financeiro.contrato_paciente_id já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_financeiro'
    AND COLUMN_NAME = 'contrato_paciente_id'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_financeiro ADD COLUMN categoria_id INT NULL AFTER tipo_transacao',
    'SELECT "tb_financeiro.categoria_id já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_financeiro'
    AND COLUMN_NAME = 'categoria_id'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_financeiro ADD COLUMN data_vencimento DATE NULL AFTER status',
    'SELECT "tb_financeiro.data_vencimento já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_financeiro'
    AND COLUMN_NAME = 'data_vencimento'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_financeiro ADD COLUMN mes_referencia CHAR(7) NULL AFTER data_vencimento',
    'SELECT "tb_financeiro.mes_referencia já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_financeiro'
    AND COLUMN_NAME = 'mes_referencia'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_financeiro ADD COLUMN data_pagamento DATE NULL AFTER mes_referencia',
    'SELECT "tb_financeiro.data_pagamento já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_financeiro'
    AND COLUMN_NAME = 'data_pagamento'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_financeiro ADD COLUMN descricao VARCHAR(255) NULL AFTER data_pagamento',
    'SELECT "tb_financeiro.descricao já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_financeiro'
    AND COLUMN_NAME = 'descricao'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_financeiro ADD COLUMN detalhes TEXT NULL AFTER descricao',
    'SELECT "tb_financeiro.detalhes já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_financeiro'
    AND COLUMN_NAME = 'detalhes'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE tb_financeiro ADD COLUMN origem VARCHAR(50) NULL AFTER detalhes',
    'SELECT "tb_financeiro.origem já existe"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_financeiro'
    AND COLUMN_NAME = 'origem'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Evita duplicar mensalidade do mesmo contrato/mês.
-- Se o índice já existir, pode ignorar o erro desta linha.
-- CREATE UNIQUE INDEX uk_financeiro_contrato_mes ON tb_financeiro (contrato_paciente_id, mes_referencia, tipo_transacao);
