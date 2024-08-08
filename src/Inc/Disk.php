<?php
namespace Leazycms\Web\Inc;

use Illuminate\Http\Request;
use Closure;
class Disk
{

    public function __invoke()
    {
        return $this->__toString();
    }

    public function __toString()
    {
        // Return a meaningful string representation of the object
        return '';
    }
     function percentTotalUsed(){
        return round($this->getPath() / $this->getSpace() * 100,2);
    }
     function getPath(){
        return getDirectorySize('/');
    }
    function getSpace(){
        return GBtoBytes(2);
    }
     function totalAvailable(){
        return size_as_kb($this->getSpace() - $this->getPath());
    }
     function totalUsed(){
        return size_as_kb($this->getPath());
    }
     function totalSpace(){
        return size_as_kb($this->getSpace());
    }
}
