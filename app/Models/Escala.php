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

    // =========================================================
    // Pacientes
    // =========================================================

    /**
     * Lista todos os pacientes para o select de filtro.
     * Retorna uuid, nome_completo e id para compatibilidade.
     */
    public function listarPacientes(): array
    {
        return $this->query("
            SELECT
                id,
                uuid,
                nome_completo
            FROM tb_pacientes
            ORDER BY nome_completo
        ")->fetchAll();
    }

    public function buscarPacientePorUuid(string $uuid): array|false
    {
        return $this->query("
            SELECT id, uuid, nome_completo
            FROM tb_pacientes
            WHERE uuid = :uuid
            LIMIT 1
        ", ['uuid' => $uuid])->fetch();
    }

    // =========================================================
    // Cuidadores
    // =========================================================

    /**
     * Lista todos os cuidadores ativos para o select de filtro.
     * Retorna uuid, nome_completo e id para compatibilidade.
     */
    public function listaCuidadores(): array
    {
        return $this->query("
            SELECT
                id,
                uuid,
                nome_completo
            FROM tb_cuidador
            ORDER BY nome_completo
        ")->fetchAll();
    }

    public function buscarCuidadorPorUuid(string $uuid): array|false
    {
        return $this->query("
            SELECT id, uuid, nome_completo
            FROM tb_cuidador
            WHERE uuid = :uuid
            LIMIT 1
        ", ['uuid' => $uuid])->fetch();
    }

    // =========================================================
    // Join completo (usado na view antiga / relatórios)
    // =========================================================

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
            INNER JOIN tb_cuidador c  ON c.id = eo.cuidador_id
            INNER JOIN tb_pacientes p ON p.id = eo.paciente_id
            ORDER BY eo.data_plantao ASC, eo.inicio ASC
        ")->fetchAll();
    }
}