<?php
/**
 * @var \lib\DataTypes\File $file
 */
$lang = \lib\Translation::getInstance();
?>
<h1 class="display-4"><?= $file->getName() ?></h1>
<p class="lead"><?= $lang->translate('DOWNLOAD_PASSWORD_TEXT') ?></p>

<form action="" method="POST">
    <p><input class="form-control form-control-lg" autofocus name="password" type="password" placeholder="Password"></p>
    <p><button type="submit" class="btn btn-primary btn-lg btn-block"><?= $lang->translate('DOWNLOAD_PASSWORD_BUTTON') . ' (' . $file->getSizeReadable() .')'?></button></p>
</form>