<?php

namespace App\Models;

class EmpresaConfig extends BaseModel
{
    protected string $table = 'tb_empresa_config';

    public function atual(): array
    {
        try {
            $row = $this->rawFirst("SELECT * FROM {$this->table} ORDER BY id ASC LIMIT 1");
            if ($row) {
                return $row;
            }
        } catch (\Throwable) {
            return $this->padrao();
        }

        return $this->padrao();
    }

    public function salvar(array $data): bool
    {
        $payload = [
            'razao_social' => $this->texto($data['razao_social'] ?? 'Cuidar no Lar'),
            'nome_fantasia' => $this->texto($data['nome_fantasia'] ?? 'Cuidar no Lar'),
            'cnpj' => $this->texto($data['cnpj'] ?? ''),
            'inscricao_estadual' => $this->texto($data['inscricao_estadual'] ?? ''),
            'endereco' => $this->texto($data['endereco'] ?? ''),
            'cidade' => $this->texto($data['cidade'] ?? ''),
            'estado' => $this->texto($data['estado'] ?? ''),
            'cep' => $this->texto($data['cep'] ?? ''),
            'telefone' => $this->texto($data['telefone'] ?? ''),
            'email' => $this->texto($data['email'] ?? ''),
            'responsavel_contrato' => $this->texto($data['responsavel_contrato'] ?? ''),
            'observacoes_contrato' => $this->texto($data['observacoes_contrato'] ?? ''),
        ];

        $id = (int)($this->rawFirst("SELECT id FROM {$this->table} ORDER BY id ASC LIMIT 1")['id'] ?? 0);
        if ($id > 0) {
            return $this->update($id, $payload);
        }

        $this->insert($payload);
        return true;
    }

    public function contratoSnapshot(): array
    {
        $empresa = $this->atual();
        $endereco = trim((string)($empresa['endereco'] ?? ''));
        $cidade = trim((string)($empresa['cidade'] ?? ''));
        $estado = trim((string)($empresa['estado'] ?? ''));
        $cep = trim((string)($empresa['cep'] ?? ''));

        $partes = array_filter([
            $endereco,
            trim($cidade . ($estado !== '' ? '/' . $estado : '')),
            $cep !== '' ? 'CEP ' . $cep : '',
        ]);

        return [
            'empresa_razao_social' => (string)($empresa['razao_social'] ?? 'Cuidar no Lar'),
            'empresa_cnpj' => (string)($empresa['cnpj'] ?? ''),
            'empresa_endereco' => implode(' - ', $partes),
            'empresa_responsavel_contrato' => (string)($empresa['responsavel_contrato'] ?? ''),
        ];
    }

    private function padrao(): array
    {
        return [
            'id' => 0,
            'razao_social' => defined('APP_COMPANY_NAME') ? (string)APP_COMPANY_NAME : 'Cuidar no Lar',
            'nome_fantasia' => 'Cuidar no Lar',
            'cnpj' => '',
            'inscricao_estadual' => '',
            'endereco' => '',
            'cidade' => '',
            'estado' => '',
            'cep' => '',
            'telefone' => '',
            'email' => '',
            'responsavel_contrato' => '',
            'observacoes_contrato' => '',
        ];
    }

    private function texto(mixed $value): ?string
    {
        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }
}
