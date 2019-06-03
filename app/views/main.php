<?php
$lang = \lib\Translation::getInstance();
?>

<div id="Loading" class="view"><?= $lang->translate('LOADING') ?></div>

<div id="Main" class="view">

    <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#" id="BrandHome"><?= $lang->translate('BRAND') ?></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" id="Logout" href="#"><?= $lang->translate('LOGOUT') ?></a>
                    </li>
                </ul>
                <div class="form-inline my-2 my-lg-0">
                    <input class="form-control mr-sm-2" id="SearchInput" type="search" placeholder="<?= $lang->translate('SEARCH') ?>" aria-label="Search">
                    <button class="btn btn-outline-light my-2 my-sm-0" id="Search" type="submit"><?= $lang->translate('SEARCH_SHORT') ?></button>
                </div>
            </div>
        </div>
    </nav>

    <div id="Menu">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <ol class="breadcrumb bg-light" id="Path">
                        <li class="breadcrumb-item"><a href="#" id="PathHome"><?= $lang->translate('HOME') ?></a></li>
                    </ol>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12" id="ButtonBar">
                    <div id="FileButtons" class="btn-group" role="group" aria-label="...">
                        <button id="Delete" type="button" class="btn btn-outline-dark"><i class="fas fa-trash-alt"></i></button>
                        <button id="Rename" type="button" class="btn btn-outline-dark"><i class="fas fa-edit"></i></button>
                        <button id="Share" type="button" class="btn btn-outline-dark"><i class="fas fa-share-alt"></i></button>
                        <button id="Download" type="button" class="btn btn-outline-dark"><i class="fas fa-cloud-download-alt"></i></button>
                    </div>

                    <div style="float:right;">
                        <div id="ClipboardButtons" class="btn-group" role="group">
                            <button id="ClipboardMenu" type="button" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-clipboard"></i>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="ClipboardMenu">
                                <a href="#" id="ClipboardPaste" class="dropdown-item text-success"><?= $lang->translate('CLIPBOARD_PASTE') ?></a>
                                <a href="#" id="ClipboardDelete" class="dropdown-item text-danger"><?= $lang->translate('CLIPBOARD_DELETE') ?></a>
                                <a href="#" id="ClipboardDismiss" class="dropdown-item"><?= $lang->translate('CANCEL') ?></a>
                                <div class="dropdown-divider"></div>
                                <span id="ClipboardFileList"></span>
                            </div>
                        </div>


                        <div class="btn-group" role="group" aria-label="...">
                            <button id="Gallery" type="button" class="btn btn-outline-dark"><i class="fas fa-images"></i></button>
                            <button id="CreateDirectory" type="button" class="btn btn-outline-dark"><i class="fas fa-folder-open"></i></button>
                            <button id="OpenEditor" type="button" class="btn btn-outline-dark"><i class="fas fa-font"></i></button>

                            <div class="btn-group" role="group">
                                <button id="SortMenu" type="button" class="btn btn-outline-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-sort-amount-down"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="SortMenu">
                                    <a class="dropdown-item sortby <?= ($currentSorting === \lib\Sort::COLUMN_NAME ? 'active' : '') ?>" href="#" data-column="<?= \lib\Sort::COLUMN_NAME ?>"><?= $lang->translate('SORT_NAME') ?></a>
                                    <a class="dropdown-item sortby <?= ($currentSorting === \lib\Sort::COLUMN_SIZE ? 'active' : '') ?>" href="#" data-column="<?= \lib\Sort::COLUMN_SIZE ?>"><?= $lang->translate('SORT_SIZE') ?></a>

                                    <?php if (\lib\Config::getInstance()->presentation->upload_date_in_list): ?>
                                    <a class="dropdown-item sortby <?= ($currentSorting === \lib\Sort::COLUMN_DATE_UPLOAD ? 'active' : '') ?>" href="#" data-column="<?= \lib\Sort::COLUMN_DATE_UPLOAD ?>"><?= $lang->translate('SORT_DATE') ?></a>
                                    <?php else: ?>
                                    <a class="dropdown-item sortby <?= ($currentSorting === \lib\Sort::COLUMN_DATE_EDIT ? 'active' : '') ?>" href="#" data-column="<?= \lib\Sort::COLUMN_DATE_EDIT ?>"><?= $lang->translate('SORT_DATE') ?></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container" id="ListContainer">
        <div id="List">
        </div>
    </div>

    <div class="modal fade" id="Modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalTitle"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="ModalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= $lang->translate('CANCEL') ?></button>
                    <button type="button" class="btn btn-primary" id="ModalOk"><?= $lang->translate('OK') ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
