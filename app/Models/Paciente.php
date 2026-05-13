<?php

namespace App\Models;

class Paciente extends BaseModuleModel
{
    protected string $table = 'tb_pacientes';
    protected string $searchColumn = 'p.nome_completo';
    protected string $orderBy = 'p.nome_completo';
    protected string $orderDirection = 'ASC';

    public function listForIndex(int $page = 1, int $perPage = 15, string $search = ''): array
    {
        return $this->listWithJoins($page, $perPage, $search);
    }

    public function findForShow(int $id): array|false
    {
        return $this->rawFirst($this->baseSelect() . ' WHERE p.id = :id', [':id' => $id]);
    }

    private function listWithJoins(int $page, int $perPage, string $search): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = $search !== '' ? ' WHERE p.nome_completo LIKE :search_nome OR p.cpf LIKE :search_cpf' : '';
        $params = $search !== '' ? [':search_nome' => "%{$search}%", ':search_cpf' => "%{$search}%"] : [];
        $total = (int) $this->query(
            'SELECT COUNT(*) FROM tb_pacientes p LEFT JOIN tb_responsavel r ON r.id = p.responsavel_id LEFT JOIN tb_cuidador c ON c.id = p.cuidador_id' . $where,
            $params
        )->fetchColumn();

        $data = $this->query(
            $this->baseSelect() . $where . ' ORDER BY p.nome_completo ASC LIMIT :limit OFFSET :offset',
            $params + [':limit' => $perPage, ':offset' => $offset]
        )->fetchAll();

        return ['data' => $data, 'total' => $total, 'per_page' => $perPage, 'current_page' => $page, 'last_page' => (int) max(1, ceil($total / $perPage))];
    }

    public function responsaveisOptions(): array
    {
        return $this->rawAll("SELECT id, nome_completo FROM tb_responsavel WHERE status = 'Ativo' ORDER BY nome_completo ASC");
    }

    public function cuidadoresOptions(): array
    {
        return $this->rawAll("SELECT id, nome_completo FROM tb_cuidador WHERE status IN ('Ativo', 'Standby') ORDER BY nome_completo ASC");
    }

    public function createPaciente(array $data): int
    {
        return $this->insert($this->normalizeData($data));
    }

    public function updatePaciente(int $id, array $data): bool
    {
        return $this->update($id, $this->normalizeData($data));
    }

    public function inativar(int $id, string $motivo = ''): bool
    {
        return $this->update($id, [
            'status' => 'Inativo',
            'motivo_inativacao' => $motivo !== '' ? $motivo : 'Inativado pelo painel MVC',
        ]);
    }

    private function normalizeData(array $data): array
    {
        return [
            'nome_completo' => trim((string) ($data['nome_completo'] ?? '')),
            'data_nascimento' => $this->nullableDate($data['data_nascimento'] ?? null),
            'cpf' => $this->nullableString($data['cpf'] ?? null),
            'rg' => $this->nullableString($data['rg'] ?? null),
            'cartao_nac_sus' => $this->nullableString($data['cartao_nac_sus'] ?? null),
            'plano_saude' => $this->nullableString($data['plano_saude'] ?? null),
            'responsavel_id' => $this->nullableInt($data['responsavel_id'] ?? null),
            'cuidador_id' => $this->nullableInt($data['cuidador_id'] ?? null),
            'anamnese_id' => $this->nullableInt($data['anamnese_id'] ?? null),
            'status' => in_array(($data['status'] ?? 'Ativo'), ['Ativo', 'Inativo'], true) ? $data['status'] : 'Ativo',
            'motivo_inativacao' => $this->nullableString($data['motivo_inativacao'] ?? null),
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function nullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }

    private function nullableDate(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function baseSelect(): string
    {
        return "SELECT p.*, r.nome_completo AS responsavel_nome, c.nome_completo AS cuidador_nome
                FROM tb_pacientes p
                LEFT JOIN tb_responsavel r ON r.id = p.responsavel_id
                LEFT JOIN tb_cuidador c ON c.id = p.cuidador_id";
    }
}
