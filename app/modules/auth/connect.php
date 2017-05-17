<?php
namespace App\Modules\Auth;
use RedCat\Route\Url;
use RedCat\Strategy\Di;
use RedCat\Route\Request;
use RedCat\Framework\FrontController\RouterInterface as Route;
use App\Modules\Auth\Auth;
class Connect extends Auth{
	function __invoke(Di $di, Request $request, Url $url, Route $route){
		if($request['action']=='login'){
			$this->authResponse = $this->login();
		}
		
		if($this->connected()){
			$route->redirect('');
			exit;
		}
		
		if($request['action']=='register'){
			$this->authResponse = $this->register();
		}
		$this->handleAuthResponse();
		$data = [];
		$data['action'] = $request['action'];
		$data['redirect'] = isset($request['redirect'])&&$request['redirect']&&isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:$route->resolveRoute($redirect);
		return $data;
    }
}
