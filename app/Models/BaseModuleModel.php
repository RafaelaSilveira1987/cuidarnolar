<?php

namespace App\Models;

abstract class BaseModuleModel extends BaseModel
{
    protected string $searchColumn = 'nome_completo';
    protected string $orderBy = 'id';
    protected string $orderDirection = 'DESC';

    public function listForIndex(int $page = 1, int $perPage = 15, string $search = ''): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = '';
        $params = [];

        if ($search !== '') {
            $where = " WHERE {$this->searchColumn} LIKE :search";
            $params[':search'] = "%{$search}%";
        }

        $total = (int) $this->query("SELECT COUNT(*) FROM {$this->table}{$where}", $params)->fetchColumn();
        $stmt = $this->query(
            "SELECT * FROM {$this->table}{$where} ORDER BY {$this->orderBy} {$this->orderDirection} LIMIT :limit OFFSET :offset",
            $params + [':limit' => $perPage, ':offset' => $offset]
        );

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    public function findForShow(int $id): array|false
    {
        return $this->find($id);
    }
}
