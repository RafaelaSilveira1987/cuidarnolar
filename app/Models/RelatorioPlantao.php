<?php

namespace App\Models;

use PDO;
use Throwable;

class RelatorioPlantao extends BaseModel
{
    protected string $table = 'tb_relatorio_plantao';

    public function buscarPorPaciente(int $pacienteId): array
    {
        $sql = "
        SELECT
            rp.*,
            sv.pa,
            sv.fc,
            sv.temperatura,
            sv.spo2,
            sv.hgt

        FROM tb_relatorio_plantao rp

        LEFT JOIN tb_sinais_vitais sv
            ON sv.relatorio_id = rp.id

        WHERE rp.paciente_id = :paciente_id

        ORDER BY rp.data_inicio DESC
    ";

        return $this->query($sql, [
            ':paciente_id' => $pacienteId
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function criarCompleto(array $dados): int
    {
        $this->db->beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | RELATÓRIO
            |--------------------------------------------------------------------------
            */

            $stmt = $this->db->prepare("
                INSERT INTO tb_relatorio_plantao (
                    paciente_id,
                    cuidador_id,
                    data_inicio,
                    data_fim,
                    evolucao,
                    status,
                    assinado
                ) VALUES (
                    :paciente_id,
                    :cuidador_id,
                    :data_inicio,
                    :data_fim,
                    :evolucao,
                    :status,
                    :assinado
                )
            ");

            $stmt->execute([
                ':paciente_id' => $dados['paciente_id'],
                ':cuidador_id' => $dados['cuidador_id'],
                ':data_inicio' => $dados['data_inicio'],
                ':data_fim' => $dados['data_fim'],
                ':evolucao' => $dados['evolucao'],
                ':status' => $dados['status'],
                ':assinado' => $dados['assinado'],
            ]);

            $relatorioId = (int)$this->db->lastInsertId();

            /*
            |--------------------------------------------------------------------------
            | SINAIS VITAIS
            |--------------------------------------------------------------------------
            */

            $sv = $this->db->prepare("
                INSERT INTO tb_sinais_vitais (
                    relatorio_id,
                    pa,
                    fc,
                    temperatura,
                    spo2,
                    hgt,
                    observacao
                ) VALUES (
                    :relatorio_id,
                    :pa,
                    :fc,
                    :temperatura,
                    :spo2,
                    :hgt,
                    :observacao
                )
            ");

            $sv->execute([
                ':relatorio_id' => $relatorioId,
                ':pa' => $dados['pa'] ?? null,
                ':fc' => $dados['fc'] ?? null,
                ':temperatura' => $dados['temperatura'] ?? null,
                ':spo2' => $dados['spo2'] ?? null,
                ':hgt' => $dados['hgt'] ?? null,
                ':observacao' => $dados['observacao_sv'] ?? null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | INTERCORRÊNCIAS
            |--------------------------------------------------------------------------
            */

            if (!empty($dados['intercorrencias'])) {

                $evento = $this->db->prepare("
                    INSERT INTO tb_relatorio_plantao_eventos (
                        relatorio_id,
                        hora_evento,
                        tipo,
                        titulo,
                        descricao,
                        intercorrencia
                    ) VALUES (
                        :relatorio_id,
                        :hora_evento,
                        :tipo,
                        :titulo,
                        :descricao,
                        1
                    )
                ");

                foreach ($dados['intercorrencias'] as $item) {

                    if (empty($item)) {
                        continue;
                    }

                    $evento->execute([
                        ':relatorio_id' => $relatorioId,
                        ':hora_evento' => date('H:i:s'),
                        ':tipo' => 'intercorrencia',
                        ':titulo' => 'Intercorrência',
                        ':descricao' => is_array($item)
                            ? ($item['descricao'] ?? '')
                            : $item,
                    ]);
                }
            }

            $this->db->commit();

            return $relatorioId;
        } catch (Throwable $e) {

            $this->db->rollBack();

            throw $e;
        }
    }

    public function criar(array $dados): bool
    {
        $sql = "
        INSERT INTO tb_relatorio_plantao (

            paciente_id,
            cuidador_id,

            data_inicio,
            data_fim,

            evolucao,
            alimentacao,
            eliminacoes,
            medicacoes,
            intercorrencias,

            status,

            observacoes_gerais,

            pa,
            fc,
            temperatura,
            spo2,
            hgt,

            consciencia,
            nivel_dor,
            hidratacao_ml,
            higiene,
            sono,
            decubito,

            created_at,
            updated_at

        ) VALUES (

            :paciente_id,
            :cuidador_id,

            :data_inicio,
            :data_fim,

            :evolucao,
            :alimentacao,
            :eliminacoes,
            :medicacoes,
            :intercorrencias,

            :status,

            :observacoes_gerais,

            :pa,
            :fc,
            :temperatura,
            :spo2,
            :hgt,

            :consciencia,
            :nivel_dor,
            :hidratacao_ml,
            :higiene,
            :sono,
            :decubito,

            NOW(),
            NOW()
        )
    ";

        return $this->query($sql, $dados) !== false;
    }
}