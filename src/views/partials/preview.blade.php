<h2>@{{ preview.name }}</h2>

<p>
    {{trans('filemanager::filemanager.size')}}: @{{ formatBytes(preview.file_size) }} <br>
    {{trans('filemanager::filemanager.type')}}: @{{ preview.ext }} <br>
    <span ng-if="preview.extra">{{trans('filemanager::filemanager.dimension')}}: @{{ preview.extra.width+'x'+preview.extra.height}} <br></span>
    {{trans('filemanager::filemanager.created')}}: @{{ preview.created_at }} <br>
    {{trans('filemanager::filemanager.modified')}}: @{{ preview.updated_at }} <br>
</p>