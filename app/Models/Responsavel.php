<?php

namespace App\Models;

class Responsavel extends BaseModuleModel
{
    protected string $table = 'tb_responsavel';
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
        return $this->withEndereco($result);
    }

    public function findForShow(int $id): array|false
    {
        $record = parent::findForShow($id);
        return $record ? $this->buildEndereco($record) : false;
    }

    private function withEndereco(array $result): array
    {
        $result['data'] = array_map(fn (array $row): array => $this->buildEndereco($row), $result['data']);
        return $result;
    }

    private function buildEndereco(array $row): array
    {
        $row['endereco_completo'] = trim(($row['endereco'] ?? '') . ', ' . ($row['numero'] ?? '') . ' - ' . ($row['bairro'] ?? '') . ', ' . ($row['cidade'] ?? '') . '/' . ($row['estado'] ?? ''));
        return $row;
    }
}
