<?php for($i = 0; $i < count($list); $i++): ?>

    <tr class="listItem">
        <td><span class="flaticon-blank"></span></td>
        <td><?= $list[$i]->getName() ?></td>
        <td><?= $list[$i]->getSizeReadable() ?></td>
        <td><?= $list[$i]->getLastEdit() ?></td>
    </tr>

<?php endfor; ?>
