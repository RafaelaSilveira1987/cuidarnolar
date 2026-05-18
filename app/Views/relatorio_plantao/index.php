<div class="rp-wrapper">

    <div class="rp-page-header">

        <div>
            <h1>
                Relatórios de Plantão
            </h1>

            <p>
                Pacientes com evoluções clínicas registradas.
            </p>
        </div>

    </div>

    <div class="page-actions">
        <a href="<?= BASE_URL ?>/relatorio-plantao/novo" class="btn-primary">
            + Novo Relatório
        </a>
    </div>

    <section class="rp-pacientes-grid">

        <?php foreach ($pacientes as $paciente): ?>

        <a href="<?= url('relatorio-plantao/paciente/' . $paciente['id']) ?>" class=" rp-paciente-card">

            <div class="rp-avatar">
                <?= strtoupper(substr($paciente['nome_completo'] ?? '', 0, 2)) ?>
            </div>

            <div class="rp-paciente-content">

                <h3>
                    <?= $paciente['nome_completo'] ?>
                </h3>

                <div class="rp-meta">

                    <span>
                        <?php
                            $idade = '-';

                            if (!empty($paciente['data_nascimento'])) {
                                $idade = date_diff(
                                    date_create($paciente['data_nascimento']),
                                    date_create('today')
                                )->y;
                            }
                            ?>

                        <?= $idade ?> anos
                    </span>

                </div>

                <p>
                    <?= $paciente['status'] ?>
                </p>

            </div>

            <div class="rp-status">

                <?= $paciente['status'] ?>

            </div>

        </a>

        <?php endforeach; ?>

    </section>

</div>