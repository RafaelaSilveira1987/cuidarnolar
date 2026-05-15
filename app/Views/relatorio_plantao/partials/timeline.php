<div class="timeline-scroll">

    <?php if(!empty($plantoes)): ?>

    <?php foreach($plantoes as $plantao): ?>

    <div class="plantao-card">

        <div class="plantao-top">

            <span>
                <?= $plantao['tipo_plantao'] ?>
            </span>

            <span class="badge badge-ok">
                <?= $plantao['status'] ?>
            </span>

        </div>

        <div class="plantao-cuidador">

            <?= $plantao['cuidador'] ?>

        </div>

        <div class="plantao-horario">

            <?= date(
                        'H:i',
                        strtotime($plantao['data_inicio'])
                    ) ?>

            →

            <?= date(
                        'H:i',
                        strtotime($plantao['data_fim'])
                    ) ?>

        </div>

    </div>

    <?php endforeach; ?>

    <?php else: ?>

    <div class="plantao-card">

        Nenhum plantão encontrado

    </div>

    <?php endif; ?>

</div>