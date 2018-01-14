class Test {
    constructor(t) {
        this.prop = t;
    }

    print() {
        console.log(this.prop);
    }
}

class aFile {
    constructor() {
        this.info = null; // Data fetched from the server
        this.path = [];

        this.findDefinedFiletypes();
        this.check();
    }

    /**
     * Fetches config and session info from the server.
     */
    check() {
        $.getJSON('app/api.php?do=Check', data => {
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

    mainView() {
        $('#Logout').click(e => {
            e.preventDefault();
            this.showLoading(true);
            this.get('Logout', '', data => {
                this.check();
            });
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

    /**
     * Takes an integer of bytes and returns it more readable, either in kB or KiB
     */
    humanFileSize(bytes, si) {
        var thresh = si ? 1000 : 1024;
        if(Math.abs(bytes) < thresh) {
            return bytes + ' B';
        }
        var units = si
            ? ['kB','MB','GB','TB','PB','EB','ZB','YB']
            : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
        var u = -1;
        do {
            bytes /= thresh;
            ++u;
        } while(Math.abs(bytes) >= thresh && u < units.length - 1);
        return bytes.toFixed(1)+' '+units[u];
    }

    /**
     * Initiates various events
     */
    initEvents() {
        var self = this;

        // MAIN

    }

    /**
     * Returns a string defined by a language code
     */
    l(code) {
        if (this.info.language[code]) {
            return this.info.language[code];
        }
        else {
            return code;
        }
    }

    /**
     * Retrieves the list of files and displayes them
     */
    list() {
        this.showLoading(true);

        var path = '/' + this.path.join('/');
        path = btoa(path);
        $.getJSON('app/api.php',{do : 'ListFiles', location : path}, data => {
            this.showLoading(false);
            $('#List').html();
            for (var i = 0; i < data.length; i++) {
                var ext = data[i].name.split('.').pop();
                if (this.exts.indexOf(ext) == -1) {
                    ext = 'blank';
                }

                var listItem = $('<tr>');
                listItem.addClass('listItem');
                listItem.append('<td><span class="flaticon-'+ ext +'"></td>');
                listItem.append('<td>'+ data[i].name +'</td>');
                listItem.append('<td>'+ this.humanFileSize(data[i].size, this.info.siprefix == '1' ? true : false) +'</td>');
                listItem.append('<td>'+ data[i].last_edit +'</td>');
                $('#List').append(listItem);
            }
        });
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

    /**
     * Replaces mustache tags with texts from the language file
     */
    translate() {
        for (var i = 0; i < this.views.length; i++) {
            var template = $('#'+this.views[i]).html();
            var rendered = Mustache.render(template, this.info.language);
            $('#'+this.views[i]).html(rendered);
        }
        this.translated = true;
    }
}
