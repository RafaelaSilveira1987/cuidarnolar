<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle">Detalhes completos do relatório de plantão</p>
    </div>
    <a class="btn btn-secondary" href="<?= url('/relatorio-plantao') ?>">Voltar</a>
</section>

<section class="panel">
    <div class="relatorio-detalhes">
        <!-- Cabeçalho do Paciente -->
        <div class="header-section">
            <h2>Paciente: <?= e($relatorio['nome_completo']) ?></h2>
            <div class="paciente-dados">
                <p><strong>Prontuário:</strong> <?= e($relatorio['prontuario']) ?></p>
                <p><strong>Data do Relatório:</strong> <?= date('d/m/Y', strtotime($relatorio['data'])) ?></p>
                <p><strong>Turno:</strong> <?= ucfirst(e($relatorio['turno'])) ?></p>
            </div>
        </div>

        <!-- Informações do Plantonista -->
        <div class="info-section">
            <h3>Informações do Plantonista</h3>
            <div class="grid grid-2">
                <div>
                    <p><strong>Nome:</strong> <?= e($relatorio['enfermeiro_nome']) ?></p>
                    <p><strong>COREN:</strong> <?= e($relatorio['enfermeiro_coren']) ?></p>
                </div>
                <div>
                    <p><strong>Status:</strong>
                        <span class="status status-<?= strtolower(str_replace(' ', '-', $relatorio['status'])) ?>">
                            <?= e($relatorio['status']) ?>
                        </span>
                    </p>
                    <p><strong>Assinado:</strong>
                        <?php if ($relatorio['assinado']): ?>
                        <span class="badge badge-success">✓ Sim</span>
                        <?php else: ?>
                        <span class="badge badge-warning">✗ Não</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Sinais Vitais -->
        <div class="info-section">
            <h3>Sinais Vitais</h3>
            <div class="grid grid-5">
                <?php
                $sinaisVitais = json_decode($relatorio['sinais_vitais_json'] ?? '[]', true);
                foreach ($sinaisVitais as $sinal):
                ?>
                <div class="vital-card sinal-<?= strtolower($sinal['status']) ?>">
                    <p class="vital-label"><?= e($sinal['label']) ?></p>
                    <p class="vital-value"><?= e($sinal['valor']) ?></p>
                    <p class="vital-unit"><?= e($sinal['unidade']) ?></p>
                    <p class="vital-status"><?= e($sinal['texto']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Medicações -->
        <div class="info-section">
            <h3>Medicações Administradas</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Medicamento</th>
                        <th>Via</th>
                        <th>Horário Previsto</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $medicacoes = json_decode($relatorio['medicacoes_json'] ?? '[]', true);
                    foreach ($medicacoes as $med):
                    ?>
                    <tr>
                        <td><?= e($med['nome']) ?></td>
                        <td><?= e($med['via']) ?></td>
                        <td><?= e($med['horario']) ?></td>
                        <td>
                            <?php if ($med['status'] === 'administrado'): ?>
                            <span class="badge badge-success">✓ Administrado</span>
                            <?php else: ?>
                            <span class="badge badge-warning">◯ Pendente</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Evolução de Enfermagem -->
        <div class="info-section">
            <h3>Evolução de Enfermagem (SOAP)</h3>
            <div class="evolucao-completa">
                <?= nl2br(e($relatorio['evolucao'])) ?>
            </div>
        </div>

        <!-- Intercorrências -->
        <div class="info-section">
            <h3>Intercorrências</h3>
            <?php
            $intercorrencias = json_decode($relatorio['intercorrencias_json'] ?? '[]', true);
            if (!empty($intercorrencias)):
            ?>
            <div class="intercorrencias-lista">
                <?php foreach ($intercorrencias as $inter): ?>
                <div class="intercorrencia-item">
                    <div class="inter-header">
                        <p class="inter-horario"><strong><?= e($inter['horario']) ?></strong></p>
                    </div>
                    <p class="inter-descricao"><?= e($inter['descricao']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-muted">Nenhuma intercorrência registrada neste turno.</p>
            <?php endif; ?>
        </div>

        <!-- Assinatura -->
        <div class="info-section assinatura-section">
            <h3>Assinatura</h3>
            <?php if ($relatorio['assinado']): ?>
            <div class="assinado-info">
                <p class="check-mark">✓</p>
                <div>
                    <p><strong>Assinado por:</strong> <?= e($relatorio['enfermeiro_nome']) ?></p>
                    <p><strong>COREN:</strong> <?= e($relatorio['enfermeiro_coren']) ?></p>
                    <p><strong>Data/Hora:</strong> <?= date('d/m/Y H:i:s', strtotime($relatorio['data_assinatura'])) ?>
                    </p>
                </div>
            </div>
            <?php else: ?>
            <p class="text-warning">Este relatório ainda não foi assinado.</p>
            <form method="POST" action="<?= url('/relatorio-plantao/' . $relatorio['id'] . '/assinar') ?>">
                <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">
                <label>
                    Informe o COREN para assinar:
                    <input type="text" name="enfermeiro_coren" required>
                </label>
                <button class="btn btn-primary" type="submit">Assinar Relatório</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
.relatorio-detalhes {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.header-section,
.info-section {
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.paciente-dados {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 1rem;
}

.grid-5 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.vital-card {
    padding: 1rem;
    border-radius: 8px;
    text-align: center;
    border: 1px solid #ddd;
    background: white;
}

.vital-card.sinal-normal {
    background: #d4edda;
    border-color: #28a745;
    color: #155724;
}

.vital-card.sinal-atencao {
    background: #fff3cd;
    border-color: #ffc107;
    color: #856404;
}

.vital-card.sinal-critico {
    background: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
}

.vital-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.vital-value {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 0.25rem;
}

.vital-unit {
    font-size: 0.8rem;
    opacity: 0.8;
    margin-bottom: 0.5rem;
}

.vital-status {
    font-size: 0.75rem;
    opacity: 0.9;
}

.evolucao-completa {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    line-height: 1.8;
    margin-top: 1rem;
}

.intercorrencias-lista {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 1rem;
}

.intercorrencia-item {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 1rem;
    border-radius: 6px;
}

.inter-header {
    margin-bottom: 0.5rem;
}

.inter-horario {
    color: #856404;
    font-size: 0.9rem;
}

.inter-descricao {
    color: #856404;
    line-height: 1.6;
}

.assinatura-section {
    border-left: 4px solid #28a745;
}

.assinado-info {
    display: flex;
    align-items: flex-start;
    gap: 2rem;
    background: #d4edda;
    padding: 1.5rem;
    border-radius: 8px;
    margin-top: 1rem;
}

.check-mark {
    font-size: 3rem;
    color: #28a745;
    line-height: 1;
}

.text-muted {
    color: #6c757d;
    font-style: italic;
}

.text-warning {
    color: #856404;
    padding: 1rem;
    background: #fff3cd;
    border-radius: 6px;
    margin-bottom: 1rem;
}

.badge {
    display: inline-block;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}
</style>