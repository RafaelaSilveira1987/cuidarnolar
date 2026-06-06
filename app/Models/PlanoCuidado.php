<?php

namespace App\Models;

class PlanoCuidado extends BaseModuleModel
{
    protected string $table = 'tb_planos_cuidado';
    protected string $searchColumn = 'titulo';
    protected string $orderBy = 'id';
    protected string $orderDirection = 'DESC';

    protected array $fillable = [
        'uuid',
        'paciente_id',
        'modelo_chave',
        'titulo',
        'subtitulo',
        'responsavel_tecnico',
        'data_inicio',
        'data_revisao',
        'status',
        'versao',
        'resumo_clinico',
        'objetivos',
        'monitoramento',
        'oxigenoterapia',
        'nebulizacao',
        'controle_ambiental',
        'alimentacao_hidratacao',
        'atividade_repouso',
        'medicamentos',
        'comunicacao_familia',
        'sinais_alerta',
        'observacoes',
    ];

    protected array $nullable = [
        'modelo_chave',
        'subtitulo',
        'responsavel_tecnico',
        'data_revisao',
        'resumo_clinico',
        'objetivos',
        'monitoramento',
        'oxigenoterapia',
        'nebulizacao',
        'controle_ambiental',
        'alimentacao_hidratacao',
        'atividade_repouso',
        'medicamentos',
        'comunicacao_familia',
        'sinais_alerta',
        'observacoes',
    ];

    public function planoAtivoPorPaciente(int $pacienteId): array|false
    {
        return $this->rawFirst(
            "SELECT *
             FROM {$this->table}
             WHERE paciente_id = :paciente_id
               AND status = 'Ativo'
             ORDER BY versao DESC, id DESC
             LIMIT 1",
            [':paciente_id' => $pacienteId]
        );
    }

    public function historicoPorPaciente(int $pacienteId): array
    {
        return $this->rawAll(
            "SELECT *
             FROM {$this->table}
             WHERE paciente_id = :paciente_id
             ORDER BY status = 'Ativo' DESC, versao DESC, id DESC",
            [':paciente_id' => $pacienteId]
        );
    }

    public function findByPaciente(int $pacienteId, int $planoId): array|false
    {
        return $this->rawFirst(
            "SELECT *
             FROM {$this->table}
             WHERE id = :id
               AND paciente_id = :paciente_id
             LIMIT 1",
            [':id' => $planoId, ':paciente_id' => $pacienteId]
        );
    }

    public function findByPacienteUuid(int $pacienteId, string $planoUuid): array|false
    {
        $planoUuid = trim($planoUuid);
        if ($planoUuid === '') {
            return false;
        }

        return $this->rawFirst(
            "SELECT *
             FROM {$this->table}
             WHERE uuid = :uuid
               AND paciente_id = :paciente_id
             LIMIT 1",
            [':uuid' => $planoUuid, ':paciente_id' => $pacienteId]
        );
    }

    public function listarModelos(): array
    {
        if ($this->tabelaExiste('tb_planos_cuidado_modelos')) {
            $modelos = $this->rawAll(
                "SELECT chave, nome, descricao
                 FROM tb_planos_cuidado_modelos
                 WHERE ativo = 1
                 ORDER BY ordem ASC, nome ASC"
            );

            if ($modelos !== []) {
                return $modelos;
            }
        }

        return [
            ['chave' => 'respiratorio', 'nome' => 'Paciente respiratório', 'descricao' => 'Asma, DPOC, oxigênio, nebulização e monitoramento respiratório.'],
            ['chave' => 'acamado', 'nome' => 'Paciente acamado', 'descricao' => 'Mudança de decúbito, pele, higiene, prevenção de lesão e conforto.'],
            ['chave' => 'gtt_sne', 'nome' => 'Paciente com GTT/SNE', 'descricao' => 'Cuidados com dieta enteral, dispositivos e sinais de alerta.'],
            ['chave' => 'demencia', 'nome' => 'Paciente com demência', 'descricao' => 'Rotina, segurança, comunicação, sono e comportamento.'],
            ['chave' => 'pos_operatorio', 'nome' => 'Pós-operatório', 'descricao' => 'Dor, curativos, mobilização, sinais de infecção e evolução.'],
            ['chave' => 'pediatrico', 'nome' => 'Plano pediátrico', 'descricao' => 'Rotina infantil, família, segurança, alimentação e sinais de alerta.'],
            ['chave' => 'acompanhante_hospitalar', 'nome' => 'Acompanhante hospitalar', 'descricao' => 'Rotina hospitalar, comunicação com equipe e apoio ao paciente.'],
        ];
    }

