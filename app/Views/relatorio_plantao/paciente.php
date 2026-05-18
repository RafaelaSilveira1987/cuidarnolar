<div class="page-header">
    <h1><?= $paciente['nome_completo'] ?></h1>
    <p>Relatórios de plantão registrados.</p>
</div>

<a href="<?= BASE_URL ?>/relatorio-plantao/paciente/<?= $paciente['id'] ?>/novo" class="btn-primary">
    Novo Relatório
</a>

<div class="timeline-plantao">

    <?php if (empty($plantoes)): ?>

    <div class="empty-state">
        Nenhum relatório encontrado.
    </div>

    <?php else: ?>

    <?php foreach ($plantoes as $relatorio): ?>

    <div class="plantao-card">

        <div class="plantao-topo">

            <div>
                <strong>
                    <?= date('d/m/Y H:i', strtotime($relatorio['data_inicio'])) ?>
                </strong>

                <?php if (!empty($relatorio['data_fim'])): ?>
                <small>
                    até
                    <?= date('d/m/Y H:i', strtotime($relatorio['data_fim'])) ?>
                </small>
                <?php endif; ?>
            </div>

            <span class="status-badge">
                <?= htmlspecialchars($relatorio['status']) ?>
            </span>

        </div>

        <!-- SINAIS VITAIS -->

        <div class="sv-grid">

            <?php if (!empty($relatorio['pa'])): ?>
            <div class="sv-item">
                <small>PA</small>
                <strong><?= htmlspecialchars($relatorio['pa']) ?></strong>
            </div>
            <?php endif; ?>

            <?php if (!empty($relatorio['fc'])): ?>
            <div class="sv-item">
                <small>FC</small>
                <strong><?= htmlspecialchars($relatorio['fc']) ?></strong>
            </div>
            <?php endif; ?>

            <?php if (!empty($relatorio['temperatura'])): ?>
            <div class="sv-item">
                <small>TEMP</small>
                <strong><?= htmlspecialchars($relatorio['temperatura']) ?>°</strong>
            </div>
            <?php endif; ?>

            <?php if (!empty($relatorio['spo2'])): ?>
            <div class="sv-item">
                <small>SpO₂</small>
                <strong><?= htmlspecialchars($relatorio['spo2']) ?>%</strong>
            </div>
            <?php endif; ?>

            <?php if (!empty($relatorio['hgt'])): ?>
            <div class="sv-item">
                <small>HGT</small>
                <strong><?= htmlspecialchars($relatorio['hgt']) ?></strong>
            </div>
            <?php endif; ?>

        </div>

        <!-- EVOLUÇÃO -->

        <div class="plantao-body">
            <?= nl2br(htmlspecialchars($relatorio['evolucao'])) ?>
        </div>

    </div>

    <?php endforeach; ?>

    <?php endif; ?>

</div>