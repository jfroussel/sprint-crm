<?php
namespace App\Modules\Auth;
use RedCat\Route\Request;
use App\Route\User;
use App\Modules\Auth\Auth;
class Activation extends Auth{
	function __invoke(Request $request, User $user){
		if(!isset($request['key']))
			return;
		
		$this->authResponse = $this->activate($request['key'],true);
		$user->getSession()->flush();
		
		$this->handleAuthResponse();
		return [
			'user'=>$user
		];
    }
}
