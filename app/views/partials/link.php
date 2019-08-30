<?php
/**
 * @var \lib\DataTypes\Link $link
 * @var \lib\Repositories\TranslationRepository $lang
 */
$url = $link->getURL();
?>
<h1 class="display-4"><?= $lang->translate('LINK_CONFIRM') ?></h1>
<p class="lead"><?= $lang->translate('LINK_TARGET') ?>: <em><?= $url; ?></em></p>
<hr class="my-4">
<a id="LinkRedirect" href="<?= $url ?>" class="btn btn-primary btn-lg btn-block"><?= $lang->translate('LINK_FOLLOW') ?></a>
<script type="text/javascript">
    document.getElementById('LinkRedirect').focus();
</script>