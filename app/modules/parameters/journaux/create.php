<?php
namespace App\Modules\Parameters\Journaux;

use App\AbstractController;

class Create extends AbstractController{
	protected $needAuth = true;
	
	function store($data){
		return $this->db['journaux']->simpleEntity($data)->store();
	}
		
}
