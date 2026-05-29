ALTER TABLE tb_relatorio_plantao
    ADD COLUMN data_nascimento DATE NULL AFTER data_fim,
    ADD COLUMN internacao VARCHAR(255) NULL AFTER data_nascimento,
    ADD COLUMN tipo_local ENUM('hospital', 'domiciliar') NULL AFTER internacao,
    ADD COLUMN quarto VARCHAR(120) NULL AFTER tipo_local,
    ADD COLUMN nome_acompanhante VARCHAR(150) NULL AFTER quarto,
    ADD COLUMN frequencia_respiratoria VARCHAR(10) NULL AFTER spo2,
    ADD COLUMN hidratacao_registros JSON NULL AFTER hidratacao_ml,
    ADD COLUMN urina_horarios JSON NULL AFTER diurese,
    ADD COLUMN fezes_horarios JSON NULL AFTER evacuacao,
    ADD COLUMN estado_geral VARCHAR(120) NULL AFTER estado_paciente,
    ADD COLUMN queixas_referidas LONGTEXT NULL AFTER estado_geral,
    ADD COLUMN exame_fisico LONGTEXT NULL AFTER queixas_referidas,
    ADD COLUMN pele_mucosas VARCHAR(255) NULL AFTER exame_fisico,
    ADD COLUMN visita_medica LONGTEXT NULL AFTER pele_mucosas,
    ADD COLUMN entrada_saida_profissionais LONGTEXT NULL AFTER visita_medica,
    ADD COLUMN entrada_saida_familiares LONGTEXT NULL AFTER entrada_saida_profissionais,
    ADD COLUMN plantao_entregue_para VARCHAR(150) NULL AFTER entrada_saida_familiares,
    ADD COLUMN peso VARCHAR(40) NULL AFTER plantao_entregue_para;

ALTER TABLE tb_sinais_vitais
    ADD COLUMN frequencia_respiratoria VARCHAR(20) NULL AFTER spo2;
