
/**
 * aFILE CLASS
 */

class aFile {
    constructor() {
        this.l = {};
        this.path = [];
        this.findDefinedFiletypes();
    }

    init(checkData) {
        this.l = checkData.language;
        this.siprefix = checkData.siprefix;
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

    humanFileSize(bytes) {
        var si = this.siprefix == '1' ? true : false;

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
}

var APP = null;

$(function(){
    APP = new aFile();

    ReactDOM.render(
        <UI />,
        document.getElementById('AppContainer')
    );
});
