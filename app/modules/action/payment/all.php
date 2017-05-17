<?php
namespace App\Modules\Action\Payment;

use App\AbstractController;

class All extends AbstractController{
	protected $needAuth = true;
	
	function delete($id){
		unset($this->db['payment'][$id]);
	}
}
