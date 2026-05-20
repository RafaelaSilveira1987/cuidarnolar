<?php

namespace App\Models;

abstract class BaseModuleModel extends BaseModel
{
    protected string $searchColumn = 'nome_completo';
    protected string $orderBy = 'id';
    protected string $orderDirection = 'DESC';
    protected array $fillable = [];
    protected array $nullable = [];

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

    public function createRecord(array $data): int
    {
        return $this->insert($this->filterData($data));
    }

    public function updateRecord(int $id, array $data): bool
    {
        return $this->update($id, $this->filterData($data));
    }

    public function inativar(int $id, string $motivo = ''): bool
    {
        $data = ['status' => 'Inativo'];

        if (in_array('motivo_inativacao', $this->fillable, true)) {
            $data['motivo_inativacao'] = $motivo !== '' ? $motivo : 'Inativado pelo painel MVC';
        }

        return $this->update($id, $data);
    }

    public function formOptions(): array
    {
        return [];
    }

    protected function filterData(array $data): array
    {
        $filtered = [];

        foreach ($this->fillable as $field) {
            $value = $data[$field] ?? null;

            if (in_array($field, $this->nullable, true) && ($value === '' || $value === null)) {
                $filtered[$field] = null;
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
                if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $value)) {
                    $value = str_replace('T', ' ', $value);
                }
            }

            $filtered[$field] = $value;
        }

        return $filtered;
    }

    protected function activePatients(): array
    {
        return $this->rawAll("SELECT id, nome_completo FROM tb_pacientes WHERE status = 'Ativo' ORDER BY nome_completo ASC");
    }

    protected function activeCaregivers(): array
    {
        return $this->rawAll("SELECT id, nome_completo FROM tb_cuidador WHERE status IN ('Ativo', 'Standby') ORDER BY nome_completo ASC");
    }

    protected function activeResponsibles(): array
    {
        return $this->rawAll("SELECT id, nome_completo FROM tb_responsavel WHERE status = 'Ativo' ORDER BY nome_completo ASC");
    }
}
