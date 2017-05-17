<?php
namespace App\Model\Entity;
use App\Model\TableModel;

class Note_Table extends TableModel{
	function loadDynamicFilters($userId){
		if($this->db['user'][$userId]->groupe=='gestionnaire'){			
			$this->join('management ON management.debtor_id = note.debtor_id');
			$this->join('user ON management.user_id = user.id AND user.id = ?',[$userId]);
		}
	}
}
