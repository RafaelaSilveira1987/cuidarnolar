-- Opcional: rode apenas se sua tabela ainda NÃO tiver uma chave única por período.
-- Essa chave evita aprovações duplicadas da mesma escala/paciente/período.

ALTER TABLE tb_escala_aprovacoes
ADD UNIQUE KEY uq_escala_aprovacao_periodo (
    escala_base_id,
    paciente_id,
    periodo_inicio,
    periodo_fim
);
