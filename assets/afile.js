class aFile {
    constructor() {
        this.info = null; // Data fetched from the server
        this.path = [];
        this.currentSearch = '';
        this.selected = null;
        this.clipboard = [];
        this.currentUploads = [];
        this.clickLock = false;

        this.keybindings();
        this.check();
    }

    /**
     * Fetches config and session info from the server.
     */
    check() {
        this.get('Check', '', data => {
            this.info = data;

            if (this.info.login) {
                this.get('ListFiles', '', html => {
                    $('body').html(html);
                    this.mainView();
                });
            }
            else {
                this.get('Login', 'form', html => {
                    $('body').html(html);
                    this.loginView();
                });
            }
        });
    }

    /**
     * Bind various keyboard shortcuts
     */
    keybindings() {
        $(document).keydown(e => {
            if (!$('#Modal, #ModalEditor').is(':visible')) {
                if (this.selected) {
                    if (e.which === 46) { // Delete
                        $('#Delete').click();
                    }
                    else if (e.which === 82) { // R
                        $('#Rename').click();
                    }
                    else if (e.which === 69) { // E
                        $('#OpenEditor').click();
                    }
                    else if (e.which === 13) { // Enter
                        this.selected.dblclick();
                    }
                    else if (e.which === 27) { // Escape
                        this.selectItem(null);
                    }
                    else if (e.which === 77 && this.selected.hasClass('file')) { // M
                        this.input(this.selected.data('mime'), value => {
                            this.get('Rename', 'changemime', data => {
                                this.list();
                            }, {id : this.selected.data('id'), mime : value});
                        });
                    }
                    else if (e.which === 88 && (e.ctrlKey || e.metaKey) && this.selected.hasClass('file')) { // Ctrl/Cmd + x
                        e.preventDefault();
                        let fileId = this.selected.data('id');

                        for (let i = 0; i < this.clipboard.length; i++) {
                            if (this.clipboard[i].id === fileId) {
                                return;
                            }
                        }

                        this.clipboard.push({
                            id : fileId,
                            name : this.selected.find('.fileName').text()
                        });
                        this.displayClipboard();
                    }
                }

                if (e.which === 38) { // Up
                    e.preventDefault();
                    if (this.selected) {
                        let prevInList = this.selected.prev();
                        if (prevInList.length) {
                            this.selectItem(prevInList);
                        }
                        let menuHeight = $('.navbar').outerHeight() + $('#Menu').outerHeight();

                        if (this.selected.position().top - $(window).scrollTop() - menuHeight < 0)
                        {
                            $(window).scrollTop($(window).scrollTop() - this.selected.outerHeight());
                        }
                    }
                    else {
                        let firstInList = $('.listItem:first');
                        if (firstInList.length) {
                            this.selectItem(firstInList);
                        }
                        $(window).scrollTop(0);
                    }
                }
                else if (e.which === 40) { // Down
                    e.preventDefault();
                    if (this.selected) {
                        let nextInList = this.selected.next();
                        if (nextInList.length) {
                            this.selectItem(nextInList);
                        }

                        if ($(window).scrollTop() + $(window).height() < $(window).scrollTop() + this.selected.position().top + this.selected.outerHeight()) {
                            $(window).scrollTop($(window).scrollTop() + this.selected.outerHeight());
                        }
                    }
                    else {
                        let firstInList = $('.listItem:first');
                        if (firstInList.length) {
                            this.selectItem(firstInList);
                        }
                        $(window).scrollTop(0);
                    }
                }
                else if (e.which === 8) {
                    if (this.currentSearch.length) {
                        this.currentSearch = '';
                        this.drawPath();
                        this.list();
                    }
                    else if (this.path.length > 0) {
                        this.path.pop();
                        this.drawPath();
                        this.list();
                    }
                }
                else if (e.which === 83) { // S
                    $('#Search').click();
                }
            }
            else if ($('#ModalEditor').is(':visible')) {
                // Ctrl/Cmd + s
                if (e.which === 83 && (e.ctrlKey || e.metaKey)) {
                    e.preventDefault();
                    $('#ModalEditorSave').click();
                }
            }
        });
    }

    /**
     * Setup the login view
     */
    loginView() {
        $('#LoginUsername').focus();

        $('#LoginButton').click(event => {
            let username = $('#LoginUsername').val();
            let password = $('#LoginPassword').val();

            if (username.length && password.length) {
                this.post('Login', '', data => {
                    if (data.error) {
                        $('#LoginMessage > .alert').html(data.error);
                        $('#LoginMessage').slideDown();
                    }
                    else if (data.status === 'ok') {
                        this.check();
                    }
                }, {username : username, password : password});
            }
        });

        $('#LoginPassword').keyup(e => {
            if (e.which === 13) {
                $('#LoginButton').click();
            }
        });
    }

    /**
     * Setup for the main file list view
     */
    mainView() {
        this.fileButtons();

        this.list();

        $('#PathHome, #BrandHome').click(e => {
            e.preventDefault();
            this.currentSearch = '';
            this.path = [];
            this.drawPath();
            this.list();
        });

        $('#Logout').click(e => {
            e.preventDefault();
            this.get('Logout', '', data => {
                this.check();
            });
        });

        $('#Search').click(e => {
            e.preventDefault();
            this.input(this.info.language.SEARCH, input => {
                if (input.length) {
                    this.currentSearch = input;
                    this.drawPath();
                    this.list();
                }
            }, this.currentSearch);
        });

        $('#Modal').on('shown.bs.modal', e => {
            if ($('#Modal').find('#ModalInput').length) {
                $('#ModalInput').focus();
            }
            else {
                $('#ModalOk').focus();
            }
        }).on('hidden.bs.modal', e => {
            $('#ModalCancel').show();
        });

        $('#ModalEditor').on('shown.bs.modal', e => {
            if ($('#EditorName').val().length === 0) {
                $('#EditorName').focus();
            }
            else {
                $('#Editor').focus();
            }
        });

        this.initiateDropZone();

        this.initiateEditor();

        this.initiateClipboard();
    }

    /**
     * Retrieves the list of files
     */
    list() {
        this.selectItem(null);
        let action, data;

        if (this.currentSearch.length) {
            action = 'search';
            data = {search : this.currentSearch};
        }
        else {
            action = 'list';
            data = {location : this.getPath()};
        }

        this.post('ListFiles', action, html => {
            $('#List').html(html);

            $('.listItem').click(e => {
                if (!this.clickLock) {
                    let clickedItem = $(e.target).closest('.listItem');

                    this.clickLock = true;

                    if (this.selected !== null && this.selected[0] === clickedItem[0]) {
                        this.selectItem(null);
                    }
                    else {
                        this.selectItem(clickedItem);
                    }
                    setTimeout(() => {
                        this.clickLock = false;
                    }, 100);
                }
            }).dblclick(e => {
                let clickedItem = $(e.target).closest('.listItem');

                if (this.selected === null) {
                    this.selectItem(clickedItem);
                }

                if (this.selected.hasClass('directory')) {
                    this.path.push(this.selected.find('.fileName').text());
                    this.drawPath();
                    this.selectItem(null);
                    this.list();
                }
                else if (this.selected.hasClass('file')) {
                    $('#Download').click();
                }
            });
        }, data);
    }

    /**
     * Sets the Jquery representation of a file in the list as selected
     * @param item
     */
    selectItem(item) {
        $('.listItem').removeClass('listItemActive');

        if (item === null) {
            this.selected = null;
            $('#FileButtons').find('button').prop('disabled', true);
        }
        else {
            this.selected = item;
            this.selected.addClass('listItemActive');
            $('#FileButtons').find('button').prop('disabled', false);

            if (this.selected.hasClass('directory')) {
                $('#Download, #Share').prop('disabled', true);
            }
        }
    }

    /**
     * Binds events for the menu buttons
     */
    fileButtons() {
        $('#FileButtons').find('button').prop('disabled', true);

        $('#Delete').click(e => {
            if (this.selected) {
                let message = this.info.language.CONFIRM_DELETE + ' ' + this.selected.find('.fileName').text() + '?';
                this.confirm(message, e => {
                    let id = this.selected.data('id');

                    let selectNextInList = this.selected.next();
                    if (selectNextInList.length === 0) {
                        selectNextInList = this.selected.prev();
                    }
                    if (selectNextInList.length === 0) {
                        selectNextInList = null;
                    }

                    this.get('Delete', '', data => {
                        if (data.error) {
                            alert(data.error);
                        }
                        else {
                            this.selected.remove();
                            this.selectItem(selectNextInList);
                        }
                    }, {id : id});
                });
            }
        });

        $('#Rename').click(e => {
            if (this.selected) {
                let currentNameElement = this.selected.find('.fileName');

                this.input(this.info.language.RENAME, value => {
                    this.post('Rename', '', data => {
                        if (data.error) {
                            alert(data.error);
                        }
                        else {
                            currentNameElement.text(value);
                        }
                    }, {id : this.selected.data('id'), name : value});
                }, currentNameElement.text());
            }
        });

        $('#Share').click(e => {
            if (this.selected) {
                let fileId = this.selected.data('id');

                $('#ModalTitle').text(this.info.language.SHARE);
                $('#ModalCancel').hide();
                $('#ModalOk').off('click').on('click', e => {
                    $('#Modal').modal('hide');
                });

                let loadShareDialog = () => {
                    this.get('Share', 'panel', html => {
                        $('#ModalBody').html(html);
                        $('#Modal').modal('show');

                        $('#CreateToken').click(e => {
                            this.get('Share', 'create', result => {
                                if (result.error) {
                                    alert(data.error);
                                }
                                else {
                                    loadShareDialog();
                                    this.list();
                                }
                            }, {id : fileId});
                        });

                        $('#DestroyToken').click(e => {
                            this.get('Share', 'destroy', result => {
                                if (result.error) {
                                    alert(data.error);
                                }
                                else {
                                    loadShareDialog();
                                    this.list();
                                }
                            }, {id : fileId});
                        });
                    }, {id : fileId});
                };

                loadShareDialog();
            }
        });

        $('#Download').click(e => {
            if (this.selected) {
                let url = 'dl.php/' + this.selected.data('stringid');

                if (this.selected.data('newtab')) {
                    window.open(url);
                }
                else {
                    window.document.location = url;
                }
            }
        });


        $('#CreateDirectory').click(e => {
            this.input(this.info.language.CREATE_DIRECTORY, value => {
                this.post('Create','directory', data => {
                    if (data.error) {
                        alert(data.error);
                    }
                    this.list();
                }, {location : this.getPath(), name : value});
            });
        });

        $('#OpenEditor').click(e => {
            if (this.selected) {
                $('#EditorFileId').val(this.selected.data('id'));

                this.get('Editor', 'read', data => {
                    if (data.error) {
                        alert(data.error);
                    }
                    else {
                        let editor = $('#Editor')[0];
                        $(editor).val(data.content);
                        editor.selectionStart = editor.selectionEnd = 0;

                        $('#EditorName').val(data.filename);
                        $('#ModalEditor').modal('show');
                    }
                }, {id : this.selected.data('id')});
            }
            else {
                $('#EditorName, #Editor, #EditorFileId').val('');
                $('#ModalEditor').modal('show');
            }
        });
    }

    /**
     * Setup of the upload functionality
     */
    initiateDropZone() {
        $('#Main').on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
        })
        .on('dragover dragenter', function() {
            //$form.addClass('is-dragover');
        })
        .on('dragleave dragend drop', function() {
            //$form.removeClass('is-dragover');
        })
        .on('drop', e => {
            let filesToUpload = e.originalEvent.dataTransfer.files;
            let uploadId = Math.random();

            let ajaxData = new FormData();

            if (filesToUpload) {
                $.each( filesToUpload, function(i, file) {
                    ajaxData.append('fileinput'+i, file );
                });
            }

            $.ajax({
                url: 'app/api.php?do=Upload&location=' + this.getPath(),
                type: 'POST',
                data: ajaxData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                complete: () => {
                    //console.log('complete');
                },
                success: data => {
                    if (data.error) {
                        alert(data.error);
                    }
                    delete this.currentUploads[uploadId];

                    if (data.status === 'confirm') {
                        let message = this.info.language.CONFIRM_OVERWRITE + ' ' + data.name + '?';
                        this.confirm(message, e => {
                            this.post('Upload', 'confirmoverwrite', overwriteResult => {
                                if (overwriteResult.error) {
                                    alert(overwriteResult.error);
                                }
                                this.list();
                            }, {newId : data.newId, oldId : data.oldId});
                        });
                    }

                    this.updateProgress();
                    this.list();
                },
                error: () => {
                    alert('Upload Error');
                    delete this.currentUploads[uploadId];
                    this.updateProgress();
                    this.list();
                },
                xhr: () => {
                    let xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", evt => {
                        if (evt.lengthComputable) {
                            this.currentUploads[uploadId] = Math.ceil(evt.loaded / evt.total * 100);
                            this.updateProgress();
                        }
                    }, false);

                    return xhr;
                }
            });
        });
    }


    /**
     * Updates the upload progress bar
     */
    updateProgress() {
        let combinedProgress = 0;
        let uploads = 0;

        for (let percent in this.currentUploads) {
            uploads++;
            combinedProgress += this.currentUploads[percent];
        }

        if (uploads > 0) {
            combinedProgress /= uploads;
        }

        $('#Progress').css('width', combinedProgress + '%');
    }

    /**
     * Sets up the texteditor modal
     */
    initiateEditor() {
        $('#ModalEditorSave').click(e => {
            let filename = $('#EditorName').val();
            let content = $('#Editor').val();
            let fileId = $('#EditorFileId').val();

            if (filename.length > 0 && content.length > 0) {
                if (fileId !== '') {
                    this.post('Editor', 'write', data => {
                        if (data.error) {
                            alert(data.error);
                        }
                        else {
                            this.list();
                        }
                    }, {filename : filename, content : content, id : fileId});
                }
                else {
                    this.post('Editor', 'create', data => {
                        if (data.error) {
                            alert(data.error);
                        }
                        else {
                            $('#ModalEditor').modal('hide');
                            this.list();
                        }
                    }, {filename : filename, content : content, location : this.getPath()});
                }
            }
        });

        $('#Editor').keydown(e => {
            let start = e.target.selectionStart;
            let end = e.target.selectionEnd;
            let value = $(e.target).val();

            if (e.which === 9) {
                $(e.target).val(value.substring(0, start)
                    + "\t"
                    + value.substring(end));

                e.target.selectionStart = e.target.selectionEnd = start + 1;
                e.preventDefault();
            }
            else if (e.which === 13) {
                e.preventDefault();

                let countTabs = 0;
                for (let i = start-1; i>=0; i--) {
                    if (value.charAt(i) === "\t") {
                        countTabs++;
                    }
                    else if (value.charAt(i) === "\n") {
                        break;
                    }
                    else {
                        countTabs = 0;
                    }
                }

                $(e.target).val(value.substring(0, start)
                    + "\n"
                    + "\t".repeat(countTabs)
                    + value.substring(end));

                e.target.selectionStart = e.target.selectionEnd = start + countTabs+1;
                countTabs = 0;
            }
        });
    }

    /**
     * Sets up the clipboard events
     */
    initiateClipboard() {
        let getClipboardFileIds = () => {
            let ids = [];

            for (let i = 0; i < this.clipboard.length; i++) {
                ids.push(this.clipboard[i].id);
            }

            return ids;
        };

        $('#ClipboardPaste').click(e => {
            let idsToPaste = getClipboardFileIds();

            this.post('Paste', '', data => {
                if (data.error) {
                    alert(data.error);
                }
                else {
                    this.clipboard = [];
                    this.displayClipboard();
                    this.list();
                }
            }, {id : idsToPaste, location : this.getPath()});
        });

        $('#ClipboardDelete').click(e => {
            let idsToDelete = getClipboardFileIds();

            let message = this.info.language.CONFIRM_DELETE + ' '
                        + this.clipboard.length
                        + this.info.language.FILES
                        + '?';
            this.confirm(message, e => {
                this.post('Delete','', data => {
                    if (data.error) {
                        alert(data.error);
                    }
                    else {
                        this.clipboard = [];
                        this.displayClipboard();
                        this.list();
                    }
                }, {id : idsToDelete});
            });
        });

        $('#ClipboardDismiss').click(e => {
            this.clipboard = [];
            this.displayClipboard();
        });
    }

    /**
     * Displays/hides the clipboard info
     */
    displayClipboard() {
        if (this.clipboard.length) {
            let infotext = this.info.language.CLIPBOARD;
            let filelist = '';

            for (let i = 0; i < this.clipboard.length; i++) {
                filelist += this.clipboard[i].name + "<br>";
            }

            infotext = infotext.replace('%files%', this.clipboard.length);

            $('#ClipboardText').html(infotext).find('strong').attr('title', filelist).tooltip({html : true});
            $('#Clipboard').show();
        }
        else {
            $('#Clipboard').hide();
        }
    }

    /**
     * Sends a GET request to the server
     * @param controller
     * @param action
     * @param callback
     * @param data
     */
    get(controller, action = '', callback = null, data = {}) {
        this.showLoading(true);

        let url = 'app/api?do=' + controller + '&action=' + action;
        $.get(url, data, returnData => {
            this.showLoading(false);
            callback(returnData);
        });
    }

    /**
     * Sends a POST request to the server
     * @param controller
     * @param action
     * @param callback
     * @param data
     */
    post(controller, action = '', callback = null, data = {}) {
        this.showLoading(true);

        let url = 'app/api?do=' + controller + '&action=' + action;
        $.post(url, data, returnData => {
            this.showLoading(false);
            callback(returnData);
        });
    }

    /**
     * Displays a confirm modal
     * @param message
     * @param callback
     */
    confirm(message, callback) {
        $('#ModalTitle').text(this.info.language.ARE_YOU_SURE);
        $('#ModalBody').text(message);
        $('#ModalOk').off('click').on('click', e => {
            callback(e);
            $('#Modal').modal('hide');
        });
        $('#Modal').modal('show');
    }

    /**
     * Displays a text input modal
     * @param title
     * @param callback
     * @param defaultValue
     */
    input(title, callback, defaultValue = '') {
        $('#ModalTitle').text(title);
        $('#ModalBody').html('<input type="text" class="form-control" spellcheck="false" id="ModalInput">');
        $('#ModalInput').val(defaultValue).keyup(e => {
            if (e.which === 13) {
                $('#ModalOk').click();
            }
        });
        $('#ModalOk').off('click').on('click', e => {
            let value = $('#ModalInput').val().trim();
            if (value !== '') {
                callback(value);
                $('#Modal').modal('hide');
            }
        });
        $('#Modal').modal('show');
    }

    /**
     * Returns the current base64-encoded path
     * @returns {string}
     */
    getPath() {
        let path = '/' + this.path.join('/');
        path = btoa(path);
        return path;
    }

    /**
     * Draws breadcrumbs of the current path
     */
    drawPath() {
        let pathElement = $('#Path');
        pathElement.find('.directory').remove();

        if (this.currentSearch.length) {
            let directory = $('<li>');
            directory.addClass('directory').text(this.info.language.SEARCH_RESULT);
            pathElement.append(directory);
        }
        else {
            for (let directoryName of this.path) {
                let directory = $('<li>');
                directory.addClass('directory').text(directoryName);
                pathElement.append(directory);
            }
        }
    }

    /**
     * Displays the loading message
     * @param show
     */
    showLoading(show) {
        if (show) {
            $('#Loading').show();
        }
        else {
            $('#Loading').hide();
        }
    }
}
