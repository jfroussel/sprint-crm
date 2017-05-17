<?php
namespace App\Modules\Creditors;

use App\AbstractController;

class Update extends AbstractController{
	protected $needAuth = true;
    function load($id){
		return $this->db['creditor'][$id];
	}
}
