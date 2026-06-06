<?php

namespace App\Models;

class Anamnese extends BaseModuleModel
{
    protected string $table = 'tb_anamnese';
    protected string $searchColumn = 'p.nome_completo';
    protected string $orderBy = 'a.data_anamnese';
    protected string $orderDirection = 'DESC';
    protected array $fillable = [
        'paciente_id',
        'data_anamnese',
        'patologia',
        'sintomas',
        'sequelas',
        'historia_medica',
        'cirurgia',
        'protese',
        'acamado',
        'hipertensao',
        'diabetes',
        'alergia',
        'estilo_de_vida',
        'dieta',
        'medicacao_continua',
        'sono',
        'visao',
        'audicao',
        'incontinencia',
        'demencia',
        'cognicao',
        'coordenacao_motora',
        'humor',
        'problemas_locomocao',
        'medico',
        'status',
    ];
    protected array $nullable = [
        'patologia',
        'sintomas',
        'sequelas',
        'historia_medica',
        'cirurgia',
        'protese',
        'acamado',
        'hipertensao',
        'diabetes',
        'alergia',
        'estilo_de_vida',
        'dieta',
        'medicacao_continua',
        'sono',
        'visao',
        'audicao',
        'incontinencia',
        'demencia',
        'cognicao',
        'coordenacao_motora',
        'humor',
        'problemas_locomocao',
        'medico',
        'status',
    ];

    public function listForIndex(int $page = 1, int $perPage = 15, string $search = ''): array
    {
        return $this->listWithPatient($page, $perPage, $search);
    }

    public function findForShow(int $id): array|false
    {
        return $this->rawFirst($this->baseSelect() . ' WHERE a.id = :id', [':id' => $id]);
    }

    public function findForShowByUuid(string $uuid): array|false
    {
        return $this->rawFirst($this->baseSelect() . ' WHERE a.uuid = :uuid LIMIT 1', [':uuid' => $uuid]);
    }

    /** Listagem para aba na ficha do paciente */
    public function listByPacienteId(int $pacienteId, int $limit = 50): array
    {
        $limit = max(1, min(100, $limit));

        return $this->rawAll(
            $this->baseSelect() . ' WHERE a.paciente_id = :pid ORDER BY a.data_anamnese DESC LIMIT ' . $limit,
            [':pid' => $pacienteId]
        );
    }

    public function formOptions(): array
    {
        return ['paciente_id' => $this->activePatients()];
    }

    private function listWithPatient(int $page, int $perPage, string $search): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = $search !== '' ? ' WHERE p.nome_completo LIKE :search_paciente OR a.patologia LIKE :search_patologia' : '';
        $params = $search !== '' ? [
            ':search_paciente' => "%{$search}%",
            ':search_patologia' => "%{$search}%",
        ] : [];
        $total = (int) $this->query('SELECT COUNT(*) FROM tb_anamnese a JOIN tb_pacientes p ON p.id = a.paciente_id' . $where, $params)->fetchColumn();
        $data = $this->query($this->baseSelect() . $where . ' ORDER BY a.data_anamnese DESC LIMIT :limit OFFSET :offset', $params + [':limit' => $perPage, ':offset' => $offset])->fetchAll();
        return ['data' => $data, 'total' => $total, 'per_page' => $perPage, 'current_page' => $page, 'last_page' => (int) max(1, ceil($total / $perPage))];
    }

    private function baseSelect(): string
    {
        return 'SELECT a.*, p.nome_completo AS paciente_nome FROM tb_anamnese a JOIN tb_pacientes p ON p.id = a.paciente_id';
    }
}
