<?php
namespace App\Modules\Templates;

use App\AbstractController;

class All extends AbstractController{
	protected $needAuth = true;
	
	function delete($id){
		unset($this->db['template'][$id]);
	}
}
