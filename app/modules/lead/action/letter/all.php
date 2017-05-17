<?php
namespace App\Modules\Action\Letter;

use App\AbstractController;

class All extends AbstractController{
	protected $needAuth = true;
	
	function delete($id){
		unset($this->db['letter'][$id]);
	}
}
