<?php
namespace App\Modules\Auth;

use RedCat\Route\Request;
use RedCat\Strategy\Di;
use RedCat\Strategy\CallTrait;
use FoxORM\MainDb;
use RedCat\Identify\PHPMailer;
use RedCat\Identify\Random;
use App\Route\Session;
use App\AbstractController;

class Auth extends AbstractController{
	use CallTrait;
	
	const ERROR_USER_BLOCKED = 1;
	const ERROR_USER_BLOCKED_2 = 46;
	const ERROR_USER_BLOCKED_3 = 47;
	const ERROR_LOGIN_SHORT = 2;
	const ERROR_LOGIN_LONG = 3;
	const ERROR_LOGIN_INCORRECT = 4;
	const ERROR_LOGIN_INVALID = 5;
	const ERROR_NAME_INVALID =  48;
	const ERROR_PASSWORD_SHORT = 6;
	const ERROR_PASSWORD_LONG = 7;
	const ERROR_PASSWORD_INVALID = 8;
	const ERROR_PASSWORD_NOMATCH = 9;
	const ERROR_PASSWORD_INCORRECT = 10;
	const ERROR_PASSWORD_NOTVALID = 11;
	const ERROR_NEWPASSWORD_SHORT = 12;
	const ERROR_NEWPASSWORD_LONG = 13;
	const ERROR_NEWPASSWORD_INVALID = 14;
	const ERROR_NEWPASSWORD_NOMATCH = 15;
	const ERROR_LOGIN_PASSWORD_INVALID = 16;
	const ERROR_LOGIN_PASSWORD_INCORRECT = 17;
	const ERROR_EMAIL_INVALID = 18;
	const ERROR_EMAIL_INCORRECT = 19;
	const ERROR_NEWEMAIL_MATCH = 20;
	const ERROR_ACCOUNT_INACTIVE = 21;
	const ERROR_SYSTEM_ERROR = 22;
	const ERROR_LOGIN_TAKEN = 23;
	const ERROR_EMAIL_TAKEN = 24;
	const ERROR_AUTHENTICATION_REQUIRED = 25;
	const ERROR_ALREADY_AUTHENTICATED = 26;
	const ERROR_RESETKEY_INVALID = 27;
	const ERROR_RESETKEY_INCORRECT = 28;
	const ERROR_RESETKEY_EXPIRED = 29;
	const ERROR_ACTIVEKEY_INVALID = 30;
	const ERROR_ACTIVEKEY_INCORRECT = 31;
	const ERROR_ACTIVEKEY_EXPIRED = 32;
	const ERROR_RESET_EXISTS = 33;
	const ERROR_ALREADY_ACTIVATED = 34;
	const ERROR_ACTIVATION_EXISTS = 35;
	const ERROR_UNABLE_SEND_ACTIVATION = 36;
	const ERROR_EMAIL_REGISTERING = 37;
	const OK = 100;
	const OK_PASSWORD_CHANGED = 101;
	const OK_EMAIL_CHANGED = 102;
	const OK_ACCOUNT_ACTIVATED = 103;
	const OK_ACCOUNT_DELETED = 104;
	const OK_LOGGED_IN = 105;
	const OK_LOGGED_OUT = 106;
	const OK_REGISTER_SUCCESS = 107;
	const OK_PASSWORD_RESET = 108;
	const OK_RESET_REQUESTED = 109;
	const OK_ACTIVATION_SENT = 110;
	const OK_ACCOUNT_ACTIVATED_AND_AUTOLOGGED = 111;
	public $siteUrl;
	protected $di;
	protected $db;
	protected $session;
	protected $cost = 10;
	protected $server;
	protected $rootLogin;
	protected $rootPassword;
	protected $rootEmail;
	protected $rootName;
	protected $rootPasswordNeedRehash;
	protected $baseHref;
	protected $suffixHref;
	protected $debug;
	
	
	protected $authMessages;
	protected $authResponse;
	protected $user;
	protected $request;
	public $authResponseMessage;
	public $errors;
	function __construct(
		Di $di,
		$rootLogin = 'root',
		$rootPassword = null,
		$rootName	= 'Developer',
		$rootEmail	= null,
		$server=null,
		Request $request,
		$debug=false,
		MainDb $db,
		Session $session
	){
		$this->di = $di;
		$this->session = $session;
		$this->db = $db;
		
		$this->rootLogin = $rootLogin;
		$this->rootPassword = $rootPassword;
		$this->rootEmail = $rootEmail;
		$this->rootName = $rootName;
		$this->server = $server?:$_SERVER;
		$this->siteUrl = rtrim($this->getBaseHref(),'/').'/';
		
		$this->request = $request;
		$this->debug = (bool)$debug;
		//dd($this->server);
	}
	function debug($b=true){
		$this->debug = (bool)$b;
	}
	protected function getMessage($code=null){
		if(is_null($code)) return;
		if(!isset($this->authMessages))
			$this->authMessages = self::getMessages();
		if(is_array($code)){
			$code[0] = $this->authMessages[$code[0]];
			return call_user_func_array('sprintf',$code);
		}
		return $this->authMessages[$code];
	}
	protected function handleAuthResponse(){
		$code = $this->authResponse;
		if($code){
			if($code<self::OK){
				$this->errors[] = $this->getMessage($code);
			}
			else{
				$this->authResponseMessage = $this->getMessage($code);
			}
		}
	}
	protected function register(){
		ob_start();
		$request = $this->request;
		$email = $request['email'];
		$password = $request['password'];
		$repeatpassword = $request['password_confirmation'];
		return $this->registerQuery($email, null, $password, $repeatpassword);
	}
	protected function login(){
		$request = $this->request;
		$login = $request['login'];
		$password = $request['password'];
		$lifetime = 0;
		if($request['remember']){
			switch($request['lifetime']){
				case 'day':
					$lifetime = 86400;
				break;
				case 'week':
					$lifetime = 604800;
				break;
				case 'month':
					$lifetime = 2592000;
				break;
				default:
				case 'year':
					$lifetime = 31536000;
				break;
			}
		}
		$authResponse = $this->loginQuery($login, $password, $lifetime);		
		$this->session->flush();
		return $authResponse;
	}
	