    public function gerarRascunhoPaciente(array $paciente, string $modeloChave = ''): array
    {
        $modeloChave = trim($modeloChave) !== '' ? trim($modeloChave) : $this->inferirModelo($paciente);

        $nome = $this->valor($paciente['nome_completo'] ?? '', 'Paciente');
        $diagnostico = $this->valor($paciente['diagnostico'] ?? $paciente['diagnostico_principal'] ?? '', 'Diagnóstico não informado');
        $cid = $this->valor($paciente['cid_principal'] ?? '', 'CID não informado');
        $idade = $this->idadeTexto($paciente['data_nascimento'] ?? null);
        $mobilidade = $this->valor($paciente['mobilidade'] ?? '', 'Mobilidade não informada');
        $cognicao = $this->valor($paciente['estado_cognitivo_base'] ?? '', 'Cognição não informada');
        $responsavel = $this->valor($paciente['responsavel_nome'] ?? $paciente['responsavel_nome_texto'] ?? '', 'responsável');
        $telefoneResp = $this->valor($paciente['responsavel_telefone'] ?? '', 'telefone não informado');
        $viaAlimentar = $this->valor($paciente['alimentacao_via'] ?? '', 'VO');
        $temOxigenio = $this->sim($paciente['usa_oxigenio'] ?? 'Não');
        $temSonda = $this->sim($paciente['usa_sonda'] ?? 'Não');
        $temGtt = $this->sim($paciente['gtt'] ?? $paciente['gastrostomia'] ?? 'Não');
        $temSne = $this->sim($paciente['sne'] ?? 'Não');
        $temTraqueo = $this->sim($paciente['traqueostomia'] ?? 'Não');
        $alergias = trim((string)($paciente['alergias'] ?? ''));

        $titulo = "Plano de Cuidados Home Care — {$nome}";
        $subtitulo = "{$diagnostico} ({$cid}) | {$idade} | {$mobilidade} | {$cognicao}";

        $resumo = implode("\n", array_filter([
            "Paciente: {$nome}",
            "Diagnóstico: {$diagnostico}",
            "CID: {$cid}",
            "Idade: {$idade}",
            "Mobilidade: {$mobilidade}",
            "Cognição: {$cognicao}",
            "Via alimentar: {$viaAlimentar}",
            $alergias !== '' ? "Alergias: {$alergias}" : '',
            $temOxigenio ? 'Dispositivo/condição relevante: usa oxigênio.' : '',
            $temSonda ? 'Dispositivo/condição relevante: usa sonda.' : '',
            $temGtt ? 'Dispositivo/condição relevante: GTT/gastrostomia.' : '',
            $temSne ? 'Dispositivo/condição relevante: SNE.' : '',
            $temTraqueo ? 'Dispositivo/condição relevante: traqueostomia.' : '',
        ]));

        $base = $this->modeloBase($modeloChave, [
            'nome' => $nome,
            'diagnostico' => $diagnostico,
            'cid' => $cid,
            'idade' => $idade,
            'mobilidade' => $mobilidade,
            'cognicao' => $cognicao,
            'responsavel' => $responsavel,
            'responsavel_telefone' => $telefoneResp,
            'via_alimentar' => $viaAlimentar,
            'tem_oxigenio' => $temOxigenio,
        ]);

        return array_merge([
            'modelo_chave' => $modeloChave,
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'responsavel_tecnico' => '',
            'data_inicio' => date('Y-m-d'),
            'data_revisao' => '',
            'status' => 'Rascunho',
            'versao' => $this->proximaVersao((int)($paciente['id'] ?? 0)),
            'resumo_clinico' => $resumo,
        ], $base);
    }

