let markdownConverter = new showdown.Converter({
    simplifiedAutoLink : true,
    excludeTrailingPunctuationFromURLs : true,
    //simpleLineBreaks : true,
    openLinksInNewWindow : true,
    emoji : true
});

Vue.directive('highlightjs', {
    deep: true,
    bind: function(el, binding) {
        let targets = el.querySelectorAll('pre code');
        targets.forEach((target) => {
            if (binding.value) {
                target.textContent = binding.value;
            }
            hljs.highlightBlock(target);
        })
    },
    componentUpdated: function(el, binding) {
        let targets = el.querySelectorAll('pre code');
        targets.forEach((target) => {
            if (binding.value) {
                target.textContent = binding.value;
                hljs.highlightBlock(target);
            }
        })
    }
});

Vue.component('preview', {
    props : ['file'],
    computed : {
        parseContent : function() {
            if (this.file.markdown) {
                return markdownConverter.makeHtml(this.file.text);
            } else if (this.file.code) {
                let code = this.file.text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                return '<pre><code>'+ code +'</code></pre>';
            } else {
                return '';
            }
        }
    },
    template : `
<div class="container editor-preview" style="margin-bottom: 100px;">
    <div class="row">
        <div class="col-md"></div>
        <div class="col-md-10">

            <div class="card">
                <h5 class="card-header">{{ file.name }}
                    <div class="float-right">
                        <small class="text-muted">
                            {{ file.date }}&nbsp;&nbsp;|
                            <a class="preview-toggle" v-if="file.editable" v-on:click.prevent="$emit('open-editor')" href="#">
                                &nbsp;&nbsp;<i class="fas fa-edit"></i>
                                Edit
                            </a>&nbsp;
                            <a id="EditorDownload" class="" :href="file.downloadLink">
                                <i class="fas fa-cloud-download-alt"></i>
                                Download
                            </a>
                        </small>
                    </div>
                </h5>

                <div class="card-body markdown-body">
                    <div id="EditorPreview" v-html="parseContent" v-highlightjs>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md"></div>
    </div>
</div>
    `
});

Vue.component('editor', {
    props : ['file', 'message'],
    computed : {
        hasPreview : function() {
            return (this.file.markdown || this.file.code);
        }
    },
    template : `
<div id="EditorContainer">

    <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#" id="BrandHome">{{ file.name }}</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav mr-auto" v-if="file.editable">
                    <button id="EditorSave" v-on:click="$emit('save')" class="btn btn-outline-success my-2 my-sm-0">Save</button>
                    <span v-show="message" class="navbar-text ml-3">Saved</span>
                </div>
                <div class="navbar-nav" v-if="hasPreview">
                    <a id="EditorClose" v-on:click.prevent="$emit('close-editor')" class="nav-item nav-link" href="#">
                        <i class="far fa-times-circle"></i>
                        Close
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <textarea id="EditorTextarea" spellcheck="false" v-model="file.text"></textarea>
</div>
    `
});

let editor = new Vue({
    el : "#Editor",
    data : {
        file : file,
        preview : false,
        savedMessage : false
    },
    methods : {
        save () {
            aFileAjax.fetch('POST', 'Editor', 'Write', {
                content : this.file.text,
                id : this.file.id
            }).then(jsonResponse => {
                if (jsonResponse.status === 'ok') {
                    this.savedMessage = true;
                    setTimeout(() => {
                        this.savedMessage = false;
                    }, 3000);
                } else {
                    alert('Failed');
                }
            });
        }
    },
    mounted : function() {
        document.querySelector('title').innerHTML = this.file.name;
        this.preview = (this.file.markdown || this.file.code) && this.file.text !== '';
    }
});
