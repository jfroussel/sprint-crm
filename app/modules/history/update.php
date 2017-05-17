<?php
namespace App\Modules\History;

use App\AbstractController;

class Update extends AbstractController{
    protected $needAuth = true;
    function load($id){
        return $this->db['history'][$id];
    }
}
