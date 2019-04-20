let aFileGallery = {
    openGallery() {
        this.fetch('GET', 'ListFiles', 'Images', {location : this.getPath()}).then(list => {
            if (list.length > 0) {
                $('#Gallery').blur();
                $('#GalleryContainer').remove();
                $('<div id="GalleryContainer" style="display:none;">').appendTo('body');
                lightGallery(document.getElementById('GalleryContainer'), {
                    dynamic: true,
                    dynamicEl: list
                });
            }
        });
    }
};