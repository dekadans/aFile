class aFileModal {
    init() {
        this.modal = $('#Modal');

        this.modal.on('shown.bs.modal', e => {
            if (this.modal.find('#ModalInput').length) {
                this.modal.find('#ModalInput').focus();
            }
            else {
                this.modal.find('#ModalOk').focus();
            }
        }).on('hidden.bs.modal', e => {
            this.modal.find('#ModalCancel').show();
            this.modal.find('.modal-dialog').removeClass('modal-xl');
        });
    }

    show() {
        this.modal.modal('show');
    }

    hide() {
        this.modal.modal('hide');
    }

    setTitle(text) {
        this.modal.find('#ModalTitle').text(text);
    }

    setBody(html) {
        this.modal.find('#ModalBody').html(html);
    }

    setOkCallback(callback) {
        this.modal.find('#ModalOk').off('click').on('click', e => {
            callback(e);
            this.hide();
        });
    }

    setSizeXl() {
        this.modal.find('.modal-dialog').addClass('modal-xl');
    }

    hideCancel() {
        this.modal.find('#ModalCancel').hide();
    }

    /**
     * Displays a confirm modal
     * @param title
     * @param message
     * @param callback
     */
    confirm(title, message, callback) {
        this.setTitle(title);
        this.setBody(message);
        this.setOkCallback(callback);
        this.show();
    }

    /**
     * Displays a text input modal
     * @param title
     * @param callback
     * @param defaultValue
     */
    input(title, callback, defaultValue = '') {
        this.setTitle(title);
        this.setBody('<input type="text" class="form-control" spellcheck="false" id="ModalInput">');
        this.modal.find('#ModalInput').val(defaultValue).keyup(e => {
            if (e.which === 13) {
                this.modal.find('#ModalOk').click();
            }
        });

        let validateInput = (e) => {
            let value = this.modal.find('#ModalInput').val().trim();
            if (value !== '') {
                callback(value);
            }
        };

        this.setOkCallback(validateInput);

        this.show();
    }

    getModal() {
        return this.modal;
    }
}