<?php namespace Haruncpi\LaravelSimpleFilemanager\Controllers;

use App\Http\Controllers\Controller;

use Haruncpi\LaravelSimpleFilemanager\Classes\SimpleImage;
use Haruncpi\LaravelSimpleFilemanager\Model\Filemanager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class FilemanagerController extends Controller
{
    private $fields;
    private $orderTypes;
    private $basePath;
    protected $tableName = 'filemanager';
    protected $config;
    protected $tinyPNGtoken = "";

    public function __construct()
    {
        $this->fields = ['id', 'name', 'ext', 'file_size', 'absolute_url', 'extra', 'created_at', 'updated_at'];
        $this->orderTypes = ['asc', 'desc'];
        $this->basePath = public_path('filemanager/uploads');
        $this->baseUrl = url('filemanager/uploads');
        $this->config = config('filemanager');
        $this->imageFormat = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    }

    public function getIndex(Request $request)
    {
        if ($request->has('action')) {
            $action = $request->get('action');

            switch ($action) {
                case 'files':
                    return $this->getFiles($request);
                    break;
                default:
                    break;
            }
        }

        $thumbUrl = $this->baseUrl . '/thumbs';
        $acceptedFiles = implode(',', preg_filter('/^/', '.', array_map('trim', explode(',', config('filemanager.allow_format', 'jpg,png')))));

        return view('filemanager::index', [
            'thumbUrl'      => $thumbUrl,
            'acceptedFiles' => $acceptedFiles
        ]);
    }

    public function postAction(Request $request)
    {
        if ($request->has('action')) {
            $action = $request->get('action');
            switch ($action) {
                case 'upload':
                    return $this->postUpload($request);
                    break;
                case 'updateName':
                    return $this->postUpdateName($request);
                    break;
                case 'convert':
                    return $this->postConvertImage($request);
                    break;
                case 'delete':
                    return $this->postDelete($request);
                    break;
            }
        }
    }

    public function getFiles(Request $r)
    {
        $data = new Filemanager();
        $data = $data->orderBy('id', 'desc');
        $data = $data->where('user_id', Auth()->user()->id);

        if ($r->has('q')) {
            $q = $r->get('q');
            if ($q !== 'undefined' && $q !== '' && !empty($q)) {
                $data = $data->where('name', 'like', '%' . $q . '%');
            }
        }

        if ($r->has('file_type')) {
            $type = $r->get('file_type');
            switch ($type) {
                case 'image';
                    $data = $data->whereIn('ext', $this->imageFormat);
                    break;
            }
        }

        return $data->paginate(20);
    }

    private function getAbsoluteUrl($fileName)
    {
        return $this->baseUrl . '/' . $fileName;
    }

    private function postConvertImage(Request $request)
    {
        $id = $request->get('id');
        $name = $request->get('name');
        $ext = $request->get('ext');
        $convertExt = $request->get('format');
        $imageExtensions = ['png', 'jpg', 'gif', 'jpeg', 'webp'];

        if (!in_array($ext, $imageExtensions)) {
            return ['success' => false, 'msg' => 'Only image can convert'];
        }

        if (!in_array($convertExt, $imageExtensions)) {
            return ['success' => false, 'msg' => "Only image are convertable"];
        }

        if ($ext == $convertExt) {
            return ['success' => false, 'msg' => "Image already in $convertExt format"];
        }

        //end validation

        $fromFile = $this->basePath . '/' . $name;
        $newName = basename($name, "." . $ext) . ".$convertExt";
        $newFile = $this->basePath . '/' . $newName;


        try {
            //recreate and delete old
            (new SimpleImage())->fromFile($fromFile)
                ->toFile($newFile, "image/$convertExt");

            File::delete($fromFile);

            //delete thumb and recreate new thumb
            File::delete($this->basePath . '/thumbs/' . $name);
            $this->makeThumb($newFile);

            $fileSizeInByte = File::size($newFile);
            $data = Filemanager::find($id);

            $data->update([
                'name'         => $newName,
                'file_size'    => $fileSizeInByte,
                'ext'          => $convertExt,
                'absolute_url' => $this->getAbsoluteUrl($newName)
            ]);

            return ['success' => true, 'msg' => 'Convert success', 'data' => $data];
        } catch (\Exception $e) {
            return ['success' => false, 'msg' => $e->getMessage()];
        }
    }

    private function postUpdateName(Request $request)
    {
        $id = $request->get('id');
        $name = $request->get('name');
        $ext = $request->get('ext');
        $finalName = $name . '.' . $ext;

        $oldData = Filemanager::findOrFail($id);
        if ($oldData->name == $finalName) {
            return ['success' => true, 'msg' => 'filename not changed'];
        };

        $data = Filemanager::whereNotIn('id', [$id])->where('name', $finalName)->count();
        if ($data) {
            return ['success' => false, 'msg' => 'Filename already exist'];
        }

        try {
            DB::beginTransaction();

            $fromName = $this->basePath . '/' . $oldData->name;
            $toName = $this->basePath . '/' . $finalName;

            File::move($fromName, $toName);

            $imageExtensions = ['png', 'jpg', 'gif', 'jpeg', 'webp'];
            if (in_array($oldData->ext, $imageExtensions)) {
                $fromThumb = $this->basePath . '/thumbs/' . $oldData->name;
                $toThumb = $this->basePath . '/thumbs/' . $finalName;
                File::move($fromThumb, $toThumb);
            }


            $oldData->update([
                'name'         => $finalName,
                'absolute_url' => $this->getAbsoluteUrl($finalName)
            ]);
            DB::commit();
            return [
                'success' => true, 'msg' => 'successfully updated',
                'data'    => $oldData
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'msg' => $e->getMessage()];
        }


    }

    public function makeThumb($sourcePath)
    {
        $filename = basename($sourcePath);
        $pathToSave = $this->basePath . '/thumbs';

        if (!file_exists($pathToSave)) {
            mkdir($pathToSave, 0777, true);
        }

        $savePathWithName = $pathToSave . '/' . $filename;

        (new SimpleImage())->fromFile($sourcePath)
            ->resize(40, 40)
            ->toFile($savePathWithName);


    }

    private function handleRollback(\Exception $e, $fileName)
    {
        Log::error($e->getMessage());
        DB::rollBack();
        $this->deleteFile($fileName);
        return ['success' => false, 'msg' => $e->getMessage()];
    }

    public function postUpload(Request $request)
    {

        $allowTypes = str_replace(' ', '', config('filemanager.allow_format'));
        $validator = Validator::make($request->all(), [
            'photo.*' => ['required', 'mimes:' . $allowTypes, 'max:' . config('filemanager.max_size')],
        ], [], ['photo' => 'file']);

        if ($validator->fails()) {
            $msg = $validator->errors()->first();
            return response()
                ->json([
                    'success' => false,
                    'msg'     => $msg
                ], 500);
        }

        if (!$request->hasFile('photo')) {
            return response()->json(['success' => false, 'msg' => 'No file selected'], 500);
        }

        $defaultWidth = config('filemanager.max_image_width', 1024);
        if (!is_numeric($defaultWidth)) throw new \Exception('max image width value must be integer value');

        $imageQuality = config('filemanager.image_quality', 100);
        if (!is_numeric($imageQuality)) throw new \Exception('image quality value must be integer value');


        $photo = $request->file('photo');
        $fileName = $photo->getClientOriginalName();
        $filePath = $this->basePath . '/' . $fileName;
        $pathInfo = pathinfo($fileName);
        $extension = $photo->getClientOriginalExtension();
        $fileSizeInByte = $photo->getSize();
        $dateTime = date('Y-m-d H:i:s');
        $imageExtensions = ['png', 'jpg', 'gif', 'jpeg', 'webp'];
        $extra = [];
        DB::beginTransaction();

        if (!file_exists($this->basePath)) {
            mkdir($this->basePath, 0777, true);
        }

        try {
            //duplicate check and rename if exist
            if (File::exists($filePath)) {
                $ext = isset($pathInfo['extension']) ? ('.' . $pathInfo['extension']) : '';
                // Look for a number before the extension; add one if there isn't already
                if (preg_match('/(.*?)(\d+)$/', $pathInfo['filename'], $match)) {
                    // Have a number; get it
                    $base = $match[1];
                    $number = intVal($match[2]);
                } else {
                    // No number; pretend we found a zero
                    $base = $pathInfo['filename'];
                    $number = 0;
                }

                // Choose a name with an incremented number until a file with that name
                // doesn't exist
                do {
                    $fileName = $base . ++$number . $ext;
                } while (File::exists($this->basePath . '/' . $fileName));
            }
        } catch (\Exception $e) {
            $this->handleRollback($e, $fileName);
        }

        if (in_array($extension, $imageExtensions)) {
            try {
                $savePathWithName = $this->basePath . '/' . $fileName;

                (new SimpleImage())->fromFile($photo)
                    ->resize($defaultWidth)
                    ->toFile($savePathWithName, null, $imageQuality);

                $fileSizeInByte = File::size($savePathWithName);

                list($width, $height) = getimagesize($savePathWithName);
                $extra['width'] = $width;
                $extra['height'] = $height;


            } catch (\Exception $e) {
                return $this->handleRollback($e, $fileName);
            }

        } else {
            $photo->move($this->basePath, $fileName);
        }


        try {
            $data = [
                'name'         => $fileName,
                'ext'          => $extension,
                'file_size'    => $fileSizeInByte,
                'user_id'      => Auth()->user()->id,
                'absolute_url' => $this->getAbsoluteUrl($fileName),
                'created_at'   => $dateTime,
                'updated_at'   => $dateTime,
                'extra'        => json_encode($extra)
            ];
            $insertId = DB::table($this->tableName)->insertGetId($data);
        } catch (\Exception $e) {
            return $this->handleRollback($e, $fileName);
        }

        //make thumbnail image
        try {
            $finalFilePath = $this->basePath . '/' . $fileName;
            if (in_array($extension, $imageExtensions)) {
                $this->makeThumb($finalFilePath);
            }
        } catch (\Exception $e) {
            return $this->handleRollback($e, $fileName);
        }
        //make thumbnail image

        DB::commit();
        $data = Filemanager::find($insertId);
        return [
            'success' => true,
            'data'    => $data,
            'msg'     => 'Upload successful'
        ];

    }

    public function deleteFile($fileName)
    {
        try {
            //delete main file
            File::delete($this->basePath . '/' . $fileName);
            //delete thumb
            File::delete($this->basePath . '/thumbs/' . $fileName);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function postDelete(Request $request)
    {
        $id = $request->input('id');
        $userId = Auth()->user()->id;

        $data = DB::table($this->tableName)
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        $query = DB::table($this->tableName)
            ->where('id', $id)
            ->where('user_id', $userId);

        if ($query->count()) {
            if (File::exists($this->basePath . '/' . $data->name)) {
                try {
                    $this->deleteFile($data->name);
                    //delete records from db
                    $query->delete();
                } catch (\Exception $e) {
                }
            } else {
                $query->delete();
            }
            return ['success' => true, 'msg' => 'Delete successfully'];
        }
    }

}