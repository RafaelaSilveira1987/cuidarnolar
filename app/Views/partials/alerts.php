<?php if (!empty($_flashes)): ?>
    <?php foreach ($_flashes as $type => $message): ?>
        <div class="alert alert-<?= e($type) ?>" role="alert">
            <?= e($message) ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
