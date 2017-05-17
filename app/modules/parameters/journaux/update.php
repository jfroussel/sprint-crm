<?php
namespace App\Modules\Parameters\Journaux;

use App\AbstractController;

class Update extends AbstractController{
	protected $needAuth = true;
	
	function load($id){
		return [
			'journaux' => $this->db['journaux'][$id]
		];
	}
	function store($data){
		if(!isset($data['id'])) return;
		return $this->db['journaux']->simpleEntity($data)->store();
	}
	
}
