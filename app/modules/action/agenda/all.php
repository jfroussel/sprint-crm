<?php
namespace App\Modules\Action\Agenda;

use App\AbstractController;

class All extends AbstractController{
	protected $needAuth = true;
	
	function delete($id){
		unset($this->db['agenda'][$id]);
	}
}
