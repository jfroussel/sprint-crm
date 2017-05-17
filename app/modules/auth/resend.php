<?php
namespace App\Modules\Auth;
use RedCat\Route\Request;
use App\Modules\Auth\Auth;
class Resend extends Auth{
	function __invoke(Request $request){
		$email = $request['email'];
		if($email){
			$this->authResponse = $this->resendActivation($request['email']);
		}
		$this->handleAuthResponse();
    }
}
