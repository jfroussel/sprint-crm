<?php
namespace App\Modules\Auth;
use RedCat\Route\Request;
use App\Modules\Auth\Auth;
class MakePassword extends Auth{
	function __invoke(Request $request){
		$data = [];
		if(!isset($request['key'])){
			$this->errors[] = 'No key provided';
		}
		else{
			$key = $request['key'];
			$data['key'] = $key;
			if($request['password']){
				$password = $request['password'];
				$password_confirm = $request['password_confirm'];
				$this->authResponse = $this->resetPass($key,$password,$password_confirm,true);
			}
		}
		$this->handleAuthResponse();
		return $data;
    }
}
