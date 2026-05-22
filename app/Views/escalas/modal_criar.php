<?php
/**
 * Views/escalas/modal_criar.php
 *
 * Incluído pelo index.php — tem acesso às mesmas variáveis:
 *   $pacientes, $colaboradores, $_csrf (injetado pelo BaseController via View::render)
 */

// Garante que $_csrf existe mesmo se o controller não passou explicitamente
// (o View::render do seu sistema já injeta _csrf no escopo — usamos ele aqui)
$csrfToken = $_csrf ?? '';
?>

<div id="modal-escala" class="modal-overlay" style="display:none" role="dialog" aria-modal="true"
    aria-labelledby="modal-escala-title">

    <div class="modal-box">

        <h2 class="modal-box__title" id="modal-escala-title">
            <i class="ti ti-calendar-plus" aria-hidden="true"></i>
            Alocar plantão
        </h2>

        <form method="POST" action="<?= BASE_URL ?>/escala/salvar" id="form-escala">

            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" id="modal_escala_id" name="escala_id">

            <!-- Paciente -->
            <div class="modal-form-group">
                <label for="modal_paciente_id">Paciente</label>
                <select id="modal_paciente_id" name="paciente_uuid" required>
                    <option value="">Selecione…</option>
                    <?php foreach ($pacientes ?? [] as $p): ?>
                    <option value="<?= htmlspecialchars($p['uuid']) ?>">
                        <?= htmlspecialchars($p['nome_completo']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Data e turno -->
            <div class="modal-form-row">
                <div class="modal-form-group">
                    <label for="modal_data_plantao">Data do plantão</label>
                    <input type="date" id="modal_data_plantao" name="data_plantao" required>
                </div>
                <div class="modal-form-group">
                    <label for="modal_turno">Turno</label>
                    <select id="modal_turno" name="turno" required>
                        <option value="">Selecione…</option>
                        <option value="diurno">Diurno (07h–19h)</option>
                        <option value="noturno">Noturno (19h–07h)</option>
                        <option value="24h">24 horas</option>
                        <option value="personalizado">Personalizado</option>
                    </select>
                </div>
            </div>

            <!-- Horário personalizado -->
            <div id="grupo-horario-custom" class="modal-form-row" style="display:none">
                <div class="modal-form-group">
                    <label for="modal_inicio">Início</label>
                    <input type="time" id="modal_inicio" name="inicio" value="07:00">
                </div>
                <div class="modal-form-group">
                    <label for="modal_fim">Fim</label>
                    <input type="time" id="modal_fim" name="fim" value="19:00">
                </div>
            </div>

            <!-- Cuidador -->
            <div class="modal-form-group">
                <label for="modal_colaborador_id">Cuidador</label>
                <select id="modal_colaborador_id" name="cuidador_uuid" required>
                    <option value="">Selecione…</option>
                    <?php foreach ($colaboradores ?? [] as $c): ?>
                    <option value="<?= htmlspecialchars($c['uuid']) ?>">
                        <?= htmlspecialchars($c['nome_completo']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Observação -->
            <div class="modal-form-group">
                <label for="modal_obs">Observação (opcional)</label>
                <textarea id="modal_obs" name="observacao" rows="2" style="resize:vertical"
                    placeholder="Ex.: levar medicação X…"></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" data-modal-close>Cancelar</button>
                <button type="submit" class="btn-primary">
                    <i class="ti ti-check" style="font-size:14px;margin-right:4px" aria-hidden="true"></i>
                    Salvar plantão
                </button>
            </div>

        </form>

    </div>
</div>

<script>
document.getElementById('modal_turno')?.addEventListener('change', function() {
    const g = document.getElementById('grupo-horario-custom');
    if (g) g.style.display = this.value === 'personalizado' ? 'grid' : 'none';
});
</script>