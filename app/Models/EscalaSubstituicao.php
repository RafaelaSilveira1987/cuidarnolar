<?php

namespace App\Models;

class EscalaSubstituicao extends BaseModel
{
    public function pendentesSemana(string $inicio, string $fim): array
    {
        return $this->porSemana($inicio, $fim);
    }

    public function porSemana(string $inicio, string $fim): array
    {
        $corOriginal = $this->escalaProfissionalCorSelect('epo');
        $corSubstituto = $this->escalaProfissionalCorSelect('eps');

        return $this->rawAll(
            "SELECT
                es.*,
                eo.data_plantao,
                TIME_FORMAT(eo.inicio, '%H:%i') AS hora_inicio,
                TIME_FORMAT(eo.fim, '%H:%i') AS hora_fim,
                eb.paciente_id,
                p.nome_completo AS paciente_nome,
                co.nome_completo AS cuidador_original_nome,
                {$corOriginal} AS cuidador_original_cor_escala,
                cs.nome_completo AS cuidador_substituto_nome,
                {$corSubstituto} AS cuidador_substituto_cor_escala
             FROM tb_escala_substituicoes es
             INNER JOIN tb_escala_ocorrencias eo ON eo.id = es.ocorrencia_id
             INNER JOIN tb_escala_base eb ON eb.id = eo.escala_base_id
             INNER JOIN tb_pacientes p ON p.id = eb.paciente_id
             LEFT JOIN tb_cuidador co ON co.id = es.cuidador_original_id
             LEFT JOIN tb_cuidador cs ON cs.id = es.cuidador_substituto_id
             LEFT JOIN tb_escala_profissionais epo
                ON epo.escala_base_id = eo.escala_base_id
               AND epo.cuidador_id = es.cuidador_original_id
               AND epo.ativo = 1
             LEFT JOIN tb_escala_profissionais eps
                ON eps.escala_base_id = eo.escala_base_id
               AND eps.cuidador_id = es.cuidador_substituto_id
               AND eps.ativo = 1
             WHERE eo.data_plantao BETWEEN :inicio AND :fim
             ORDER BY eo.data_plantao ASC, eo.inicio ASC",
            [':inicio' => $inicio, ':fim' => $fim]
        );
    }

    public function registrar(int $ocorrenciaId, int $substitutoId, string $motivo, ?string $observacoes = null): bool
    {
        $ocorrencia = $this->rawFirst(
            'SELECT * FROM tb_escala_ocorrencias WHERE id = :id',
            [':id' => $ocorrenciaId]
        );

        if (!$ocorrencia) {
            return false;
        }

        $originalId = !empty($ocorrencia['cuidador_id']) ? (int)$ocorrencia['cuidador_id'] : $substitutoId;

        $this->db->beginTransaction();
        try {
            $this->query(
                "INSERT INTO tb_escala_substituicoes
                    (ocorrencia_id, cuidador_original_id, cuidador_substituto_id, motivo, observacoes, data_plantao)
                 VALUES
                    (:ocorrencia_id, :original_id, :substituto_id, :motivo, :observacoes, :data_plantao)",
                [
                    ':ocorrencia_id' => $ocorrenciaId,
                    ':original_id' => $originalId,
                    ':substituto_id' => $substitutoId,
                    ':motivo' => $motivo ?: 'Substituição manual',
                    ':observacoes' => $observacoes,
                    ':data_plantao' => $ocorrencia['data_plantao'] ?? null,
                ]
            );

            $this->query(
                "UPDATE tb_escala_ocorrencias
                 SET cuidador_id = :substituto_id,
                     status = 'substituido',
                     origem = 'manual'
                 WHERE id = :id",
                [':substituto_id' => $substitutoId, ':id' => $ocorrenciaId]
            );

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function escalaProfissionalCorSelect(string $alias = 'ep'): string
    {
        if ($this->tableHasColumn('tb_escala_profissionais', 'cor_escala')) {
            return "{$alias}.cor_escala";
        }

        return "NULL";
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        try {
            $row = $this->rawFirst("SHOW COLUMNS FROM {$table} LIKE :column", [':column' => $column]);
            return (bool)$row;
        } catch (\Throwable) {
            return false;
        }
    }
}
