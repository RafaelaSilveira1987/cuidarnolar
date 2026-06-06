<?php

namespace App\Models;

use PDO;
use Throwable;

class Dashboard extends BaseModel
{
    protected string $table = 'tb_pacientes';

    public function resumo(): array
    {
        return [
            // Mantidos por compatibilidade com versões antigas da view.
            'pacientes' => $this->countTable('tb_pacientes'),
            'responsaveis' => $this->countTable('tb_responsavel'),
            'cuidadores' => $this->countTable('tb_cuidador'),
            'eventos_pendentes' => $this->countWhere('tb_eventos', "status IS NULL OR status = 'Pendente'"),
            'financeiro_pendente' => $this->countWhere('tb_financeiro', "status = 'Pendente'"),

            // Nova visão operacional do ERP.
            'pacientes_ativos' => $this->countWhere('tb_pacientes', "status = 'Ativo'"),
            'cuidadores_ativos' => $this->countWhere('tb_cuidador', "status IN ('Ativo', 'Standby')"),
            'plantoes_hoje' => $this->plantoesHojeCount(),
            'escalas_pendentes' => $this->escalasPendentesCount(),
            'escalas_reabertas' => $this->escalasReabertasCount(),
            'fechadas_aguardando_financeiro' => $this->escalasFechadasAguardandoFinanceiroCount(),
            'planos_rascunho' => $this->planosRascunhoCount(),
            'pacientes_sem_contrato' => $this->pacientesSemContratoCount(),
            'pacientes_sem_escala' => $this->pacientesSemEscalaCount(),
            'contratos_vencendo' => $this->contratosVencendoCount(),
        ];
    }

    public function notificacoes(): array
    {
        // Mantém retorno antigo, mas agora sem expor valores financeiros.
        return [
            ['titulo' => 'Diários atrasados', 'valor' => $this->diariosAtrasados(), 'descricao' => 'Sem visita mensal há mais de 30 dias'],
            ['titulo' => 'Cadastros incompletos', 'valor' => $this->cuidadoresIncompletos(), 'descricao' => 'Cuidadores sem campos essenciais'],
            ['titulo' => 'Agendamentos próximos', 'valor' => $this->agendamentosProximos(), 'descricao' => 'Eventos nos próximos 15 dias'],
            ['titulo' => 'Escalas reabertas', 'valor' => $this->escalasReabertasCount(), 'descricao' => 'Aguardando reaplicação ou nova confirmação'],
        ];
    }

    public function alertasOperacionais(): array
    {
        $itens = [
            [
                'titulo' => 'Plantões hoje',
                'valor' => $this->plantoesHojeCount(),
                'descricao' => 'Plantões previstos, confirmados ou finalizados para hoje',
                'rota' => '/escala',
                'tipo' => 'info',
            ],
            [
                'titulo' => 'Escalas reabertas',
                'valor' => $this->escalasReabertasCount(),
                'descricao' => 'Períodos que foram reabertos para ajuste',
                'rota' => '/escala',
                'tipo' => 'warn',
            ],
            [
                'titulo' => 'Aguardando financeiro',
                'valor' => $this->escalasFechadasAguardandoFinanceiroCount(),
                'descricao' => 'Escalas fechadas ainda sem geração de contas a pagar',
                'rota' => '/financeiro/contas-pagar/gerar',
                'tipo' => 'warn',
            ],
            [
                'titulo' => 'Pacientes sem contrato ativo',
                'valor' => $this->pacientesSemContratoCount(),
                'descricao' => 'Pacientes ativos sem contrato válido',
                'rota' => '/pacientes',
                'tipo' => 'danger',
            ],
            [
                'titulo' => 'Pacientes sem escala',
                'valor' => $this->pacientesSemEscalaCount(),
                'descricao' => 'Pacientes ativos sem escala base ativa',
                'rota' => '/pacientes',
                'tipo' => 'info',
            ],
            [
                'titulo' => 'Planos em rascunho',
                'valor' => $this->planosRascunhoCount(),
                'descricao' => 'Planos de cuidado aguardando revisão/ativação',
                'rota' => '/pacientes',
                'tipo' => 'info',
            ],
            [
                'titulo' => 'Contratos vencendo',
                'valor' => $this->contratosVencendoCount(),
                'descricao' => 'Contratos ativos com término nos próximos 7 dias',
                'rota' => '/financeiro/contratos',
                'tipo' => 'warn',
            ],
        ];

        return $itens;
    }

