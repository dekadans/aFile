class aFile {
    constructor() {
        this.info = null; // Data fetched from the server
        this.path = [];
        this.selected = null;

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
            var username = $('#LoginUsername').val();
            var password = $('#LoginPassword').val();

            if (username.length && password.length) {
                this.showLoading(true);
                this.post('Login', '', data => {
                    if (data.error) {
                        this.showLoading(false);
                        $('#LoginMessage > .alert').html(data.error);
                        $('#LoginMessage').slideDown();
                    }
                    else if (data.status == 'ok') {
                        this.check();
                    }
                }, {username : username, password : password});
            }
        });

        $('#LoginPassword').keyup(e => {
            if (e.which == 13) {
                $('#LoginButton').click();
            }
        });
    }

    /**
     * Eventbindings for the main view
     */
    mainView() {
        this.list();

        $('#Logout').click(e => {
            e.preventDefault();
            this.showLoading(true);
            this.get('Logout', '', data => {
                this.check();
            });
        });
    }

    /**
     * Retrieves the list of files and displayes them
     */
    list() {
        this.showLoading(true);

        var path = '/' + this.path.join('/');
        path = btoa(path);

        this.get('ListFiles', 'list', html => {
            this.showLoading(false);
            $('#List').html(html);
            this.fileButtons();

            $('.listItem').click(e => {
                $('.listItem').removeClass('listItemActive');

                if (this.selected !== null && this.selected[0] == $(e.target).closest('.listItem')[0]) {
                    this.selected = null;
                    $('#FileButtons button').prop('disabled', true);
                }
                else {
                    this.selected = $(e.target).closest('.listItem');
                    this.selected.addClass('listItemActive');
                    $('#FileButtons button').prop('disabled', false);
                }
            }).dblclick(e => {
                console.log('hej');
            });
        }, {location : path});
    }

    fileButtons() {
        $('#FileButtons button').prop('disabled', true);

        $('#Download').click(e => {
            if (this.selected) {
                var url = 'dl.php/' + this.selected.data('stringid');

                if (this.selected.data('newtab')) {
                    window.open(url);
                }
                else {
                    window.document.location = url;
                }
            }
        });
    }

    get(controller, action = '', callback = null, data = {}) {
        var url = 'app/api?do=' + controller + '&action=' + action;
        $.get(url, data, callback);
    }

    post(controller, action = '', callback = null, data = {}) {
        var url = 'app/api?do=' + controller + '&action=' + action;
        $.post(url, data, callback);
    }

    /**
     * Shows/hides a "Loading" message
     */
    showLoading(show) {
        if (show) {
            $('#Loading').show();
        }
        else {
            $('#Loading').hide();
        }
    }


    /*************  OLD STUFF *******************''*/

    /**
     * Find which file extentions that has icons defined for them
     */
    findDefinedFiletypes() {
        this.exts = [];
        for (var i = 0; i < document.styleSheets.length; i++) {
            var name = document.styleSheets[i].href.split('/').pop();
            if (name == 'flaticon.css') {
                for (var j = 0; j < document.styleSheets[i].rules.length; j++) {
                    var definition = document.styleSheets[i].rules[j].selectorText;
                    if (typeof definition != 'undefined') {
                        if (definition.substring(0,10) == '.flaticon-') {
                            var ext = definition.substring(10, definition.search(':'));
                            this.exts.push(ext);
                        }
                    }
                }
            }
        }
    }
}
