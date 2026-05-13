<?php
namespace Leazycms\Web\Inc;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Cache;
class Menu
{
    protected $dataloop;
    protected $take;
    protected static $requestCache = null;

    public function __construct($name,$take=false)
    {   $this->take = $take;
        if (self::$requestCache === null) {
            self::$requestCache = Cache::get('menu', []);
        }
        $this->dataloop = collect(self::$requestCache)->where('slug', $name)->first()?->data_loop;
    }

    public function __invoke(){
        return $this->dataloop ? ($this->take ? collect($this->dataloop)->where('menu_parent',0)->take($this->take):collect($this->dataloop)->where('menu_parent',0)) : [];
    }

    public function sub($id){
        return $this->dataloop ? collect($this->dataloop)->where('menu_parent',$id) : [];

    }
    public function parent($id){
        return $this->dataloop ? collect($this->dataloop)->where('menu_parent',$id) : [];

    }
}
