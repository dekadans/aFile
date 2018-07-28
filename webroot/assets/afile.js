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
    async check() {
        this.info = await this.fetch('GET', 'Check');

        if (this.info.login) {
            document.querySelector('body').innerHTML = await this.fetch('GET', 'ListFiles');
            this.mainView();
        }
        else {
            document.querySelector('body').innerHTML = await this.fetch('GET', 'Login', 'form');
            this.loginView();
        }
    }

    /**
     * Bind various keyboard shortcuts
     */
    keybindings() {
        $(document).keydown(e => {
            if (!$('#Modal').is(':visible') && !$('#SearchInput').is(':focus')) {
                if (this.selected) {
                    if (e.which === 46) { // Delete
                        $('#Delete').click();
                    }
                    else if (e.which === 82) { // R
                        $('#Rename').click();
                    }
                    else if (e.which === 13) { // Enter
                        this.selected.dblclick();
                    }
                    else if (e.which === 27) { // Escape
                        this.selectItem(null);
                    }
                    else if (e.which === 77 && this.selected.hasClass('file')) { // M
                        this.input(this.selected.data('mime'), value => {
                            this.fetch('GET', 'Rename', 'Changemime', {
                                id : this.selected.data('id'),
                                mime : value
                            }).then(data => {
                                this.list();
                            });
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
                    setTimeout(function(){
                        $('#SearchInput').focus();
                    }, 100);
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
            let remember = $('#RememberMe').is(':checked');

            if (username.length && password.length) {
                this.fetch('POST', 'Login', '', {
                    username : username,
                    password : password,
                    remember : remember
                }).then(data => {
                    if (data.loginError) {
                        $('#LoginMessage > .alert').html(data.loginError);
                        $('#LoginMessage').slideDown();
                    }
                    else if (data.status === 'ok') {
                        this.check();
                    }
                });
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
            this.fetch('GET', 'Logout').then(data => {
                this.check();
            });
        });

        $('#SearchInput').keyup(e => {
            if (e.which === 13) {
                e.preventDefault();
                $('#Search').click();
            }
            else if (e.which === 27) {
                $(e.target).blur();
            }
        });

        $('#Search').click(e => {
            this.currentSearch = $('#SearchInput').val();
            this.drawPath();
            this.list();
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
        this.initiateDropZone();

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

        this.fetch('POST', 'ListFiles', action, data).then(html => {
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
                    //this.path.push(this.selected.find('.fileName').text());
                    this.path.push({
                        name : this.selected.find('.fileName').text(),
                        id : this.selected.data('id')
                    });
                    this.drawPath();
                    this.selectItem(null);
                    this.list();
                }
                else if (this.selected.hasClass('file')) {
                    $('#Download').click();
                }
            });
        });
    }

    /**
     * Sets the Jquery representation of a file in the list as selected
     * @param item
     */
    selectItem(item) {
        $('.listItem').removeClass('bg-light');

        if (item === null) {
            this.selected = null;
            $('#FileButtons').find('button').prop('disabled', true);
        }
        else {
            this.selected = item;
            this.selected.addClass('bg-light');
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

                    this.fetch('GET', 'Delete', '', {
                        id : id
                    }).then(data => {
                        this.selected.remove();
                        this.selectItem(selectNextInList);
                    });
                });
            }
        });

        $('#Rename').click(e => {
            if (this.selected) {
                let currentNameElement = this.selected.find('.fileName');

                this.input(this.info.language.RENAME, value => {
                    this.fetch('POST', 'Rename', '', {
                        id : this.selected.data('id'),
                        name : value
                    }).then(data => {
                        currentNameElement.text(value);
                    });
                }, currentNameElement.text());
            }
        });

        $('#Share').click(e => {
            if (this.selected) {
                $('#ModalTitle').text(this.info.language.SHARE);
                $('#ModalCancel').hide();
                $('#ModalOk').off('click').on('click', e => {
                    $('#Modal').modal('hide');
                });

                let fetchData = {
                    id : this.selected.data('id')
                };

                let loadShareDialog = () => {
                    this.fetch('GET', 'Share', 'Panel', fetchData).then(html => {
                        $('#ModalBody').html(html);
                        $('#Modal').modal('show');

                        $('#CreateToken').click(e => {
                            this.fetch('GET', 'Share', 'Create', fetchData).then(result => {
                                loadShareDialog();
                                this.list();
                            });
                        });

                        $('#DestroyToken').click(e => {
                            this.fetch('GET', 'Share', 'Destroy', fetchData).then(result => {
                                loadShareDialog();
                                this.list();
                            });
                        });
                    });
                };

                loadShareDialog();
            }
        });

        $('#Download').click(e => {
            if (this.selected) {
                let url = 'dl' + (this.info.skip_dl_php_extension ? '' : '.php') + '/' + this.selected.data('stringid');

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
                this.fetch('POST', 'Create', 'Directory', {
                    location : this.getPath(),
                    name : value
                }).then(data => {
                    this.list();
                });
            });
        });

        $('#OpenEditor').click(e => {
            this.input(this.info.language.EDITOR_NAME, value => {
                this.fetch('POST', 'Editor', 'Create', {
                    filename : value,
                    location : this.getPath()
                }).then(jsonResponse => {
                    this.list();
                });
            });
        });

        $('.sortby').click(e => {
            e.preventDefault();
            let column = $(e.target).data('column');

            this.fetch('GET', 'Sort', '', {
                column : column
            }).then(data => {
                $('.sortby').removeClass('active');
                $(e.target).addClass('active');
                this.list();
            });
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
                url: 'ajax.php?do=Upload&location=' + this.getPath(),
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
                            this.fetch('POST', 'Upload', 'Confirmoverwrite', {
                                newId : data.newId,
                                oldId : data.oldId
                            }).then(overwriteResult => {
                                if (overwriteResult.error) {
                                    alert(overwriteResult.error);
                                }
                                this.list();
                            });
                        });
                    }

                    this.updateProgress();
                    this.list();
                },
                error: response => {
                    delete this.currentUploads[uploadId];
                    $('body').html(response.responseText);
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

            this.fetch('POST', 'Paste', '', {
                id : idsToPaste,
                location : this.getPath()
            }).then(data => {
                this.clipboard = [];
                this.displayClipboard();
                this.list();
            });
        });

        $('#ClipboardDelete').click(e => {
            let idsToDelete = getClipboardFileIds();

            let message = this.info.language.CONFIRM_DELETE + ' '
                        + this.clipboard.length
                        + this.info.language.FILES
                        + '?';
            this.confirm(message, e => {
                this.fetch('POST', 'Delete', '', {
                    id : idsToDelete
                }).then(data => {
                    this.clipboard = [];
                    this.displayClipboard();
                    this.list();
                });
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
        if (this.path.length) {
            let path = this.path[this.path.length-1];
            return path.id;
        }
        else {
            return null;
        }
    }

    /**
     * Draws breadcrumbs of the current path
     */
    drawPath() {
        let pathElement = $('#Path');
        pathElement.find('.directory').remove();

        if (this.currentSearch.length) {
            let directory = $('<li class="breadcrumb-item">');
            directory.addClass('directory').text(this.info.language.SEARCH_RESULT);
            pathElement.append(directory);
        }
        else {
            for (let directoryObject of this.path) {
                let directory = $('<li class="breadcrumb-item">');
                directory.addClass('directory').text(directoryObject.name);
                pathElement.append(directory);
            }
        }
    }
}

Object.assign(aFile.prototype, aFileAjax);