    public function salvarPlano(int $pacienteId, array $data, ?int $planoId = null): int
    {
        $payload = $this->normalizarPayload($pacienteId, $data);

        if ($planoId) {
            $this->update($planoId, $payload + ['updated_at' => date('Y-m-d H:i:s')]);
            return $planoId;
        }

        $payload['uuid'] = $this->gerarUuid();
        $payload['created_at'] = date('Y-m-d H:i:s');
        $payload['updated_at'] = date('Y-m-d H:i:s');

        return $this->insert($payload);
    }

    public function ativarPlano(int $pacienteId, int $planoId): bool
    {
        $plano = $this->findByPaciente($pacienteId, $planoId);
        if (!$plano) {
            return false;
        }

        $this->query(
            "UPDATE {$this->table}
             SET status = 'Revisado', updated_at = NOW()
             WHERE paciente_id = :paciente_id
               AND status = 'Ativo'
               AND id <> :id",
            [':paciente_id' => $pacienteId, ':id' => $planoId]
        );

        $this->query(
            "UPDATE {$this->table}
             SET status = 'Ativo', updated_at = NOW()
             WHERE id = :id
               AND paciente_id = :paciente_id",
            [':id' => $planoId, ':paciente_id' => $pacienteId]
        );

        return true;
    }

    public function arquivarPlano(int $pacienteId, int $planoId): bool
    {
        $this->query(
            "UPDATE {$this->table}
             SET status = 'Arquivado', updated_at = NOW()
             WHERE id = :id
               AND paciente_id = :paciente_id",
            [':id' => $planoId, ':paciente_id' => $pacienteId]
        );

        return true;
    }

    private function normalizarPayload(int $pacienteId, array $data): array
    {
        $status = trim((string)($data['status'] ?? 'Rascunho'));
        if (!in_array($status, ['Rascunho', 'Ativo', 'Revisado', 'Arquivado'], true)) {
            $status = 'Rascunho';
        }

        $payload = [
            'paciente_id' => $pacienteId,
            'modelo_chave' => trim((string)($data['modelo_chave'] ?? '')),
            'titulo' => trim((string)($data['titulo'] ?? 'Plano de Cuidados Home Care')),
            'subtitulo' => trim((string)($data['subtitulo'] ?? '')),
            'responsavel_tecnico' => trim((string)($data['responsavel_tecnico'] ?? '')),
            'data_inicio' => trim((string)($data['data_inicio'] ?? date('Y-m-d'))) ?: date('Y-m-d'),
            'data_revisao' => trim((string)($data['data_revisao'] ?? '')) ?: null,
            'status' => $status,
            'versao' => max(1, (int)($data['versao'] ?? $this->proximaVersao($pacienteId))),
            'resumo_clinico' => trim((string)($data['resumo_clinico'] ?? '')),
            'objetivos' => trim((string)($data['objetivos'] ?? '')),
            'monitoramento' => trim((string)($data['monitoramento'] ?? '')),
            'oxigenoterapia' => trim((string)($data['oxigenoterapia'] ?? '')),
            'nebulizacao' => trim((string)($data['nebulizacao'] ?? '')),
            'controle_ambiental' => trim((string)($data['controle_ambiental'] ?? '')),
            'alimentacao_hidratacao' => trim((string)($data['alimentacao_hidratacao'] ?? '')),
            'atividade_repouso' => trim((string)($data['atividade_repouso'] ?? '')),
            'medicamentos' => trim((string)($data['medicamentos'] ?? '')),
            'comunicacao_familia' => trim((string)($data['comunicacao_familia'] ?? '')),
            'sinais_alerta' => trim((string)($data['sinais_alerta'] ?? '')),
            'observacoes' => trim((string)($data['observacoes'] ?? '')),
        ];

        return $payload;
    }

