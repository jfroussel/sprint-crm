<?php
namespace App\Modules\Action\Promise;

use App\AbstractController;

class Update extends AbstractController{
	protected $needAuth = true;
    
	function load($id){
		return [
			'promise' => $this->db['promise'][$id]
		];
	}

}
