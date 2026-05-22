<?php

namespace App\Models;

class EscalaOcorrencia extends BaseModuleModel
{
    protected string $table = 'tb_escala_ocorrencias';

    protected array $fillable = [
        'escala_base_id',
        'paciente_id',
        'cuidador_id',
        'data_plantao',
        'inicio',
        'fim',
        'tipo_plantao',
        'status',
        'origem',
        'observacoes',
    ];

    protected array $nullable = [
        'escala_base_id',  // plantão avulso não tem escala base
        'observacoes',
    ];

    public function conflito(
        int $cuidadorId,
        string $inicio,
        string $fim
    ): bool {
        $sql = "
            SELECT id
            FROM tb_escala_ocorrencias
            WHERE cuidador_id = :cuidador_id
              AND status NOT IN ('cancelado', 'faltou')
              AND inicio < :fim
              AND fim   > :inicio
            LIMIT 1
        ";

        $stmt = $this->query($sql, [
            ':cuidador_id' => $cuidadorId,
            ':inicio'      => $inicio,
            ':fim'         => $fim,
        ]);

        return (bool) $stmt->fetch();
    }

    public function listarComJoin(): array
    {
        return $this->query("
            SELECT
                eo.id,
                eo.data_plantao,
                eo.inicio,
                eo.fim,
                eo.status,
                c.nome_completo AS cuidador,
                p.nome_completo AS paciente
            FROM tb_escala_ocorrencias eo
            INNER JOIN tb_cuidador  c ON c.id = eo.cuidador_id
            INNER JOIN tb_pacientes p ON p.id = eo.paciente_id
            ORDER BY eo.data_plantao ASC, eo.inicio ASC
        ")->fetchAll();
    }

    public function porSemana(
        string $inicio,
        string $fim,
        ?int $pacienteId  = null,
        ?int $cuidadorId  = null
    ): array {
        $sql = "
            SELECT
                eo.*,
                eo.inicio        AS turno_inicio,     -- alias que o controller espera
                eo.fim           AS turno_fim,        -- alias que o controller espera
                eo.paciente_id   AS paciente_id,
                eo.cuidador_id   AS colaborador_id,
                CONCAT(
                    TIME_FORMAT(eo.inicio, '%Hh'),
                    ' → ',
                    TIME_FORMAT(eo.fim, '%Hh')
                )                AS turno_label,      -- alias que o controller espera
                p.nome_completo  AS paciente_nome,
                p.uuid           AS paciente_uuid,
                c.nome_completo  AS colaborador_nome,
                c.uuid           AS colaborador_uuid,
                det.endereco,
                det.tipo_contrato,
                det.cor_avatar,
                det.cor_avatar_t
            FROM tb_escala_ocorrencias eo
            INNER JOIN tb_pacientes p           ON p.id = eo.paciente_id
            LEFT  JOIN tb_paciente_detalhes det ON det.paciente_id = p.id
            LEFT  JOIN tb_cuidador c            ON c.id = eo.cuidador_id
            WHERE eo.data_plantao BETWEEN :inicio AND :fim  
        ";

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