<?php
namespace App\Modules\Auth;
use RedCat\Route\Url;
use RedCat\Strategy\Di;
use RedCat\Route\Request;
use RedCat\Framework\FrontController\RouterInterface as Route;
use App\Modules\Auth\Auth;
class Login extends Auth{
	function __invoke(Request $request, Url $url, Route $route, Di $di){
		if(count($request))
			$this->authResponse = $this->login();
		if($this->connected()){
			if(isset($request['redirect'])&&$request['redirect']&&isset($_SERVER['HTTP_REFERER'])){
				$redirect = $_SERVER['HTTP_REFERER'];
			}
			else{
				$redirect = '';
			}
			$route->redirect($redirect);
		}
		$this->handleAuthResponse();
		$data = [];
		$data['action'] = $request['action'];
		return $data;
    }
}
