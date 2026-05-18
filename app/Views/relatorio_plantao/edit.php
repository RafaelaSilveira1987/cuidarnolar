<?php
/**
 * app/Views/relatorio_plantao/edit.php
 *
 * Variáveis disponíveis via extract($data):
 *   $paciente            array   — dados normalizados
 *   $pacienteSelecionado array|null
 *   $pacientes           array
 *   $cuidadores          array
 *   $medicacoes          array
 *   $relatorio           array   — dados do relatório existente
 *   $turno_atual         string
 *   $enfermeiro          array
 */

$nomePaciente = $paciente['nome'] ?? '';
$relatorioId  = (int)($relatorio['id'] ?? 0);
?>

<section class="page-header">
    <div>
        <h1>Editar Relatório de Plantão</h1>
        <?php if ($nomePaciente): ?>
        <p class="page-subtitle">
            Paciente: <strong><?= htmlspecialchars($nomePaciente) ?></strong>
        </p>
        <?php endif ?>
    </div>
    <a class="btn btn-secondary" href="<?= BASE_URL ?>/relatorio-plantao/paciente/<?= (int)($paciente['id'] ?? 0) ?>">←
        Voltar</a>
</section>

<section class="panel">
    <?php require __DIR__ . '/form.php'; ?>
</section>