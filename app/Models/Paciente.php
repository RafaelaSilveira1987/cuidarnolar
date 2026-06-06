<?php

namespace App\Models;

class Paciente extends BaseModuleModel
{
    protected string $table = 'tb_pacientes';
    protected string $searchColumn = 'p.nome_completo';
    protected string $orderBy = 'p.nome_completo';
    protected string $orderDirection = 'ASC';

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->query('SELECT * FROM tb_pacientes WHERE id = :id LIMIT 1', [':id' => $id]);
        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function buscarPorUuid(string $uuid): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tb_pacientes WHERE uuid = ? LIMIT 1');
        $stmt->execute([$uuid]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function listForIndex(int $page = 1, int $perPage = 15, string $search = ''): array
    {
        return $this->listWithJoins($page, $perPage, $search);
    }


    public function listForIndexPorCuidador(int $cuidadorId, int $page = 1, int $perPage = 15, string $search = ''): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ' WHERE ' . $this->whereEscopoCuidador();
        $params = $this->paramsEscopoCuidador($cuidadorId);

        if ($search !== '') {
            $where .= ' AND (p.nome_completo LIKE :search_nome OR p.cpf LIKE :search_cpf)';
            $params[':search_nome'] = "%{$search}%";
            $params[':search_cpf'] = "%{$search}%";
        }

        $total = (int) $this->query(
            'SELECT COUNT(DISTINCT p.id)
             FROM tb_pacientes p
             LEFT JOIN tb_responsavel r ON r.id = p.responsavel_id
             LEFT JOIN tb_cuidador c ON c.id = p.cuidador_id'
                . $where,
            $params
        )->fetchColumn();

        $data = $this->query(
            $this->baseSelect()
                . $where
                . ' GROUP BY p.id ORDER BY p.nome_completo ASC LIMIT :limit OFFSET :offset',
            $params + [':limit' => $perPage, ':offset' => $offset]
        )->fetchAll();

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    public function pacienteVinculadoAoCuidador(int $pacienteId, int $cuidadorId): bool
    {
        if ($pacienteId <= 0 || $cuidadorId <= 0) {
            return false;
        }

        $count = (int)$this->query(
            'SELECT COUNT(DISTINCT p.id)
             FROM tb_pacientes p
             WHERE p.id = :paciente_id
               AND ' . $this->whereEscopoCuidador(),
            $this->paramsEscopoCuidador($cuidadorId) + [
                ':paciente_id' => $pacienteId,
            ]
        )->fetchColumn();

        return $count > 0;
    }

    private function whereEscopoCuidador(): string
    {
        // Não reutilize o mesmo placeholder várias vezes.
        // Em alguns ambientes PDO isso gera SQLSTATE[HY093].
        return "(
            p.cuidador_id = :cuidador_referencia_id
            OR EXISTS (
                SELECT 1
                FROM tb_escala_base eb
                INNER JOIN tb_escala_profissionais ep ON ep.escala_base_id = eb.id
                WHERE eb.paciente_id = p.id
                  AND ep.cuidador_id = :cuidador_escala_base_id
                  AND COALESCE(ep.ativo, 1) = 1
            )
            OR EXISTS (
                SELECT 1
                FROM tb_escala_ocorrencias eo
                WHERE eo.paciente_id = p.id
                  AND eo.cuidador_id = :cuidador_ocorrencia_id
                  AND eo.status NOT IN ('cancelado')
            )
        )";
    }

    private function paramsEscopoCuidador(int $cuidadorId): array
    {
        return [
            ':cuidador_referencia_id' => $cuidadorId,
            ':cuidador_escala_base_id' => $cuidadorId,
            ':cuidador_ocorrencia_id' => $cuidadorId,
        ];
    }

    public function findForShow(int $id): array|false
    {
        return $this->rawFirst($this->baseSelect() . ' WHERE p.id = :id', [':id' => $id]);
    }

    public function findForShowByUuid(string $uuid): array|false
    {
        return $this->rawFirst($this->baseSelect() . ' WHERE p.uuid = :uuid', [':uuid' => $uuid]);
    }

