<?php
/**
 * Views/escalas/modal_substituicao.php
 * Modal para registrar substituição de cuidador em um plantão.
 */
?>

<div id="modal-substituicao" class="modal-overlay" hidden role="dialog" aria-modal="true"
    aria-labelledby="modal-sub-title">

    <div class="modal-box">

        <h2 class="modal-box__title" id="modal-sub-title">
            <i class="ti ti-arrows-exchange" aria-hidden="true"></i>
            Registrar substituição
        </h2>

        <form method="POST" action="<?= BASE_URL ?>/escalas/substituir" id="form-substituicao">

            <input type="hidden" name="_csrf" value="<?= $_csrf ?>">
            <input type="hidden" id="sub_escala_id" name="escala_id">

            <!-- Cuidador original (somente leitura) -->
            <div class="modal-form-group">
                <label for="sub_colaborador_id">Cuidador original</label>
                <select id="sub_colaborador_id" name="colaborador_original_id" disabled>
                    <option value="">—</option>
                    <?php foreach ($colaboradores as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Data -->
            <div class="modal-form-group">
                <label for="sub_data">Data</label>
                <input type="date" id="sub_data" name="data_plantao" readonly style="background:#f9fafb;color:#6b7280">
            </div>

            <!-- Motivo -->
            <div class="modal-form-group">
                <label for="sub_motivo">Motivo da substituição</label>
                <select id="sub_motivo" name="motivo" required>
                    <option value="">Selecione…</option>
                    <option value="atestado">Atestado médico</option>
                    <option value="falta">Falta sem aviso</option>
                    <option value="folga_extra">Folga extra acordada</option>
                    <option value="emergencia">Emergência pessoal</option>
                    <option value="outro">Outro</option>
                </select>
            </div>

            <!-- Substituto -->
            <div class="modal-form-group">
                <label for="sub_substituto_id">Substituto</label>
                <select id="sub_substituto_id" name="substituto_id" required>
                    <option value="">Selecione o cuidador disponível…</option>
                    <?php foreach ($colaboradores as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Observação -->
            <div class="modal-form-group">
                <label for="sub_obs">Observação</label>
                <textarea id="sub_obs" name="observacao" rows="2" style="resize:vertical"
                    placeholder="Detalhes adicionais sobre a substituição…"></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" data-modal-close>Cancelar</button>
                <button type="submit" class="btn-primary">
                    <i class="ti ti-check" style="font-size:14px;margin-right:4px" aria-hidden="true"></i>
                    Confirmar substituição
                </button>
            </div>

            <button type="button" data-modal-close>Cancelar</button>

        </form>

    </div>
</div>