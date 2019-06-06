<?php
/**
 * @var \lib\DataTypes\File $file
 */
$lang = \lib\Translation::getInstance();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $lang->translate('DOWNLOAD_PASSWORD_TITLE') ?></title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= AFILE_LOCATION ?>assets/general.css">
</head>
<body>
<div class="cover-image">
    <div class="container py-5">
        <div class="jumbotron">
            <h1 class="display-4"><?= $file->getName() ?></h1>
            <p class="lead"><?= $lang->translate('DOWNLOAD_PASSWORD_TEXT') ?></p>

            <form action="" method="POST">
            <p><input class="form-control form-control-lg" autofocus name="password" type="password" placeholder="Password"></p>
            <p><button type="submit" class="btn btn-primary btn-lg btn-block"><?= $lang->translate('DOWNLOAD_PASSWORD_BUTTON') . ' (' . $file->getSizeReadable() .')'?></button></p>
            </form>
        </div>
    </div>
</div>
</body>
</html>