let aFileShare = {
    loadShareDialog(fileId) {
        let $m = this.modal.getModal();

        this.fetch('GET', 'Share', 'Panel', {id : fileId}).then(html => {
            this.modal.setBody(html);
            this.modal.show();

            $m.find('#CreateToken').click(e => {
                this.fetch('GET', 'Share', 'Create', {id : fileId}).then(result => {
                    this.loadShareDialog(fileId);
                    this.list();
                });
            });

            $m.find('#DestroyToken').click(e => {
                this.fetch('GET', 'Share', 'Destroy', {id : fileId}).then(result => {
                    this.loadShareDialog(fileId);
                    this.list();
                });
            });

            $m.find('#TokenRestrict').change(e => {
                if ($m.find('#TokenRestrict').prop('checked')) {
                    $m.find('#TokenPassword').removeClass('d-none');
                } else {
                    $m.find('#TokenPassword').addClass('d-none')
                }

                this.fetch('GET', 'Share', 'Active', {id : fileId}).then(result => {
                    if (result.error) {
                        alert(result.error);
                    }
                });
            });

            $m.find('#TokenPasswordBtn').click(result => {
                let password = null;

                if (!$m.find('#TokenPasswordInput').is(':disabled')) {
                    password = $m.find('#TokenPasswordInput').val();

                    if (password === '') return;
                }

                if (confirm(this.info.language.ARE_YOU_SURE)) {
                    this.fetch('POST', 'Share', 'Password', {id : fileId, password : password}).then(result => {
                        this.loadShareDialog(fileId);
                        this.list();
                    });
                }
            });
        });
    }
};