<?php
/**
 * @var \lib\Repositories\TranslationRepository $lang
 */
?>
<h1 class="display-4"><?= $lang->translate('403_FORBIDDEN') ?></h1>
<p class="lead"><?= $lang->translate('403_FORBIDDEN_TEXT') ?></p>
<hr class="my-4">
<a href="<?= AFILE_LOCATION ?>" class="btn btn-primary"><?= $lang->translate('LOGIN') ?></a>