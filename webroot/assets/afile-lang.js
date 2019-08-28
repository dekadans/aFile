class aFileLang {
    constructor() {
        this.translations = {};
    }

    find(code) {
        if (typeof this.translations[code] !== 'undefined') {
            return this.translations[code];
        } else {
            return code;
        }
    }

    setTranslations(translations) {
        this.translations = translations;
    }
}