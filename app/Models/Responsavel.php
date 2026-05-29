<?php

namespace App\Models;

class Responsavel extends BaseModuleModel
{
    protected string $table = 'tb_responsavel';
    protected string $searchColumn = 'nome_completo';
    protected string $orderBy = 'nome_completo';
    protected string $orderDirection = 'ASC';

    protected array $fillable = [
        'nome_completo',
        'endereco',
        'numero',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'cpf',
        'email',
        'data_nascimento',
        'telefone',
        'grau_parentesco',
        'status',
        'motivo_inativacao',
    ];

    protected array $nullable = [
        'numero',
        'bairro',
        'cep',
        'email',
        'data_nascimento',
        'telefone',
        'grau_parentesco',
        'motivo_inativacao',
    ];

    public function listForIndex(int $page = 1, int $perPage = 15, string $search = ''): array
    {
        $result = parent::listForIndex($page, $perPage, $search);
        $result['data'] = array_map(fn (array $row): array => $this->buildEndereco($row), $result['data']);

        return $result;
    }

    public function findForShow(int $id): array|false
    {
        $record = parent::findForShow($id);

        return $record ? $this->buildEndereco($record) : false;
    }

    public function findForShowByUuid(string $uuid): array|false
    {
        $record = $this->rawFirst(
            "SELECT * FROM {$this->table} WHERE uuid = :uuid LIMIT 1",
            [':uuid' => $uuid]
        );

        return $record ? $this->buildEndereco($record) : false;
    }

    public function updateRecordByUuid(string $uuid, array $data): bool
    {
        $record = $this->findForShowByUuid($uuid);

        if (!$record) {
            return false;
        }

        return $this->updateRecord((int)$record['id'], $data);
    }

    public function pacientesVinculados(int $responsavelId): array
    {
        return $this->rawAll(
            "SELECT id, uuid, prontuario, nome_completo, status
             FROM tb_pacientes
             WHERE responsavel_id = :responsavel_id
             ORDER BY nome_completo ASC",
            [':responsavel_id' => $responsavelId]
        );
    }

    public function listByPacienteId(int $pacienteId): array
    {
        $responsaveis = $this->rawAll(
            "SELECT
                r.id,
                r.uuid,
                r.nome_completo,
                r.endereco,
                r.numero,
                r.bairro,
                r.cidade,
                r.estado,
                r.cep,
                r.cpf,
                r.email,
                r.data_nascimento,
                r.telefone,
                r.grau_parentesco,
                r.status,
                r.motivo_inativacao
            FROM tb_responsavel r
            INNER JOIN tb_pacientes p
                ON p.responsavel_id = r.id
            WHERE p.id = :paciente_id
            ORDER BY r.nome_completo ASC",
            [':paciente_id' => $pacienteId]
        );

        return array_map(function (array $row): array {
            $row = $this->buildEndereco($row);
            $row['origem'] = 'tb_responsavel';

            return $row;
        }, $responsaveis);
    }

    private function buildEndereco(array $row): array
    {
        $part1 = trim(($row['endereco'] ?? '') . ' ' . ($row['numero'] ?? ''));
        $part2 = trim((string)($row['bairro'] ?? ''));
        $part3 = trim((string)($row['cidade'] ?? ''));
        $uf = trim((string)($row['estado'] ?? ''));

        $cidadeUf = $part3 !== '' || $uf !== '' ? trim($part3 . '/' . $uf, '/') : '';
        $row['endereco_completo'] = implode(' - ', array_filter([$part1, $part2, $cidadeUf]));

        return $row;
    }
}