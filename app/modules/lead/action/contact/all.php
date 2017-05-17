<?php
namespace App\Modules\Action\Contact;

use App\AbstractController;

class All extends AbstractController{
	protected $needAuth = true;
	
	function delete($id){
		unset($this->db['contact'][$id]);
	}
}
