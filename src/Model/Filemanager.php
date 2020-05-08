<?php namespace Haruncpi\LaravelSimpleFilemanager\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Filemanager extends Model
{
    protected $table = "filemanager";
    protected $casts = ['extra' => 'json'];
    protected $fillable = ['name', 'ext', 'file_size', 'absolute_url', 'extra'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    }
}