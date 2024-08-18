<?php
namespace Leazycms\Web\Traits;
use Leazycms\Web\Models\File;

trait Fileable
{
    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function addFile($attribute)
    {
        $file = new File($attribute);
        return $this->file()->save($file);
    }
}
