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
    methods : {
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
                            {{ file.date }}&nbsp;&nbsp;|&nbsp;&nbsp;
                            <a class="preview-toggle" v-if="file.editable" v-on:click.prevent="$emit('open-editor')" href="#">
                                <i class="fas fa-edit"></i>
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
                    <div id="EditorPreview" v-html="parseContent()" v-highlightjs>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md"></div>
    </div>
</div>
    `
});


let editor = new Vue({
    el : "#Editor",
    data : {
        file : false,
        preview : false
    },
    methods : {
        openEditor () {
            this.preview = false;
        }
    },
    mounted : function() {
        let fid = document.location.pathname.match(/dl(.php)?\/([a-z0-9]*)/)[2];
        aFileAjax.fetch('GET', 'Editor', 'Get', {file : fid})
            .then(data => {
                this.file = data;
                this.preview = (this.file.markdown || this.file.code) && this.file.text !== '';
            });
    }
});
