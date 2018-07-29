<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>404 <?= \lib\Translation::getInstance()->translate('404_NOT_FOUND') ?></title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= AFILE_LOCATION ?>assets/general.css">
</head>
<body>
<div class="cover-image">
    <div class="container py-5">
        <div class="jumbotron">
            <h1 class="display-4"><?= \lib\Translation::getInstance()->translate('404_NOT_FOUND') ?></h1>
            <p class="lead"><?= \lib\Translation::getInstance()->translate('404_NOT_FOUND_TEXT') ?></p>
            <hr class="my-4">
            <a href="<?= AFILE_LOCATION ?>" class="btn btn-primary"><?= \lib\Translation::getInstance()->translate('LOGIN') ?></a>
        </div>
    </div>
</div>
</body>
</html>