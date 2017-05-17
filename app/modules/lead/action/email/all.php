<?php
namespace App\Modules\Action\Email;

use App\AbstractController;

class All extends AbstractController{
	protected $needAuth = true;
	
	function delete($id){
		unset($this->db['email'][$id]);
	}
}
