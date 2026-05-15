<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Cuidar no Lar') ?> - ERP</title>
    <link rel="stylesheet" href="<?= url('/assets/css/app.css') ?>">
    <link rel="stylesheet" href="<?= url('/assets/css/financeiro.css') ?>">
    <link rel="stylesheet" href="<?= url('/assets/css/relatorio_plantao.css') ?>">
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
    <script src="<?= assets('js/app.js') ?>"></script>
</body>

</html>