	protected function rootPasswordNeedRehash(){
		return $this->rootPasswordNeedRehash;
	}
	protected function sendMail($email, $type, $key, $login){
		if($type=='activation'){
			$subject = 'Activation du compte - desico.Sprint-CRM';
			$message = includeOutput('app/auth/email/activation.php',['site'=>$this->siteUrl,'uri'=>'auth/signin','key'=>$key]);
		}
		else{
			$subject = 'Réinitialisation du mot de passe - desico.Sprint-CRM';
			$message = includeOutput('app/auth/email/reset.php',['site'=>$this->siteUrl,'uri'=>'auth/reset','key'=>$key]);
		}
		$mailer = $this->di->get(PHPMailer::class);
		return $mailer->mail([$email=>$login],$subject,$message);
	}
	protected function loginRoot($password,$lifetime=0){
		$pass = $this->rootPassword;
		if(!$pass)
			return self::ERROR_SYSTEM_ERROR;
		$id = 0;
		if(strpos($pass,'$')!==0){
			if($pass!=$password){
				$this->session->addAttempt();
				return self::ERROR_LOGIN_PASSWORD_INCORRECT;
			}
		}
		else{
			if(!($password&&password_verify($password, $pass))){
				$this->session->addAttempt();
				return self::ERROR_LOGIN_PASSWORD_INCORRECT;
			}
			else{
				$options = ['cost' => $this->cost];
				if(password_needs_rehash($pass, PASSWORD_DEFAULT, $options)){
					$this->rootPassword = password_hash($password, PASSWORD_DEFAULT, $options);
					$this->rootPasswordNeedRehash = true;
				}
			}
		}
		if($this->db){
			$tableUsers = $this->db['user'];
			if($tableUsers->exists()){
				if($this->rootEmail){
					if($tableUsers->columnExists('login'))
						$id = $this->db->getCell('SELECT id FROM '.$this->db->escTable('user').' WHERE login = ?',[$this->rootLogin]);
				}
				else{
					if($tableUsers->columnExists('login')&&$tableUsers->columnExists('email'))
						$id = $this->db->getCell('SELECT id FROM '.$this->db->escTable('user').' WHERE login = ? OR email = ?',[$this->rootLogin,$this->rootEmail]);
					elseif($tableUsers->columnExists('login'))
						$id = $this->db->getCell('SELECT id FROM '.$this->db->escTable('user').' WHERE login = ?',[$this->rootLogin]);
					elseif($tableUsers->columnExists('email'))
						$id = $this->db->getCell('SELECT id FROM '.$this->db->escTable('user').' WHERE email = ?',[$this->rootEmail]);
				}
				if(!$id){
					if($tableUsers->columnExists('is_superroot'))
						$id = $this->db->getCell('SELECT id FROM '.$this->db->escTable('user').' WHERE is_superroot = ?',[true]);
					if($id){
						$this->db->exec('UPDATE '.$this->db->escTable('user').' SET login = ?, email = ? WHERE id = ?',[$this->rootLogin,$this->rootEmail,$id]);
					}
				}
			}
			else{
				$id = null;
			}
			if(!$id){
				try{
					$user = $this->createUser([
						'login'=>$this->rootLogin,
						'name'=>isset($this->rootName)?$this->rootName:$this->rootLogin,
						'email'=>isset($this->rootEmail)?$this->rootEmail:null,
						'active'=>1,
						'is_superroot'=>true
					]);
				}
				catch(\Exception $e){
					if($this->debug) throw $e;
					return self::ERROR_SYSTEM_ERROR;
				}
			}
			else{
				$user = $this->db['user'][$id];
			}
		}
		else{
			$user = (object)[
				'id'=>$id,
				'login'=>$this->rootLogin,
				'name'=>isset($this->rootName)?$this->rootName:$this->rootLogin,
				'email'=>isset($this->rootEmail)?$this->rootEmail:null,
				'is_superroot'=>true
			];
		}
		$this->addSession($user,$lifetime);
		return self::OK_LOGGED_IN;
	}
	protected function loginQuery($login, $password, $lifetime=0){
		if($s=$this->session->isBlocked()){
			return [self::ERROR_USER_BLOCKED,$s];
		}
		if(($login==$this->rootLogin||$this->rootEmail&&$login==$this->rootEmail)&&$this->rootPassword)
			return $this->loginRoot($password,$lifetime);
		$loginIsEmail = !ctype_alnum($login)&&filter_var($login,FILTER_VALIDATE_EMAIL);
		if(!$loginIsEmail&&$this->validateLogin($login)){
			$this->session->addAttempt();
			return self::ERROR_LOGIN_PASSWORD_INVALID;
		}

		$col = $loginIsEmail?'email':'login';
		
		$user = null;
		if($this->db->has(['user'=>[$col]])){
			$user = $this->db['user']->select('password')->where($col.' = ?',[$login])->getRow();
		}

		if(!$user){
			$this->session->addAttempt();
			return self::ERROR_LOGIN_PASSWORD_INCORRECT;
		}
		if(!($password&&password_verify($password, $user->password))){
			$this->session->addAttempt();
			return self::ERROR_LOGIN_PASSWORD_INCORRECT;
		}
		else{
			$options = ['cost' => $this->cost];
			if(password_needs_rehash($user->password, PASSWORD_DEFAULT, $options)){
				$password = password_hash($password, PASSWORD_DEFAULT, $options);
				$row = $this->db->read('user',(int)$user->id);
				$row->password = $password;
				try{
					$this->db->put($row);
				}
				catch(\Exception $e){
					if($this->debug) throw $e;
					return self::ERROR_SYSTEM_ERROR;
				}
			}
		}
		if(!isset($user->active)||$user->active!=1){
			$this->session->addAttempt();
			return self::ERROR_ACCOUNT_INACTIVE;
		}
		if(!$this->addSession($user,$lifetime)){
			return self::ERROR_SYSTEM_ERROR;
		}
		return self::OK_LOGGED_IN;
	}

