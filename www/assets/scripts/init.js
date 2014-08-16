var basePath = '{{$basePath}}';

jQuery(document).ready(function() {
    Metronic.init(); // init metronic core componets
    Layout.init(); // init layout

    ComponentsPickers.init();
    HtmlEditors.init();
    ComponentsFormTools.init();
    Login.init();
    UIToastr.init();
    Fullscreen.init();
    TableManaged.init();
    ComponentsDropdowns.init();
    
    Background.init();
});