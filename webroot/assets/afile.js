class aFile {
    constructor() {
        this.info = null; // Data fetched from the server
        this.selected = null;
        this.clipboard = [];
        this.clickLock = false;
        this.keepAliveInterval = null;

        this.nav = new aFileNavigation();
        this.modal = new aFileModal();

        window.onpopstate = event => {
            if (this.info.login && event.state !== null) {
                this.nav.loadState(event.state);
                this.drawPath();
                this.list();
            }
        };

        this.keybindings();
        this.check();
    }

    /**
     * Fetches config and session info from the server.
     */
    async check() {
        if (this.keepAliveInterval) {
            clearInterval(this.keepAliveInterval);
        }

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

    async keepalive() {
        let result = await this.fetch('GET', 'Keepalive');

        if (result.status !== 'ok') {
            this.check();
        }
    }

    /**
     * Bind various keyboard shortcuts
     */
    keybindings() {
        $(document).keydown(e => {
            if (!this.modal.getModal().is(':visible') && !$('#SearchInput').is(':focus')) {
                if (this.selected) {
                    if (e.which === 46) { // Delete
                        $('#Delete').click();
                    }
                    else if (e.which === 82 && !(e.ctrlKey || e.metaKey)) { // R
                        $('#Rename').click();
                    }
                    else if (e.which === 13) { // Enter
                        this.selected.dblclick();
                    }
                    else if (e.which === 27) { // Escape
                        this.selectItem(null);
                    }
                    else if (e.which === 77 && this.selected.hasClass('file')) { // M
                        this.modal.input(this.selected.data('mime'), value => {
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
                    if (!this.nav.isSearching()) {
                        if (this.nav.getCurrentLocation() !== null) {
                            history.go(-1);
                        }
                    } else {
                        history.go(-1);
                    }
                }
                else if (e.which === 83) { // S
                    setTimeout(function(){
                        $('#SearchInput').focus();
                    }, 100);
                }
                else if (e.which === 72) { // H
                    $('#PathHome').click();
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
                        $('#LoginMessage').show();
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

        this.drawPath();
        this.list();

        $('#PathHome, #BrandHome').click(e => {
            $(e.target).blur();
            e.preventDefault();
            if (this.nav.getCurrentLocation() !== null || this.nav.isSearching()) {
                this.nav.goToRoot();
                this.drawPath();
                this.list();
            }
        });

        $('#Help').click(e => {
            e.preventDefault();

            this.fetch('GET', 'Info', 'Help').then(html => {
                this.modal.setSizeXl();
                this.modal.hideCancel();
                this.modal.setTitle(this.info.language.HELP);
                this.modal.setBody(html);
                this.modal.setOkCallback(e => {
                    this.modal.hide();
                });
                this.modal.show();
            });
        });

        $('#Size').click(e => {
            e.preventDefault();

            this.fetch('GET', 'Info', 'Size').then(json => {
                this.modal.hideCancel();
                this.modal.setTitle(this.info.language.SIZE_TITLE);
                this.modal.setBody('<strong>' + json.h + '</strong> (' + json.b + ')');
                this.modal.setOkCallback(e => {
                    this.modal.hide();
                });
                this.modal.show();
            });
        });

        $('#Logout').click(e => {
            e.preventDefault();
            this.selectItem(null);
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
            this.nav.setSearchString( $('#SearchInput').val());
            this.drawPath();
            this.list();
        });

        this.initiateDropZone();

        this.initiateClipboard();

        this.modal.init();

        this.keepAliveInterval = setInterval(() => {
            this.keepalive();
        }, 900000);
    }

    /**
     * Retrieves the list of files
     */
    list() {
        this.selectItem(null);
        let action, data;

        if (this.nav.isSearching()) {
            action = 'search';
            data = {search : this.nav.getSearchString()};
        }
        else {
            action = 'list';
            data = {location : this.nav.getCurrentLocation()};
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
                    this.nav.pushDirectory({
                        id : this.selected.data('id'),
                        name : this.selected.find('.fileName').text()
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
                this.modal.confirm(this.info.language.ARE_YOU_SURE, message, e => {
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

                this.modal.input(this.info.language.RENAME, value => {
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
                this.modal.setTitle(this.info.language.SHARE);
                this.modal.hideCancel();
                this.modal.setOkCallback(e => {
                    this.modal.hide();
                });

                this.loadShareDialog(this.selected.data('id'));
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

        $('#Gallery').click(e => {
            this.openGallery();
        });

        $('#Upload').click(e => {
            e.preventDefault();
            this.modal.setTitle(this.info.language.UPLOAD);
            this.modal.setBody('<input type="file" id="ManualUpload" multiple>');
            this.modal.setOkCallback(e => {
                let filesToUpload = document.getElementById('ManualUpload').files;

                if (filesToUpload.length) {
                    this.upload(filesToUpload, this.nav.getCurrentLocation()).then(result => {
                        this.modal.hide();
                        this.list();
                    });
                } else {
                    this.modal.hide();
                }
            });

            this.modal.show();
        });

        $('#CreateDirectory').click(e => {
            e.preventDefault();
            this.modal.input(this.info.language.CREATE_DIRECTORY, value => {
                this.fetch('POST', 'Create', 'Directory', {
                    location : this.nav.getCurrentLocation(),
                    name : value
                }).then(data => {
                    this.list();
                });
            });
        });

        $('#OpenEditor').click(e => {
            e.preventDefault();
            this.modal.input(this.info.language.EDITOR_NAME, value => {
                this.fetch('POST', 'Editor', 'Create', {
                    filename : value,
                    location : this.nav.getCurrentLocation()
                }).then(jsonResponse => {
                    this.list();
                });
            });
        });

        $('#CreateLink').click(e => {
            e.preventDefault();
            let modalBody = '<p><input type="text" class="form-control focusOnShow" spellcheck="false" id="ModalInputName" placeholder="Name"></p>';
            modalBody += '<p><input type="text" class="form-control confirmOnEnter" spellcheck="false" id="ModalInputUrl" placeholder="URL"></p>';

            this.modal.setTitle('Create link');
            this.modal.setBody(modalBody);
            this.modal.setOkCallback(e => {
                let m = this.modal.getModal();
                let name = m.find('#ModalInputName').val();
                let url = m.find('#ModalInputUrl').val();

                try {
                    new URL(url);
                } catch (_) {
                    alert(this.info.language.LINK_ERROR);
                    return false;
                }

                this.fetch('POST', 'Link', 'Create', {
                    name : name,
                    url : url,
                    location : this.nav.getCurrentLocation()
                }).then(data => {
                    this.list();
                });
            });
            this.modal.show();
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
        })
        .on('dragleave dragend drop', function() {
        })
        .on('drop', e => {
            let filesToUpload = e.originalEvent.dataTransfer.files;

            if (filesToUpload.length) {
                this.upload(filesToUpload, this.nav.getCurrentLocation()).then(result => {
                    this.list();
                });
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

            this.fetch('POST', 'Paste', '', {
                id : idsToPaste,
                location : this.nav.getCurrentLocation()
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
            this.modal.confirm(this.info.language.ARE_YOU_SURE, message, e => {
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
        let $clipboard = $('#ClipboardButtons');

        if (this.clipboard.length) {
            $clipboard.find('.clipboard-item').remove();

            for (let i = 0; i < this.clipboard.length; i++) {
                let $fileitem = $('<span>');
                $fileitem.addClass('dropdown-item-text clipboard-item');
                $fileitem.text(this.clipboard[i].name);

                $clipboard.find('#ClipboardFileList').append($fileitem);
            }

            $('#ClipboardButtons').css('display', 'inline-flex');
        }
        else {
            $('#ClipboardButtons').hide();
        }
    }

    /**
     * Draws breadcrumbs of the current path
     */
    drawPath() {
        let pathElement = $('#Path');
        pathElement.find('.directory').remove();

        if (this.nav.isSearching() || this.nav.isFromSearchResult()) {
            let directory = $('<li class="breadcrumb-item">');
            directory.addClass('directory').text(this.info.language.SEARCH_RESULT);
            pathElement.append(directory);
        }

        if (!this.nav.isSearching()) {
            let path = this.nav.getPathStack();
            for (let directoryObject of path) {
                let directory = $('<li class="breadcrumb-item directory">');
                directory.text(directoryObject.name);
                pathElement.append(directory);
            }
        }
    }
}

Object.assign(aFile.prototype, aFileShare);
Object.assign(aFile.prototype, aFileGallery);
Object.assign(aFile.prototype, aFileAjax);