	protected function registerQuery($email, $login, $password, $repeatpassword, $name=null){
		if ($s=$this->session->isBlocked()){
			return [self::ERROR_USER_BLOCKED,$s];
		}
		if($e=$this->validateEmail($email))
			return $e;
		if($login&&($e=$this->validateLogin($login)))
			return $e;
		if($name&&($e=$this->validateDisplayname($name)))
			return $e;
		if($e=$this->validatePassword($password))
			return $e;
		if($password!==$repeatpassword){
			return self::ERROR_PASSWORD_NOMATCH;
		}
		if($this->isEmailRegistering($email)){
			return self::ERROR_EMAIL_REGISTERING;
		}
		if($this->isEmailTaken($email)){
			$this->session->addAttempt();
			return self::ERROR_EMAIL_TAKEN;
		}
		if($login&&$this->isLoginTaken($login)){
			$this->session->addAttempt();
			return self::ERROR_LOGIN_TAKEN;
		}
		if(self::ERROR_SYSTEM_ERROR===$this->addUser($email, $password, $login, $name))
			return self::ERROR_UNABLE_SEND_ACTIVATION;
		return self::OK_REGISTER_SUCCESS;
	}
	protected function activate($key,$autologin=false,$lifetime=0){
		if($s=$this->session->isBlocked()){
			return [self::ERROR_USER_BLOCKED,$s];
		}
		$getRequest = $this->getRequest($key, 'activation');
		if(!is_object($getRequest))
			return self::ERROR_ACTIVEKEY_INVALID;
		$user = $this->getUser($getRequest->{'user_id'});
		if(isset($user->active)&&$user->active==1){
			$this->session->addAttempt();
			$this->deleteRequest($getRequest->id);
			return self::ERROR_SYSTEM_ERROR;
		}
		$row = $this->db->read('user',(int)$getRequest->{'user_id'});
		$row->active = 1;
		$this->db->put($row);
		$this->deleteRequest($getRequest->id);
		if($autologin){
			if(!$this->addSession($user,$lifetime)){
				return self::ERROR_SYSTEM_ERROR;
			}
			return self::OK_ACCOUNT_ACTIVATED_AND_AUTOLOGGED;
		}
		return self::OK_ACCOUNT_ACTIVATED;
	}
	function requestReset($email, $noSendMail=false, &$key=null){
		ob_start();
		if ($s=$this->session->isBlocked()){
			return [self::ERROR_USER_BLOCKED,$s];
		}
		if($e=$this->validateEmail($email))
			return $e;
		if($this->db['user']->exists())
			$id = $this->db->getCell('SELECT id FROM '.$this->db->escTable('user').' WHERE email = ?',[$email]);
		else
			$id = null;
		if(!$id){
			$this->session->addAttempt();
			return self::ERROR_EMAIL_INCORRECT;
		}
		if($e=$this->addRequest($id, $email, 'reset', $noSendMail, $key)){
			$this->session->addAttempt();
			return $e;
		}
		return self::OK_RESET_REQUESTED;
	}
	protected function logout(){
		$userId = (int)$this->session->id;
		$connected = $this->connected();
		$destroyed = $this->session->destroy();
		if($connected&&$destroyed){
			$this->db['auth_log'][] = ['_one_user'=>$userId,'action'=>'disconnect','ip'=>$this->session->getIp()];
		}
		if($destroyed){
			return self::OK_LOGGED_OUT;
		}
	}
	protected function getHash($string){
		return password_hash($string, PASSWORD_DEFAULT, ['cost' => $this->cost]);
	}
	private function addSession($user,$lifetime=0){
		$this->session->setCookieLifetime($lifetime);
		$this->session->setKey($user->id);
		foreach($user as $k=>$v){
			if($k!='password')
				$this->session->set($k,$v);
		}
		$this->db['auth_log'][] = ['_one_user'=>$user->id,'action'=>'connect','ip'=>$this->session->getIp()];
		return true;
	}
	private function isEmailRegistering($email){
		if($this->db['user']->exists())
			return (bool)$this->db->getCell('SELECT id FROM '.$this->db->escTable('user').' WHERE email = ? AND active < 1',[$email]);
	}
	private function isEmailTaken($email){
		if($this->db['user']->exists())
			return (bool)$this->db->getCell('SELECT id FROM '.$this->db->escTable('user').' WHERE email = ?',[$email]);
	}
	private function isLoginTaken($login){
		if($this->db['user']->exists())
			 return (bool)$this->db->getCell('SELECT id FROM '.$this->db->escTable('user').' WHERE login = ?',[$login]);
	}
	private function createUser($data){
		$table = $this->db['user'];
		$row = $table->simpleEntity($data);
		$row->type = 'saas';
		$row->store();
		$row->_one_instance = ['_type'=>'user','id'=>$row->id];
		$row->store();
		return $row;
	}
	private function addUser($email, $password, $login=null, $name=null){
		$password = $this->getHash($password);
		try{
			$row = $this->createUser([
				'login' => $login,
				'name' => $name,
				'email' => $email,
				'password' => $password,
				'active' => 0,
			]);
		}
		catch(\Exception $e){
			if($this->debug) throw $e;
			return self::ERROR_SYSTEM_ERROR;
		}
		$uid = $row->id;
		if(self::ERROR_SYSTEM_ERROR===$e=$this->addRequest($uid, $email, 'activation')){
			$this->db->delete($row);
			return $e;
		}
	}

