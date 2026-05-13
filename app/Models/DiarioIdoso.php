<?php

namespace App\Models;

class DiarioIdoso extends BaseModuleModel
{
    protected string $table = 'tb_diarioidoso';
    protected string $searchColumn = 'p.nome_completo';
    protected string $orderBy = 'd.visita_mensal';
    protected string $orderDirection = 'DESC';

    public function listForIndex(int $page = 1, int $perPage = 15, string $search = ''): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = $search !== '' ? ' WHERE p.nome_completo LIKE :search_paciente OR d.observacao LIKE :search_observacao' : '';
        $params = $search !== '' ? [
            ':search_paciente' => "%{$search}%",
            ':search_observacao' => "%{$search}%",
        ] : [];
        $total = (int) $this->query('SELECT COUNT(*) FROM tb_diarioidoso d JOIN tb_pacientes p ON p.id = d.paciente_id' . $where, $params)->fetchColumn();
        $data = $this->query($this->baseSelect() . $where . ' ORDER BY d.visita_mensal DESC LIMIT :limit OFFSET :offset', $params + [':limit' => $perPage, ':offset' => $offset])->fetchAll();
        return ['data' => $data, 'total' => $total, 'per_page' => $perPage, 'current_page' => $page, 'last_page' => (int) max(1, ceil($total / $perPage))];
    }

    public function findForShow(int $id): array|false
    {
        return $this->rawFirst($this->baseSelect() . ' WHERE d.id = :id', [':id' => $id]);
    }

    private function baseSelect(): string
    {
        return 'SELECT d.*, p.nome_completo AS paciente_nome FROM tb_diarioidoso d JOIN tb_pacientes p ON p.id = d.paciente_id';
    }
}
