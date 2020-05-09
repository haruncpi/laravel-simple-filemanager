<button ng-if="isPreviewable(photo)"
        class="btn_desktop_preview select"
        type="button"
        title="{{trans('filemanager::filemanager.preview')}}"
        ng-click="previewPhoto(photo)"><i class="fa fa-eye"></i>
</button>

<button ng-if="isPreviewable(photo)"
        class="btn_mobile_preview select"
        type="button"
        title="{{trans('filemanager::filemanager.preview')}}"
        ng-click="mobilePreview(photo)"><i class="fa fa-eye"></i>
</button>

<button type="button"
        ng-click="editName(photo,$index)"
        class="default"><i class="fa fa-edit"></i></button>

<button type="button"
        title="convert"
        ng-if="isPreviewable(photo)"
        ng-click="openConvertPopup(photo,$index)"
        class="default"><i class="fa fa-recycle"></i></button>

<button type="button"
        ng-click="select(photo)"
        ng-show="!bulkMode && selectMode"
        class="select"><i class="fa fa-check"></i></button>

<a download ng-href="@{{ photo.absolute_url }}" class="btn warning"><i
            class="fa fa-download"></i></a>
<button type="button" class="delete" ladda="photo.deleting"
        ng-click="deletePhoto(photo,$index)">
    <i class="fa fa-trash"></i>
</button>