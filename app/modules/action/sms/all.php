<?php
namespace App\Modules\Action\Sms;

use App\AbstractController;

class All extends AbstractController{
	protected $needAuth = true;
	
	function delete($id){
		unset($this->db['sms'][$id]);
	}
}
