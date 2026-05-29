-- =====================================================
-- Agenda - Etapa 1
-- Execute uma vez antes de substituir os arquivos.
-- Faça backup do banco antes. Sem heroísmo com banco em produção.
-- =====================================================

ALTER TABLE tb_eventos
    MODIFY paciente_id INT NULL DEFAULT NULL;

ALTER TABLE tb_eventos
    MODIFY status ENUM('Pendente','Agendado','Em andamento','Concluído','Cancelado')
    CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'Pendente';

ALTER TABLE tb_eventos
    ADD COLUMN IF NOT EXISTS tipo_evento VARCHAR(60) NULL DEFAULT 'Outro' AFTER paciente_id,
    ADD COLUMN IF NOT EXISTS data_inicio DATETIME NULL AFTER descricao,
    ADD COLUMN IF NOT EXISTS data_fim DATETIME NULL AFTER data_inicio,
    ADD COLUMN IF NOT EXISTS local VARCHAR(180) NULL AFTER data_fim,
    ADD COLUMN IF NOT EXISTS prioridade ENUM('Baixa','Normal','Alta','Urgente') NULL DEFAULT 'Normal' AFTER local;

UPDATE tb_eventos
SET data_inicio = data_evento
WHERE data_inicio IS NULL;

UPDATE tb_eventos
SET tipo_evento = 'Outro'
WHERE tipo_evento IS NULL OR tipo_evento = '';

UPDATE tb_eventos
SET prioridade = 'Normal'
WHERE prioridade IS NULL OR prioridade = '';

UPDATE tb_eventos
SET status = 'Pendente'
WHERE status IS NULL OR status = '';
