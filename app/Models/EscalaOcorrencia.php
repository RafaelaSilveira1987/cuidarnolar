<?php

namespace App\Models;

class EscalaOcorrencia extends BaseModuleModel
{
    protected string $table = 'tb_escala_ocorrencias';

    protected array $fillable = [
        'escala_base_id','paciente_id','cuidador_id','data_plantao','inicio','fim',
        'tipo_plantao','status','origem','observacoes',
    ];

    protected array $nullable = ['escala_base_id', 'cuidador_id', 'observacoes'];

    public function conflito(int $cuidadorId, string $inicio, string $fim, ?int $ignorarId = null): bool
    {
        $sql = "\n            SELECT id\n            FROM tb_escala_ocorrencias\n            WHERE cuidador_id = :cuidador_id\n              AND status NOT IN ('cancelado', 'faltou')\n              AND inicio < :fim\n              AND fim > :inicio\n        ";

        $params = [
            ':cuidador_id' => $cuidadorId,
            ':inicio' => $inicio,
            ':fim' => $fim,
        ];

        if ($ignorarId) {
            $sql .= ' AND id <> :id';
            $params[':id'] = $ignorarId;
        }

        $sql .= ' LIMIT 1';

        return (bool)$this->rawFirst($sql, $params);
    }

    public function porSemana(string $inicio, string $fim, ?int $pacienteId = null, ?int $cuidadorId = null): array
    {
        $sql = "\n            SELECT\n                eo.*,\n                TIME(eo.inicio) AS turno_inicio,\n                TIME(eo.fim) AS turno_fim,\n                eo.paciente_id,\n                eo.cuidador_id AS colaborador_id,\n                p.nome_completo AS paciente_nome,\n                p.uuid AS paciente_uuid,\n                c.nome_completo AS colaborador_nome,\n                c.uuid AS colaborador_uuid\n            FROM tb_escala_ocorrencias eo\n            INNER JOIN tb_pacientes p ON p.id = eo.paciente_id\n            LEFT JOIN tb_cuidador c ON c.id = eo.cuidador_id\n            WHERE eo.data_plantao BETWEEN :inicio AND :fim\n        ";

        $params = [':inicio' => $inicio, ':fim' => $fim];

        if ($pacienteId) {
            $sql .= ' AND eo.paciente_id = :paciente_id';
            $params[':paciente_id'] = $pacienteId;
        }

        if ($cuidadorId) {
            $sql .= ' AND eo.cuidador_id = :cuidador_id';
            $params[':cuidador_id'] = $cuidadorId;
        }

        $sql .= ' ORDER BY eo.data_plantao ASC, eo.inicio ASC';

        return $this->rawAll($sql, $params);
    }
}
