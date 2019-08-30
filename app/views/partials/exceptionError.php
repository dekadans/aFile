<?php
/**
 * @var \Throwable $exception
 * @var \lib\Repositories\TranslationRepository $lang
 * @var \lib\Repositories\ConfigurationRepository $config
 */
?>
<div class="container py-5">
    <h2>Error!</h2>

    <?php if ($config->find('show_detailed_exceptions')): ?>

    <p><?= $exception->getMessage() ?></p>

    <p>
        <pre><?= $exception->getTraceAsString() ?></pre>
    </p>

    <p>
        <pre><?= print_r($exception, true) ?></pre>
    </p>

    <?php else: ?>

    <?= $lang->translate('EXCEPTION_MESSAGE') ?>

    <?php endif; ?>
</div>