    public function alertasFinanceiros(): array
    {
        return [
            [
                'titulo' => 'Contas a receber vencidas',
                'valor' => $this->financeiroCount('Entrada', 'vencidas'),
                'descricao' => 'Entradas pendentes com vencimento anterior a hoje',
                'rota' => '/financeiro/contas-receber',
                'tipo' => 'danger',
            ],
            [
                'titulo' => 'Contas a receber vencendo',
                'valor' => $this->financeiroCount('Entrada', 'vencendo'),
                'descricao' => 'Entradas pendentes com vencimento nos próximos 7 dias',
                'rota' => '/financeiro/contas-receber',
                'tipo' => 'warn',
            ],
            [
                'titulo' => 'Contas a pagar vencidas',
                'valor' => $this->financeiroCount('Saida', 'vencidas'),
                'descricao' => 'Saídas pendentes com vencimento anterior a hoje',
                'rota' => '/financeiro/contas-pagar',
                'tipo' => 'danger',
            ],
            [
                'titulo' => 'Contas a pagar vencendo',
                'valor' => $this->financeiroCount('Saida', 'vencendo'),
                'descricao' => 'Saídas pendentes com vencimento nos próximos 7 dias',
                'rota' => '/financeiro/contas-pagar',
                'tipo' => 'warn',
            ],
        ];
    }

    public function operacaoHoje(int $limit = 8): array
    {
        if (!$this->tableExists('tb_escala_ocorrencias')) {
            return [];
        }

        $sql = "SELECT
                    eo.id,
                    eo.data_plantao,
                    DATE_FORMAT(eo.inicio, '%H:%i') AS inicio_hora,
                    DATE_FORMAT(eo.fim, '%H:%i') AS fim_hora,
                    eo.status,
                    p.nome_completo AS paciente_nome,
                    c.nome_completo AS cuidador_nome
                FROM tb_escala_ocorrencias eo
                LEFT JOIN tb_pacientes p ON p.id = eo.paciente_id
                LEFT JOIN tb_cuidador c ON c.id = eo.cuidador_id
                WHERE DATE(eo.data_plantao) = CURDATE()
                ORDER BY eo.inicio ASC, eo.id ASC
                LIMIT :limit";

        return $this->safeAll($sql, [':limit' => $limit]);
    }

    private function plantoesHojeCount(): int
    {
        if (!$this->tableExists('tb_escala_ocorrencias')) {
            return 0;
        }

        return $this->safeScalar("SELECT COUNT(*) FROM tb_escala_ocorrencias WHERE DATE(data_plantao) = CURDATE()");
    }

    private function escalasPendentesCount(): int
    {
        if (!$this->tableExists('tb_escala_ocorrencias')) {
            return 0;
        }

        return $this->safeScalar(
            "SELECT COUNT(*)
             FROM tb_escala_ocorrencias
             WHERE status IN ('sugerido', 'previsto', 'pendente')
               AND DATE(data_plantao) >= CURDATE()"
        );
    }

    private function escalasReabertasCount(): int
    {
        if (!$this->tableExists('tb_escala_aprovacoes')) {
            return 0;
        }

        return $this->safeScalar(
            "SELECT COUNT(*)
             FROM tb_escala_aprovacoes
             WHERE LOWER(COALESCE(status, '')) IN ('reaberta', 'reaberto')"
        );
    }

    private function escalasFechadasAguardandoFinanceiroCount(): int
    {
        if (!$this->tableExists('tb_escala_ocorrencias')) {
            return 0;
        }

        $statusFechado = "('finalizado', 'finalizada', 'fechado', 'fechada')";

        if ($this->tableExists('tb_financeiro') && $this->columnExists('tb_financeiro', 'escala_ocorrencia_id')) {
            return $this->safeScalar(
                "SELECT COUNT(*)
                 FROM tb_escala_ocorrencias eo
                 WHERE LOWER(COALESCE(eo.status, '')) IN {$statusFechado}
                   AND NOT EXISTS (
                        SELECT 1
                        FROM tb_financeiro f
                        WHERE f.escala_ocorrencia_id = eo.id
                          AND f.tipo_transacao <> 'Entrada'
                          AND f.status <> 'Cancelado'
                   )"
            );
        }

        return $this->safeScalar(
            "SELECT COUNT(*)
             FROM tb_escala_ocorrencias
             WHERE LOWER(COALESCE(status, '')) IN {$statusFechado}"
        );
    }

    private function planosRascunhoCount(): int
    {
        if (!$this->tableExists('tb_planos_cuidado')) {
            return 0;
        }

        return $this->safeScalar(
            "SELECT COUNT(*) FROM tb_planos_cuidado WHERE LOWER(COALESCE(status, '')) = 'rascunho'"
        );
    }

