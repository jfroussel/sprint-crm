<?php

namespace App\Modules\Debtors;

use App\AbstractController;

class TabParametres extends AbstractController{
	protected $needAuth = true;
	
	function load($debtor_id){
		$management = $this->getManagement($debtor_id);
		return [
			'userList' => $this->db['user'],
			'user_id' => $management?$management->user_id:null,
		];
	}

	function updateManagement($debtor_id,$user_id=null){
		if(!$debtor_id) return;
		$management = $this->db['management'];
		$row = $this->getManagement($debtor_id);
		if(!$row){
			$row = $management->entity([
				'debtor_id'=>$debtor_id,
			]);
		}
		$row->user_id = $user_id;
		$row->store();
		return true;
	}
	
	protected function getManagement($debtor_id){
		$management = $this->db['management'];
		if($management->exists()){
			return $management->where('debtor_id = ?',[$debtor_id])->limit(1)->getRow();
		}
	}
}
