class aFile {
    constructor() {
        this.info = null; // Data fetched from the server
        this.path = [];
        this.selected = null;
        this.currentUploads = [];
        this.clickLock = false;

        //this.findDefinedFiletypes();
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
     * Eventbindings for the login view
     */
    loginView() {
        $('#LoginUsername').focus();

        $('#LoginButton').click(event => {
            let username = $('#LoginUsername').val();
            let password = $('#LoginPassword').val();

            if (username.length && password.length) {
                this.showLoading(true);
                this.post('Login', '', data => {
                    if (data.error) {
                        this.showLoading(false);
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
     * Eventbindings for the main view
     */
    mainView() {
        this.fileButtons();

        this.list();

        $('#PathHome').click(e => {
            e.preventDefault();
            this.path = [];
            this.drawPath();
            this.list();
        });

        $('#Logout').click(e => {
            e.preventDefault();
            this.showLoading(true);
            this.get('Logout', '', data => {
                this.check();
            });
        });

        $('#Modal').on('shown.bs.modal', e => {
            $('#ModalOk').focus();
        });

        this.initiateDropZone();
    }

    /**
     * Retrieves the list of files and displayes them
     */
    list() {
        this.showLoading(true);

        let path = this.getPath();

        this.get('ListFiles', 'list', html => {
            this.showLoading(false);
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
                    this.list();
                }
                else if (this.selected.hasClass('file')) {
                    $('#Download').click();
                }
            });
        }, {location : path});
    }

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
        }
    }

    fileButtons() {
        $('#FileButtons').find('button').prop('disabled', true);

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

        $('#Delete').click(e => {
            if (this.selected) {
                let message = this.info.language.CONFIRM_DELETE + ' ' + this.selected.find('.fileName').text() + '?';
                this.confirm(message, e => {
                    let id = this.selected.data('id');

                    this.get('Delete', '', data => {
                        if (data.error) {
                            alert(data.error);
                        }
                        else {
                            this.selected.remove();
                            this.selected = null;
                        }
                    }, {id : id});
                });
            }
        });
    }

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

    get(controller, action = '', callback = null, data = {}) {
        let url = 'app/api?do=' + controller + '&action=' + action;
        $.get(url, data, callback);
    }

    post(controller, action = '', callback = null, data = {}) {
        let url = 'app/api?do=' + controller + '&action=' + action;
        $.post(url, data, callback);
    }

    confirm(message, callback) {
        $('#ModalTitle').text(this.info.language.ARE_YOU_SURE);
        $('#ModalBody').text(message);
        $('#ModalOk').off('click').on('click', e => {
            callback(e);
            $('#Modal').modal('hide');
        });
        $('#Modal').modal('show');
    }

    getPath() {
        let path = '/' + this.path.join('/');
        path = btoa(path);
        return path;
    }

    drawPath() {
        $('#Path').find('.directory').remove();

        for (let directoryName of this.path) {
            let directory = $('<li>');
            directory.addClass('directory').text(directoryName);
            $('#Path').append(directory);
        }
    }

    showLoading(show) {
        if (show) {
            $('#Loading').show();
        }
        else {
            $('#Loading').hide();
        }
    }

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
        console.log(this.currentUploads, combinedProgress);
        $('#Progress').css('width', combinedProgress + '%');
    }

    /*************  OLD STUFF *******************''*/

    /**
     * Find which file extentions that has icons defined for them
     */
    findDefinedFiletypes() {
        this.exts = [];
        for (let i = 0; i < document.styleSheets.length; i++) {
            let name = document.styleSheets[i].href.split('/').pop();
            if (name == 'flaticon.css') {
                for (let j = 0; j < document.styleSheets[i].rules.length; j++) {
                    let definition = document.styleSheets[i].rules[j].selectorText;
                    if (typeof definition != 'undefined') {
                        if (definition.substring(0,10) == '.flaticon-') {
                            let ext = definition.substring(10, definition.search(':'));
                            this.exts.push(ext);
                        }
                    }
                }
            }
        }
    }
}
