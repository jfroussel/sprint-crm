<?php
namespace App\Modules\Action\Promise;

use App\AbstractController;

class Create extends AbstractController{
	protected $needAuth = true;
    
    function load($debtor_id){
        return [
            'debtor'        =>  $this->db['debtor'][$debtor_id],
            'contactsList'	=>	$this->db['contact']->where('debtor_primary = ?',[$this->db['debtor']->getPrimary($debtor_id)]),

        ];
    }
	function store($data){
        $data['user_id'] = $this->user->id;
		$row = $this->db['promise']->simpleEntity($data);
		$_many2many_paperwork = [];
		foreach($data['_many2many_paperwork'] as $id){
			$_many2many_paperwork[] = (int)$id;
		}
		$row->_many2many_paperwork = $_many2many_paperwork;
		return $row->store();
	}
	
	function getOnePromesses($id){
        return $this->db['promise']->getAmountSumForDebtor($id,true);
    }
    
}
