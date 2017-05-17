<?php
namespace App\Modules\Parameters\Bank;

use App\AbstractController;

class Create extends AbstractController{
	protected $needAuth = true;
	
	function store($data){
		return $this->db['bank']->simpleEntity($data)->store();
	}
	
}
