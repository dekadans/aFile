<?php
/** @var \lib\DataTypes\FileToken $token */
/** @var \lib\DataTypes\File $file */
/** @var \lib\Repositories\ConfigurationRepository $config */
$lang = \lib\Translation::getInstance();
?>

<?php if ($token):
$skipExtension = $config->find('files', 'skip_dl_php_extension');
$link = AFILE_LOCATION . 'dl'. ($skipExtension ? '' : '.php') .'/' . $file->getStringId() . '/' . $token->getToken();
$restricted = $token->getActiveState() === \lib\DataTypes\FileToken::STATE_RESTRICTED;
$passwordIsSet = !empty($token->getPasswordHash());
?>
    <input type="text" onClick="this.select();" class="form-control" value="<?= $link ?>" spellcheck="false">
    <p class="mt-3 mb-0">
        <input type="checkbox" id="TokenRestrict" <?= ($restricted ? 'checked' : '') ?>> <label for="TokenRestrict"><?= $lang->translate('SHARE_DISABLE')?> </label>
    </p>

    <?php if ($passwordIsSet):  ?>

    <div id="TokenPassword" class="input-group mb-3 <?= (!$restricted ? 'd-none' : '') ?>">
        <input id="TokenPasswordInput" type="password" class="form-control" disabled placeholder="<?= $lang->translate('SHARE_PASSWORD_SAVED') ?>">
        <div class="input-group-append">
            <button class="btn btn-outline-secondary" type="button" id="TokenPasswordBtn"><?= $lang->translate('SHARE_PASSWORD_CLEAR') ?></button>
        </div>
    </div>

    <?php else: ?>

    <div id="TokenPassword" class="input-group mb-3 <?= (!$restricted ? 'd-none' : '') ?>">
        <input id="TokenPasswordInput" type="password" class="form-control" placeholder="<?= $lang->translate('SHARE_PASSWORD') ?>">
        <div class="input-group-append">
            <button class="btn btn-outline-secondary" type="button" id="TokenPasswordBtn"><?= $lang->translate('SHARE_PASSWORD_SAVE') ?></button>
        </div>
    </div>

    <?php endif; ?>

    <hr class="mt-1">
    <button id="DestroyToken" class="btn btn-danger btn-block"><?= $lang->translate('SHARE_DESTROY') ?></button>
<?php else: ?>

    <button id="CreateToken" class="btn btn-success btn-block"><?= $lang->translate('SHARE_CREATE') ?></button>

<?php endif; ?>
