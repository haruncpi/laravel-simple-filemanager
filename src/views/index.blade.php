<!doctype html>
<html lang="en" ng-app="filemanager">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <title>{{trans('filemanager::filemanager.title')}}</title>
    <link rel="stylesheet" href="{{asset('filemanager/bundle/app.min.css')}}">
    <style type="text/css">
        [ng\:cloak],
        [ng-cloak],
        [data-ng-cloak],
        [x-ng-cloak],
        .ng-cloak,
        .x-ng-cloak {
            display: none !important;
        }
    </style>

    <script>
        window.translations = {!! collect(trans('filemanager::filemanager'))->toJson() !!}
    </script>
</head>

<body ng-cloak ng-controller="FilemanagerCtrl" class="{{config('app.locale')}}">
    <header>
        <h2 class="app_title"><img src="{{asset('filemanager/img/logo.png')}}"
                alt=""><span>{{trans('filemanager::filemanager.title')}}</span></h2>

        <div class="right">
            <button class="btn_bulk_actions danger" ng-click="bulkDelete()" ng-show="checkedIds.length>1"
                ng-disabled="bulkDeleting" ladda="bulkDeleting">{{trans('filemanager::filemanager.bulk-delete')}} (@{{
            checkedIds.length }})
            </button>

            <div class="search_box">
                <input type="text" placeholder="{{trans('filemanager::filemanager.search')}}" ng-model="q"
                    ng-model-options="{debounce:500}" ng-change="init(q)">
                <div class="searching" ng-show="searching">
                    <img src="{{asset('filemanager/img/ajax-loader.gif')}}" alt="loader" />
                </div>
                <div class="clear_search" ng-show="q" ng-click="clearSearch()">
                    <i class="fa fa-times"></i>
                </div>
            </div>
            <a class="btn-doc" href="https://laravelarticle.com/laravel-simple-filemanager"><i
                    class="fa fa-info-circle"></i> {{trans('filemanager::filemanager.doc')}}</a>
        </div>
    </header>
    <!--custom preview-->
    <div class="dz_custom_preview_box">
        <div class="dz_custom_preview">
            <div class="image">
                <img data-dz-thumbnail />
            </div>
            <div class="info">
                <div class="dz-filename"><span data-dz-name></span></div>
                <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
            </div>
        </div>
    </div>
    <!--custom preview-->

    <div class="wrapper">
        <div class="sidebar">
            <button class="btn_mobile_upload success" ng-click="mobileUploadOpen=1"><i class="fa fa-upload"></i>
                {{trans('filemanager::filemanager.upload')}}
            </button>
            {{--upload section--}}
            <div class="upload_section" ng-hide="previewOpen">
                <div class="btn_mobile_upload_close">
                    <button ng-click="mobileUploadOpen=0"><i class="fa fa-times"></i></button>
                </div>

                <p class="upload_title">{{trans('filemanager::filemanager.upload')}}</p>
                <input type="hidden" name="_token" value="{{csrf_token()}}">


                <div class="upload_area dropzone" options="dzOptions" callbacks="dzCallbacks" methods="dzMethods"
                    ng-dropzone></div>

                <div class="dz_preview_container"></div>
            </div>
            {{--end upload section--}}
            {{--preview section--}}
            <div class="preview_section" ng-show="previewOpen">
                <div class="preview_header">
                    <div class="title">{{trans('filemanager::filemanager.preview')}}</div>
                    <div class="button">
                        <button ng-click="closePreview()"><i class="fa fa-times"></i></button>
                    </div>
                </div>

                <div class="image_wrapper">
                    <img onerror="this.src='/filemanager/img/previews/missing.png'" ng-src="@{{ preview.absolute_url }}"
                        alt="@{{ preview.name }}">
                </div>
                <div class="info">
                    @include('filemanager::partials.preview')
                </div>
            </div>
            {{--end preview section--}}

        </div>
        <div class="content">
            <div class="gallery">
                <!--gallery header-->
                <div class="gl_item gl_header">
                    <div class="name">
                        <input type="checkbox" ng-model="bulk_select" ng-click="checkAll()">
                        {{trans('filemanager::filemanager.name')}}
                    </div>
                    <div class="size">{{trans('filemanager::filemanager.size')}}</div>
                    <div class="type">{{trans('filemanager::filemanager.type')}}</div>
                    <div class="modified">{{trans('filemanager::filemanager.modified')}}</div>
                    <div class="action">{{trans('filemanager::filemanager.action')}}</div>
                </div>
                <!--end gallery header-->
                <!--single-->
                <div class="gl_item" ng-repeat="photo in photos"
                    ng-class="checkedIds.indexOf(photo.id) !== -1 ? 'active':''">
                    <div class="name">
                        <input type="checkbox" ng-checked="checkedIds.indexOf(photo.id) != -1"
                            ng-click="toggleCheck(photo.id)">

                        <div class="thum" ng-click="toggleCheck(photo.id)">
                            <img ng-if="photo.ext=='png' ||
                                    photo.ext=='jpg' ||
                                    photo.ext=='jpeg' ||
                                    photo.ext=='webp' ||
                                    photo.ext=='gif'" ng-src="{{$thumbUrl}}/@{{ photo.name }}" alt="@{{ photo.name }}">

                            <p ng-if="photo.ext=='txt'"><i class="fa fa-file-text fa-2x"></i></p>
                            <p ng-if="photo.ext=='pdf'"><i class="fa fa-file-pdf-o fa-2x"></i></p>
                            <p ng-if="photo.ext=='doc'||photo.ext=='docx'"><i class="fa fa-file-word-o fa-2x"></i></p>
                            <p ng-if="photo.ext=='xls'||photo.ext=='xlsx'"><i class="fa fa-file-excel-o fa-2x"></i></p>

                        </div>
                        <p ng-click="toggleCheck(photo.id)">@{{ photo.name }}</p>
                    </div>
                    <div class="size">@{{ formatBytes(photo.file_size) }}</div>
                    <div class="type">@{{ photo.ext }}</div>
                    <div class="modified">@{{ photo.updated_at }}</div>
                    <div class="action">
                        @include('filemanager::partials.action')
                    </div>
                </div>
                <!--single-->
                <!--load more-->
                <div class="load_more" ng-hide="data.last_page === currentPage">Loading ...</div>
                <!--#load more-->
            </div>
            <div class="content_footer">
                <div class="left">
                    {{--<strong>@{{checkedIds.length}}</strong> {{trans('filemanager::filemanager.item-selected')}}--}}
                    <span>
                        @{{ showing_file_translation }}
                    </span>
                </div>
                <div class="right">
                    <button type="button" ng-click="bulkSelect()" ng-if="bulkMode"
                        ng-disabled="!checkedIds.length">{{trans('filemanager::filemanager.bulk-select')}}
                        (@{{ checkedIds.length }})
                    </button>
                </div>
            </div>
        </div>

        <!--mobile -->
        @include('filemanager::partials.mobile')
        <!--mobile -->

        <!--popup -->
        @include('filemanager::partials.popup')
        <!--popup -->
    </div>

    <script src="{{asset('filemanager/bundle/app.min.js')}}"></script>

    <script>
        var _DEBUG = false;
    Dropzone.autoDiscover = false;

    if (!_DEBUG) {
        console.log = function () {
        }
    }

    var filemanager = angular.module('filemanager', ['thatisuday.dropzone', 'angular-ladda'])
    filemanager.controller('FilemanagerCtrl', function ($scope, $http, $q, $window, $timeout) {

        var csrf_token = document.querySelector('input[name="_token"]').value;
        $scope.preview = null;
        $scope.previewOpen = false;
        $scope.mobileUploadOpen = false;
        $scope.mobilePreviewOpen = false;
        $scope.bulkDeleting = false;
        $scope.bulkMode = false;
        $scope.selectMode = false;
        $scope.searching = false;
        $scope.editNamePopup = false;

        $scope.data = {};
        $scope.currentPage = 1;
        var url = '';

        var urlParam = function (name) {
            var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
            if (results)
                return results[1];
            else
                return 0;
        };
        var serialize = function (obj) {
            var str = [];
            for (var p in obj)
                if (obj.hasOwnProperty(p)) {
                    str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                }
            return str.join("&");
        };

        if (urlParam('bulk')) {
            $scope.bulkMode = true;
            console.log($scope.bulkMode)
        }
        if (urlParam('input_id') || urlParam('editor')) {
            $scope.selectMode = true;
        }


        $scope.formatBytes = function (bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';

            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

            const i = Math.floor(Math.log(bytes) / Math.log(k));

            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        };

        var params = {
            action: 'files',
            page: 1
        };
        if (urlParam('file_type')) {
            params.file_type = urlParam('file_type')
        }

        $scope.init = function (q) {

            if (q === '' || q === undefined) {
                url = '{{route('filemanager.base_route')}}?' + serialize(params)
            } else {
                $scope.searching = true;
                $scope.currentPage = 1;
                url = '{{route('filemanager.base_route')}}?action=files&page=1&q=' + q
            }
            $http.get(url)
                .success(function (data) {
                    //console.log(data)
                    $scope.data = data;
                    $scope.photos = data.data;
                    $scope.searching = false;
                })
        };

        $scope.init();

        $scope.clearSearch = function () {
            $scope.q = null;
            $scope.init();
        };

        $scope.updateTranslation = function () {
            var total = 0, current = 0;
            if ($scope.data.total) {
                current = $scope.photos.length;
                total = $scope.data.total
            }
            $scope.showing_file_translation = translations['showing-files'].replace(':current', current)
                .replace(':total', total);
        };

        $scope.$watch('photos', function () {
            $scope.updateTranslation();
        });

        function updateURLParameter(url, param, paramVal) {
            var href = new URL(url);
            href.searchParams.set(param, paramVal);
            return href.toString();
        }

        $scope.loadMore = function () {
            if ($scope.data.last_page === $scope.currentPage) {
                // console.log('no more data for loading');
                return;
            }
            url = updateURLParameter(url, 'page', ++$scope.currentPage);
            // console.log(url)

            $http.get(url)
                .success(function (data) {
                    //console.log(data)
                    $scope.data = data;
                    $scope.photos = $scope.photos.concat(data.data);
                })
        };


        var _target = $('.gallery')
        _target.on('scroll', function () {
            if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
                // console.log('end reached');
                $scope.loadMore()
            }
        });


        $scope.checkedIds = [];

        $scope.toggleCheck = function (id) {
            if ($scope.checkedIds.indexOf(id) === -1) {
                $scope.checkedIds.push(id);
            } else {
                $scope.checkedIds.splice($scope.checkedIds.indexOf(id), 1);
            }
        };


        $scope.checkAll = function () {
            if ($scope.bulk_select) {
                $scope.checkedIds = [];
                $scope.photos.forEach(function (photo) {
                    $scope.checkedIds.push(photo.id);
                });
            } else {
                $scope.checkedIds = [];
            }
        };

        var defaultOptions = {
            url: '{{route('filemanager.base_route')}}?action=upload',
            autoProcessQueue: true,
            parallelUploads: 1,
            paramName: 'photo',
            acceptedFiles: '{{$acceptedFiles}}',
            addRemoveLinks: false,
            sending: function (file, xhr, formData) {
                formData.append('_token', csrf_token);
            },
            dictDefaultMessage: "{{trans('filemanager::filemanager.upload-message')}}",
            previewTemplate: document.querySelector('.dz_custom_preview_box').innerHTML,
        };

        $scope.dzOptions = Object.assign({}, defaultOptions);
        $scope.dzOptions.previewsContainer = document.querySelector('.dz_preview_container');

        $scope.dzOptionsMobile = Object.assign({}, defaultOptions);
        $scope.dzOptionsMobile.previewsContainer = document.querySelector('.mobile_preview_container')


        $scope.dzCallbacks = {
            'addedfile': function (file) {
                var ext = file.name.split('.').pop();
                // console.log(file.previewElement)
                switch (ext) {
                    case "pdf":
                        $(file.previewElement).find("img").attr("src", "/filemanager/img/previews/pdf.png");
                        break;
                    case "doc":
                    case "docx":
                        $(file.previewElement).find("img").attr("src", "/filemanager/img/previews/doc.png");
                        break;
                    case "txt":
                        $(file.previewElement).find("img").attr("src", "/filemanager/img/previews/txt.png");
                        break;
                    case "xls":
                    case "xlsx":
                        $(file.previewElement).find("img").attr("src", "/filemanager/img/previews/xls.png");
                        break;
                    default:
                        break;
                }
            },
            'success': function (file, xhr) {
                if (!xhr.success) {
                    alert(xhr.msg)
                }
                console.log('success callback:');
                console.log(xhr)
                $scope.dzMethods.removeFile(file);
                $scope.mobileUploadOpen = false;

                $scope.photos.unshift(xhr.data)
                // $scope.$apply()
            },
            'error': function (file, xhr) {
                $scope.dzMethods.removeFile(file)
                if (typeof xhr === 'string') {
                    alert(xhr)
                } else {
                    alert(xhr.msg)
                }
            },
            'queuecomplete': function (files, xhr) {
                $scope.dzMethods.removeAllFiles()
                $scope.init();
                console.log('queue completed');
                //flash message
                var el = $('.dz-default span');
                var oldText = "{{trans('filemanager::filemanager.upload-message')}}";
                el.text("{{trans('filemanager::filemanager.success-upload')}}");
                setTimeout(function () {
                    el.text(oldText)
                }, 3000)
                //flash message
            }
        };

        $scope.dzMethods = {};

        $scope.isPreviewable = function (file) {
            return file.ext === 'png' ||
                file.ext === 'jpg' ||
                file.ext === 'jpeg' ||
                file.ext === 'webp' ||
                file.ext === 'gif'
        };
        $scope.previewPhoto = function (row) {
            $scope.preview = row;
            $scope.previewOpen = true;
        };
        $scope.mobilePreview = function (row) {
            $scope.preview = row;
            $scope.mobilePreviewOpen = true;
        };

        $scope.closePreview = function () {
            $scope.previewOpen = false;
        };


        $scope.bulkSelect = function () {
            if (urlParam('bulk') && urlParam('callback')) {
                var _callback = urlParam('callback')

                if ($scope.checkedIds.length > 0) {
                    var rows = $scope.photos.filter(function (photo) {
                        return $scope.checkedIds.indexOf(photo.id) > -1;
                    });
                    console.log(rows)
                    if (typeof window.opener[_callback] === "function") {
                        window.opener[_callback](rows);

                        //custom event trigger
                        var _bulkselect_event = new CustomEvent('filemanager.bulkselect', {
                            bubbles: false,
                            detail: {data: rows}
                        });
                        window.opener.dispatchEvent(_bulkselect_event);
                        //custom event

                        window.close();
                    } else {
                        alert(_callback + ' callback function not define ' +
                            'or your defined it inside document ready. ' +
                            'Please try to define it outside of document ready')
                    }
                }
            }
        };

        $scope.select = function (row) {

            //this from tinyMCE editor filemanager
            if (urlParam('editor')) {
                
                if (urlParam('editor') === 'tinyMCE') {
                    if (typeof parent.tinyMCE !== "undefined") {
                        var version = parseInt(parent.tinyMCE.majorVersion)
                        if(version===4){
                            $('.mce-textbox', window.parent.document).eq(0).val(row.absolute_url);
                            $('.mce-textbox', window.parent.document).eq(1).val(row.name);
                            var ed = parent.tinymce.editors[0]; //get the first editor
                            ed.windowManager.windows[1].close();
                        }
                        else if(version===5){
                            $('.tox-textfield', window.parent.document).eq(0).val(row.absolute_url);
                            $('.tox-textfield', window.parent.document).eq(1).val(row.name);
                            var ed = parent.tinymce.editors[0]; //get the first editor
                            ed.windowManager.close();
                        }
                        else{
                            throw("TinyMCE version:"+version+" is not supported")
                        }
                    }
                }

                if (urlParam('editor') === 'ckEditor') {
                    window.opener.CKEDITOR.tools.callFunction(urlParam('CKEditorFuncNum'), row.absolute_url, '');
                    window.close();
                }
                
                if (urlParam('editor') === 'summernote') {

                    if (urlParam('note')) {
                        //custom event trigger
                        var noteId = urlParam('note');
                        row.note = noteId
                        var _select_event = new CustomEvent('filemanager.select', {
                            bubbles: false,
                            detail: {data: row}
                        });
                        window.opener.dispatchEvent(_select_event);
                        //custom event

                        window.close();
                    }

                }
            }

            //this for normal button filemanager
            if (urlParam('input_id')) {
                if (window.opener.document.getElementById(urlParam('input_id'))) {

                    var el = window.opener.document.getElementById(urlParam('input_id'))
                    if (el.value !== 'undefined') el.value = row.absolute_url;
                    if (el.text !== 'undefined') el.text = row.absolute_url;
                    if (el.src !== 'undefined') el.src = row.absolute_url;

                    //for image preview
                    if (window.opener.document.getElementById(urlParam('input_id') + '-preview')) {
                        var previewEl = window.opener.document.getElementById(urlParam('input_id') + '-preview');
                        if (previewEl.src !== 'undefined') previewEl.src = row.absolute_url;
                    }
                    //custom event trigger
                    var _select_event = new CustomEvent('filemanager.select', {
                        bubbles: false,
                        detail: {data: row}
                    });
                    window.opener.dispatchEvent(_select_event);
                    //custom event
                } else {
                    console.log(urlParam('input_id') + ' input id not found');
                }
                parent.window.close()
            }
        };

        $scope.bulkDelete = function () {
            var promises = [];

            if (confirm("This action will delete " + $scope.checkedIds.length + " number of files. Are you sure?")) {
                $scope.bulkDeleting = true;

                $scope.checkedIds.forEach(function (id) {
                    var promise = $http.post('{{route('filemanager.base_route')}}?action=delete', {'id': id});
                    promises.push(promise);
                });

                var mapPromiseCallback = function (p, index) {
                    return p.then(function (data) {
                        // console.log(data)
                    });
                }

                $q.all(promises.map(mapPromiseCallback)).then(function () {
                    $scope.init();
                    $scope.checkedIds = [];
                    $scope.bulkDeleting = false;
                    $scope.bulk_select = false
                });

            }
        }

        $scope.deletePhoto = function (photo, index) {

            if (confirm("Are you sure?")) {
                photo.deleting = true
                $http.post('{{route('filemanager.base_route')}}?action=delete', {'id': photo.id})
                    .success(function (data) {
                        console.log(data)
                        photo.deleting = false
                        $scope.previewOpen = false;
                        $scope.preview = null;
                        $scope.photos.splice(index, 1);
                    })
            }
        };

        //edit part
        $scope.selected = {};
        $scope.selectedIndex = null;
        $scope.editName = function (row, index) {
            $scope.selectedIndex = index;
            console.log(index)
            var data = Object.assign({}, row);
            $scope.selected = data;
            $scope.selected.name = data.name.split('.')[0]
            $scope.editNamePopup = true;
            $timeout(function () {
                $window.document.getElementById('edit_name').focus()
            })
        };

        $scope.updateName = function (row) {

            if (!confirm('Are you sure?')) return;
            row.nameUpdating = true;

            $http.post('{{route('filemanager.base_route')}}?action=updateName', row)
                .success(function (data) {
                    if (data.success) {
                        $scope.editNamePopup = false;
                        $scope.selected = {}
                        row.nameUpdating = false;
                        // $scope.init()

                        $scope.photos[$scope.selectedIndex] = data.data;
                        if ($scope.isPreviewable(row)) {
                            $scope.preview = data.data
                        }

                    } else {
                        alert(data.msg)
                    }
                    console.log(data)
                }).error(function (err) {
            });
        };


        $scope.convertPopup = false;
        $scope.imageFormats = ['webp', 'jpeg', 'png'];
        $scope.selectedFormat = '';

        $scope.openConvertPopup = function (photo, index) {
            $scope.convertPopup = true;
            $scope.selectedPhoto = photo;
            $scope.selectedFormat = '';
            $scope.selectedIndex = index
        };

        $scope.selectFormat = function (format) {
            $scope.selectedFormat = format
        };

        $scope.convert = function (photo, format) {

            if (!confirm('Are you sure?')) return;
            photo.converting = true;
            photo.format = format;

            $http.post('{{route('filemanager.base_route')}}?action=convert', photo)
                .success(function (data) {
                    if (data.success) {
                        photo.converting = false;
                        $scope.convertPopup = false;
                        $scope.photos[$scope.selectedIndex] = data.data;
                        if ($scope.isPreviewable(photo)) {
                            $scope.preview = data.data
                        }
                        // $scope.init()
                    } else {
                        alert(data.msg)
                    }
                    console.log(data)
                })
                .error(function (err) {
                    alert('something went wrong')
                });
        }

    })
    </script>
</body>

</html>