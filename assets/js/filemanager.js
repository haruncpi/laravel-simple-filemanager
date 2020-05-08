    var filemanager ={
        baseUrl:'',
        ckBrowseUrl: ''
    };
    filemanager.ckBrowseUrl = filemanager.baseUrl+'/admin/filemanager?editor=ckEditor'

    function _filemanagerWindow(url){
        var width = screen.width * 0.7,
            height = screen.height * 0.7;
        var iLeft = (screen.width - width) / 2;
        var iTop = (screen.height - height) / 2;
        var sOptions = "toolbar=no,location=no,directories=no,status=no,menubar=no,resizable=yes,dependent=yes";
        sOptions += ",width=" + width;
        sOptions += ",height=" + height;
        sOptions += ",left=" + iLeft;
        sOptions += ",top=" + iTop;
        window.open(url, "Filemanager", sOptions);
    }
    function callbackTinyMceEditor(field_name, url, type, win) {
        console.log('field name:', field_name)
        var w = window,
            d = document,
            e = d.documentElement,
            g = d.getElementsByTagName('body')[0],
            x = w.innerWidth || e.clientWidth || g.clientWidth,
            y = w.innerHeight || e.clientHeight || g.clientHeight;

        var _url = filemanager.baseUrl + '/admin/filemanager?&editor=tinyMCE';
        tinyMCE.activeEditor.windowManager.open({
            file: _url,
            title: 'Filemanager',
            width: x * 0.8,
            height: y * 0.8,
            resizable: "yes",
            close_previous: "no"
        })
    }

    function selectFile(inputId) {
        var _url = filemanager.baseUrl + '/admin/filemanager?&input_id=' + inputId
        _filemanagerWindow(_url)
    }

    function bulkSelectFile(callback) {
        var _url = filemanager.baseUrl + '/admin/filemanager?bulk=true&callback=' + callback
        _filemanagerWindow(_url)
    }