    public function responsaveisOptions(): array
    {
        return $this->rawAll(
            "SELECT id, uuid, nome_completo, cpf, telefone, email, grau_parentesco
             FROM tb_responsavel
             WHERE status = 'Ativo'
             ORDER BY nome_completo ASC"
        );
    }

    public function cuidadoresOptions(): array
    {
        return $this->rawAll(
            "SELECT id, nome_completo
             FROM tb_cuidador
             WHERE status IN ('Ativo', 'Standby')
             ORDER BY nome_completo ASC"
        );
    }

    public function createPaciente(array $data): int
    {
        $id = $this->insert(
            $this->filterExistingColumns(
                $this->normalizeData($data)
            )
        );

        $prontuario = $this->gerarProntuario((int)$id);

        $this->query(
            "UPDATE tb_pacientes
         SET prontuario = :prontuario
         WHERE id = :id
           AND (prontuario IS NULL OR TRIM(prontuario) = '')",
            [
                ':prontuario' => $prontuario,
                ':id' => $id,
            ]
        );

        return (int)$id;
    }

    private function gerarProntuario(int $id): string
    {
        return 'PRT-' . date('Y') . '-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
    }

    public function updatePaciente(int $id, array $data): bool
    {
        return $this->update($id, $this->filterExistingColumns($this->normalizeData($data)));
    }

    public function inativar(int $id, string $motivo = ''): bool
    {
        return $this->update($id, [
            'status' => 'Inativo',
            'motivo_inativacao' => $motivo !== '' ? $motivo : 'Inativado pelo painel MVC',
        ]);
    }

