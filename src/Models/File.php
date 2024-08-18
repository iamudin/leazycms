<?php
namespace Leazycms\Web\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Leazycms\Web\Traits\Fileable;
class File extends Model
{
    use Fileable;
    protected $fillable = ['file_path', 'file_type','file_auth','file_name','file_size','purpose','child_id','user_id'];
    protected $casts = ['created_at'=>'datetime'];
    public function fileable()
    {
        return $this->morphTo();
    }
    public function user(){
        return $this->belongsTo('Leazycms\Web\Models\User');
    }
    public function deleteFile()
    {
        Storage::delete($this->file_path);
        $this->delete();
    }
}
