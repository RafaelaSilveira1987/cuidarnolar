<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Login') ?> - Cuidar no Lar</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/ui_refino_v14.css') ?>">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <?= $content ?>
    </main>
</body>
</html>
