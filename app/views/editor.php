<?php
/**
 * @var \lib\Repositories\TranslationRepository $lang
 * @var \lib\DataTypes\EditableFile $editableFile
 * @var bool $isWritable
 */

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

    <script type="text/javascript" src="<?= AFILE_LOCATION ?>node_modules/showdown/dist/showdown.min.js"></script>

    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.10/styles/github.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.10/highlight.min.js"></script>

    <script src="<?= AFILE_LOCATION ?>node_modules/@fortawesome/fontawesome-free/js/all.min.js"></script>

    <script src="<?= AFILE_LOCATION ?>node_modules/jquery/dist/jquery.slim.min.js"></script>
    <script src="<?= AFILE_LOCATION ?>node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="<?= AFILE_LOCATION ?>node_modules/bootstrap/dist/css/bootstrap.min.css">

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
                    <?php if ($isWritable): ?>
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

    <textarea id="EditorTextarea" data-fileid="<?= $editableFile->getFile()->getId() ?>" spellcheck="false" <?= $isWritable ? '' : 'readonly' ?>><?= $text ?></textarea>
</div>

<div class="container editor-preview <?= $openInPreview ? '' : 'd-none' ?>" style="margin-bottom: 100px;">
    <div class="row">
        <div class="col"></div>
        <div class="col-10">
            <h3><?= $editableFile->getFile()->getName() ?></h3>

            <div class="clearfix">
                <span class="h6" style="float: left; margin: 0;">
                    <?= $editableFile->getFile()->getReadableDate($lang) ?>
                </span>

                <div style="float: right;">
                    <?php if ($isWritable): ?>
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