<?php
namespace App\Modules\Action\Contact;

use App\AbstractController;

class Update extends AbstractController{
	protected $needAuth = true;
	
	function load($id){
		return [
			'contact' => $this->db['contact'][$id]
		];
	}
	function store($data){
        if(!isset($data['id'])) return;
        return $this->db['contact']->simpleEntity($data)->store();
	}
	
}
