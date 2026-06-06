<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= BASE_URL ?>">
    <title><?= e($pageTitle ?? 'Cuidar no Lar') ?> - ERP</title>
    <link rel="stylesheet" href="<?= url('/assets/css/app.css') ?>">
    <link rel="stylesheet" href="<?= url('/assets/css/financeiro.css') ?>">
    <link rel="stylesheet" href="<?= url('/assets/css/relatorio_plantao.css') ?>">
    <link rel="stylesheet" href="<?= url('/assets/css/responsaveis_paciente_patch.css') ?>">
    <link rel="stylesheet" href="<?= url('/assets/css/agendamentos.css') ?>">
    <link rel="stylesheet" href="<?= url('/assets/css/configuracoes.css') ?>">
    <link rel="stylesheet" href="<?= url('/assets/css/ui_refino_v14.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">


</head>

<body>
    <?php include BASE_PATH . '/app/Views/partials/header.php'; ?>
    <div class="layout">
        <?php include BASE_PATH . '/app/Views/partials/sidebar.php'; ?>
        <main class="main-content">
            <?php include BASE_PATH . '/app/Views/partials/alerts.php'; ?>
            <?= $content ?>
        </main>
    </div>

    <?php
    $empresaFooter = $empresaFooter ?? [];

    if (empty($empresaFooter)) {
        try {
            $empresaFooter = (new \App\Models\EmpresaConfig())->atual();
        } catch (\Throwable $e) {
            $empresaFooter = [];
        }
    }

    $empresaNome = trim((string)(
        $empresaFooter['nome_fantasia']
        ?? $empresaFooter['razao_social']
        ?? ''
    ));

    $empresaDescricao = trim((string)(
        $empresaFooter['descricao_sistema']
        ?? 'Gestão Home Care'
    ));

    if ($empresaNome === '') {
        $empresaNome = 'Cuidar no Lar';
    }
    ?>

    <footer class="app-footer">
        <div>
            <strong><?= e($empresaNome) ?></strong>
            <span><?= e($empresaDescricao) ?></span>
        </div>

        <div>
            <span>© <?= date('Y') ?> • Uso interno</span>
            <span class="app-footer-dot">•</span>
            <span>Versão 1.0</span>
        </div>
    </footer>

    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>

</html>