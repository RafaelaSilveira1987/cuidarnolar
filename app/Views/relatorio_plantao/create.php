<?php
/**
 * app/Views/relatorio_plantao/create.php
 *
 * Variáveis disponíveis via extract($data):
 *   $paciente            array   — dados normalizados (nunca null)
 *   $pacienteSelecionado array|null — dado bruto do banco
 *   $pacientes           array   — lista para o <select>
 *   $cuidadores          array   — lista para o <select>
 *   $medicacoes          array   — []
 *   $relatorio           null
 *   $turno_atual         string
 *   $enfermeiro          array   — [nome, coren]
 *   $_user               array   — usuário logado
 *   $_csrf               string  — token CSRF
 */

$nomePaciente = $paciente['nome'] ?? '';
?>

<section class="page-header">
    <div>
        <h1>Novo Relatório de Plantão</h1>
        <?php if ($nomePaciente): ?>
        <p class="page-subtitle">
            Paciente: <strong><?= htmlspecialchars($nomePaciente) ?></strong>
        </p>
        <?php else: ?>
        <p class="page-subtitle">Selecione o paciente no formulário abaixo</p>
        <?php endif ?>
    </div>
    <a class="btn btn-secondary" href="<?= BASE_URL ?>/relatorio-plantao">← Voltar</a>
</section>

<section class="panel">
    <?php require __DIR__ . '/form.php'; ?>
</section>