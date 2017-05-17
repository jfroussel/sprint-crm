<?php
namespace App\Modules\Action\Promise;

use App\AbstractController;

class All extends AbstractController{
    protected $needAuth = true;

    function delete($id){
        unset($this->db['promise'][$id]);
    }
}