    private function proximaVersao(int $pacienteId): int
    {
        if ($pacienteId <= 0) {
            return 1;
        }

        $versao = $this->query(
            "SELECT COALESCE(MAX(versao), 0) + 1
             FROM {$this->table}
             WHERE paciente_id = :paciente_id",
            [':paciente_id' => $pacienteId]
        )->fetchColumn();

        return max(1, (int)$versao);
    }

    private function inferirModelo(array $paciente): string
    {
        $texto = mb_strtolower((string)(($paciente['diagnostico'] ?? '') . ' ' . ($paciente['diagnostico_principal'] ?? '') . ' ' . ($paciente['cid_principal'] ?? '')), 'UTF-8');

        if (str_contains($texto, 'asma') || str_contains($texto, 'respirat') || str_starts_with(trim((string)($paciente['cid_principal'] ?? '')), 'J') || $this->sim($paciente['usa_oxigenio'] ?? 'Não')) {
            return 'respiratorio';
        }

        if ($this->sim($paciente['gtt'] ?? $paciente['gastrostomia'] ?? 'Não') || $this->sim($paciente['sne'] ?? 'Não')) {
            return 'gtt_sne';
        }

        $mobilidade = mb_strtolower((string)($paciente['mobilidade'] ?? ''), 'UTF-8');
        if (str_contains($mobilidade, 'acam')) {
            return 'acamado';
        }

        $cognicao = mb_strtolower((string)($paciente['estado_cognitivo_base'] ?? ''), 'UTF-8');
        if (str_contains($cognicao, 'dem') || str_contains($cognicao, 'confus')) {
            return 'demencia';
        }

        $idade = $this->idadeAnos($paciente['data_nascimento'] ?? null);
        if ($idade !== null && $idade < 18) {
            return 'pediatrico';
        }

        return 'geral';
    }

    private function modeloBase(string $modeloChave, array $ctx): array
    {
        $modelos = $this->templatesInternos($ctx);
        return $modelos[$modeloChave] ?? $modelos['geral'];
    }

