let file = {
    "id": "21",
    "name": "test.md",
    "date": "Today 20:56",
    "code": false,
    "markdown": true,
    "text": "### Hej\n\n```\n<?php\necho 'hej';\n```",
    "downloadLink": "http://localhost:9001/dl.php/go8kt/?fdl=1"
};


Vue.component('preview', {
    props : ['file'],
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
                            <a class="preview-toggle" href="#">
                                <i class="fas fa-edit"></i>
                                Edit
                            </a>&nbsp;
                            <a id="EditorDownload" class="" href="#">
                                <i class="fas fa-cloud-download-alt"></i>
                                Download
                            </a>
                        </small>
                    </div>
                </h5>

                <div class="card-body">
                    <div id="EditorPreview">
                        {{ file.text }}
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
        test : 'hej',
        file : file,
        preview : true
    }
});
