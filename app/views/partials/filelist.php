<?php foreach ($fileList as $file): ?>

    <tr class="listItem">
        <td><span class="flaticon-blank"></span></td>
        <td><?= $file->getName() ?></td>
        <td><?= $file->getSizeReadable() ?></td>
        <td><?= $file->getLastEdit() // TODO: Readable version eg. Idag kl 12:00 ?></td>
    </tr>

<?php endforeach; ?>
