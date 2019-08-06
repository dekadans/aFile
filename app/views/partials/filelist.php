<ul class="list-group list-group-flush">
<?php
/** @var \lib\DataTypes\FileList $fileList */

foreach ($fileList as $file): ?>
    <?php if ($file->isFile() || $file->isLink()): ?>

        <li class="list-group-item py-0 listItem file"
            data-id="<?= $file->getId() ?>"
            data-newtab="<?= ($file->isInlineDownload() || $file->isEditable()) ?>"
            data-stringid="<?= $file->getStringId() ?>"
            data-mime="<?= $file->getMime() ?>">
            <div class="row align-items-center">
                <div class="col-1 pl-0 pl-sm-3">
                    <?php if ($file->isFile()): ?>
                        <span class="flaticon-<?= $file->getFileExtension() ?> flaticon-blank fileIcon"></span>
                    <?php elseif ($file->isLink()): ?>
                        <img class="linkIcon" src="assets/filetypes/link.svg" alt="Link">
                    <?php endif; ?>
                </div>
                <div class="col-7 col-sm-8 pl-3 pl-md-0">
                    <span class="fileName align-middle"><?= $file->getName() ?></span>
                </div>
                <div class="col-1 text-right">
                    <?php if ($file->getEncryption() === lib\Repositories\EncryptionKeyRepository::ENCRYPTION_TOKEN): ?>
                        <span class="badge badge-secondary badge-pill"><i class="fas fa-share-alt"></i></span>
                    <?php endif; ?>
                </div>
                <div class="col text-right">
                    <span class=""><?= $file->getSizeReadable() ?></span><br>
                    <small class="d-none d-sm-block"><span><?= $file->getReadableDateForFileList() ?></span></small>
                </div>
            </div>
        </li>

    <?php elseif ($file->isDirectory()): ?>

        <li class="list-group-item py-0 listItem directory"
            data-id="<?= $file->getId() ?>">
            <div class="row h-100 align-items-center">
                <div class="col-1 pl-0 pl-sm-3">
                    <img class="directoryIcon" src="assets/filetypes/folder.svg" alt="Directory">
                </div>
                <div class="col pl-3 pl-md-0">
                    <span class="fileName align-middle"><?= $file->getName() ?></span>
                </div>
            </div>
        </li>

    <?php endif; ?>
<?php endforeach; ?>
</ul>