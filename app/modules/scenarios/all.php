<?php
namespace App\Modules\Scenarios;

use App\AbstractController;

class All  extends AbstractController{
	
	protected $needAuth = true;
	
	function create($data=[]){
		return $this->db['scenario']->simpleEntity($data)->store();
	}
	
	function delete($id){
		unset($this->db['scenario'][$id]);
	}
}
