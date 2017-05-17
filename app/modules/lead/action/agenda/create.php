<?php
namespace App\Modules\Action\Agenda;

use App\AbstractController;

class Create extends AbstractController{
	protected $needAuth = true;
	
	function load($debtor_id){
		return [
			'usersList'		=>	$this->db['user'],
			'contactsList'	=>	$this->db['contact']->where('debtor_primary = ?',[$this->db['debtor']->getPrimary($debtor_id)]),
            'debtor'        =>  $this->db['debtor'][$debtor_id],

		];	
	}
	
	function store($data){
        $data['user_id'] = $this->user->id;
		return $this->db['agenda']->simpleEntity($data)->store();
	}
}
