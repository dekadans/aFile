<?php
/**
 * @var \lib\File $file
 * @var bool $editable
 */
$lang = \lib\Translation::getInstance();

$text = $file->getContent()->getAsText();

$preview = false;

if (in_array($file->getFileExtension(), \lib\Config::getInstance()->files->code)) {
    $preview = 'code';
}
else if ($file->getFileExtension() === 'md') {
    $preview = 'markdown';
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $file->getName() ?></title>

    <script type="text/javascript" src=" https://cdn.rawgit.com/showdownjs/showdown/1.8.6/dist/showdown.min.js"></script>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/default.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= AFILE_LOCATION ?>assets/general.css">
    <script type="text/javascript" src="<?= AFILE_LOCATION ?>assets/afile-ajax.js"></script>
    <script type="text/javascript" src="<?= AFILE_LOCATION ?>assets/afile-editor.js"></script>

    <script type="text/javascript">
        let f;
        $(function(){
            f = new aFileEditor(new showdown.Converter({
                simplifiedAutoLink : true,
                excludeTrailingPunctuationFromURLs : true,
                simpleLineBreaks : true,
                openLinksInNewWindow : true,
                emoji : true
            }));
        });
    </script>

    <?php if ($preview): ?>
        <script type="text/javascript">
            $(function(){
                f.<?= $preview ?> = true;
                f.togglePreview();
            });
        </script>
    <?php endif; ?>
</head>
<body>
<nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#" id="BrandHome"><?= $file->getName() ?></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            <div class="navbar-nav mr-auto">
                <?php if ($editable): ?>
                <button id="EditorSave" class="btn btn-outline-success my-2 my-sm-0"><?= $lang->translate('EDITOR_SAVE') ?></button>
                <?php endif; ?>

                <span class="navbar-text ml-3 d-none" id="EditorSavedMessage"><?= $lang->translate('EDITOR_SAVED') ?></span>
            </div>
            <div class="navbar-nav <?= $preview ? '' : 'd-none' ?>">
                <a id="EditorPreviewToggle" class="nav-item nav-link" href="#"><?= $lang->translate('EDITOR_TOGGLE_PREVIEW') ?></a>
            </div>
        </div>
    </div>
</nav>


<div class="container editor-preview d-none">
</div>

<div id="EditorContainer" class="container-fluid">
    <textarea id="EditorTextarea" data-fileid="<?= $file->getId() ?>" spellcheck="false" <?= $editable ? '' : 'readonly' ?>><?= $text ?></textarea>
</div>
</body>
</html>