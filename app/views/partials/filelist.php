<?php foreach ($fileList as $file): ?>

    <tr class="listItem"
        data-id="<?= $file->getId() ?>"
        data-newtab="<?= $file->openFileInNewTab() ?>"
        data-stringid="<?= $file->getStringId() ?>">
        <td><span class="flaticon-<?= $file->getFileExtension() ?> flaticon-blank"></span></td>
        <td><?= $file->getName() ?></td>
        <td><?= $file->getSizeReadable() ?></td>
        <td><?= $file->getLastEdit() // TODO: Readable version eg. Idag kl 12:00 ?></td>
    </tr>

<?php endforeach; ?>
