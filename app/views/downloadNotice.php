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

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
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