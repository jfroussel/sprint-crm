<?php
namespace App\Modules\Action\Schedule;

use App\AbstractController;

class All extends AbstractController{
	protected $needAuth = true;
	
	function delete($id){
		unset($this->db['schedule'][$id]);
	}
}
