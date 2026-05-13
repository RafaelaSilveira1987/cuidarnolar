<?php

namespace App\Models;

class Dashboard extends BaseModel
{
    protected string $table = 'tb_pacientes';

    public function resumo(): array
    {
        return [
            'pacientes' => $this->countTable('tb_pacientes'),
            'responsaveis' => $this->countTable('tb_responsavel'),
            'cuidadores' => $this->countTable('tb_cuidador'),
            'eventos_pendentes' => $this->countWhere('tb_eventos', "status IS NULL OR status = 'Pendente'"),
            'financeiro_pendente' => $this->countWhere('tb_financeiro', "status = 'Pendente'"),
        ];
    }

    public function notificacoes(): array
    {
        return [
            ['titulo' => 'Diarios atrasados', 'valor' => $this->diariosAtrasados(), 'descricao' => 'Sem visita mensal ha mais de 30 dias'],
            ['titulo' => 'Cadastros incompletos', 'valor' => $this->cuidadoresIncompletos(), 'descricao' => 'Cuidadores sem campos essenciais'],
            ['titulo' => 'Agendamentos proximos', 'valor' => $this->agendamentosProximos(), 'descricao' => 'Eventos nos proximos 15 dias'],
            ['titulo' => 'Financeiro pendente', 'valor' => $this->countWhere('tb_financeiro', "status = 'Pendente'"), 'descricao' => 'Entradas ou saidas aguardando baixa'],
        ];
    }

    private function countTable(string $table): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
    }

    private function countWhere(string $table, string $where): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM {$table} WHERE {$where}")->fetchColumn();
    }

    private function diariosAtrasados(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM tb_pacientes p WHERE p.status = 'Ativo' AND NOT EXISTS (SELECT 1 FROM tb_diarioidoso d WHERE d.paciente_id = p.id AND d.visita_mensal >= DATE_SUB(NOW(), INTERVAL 30 DAY))")->fetchColumn();
    }

    private function cuidadoresIncompletos(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM tb_cuidador WHERE status = 'Ativo' AND (telefone IS NULL OR telefone = '' OR cpf IS NULL OR cpf = '' OR endereco IS NULL OR endereco = '')")->fetchColumn();
    }

    private function agendamentosProximos(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM tb_eventos WHERE data_evento BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 15 DAY) AND (status IS NULL OR status = 'Pendente')")->fetchColumn();
    }
}
