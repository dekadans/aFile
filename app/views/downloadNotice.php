<?php
/**
 * @var string $title
 * @var string $partial
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $title ?></title>

    <link rel="stylesheet" href="<?= AFILE_LOCATION ?>vendor/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= AFILE_LOCATION ?>assets/general.css">
</head>
<body>
<div class="cover-image">
    <div class="container py-5">
        <div class="jumbotron">
            <?= $partial ?>
        </div>
    </div>
</div>
</body>
</html>