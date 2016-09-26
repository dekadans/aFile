var aFile = {
    info : null,
    path : [],
    translated : false,
    views : [
        'Loading',
        'Login',
        'Main'
    ],

    init : function() {
        this.findDefinedFiletypes();
        this.check();
    },

    check : function(){
        $.getJSON('app/api.php?do=Check', function(data){
            aFile.info = data;

            if (!aFile.translated) {
                aFile.translate();
                aFile.initEvents();
            }

            if (aFile.info.login) {
                aFile.displayView('Main');
                aFile.list();
            }
            else {
                aFile.displayView('Login');
                $('#LoginUsername').focus();
            }
        });
    },

    displayView : function(view){
        if ($.inArray(view, this.views) > -1) {
            $('.view').hide();
            $('#'+view).show();
        }
    },

    /**
     * Find which file extentions that has icons defined for them
     */
    findDefinedFiletypes : function() {
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
    },

    humanFileSize : function(bytes, si) {
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
    },

    initEvents : function() {
        // LOGIN
        $('#LoginButton').click(function(){
            var username = $('#LoginUsername').val();
            var password = $('#LoginPassword').val();

            if (username.length && password.length) {
                aFile.showLoading(true);
                $.post('app/api.php?do=Login', {username : username, password : password}, function(data){
                    if (data.error) {
                        aFile.showLoading(false);
                        $('#LoginMessage > .alert').html(aFile.l(data.error));
                        $('#LoginMessage').slideDown();
                    }
                    else if (data.status == 'ok') {
                        $('#LoginUsername, #LoginPassword').val('');
                        $('#LoginMessage').slideUp();
                        aFile.check();
                    }
                });
            }
        });

        $('#LoginPassword').keyup(function(e){
            if (e.which == 13) {
                $('#LoginButton').click();
            }
        });

        // MAIN
        $('#Logout').click(function(e){
            e.preventDefault();
            aFile.showLoading(true);
            $.getJSON('app/api.php?do=Logout', function(data){
                aFile.check();
            });
        });
    },

    l : function(code) {
        if (this.info.language[code]) {
            return this.info.language[code];
        }
        else {
            return code;
        }
    },

    list : function() {
        this.showLoading(true);

        var path = '/' + this.path.join('/');
        path = btoa(path);
        $.getJSON('app/api.php',{do : 'ListFiles', location : path}, function(data){
            aFile.showLoading(false);
            for (var i = 0; i < data.length; i++) {
                var ext = data[i].name.split('.').pop();
                if ($.inArray(ext, aFile.exts) == -1) {
                    ext = 'blank';
                }

                var listItem = $('<tr>');
                listItem.addClass('listItem');
                listItem.append('<td><span class="flaticon-'+ ext +'"></td>');
                listItem.append('<td>'+ data[i].name +'</td>');
                listItem.append('<td>'+ aFile.humanFileSize(data[i].size, aFile.info.siprefix == '1' ? true : false) +'</td>');
                listItem.append('<td>'+ data[i].last_edit +'</td>');
                $('#List').append(listItem);
            }
        });
    },

    showLoading : function(show) {
        if (show) {
            $('#Loading').show();
        }
        else {
            $('#Loading').hide();
        }
    },

    /**
     * Replaces mustache tags with texts from the language file
     */
    translate : function() {
        for (var i = 0; i < this.views.length; i++) {
            var template = $('#'+this.views[i]).html();
            var rendered = Mustache.render(template, this.info.language);
            $('#'+this.views[i]).html(rendered);
        }
        this.translated = true;
    }
}
