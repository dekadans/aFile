<div id="Loading" class="view"><?= $L['LOADING'] ?></div>

<div id="Progress"></div>

<div id="Main" class="view">
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#test" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a href="#" class="navbar-brand" id="BrandHome"><?= $L['BRAND'] ?></a>
            </div>
            <div class="navbar-collapse collapse" id="test" aria-expanded="true">
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a href="#" id="Search"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> <span class="visible-xs-inline"><?= $L['SEARCH'] ?></span></a>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <span class="glyphicon glyphicon-cog" aria-hidden="true"></span> <span class="visible-xs-inline"><?= $L['SETTINGS'] ?></span> <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="#">Something else here</a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="#" id="Logout"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> <?= $L['LOGOUT'] ?></a></li>
                          </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div id="Menu">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <ol class="breadcrumb" id="Path">
                        <li><a href="#" id="PathHome"><?= $L['HOME'] ?></a></li>
                    </ol>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="FileButtons" class="btn-group" role="group" aria-label="...">
                        <button id="Delete" type="button" class="btn btn-default"><span class="glyphicon glyphicon-trash"></span></button>
                        <button id="Rename" type="button" class="btn btn-default"><span class="glyphicon glyphicon-edit"></span></button>
                        <button id="Share" type="button" class="btn btn-default"><span class="glyphicon glyphicon-share"></span></button>
                        <button id="Download" type="button" class="btn btn-default"><span class="glyphicon glyphicon-download-alt"></span></button>
                    </div>

                    <div class="btn-group" role="group" aria-label="..." style="float:right;">
                        <button id="CreateDirectory" type="button" class="btn btn-default"><span class="glyphicon glyphicon-folder-open"></span></button>
                        <button type="button" class="btn btn-default"><span class="glyphicon glyphicon-link"></span></button>
                        <button id="OpenEditor" type="button" class="btn btn-default"><span class="glyphicon glyphicon-font"></span></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="alert alert-info" id="Clipboard">
            <button id="ClipboardDismiss" type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <span id="ClipboardText"><?= $L['CLIPBOARD'] ?></span>
            <button id="ClipboardPaste" class="btn btn-xs btn-success"><?= $L['CLIPBOARD_PASTE'] ?></button>
            <button id="ClipboardDelete" class="btn btn-xs btn-danger"><?= $L['CLIPBOARD_DELETE'] ?></button>
        </div>

        <table id="List">
        </table>
    </div>


    <div class="modal fade" id="Modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="ModalTitle"></h4>
                </div>
                <div class="modal-body" id="ModalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" id="ModalCancel" data-dismiss="modal"><?= $L['CANCEL'] ?></button>
                    <button type="button" class="btn btn-primary" id="ModalOk"><?= $L['OK'] ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalEditor" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="ModalEditorTitle"><?= $L['EDITOR'] ?></h4>
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