    private function pacientesSemContratoCount(): int
    {
        if (!$this->tableExists('tb_pacientes') || !$this->tableExists('tb_contratos_paciente')) {
            return 0;
        }

        return $this->safeScalar(
            "SELECT COUNT(*)
             FROM tb_pacientes p
             WHERE p.status = 'Ativo'
               AND NOT EXISTS (
                    SELECT 1
                    FROM tb_contratos_paciente cp
                    WHERE cp.paciente_id = p.id
                      AND cp.status = 'Ativo'
                      AND (cp.vigencia_fim IS NULL OR cp.vigencia_fim = '' OR cp.vigencia_fim >= CURDATE())
               )"
        );
    }

    private function pacientesSemEscalaCount(): int
    {
        if (!$this->tableExists('tb_pacientes') || !$this->tableExists('tb_escala_base')) {
            return 0;
        }

        return $this->safeScalar(
            "SELECT COUNT(*)
             FROM tb_pacientes p
             WHERE p.status = 'Ativo'
               AND NOT EXISTS (
                    SELECT 1
                    FROM tb_escala_base eb
                    WHERE eb.paciente_id = p.id
                      AND eb.ativo = 1
               )"
        );
    }

    private function contratosVencendoCount(): int
    {
        if (!$this->tableExists('tb_contratos_paciente')) {
            return 0;
        }

        return $this->safeScalar(
            "SELECT COUNT(*)
             FROM tb_contratos_paciente
             WHERE status = 'Ativo'
               AND vigencia_fim IS NOT NULL
               AND vigencia_fim <> ''
               AND vigencia_fim BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
        );
    }

    private function financeiroCount(string $tipo, string $janela): int
    {
        if (!$this->tableExists('tb_financeiro')) {
            return 0;
        }

        $campoData = $this->columnExists('tb_financeiro', 'data_vencimento') ? 'data_vencimento' : 'DATE(data)';
        $whereTipo = $tipo === 'Entrada'
            ? "tipo_transacao = 'Entrada'"
            : "tipo_transacao <> 'Entrada'";

        $whereData = $janela === 'vencidas'
            ? "{$campoData} IS NOT NULL AND {$campoData} < CURDATE()"
            : "{$campoData} IS NOT NULL AND {$campoData} BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";

        return $this->safeScalar(
            "SELECT COUNT(*)
             FROM tb_financeiro
             WHERE status = 'Pendente'
               AND {$whereTipo}
               AND {$whereData}"
        );
    }

    private function countTable(string $table): int
    {
        if (!$this->tableExists($table)) {
            return 0;
        }

        return $this->safeScalar("SELECT COUNT(*) FROM {$table}");
    }

    private function countWhere(string $table, string $where): int
    {
        if (!$this->tableExists($table)) {
            return 0;
        }

        return $this->safeScalar("SELECT COUNT(*) FROM {$table} WHERE {$where}");
    }

    private function diariosAtrasados(): int
    {
        if (!$this->tableExists('tb_pacientes') || !$this->tableExists('tb_diarioidoso')) {
            return 0;
        }

        return $this->safeScalar(
            "SELECT COUNT(*)
             FROM tb_pacientes p
             WHERE p.status = 'Ativo'
               AND NOT EXISTS (
                    SELECT 1
                    FROM tb_diarioidoso d
                    WHERE d.paciente_id = p.id
                      AND d.visita_mensal >= DATE_SUB(NOW(), INTERVAL 30 DAY)
               )"
        );
    }

    private function cuidadoresIncompletos(): int
    {
        if (!$this->tableExists('tb_cuidador')) {
            return 0;
        }

        return $this->safeScalar(
            "SELECT COUNT(*)
             FROM tb_cuidador
             WHERE status = 'Ativo'
               AND (telefone IS NULL OR telefone = '' OR cpf IS NULL OR cpf = '' OR endereco IS NULL OR endereco = '')"
        );
    }

    private function agendamentosProximos(): int
    {
        if (!$this->tableExists('tb_eventos')) {
            return 0;
        }

        return $this->safeScalar(
            "SELECT COUNT(*)
             FROM tb_eventos
             WHERE data_evento BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 15 DAY)
               AND (status IS NULL OR status = 'Pendente')"
        );
    }

    private function tableExists(string $table): bool
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*)
                 FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = :table"
            );
            $stmt->execute([':table' => $table]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*)
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = :table
                   AND COLUMN_NAME = :column"
            );
            $stmt->execute([':table' => $table, ':column' => $column]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    private function safeScalar(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (Throwable) {
            return 0;
        }
    }

    private function safeAll(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            return [];
        }
    }
}
