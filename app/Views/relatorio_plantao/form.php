<?php

$rel = $relatorio ?? [];

$pacienteNome = $paciente['nome_completo'] ?? 'Paciente';
$enfermeiroNome = $enfermeiro['nome'] ?? 'Profissional';

?>

<form method="POST" action="<?= BASE_URL ?>/relatorio-plantao/store">

    <input type="hidden" name="_csrf" value="<?= $_csrf ?>">

    <div class="form-group">
        <label>Paciente</label>

        <select name="paciente_id" required>
            <option value="">Selecione</option>

            <?php foreach ($pacientes as $itemPaciente): ?>

            <option value="<?= $itemPaciente['id'] ?>" <?= !empty($pacienteSelecionado)
                                                                && (int)$pacienteSelecionado['id'] === (int)$itemPaciente['id']
                                                                ? 'selected'
                                                                : '' ?>>
                <?= htmlspecialchars($itemPaciente['nome_completo']) ?>
            </option>

            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Cuidador</label>

        <select name="cuidador_id">

            <option value="">Selecione</option>

            <?php foreach ($cuidadores as $cuidador): ?>

            <option value="<?= $cuidador['id'] ?>">
                <?= htmlspecialchars($cuidador['nome_completo']) ?>
            </option>

            <?php endforeach; ?>

        </select>
    </div>

    <div class="grid-2">

        <div class="form-group">
            <label>Início</label>
            <input type="datetime-local" name="data_inicio">
        </div>

        <div class="form-group">
            <label>Fim</label>
            <input type="datetime-local" name="data_fim">
        </div>

    </div>

    <div class="card">
        <h3>Sinais Vitais</h3>

        <input type="text" name="sv_pa" placeholder="PA">
        <input type="text" name="sv_fc" placeholder="FC">
        <input type="text" name="sv_temp" placeholder="Temperatura">
        <input type="text" name="sv_spo2" placeholder="SPO2">
        <input type="text" name="sv_hgt" placeholder="HGT">

        <textarea name="observacao_sinais"></textarea>
    </div>

    <div class="form-group">
        <label>Evolução</label>
        <textarea name="evolucao" rows="6"></textarea>
    </div>

    <div class="form-group">
        <label>Medicações</label>
        <textarea name="medicacoes" rows="4"></textarea>
    </div>

    <div class="form-group">
        <label>Eliminações</label>
        <textarea name="eliminacoes" rows="4"></textarea>
    </div>

    <div class="form-group">
        <label>Intercorrências</label>
        <textarea name="intercorrencias" rows="4"></textarea>
    </div>

    <input type="hidden" name="status" value="finalizado">

    <button class="btn-primary">
        Finalizar Relatório
    </button>

</form>