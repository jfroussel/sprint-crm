<?php
namespace App\Modules\Auth;
use App\Modules\Auth\Auth;
class Logout extends Auth{
	function __invoke(){
		$id = $this->session->id;
		$this->authResponse = $this->logout();
		$this->handleAuthResponse();
    }
}