	protected function getUser($uid){
		return $this->db->read('user',(int)$uid);
	}

	protected function deleteUser($uid, $password){
		if ($s=$this->session->isBlocked()){
			return [self::ERROR_USER_BLOCKED,$s];
		}
		if($e=$this->validatePassword($password)){
			$this->session->addAttempt();
			return $e;
		}
		$getUser = $this->getUser($uid);
		if(!($password&&password_verify($password, $getUser['password']))){
			$this->session->addAttempt();
			return self::ERROR_PASSWORD_INCORRECT;
		}
		$row = $this->db->read('user',(int)$uid);
		if(!$this->db->delete($row)){
			return self::ERROR_SYSTEM_ERROR;
		}
		$this->session->destroyKey($uid);
		foreach($this->db->one2many($row,'auth_request') as $request){
			if(!$this->db->delete($request)){
				return self::ERROR_SYSTEM_ERROR;
			}
		}		
		return self::OK_ACCOUNT_DELETED;
	}
	private function addRequest($uid, $email, $type, $noSendMail=false, &$key=null){
		if($type != "activation" && $type != "reset"){
			return self::ERROR_SYSTEM_ERROR;
		}
		$row = $this->db->findOne('auth_request',$this->db->esc('user_id').' = ? AND type = ?',[$uid, $type]);
		if($row){
			$this->deleteRequest($row->id);
		}
		$user = $this->getUser($uid);
		if($type == 'activation' && isset($user->active) && $user->active == 1){
			return self::ERROR_ALREADY_ACTIVATED;
		}
		$key = Random::getString(40);
		$expire = date("Y-m-d H:i:s", strtotime("+1 day"));
		$request = [
			'_type'=>'auth_request',
			'_one_user_x_'=>$user,
			'rkey'=>$key,
			'expire'=>$expire,
			'type'=>$type
		];
		try{
			$this->db->put($request);
		}
		catch(\Exception $e){
			if($this->debug) throw $e;
			return self::ERROR_SYSTEM_ERROR;
		}
		if(!$noSendMail){
			$this->postProcess(function()use($email, $type, $key, $user){
				$this->sendMail($email, $type, $key, isset($user->name)?$user->name:null);
			});
		}
	}
	private function postProcess($callback){
		if($this->debug){
			register_shutdown_function($callback);
			return;
		}
		
		header("Content-Encoding: none");
		header("Connection: close");
		register_shutdown_function(function()use($callback){			
			$size = ob_get_length();
			header("Content-Length: {$size}");
			ob_end_flush();
			if(ob_get_length()){
				ob_flush();
			}
			flush();
			
			call_user_func($callback);
		});
	}
	private function getRequest($key, $type){
		$row = $this->db->findOne('auth_request',' rkey = ? AND type = ?',[$key, $type]);
		if(!$row){
			$this->session->addAttempt();
			if($type=='activation')
				return self::ERROR_ACTIVEKEY_INCORRECT;
			elseif($type=='reset')
				return self::ERROR_RESETKEY_INCORRECT;
			return;
		}
		$expiredate = strtotime($row->expire);
		$currentdate = strtotime(date("Y-m-d H:i:s"));
		if ($currentdate > $expiredate){
			$this->session->addAttempt();
			$this->deleteRequest($row->id);
			if($type=='activation')
				return self::ERROR_ACTIVEKEY_EXPIRED;
			elseif($type=='reset')
				return self::ERROR_ACTIVEKEY_EXPIRED;
		}
		return $row;
	}
	private function deleteRequest($id){
		return $this->db->execute('DELETE FROM '.$this->db->escTable('auth_request').' WHERE id = ?',[$id]);
	}
	protected function validateLogin($login){
		if (strlen($login) < 1)
			return self::ERROR_LOGIN_SHORT;
		elseif (strlen($login) > 30)
			return self::ERROR_LOGIN_LONG;
		elseif(!ctype_alnum($login)&&!filter_var($login, FILTER_VALIDATE_EMAIL))
			return self::ERROR_LOGIN_INVALID;
	}
	protected function validateDisplayname($login){
		if (strlen($login) < 1)
			return self::ERROR_NAME_INVALID;
		elseif (strlen($login) > 50)
			return self::ERROR_NAME_INVALID;
	}
	private function validatePassword($password){
		if (strlen($password) < 6)
			return self::ERROR_PASSWORD_SHORT;
		elseif (strlen($password) > 72)
			return self::ERROR_PASSWORD_LONG;
		elseif ((!preg_match('@[A-Z]@', $password) && !preg_match('@[a-z]@', $password)) || !preg_match('@[0-9]@', $password))
			return self::ERROR_PASSWORD_INVALID;
	}
	private function validateEmail($email){
		if (!filter_var($email, FILTER_VALIDATE_EMAIL))
			return self::ERROR_EMAIL_INVALID;
	}
	protected function resetPass($key, $password, $repeatpassword, $active=null){
		if ($s=$this->session->isBlocked()){
			return [self::ERROR_USER_BLOCKED,$s];
		}
		if($e=$this->validatePassword($password))
			return $e;
		if($password !== $repeatpassword){ // Passwords don't match
			return self::ERROR_NEWPASSWORD_NOMATCH;
		}
		$data = $this->getRequest($key, 'reset');
		
		if(empty($data))
			return self::ERROR_RESETKEY_INVALID;
		
		if(!is_object($data)){
			return $data;
		}
			
		$user = $this->getUser($data->user_id);
		if(!$user){
			$this->session->addAttempt();
			$this->deleteRequest($data['id']);
			return self::ERROR_SYSTEM_ERROR;
		}
		if(!($password&&password_verify($password, $user->password))){
			$password = $this->getHash($password);
			$row = $this->db->read('user',$data['user_id']);
			$row->password = $password;
			if($active){
				$row->active = 1;
			}
			try{
				$this->db->put($row);
			}
			catch(\Exception $e){
				if($this->debug) throw $e;
				return self::ERROR_SYSTEM_ERROR;
			}
		}
		$this->deleteRequest($data['id']);
		return self::OK_PASSWORD_RESET;
	}
	protected function resendActivation($email){
		ob_start();
		if ($s=$this->session->isBlocked()){
			return [self::ERROR_USER_BLOCKED,$s];
		}
		if($e=$this->validateEmail($email))
			return $e;
		$row = $this->db->findOne('user',' email = ?',[$email]);
		if(!$row){
			$this->session->addAttempt();
			return self::ERROR_EMAIL_INCORRECT;
		}
		if(isset($row->active)&&$row->active == 1){
			$this->session->addAttempt();
			return self::ERROR_ALREADY_ACTIVATED;
		}
		if($e=$this->addRequest($row->id, $email, "activation")){
			$this->session->addAttempt();
			return $e;
		}
		return self::OK_ACTIVATION_SENT;
	}
	protected function changePassword($uid, $currpass, $newpass, $repeatnewpass){
		if ($s=$this->session->isBlocked()){
			return [self::ERROR_USER_BLOCKED,$s];
		}
		if($e=$this->validatePassword($currpass)){
			$this->session->addAttempt();
			return $e;
		}
		if($e=$this->validatePassword($newpass))
			return $e;
		if($newpass !== $repeatnewpass){
			return self::ERROR_NEWPASSWORD_NOMATCH;
		}
		$user = $this->getUser($uid);
		if(!$user){
			$this->session->addAttempt();
			return self::ERROR_SYSTEM_ERROR;
		}
		$newpass = $this->getHash($newpass);
		if(!($currpass&&password_verify($currpass, $user->password))){
			$this->session->addAttempt();
			return self::ERROR_PASSWORD_INCORRECT;
		}
		if($currpass != $newpass){			
			$row = $this->db->read('user',(int)$uid);
			$row->password = $newpass;
			$this->db->put($row);
		}
		return self::OK_PASSWORD_CHANGED;
	}
	protected function getEmail($uid){
		$row = $this->db->read('user',(int)$uid);
		if (!$row->id){
			return false;
		}
		return $row->email;
	}
	protected function changeEmail($uid, $email, $password){
		if($s=$this->session->isBlocked()){
			return [self::ERROR_USER_BLOCKED,$s];
		}
		if($e=$this->validateEmail($email))
			return $e;
		if($e=$this->validatePassword($password))
			return $e;
		$user = $this->getUser($uid);
		if(!$user){
			$this->session->addAttempt();
			return self::ERROR_SYSTEM_ERROR;
		}
		if(!($password&&password_verify($password, $user->password))){
			$this->session->addAttempt();
			return self::ERROR_PASSWORD_INCORRECT;
		}
		if ($email == $user->email){
			$this->session->addAttempt();
			return self::ERROR_NEWEMAIL_MATCH;
		}
		$row = $this->db->read('user',(int)$uid);
		$row->email = $email;
		try{
			$this->db->put($row);
		}
		catch(\Exception $e){
			if($this->debug) throw $e;
			return self::ERROR_SYSTEM_ERROR;
		}
		return self::OK_EMAIL_CHANGED;
	}
	
