<?php
$plantoes = $plantoes ?? [];
$registro = $registro ?? [];
$errors = $errors ?? [];
$value = static fn(string $key, string $default = ''): string => (string)($registro[$key] ?? $default);
$selected = static fn(string $key, string $expected): string => ((string)($registro[$key] ?? '') === $expected) ? 'selected' : '';
$checked = static fn(string $key, bool $default = true): string => array_key_exists($key, $registro) ? (!empty($registro[$key]) ? 'checked' : '') : ($default ? 'checked' : '');
$money = static function (mixed $valor): string {
    return function_exists('formatMoney') ? formatMoney((float)$valor) : 'R$ ' . number_format((float)$valor, 2, ',', '.');
};
$fmtHora = static fn(mixed $hora): string => $hora ? substr((string)$hora, 0, 5) : '—';
?>

<section class="cfg-page cfg-plantoes-page">
    <div class="cfg-header">
        <div>
            <span class="cfg-kicker">Configurações</span>
            <h1>Tabela de plantões</h1>
            <p>Base de valores para calcular pagamentos dos cuidadores. No fechamento ainda será possível ajustar valores fora da tabela.</p>
        </div>
    </div>

    <?php include __DIR__ . '/_subnav.php'; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <strong>Revise os campos:</strong>
            <ul>
                <?php foreach ($errors as $erro): ?>
                    <li><?= e($erro) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="cfg-layout-two cfg-layout-stack">
        <form method="POST" action="<?= url('/configuracoes/plantoes') ?>" class="cfg-card cfg-form cfg-sticky-form">
            <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">
            <input type="hidden" name="uuid" value="<?= e($value('uuid')) ?>">

            <div class="cfg-section-title compact">
                <h2><?= !empty($registro['uuid']) ? 'Editar regra' : 'Nova regra' ?></h2>
                <p>Use nomes claros. O financeiro agradece e o futuro eu também.</p>
            </div>

            <label>
                Nome da regra
                <input type="text" name="titulo" value="<?= e($value('titulo')) ?>" placeholder="Ex.: Plantão 12h diurno" required>
            </label>

            <div class="cfg-grid two compact-grid">
                <label>
                    Tipo
                    <select name="tipo_plantao">
                        <option value="6h" <?= $selected('tipo_plantao', '6h') ?>>6h</option>
                        <option value="8h" <?= $selected('tipo_plantao', '8h') ?>>8h</option>
                        <option value="12h" <?= $selected('tipo_plantao', '12h') ?>>12h</option>
                        <option value="24h" <?= $selected('tipo_plantao', '24h') ?>>24h</option>
                        <option value="Personalizado" <?= $selected('tipo_plantao', 'Personalizado') ?>>Personalizado</option>
                    </select>
                </label>
                <label>
                    Período
                    <select name="periodo">
                        <option value="Diurno" <?= $selected('periodo', 'Diurno') ?>>Diurno</option>
                        <option value="Noturno" <?= $selected('periodo', 'Noturno') ?>>Noturno</option>
                        <option value="24h" <?= $selected('periodo', '24h') ?>>24h</option>
                        <option value="Personalizado" <?= $selected('periodo', 'Personalizado') ?>>Personalizado</option>
                    </select>
                </label>
            </div>

            <div class="cfg-grid two compact-grid">
                <label>
                    Início
                    <input type="time" name="hora_inicio" value="<?= e(substr($value('hora_inicio'), 0, 5)) ?>">
                </label>
                <label>
                    Fim
                    <input type="time" name="hora_fim" value="<?= e(substr($value('hora_fim'), 0, 5)) ?>">
                </label>
            </div>

            <div class="cfg-grid two compact-grid">
                <label>
                    Valor cuidador
                    <input type="text" name="valor_cuidador" value="<?= e($value('valor_cuidador')) ?>" placeholder="0,00" required>
                </label>
                <label>
                    Extra/adicional
                    <input type="text" name="valor_extra" value="<?= e($value('valor_extra')) ?>" placeholder="0,00">
                </label>
            </div>

            <label>
                Ordem
                <input type="number" name="ordem" value="<?= e($value('ordem', '0')) ?>" min="0">
            </label>

            <label class="cfg-check">
                <input type="checkbox" name="ativo" value="1" <?= $checked('ativo', true) ?>>
                Regra ativa
            </label>

            <label>
                Observação
                <textarea name="descricao" rows="3" placeholder="Detalhe interno para fechamento."><?= e($value('descricao')) ?></textarea>
            </label>

            <div class="cfg-actions split">
                <a class="btn btn-secondary" href="<?= url('/configuracoes/plantoes') ?>">Limpar</a>
                <button type="submit" class="btn btn-primary"><?= !empty($registro['uuid']) ? 'Salvar regra' : 'Adicionar regra' ?></button>
            </div>
        </form>

        <div class="cfg-card cfg-table-card">
            <div class="cfg-section-title compact">
                <h2>Regras cadastradas</h2>
                <p>Esses valores serão a régua padrão do pagamento por plantão.</p>
            </div>

            <div class="cfg-table-wrap">
                <table class="cfg-table">
                    <thead>
                        <tr>
                            <th>Regra</th>
                            <th>Tipo</th>
                            <th>Horário</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($plantoes === []): ?>
                            <tr>
                                <td colspan="6" class="cfg-empty">Nenhuma regra cadastrada ainda.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($plantoes as $plantao): ?>
                            <tr>
                                <td>
                                    <strong><?= e($plantao['titulo'] ?? '') ?></strong>
                                    <?php if (!empty($plantao['descricao'])): ?>
                                        <small><?= e($plantao['descricao']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= e(($plantao['tipo_plantao'] ?? '') . ' · ' . ($plantao['periodo'] ?? '')) ?></td>
                                <td><?= e($fmtHora($plantao['hora_inicio'] ?? null)) ?> às <?= e($fmtHora($plantao['hora_fim'] ?? null)) ?></td>
                                <td>
                                    <strong><?= e($money($plantao['valor_cuidador'] ?? 0)) ?></strong>
                                    <?php if ((float)($plantao['valor_extra'] ?? 0) > 0): ?>
                                        <small>Extra: <?= e($money($plantao['valor_extra'])) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="cfg-badge <?= !empty($plantao['ativo']) ? 'ok' : 'muted' ?>">
                                        <?= !empty($plantao['ativo']) ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </td>
                                <td class="cfg-actions-cell">
                                    <a class="btn-icon" title="Editar" href="<?= url('/configuracoes/plantoes?editar=' . rawurlencode((string)($plantao['uuid'] ?? $plantao['id']))) ?>"><i class="ti ti-pencil"></i></a>
                                    <form method="POST" action="<?= url('/configuracoes/plantoes/' . rawurlencode((string)($plantao['uuid'] ?? $plantao['id'])) . '/alternar') ?>">
                                        <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">
                                        <button class="btn-icon" type="submit" title="Ativar/Inativar"><i class="ti ti-power"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
