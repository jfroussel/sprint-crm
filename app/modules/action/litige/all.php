<?php
namespace App\Modules\Action\Litige;

use App\AbstractController;

class All extends AbstractController{
	protected $needAuth = true;
	
	function delete($id){
		unset($this->db['litige'][$id]);
	}
}
