<?php foreach ($fileList as $file): ?>
    <?php if ($file->isFile()): ?>

        <tr class="listItem file"
            data-id="<?= $file->getId() ?>"
            data-newtab="<?= $file->openFileInNewTab() ?>"
            data-stringid="<?= $file->getStringId() ?>">
            <td><span class="flaticon-<?= $file->getFileExtension() ?> flaticon-blank"></span></td>
            <td class="fileName"><?= $file->getName() ?></td>
            <td><?= $file->getSizeReadable() ?></td>
            <td><?= $file->getLastEdit() // TODO: Readable version eg. Idag kl 12:00 ?></td>
        </tr>

    <?php elseif ($file->isDirectory()): ?>

        <tr class="listItem directory" data-id="<?= $file->getId() ?>">
            <td><img class="directoryIcon" src="assets/filetypes/folder.svg" alt="Directory"></td>
            <td class="fileName" colspan="2"><?= $file->getName() ?></td>
            <td>&nbsp;</td>
        </tr>

    <?php endif; ?>
<?php endforeach; ?>
