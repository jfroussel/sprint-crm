<?php
namespace App\Modules\Users;

use App\AbstractController;

class All extends AbstractController{
	protected $needAuth = true;
	
	function delete($id){
		unset($this->db['user'][$id]);
	}
}
