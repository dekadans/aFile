<?php
/** @var \lib\FileList $fileList */
/** @var string $printPath */
foreach ($fileList as $file): ?>
    <?php if ($file->isFile()): ?>

        <tr class="listItem file"
            data-id="<?= $file->getId() ?>"
            data-newtab="<?= $file->openFileInNewTab() ?>"
            data-stringid="<?= $file->getStringId() ?>"
            data-mime="<?= $file->getMime() ?>"
            title="<?= ($printPath ? base64_decode($file->getLocation()) : '') ?>">
            <td>
                <?php if ($file->getEncryption() === \lib\File::ENCRYPTION_TOKEN): ?>
                <span class="glyphicon glyphicon-link hasToken"></span>
                <?php endif; ?>
                <span class="flaticon-<?= $file->getFileExtension() ?> flaticon-blank fileIcon"></span>
            </td>
            <td class="fileName"><?= $file->getName() ?></td>
            <td><?= $file->getSizeReadable() ?></td>
            <td><?= $file->getLastEdit() // TODO: Readable version eg. Idag kl 12:00 ?></td>
        </tr>

    <?php elseif ($file->isDirectory()): ?>

        <tr class="listItem directory" data-id="<?= $file->getId() ?>">
            <td><img class="directoryIcon" src="assets/filetypes/folder.svg" alt="Directory"></td>
            <td class="fileName"><?= $file->getName() ?></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>

    <?php endif; ?>
<?php endforeach; ?>
