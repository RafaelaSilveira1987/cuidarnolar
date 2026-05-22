<?php

namespace App\Models;

class Escala extends BaseModuleModel
{
    protected string $table = 'tb_escala_base';

    protected string $searchColumn = 'nome';

    protected array $fillable = [
        'paciente_id',
        'nome',
        'tipo_plantao',
        'hora_inicio',
        'hora_fim',
        'tipo_atendimento',
        'local',
        'recorrente',
        'domingo',
        'segunda',
        'terca',
        'quarta',
        'quinta',
        'sexta',
        'sabado',
        'revezamento_automatico',
        'ativo',
        'observacoes',
    ];

    protected array $nullable = [
        'local',
        'observacoes',
    ];

    public function listarPacientes(): array
    {
        return $this->query("SELECT * FROM tb_pacientes ORDER BY nome_completo")->fetchAll();
    }

    public function listarCuidadores(): array
    {
        return $this->query("SELECT * FROM tb_cuidador")->fetchAll();
    }

    public function listarComJoin()
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
}