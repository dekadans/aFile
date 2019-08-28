let aFileAjax = {
    async fetch(method, controller, action = '', data = {}) {
        this.showLoading(true);
        let url = 'ajax.php?do=' + controller + '&action=' + action;

        if (document.location.pathname.match(/\/dl(.php|\/)/)) {
            url = '../' +  url;

            if (document.location.pathname.match(/[a-f0-9]{40}/)) {
                url = '../' +  url;
            }
        }

        let body = null;

        for (let i in data) {
            if (data.hasOwnProperty(i)) {
                if (method === 'GET') {
                    url += '&' + i + '=' + data[i];
                }
                else {
                    if (body === null) {
                        body = new FormData();
                    }
                    body.append(i, data[i]);
                }
            }
        }

        let response = await fetch(url, {
            method : method,
            body : body,
            credentials : 'same-origin',
            cache : 'no-cache'
        });

        this.showLoading(false);

        if (response.ok) {
            let contentType = response.headers.get('Content-Type');
            let content = '';

            if (contentType.indexOf('application/json') > -1) {
                content = await response.json();

                if (content.error) {
                    alert(content.error);
                }
            }
            else {
                content = await response.text();
            }

            return content;
        }
        else {
            let content = await response.text();
            document.querySelector('body').innerHTML = content;
            return content;
        }
    },

    async upload(fileList, location) {
        this.showLoading(true);

        let ajaxData = new FormData();

        if (fileList) {
            $.each( fileList, function(i, file) {
                ajaxData.append('fileinput'+i, file );
            });
        }

        let response = await fetch('ajax.php?do=Upload&location=' + location, {
            method : 'POST',
            credentials : 'same-origin',
            body : ajaxData
        });

        this.showLoading(false);

        if (response.ok) {
            let content = await response.json();

            if (content.status === 'error') {
                alert(this.lang.find('UPLOAD_FAILED'));
            }
            else if (content.status === 'confirm') {
                this.confirmOverwrite(content);
            }

            return content;
        }

        return false;
    },

    confirmOverwrite(uploadResult) {
        let message = this.lang.find('CONFIRM_OVERWRITE') + ' ' + uploadResult.name + '?';

        this.modal.confirm(this.lang.find('ARE_YOU_SURE'), message, e => {
            this.fetch('POST', 'Upload', 'Confirmoverwrite', {
                newId : uploadResult.newId,
                oldId : uploadResult.oldId
            }).then(overwriteResult => {
                if (overwriteResult.error) {
                    alert(overwriteResult.error);
                }
                this.list();
            });
        });
    },

    showLoading(show) {
        let el = document.querySelector('#Loading');

        if (el) {
            el.style.display = (show ? 'block' : 'none');
        }
    }
};