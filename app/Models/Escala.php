<?php

namespace App\Models;

class Escala extends BaseModuleModel
{
    protected string $table = 'tb_escala_base';
    protected string $searchColumn = 'nome';
    protected string $orderBy = 'id';
    protected string $orderDirection = 'DESC';

    protected array $fillable = [
        'paciente_id',
        'nome',
        'tipo_cobertura',
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
        'nome',
        'local',
        'observacoes',
    ];

    public function listarPacientes(): array
    {
        return $this->query("SELECT id, uuid, nome_completo FROM tb_pacientes ORDER BY nome_completo")->fetchAll();
    }

    public function buscarPacientePorUuid(string $uuid): array|false
    {
        return $this->query(
            "SELECT id, uuid, nome_completo FROM tb_pacientes WHERE uuid = :uuid LIMIT 1",
            [':uuid' => $uuid]
        )->fetch();
    }

    public function listaCuidadores(): array
    {
        return $this->query(
            "SELECT id, uuid, nome_completo, especialidade, contrato_horas, status, COALESCE(cor_escala, '#0f766e') AS cor_escala
             FROM tb_cuidador
             WHERE status IN ('Ativo', 'Standby')
             ORDER BY nome_completo"
        )->fetchAll();
    }

    public function buscarCuidadorPorUuid(string $uuid): array|false
    {
        return $this->query(
            "SELECT id, uuid, nome_completo, COALESCE(cor_escala, '#0f766e') AS cor_escala FROM tb_cuidador WHERE uuid = :uuid LIMIT 1",
            [':uuid' => $uuid]
        )->fetch();
    }

    public function escalaBaseAtivaPorPaciente(int $pacienteId): array|false
    {
        return $this->rawFirst(
            "SELECT eb.*
             FROM tb_escala_base eb
             WHERE eb.paciente_id = :paciente_id
               AND eb.ativo = 1
             ORDER BY eb.id DESC
             LIMIT 1",
            [':paciente_id' => $pacienteId]
        );
    }

    public function profissionaisDaEscala(int $escalaBaseId): array
    {
        return $this->rawAll(
            "SELECT ep.*, c.nome_completo, c.especialidade, c.contrato_horas, COALESCE(c.cor_escala, '#0f766e') AS cor_escala
             FROM tb_escala_profissionais ep
             JOIN tb_cuidador c ON c.id = ep.cuidador_id
             WHERE ep.escala_base_id = :escala_base_id
               AND ep.ativo = 1
             ORDER BY ep.ordem_revezamento ASC, c.nome_completo ASC",
            [':escala_base_id' => $escalaBaseId]
        );
    }

    public function resumoPorPaciente(int $pacienteId): array
    {
        $base = $this->escalaBaseAtivaPorPaciente($pacienteId);

        return [
            'base' => $base ?: null,
            'profissionais' => $base ? $this->profissionaisDaEscala((int)$base['id']) : [],
            'proximos' => $this->proximosPlantoesPaciente($pacienteId),
        ];
    }

    public function proximosPlantoesPaciente(int $pacienteId, int $limit = 8): array
    {
        return $this->rawAll(
            "SELECT eo.*, c.nome_completo AS cuidador_nome, COALESCE(c.cor_escala, '#0f766e') AS cuidador_cor
             FROM tb_escala_ocorrencias eo
             LEFT JOIN tb_cuidador c ON c.id = eo.cuidador_id
             WHERE eo.paciente_id = :paciente_id
               AND eo.data_plantao >= CURDATE()
             ORDER BY eo.data_plantao ASC, eo.inicio ASC
             LIMIT {$limit}",
            [':paciente_id' => $pacienteId]
        );
    }

    public function salvarBasePaciente(int $pacienteId, array $data, array $cuidadorIds = []): int
    {
        $existente = $this->escalaBaseAtivaPorPaciente($pacienteId);
        $payload = $this->normalizarPayloadBase($pacienteId, $data);

        if ($existente) {
            $escalaId = (int)$existente['id'];
            $this->updateRecord($escalaId, $payload);
        } else {
            $escalaId = $this->createRecord($payload);
        }

        $this->sincronizarProfissionais($escalaId, $cuidadorIds);

        return $escalaId;
    }

    private function normalizarPayloadBase(int $pacienteId, array $data): array
    {
        $tipo = (string)($data['tipo_cobertura'] ?? '12h');
        if (!in_array($tipo, ['24h', '12h', '8h', '6h'], true)) {
            $tipo = '12h';
        }

        $horaInicio = trim((string)($data['hora_inicio'] ?? '07:00'));
        $horaFim = trim((string)($data['hora_fim'] ?? $this->horaFimPadrao($tipo, $horaInicio)));

        return [
            'paciente_id' => $pacienteId,
            'nome' => trim((string)($data['nome'] ?? 'Escala base')) ?: 'Escala base',
            'tipo_cobertura' => $tipo,
            'hora_inicio' => $horaInicio,
            'hora_fim' => $horaFim,
            'tipo_atendimento' => in_array(($data['tipo_atendimento'] ?? 'domiciliar'), ['domiciliar', 'hospitalar'], true)
                ? $data['tipo_atendimento']
                : 'domiciliar',
            'local' => trim((string)($data['local'] ?? '')),
            'recorrente' => (($data['recorrente'] ?? 'sim') === 'nao') ? 'nao' : 'sim',
            'domingo' => !empty($data['domingo']) ? 1 : 0,
            'segunda' => !empty($data['segunda']) ? 1 : 0,
            'terca' => !empty($data['terca']) ? 1 : 0,
            'quarta' => !empty($data['quarta']) ? 1 : 0,
            'quinta' => !empty($data['quinta']) ? 1 : 0,
            'sexta' => !empty($data['sexta']) ? 1 : 0,
            'sabado' => !empty($data['sabado']) ? 1 : 0,
            'revezamento_automatico' => !empty($data['revezamento_automatico']) ? 1 : 0,
            'ativo' => 1,
            'observacoes' => trim((string)($data['observacoes'] ?? '')),
        ];
    }

