<?php

namespace App\Models;

class MedicacaoPaciente extends BaseModuleModel
{
    protected string $table = 'tb_medicacoes_paciente';
    protected string $searchColumn = 'nome_medicamento';
    protected string $orderBy = 'nome_medicamento';
    protected string $orderDirection = 'ASC';

    protected array $fillable = [
        'paciente_id',
        'nome_medicamento',
        'apresentacao',
        'dosagem',
        'via',
        'horarios',
        'frequencia',
        'data_inicio',
        'data_fim',
        'status',
        'observacoes',
        'created_by',
    ];

    protected array $nullable = [
        'apresentacao',
        'dosagem',
        'via',
        'horarios',
        'frequencia',
        'data_inicio',
        'data_fim',
        'observacoes',
        'created_by',
    ];

    public function listByPacienteId(int $pacienteId): array
    {
        return $this->query("
            SELECT *
            FROM {$this->table}
            WHERE paciente_id = :paciente_id
            ORDER BY
                CASE WHEN status = 'Ativo' THEN 0 ELSE 1 END,
                nome_medicamento ASC
        ", [':paciente_id' => $pacienteId])->fetchAll();
    }

    public function ativosByPacienteId(int $pacienteId): array
    {
        return $this->query("
            SELECT *
            FROM {$this->table}
            WHERE paciente_id = :paciente_id
              AND status = 'Ativo'
            ORDER BY nome_medicamento ASC
        ", [':paciente_id' => $pacienteId])->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $registro = $this->find($id);
        return $registro ?: null;
    }

    public function createMedicacao(array $data): int
    {
        return $this->createRecord($data);
    }

    public function updateMedicacao(int $id, array $data): bool
    {
        return $this->updateRecord($id, $data);
    }

    public function inativarMedicacao(int $id): bool
    {
        return $this->updateMedicacao($id, [
            'status' => 'Inativo'
        ]);
    }

    public function deleteMedicacao(int $id): bool
    {
        return $this->updateMedicacao($id, [
            'status' => 'Inativo'
        ]);
    }

    public function listarPorPacienteId(int $pacienteId): array
    {
        $sql = "
        SELECT
            id,
            uuid,
            paciente_id,
            nome_medicamento,
            apresentacao,
            dosagem,
            via,
            horarios,
            frequencia,
            data_inicio,
            data_fim,
            observacoes,
            status,
            created_at,
            updated_at
        FROM tb_medicacoes_paciente
        WHERE paciente_id = :paciente_id
        ORDER BY
            CASE WHEN status = 'Ativo' THEN 0 ELSE 1 END,
            nome_medicamento ASC
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':paciente_id' => $pacienteId,
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function salvarListaPaciente(int $pacienteId, array $itens): void
    {
        foreach ($itens as $item) {
            $nome = trim((string)($item['nome_medicamento'] ?? ''));
            $id = (int)($item['id'] ?? 0);
            $remover = !empty($item['_delete']);

            if ($id > 0 && ($remover || $nome === '')) {
                $this->deleteMedicacao($id);
                continue;
            }

            if ($nome === '') {
                continue;
            }

            $data = [
                'paciente_id' => $pacienteId,
                'nome_medicamento' => $nome,
                'apresentacao' => $item['apresentacao'] ?? '',
                'dosagem' => $item['dosagem'] ?? '',
                'via' => $item['via'] ?? '',
                'horarios' => $item['horarios'] ?? '',
                'frequencia' => $item['frequencia'] ?? '',
                'data_inicio' => $item['data_inicio'] ?? '',
                'data_fim' => $item['data_fim'] ?? '',
                'status' => $item['status'] ?? 'Ativo',
                'observacoes' => $item['observacoes'] ?? '',
                'created_by' => $_SESSION['user']['id'] ?? null,
            ];

            if ($id > 0) {
                $this->updateMedicacao($id, $data);
                continue;
            }

            $this->createMedicacao($data);
        }
    }

    public function formOptions(): array
    {
        return [
            'vias' => [
                'VO' => 'VO',
                'EV' => 'EV',
                'IM' => 'IM',
                'SC' => 'SC',
                'SL' => 'SL',
                'SNE' => 'SNE',
                'GTT' => 'GTT',
                'Retal' => 'Retal',
                'Tópica' => 'Tópica',
                'Inalatória' => 'Inalatória',
                'Gastrostomia' => 'Gastrostomia',
            ],
            'status' => [
                'Ativo' => 'Ativo',
                'Inativo' => 'Inativo',
            ],
        ];
    }
}