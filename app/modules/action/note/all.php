<?php
namespace App\Modules\Action\Note;

use App\AbstractController;

class All extends AbstractController{
	protected $needAuth = true;
	
	function delete($id){
		unset($this->db['note'][$id]);
	}
}
