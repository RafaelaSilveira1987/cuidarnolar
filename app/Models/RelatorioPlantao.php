<?php

namespace App\Models;

use PDO;
use Throwable;

class RelatorioPlantao extends BaseModel
{
    protected string $table = 'tb_relatorio_plantao';

    /**
     * Campos permitidos para insert/update genéricos do BaseModel.
     * Mantido para compatibilidade com o restante do sistema.
     */
    protected array $fillable = [
        'uuid',
        'paciente_id',
        'cuidador_id',
        'data_inicio',
        'data_fim',
        'data_nascimento',
        'internacao',
        'tipo_local',
        'quarto',
        'nome_acompanhante',
        'evolucao',
        'assinado',
        'estado_paciente',
        'alimentacao',
        'eliminacoes',
        'medicacoes',
        'intercorrencias',
        'status',
        'observacoes_gerais',
        'consciencia',
        'nivel_dor',
        'hidratacao_ml',
        'hidratacao_registros',
        'higiene',
        'sono',
        'decubito',
        'estado_geral',
        'queixas_referidas',
        'exame_fisico',
        'pele_mucosas',
        'visita_medica',
        'entrada_saida_profissionais',
        'entrada_saida_familiares',
        'plantao_entregue_para',
        'peso',
        'pa',
        'fc',
        'temperatura',
        'spo2',
        'frequencia_respiratoria',
        'hgt',
        'urina_horarios',
        'fezes_horarios',
        'created_at',
        'updated_at',
    ];

    public function buscarPorPaciente(int $pacienteId): array
    {
        $sql = "
            SELECT
                rp.*,
                COALESCE(rp.pa, sv.pa) AS pa,
                COALESCE(rp.fc, sv.fc) AS fc,
                COALESCE(rp.temperatura, sv.temperatura) AS temperatura,
                COALESCE(rp.spo2, sv.spo2) AS spo2,
                COALESCE(rp.frequencia_respiratoria, sv.frequencia_respiratoria) AS frequencia_respiratoria,
                COALESCE(rp.hgt, sv.hgt) AS hgt,
                COALESCE(sv.observacao, '') AS observacao_sv
            FROM tb_relatorio_plantao rp
            LEFT JOIN tb_sinais_vitais sv
                ON sv.relatorio_id = rp.id
            WHERE rp.paciente_id = :paciente_id
            ORDER BY rp.data_inicio DESC, rp.id DESC
        ";

        return $this->query($sql, [
            ':paciente_id' => $pacienteId,
        ])->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorUuid(string $uuid): ?array
    {
        $sql = "
        SELECT
            rp.*,

            p.nome_completo AS paciente_nome,
            r.nome_completo AS responsavel_nome,
            r.telefone AS responsavel_telefone,

            a.acamado,
            a.diabetes,
            p.estado_cognitivo_base,
            p.prescricao_medica,
            p.usa_oxigenio,
            p.usa_sonda,
            a.usa_gastrostomia,
            a.usa_traqueostomia,
            a.usa_colostomia,
            a.usa_cateter_vesical,
            p.sne,
            p.picc,
            p.gtt,

            COALESCE(rp.pa, sv.pa) AS pa,
            COALESCE(rp.fc, sv.fc) AS fc,
            COALESCE(rp.temperatura, sv.temperatura) AS temperatura,
            COALESCE(rp.spo2, sv.spo2) AS spo2,
            COALESCE(rp.frequencia_respiratoria, sv.frequencia_respiratoria) AS frequencia_respiratoria,
            COALESCE(rp.hgt, sv.hgt) AS hgt,
            sv.observacao AS observacao_sv

        FROM tb_relatorio_plantao rp

        LEFT JOIN tb_pacientes p
            ON p.id = rp.paciente_id

        LEFT JOIN tb_responsavel r
            ON r.id = p.responsavel_id

        LEFT JOIN tb_anamnese a
            ON a.paciente_id = p.id

        LEFT JOIN tb_sinais_vitais sv
            ON sv.relatorio_id = rp.id

        WHERE rp.uuid = ?
        LIMIT 1
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$uuid]);

