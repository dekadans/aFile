class aFileEditor {
    constructor() {
        this.markdownConverter = new showdown.Converter({
            simplifiedAutoLink : true,
            excludeTrailingPunctuationFromURLs : true,
            //simpleLineBreaks : true,
            openLinksInNewWindow : true,
            emoji : true
        });

        this.edited = false;

        this.markdown = false;
        this.code = false;

        this.bindEvents();
    }

    getText() {
        return document.querySelector('#EditorTextarea').value;
    }

    getFileId() {
        return document.querySelector('#EditorTextarea').dataset.fileid;
    }

    parseMarkdown() {
        let html = this.markdownConverter.makeHtml(this.getText());
        document.querySelector('#EditorPreview').innerHTML = html;
    }

    highlightCode() {
        let previewElement = document.querySelector('#EditorPreview');
        let code = this.getText();
        code = code.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        previewElement.innerHTML = '<pre><code class="editorCode">'+ code +'</code></pre>';
        hljs.highlightBlock(previewElement);
    }

    togglePreview() {
        this.parsePreview();

        document.querySelector('.editor-preview').classList.toggle('d-none');
        document.querySelector('#EditorContainer').classList.toggle('d-none');

        let saveButton = document.querySelector('#EditorSave');
        if (saveButton) {
            saveButton.classList.toggle('d-none');
        }
    }

    parsePreview() {
        if (this.markdown) {
            this.parseMarkdown();
        } else if (this.code) {
            this.highlightCode();
        }
    }

    saveContent() {
        this.fetch('POST', 'Editor', 'Write', {
            content : this.getText(),
            id : this.getFileId()
        }).then(jsonResponse => {
            if (jsonResponse.status === 'ok') {
                this.edited = false;
                let message = document.querySelector('#EditorSavedMessage');

                message.classList.remove('d-none');
                setTimeout(() => {
                    message.classList.add('d-none');
                }, 3000);
            } else {
                alert('Failed');
            }
        });
    }

    bindEvents() {
        let preview = document.querySelector('.preview-toggle');
        if (preview) {
            preview.addEventListener('click', e => {
                e.preventDefault();
                this.togglePreview();
            });

            document.querySelector('#EditorClose').addEventListener('click', e => {
                e.preventDefault();
                this.togglePreview();
            });
        }

        document.querySelector('#EditorTextarea').addEventListener('keydown', e => {
            if (e.key.length === 1 && !e.metaKey) {
                this.edited = true;
            }

            let start = e.target.selectionStart;
            let end = e.target.selectionEnd;
            let value = e.target.value;

            if (e.which === 9) {
                e.target.value = value.substring(0, start)
                    + "\t"
                    + value.substring(end);

                e.target.selectionStart = e.target.selectionEnd = start + 1;
                e.preventDefault();
            } else if (e.which === 13) {
                e.preventDefault();

                let countTabs = 0;
                for (let i = start-1; i>=0; i--) {
                    if (value.charAt(i) === "\t") {
                        countTabs++;
                    } else if (value.charAt(i) === "\n") {
                        break;
                    } else {
                        countTabs = 0;
                    }
                }

                e.target.value = value.substring(0, start)
                    + "\n"
                    + "\t".repeat(countTabs)
                    + value.substring(end);

                e.target.selectionStart = e.target.selectionEnd = start + countTabs+1;
                countTabs = 0;
            }
        });

        let saveButton = document.querySelector('#EditorSave');
        if (saveButton) {
            saveButton.addEventListener('click', e => {
                e.preventDefault();
                this.saveContent();
            });

            document.addEventListener('keydown', e => {
                if ((e.key === 's' || e.key === 'S') && (e.ctrlKey || e.metaKey)) {
                    e.preventDefault();
                    this.saveContent();
                }
            });
        }

        window.onbeforeunload = e => this.edited ? '' : null;
    }
}

Object.assign(aFileEditor.prototype, aFileAjax);