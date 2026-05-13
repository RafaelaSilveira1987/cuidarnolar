<?php

namespace App\Models;

class Cuidador extends BaseModuleModel
{
    protected string $table = 'tb_cuidador';
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
        'rg',
        'data_nascimento',
        'telefone',
        'pix',
        'especialidade',
        'status',
        'contrato_horas',
        'motivo_inativacao',
    ];
    protected array $nullable = [
        'numero',
        'bairro',
        'email',
        'rg',
        'data_nascimento',
        'telefone',
        'pix',
        'especialidade',
        'contrato_horas',
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

    private function buildEndereco(array $row): array
    {
        $row['endereco_completo'] = trim(($row['endereco'] ?? '') . ', ' . ($row['numero'] ?? '') . ' - ' . ($row['bairro'] ?? '') . ', ' . ($row['cidade'] ?? '') . '/' . ($row['estado'] ?? ''));
        return $row;
    }
}