    private function sincronizarProfissionais(int $escalaBaseId, array $cuidadorIds): void
    {
        $this->query(
            "UPDATE tb_escala_profissionais SET ativo = 0 WHERE escala_base_id = :escala_base_id",
            [':escala_base_id' => $escalaBaseId]
        );

        $cuidadorIds = array_values(array_unique(array_filter(array_map('intval', $cuidadorIds))));

        foreach ($cuidadorIds as $ordem => $cuidadorId) {
            $this->query(
                "INSERT INTO tb_escala_profissionais
                    (escala_base_id, cuidador_id, ordem_revezamento, principal_escala, ativo)
                 VALUES
                    (:escala_base_id, :cuidador_id, :ordem, 1, 1)",
                [
                    ':escala_base_id' => $escalaBaseId,
                    ':cuidador_id' => $cuidadorId,
                    ':ordem' => $ordem + 1,
                ]
            );
        }
    }

    private function horaFimPadrao(string $tipo, string $horaInicio): string
    {
        $horas = match ($tipo) {
            '24h' => 24,
            '12h' => 12,
            '8h' => 8,
            '6h' => 6,
            default => 12,
        };

        try {
            $dt = new \DateTime('2000-01-01 ' . $horaInicio);
            $dt->modify('+' . $horas . ' hours');
            return $dt->format('H:i');
        } catch (\Throwable) {
            return $tipo === '24h' ? '07:00' : '19:00';
        }
    }

    public function listarComJoin(): array
    {
        return $this->query(
            "SELECT
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
            ORDER BY eo.data_plantao ASC, eo.inicio ASC"
        )->fetchAll();
    }

    public function listarPacientesOperacionais(): array
    {
        $sql = "
        SELECT
            p.id,
            p.uuid,
            p.nome_completo,
            p.prontuario,
            p.status,
            p.cuidador_id,

            cr.nome_completo AS cuidador_referencia_nome,
            cr.uuid AS cuidador_referencia_uuid,
            COALESCE(cr.cor_escala, '#0f766e') AS cuidador_referencia_cor,

            eb.id AS escala_base_id,
            eb.nome AS escala_nome,
            eb.tipo_cobertura,
            eb.hora_inicio,
            eb.hora_fim,
            eb.tipo_atendimento,
            eb.local,
            eb.recorrente,
            eb.revezamento_automatico,
            eb.ativo AS escala_ativa,

            cp.id AS contrato_id,
            cp.tipo_servico,
            cp.status AS contrato_status,
            cp.vigencia_inicio,
            cp.vigencia_fim,

            (SELECT GROUP_CONCAT(c2.id ORDER BY ep.ordem_revezamento ASC SEPARATOR '||')
               FROM tb_escala_profissionais ep
               JOIN tb_cuidador c2 ON c2.id = ep.cuidador_id
              WHERE ep.escala_base_id = eb.id AND ep.ativo = 1) AS equipe_ids,

            (SELECT GROUP_CONCAT(c2.uuid ORDER BY ep.ordem_revezamento ASC SEPARATOR '||')
               FROM tb_escala_profissionais ep
               JOIN tb_cuidador c2 ON c2.id = ep.cuidador_id
              WHERE ep.escala_base_id = eb.id AND ep.ativo = 1) AS equipe_uuids,

            (SELECT GROUP_CONCAT(c2.nome_completo ORDER BY ep.ordem_revezamento ASC SEPARATOR '||')
               FROM tb_escala_profissionais ep
               JOIN tb_cuidador c2 ON c2.id = ep.cuidador_id
              WHERE ep.escala_base_id = eb.id AND ep.ativo = 1) AS equipe_nomes,

            (SELECT GROUP_CONCAT(COALESCE(c2.cor_escala, '#0f766e') ORDER BY ep.ordem_revezamento ASC SEPARATOR '||')
               FROM tb_escala_profissionais ep
               JOIN tb_cuidador c2 ON c2.id = ep.cuidador_id
              WHERE ep.escala_base_id = eb.id AND ep.ativo = 1) AS equipe_cores

        FROM tb_escala_base eb

        INNER JOIN tb_pacientes p
            ON p.id = eb.paciente_id

        LEFT JOIN tb_cuidador cr
            ON cr.id = p.cuidador_id

        LEFT JOIN tb_contratos_paciente cp
            ON cp.paciente_id = p.id
            AND cp.status = 'Ativo'

        WHERE p.status = 'Ativo'
          AND eb.ativo = 1

        ORDER BY p.nome_completo ASC
    ";

        return $this->rawAll($sql);
    }
    
}