<?php
/** @var \lib\FileToken $token */
/** @var \lib\File $file */
/** @var array $L */
$lang = \lib\Translation::getInstance();
?>

<?php if ($token->exists()):
$link = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . str_replace('ajax', 'dl', $_SERVER['PHP_SELF']) . '/' . $file->getStringId() . '/' . $token->getOpenToken();
?>
    <input type="text" onClick="this.select();" class="form-control" value="<?= $link ?>" spellcheck="false">
    <hr>
    <button id="DestroyToken" class="btn btn-danger btn-block"><?= $lang->translate('SHARE_DESTROY') ?></button>
<?php else: ?>

    <button id="CreateToken" class="btn btn-success btn-block"><?= $lang->translate('SHARE_CREATE') ?></button>

<?php endif; ?>
