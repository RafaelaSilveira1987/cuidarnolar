<?php

namespace App\Models;

use PDO;

class Evento extends BaseModuleModel
{
    protected string $table = 'tb_eventos';
    protected string $searchColumn = 'e.titulo';
    protected string $orderBy = 'e.data_inicio';
    protected string $orderDirection = 'DESC';

    protected array $fillable = [
        'paciente_id',
        'cuidador_id',
        'tipo_evento',
        'titulo',
        'descricao',
        'data_evento',
        'data_inicio',
        'data_fim',
        'local',
        'prioridade',
        'status',
    ];

    protected array $nullable = [
        'paciente_id',
        'cuidador_id',
        'descricao',
        'data_fim',
        'local',
    ];

    public function listForIndex(int $page = 1, int $perPage = 15, string $search = ''): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $where = '';
        $params = [];

        if ($search !== '') {
            $where = ' WHERE e.titulo LIKE :search_titulo
                OR e.tipo_evento LIKE :search_tipo
                OR p.nome_completo LIKE :search_paciente
                OR c.nome_completo LIKE :search_cuidador';

            $params = [
                ':search_titulo' => "%{$search}%",
                ':search_tipo' => "%{$search}%",
                ':search_paciente' => "%{$search}%",
                ':search_cuidador' => "%{$search}%",
            ];
        }

        $countSql = 'SELECT COUNT(*)
            FROM tb_eventos e
            LEFT JOIN tb_pacientes p ON p.id = e.paciente_id
            LEFT JOIN tb_cuidador c ON c.id = e.cuidador_id';

        $total = (int)$this->query($countSql . $where, $params)->fetchColumn();

        $data = $this->query(
            $this->baseSelect() . $where . ' ORDER BY e.data_inicio DESC LIMIT :limit OFFSET :offset',
            $params + [':limit' => $perPage, ':offset' => $offset]
        )->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int)max(1, ceil($total / $perPage)),
        ];
    }

    public function findForShow(int $id): array|false
    {
        return $this->rawFirst($this->baseSelect() . ' WHERE e.id = :id LIMIT 1', [':id' => $id]);
    }

    public function findForShowByUuid(string $uuid): array|false
    {
        return $this->rawFirst($this->baseSelect() . ' WHERE e.uuid = :uuid LIMIT 1', [':uuid' => $uuid]);
    }

    public function updateRecordByUuid(string $uuid, array $data): bool
    {
        $record = $this->findForShowByUuid($uuid);

        if (!$record) {
            return false;
        }

        return $this->updateRecord((int)$record['id'], $data);
    }

    public function eventosDoDia(string $date): array
    {
        return $this->rawAll(
            $this->baseSelect() . "
            WHERE DATE(e.data_inicio) = :data
            ORDER BY e.data_inicio ASC",
            [':data' => $date]
        );
    }

    public function proximos(int $limit = 8): array
    {
        $limit = max(1, min(30, $limit));

        return $this->rawAll(
            $this->baseSelect() . "
            WHERE e.data_inicio >= NOW()
              AND COALESCE(e.status, 'Pendente') NOT IN ('Concluído', 'Cancelado')
            ORDER BY e.data_inicio ASC
            LIMIT {$limit}"
        );
    }

    public function pendentes(int $limit = 8): array
    {
        $limit = max(1, min(30, $limit));

        return $this->rawAll(
            $this->baseSelect() . "
            WHERE COALESCE(e.status, 'Pendente') IN ('Pendente', 'Agendado', 'Em andamento')
            ORDER BY e.data_inicio ASC
            LIMIT {$limit}"
        );
    }

    public function resumoPorStatus(): array
    {
        $rows = $this->rawAll("SELECT COALESCE(status, 'Pendente') AS status, COUNT(*) AS total FROM tb_eventos GROUP BY COALESCE(status, 'Pendente')");
        $resumo = [
            'Pendente' => 0,
            'Agendado' => 0,
            'Em andamento' => 0,
            'Concluído' => 0,
            'Cancelado' => 0,
        ];

        foreach ($rows as $row) {
            $resumo[(string)$row['status']] = (int)$row['total'];
        }

        return $resumo;
    }

    public function diasComEventos(int $ano, int $mes): array
    {
        $inicio = sprintf('%04d-%02d-01', $ano, $mes);
        $fim = date('Y-m-d', strtotime($inicio . ' +1 month'));

        $rows = $this->rawAll(
            "SELECT DATE(data_inicio) AS dia, COUNT(*) AS total
             FROM tb_eventos
             WHERE data_inicio >= :inicio AND data_inicio < :fim
             GROUP BY DATE(data_inicio)",
            [
                ':inicio' => $inicio,
                ':fim' => $fim,
            ]
        );

        $map = [];
        foreach ($rows as $row) {
            $map[(string)$row['dia']] = (int)$row['total'];
        }

        return $map;
    }

    public function formOptions(): array
    {
        return [
            'paciente_id' => $this->activePatients(),
            'cuidador_id' => $this->activeCaregivers(),
            'tipo_evento' => self::tiposEvento(),
            'status' => self::statusOptions(),
            'prioridade' => self::prioridades(),
        ];
    }

    public static function tiposEvento(): array
    {
        return [
            'Visita domiciliar' => 'Visita domiciliar',
            'Entrevista' => 'Entrevista',
            'Avaliação inicial' => 'Avaliação inicial',
            'Supervisão' => 'Supervisão',
            'Reunião' => 'Reunião',
            'Administrativo' => 'Administrativo',
            'Retorno médico' => 'Retorno médico',
            'Outro' => 'Outro',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'Pendente' => 'Pendente',
            'Agendado' => 'Agendado',
            'Em andamento' => 'Em andamento',
            'Concluído' => 'Concluído',
            'Cancelado' => 'Cancelado',
        ];
    }

    public static function prioridades(): array
    {
        return [
            'Baixa' => 'Baixa',
            'Normal' => 'Normal',
            'Alta' => 'Alta',
            'Urgente' => 'Urgente',
        ];
    }

    private function baseSelect(): string
    {
        return "SELECT
            e.*,
            p.nome_completo AS paciente_nome,
            p.uuid AS paciente_uuid,
            c.nome_completo AS cuidador_nome,
            c.uuid AS cuidador_uuid
        FROM tb_eventos e
        LEFT JOIN tb_pacientes p ON p.id = e.paciente_id
        LEFT JOIN tb_cuidador c ON c.id = e.cuidador_id";
    }
}
