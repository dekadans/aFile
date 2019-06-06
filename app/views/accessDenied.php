<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>403 <?= \lib\Translation::getInstance()->translate('403_FORBIDDEN') ?></title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= AFILE_LOCATION ?>assets/general.css">
</head>
<body>
<div class="cover-image">
    <div class="container py-5">
        <div class="jumbotron">
            <h1 class="display-4"><?= \lib\Translation::getInstance()->translate('403_FORBIDDEN') ?></h1>
            <p class="lead"><?= \lib\Translation::getInstance()->translate('403_FORBIDDEN_TEXT') ?></p>
            <hr class="my-4">
            <a href="<?= AFILE_LOCATION ?>" class="btn btn-primary"><?= \lib\Translation::getInstance()->translate('LOGIN') ?></a>
        </div>
    </div>
</div>
</body>
</html>