        $relatorio = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if (!$relatorio) {
            return null;
        }

        return $this->normalizarRelatorioParaShow($relatorio);
    }

    private function normalizarRelatorioParaShow(array $relatorio): array
    {
        $defaults = [
            'frequencia_respiratoria'     => null,
            'estado_geral'                => null,
            'queixas_referidas'           => null,
            'exame_fisico'                => null,
            'pele_mucosas'                => null,
            'visita_medica'               => null,
            'entrada_saida_profissionais' => null,
            'entrada_saida_familiares'    => null,
            'plantao_entregue_para'       => null,
            'peso'                        => null,
            'observacoes_gerais'          => null,
            'medicacoes'                  => null,
        ];

        $relatorio = array_merge($defaults, $relatorio);

        if (empty($relatorio['medicacoes']) && !empty($relatorio['id'])) {
            $medicacoesPlantao = $this->buscarMedicacoesPlantao((int)$relatorio['id']);
            if (!empty($medicacoesPlantao)) {
                $relatorio['medicacoes'] = json_encode($medicacoesPlantao, JSON_UNESCAPED_UNICODE);
            }
        }

        if (empty($relatorio['observacoes_gerais']) && !empty($relatorio['observacao_sv'])) {
            $relatorio['observacoes_gerais'] = (string)$relatorio['observacao_sv'];
        }

        return $relatorio;
    }

    private function buscarMedicacoesPlantao(int $plantaoId): array
    {
        $sql = "
            SELECT medicamento, via, horario, status, observacao
            FROM tb_medicacoes_plantao
            WHERE plantao_id = :plantao_id
            ORDER BY id ASC
        ";

        return $this->query($sql, [':plantao_id' => $plantaoId])->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorIdCompleto(int $id): array|false
    {
        $sql = "
            SELECT
                rp.*,
                COALESCE(rp.pa, sv.pa) AS pa,
                COALESCE(rp.fc, sv.fc) AS fc,
                COALESCE(rp.temperatura, sv.temperatura) AS temperatura,
                COALESCE(rp.spo2, sv.spo2) AS spo2,
                COALESCE(rp.frequencia_respiratoria, sv.frequencia_respiratoria) AS frequencia_respiratoria,
                COALESCE(rp.hgt, sv.hgt) AS hgt,
                COALESCE(sv.observacao, '') AS observacao_sv
            FROM tb_relatorio_plantao rp
            LEFT JOIN tb_sinais_vitais sv
                ON sv.relatorio_id = rp.id
            WHERE rp.id = :id
            LIMIT 1
        ";

        return $this->query($sql, [
            ':id' => $id,
        ])->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarContextoPaciente(int $pacienteId): array
    {
        // Medicamentos ativos com horários
        $medicamentos = $this->query("
        SELECT id, uuid, nome_medicamento, dosagem, via, horarios, frequencia
        FROM tb_medicacoes_paciente
        WHERE paciente_id = :pid AND status = 'Ativo'
        ORDER BY nome_medicamento ASC
    ", [':pid' => $pacienteId])->fetchAll(PDO::FETCH_ASSOC);

        // Dispositivos ativos
        $dispositivos = $this->query("
        SELECT id, uuid, tipo, descricao, protocolo_cuidado
        FROM tb_dispositivos_paciente
        WHERE paciente_id = :pid AND status = 'Ativo'
        ORDER BY tipo ASC
    ", [':pid' => $pacienteId])->fetchAll(PDO::FETCH_ASSOC);

        // Anamnese (diagnóstico, CID, dispositivos legado)
        $anamnese = $this->query("
        SELECT diagnostico_principal, cid_principal, motivo_homecare,
               observacoes_clinicas, acamado, diabetes, hipertensao,
               usa_sonda, usa_gastrostomia, usa_traqueostomia,
               usa_oxigenio, fluxo_oxigenio, usa_colostomia,
               usa_cateter_vesical, dieta
        FROM tb_anamnese
        WHERE paciente_id = :pid
        ORDER BY id DESC
        LIMIT 1
    ", [':pid' => $pacienteId])->fetch(PDO::FETCH_ASSOC);

        return [
            'medicamentos' => $medicamentos,
            'dispositivos' => $dispositivos,
            'anamnese'     => $anamnese ?: [],
        ];
    }

    public function criarCompleto(array $dados): int
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                INSERT INTO tb_relatorio_plantao (
                    paciente_id,
                    cuidador_id,
                    data_inicio,
                    data_fim,
                    data_nascimento,
                    internacao,
                    tipo_local,
                    quarto,
                    nome_acompanhante,
                    turno,
                    evolucao,
                    assinado,
                    estado_paciente,
                    alimentacao,
                    eliminacoes,
                    medicacoes,
                    intercorrencias,
                    status,
                    observacoes_gerais,
                    consciencia,
                    nivel_dor,
                    hidratacao_ml,
                    hidratacao_registros,
                    higiene,
                    sono,
                    decubito,
                    estado_geral,
                    queixas_referidas,
                    exame_fisico,
                    pele_mucosas,
                    visita_medica,
                    entrada_saida_profissionais,
                    entrada_saida_familiares,
                    plantao_entregue_para,
                    peso,
                    pa,
                    fc,
                    temperatura,
                    spo2,
                    frequencia_respiratoria,
                    hgt,
                    diurese,
                    urina_horarios,
                    evacuacao,
                    fezes_horarios,
                    dispositivos,
                    alimentacao_via,
                    created_at,
                    updated_at
                ) VALUES (
                    :paciente_id,
                    :cuidador_id,
                    :data_inicio,
                    :data_fim,
                    :data_nascimento,
                    :internacao,
                    :tipo_local,
                    :quarto,
                    :nome_acompanhante,
                    :turno,
                    :evolucao,
                    :assinado,
                    :estado_paciente,
                    :alimentacao,
                    :eliminacoes,
                    :medicacoes,
                    :intercorrencias,
                    :status,
                    :observacoes_gerais,
                    :consciencia,
                    :nivel_dor,
                    :hidratacao_ml,
                    :hidratacao_registros,
                    :higiene,
                    :sono,
                    :decubito,
                    :estado_geral,
                    :queixas_referidas,
                    :exame_fisico,
                    :pele_mucosas,
                    :visita_medica,
                    :entrada_saida_profissionais,
                    :entrada_saida_familiares,
                    :plantao_entregue_para,
                    :peso,
                    :pa,
                    :fc,
                    :temperatura,
                    :spo2,
                    :frequencia_respiratoria,
                    :hgt,
                    :diurese,
                    :urina_horarios,
                    :evacuacao,
                    :fezes_horarios,
                    :dispositivos,
                    :alimentacao_via,
                    NOW(),
                    NOW()
                )
            ");

            $stmt->execute($this->montarParametros($dados));

            $relatorioId = (int) $this->db->lastInsertId();

            $this->salvarSinaisVitais($relatorioId, $dados);

            $this->db->commit();

            return $relatorioId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log($e->getMessage());

            throw $e;
        }
    }

    public function atualizarCompleto(int $id, array $dados): bool
    {
        $this->db->beginTransaction();

        try {
            $sql = "
                UPDATE tb_relatorio_plantao
                SET
                    paciente_id = :paciente_id,
                    cuidador_id = :cuidador_id,
                    data_inicio = :data_inicio,
                    data_fim = :data_fim,
                    data_nascimento = :data_nascimento,
                    internacao = :internacao,
                    tipo_local = :tipo_local,
                    quarto = :quarto,
                    nome_acompanhante = :nome_acompanhante,
                    turno = :turno,
                    evolucao = :evolucao,
                    assinado = :assinado,
                    estado_paciente = :estado_paciente,
                    alimentacao = :alimentacao,
                    eliminacoes = :eliminacoes,
                    medicacoes = :medicacoes,
                    intercorrencias = :intercorrencias,
                    status = :status,
                    observacoes_gerais = :observacoes_gerais,
                    consciencia = :consciencia,
                    nivel_dor = :nivel_dor,
                    hidratacao_ml = :hidratacao_ml,
                    hidratacao_registros = :hidratacao_registros,
                    higiene = :higiene,
                    sono = :sono,
                    decubito = :decubito,
                    estado_geral = :estado_geral,
                    queixas_referidas = :queixas_referidas,
                    exame_fisico = :exame_fisico,
                    pele_mucosas = :pele_mucosas,
                    visita_medica = :visita_medica,
                    entrada_saida_profissionais = :entrada_saida_profissionais,
                    entrada_saida_familiares = :entrada_saida_familiares,
                    plantao_entregue_para = :plantao_entregue_para,
                    peso = :peso,
                    pa = :pa,
                    fc = :fc,
                    temperatura = :temperatura,
                    spo2 = :spo2,
                    frequencia_respiratoria = :frequencia_respiratoria,
                    hgt = :hgt,
                    diurese = :diurese,
                    urina_horarios = :urina_horarios,
                    evacuacao = :evacuacao,
                    fezes_horarios = :fezes_horarios,
                    dispositivos = :dispositivos,
                    alimentacao_via = :alimentacao_via,
                    updated_at = NOW()
                WHERE id = :id
            ";

            $params = $this->montarParametros($dados);
            $params[':id'] = $id;

            $ok = $this->db->prepare($sql)->execute($params);

            $this->salvarSinaisVitais($id, $dados);

            $this->db->commit();

            return (bool) $ok;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log($e->getMessage());

            throw $e;
        }
    }

    /**
     * Normaliza todos os parâmetros usados em INSERT e UPDATE.
     */
    private function montarParametros(array $dados): array
    {
        // Compatibilidade com nomes alternativos vindos do form.
        $evolucao = $dados['evolucao']
            ?? $dados['evolucao_enfermagem']
            ?? null;

        $observacoesGerais = $dados['observacoes_gerais']
            ?? $dados['observacoes']
            ?? null;

        return [
            ':paciente_id'       => (int) ($dados['paciente_id'] ?? 0),
            ':cuidador_id'       => !empty($dados['cuidador_id']) ? (int) $dados['cuidador_id'] : null,
            ':data_inicio'       => $this->normalizarDatetime($dados['data_inicio'] ?? null),
            ':data_fim'          => $this->normalizarDatetime($dados['data_fim'] ?? null),
            ':data_nascimento'   => $this->stringOrNull($dados['data_nascimento'] ?? null),
            ':internacao'        => $this->stringOrNull($dados['internacao'] ?? null),
            ':tipo_local'        => $this->stringOrNull($dados['tipo_local'] ?? null),
            ':quarto'            => $this->stringOrNull($dados['quarto'] ?? null),
            ':nome_acompanhante' => $this->stringOrNull($dados['nome_acompanhante'] ?? null),
            ':turno'             => $this->stringOrNull($dados['turno'] ?? null),
            ':evolucao'          => $this->stringOrNull($evolucao),
            ':assinado'          => (int) ($dados['assinado'] ?? 0),
            ':estado_paciente'   => $this->stringOrNull($dados['estado_paciente'] ?? null),
            ':estado_geral'      => $this->stringOrNull($dados['estado_geral'] ?? null),
            ':queixas_referidas' => $this->jsonOrString($dados['queixas_referidas'] ?? null),
            ':exame_fisico'      => $this->jsonOrString($dados['exame_fisico'] ?? null),
            ':pele_mucosas'      => $this->stringOrNull($dados['pele_mucosas'] ?? null),
            ':alimentacao'       => $this->jsonOrString($dados['alimentacao'] ?? null),
            ':alimentacao_via'   => $this->stringOrNull($dados['alimentacao_via'] ?? null),
            ':eliminacoes'       => $this->jsonOrString($dados['eliminacoes'] ?? null),
            ':diurese'           => $this->jsonOrString($dados['diurese'] ?? null),
            ':urina_horarios'    => $this->jsonOrString($dados['urina_horarios'] ?? null),
            ':evacuacao'         => $this->jsonOrString($dados['evacuacao'] ?? null),
            ':fezes_horarios'    => $this->jsonOrString($dados['fezes_horarios'] ?? null),
            ':medicacoes'        => $this->jsonOrString($dados['medicacoes'] ?? null),
            ':intercorrencias'   => $this->jsonOrString($dados['intercorrencias'] ?? null),
            ':status'            => $this->stringOrNull($dados['status'] ?? 'rascunho') ?? 'rascunho',
            ':observacoes_gerais' => $this->stringOrNull($observacoesGerais),
            ':consciencia'       => $this->stringOrNull($dados['consciencia'] ?? null),
            ':nivel_dor'         => $this->intOrNull($dados['nivel_dor'] ?? null),
            ':hidratacao_ml'     => $this->intOrNull($dados['hidratacao_ml'] ?? null),
            ':hidratacao_registros' => $this->jsonOrString($dados['hidratacao_registros'] ?? null),
            ':higiene'           => $this->jsonOrString($dados['higiene'] ?? null),
            ':sono'              => $this->jsonOrString($dados['sono'] ?? null),
            ':decubito'          => $this->jsonOrString($dados['decubito'] ?? null),
            ':dispositivos'      => $this->jsonOrString($dados['dispositivos'] ?? null),
            ':visita_medica'     => $this->jsonOrString($dados['visita_medica'] ?? null),
            ':entrada_saida_profissionais' => $this->jsonOrString($dados['entrada_saida_profissionais'] ?? null),
            ':entrada_saida_familiares' => $this->jsonOrString($dados['entrada_saida_familiares'] ?? null),
            ':plantao_entregue_para' => $this->stringOrNull($dados['plantao_entregue_para'] ?? null),
            ':peso'              => $this->stringOrNull($dados['peso'] ?? null),
            ':pa'                => $this->stringOrNull($dados['pa'] ?? null),
            ':fc'                => $this->stringOrNull($dados['fc'] ?? null),
            ':temperatura'       => $this->stringOrNull($dados['temperatura'] ?? null),
            ':spo2'              => $this->stringOrNull($dados['spo2'] ?? null),
            ':frequencia_respiratoria' => $this->stringOrNull($dados['frequencia_respiratoria'] ?? null),
            ':hgt'               => $this->stringOrNull($dados['hgt'] ?? null),
        ];
    }

    private function salvarSinaisVitais(int $relatorioId, array $dados): void
    {
        $exists = (int) $this->query(
            'SELECT COUNT(*) FROM tb_sinais_vitais WHERE relatorio_id = :relatorio_id',
            [':relatorio_id' => $relatorioId]
        )->fetchColumn();

        $params = [
            ':relatorio_id' => $relatorioId,
            ':pa'           => $this->stringOrNull($dados['pa'] ?? null),
            ':fc'           => $this->stringOrNull($dados['fc'] ?? null),
            ':temperatura'  => $this->stringOrNull($dados['temperatura'] ?? null),
            ':spo2'         => $this->stringOrNull($dados['spo2'] ?? null),
            ':frequencia_respiratoria' => $this->stringOrNull($dados['frequencia_respiratoria'] ?? null),
            ':hgt'          => $this->stringOrNull($dados['hgt'] ?? null),
            ':observacao'   => $this->stringOrNull($dados['observacao_sv'] ?? null),
        ];

        if ($exists > 0) {
            $this->db->prepare("
                UPDATE tb_sinais_vitais
                SET
                    pa = :pa,
                    fc = :fc,
                    temperatura = :temperatura,
                    spo2 = :spo2,
                    frequencia_respiratoria = :frequencia_respiratoria,
                    hgt = :hgt,
                    observacao = :observacao
                WHERE relatorio_id = :relatorio_id
            ")->execute($params);

            return;
        }

        $this->db->prepare("
            INSERT INTO tb_sinais_vitais (
                relatorio_id,
                pa,
                fc,
                temperatura,
                spo2,
                frequencia_respiratoria,
                hgt,
                observacao
            ) VALUES (
                :relatorio_id,
                :pa,
                :fc,
                :temperatura,
                :spo2,
                :frequencia_respiratoria,
                :hgt,
                :observacao
            )
        ")->execute($params);
    }

    public function salvarMedicacoesPlantao(int $plantaoId, array $medicacoes): void
    {
        // Remove registros anteriores deste plantão
        $this->db->prepare(
            'DELETE FROM tb_medicacoes_plantao WHERE plantao_id = ?'
        )->execute([$plantaoId]);

        if (empty($medicacoes)) return;

        $stmt = $this->db->prepare("
        INSERT INTO tb_medicacoes_plantao
            (plantao_id, medicacao_paciente_id, medicamento, via, horario, status, observacao)
        VALUES
            (:plantao_id, :med_pac_id, :medicamento, :via, :horario, :status, :obs)
    ");

        foreach ($medicacoes as $med) {
            if (empty($med['medicamento'])) continue;
            $stmt->execute([
                ':plantao_id' => $plantaoId,
                ':med_pac_id' => !empty($med['medicacao_paciente_id']) ? (int)$med['medicacao_paciente_id'] : null,
                ':medicamento' => $med['medicamento'],
                ':via'        => $med['via']     ?? null,
                ':horario'    => $med['horario'] ?? null,
                ':status'     => in_array($med['status'] ?? '', ['administrado', 'pendente', 'recusado'])
                    ? $med['status'] : 'pendente',
                ':obs'        => $med['observacao'] ?? null,
            ]);
        }
    }

    private function normalizarDatetime(mixed $value): ?string
    {
        $value = $this->stringOrNull($value);

        if ($value === null) {
            return null;
        }

        // Converte 2026-05-17T07:00 para 2026-05-17 07:00:00
        return str_replace('T', ' ', $value) . (
            strlen($value) === 16 ? ':00' : ''
        );
    }

    private function intOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function jsonOrString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            $filtered = array_values(array_filter(
                $value,
                static fn($item) => $item !== null && $item !== ''
            ));

            return $filtered === []
                ? null
                : json_encode($filtered, JSON_UNESCAPED_UNICODE);
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed === '' ? null : $trimmed;
        }

        return (string) $value;
    }

    public function listarPorPaciente(int $pacienteId): array
    {
        $sql = "
        SELECT *
        FROM tb_relatorio_plantao
        WHERE paciente_id = :paciente_id
        ORDER BY data_inicio DESC
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':paciente_id', $pacienteId, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listarPacientesComUltimoRelatorio(): array
    {
        $sql = "
        SELECT
            p.*,

            (
                SELECT rp.uuid
                FROM tb_relatorio_plantao rp
                WHERE rp.paciente_id = p.id
                ORDER BY rp.data_inicio DESC
                LIMIT 1
            ) AS ultimo_relatorio_uuid

        FROM tb_pacientes p

        WHERE EXISTS (
            SELECT 1
            FROM tb_relatorio_plantao rp2
            WHERE rp2.paciente_id = p.id
        )

        ORDER BY p.nome_completo ASC
    ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function gerarNumeroProntuario(): string
    {
        $ano = (int)date('Y');

        $stmt = $this->db->prepare("
        INSERT INTO tb_prontuario_sequencia (ano)
        VALUES (:ano)
    ");

        $stmt->execute([
            ':ano' => $ano,
        ]);

        $sequencial = (int)$this->db->lastInsertId();

        return sprintf('PRT-%d-%06d', $ano, $sequencial);
    }

    public function garantirProntuario(int $pacienteId): ?string
    {
        $stmt = $this->db->prepare("
        SELECT prontuario
        FROM tb_pacientes
        WHERE id = :id
        LIMIT 1
    ");

        $stmt->execute([
            ':id' => $pacienteId,
        ]);

        $atual = $stmt->fetchColumn();

        if (!empty($atual)) {
            return (string)$atual;
        }

        $novoProntuario = $this->gerarNumeroProntuario();

        $update = $this->db->prepare("
        UPDATE tb_pacientes
        SET prontuario = :prontuario
        WHERE id = :id
    ");

        $update->execute([
            ':prontuario' => $novoProntuario,
            ':id' => $pacienteId,
        ]);

        return $novoProntuario;
    }
}