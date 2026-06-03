<?php

namespace App\Models;

class TabelaPlantao extends BaseModel
{
    protected string $table = 'tb_tabela_plantoes';

    public function listar(bool $somenteAtivos = false): array
    {
        try {
            $where = $somenteAtivos ? 'WHERE ativo = 1' : '';
            return $this->rawAll("SELECT * FROM {$this->table} {$where} ORDER BY ativo DESC, ordem ASC, titulo ASC");
        } catch (\Throwable) {
            return [];
        }
    }

    public function buscar(int $id): array|false
    {
        return $this->find($id);
    }

    public function salvar(array $data, ?int $id = null): int
    {
        $payload = [
            'titulo' => $this->textoObrigatorio($data['titulo'] ?? '', 'Plantão'),
            'tipo_plantao' => $this->tipoPlantao($data['tipo_plantao'] ?? '12h'),
            'periodo' => $this->periodo($data['periodo'] ?? 'Diurno'),
            'hora_inicio' => $this->horaOuNull($data['hora_inicio'] ?? null),
            'hora_fim' => $this->horaOuNull($data['hora_fim'] ?? null),
            'valor_cuidador' => $this->dinheiro($data['valor_cuidador'] ?? 0),
            'valor_extra' => $this->dinheiro($data['valor_extra'] ?? 0),
            'descricao' => $this->texto($data['descricao'] ?? null),
            'ativo' => !empty($data['ativo']) ? 1 : 0,
            'ordem' => max(0, (int)($data['ordem'] ?? 0)),
        ];

        if ($id !== null && $id > 0) {
            $this->update($id, $payload);
            return $id;
        }

        return (int)$this->insert($payload);
    }

    public function alternarAtivo(int $id): bool
    {
        $row = $this->find($id);
        if (!$row) {
            return false;
        }

        return $this->update($id, ['ativo' => empty($row['ativo']) ? 1 : 0]);
    }

    public function sugestaoPorTipo(?string $tipoPlantao, ?string $periodo = null): array|false
    {
        $tipoPlantao = $this->tipoPlantao($tipoPlantao ?: '12h');
        $periodo = $periodo ? $this->periodo($periodo) : '';

        $params = [':tipo' => $tipoPlantao];
        $wherePeriodo = '';
        if ($periodo !== '') {
            $wherePeriodo = ' AND periodo = :periodo';
            $params[':periodo'] = $periodo;
        }

        return $this->rawFirst(
            "SELECT * FROM {$this->table}
              WHERE ativo = 1 AND tipo_plantao = :tipo {$wherePeriodo}
              ORDER BY ordem ASC, id ASC
              LIMIT 1",
            $params
        );
    }

    public function validar(array $data): array
    {
        $errors = [];
        if (trim((string)($data['titulo'] ?? '')) === '') {
            $errors['titulo'] = 'Informe o nome da regra de plantão.';
        }

        if ($this->dinheiro($data['valor_cuidador'] ?? 0) <= 0) {
            $errors['valor_cuidador'] = 'Informe um valor para pagamento do cuidador maior que zero.';
        }

        return $errors;
    }

    private function textoObrigatorio(mixed $value, string $fallback): string
    {
        $value = trim((string)$value);
        return $value === '' ? $fallback : $value;
    }

    private function texto(mixed $value): ?string
    {
        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }

    private function horaOuNull(mixed $value): ?string
    {
        $value = trim((string)$value);
        return preg_match('/^\d{2}:\d{2}$/', $value) ? $value . ':00' : null;
    }

    private function tipoPlantao(mixed $value): string
    {
        $value = trim((string)$value);
        return in_array($value, ['6h', '8h', '12h', '24h', 'Personalizado'], true) ? $value : '12h';
    }

    private function periodo(mixed $value): string
    {
        $value = trim((string)$value);
        return in_array($value, ['Diurno', 'Noturno', '24h', 'Personalizado'], true) ? $value : 'Diurno';
    }

    private function dinheiro(mixed $valor): float
    {
        if (is_int($valor) || is_float($valor)) {
            return (float)$valor;
        }

        $valor = trim((string)$valor);
        if ($valor === '') {
            return 0.0;
        }

        $valor = str_replace(['R$', ' ', "\xc2\xa0"], '', $valor);
        if (str_contains($valor, ',') && str_contains($valor, '.')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } elseif (str_contains($valor, ',')) {
            $valor = str_replace(',', '.', $valor);
        }

        return is_numeric($valor) ? (float)$valor : 0.0;
    }
}
