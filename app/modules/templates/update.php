<?php
namespace App\Modules\Templates;

use App\AbstractController;

class Update extends AbstractController{
	protected $needAuth = true;
	
	function load($id){
        return [
            'template' => $this->db['template'][$id],
            'is_superroot' => $this->user->is_superroot,
        ];
    }

	function store($data){
		if(!isset($data['id'])) return;
		return $this->db['template']->simpleEntity($data)->store();
	}
	
}
