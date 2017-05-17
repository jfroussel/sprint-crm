<?php
namespace App\Modules\Action\Note;

use App\AbstractController;

class Update extends AbstractController{
	protected $needAuth = true;
	
	function load($id){
		return [
			'note' => $this->db['note'][$id]
		];
	}
	
	function store($data){
		if(!isset($data['id'])) return;
		return $this->db['note']->simpleEntity($data)->store();
	}
}
