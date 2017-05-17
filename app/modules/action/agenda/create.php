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
		
		$agenda = $this->db['agenda']->simpleEntity($data);
		$agenda->linked_by = 'agenda_user';
		$agenda->user_id = $this->user->id;
		$agenda->store();
        
        $user_id = $data['user_id'] ?? $this->user->id;
        
        $this->db['agenda_user']->simpleEntity([
			'user_id'	=> $user_id,
			'agenda_id'	=> $agenda->id,
        ])->store();
        
		return $agenda;
	}
}
