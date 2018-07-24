<?php
/** @var \lib\FileToken $token */
/** @var \lib\File $file */
/** @var array $L */
$lang = \lib\Translation::getInstance();
?>

<?php if ($token->exists()):
$skipExtension = \lib\Config::getInstance()->files->skip_dl_php_extension;
$link = AFILE_LOCATION . 'dl'. ($skipExtension ? '' : '.php') .'/' . $file->getStringId() . '/' . $token->getOpenToken();
?>
    <input type="text" onClick="this.select();" class="form-control" value="<?= $link ?>" spellcheck="false">
    <hr>
    <button id="DestroyToken" class="btn btn-danger btn-block"><?= $lang->translate('SHARE_DESTROY') ?></button>
<?php else: ?>

    <button id="CreateToken" class="btn btn-success btn-block"><?= $lang->translate('SHARE_CREATE') ?></button>

<?php endif; ?>
