<?php
namespace Leazycms\Web\Inc;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Cache;
class Menu
{
    protected $dataloop;
    protected $take;
    public function __construct($name,$take=false)
    {   $this->take = $take;
        $this->dataloop = Cache::get('menu')->where('slug', $name)->first()?->data_loop;
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
