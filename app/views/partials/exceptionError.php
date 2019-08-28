<?php
/** @var \Throwable $exception */
?>
<div class="container py-5">
    <h2>Error!</h2>

    <?php if (\lib\Config::getInstance()->get('show_detailed_exceptions')): ?>

    <p><?= $exception->getMessage() ?></p>

    <p>
        <pre><?= $exception->getTraceAsString() ?></pre>
    </p>

    <p>
        <pre><?= print_r($exception, true) ?></pre>
    </p>

    <?php else: ?>

    <?= \lib\Translation::getInstance()->translate('EXCEPTION_MESSAGE') ?>

    <?php endif; ?>
</div>
