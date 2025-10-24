<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.1/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/dracula.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.1/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.1/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.1/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.1/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.1/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.1/mode/clike/clike.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.1/mode/php/php.min.js"></script>
<script>
       var editor = CodeMirror.fromTextArea(document.getElementById("editor"), {
            lineNumbers: true,
            mode: "{{ $type }}",
            matchBrackets: true,
            indentUnit: 4,
            indentWithTabs: true,
            theme: "dracula",
            lineWrapping: true
        });
        $('.CodeMirror').attr('style','height:74vh;border-radius:10px;border:5px solid #111');
</script>
