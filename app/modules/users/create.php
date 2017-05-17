<?php
namespace App\Modules\Users;

use RedCat\Strategy\Di;
use DateTime;
use App\Route\Route;
use App\Route\User;
use RedCat\Route\Url;
use RedCat\Route\Request;
use App\Model\Db;
use App\Modules\Auth\Auth;
use RedCat\Identify\PHPMailer;
use RedCat\Route\SilentProcess;

use RedCat\Strategy\CallTrait;
use RedCat\FileIO\Uploader;

use App\AbstractController;

class Create extends AbstractController{
	
	protected $needAuth = true;
	
	protected $table;

	function __construct(Db $db, Route $route, Di $di, User $user, Request $request){
		$this->di = $di;
		$this->db = $db;
		$this->request = $request;
		$this->user = $user;
		$this->table = clone $this->db['user'];
		$this->table->where('user_id = ?',[$user->id]);
	}
	function store($user, Url $url, SilentProcess $silentProcess, Auth $auth){
		$user = $this->db->simpleEntity('user',$user);
		$user->user_id = $this->user->id;
		$user->type = 'saas';
		$user->hashPassword();
		$this->db['user'][] = $user;
		$this->sendSetPasswordMail($user, $url, $auth, $silentProcess);
		return $user->id?$user:false;
	}
	function checkEmail($email,$compare=null){
		if(!$this->table->exists()) return true;
		$id = $this->db['user']->select('id')->where('email = ?',[$email])->getCell();
		return (!$id)||($compare&&$id==$compare);
	}

	protected function sendSetPasswordMail($user, Url $url, Auth $auth, SilentProcess $silentProcess){
		$email = $user->email;
		$name = $user->name;
		$subject = 'Nouveau compte utilisateur Sprint-crm';
		$auth->requestReset($email, true, $key);
		$href = rtrim($url->getBaseHref(),'/').'/auth/set-password?key='.$key;
		$message = <<<HTML
		Un compte utilisteur a été créé pour vous, pour pouvoir l'utiliser vous devez d'abord définir le mot de passe en cliquant sur ce lien:
		<strong><a href="{$href}" target="_blank">Définir votre mot de passe</a></strong>;
HTML;
		$silentProcess->register(function()use($email, $name, $subject, $message){
			$mailer = $this->di->get(PHPMailer::class);
			$mailer->mail([$email=>$name], $subject, $message);
		});
	}
	
}
