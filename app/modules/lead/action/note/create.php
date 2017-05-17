<?php
namespace App\Modules\Action\Note;

use App\AbstractController;

class Create extends AbstractController{
	protected $needAuth = true;
	
	function load($debtor_id){
        return [
			'usersList'		=>	$this->db['user'],
			'contactsList'	=>	$this->db['contact']->where('debtor_primary = ?',[ $this->db['debtor']->getPrimary($debtor_id) ]),
            'debtor'        =>  $this->db['debtor'][$debtor_id],
            'note'          =>  ['debtor_id' => $debtor_id,'category' => 'note'  ],


		];
	}
	
	function store($data){
	    $data['user_id'] = $this->user->id;
		return $this->db['note']->simpleEntity($data)->store();
	}
}
