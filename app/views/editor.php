<?php
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>aFile</title>

    <script type="text/javascript" src="<?= AFILE_LOCATION ?>vendor/showdown/dist/showdown.min.js"></script>

    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.10/styles/github.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.10/highlight.min.js"></script>

    <script src="<?= AFILE_LOCATION ?>vendor/@fortawesome/fontawesome-free/js/all.min.js"></script>
    <script src="<?= AFILE_LOCATION ?>vendor/vue/dist/vue.js"></script>

    <script src="<?= AFILE_LOCATION ?>vendor/jquery/dist/jquery.slim.min.js"></script>
    <script src="<?= AFILE_LOCATION ?>vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="<?= AFILE_LOCATION ?>vendor/bootstrap/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="<?= AFILE_LOCATION ?>vendor/github-markdown-css/github-markdown.css">

    <link rel="stylesheet" href="<?= AFILE_LOCATION ?>assets/general.css">
    <script type="text/javascript" src="<?= AFILE_LOCATION ?>assets/afile-ajax.js"></script>
    <script type="text/javascript" src="<?= AFILE_LOCATION ?>assets/afile-editor2.js" defer></script>

    <script type="text/javascript">
        let file = <?= $file ?>;
    </script>
</head>
<body>
<div id="Editor">
    <preview v-bind:file="file" v-if="preview" v-on:open-editor="preview = false"></preview>
    <editor v-bind:file="file" v-bind:message="savedMessage" v-if="!preview" v-on:save="save" v-on:close-editor="preview = true"></editor>
</div>

</body>
</html>
