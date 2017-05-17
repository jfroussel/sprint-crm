<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
class Auth_Log_Row extends EntityModel{
	function beforePut(){
		$this->time = $this->now();
	}
}
