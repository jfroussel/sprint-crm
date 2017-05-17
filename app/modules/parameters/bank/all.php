<?php
namespace App\Modules\Parameters\Bank;

use App\AbstractController;

class All extends AbstractController{
	protected $needAuth = true;
	
	function delete($id){
		unset($this->db['bank'][$id]);
	}
}
