-- Patch v12 - Contrato sem tipo "Contrato fechado" e cobrança por plantão mais flexível.
-- Não remove a coluna valor_contrato para evitar quebrar instalações existentes.
-- Apenas migra contratos antigos do tipo "Contrato fechado" para "Mensal" quando houver valor.

UPDATE tb_contratos_paciente
   SET valor_mensal = valor_contrato
 WHERE tipo_cobranca = 'Contrato fechado'
   AND COALESCE(valor_mensal, 0) = 0
   AND COALESCE(valor_contrato, 0) > 0;

UPDATE tb_contratos_paciente
   SET tipo_cobranca = 'Mensal'
 WHERE tipo_cobranca = 'Contrato fechado';
