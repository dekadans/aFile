<?php
/** @var \lib\DataTypes\FileToken $token */
/** @var \lib\DataTypes\File $file */
$lang = \lib\Translation::getInstance();
?>

<?php if ($token):
$skipExtension = \lib\Config::getInstance()->files->skip_dl_php_extension;
$link = AFILE_LOCATION . 'dl'. ($skipExtension ? '' : '.php') .'/' . $file->getStringId() . '/' . $token->getToken();
$inactive = $token->getActiveState() === \lib\DataTypes\FileToken::STATE_NONE;
?>
    <input type="text" onClick="this.select();" class="form-control" value="<?= $link ?>" spellcheck="false">
    <p class="mt-3 mb-0">
        <input type="checkbox" id="TokenActive" <?= ($inactive ? 'checked' : '') ?>> <label for="TokenActive"><?= $lang->translate('SHARE_DISABLE')?> </label>
    </p>
    <hr class="mt-1">
    <button id="DestroyToken" class="btn btn-danger btn-block"><?= $lang->translate('SHARE_DESTROY') ?></button>
<?php else: ?>

    <button id="CreateToken" class="btn btn-success btn-block"><?= $lang->translate('SHARE_CREATE') ?></button>

<?php endif; ?>
