<?php
namespace App\Modules\Action\Schedule;

use App\AbstractController;

class Update extends AbstractController{
	protected $needAuth = true;

	function load($id){
		return [
			'schedule' => $this->db['schedule'][$id]
		];
	}

}
