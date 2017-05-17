<?php
namespace App\Modules\Creditors;

use App\AbstractController;

class All extends AbstractController{
	protected $needAuth = true;
	
	function delete($id){
		unset($this->db['creditor'][$id]);
	}
}
