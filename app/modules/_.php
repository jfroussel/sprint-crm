<?php
namespace App\Modules;
use RedCat\Framework\FrontController\RenderInterface;
use App\AbstractController;
use App\Templix\Templix;

class _ extends AbstractController implements RenderInterface{
	
	function __invoke(Templix $templix){
		if(!$this->user->id){
			$templix->display('modules/corporate.tml');
		}
		else{
			$data = [];
			$templix->display('modules/saas.tml',$data);
		}
	}
	
	function load(){
		$data = [];
		
		if($this->user->instance_id){
			$db = $this->db;
		}
		else{
			$db = $this->mainDb;
		}
		
		$data['user'] = $db['user'][$this->user->id];
		return $data;
	}
	
}
