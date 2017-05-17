<?php
namespace App\Modules\Parameters\Journaux;

use App\AbstractController;

class All extends AbstractController{
	protected $needAuth = true;
	
	function delete($id){
		unset($this->db['journaux'][$id]);
	}
}
