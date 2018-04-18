<div id="Loading" class="view"><?= $L['LOADING'] ?></div>

<div id="Progress" class="bg-success"></div>

<div id="Main" class="view">

        <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#" id="BrandHome"><?= $L['BRAND'] ?></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" id="Logout" href="#"><?= $L['LOGOUT'] ?></a>
                    </li>
                </ul>
                <div class="form-inline my-2 my-lg-0">
                    <input class="form-control mr-sm-2" id="SearchInput" type="search" placeholder="<?= $L['SEARCH'] ?>" aria-label="Search">
                    <button class="btn btn-outline-light my-2 my-sm-0" id="Search" type="submit"><?= $L['SEARCH_SHORT'] ?></button>
                </div>
            </div>
        </div>
    </nav>

    <div id="Menu">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <ol class="breadcrumb bg-light" id="Path">
                        <li class="breadcrumb-item"><a href="#" id="PathHome"><?= $L['HOME'] ?></a></li>
                    </ol>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="FileButtons" class="btn-group" role="group" aria-label="...">
                        <button id="Delete" type="button" class="btn btn-outline-dark"><i class="fas fa-trash-alt"></i></button>
                        <button id="Rename" type="button" class="btn btn-outline-dark"><i class="fas fa-edit"></i></button>
                        <button id="Share" type="button" class="btn btn-outline-dark"><i class="fas fa-share-alt"></i></button>
                        <button id="Download" type="button" class="btn btn-outline-dark"><i class="fas fa-cloud-download-alt"></i></button>
                    </div>

                    <div class="btn-group" role="group" aria-label="..." style="float:right;">
                        <button id="CreateDirectory" type="button" class="btn btn-outline-dark"><i class="fas fa-folder-open"></i></button>
                        <button id="OpenEditor" type="button" class="btn btn-outline-dark"><i class="fas fa-font"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container" id="ListContainer">
        <div class="alert alert-info" id="Clipboard">
            <button id="ClipboardDismiss" type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <span id="ClipboardText"><?= $L['CLIPBOARD'] ?></span>
            <button id="ClipboardPaste" class="btn btn-xs btn-success"><?= $L['CLIPBOARD_PASTE'] ?></button>
            <button id="ClipboardDelete" class="btn btn-xs btn-danger"><?= $L['CLIPBOARD_DELETE'] ?></button>
        </div>

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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= $L['CANCEL'] ?></button>
                    <button type="button" class="btn btn-primary" id="ModalOk"><?= $L['OK'] ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalEditor" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalEditorTitle"><?= $L['EDITOR'] ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="ModalEditorBody">
                    <p><input id="EditorName" type="text" placeholder="<?= $L['EDITOR_NAME'] ?>" class="form-control"></p>
                    <p><textarea id="Editor" class="form-control" spellcheck="false"></textarea></p>
                    <input type="hidden" id="EditorFileId" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" id="ModalEditorClose" data-dismiss="modal"><?= $L['EDITOR_CLOSE'] ?></button>
                    <button type="button" class="btn btn-primary" id="ModalEditorSave"><?= $L['EDITOR_SAVE'] ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
