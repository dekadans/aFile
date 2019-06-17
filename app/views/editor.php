<?php
/**
 * @var \lib\DataTypes\EditableFile $editableFile
 */

$lang = \lib\Translation::getInstance();

$text = $editableFile->getText();
$openInPreview = $editableFile->hasPreview() && !empty($text);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $editableFile->getFile()->getName() ?></title>

    <script type="text/javascript" src=" https://cdn.rawgit.com/showdownjs/showdown/1.8.6/dist/showdown.min.js"></script>

    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/github.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js"></script>

    <script src="https://kit.fontawesome.com/aabf35e2be.js"></script>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <link rel="stylesheet" href="<?= AFILE_LOCATION ?>assets/general.css">
    <script type="text/javascript" src="<?= AFILE_LOCATION ?>assets/afile-ajax.js"></script>
    <script type="text/javascript" src="<?= AFILE_LOCATION ?>assets/afile-editor.js"></script>

    <script type="text/javascript">
        let f;
        $(function(){
            f = new aFileEditor();

            f.markdown = <?= $editableFile->isMarkdown() ? 'true' : 'false' ?>;
            f.code = <?= $editableFile->isCode() ? 'true' : 'false' ?>;
            f.parsePreview();
        });
    </script>
</head>
<body>

<div id="EditorContainer" class="<?= $openInPreview ? 'd-none' : '' ?>">

    <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#" id="BrandHome"><?= $editableFile->getFile()->getName() ?></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav mr-auto">
                    <?php if ($editableFile->isWritable()): ?>
                    <button id="EditorSave" class="btn btn-outline-success my-2 my-sm-0 <?= $openInPreview ? 'd-none' : '' ?>"><?= $lang->translate('EDITOR_SAVE') ?></button>
                    <?php endif; ?>

                    <span class="navbar-text ml-3 d-none" id="EditorSavedMessage"><?= $lang->translate('EDITOR_SAVED') ?></span>
                </div>
                <div class="navbar-nav">
                    <a id="EditorClose" class="nav-item nav-link <?= $editableFile->hasPreview() ? '' : 'd-none' ?>" href="#">
                        <i class="far fa-times-circle"></i>
                        <?= $lang->translate('EDITOR_CLOSE') ?>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <textarea id="EditorTextarea" data-fileid="<?= $editableFile->getFile()->getId() ?>" spellcheck="false" <?= $editableFile->isWritable() ? '' : 'readonly' ?>><?= $text ?></textarea>
</div>

<div class="container editor-preview <?= $openInPreview ? '' : 'd-none' ?>" style="margin-bottom: 100px;">
    <div class="row">
        <div class="col"></div>
        <div class="col-10">
            <h3><?= $editableFile->getFile()->getName() ?></h3>

            <div class="clearfix">
                <span class="h6" style="float: left; margin: 0;">
                    <?= $editableFile->getFile()->getReadableDateForFileList() ?>
                </span>

                <div style="float: right;">
                    <?php if ($editableFile->isWritable()): ?>
                    <a class="preview-toggle" href="#">
                        <i class="fas fa-edit"></i>
                        <?= $lang->translate('EDITOR_EDIT') ?>
                    </a>&nbsp;
                    <?php endif; ?>
                    <a id="EditorDownload" class="" href="<?= $editableFile->getForceDownloadLink() ?>">
                        <i class="fas fa-cloud-download-alt"></i>
                        <?= $lang->translate('EDITOR_DOWNLOAD') ?>
                    </a>
                </div>
            </div>
            <hr>
            <div id="EditorPreview"></div>
        </div>

        <div class="col"></div>
    </div>
</div>

</body>
</html>