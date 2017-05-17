<?php
namespace App\Modules\Paperworks;

use App\AbstractController;

class Update extends AbstractController{
	protected $needAuth = true;
    function load($id,$debtor_id=null){
		$data = $this->db['paperwork'][$id];
		if($debtor_id){
			$debtor = $this->db['debtor'][$debtor_id];
			$data['debtor_id'] = $debtor->id;
			$data['debtor_nom_client'] = $debtor->nom_client;
		}
		return $data;
	}
}
