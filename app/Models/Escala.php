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

    private ?bool $escalaProfissionalTemCorEscala = null;

    private function escalaProfissionalCorSelect(string $alias, string $as = 'cor_escala'): string
    {
        return $this->escalaProfissionalTemCorEscala()
            ? "{$alias}.cor_escala AS {$as}"
            : "NULL AS {$as}";
    }

    private function escalaProfissionalTemCorEscala(): bool
    {
        if ($this->escalaProfissionalTemCorEscala !== null) {
            return $this->escalaProfissionalTemCorEscala;
        }

        $row = $this->rawFirst(
            "SHOW COLUMNS FROM tb_escala_profissionais LIKE 'cor_escala'"
        );

        return $this->escalaProfissionalTemCorEscala = (bool)$row;
    }

    private function paletaCoresEscala(): array
    {
        return [
            '#68f7f2',
            '#08f31c',
            '#2563eb',
            '#7c3aed',
            '#db2777',
            '#dc2626',
            '#ea580c',
            '#ca8a04',
            '#16a34a',
            '#475569',
        ];
    }

    private function corEscalaValida(mixed $cor): ?string
    {
        $cor = strtolower(trim((string)$cor));

        // Sem cor / sem preenchimento deve ser gravado como NULL.
        if ($cor === '') {
            return null;
        }

        if (!preg_match('/^#[0-9a-f]{6}$/', $cor)) {
            return null;
        }

        return in_array($cor, $this->paletaCoresEscala(), true) ? $cor : null;
    }

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
        return $this->rawAll(
            "SELECT id, uuid, nome_completo, especialidade, contrato_horas, status
             FROM tb_cuidador
             WHERE status IN ('Ativo', 'Standby')
             ORDER BY nome_completo"
        );
    }

    public function buscarCuidadorPorUuid(string $uuid): array|false
    {
        return $this->rawFirst(
            "SELECT id, uuid, nome_completo
             FROM tb_cuidador
             WHERE uuid = :uuid
             LIMIT 1",
            [':uuid' => $uuid]
        );
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
        $corSelect = $this->escalaProfissionalCorSelect('ep');

        return $this->rawAll(
            "SELECT ep.*, c.nome_completo, c.especialidade, c.contrato_horas, {$corSelect}
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
            "SELECT eo.*, c.nome_completo AS cuidador_nome
             FROM tb_escala_ocorrencias eo
             LEFT JOIN tb_cuidador c ON c.id = eo.cuidador_id
             WHERE eo.paciente_id = :paciente_id
               AND eo.data_plantao >= CURDATE()
             ORDER BY eo.data_plantao ASC, eo.inicio ASC
             LIMIT {$limit}",
            [':paciente_id' => $pacienteId]
        );
    }

    public function salvarBasePaciente(int $pacienteId, array $data, array $cuidadorIds = [], array $cuidadorCores = []): int
    {
        $existente = $this->escalaBaseAtivaPorPaciente($pacienteId);
        $payload = $this->normalizarPayloadBase($pacienteId, $data);

        if ($existente) {
            $escalaId = (int)$existente['id'];
            $this->updateRecord($escalaId, $payload);
        } else {
            $escalaId = $this->createRecord($payload);
        }

        $this->sincronizarProfissionais($escalaId, $cuidadorIds, $cuidadorCores);

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

    private function sincronizarProfissionais(int $escalaBaseId, array $cuidadorIds, array $cuidadorCores = []): void
    {
        $this->query(
            "UPDATE tb_escala_profissionais SET ativo = 0 WHERE escala_base_id = :escala_base_id",
            [':escala_base_id' => $escalaBaseId]
        );

        $cuidadorIds = array_values(array_unique(array_filter(array_map('intval', $cuidadorIds))));
        $temCorPorEscala = $this->escalaProfissionalTemCorEscala();

        foreach ($cuidadorIds as $ordem => $cuidadorId) {
            $cor = $this->corEscalaValida($cuidadorCores[$cuidadorId] ?? null);

            if ($temCorPorEscala) {
                $this->query(
                    "INSERT INTO tb_escala_profissionais
                        (escala_base_id, cuidador_id, ordem_revezamento, principal_escala, ativo, cor_escala)
                     VALUES
                        (:escala_base_id, :cuidador_id, :ordem, 1, 1, :cor_escala)",
                    [
                        ':escala_base_id' => $escalaBaseId,
                        ':cuidador_id' => $cuidadorId,
                        ':ordem' => $ordem + 1,
                        ':cor_escala' => $cor,
                    ]
                );
                continue;
            }

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

    public function listarPacientesOperacionais(?int $pacienteId = null, ?int $cuidadorId = null): array
    {
        $corEquipeExpr = $this->escalaProfissionalTemCorEscala() ? 'ep.cor_escala' : 'NULL';

        $sql = "
            SELECT
                p.id,
                p.uuid,
                p.nome_completo,
                p.prontuario,
                p.status,
                p.endereco_completo AS endereco,
                p.cuidador_id AS cuidador_referencia_id,

                cr.uuid AS cuidador_referencia_uuid,
                cr.nome_completo AS cuidador_referencia_nome,

                eb.id AS escala_base_id,
                eb.nome AS escala_nome,
                eb.tipo_cobertura,
                eb.tipo_cobertura AS tipo_contrato,
                eb.hora_inicio,
                eb.hora_fim,
                eb.tipo_atendimento,
                eb.local,
                eb.recorrente,
                eb.revezamento_automatico,
                eb.domingo,
                eb.segunda,
                eb.terca,
                eb.quarta,
                eb.quinta,
                eb.sexta,
                eb.sabado,
                eb.ativo AS escala_ativa,

                cp.id AS contrato_id,
                cp.tipo_servico,
                cp.valor_mensal,
                cp.status AS contrato_status,
                cp.vigencia_inicio AS contrato_data_inicio,
                cp.vigencia_fim AS contrato_data_fim,

                GROUP_CONCAT(ce.id ORDER BY ep.ordem_revezamento ASC SEPARATOR '||') AS equipe_ids,
                GROUP_CONCAT(ce.uuid ORDER BY ep.ordem_revezamento ASC SEPARATOR '||') AS equipe_uuids,
                GROUP_CONCAT(ce.nome_completo ORDER BY ep.ordem_revezamento ASC SEPARATOR '||') AS equipe_nomes,
                GROUP_CONCAT(CASE
                    WHEN ce.id IS NULL THEN ''
                    ELSE COALESCE({$corEquipeExpr}, '')
                END ORDER BY ep.ordem_revezamento ASC SEPARATOR '||') AS equipe_cores

            FROM tb_escala_base eb
            INNER JOIN tb_pacientes p ON p.id = eb.paciente_id
            LEFT JOIN tb_cuidador cr ON cr.id = p.cuidador_id
            LEFT JOIN tb_contratos_paciente cp
                ON cp.paciente_id = p.id
               AND cp.status = 'Ativo'
            LEFT JOIN tb_escala_profissionais ep
                ON ep.escala_base_id = eb.id
               AND ep.ativo = 1
            LEFT JOIN tb_cuidador ce ON ce.id = ep.cuidador_id
            WHERE p.status = 'Ativo'
              AND eb.ativo = 1
        ";

        $params = [];
        if ($pacienteId) {
            $sql .= ' AND p.id = :paciente_id';
            $params[':paciente_id'] = $pacienteId;
        }

        if ($cuidadorId) {
            // Escopo do cuidador:
            // 1) cuidador referência do cadastro do paciente;
            // 2) cuidador na equipe da escala base;
            // 3) cuidador em plantões/ocorrências já geradas.
            // Placeholders distintos evitam SQLSTATE[HY093] em ambientes PDO que
            // não aceitam reutilizar o mesmo parâmetro nomeado na mesma query.
            $sql .= " AND (
                p.cuidador_id = :cuidador_referencia_id
                OR ep.cuidador_id = :cuidador_equipe_id
                OR EXISTS (
                    SELECT 1
                    FROM tb_escala_ocorrencias eo_scope
                    WHERE eo_scope.paciente_id = p.id
                      AND eo_scope.cuidador_id = :cuidador_ocorrencia_id
                )
            )";
            $params[':cuidador_referencia_id'] = $cuidadorId;
            $params[':cuidador_equipe_id'] = $cuidadorId;
            $params[':cuidador_ocorrencia_id'] = $cuidadorId;
        }

        $sql .= "
            GROUP BY
                p.id, p.uuid, p.nome_completo, p.prontuario, p.status, p.endereco_completo,
                p.cuidador_id, cr.uuid, cr.nome_completo,
                eb.id, eb.nome, eb.tipo_cobertura, eb.hora_inicio, eb.hora_fim,
                eb.tipo_atendimento, eb.local, eb.recorrente, eb.revezamento_automatico,
                eb.domingo, eb.segunda, eb.terca, eb.quarta, eb.quinta, eb.sexta, eb.sabado, eb.ativo,
                cp.id, cp.tipo_servico, cp.valor_mensal, cp.status, cp.vigencia_inicio, cp.vigencia_fim
            ORDER BY p.nome_completo ASC
        ";

        return $this->rawAll($sql, $params);
    }

    /**
     * Recria os plantões do período usando a escala base atual.
     * Mantém plantões finalizados/substituídos para não apagar histórico sensível.
     */
    public function reaplicarBasePaciente(int $pacienteId, string $periodoInicio, string $periodoFim): array
    {
        $base = $this->escalaBaseAtivaPorPaciente($pacienteId);
        if (!$base) {
            return [
                'ok' => false,
                'mensagem' => 'Paciente sem escala base ativa.',
                'escala_base_id' => null,
                'removidos' => 0,
                'gerados' => 0,
            ];
        }

        $escalaBaseId = (int)$base['id'];
        $profissionais = $this->profissionaisDaEscala($escalaBaseId);
        $cuidadorIds = array_values(array_filter(array_map(
            static fn(array $item): int => (int)($item['cuidador_id'] ?? 0),
            $profissionais
        )));

        $removidos = $this->removerOcorrenciasReaplicaveis($pacienteId, $escalaBaseId, $periodoInicio, $periodoFim);
        $gerados = $this->gerarOcorrenciasDaBase($base, $cuidadorIds, $periodoInicio, $periodoFim);

        return [
            'ok' => true,
            'mensagem' => 'Escala base reaplicada no período informado.',
            'escala_base_id' => $escalaBaseId,
            'removidos' => $removidos,
            'gerados' => $gerados,
        ];
    }

    private function removerOcorrenciasReaplicaveis(int $pacienteId, int $escalaBaseId, string $periodoInicio, string $periodoFim): int
    {
        $stmt = $this->query(
            "DELETE FROM tb_escala_ocorrencias
             WHERE paciente_id = :paciente_id
               AND escala_base_id = :escala_base_id
               AND data_plantao BETWEEN :periodo_inicio AND :periodo_fim
               AND status NOT IN ('finalizado', 'substituido')",
            [
                ':paciente_id' => $pacienteId,
                ':escala_base_id' => $escalaBaseId,
                ':periodo_inicio' => $periodoInicio,
                ':periodo_fim' => $periodoFim,
            ]
        );

        return $stmt->rowCount();
    }

    private function gerarOcorrenciasDaBase(array $base, array $cuidadorIds, string $periodoInicio, string $periodoFim): int
    {
        $inicio = new \DateTime($periodoInicio);
        $fim = new \DateTime($periodoFim);
        $fimInclusivo = (clone $fim)->modify('+1 day');
        $periodo = new \DatePeriod($inicio, new \DateInterval('P1D'), $fimInclusivo);

        $turnos = $this->turnosDaBase($base);
        $gerados = 0;
        $slot = 0;
        $agora = date('d/m/Y H:i');

        foreach ($periodo as $dia) {
            $data = $dia->format('Y-m-d');
            if (!$this->diaCobertoPelaBase($base, $dia)) {
                continue;
            }

            foreach ($turnos as $turno) {
                $cuidadorId = $this->cuidadorDoSlot($cuidadorIds, $slot, !empty($base['revezamento_automatico']));
                $slot++;

                $inicioPlantao = $data . ' ' . $turno['inicio'] . ':00';
                $fimPlantao = $this->fimPlantao($data, $turno['inicio'], $turno['fim']);
                $coberturaIncompleta = $cuidadorId ? 0 : 1;

                $this->query(
                    "INSERT INTO tb_escala_ocorrencias
                        (escala_base_id, paciente_id, cuidador_id, data_plantao, inicio, fim, tipo_plantao, status, conflito, cobertura_incompleta, observacoes, origem)
                     VALUES
                        (:escala_base_id, :paciente_id, :cuidador_id, :data_plantao, :inicio, :fim, :tipo_plantao, 'previsto', 0, :cobertura_incompleta, :observacoes, 'Automatica')
                     ON DUPLICATE KEY UPDATE
                        cuidador_id = VALUES(cuidador_id),
                        tipo_plantao = VALUES(tipo_plantao),
                        status = 'previsto',
                        conflito = 0,
                        cobertura_incompleta = VALUES(cobertura_incompleta),
                        observacoes = VALUES(observacoes),
                        origem = 'Automatica'",
                    [
                        ':escala_base_id' => (int)$base['id'],
                        ':paciente_id' => (int)$base['paciente_id'],
                        ':cuidador_id' => $cuidadorId ?: null,
                        ':data_plantao' => $data,
                        ':inicio' => $inicioPlantao,
                        ':fim' => $fimPlantao,
                        ':tipo_plantao' => $turno['tipo_plantao'],
                        ':cobertura_incompleta' => $coberturaIncompleta,
                        ':observacoes' => 'Reaplicado da escala base em ' . $agora . '.',
                    ]
                );

                $gerados++;
            }
        }

        return $gerados;
    }

    private function diaCobertoPelaBase(array $base, \DateTimeInterface $dia): bool
    {
        $mapa = [
            0 => 'domingo',
            1 => 'segunda',
            2 => 'terca',
            3 => 'quarta',
            4 => 'quinta',
            5 => 'sexta',
            6 => 'sabado',
        ];

        $campo = $mapa[(int)$dia->format('w')] ?? 'domingo';
        return !empty($base[$campo]);
    }

    private function turnosDaBase(array $base): array
    {
        $tipo = (string)($base['tipo_cobertura'] ?? '12h');
        $inicio = substr((string)($base['hora_inicio'] ?? '07:00'), 0, 5) ?: '07:00';
        $fim = substr((string)($base['hora_fim'] ?? ''), 0, 5);

        if ($tipo === '24h') {
            $primeiroFim = $fim ?: '19:00';
            return [
                ['inicio' => $inicio, 'fim' => $primeiroFim, 'tipo_plantao' => '12h'],
                ['inicio' => $primeiroFim, 'fim' => $inicio, 'tipo_plantao' => '12h'],
            ];
        }

        $tipoValido = in_array($tipo, ['12h', '8h', '6h'], true) ? $tipo : '12h';
        $fim = $fim ?: $this->horaFimPadrao($tipoValido, $inicio);

        return [
            ['inicio' => $inicio, 'fim' => $fim, 'tipo_plantao' => $tipoValido],
        ];
    }

    private function fimPlantao(string $data, string $horaInicio, string $horaFim): string
    {
        $inicio = new \DateTime($data . ' ' . $horaInicio . ':00');
        $fim = new \DateTime($data . ' ' . $horaFim . ':00');

        if ($fim <= $inicio) {
            $fim->modify('+1 day');
        }

        return $fim->format('Y-m-d H:i:s');
    }

    private function cuidadorDoSlot(array $cuidadorIds, int $slot, bool $revezamentoAutomatico): ?int
    {
        if ($cuidadorIds === []) {
            return null;
        }

        if (!$revezamentoAutomatico) {
            return (int)$cuidadorIds[0];
        }

        return (int)$cuidadorIds[$slot % count($cuidadorIds)];
    }

}
