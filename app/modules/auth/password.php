<?php
namespace App\Modules\Auth;
use RedCat\Route\Request;
use App\Modules\Auth\Auth;
class Password extends Auth{
	function __invoke(Request $request){
		$email = $request['email'];
		if($email){
			$this->authResponse = $this->requestReset($request['email']);
		}
		$this->handleAuthResponse();
    }
}