	protected function setBaseHref($href){
		$this->baseHref = $href;
	}
	protected function getServerHttps(){
		return isset($this->server['HTTPS'])?$this->server['HTTPS']:null;
	}
	protected function getServerPort(){
		return isset($this->server['SERVER_PORT'])?$this->server['SERVER_PORT']:null;
	}
	protected function getProtocolHref(){
		return 'http'.($this->getServerHttps()=="on"?'s':'').'://';
	}
	protected function getServerHref(){
		return isset($this->server['SERVER_NAME'])?$this->server['SERVER_NAME']:null;
	}
	protected function getPortHref(){
		$ssl = $this->getServerHttps()=="on";
		return $this->getServerPort()&&((!$ssl&&(int)$this->getServerPort()!=80)||($ssl&&(int)$this->getServerPort()!=443))?':'.$this->getServerPort():'';
	}
	protected function getBaseHref(){
		if(!isset($this->baseHref)){
			$this->setBaseHref($this->getProtocolHref().$this->getServerHref().$this->getPortHref().'/');
		}
		return $this->baseHref.$this->getSuffixHref();
	}
	protected function setSuffixHref($href){
		$this->suffixHref = $href;
	}
	protected function getSuffixHref(){
		if(!isset($this->suffixHref)){
			if(isset($this->server['REDCAT_URI'])){
				$this->suffixHref = ltrim($this->server['REDCAT_URI'],'/');				
			}
			else{
				$docRoot = $this->server['DOCUMENT_ROOT'].'/';
				if(defined('REDCAT_CWD'))
					$cwd = REDCAT_CWD;
				else
					$cwd = getcwd();
				if($docRoot!=$cwd&&strpos($cwd,$docRoot)===0)
					$this->suffixHref = substr($cwd,strlen($docRoot));
			}
		}
		return $this->suffixHref;
	}
	protected function connected(){
		return !!$this->session->id;
	}
	
