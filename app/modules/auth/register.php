<?php
namespace App\Modules\Auth;
use RedCat\Route\Request;
use App\Modules\Auth\Auth;
class Register extends Auth{
	function __invoke(Request $request){
		$data = [];
		if(count($request)){
			$this->authResponse = $this->register();
		}
		$this->handleAuthResponse();
		$data['authResponse'] = $this->authResponse;
		$data['authResponseMessage'] = $this->authResponseMessage;
		$data['action'] = $request['action'];
		return $data;
    }
    
}