    private function templatesInternos(array $ctx): array
    {
        $nomeResp = $ctx['responsavel'] ?? 'responsável';
        $telResp = $ctx['responsavel_telefone'] ?? 'telefone não informado';
        $via = $ctx['via_alimentar'] ?? 'VO';
        $oxigenio = !empty($ctx['tem_oxigenio']);

        return [
            'geral' => [
                'objetivos' => "Manter estabilidade clínica, segurança domiciliar, conforto, adesão às orientações da equipe e registro claro de intercorrências em cada plantão.",
                'monitoramento' => "Avaliar sinais gerais, queixas, dor, padrão de sono, alimentação, hidratação e eliminações em cada plantão. Registrar alterações no relatório de plantão e comunicar responsável quando houver piora ou intercorrência.",
                'oxigenoterapia' => $oxigenio ? "Manter equipamento de oxigênio disponível, limpo e funcionando. Usar somente conforme prescrição/orientação profissional e registrar necessidade, horário e resposta do paciente." : "Não há uso rotineiro de oxigenoterapia informado. Manter observação de desconforto respiratório e comunicar responsável/equipe se surgirem sinais de alerta.",
                'nebulizacao' => "Realizar nebulização somente quando houver prescrição/orientação registrada. Higienizar equipamento após o uso e registrar horário, solução/medicamento utilizado e resposta observada.",
                'controle_ambiental' => "Manter ambiente limpo, ventilado, seguro, com circulação livre e sem excesso de objetos que dificultem o cuidado. Observar riscos de queda, higiene do leito e conforto térmico.",
                'alimentacao_hidratacao' => "Via alimentar informada: {$via}. Ofertar alimentação e hidratação conforme orientação familiar/profissional. Registrar aceitação, náuseas, engasgos, vômitos ou recusa alimentar.",
                'atividade_repouso' => "Respeitar limites de mobilidade e fadiga. Estimular repouso adequado, posicionamento confortável e mudança de posição quando necessário.",
                'medicamentos' => "Conferir medicações conforme prescrição vigente. Nunca administrar medicamento fora da prescrição. Registrar horários, recusas, efeitos observados e intercorrências.",
                'comunicacao_familia' => "Manter comunicação clara com {$nomeResp} ({$telResp}). Registrar intercorrências no relatório de plantão e acionar responsável quando houver alteração relevante.",
                'sinais_alerta' => "Acionar responsável/equipe em caso de piora súbita, queda, febre persistente, desconforto respiratório, alteração importante de consciência, dor intensa ou qualquer evento fora do padrão do paciente.",
                'observacoes' => "Rascunho gerado automaticamente com base nos dados cadastrados do paciente. Revisar e validar antes de ativar.",
            ],
            'respiratorio' => [
                'objetivos' => "Manter estabilidade respiratória, reduzir risco de crise, reconhecer sinais precoces de desconforto e garantir comunicação rápida com família/equipe.",
                'monitoramento' => "Avaliar frequência respiratória, padrão ventilatório e sinais de esforço em cada plantão. Registrar presença de chiado, tosse persistente, cansaço, tiragem, cianose ou queda de saturação quando houver oxímetro disponível.",
                'oxigenoterapia' => "Manter equipamento de oxigênio disponível e funcionando. Verificar disponibilidade do cilindro/concentrador e acessórios. Usar oxigênio somente conforme prescrição/orientação profissional. Registrar uso, horário e resposta.",
                'nebulizacao' => "Realizar nebulização conforme prescrição/orientação registrada. Higienizar equipamento após cada uso. Observar resposta clínica e registrar horário, medicamento/solução e evolução dos sintomas.",
                'controle_ambiental' => "Manter ambiente livre de gatilhos respiratórios: poeira, mofo, fumaça, perfumes fortes, sprays, produtos de limpeza com odor intenso, tapetes e excesso de pelúcias. Ventilar sem exposição direta a frio intenso.",
                'alimentacao_hidratacao' => "Via alimentar informada: {$via}. Incentivar hidratação conforme orientação. Observar refluxo, engasgos ou recusa alimentar, pois podem piorar sintomas respiratórios em alguns pacientes.",
                'atividade_repouso' => "Evitar esforço físico em dias de crise ou desconforto respiratório. Favorecer repouso em posição confortável, com cabeceira elevada quando indicado e tolerado.",
                'medicamentos' => "Conferir medicações respiratórias conforme prescrição. Manter medicação de resgate acessível quando prescrita e dentro da validade. Nunca administrar medicamento fora da prescrição.",
                'comunicacao_familia' => "Orientar comunicação imediata com {$nomeResp} ({$telResp}) diante de piora respiratória, crise sem resposta à conduta prescrita, sonolência incomum, cianose ou dificuldade importante para falar/chorar.",
                'sinais_alerta' => "Acionar serviço de urgência se houver cianose, perda de consciência, dificuldade respiratória intensa, piora rápida, saturação muito baixa quando monitorada ou ausência de resposta às medidas prescritas.",
                'observacoes' => "Plano respiratório gerado como rascunho. Conferir limites de SpO₂, medicações, doses e condutas com prescrição/orientação profissional antes de ativar.",
            ],
            'pediatrico' => [
                'objetivos' => "Garantir segurança, conforto, rotina adequada à idade, comunicação com responsável e observação precoce de alterações clínicas.",
                'monitoramento' => "Observar comportamento, choro, irritabilidade, sonolência, alimentação, hidratação, eliminações e sinais de dor/desconforto. Registrar alterações no relatório de plantão.",
                'oxigenoterapia' => $oxigenio ? "Manter oxigênio disponível e usar somente conforme prescrição/orientação profissional. Crianças exigem atenção especial a dose, dispositivo e tolerância." : "Sem oxigenoterapia rotineira informada. Observar respiração, coloração de lábios e esforço ventilatório.",
                'nebulizacao' => "Realizar nebulização somente conforme prescrição. Higienizar máscara/copo após uso. Registrar horário, solução/medicamento e resposta da criança.",
                'controle_ambiental' => "Manter ambiente seguro, limpo, sem objetos pequenos soltos, fios expostos, produtos químicos acessíveis, mofo, fumaça ou odores fortes.",
                'alimentacao_hidratacao' => "Via alimentar informada: {$via}. Ofertar alimentos e líquidos conforme orientação familiar/profissional. Não forçar alimentação; registrar aceitação e intercorrências.",
                'atividade_repouso' => "Respeitar rotina, sono e limites da criança. Evitar esforço em períodos de indisposição. Manter supervisão constante durante brincadeiras e deslocamentos.",
                'medicamentos' => "Conferir prescrição, dose, horário e via antes de administrar. Nunca medicar fora da prescrição. Registrar recusa, vômito após medicação ou efeitos observados.",
                'comunicacao_familia' => "Manter contato com {$nomeResp} ({$telResp}) em caso de febre, piora respiratória, recusa alimentar persistente, sonolência excessiva, vômitos repetidos ou comportamento fora do habitual.",
                'sinais_alerta' => "Acionar urgência em caso de dificuldade respiratória intensa, cianose, perda de consciência, convulsão, desidratação importante ou piora rápida do estado geral.",
                'observacoes' => "Plano pediátrico gerado como rascunho. Validar rotinas, limites clínicos e condutas com responsável técnico antes de ativar.",
            ],
            'acamado' => [
                'objetivos' => "Prevenir lesão por pressão, manter conforto, higiene, hidratação, segurança e registro de alterações clínicas.",
                'monitoramento' => "Avaliar pele, dor, conforto, eliminações, aceitação alimentar, hidratação e sinais vitais conforme rotina. Registrar alterações e comunicar responsável.",
                'oxigenoterapia' => $oxigenio ? "Manter equipamento disponível e usar conforme prescrição/orientação profissional, observando posicionamento e conforto." : "Observar desconforto respiratório, tosse, secreções e alteração de padrão respiratório.",
                'nebulizacao' => "Realizar somente conforme prescrição/orientação registrada.",
                'controle_ambiental' => "Manter leito limpo, seco, seguro, com campainha/contato acessível quando aplicável. Evitar dobras em lençol e objetos pressionando pele.",
                'alimentacao_hidratacao' => "Via alimentar informada: {$via}. Observar aceitação, engasgos, náuseas, vômitos e hidratação.",
                'atividade_repouso' => "Realizar mudança de decúbito conforme orientação, observar pontos de pressão e manter alinhamento corporal confortável.",
                'medicamentos' => "Administrar conforme prescrição. Registrar horários e intercorrências.",
                'comunicacao_familia' => "Comunicar {$nomeResp} ({$telResp}) em caso de lesão de pele, febre, dor intensa, queda do estado geral ou alteração respiratória.",
                'sinais_alerta' => "Acionar suporte em caso de alteração súbita de consciência, desconforto respiratório intenso, queda, sangramento, febre persistente ou piora importante.",
                'observacoes' => "Plano para paciente acamado gerado como rascunho. Validar frequência de mudanças de decúbito e curativos.",
            ],
            'gtt_sne' => [
                'objetivos' => "Garantir administração segura de dieta/medicações por dispositivo, prevenir obstrução, broncoaspiração e irritação local.",
                'monitoramento' => "Observar posicionamento do dispositivo, integridade da pele, náuseas, vômitos, distensão abdominal, tosse durante dieta e sinais de desconforto.",
                'oxigenoterapia' => $oxigenio ? "Manter oxigênio conforme prescrição/orientação e observar desconforto respiratório durante dieta." : "Observar sinais respiratórios, especialmente durante e após dieta.",
                'nebulizacao' => "Realizar somente conforme prescrição/orientação registrada.",
                'controle_ambiental' => "Manter área de preparo limpa, materiais organizados e cuidados de higiene antes do manuseio do dispositivo.",
                'alimentacao_hidratacao' => "Seguir prescrição de dieta, volume, velocidade e horários. Manter cabeceira elevada durante administração e após, conforme orientação. Registrar aceitação e intercorrências.",
                'atividade_repouso' => "Manter posicionamento seguro durante dieta e repouso. Evitar tração no dispositivo.",
                'medicamentos' => "Administrar medicações por dispositivo apenas quando prescritas para essa via. Realizar lavagem conforme orientação para evitar obstrução.",
                'comunicacao_familia' => "Comunicar {$nomeResp} ({$telResp}) em caso de saída do dispositivo, obstrução, vômitos, tosse intensa durante dieta ou irritação importante no local.",
                'sinais_alerta' => "Acionar suporte em caso de saída acidental de sonda/GTT, engasgo importante, suspeita de broncoaspiração, vômitos persistentes ou desconforto respiratório.",
                'observacoes' => "Plano para GTT/SNE gerado como rascunho. Conferir prescrição nutricional e cuidados específicos do dispositivo.",
            ],
            'demencia' => [
                'objetivos' => "Manter segurança, rotina previsível, conforto emocional, prevenção de quedas e redução de agitação.",
                'monitoramento' => "Observar comportamento, orientação, sono, alimentação, hidratação, agitação, risco de fuga, quedas e alterações súbitas do estado mental.",
                'oxigenoterapia' => $oxigenio ? "Usar oxigênio conforme prescrição/orientação, observando tolerância e risco de retirada do dispositivo." : "Observar padrão respiratório e sinais de desconforto.",
                'nebulizacao' => "Realizar somente conforme prescrição, explicando com calma e mantendo supervisão.",
                'controle_ambiental' => "Manter ambiente seguro, iluminado, sem tapetes soltos, fios expostos ou objetos que favoreçam queda. Reduzir ruídos e estímulos excessivos.",
                'alimentacao_hidratacao' => "Ofertar alimentação com calma, respeitando ritmo do paciente. Observar engasgos, recusa, desidratação e perda de apetite.",
                'atividade_repouso' => "Manter rotina de sono e atividades simples, evitando confrontos. Estimular mobilidade segura conforme tolerância.",
                'medicamentos' => "Administrar conforme prescrição. Observar sonolência, agitação paradoxal, recusa ou efeitos colaterais.",
                'comunicacao_familia' => "Comunicar {$nomeResp} ({$telResp}) em caso de agitação intensa, queda, fuga, agressividade incomum, febre ou alteração súbita de consciência.",
                'sinais_alerta' => "Acionar suporte em caso de queda com trauma, alteração súbita de consciência, febre com confusão importante, agressividade incontrolável ou risco imediato.",
                'observacoes' => "Plano para demência gerado como rascunho. Ajustar rotinas e gatilhos comportamentais específicos do paciente.",
            ],
            'pos_operatorio' => [
                'objetivos' => "Acompanhar recuperação, prevenir infecção, controlar dor conforme prescrição e estimular mobilização segura.",
                'monitoramento' => "Observar dor, temperatura, aspecto de curativo/incisão, sangramento, edema, secreção, mobilidade e aceitação alimentar.",
                'oxigenoterapia' => $oxigenio ? "Usar oxigênio conforme prescrição/orientação profissional." : "Observar desconforto respiratório, principalmente após esforço ou dor intensa.",
                'nebulizacao' => "Realizar somente conforme prescrição/orientação registrada.",
                'controle_ambiental' => "Manter ambiente limpo, seguro e materiais de curativo organizados conforme orientação.",
                'alimentacao_hidratacao' => "Via alimentar informada: {$via}. Estimular hidratação e alimentação conforme tolerância/orientação.",
                'atividade_repouso' => "Estimular repouso e mobilização conforme orientação. Evitar esforços não autorizados.",
                'medicamentos' => "Administrar analgésicos, antibióticos ou demais medicações conforme prescrição. Registrar resposta e queixas.",
                'comunicacao_familia' => "Comunicar {$nomeResp} ({$telResp}) em caso de febre, dor não controlada, sangramento, secreção purulenta, queda ou piora do estado geral.",
                'sinais_alerta' => "Acionar suporte em caso de sangramento intenso, falta de ar, dor torácica, febre alta, alteração de consciência ou sinais de infecção importante.",
                'observacoes' => "Plano pós-operatório gerado como rascunho. Validar restrições, curativos e retorno médico.",
            ],
            'acompanhante_hospitalar' => [
                'objetivos' => "Garantir presença, segurança, comunicação com equipe hospitalar e apoio ao paciente/família durante internação.",
                'monitoramento' => "Observar queixas, solicitações, alterações de comportamento, dor, alimentação, eliminações e orientações passadas pela equipe do hospital.",
                'oxigenoterapia' => "Não manipular oxigênio hospitalar sem orientação da equipe. Observar desconforto respiratório e acionar enfermagem do setor.",
                'nebulizacao' => "Acompanhar administração pela equipe ou conforme rotina hospitalar. Registrar quando orientado.",
                'controle_ambiental' => "Manter leito organizado, pertences seguros e comunicação respeitosa com equipe hospitalar.",
                'alimentacao_hidratacao' => "Acompanhar dieta liberada pela equipe. Não oferecer alimentos sem autorização hospitalar/familiar.",
                'atividade_repouso' => "Auxiliar repouso e deslocamentos somente quando permitido. Solicitar equipe em caso de risco de queda.",
                'medicamentos' => "Não administrar medicamentos por conta própria. Conferir com equipe hospitalar qualquer dúvida ou necessidade.",
                'comunicacao_familia' => "Manter família informada conforme combinado e registrar intercorrências relevantes.",
                'sinais_alerta' => "Acionar imediatamente enfermagem do setor em caso de queda, falta de ar, dor intensa, sangramento, confusão, piora súbita ou pedido urgente do paciente.",
                'observacoes' => "Plano de acompanhante hospitalar gerado como rascunho. Ajustar conforme normas da instituição.",
            ],
        ];
    }

    private function idadeTexto(?string $dataNascimento): string
    {
        $anos = $this->idadeAnos($dataNascimento);
        if ($anos === null) {
            return 'idade não informada';
        }
        return $anos . ' ano' . ($anos === 1 ? '' : 's');
    }

    private function idadeAnos(?string $dataNascimento): ?int
    {
        if (!$dataNascimento) {
            return null;
        }

        try {
            return (new \DateTime($dataNascimento))->diff(new \DateTime('today'))->y;
        } catch (\Throwable) {
            return null;
        }
    }

    private function sim(mixed $valor): bool
    {
        return mb_strtolower(trim((string)$valor), 'UTF-8') === 'sim';
    }

    private function valor(mixed $valor, string $fallback = '—'): string
    {
        $valor = trim((string)$valor);
        return $valor !== '' ? $valor : $fallback;
    }

    private function tabelaExiste(string $tabela): bool
    {
        $stmt = $this->query(
            "SELECT COUNT(*)
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :tabela",
            [':tabela' => $tabela]
        );

        return (int)$stmt->fetchColumn() > 0;
    }

    private function gerarUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
