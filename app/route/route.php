<?php
namespace App\Route;

use RedCat\Strategy\Di;
use RedCat\Strategy\CallTrait;
use RedCat\Route\Router;
use RedCat\Route\Url;
use RedCat\Framework\FrontController\Synaptic;
use RedCat\Framework\FrontController\FrontController;
use RedCat\Framework\FrontController\RenderInterface;
use RedCat\Framework\FrontController\AssetLoader;

use App\Templix\Templix;
use App\Route\User;

class Route extends FrontController{
	use CallTrait;
	protected $controllerNamespace = 'App\\Modules';
	protected $uri;
	protected $controller;
	protected $session;
	protected $user;
	function load(){
		$this->callLoadRoutes();
	}
	
	protected function _loadRoutes(Session $session, User $user){
		$this->session = $session;
		$this->user = $user;
		
		$this->prefix('download/',function($path){
			$file = '.data/content/'.$this->user->instance_id.'/'.$path;
			if(!file_exists($file)){
				$this->viewError('404');
				exit;
			}
			$download = new ResumeDownload($file);
			$download->process();
			exit;
		});
		
		$this->extension('css|js|png|jpg|jpeg|gif','new:'.AssetLoader::class);
		
		$this->extension('jsonp',[$this,'outputJsonp']);
		$this->extension('json',[$this,'outputJson']);
		$this->append([$this,'findControllerRenderer'],[$this,'controllerApi']);
		$this->append([$this,'findController'],[$this,'outputTml']);
		
		//$this->byTml(['modules/','view'],[$this,'viewModule']);
		$this->prepend('401',[$this,'viewError']);
		$this->prepend('403',[$this,'viewError']);
		$this->prepend('404',[$this,'viewError']);
		$this->prepend('500',[$this,'viewError']);
	}
	
	function findControllerRenderer($uri){
		$controller = $this->findController($uri);
		if($controller){
			list($controllerClass,$uri) = $controller;
			if(is_subclass_of($controllerClass,RenderInterface::class))
				return $controllerClass;
		}
	}
	function findController($uri){
		$ctrl = $this->controllerNamespace.'\\'.ucfirst(str_replace(['  ',' '], ['','\\'], ucwords(str_replace(['/','-'], [' ','  '], $uri))));
		if(substr($ctrl,-1)=='\\') $ctrl .= '_';
		if(class_exists($ctrl)&&(new \ReflectionClass($ctrl))->isInstantiable())
			return [$ctrl,$uri];
	}
	
	function controllerApi($controllerClass){
		$di = $this->di;
		$controller = $di($controllerClass);
		$this->controller = $controller;
		$controller->checkAuth();
		$method = isset($this->request['method'])?$this->request['method']:'__invoke';
		$params = isset($this->request['params'])?$this->request['params']:[];
		if(is_object($params)) $params = $params->getArray();
		if($method!='__invoke'&&substr($method,0,1)=='_'){
			throw new \RuntimeException("Underscore prefixed method \"$method\" is not allowed to public api access");
		}
		if(method_exists($controller, $method)){
			if(!(new \ReflectionMethod($controller, $method))->isPublic()) {
				throw new \RuntimeException("The called method is not public");
			}
			return $di->method($controller,$method,(array)$params);
		}
	}
	function _outputTml($params, Templix $template){
		list($controllerClass,$uri) = $params;
		$data = $this->controllerApi($controllerClass);
		
		if($data===false){
			return;
		}
		
		$uri = 'modules/'.$uri;
		
		if(isset($data['_view'])){
			$uri = $data['_view'];
		}
		
		foreach(get_object_vars($this->controller) as $k=>$v){
			$template[$k] = $v;
		}
		$template($uri, $data);
	}
	function outputJson($params){
		if($params=$this->findController(array_shift($params))){
			$data = $this->controllerApi(array_shift($params));
		}
		else{
			$data = ['error'=>404];
		}
		if(!headers_sent()){
			header('Content-type:application/json;charset=utf-8');
		}
		echo json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	}
	function outputJsonp($params){
		$callback = $this->request['callback'];
		unset($this->request['callback']);
		if($params=$this->findController(array_shift($params))){
			$data = $this->controllerApi(array_shift($params));
		}
		else{
			$data = ['error'=>404];
		}
		if(!headers_sent()){
			header('Content-type:application/javascript;charset=utf-8');
		}
		echo $callback.'('.json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT).');';
	}
	
	function _view($path, $data=[], Templix $templix){
		return $templix($path,$data);
	}
	function viewError($error, $data=[]){
		return $this->view('modules/errors/'.$error, $data);
	}
	function _redirect($uri,$code=302,Url $url){
		$baseHref = $url->getBaseHref();
		$url = $baseHref.$uri;
		if(!empty($_SERVER['QUERY_STRING'])){
			if(strpos($url,'?')===false){
				$url .= '?';
			}
			$url .= $_SERVER['QUERY_STRING'];
		}
		header('Location: '.$url,true,$code);
		exit;
	}
	function redirectBack(Url $url){
		header('Location: '.$url->getBaseHref().(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:''),true,302);
		exit;
	}
	function isAjax(){
		return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])&&strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest';
	}
	
	function run($path,$domain=null){
		if(!parent::run($path,$domain)){
			$this->viewError(404);
			exit;
		}
		return true;
	}
	
}
