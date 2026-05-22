<?php

namespace App\Models;

class EscalaOcorrencia extends BaseModuleModel
{
    protected string $table =
    'tb_escala_ocorrencias';

    protected array $fillable = [
        'escala_base_id',
        'paciente_id',
        'cuidador_id',

        'data_plantao',

        'inicio',
        'fim',

        'status',
        'observacoes',
    ];

    protected array $nullable = [
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
            AND inicio < :fim
            AND fim > :inicio
            LIMIT 1
        ";

        $stmt = $this->query($sql, [
            ':cuidador_id' => $cuidadorId,
            ':inicio' => $inicio,
            ':fim' => $fim,
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
        INNER JOIN tb_cuidador c ON c.id = eo.cuidador_id
        INNER JOIN tb_pacientes p ON p.id = eo.paciente_id
        ORDER BY eo.data_plantao ASC, eo.inicio ASC
    ")->fetchAll();
    }

    public function porSemana($inicio, $fim, $pacienteId = null, $cuidadorId = null)
{
    $sql = "
        SELECT 
            eo.*,
            p.nome_completo AS paciente_nome,
            c.nome_completo AS colaborador_nome,
            det.endereco,
            det.tipo_contrato,
            det.cor_avatar,
            det.cor_avatar_t
        FROM tb_escala_ocorrencias eo
        INNER JOIN tb_pacientes p ON p.id = eo.paciente_id
        LEFT JOIN tb_paciente_detalhes det ON det.paciente_id = p.id
        LEFT JOIN tb_cuidador c ON c.id = eo.cuidador_id
        WHERE eo.data_plantao BETWEEN :inicio AND :fim
    ";

    $params = [
        ':inicio' => $inicio,
        ':fim'    => $fim
    ];

    if ($pacienteId) {
        $sql .= " AND eo.paciente_id = :paciente_id";
        $params[':paciente_id'] = $pacienteId;
    }

    if ($cuidadorId) {
        $sql .= " AND eo.cuidador_id = :cuidador_id";
        $params[':cuidador_id'] = $cuidadorId;
    }

    $sql .= " ORDER BY eo.data_plantao ASC, eo.inicio ASC";

    return $this->rawAll($sql, $params);
}
}