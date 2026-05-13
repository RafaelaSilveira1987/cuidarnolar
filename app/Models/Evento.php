<?php

namespace App\Models;

class Evento extends BaseModuleModel
{
    protected string $table = 'tb_eventos';
    protected string $searchColumn = 'e.titulo';
    protected string $orderBy = 'e.data_evento';
    protected string $orderDirection = 'DESC';
    protected array $fillable = ['paciente_id', 'titulo', 'descricao', 'data_evento', 'cuidador_id', 'status'];
    protected array $nullable = ['descricao', 'cuidador_id', 'status'];

    public function listForIndex(int $page = 1, int $perPage = 15, string $search = ''): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = $search !== '' ? ' WHERE e.titulo LIKE :search_titulo OR p.nome_completo LIKE :search_paciente OR c.nome_completo LIKE :search_cuidador' : '';
        $params = $search !== '' ? [
            ':search_titulo' => "%{$search}%",
            ':search_paciente' => "%{$search}%",
            ':search_cuidador' => "%{$search}%",
        ] : [];
        $countSql = 'SELECT COUNT(*) FROM tb_eventos e LEFT JOIN tb_pacientes p ON p.id = e.paciente_id LEFT JOIN tb_cuidador c ON c.id = e.cuidador_id';
        $total = (int) $this->query($countSql . $where, $params)->fetchColumn();
        $data = $this->query($this->baseSelect() . $where . ' ORDER BY e.data_evento DESC LIMIT :limit OFFSET :offset', $params + [':limit' => $perPage, ':offset' => $offset])->fetchAll();
        return ['data' => $data, 'total' => $total, 'per_page' => $perPage, 'current_page' => $page, 'last_page' => (int) max(1, ceil($total / $perPage))];
    }

    public function findForShow(int $id): array|false
    {
        return $this->rawFirst($this->baseSelect() . ' WHERE e.id = :id', [':id' => $id]);
    }

    public function proximos(int $limit = 5): array
    {
        return $this->rawAll($this->baseSelect() . " WHERE e.data_evento >= NOW() ORDER BY e.data_evento ASC LIMIT {$limit}");
    }

    public function formOptions(): array
    {
        return [
            'paciente_id' => $this->activePatients(),
            'cuidador_id' => $this->activeCaregivers(),
        ];
    }

    private function baseSelect(): string
    {
        return 'SELECT e.*, p.nome_completo AS paciente_nome, c.nome_completo AS cuidador_nome FROM tb_eventos e LEFT JOIN tb_pacientes p ON p.id = e.paciente_id LEFT JOIN tb_cuidador c ON c.id = e.cuidador_id';
    }
}
