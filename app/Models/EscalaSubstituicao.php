<?php

namespace App\Models;

class EscalaSubstituicao extends BaseModuleModel
{
    protected string $table = 'tb_escala_substituicoes';

    protected array $fillable = [
        'ocorrencia_id',
        'cuidador_original_id',
        'cuidador_substituto_id',
        'motivo',
        'observacoes',
    ];

    protected array $nullable = [
        'motivo',
        'observacoes',
    ];

    public function porSemana($inicio, $fim)
    {
        return $this->query("
        SELECT 
            s.*,
            p.nome_completo AS paciente_nome,
            c.nome_completo AS colaborador_original_nome,
            sub.nome_completo AS substituto_nome
        FROM tb_escala_substituicoes s
        INNER JOIN tb_escala_ocorrencias eo ON eo.id = s.ocorrencia_id
        INNER JOIN tb_pacientes p ON p.id = eo.paciente_id
        INNER JOIN tb_cuidador c ON c.id = s.cuidador_original_id
        INNER JOIN tb_cuidador sub ON sub.id = s.cuidador_substituto_id
        WHERE s.data_plantao BETWEEN :inicio AND :fim
        ORDER BY s.data_plantao ASC
    ", [
            ':inicio' => $inicio,
            ':fim'    => $fim
        ])->fetchAll();
    }
}