	static function getMessages(){
		return [
			self::ERROR_USER_BLOCKED => "Too many failed attempts, try again in %d seconds",
			self::ERROR_USER_BLOCKED_2 => "Too many failed attempts, try again in %d minutes and %d seconds",
			self::ERROR_USER_BLOCKED_3 => "Too many failed attempts, try again in :",
			self::ERROR_LOGIN_SHORT => "Login is too short",
			self::ERROR_LOGIN_LONG => "Login is too long",
			self::ERROR_LOGIN_INCORRECT => "Login is incorrect",
			self::ERROR_LOGIN_INVALID => "Login is invalid",
			self::ERROR_NAME_INVALID => "Name is invalid",
			self::ERROR_PASSWORD_SHORT => "Password is too short",
			self::ERROR_PASSWORD_LONG => "Password is too long",
			self::ERROR_PASSWORD_INVALID => "Password must contain at least one uppercase and lowercase character, and at least one digit",
			self::ERROR_PASSWORD_NOMATCH => "Passwords do not match",
			self::ERROR_PASSWORD_INCORRECT => "Current password is incorrect",
			self::ERROR_PASSWORD_NOTVALID => "Password is invalid",
			self::ERROR_NEWPASSWORD_SHORT => "New password is too short",
			self::ERROR_NEWPASSWORD_LONG => "New password is too long",
			self::ERROR_NEWPASSWORD_INVALID => "New password must contain at least one uppercase and lowercase character, and at least one digit",
			self::ERROR_NEWPASSWORD_NOMATCH => "New passwords do not match",
			self::ERROR_LOGIN_PASSWORD_INVALID => "Login / Password are invalid",
			self::ERROR_LOGIN_PASSWORD_INCORRECT => "Login / Password are incorrect",
			self::ERROR_EMAIL_INVALID => "Email address is invalid",
			self::ERROR_EMAIL_INCORRECT => "Email address is incorrect",
			self::ERROR_NEWEMAIL_MATCH => "New email matches previous email",
			self::ERROR_ACCOUNT_INACTIVE => "Account has not yet been activated",
			self::ERROR_SYSTEM_ERROR => "A system error has been encountered. Please try again",
			self::ERROR_LOGIN_TAKEN => "The login is already taken",
			self::ERROR_EMAIL_TAKEN => "The email address is already in use",
			self::ERROR_EMAIL_REGISTERING => "The email address is already registered but not activated",
			self::ERROR_AUTHENTICATION_REQUIRED => "Authentication required",
			self::ERROR_ALREADY_AUTHENTICATED => "You are already authenticated",
			self::ERROR_RESETKEY_INVALID => "Reset key is invalid",
			self::ERROR_RESETKEY_INCORRECT => "Reset key is incorrect",
			self::ERROR_RESETKEY_EXPIRED => "Reset key has expired",
			self::ERROR_ACTIVEKEY_INVALID => "Activation key is invalid",
			self::ERROR_ACTIVEKEY_INCORRECT => "Activation key is incorrect",
			self::ERROR_ACTIVEKEY_EXPIRED => "Activation key has expired",
			self::ERROR_RESET_EXISTS => "A reset request already exists",
			self::ERROR_ALREADY_ACTIVATED => "Account is already activated",
			self::ERROR_ACTIVATION_EXISTS => "An activation email has already been sent",
			self::ERROR_UNABLE_SEND_ACTIVATION => "Unable to send activation email",
			
			self::OK_PASSWORD_CHANGED => "Password changed successfully",
			self::OK_EMAIL_CHANGED => "Email address changed successfully",
			self::OK_ACCOUNT_ACTIVATED => "Account has been activated. You can now log in",
			self::OK_ACCOUNT_ACTIVATED_AND_AUTOLOGGED => "Account has been activated. And you are now logged in",
			self::OK_ACCOUNT_DELETED => "Account has been deleted",
			self::OK_LOGGED_IN => "You are now logged in",
			self::OK_LOGGED_OUT => "You are now logged out",
			self::OK_REGISTER_SUCCESS => "Account created. Activation email sent to email",
			self::OK_PASSWORD_RESET => "Password reset successfully",
			self::OK_RESET_REQUESTED => "Password reset request sent to email address",
			self::OK_ACTIVATION_SENT => "Activation email has been sent",
		];
	}
}

function includeOutput(){
	if(func_num_args()>1)
		extract(func_get_arg(1));
	ob_start();
	include REDCAT_CWD.func_get_arg(0);
	return ob_get_clean();
}