    public function pacientesComRelatorio(): array
    {
        $sql = "
        SELECT
            p.id,
            p.uuid,
            p.nome_completo,
            p.prontuario,
            COUNT(rp.id) AS total_relatorios,
            MAX(rp.data_inicio) AS ultimo_relatorio_data
        FROM tb_pacientes p
        INNER JOIN tb_relatorio_plantao rp
            ON rp.paciente_id = p.id
        GROUP BY
            p.id,
            p.uuid,
            p.nome_completo,
            p.prontuario
        ORDER BY p.nome_completo ASC
    ";

        return $this->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function buscarAnamnese(int $pacienteId): ?array
    {
        $resultado = $this->query("
            SELECT *
            FROM tb_anamnese
            WHERE paciente_id = :paciente_id
            ORDER BY id DESC
            LIMIT 1
        ", [':paciente_id' => $pacienteId])->fetch();

        return $resultado ?: null;
    }

    public function buscarMedicacoes(int $pacienteId, bool $somenteAtivas = true): array
    {
        $sql = "
            SELECT *
            FROM tb_medicacoes_paciente
            WHERE paciente_id = :paciente_id
        ";

        if ($somenteAtivas) {
            $sql .= " AND status = 'Ativo'";
        }

        $sql .= " ORDER BY nome_medicamento ASC";

        return $this->query($sql, [':paciente_id' => $pacienteId])->fetchAll();
    }

    public function buscarDispositivos(int $pacienteId, bool $somenteAtivas = true): array
    {
        $sql = "
            SELECT *
            FROM tb_dispositivos_paciente
            WHERE paciente_id = :paciente_id
        ";

        if ($somenteAtivas) {
            $sql .= " AND status = 'Ativo'";
        }

        $sql .= " ORDER BY tipo ASC";

        return $this->query($sql, [':paciente_id' => $pacienteId])->fetchAll();
    }

    public function buscarPerfilClinicoCompleto(int $pacienteId): ?array
    {
        $paciente = $this->buscarPorId($pacienteId);

        if (!$paciente) {
            return null;
        }

        $anamnese = $this->buscarAnamnese($pacienteId);
        $medicacoes = $this->buscarMedicacoes($pacienteId);
        $dispositivos = $this->buscarDispositivos($pacienteId);

        if (empty($paciente['diagnostico']) && !empty($anamnese['diagnostico_principal'])) {
            $paciente['diagnostico'] = $anamnese['diagnostico_principal'];
        }

        $paciente['anamnese'] = $anamnese;
        $paciente['medicacoes'] = $medicacoes;
        $paciente['dispositivos'] = $dispositivos;

        return $paciente;
    }

    private function listWithJoins(int $page, int $perPage, string $search): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $where = $search !== ''
            ? ' WHERE p.nome_completo LIKE :search_nome OR p.cpf LIKE :search_cpf'
            : '';

        $params = $search !== ''
            ? [':search_nome' => "%{$search}%", ':search_cpf' => "%{$search}%"]
            : [];

        $total = (int) $this->query(
            'SELECT COUNT(*)
             FROM tb_pacientes p
             LEFT JOIN tb_responsavel r ON r.id = p.responsavel_id
             LEFT JOIN tb_cuidador c ON c.id = p.cuidador_id'
                . $where,
            $params
        )->fetchColumn();

        $data = $this->query(
            $this->baseSelect()
                . $where
                . ' ORDER BY p.nome_completo ASC LIMIT :limit OFFSET :offset',
            $params + [':limit' => $perPage, ':offset' => $offset]
        )->fetchAll();

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    private function normalizeData(array $data): array
    {
        return [
            'nome_completo' => trim((string)($data['nome_completo'] ?? '')),
            'data_nascimento' => $this->nullableDate($data['data_nascimento'] ?? null),
            'sexo' => $this->nullableString($data['sexo'] ?? null),
            'cpf' => $this->nullableString($data['cpf'] ?? null),
            'rg' => $this->nullableString($data['rg'] ?? null),
            'cartao_nac_sus' => $this->nullableString($data['cartao_nac_sus'] ?? null),
            'foto' => $this->nullableString($data['foto'] ?? null),
            'endereco_completo' => $this->nullableString($data['endereco_completo'] ?? null),
            'telefone_principal' => $this->nullableString($data['telefone_principal'] ?? null),
            'telefone_secundario' => $this->nullableString($data['telefone_secundario'] ?? null),
            'email' => $this->nullableString($data['email'] ?? null),
            'plano_saude' => $this->nullableString($data['plano_saude'] ?? null),
            'responsavel_id' => $this->nullableInt($data['responsavel_id'] ?? null),
            'cuidador_id' => $this->nullableInt($data['cuidador_id'] ?? null),
            'anamnese_id' => $this->nullableInt($data['anamnese_id'] ?? null),
            'diagnostico' => $this->nullableString($data['diagnostico'] ?? null),
            'cid_principal' => $this->nullableString($data['cid_principal'] ?? null),
            'diagnostico_principal' => $this->nullableString($data['diagnostico_principal'] ?? null),
            'comorbidades' => $this->nullableString($data['comorbidades'] ?? null),
            'alergias' => $this->nullableString($data['alergias'] ?? null),
            'historico_cirurgico' => $this->nullableString($data['historico_cirurgico'] ?? null),
            'tipo_sanguineo' => $this->nullableString($data['tipo_sanguineo'] ?? null),
            'peso' => $this->nullableDecimal($data['peso'] ?? null),
            'altura' => $this->nullableDecimal($data['altura'] ?? null),
            'motivo_homecare' => $this->nullableString($data['motivo_homecare'] ?? null),
            'dieta_tipo' => $this->nullableString($data['dieta_tipo'] ?? null),
            'dieta_restricao' => $this->nullableString($data['dieta_restricao'] ?? null),
            'alimentacao_via' => $this->nullableString($data['alimentacao_via'] ?? null),
            'sonda_vesical' => $this->normalizeSimNao($data['sonda_vesical'] ?? 'Nao'),
            'incontinencia' => $this->nullableString($data['incontinencia'] ?? null),
            'mobilidade' => $this->nullableString($data['mobilidade'] ?? null),
            'estado_cognitivo_base' => $this->nullableString($data['estado_cognitivo_base'] ?? null),
            'usa_sonda' => $this->normalizeSimNao($data['usa_sonda'] ?? 'Nao'),
            'usa_oxigenio' => $this->normalizeSimNao($data['usa_oxigenio'] ?? 'Nao'),
            'traqueostomia' => $this->normalizeSimNao($data['traqueostomia'] ?? 'Nao'),
            'gastrostomia' => $this->normalizeSimNao($data['gastrostomia'] ?? 'Nao'),
            'colostomia' => $this->normalizeSimNao($data['colostomia'] ?? 'Nao'),
            'cateter_vesical' => $this->normalizeSimNao($data['cateter_vesical'] ?? 'Nao'),
            'gtt' => $this->normalizeSimNao($data['gtt'] ?? 'Nao'),
            'sne' => $this->normalizeSimNao($data['sne'] ?? 'Nao'),
            'cateter_venoso' => $this->normalizeSimNao($data['cateter_venoso'] ?? 'Nao'),
            'picc' => $this->normalizeSimNao($data['picc'] ?? 'Nao'),
            'lesao_pressao' => $this->normalizeSimNao($data['lesao_pressao'] ?? 'Nao'),
            'curativos' => $this->nullableString($data['curativos'] ?? null),
            'areas_risco' => $this->nullableString($data['areas_risco'] ?? null),
            'condutas_permanentes' => $this->jsonList($data['condutas_permanentes'] ?? null),
            'convenio' => $this->nullableString($data['convenio'] ?? null),
            'numero_carteirinha' => $this->nullableString($data['numero_carteirinha'] ?? null),
            'prescricao_medica' => $this->nullableString($data['prescricao_medica'] ?? null),
            'termos_assinados' => $this->nullableString($data['termos_assinados'] ?? null),
            'observacoes_clinicas' => $this->nullableString($data['observacoes_clinicas'] ?? null),
            'status' => in_array(($data['status'] ?? 'Ativo'), ['Ativo', 'Inativo'], true)
                ? $data['status']
                : 'Ativo',
            'motivo_inativacao' => $this->nullableString($data['motivo_inativacao'] ?? null),
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }

    private function nullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int)$value;
    }

    private function nullableDate(mixed $value): ?string
    {
        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }

    private function nullableDecimal(mixed $value): ?string
    {
        $value = str_replace(',', '.', trim((string)$value));
        return $value === '' ? null : $value;
    }

    private function normalizeSimNao(mixed $value): string
    {
        return (string)$value === 'Sim' ? 'Sim' : 'Nao';
    }

    private function jsonList(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            $filtered = array_values(array_filter($value, static fn($item) => trim((string)$item) !== ''));
            return $filtered === [] ? null : json_encode($filtered, JSON_UNESCAPED_UNICODE);
        }

        return $this->nullableString($value);
    }

