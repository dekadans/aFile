<?php
/**
 * @var \lib\Repositories\TranslationRepository $lang
 */
?>
<h1 class="display-4"><?= $lang->translate('404_NOT_FOUND') ?></h1>
<p class="lead"><?= $lang->translate('404_NOT_FOUND_TEXT') ?></p>
<hr class="my-4">
<a href="<?= AFILE_LOCATION ?>" class="btn btn-primary"><?= $lang->translate('LOGIN') ?></a>