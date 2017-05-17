<?php
namespace App\Modules\Action\Agenda;

use App\AbstractController;

class Update extends AbstractController{
	protected $needAuth = true;
	
	function load($id){
		$agenda = $this->db['agenda'][$id];
		$debtor = $agenda->_one_debtor;
		return [
			'agenda'		=>	$agenda,
			'contactsList'	=>  $debtor ? $debtor->_many_contact : [],
			'user'			=>	$agenda->_one_user,
			'contact'		=>	$agenda->_one_contact,
			'debtor'		=>	$debtor,
			'lead_id'		=>	$agenda->lead_id,

		];	
	}
	
	function store($data){
		if(!isset($data['id'])) return;
		return $this->db['agenda']->simpleEntity($data)->store();
	}
}
