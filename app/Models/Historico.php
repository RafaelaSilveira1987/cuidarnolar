<?php

namespace App\Models;

class Historico extends BaseModuleModel
{
    protected string $table = 'tb_historico';
    protected string $searchColumn = 'p.nome_completo';
    protected string $orderBy = 'h.id';
    protected string $orderDirection = 'DESC';

    public function listForIndex(int $page = 1, int $perPage = 15, string $search = ''): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = $search !== '' ? ' WHERE p.nome_completo LIKE :search_paciente OR h.necessidades LIKE :search_necessidades OR h.limitacoes LIKE :search_limitacoes' : '';
        $params = $search !== '' ? [
            ':search_paciente' => "%{$search}%",
            ':search_necessidades' => "%{$search}%",
            ':search_limitacoes' => "%{$search}%",
        ] : [];
        $total = (int) $this->query('SELECT COUNT(*) FROM tb_historico h JOIN tb_pacientes p ON p.id = h.paciente_id' . $where, $params)->fetchColumn();
        $data = $this->query($this->baseSelect() . $where . ' ORDER BY h.id DESC LIMIT :limit OFFSET :offset', $params + [':limit' => $perPage, ':offset' => $offset])->fetchAll();
        return ['data' => $data, 'total' => $total, 'per_page' => $perPage, 'current_page' => $page, 'last_page' => (int) max(1, ceil($total / $perPage))];
    }

    public function findForShow(int $id): array|false
    {
        return $this->rawFirst($this->baseSelect() . ' WHERE h.id = :id', [':id' => $id]);
    }

    private function baseSelect(): string
    {
        return 'SELECT h.*, p.nome_completo AS paciente_nome FROM tb_historico h JOIN tb_pacientes p ON p.id = h.paciente_id';
    }
}