    private function filterExistingColumns(array $data): array
    {
        return array_intersect_key($data, array_flip($this->tableColumns()));
    }

    private function tableColumns(): array
    {
        static $columns = null;

        if ($columns !== null) {
            return $columns;
        }

        $rows = $this->query("SHOW COLUMNS FROM {$this->table}")->fetchAll(\PDO::FETCH_ASSOC);
        $columns = array_map(static fn(array $row): string => (string)$row['Field'], $rows);

        return $columns;
    }

    private function baseSelect(): string
    {
        return "
            SELECT
                p.*,
                r.uuid AS responsavel_uuid,
                r.nome_completo AS responsavel_nome,
                r.cpf AS responsavel_cpf,
                r.email AS responsavel_email,
                r.telefone AS responsavel_telefone,
                r.data_nascimento AS responsavel_data_nascimento,
                r.grau_parentesco AS responsavel_parentesco,
                r.status AS responsavel_status,
                CONCAT_WS(', ', NULLIF(CONCAT_WS(' ', r.endereco, r.numero), ''), r.bairro, NULLIF(CONCAT_WS('/', r.cidade, r.estado), '/')) AS responsavel_endereco_completo,
                c.nome_completo AS cuidador_nome
            FROM tb_pacientes p
            LEFT JOIN tb_responsavel r ON r.id = p.responsavel_id
            LEFT JOIN tb_cuidador c ON c.id = p.cuidador_id
        ";
    }
}