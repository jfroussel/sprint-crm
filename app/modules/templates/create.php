<?php
namespace App\Modules\Templates;

use App\AbstractController;

class Create extends AbstractController{
	protected $needAuth = true;
	
	function store($data){
		return $this->db['template']->simpleEntity($data)->store();
	}
		
}
