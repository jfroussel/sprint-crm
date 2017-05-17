<?php
namespace App\Modules\Action\Contact;

use App\AbstractController;

class Create extends AbstractController{
	protected $needAuth = true;
	
	function load($debtor_id){
        return [
            'debtor'        =>  $this->db['debtor'][$debtor_id],
        ];
    }
    
    function store($data){
		return $this->db['contact']->simpleEntity($data)->store();
	}
	
}
