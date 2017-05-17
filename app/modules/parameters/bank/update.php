<?php
namespace App\Modules\Parameters\Bank;

use App\AbstractController;

class Update extends AbstractController{
	protected $needAuth = true;
	
	function load($id){
		return [
			'bank' => $this->db['bank'][$id]
		];
	}
	
	function store($data){
		if(!isset($data['id'])) return;
		return $this->db['bank']->simpleEntity($data)->store();
	}

	
}
