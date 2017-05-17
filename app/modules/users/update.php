<?php
namespace App\Modules\Users;

use RedCat\Strategy\Di;
use DateTime;
use App\Route\Route;
use App\Route\User;
use RedCat\Route\Url;
use RedCat\Route\Request;
use App\Model\Db;
use App\Auth\Auth;
use RedCat\Identify\PHPMailer;
use RedCat\Route\SilentProcess;

use RedCat\Strategy\CallTrait;
use RedCat\FileIO\Uploader;

use App\AbstractController;

class Update extends AbstractController{
	
	protected $needAuth = true;
	
	protected $table;
	
	protected $localAvatar = 'content/user/%s/avatar.png';
	function __construct(Db $db, Route $route, Di $di, User $user, Request $request){
		$this->di = $di;
		$this->db = $db;
		$this->request = $request;
		$this->user = $user;
		$this->table = clone $this->db['user'];
		$this->table->where('user_id = ?',[$user->id]);
	}
	
	function load($id){
		$data = [];
		$user = $this->db['user'][$id];
		$this->checkUser($user);
		if($user){
			$data['user'] = $user;
			$data['avatar'] = $this->getAvatar($this->localAvatar,[$user->id],$user->email,true);
			$data['avatarLocal'] = $this->getAvatarLocal($this->localAvatar,[$user->id],$user->email);
            $data['userIsHimself'] = $id == $this->user->id;
		}
		return $data;
	}

	function store($user){
		if(!isset($user['id'])) return;
		$this->updateLogo($this->localAvatar,'params[0][avatarFile]',[$user['id']]);
		$user = $this->db->simpleEntity('user',$user);
		
		$this->checkUser($user);
		
		$user->hashPassword();
		$this->db['user'][] = $user;
		
		$data = [];
		unset($user['password']);
		$data['user'] = $user;
		$data['avatar'] = $this->getAvatar($this->localAvatar,[$user['id']],$user['email'],true);
		$data['avatarLocal'] = $this->getAvatarLocal($this->localAvatar,[$user['id']],true);
		return $user->id?$data:false;
	}

	function checkEmail($email,$compare=null){
		if(!$this->table->exists()) return true;
		$id = $this->db['user']->select('id')->where('email = ?',[$email])->getCell();
		return (!$id)||($compare&&$id==$compare);
	}

	protected function checkUser($user){
		if(!is_object($user)){
			$user = $this->db['user']->unSelect()->select('id, user_id')->where('id = ?',[$user])->getRow();
		}
		if(!$user){
			return;
		}
		$user_id = $user->user_id;
		$id = $user->id;		
		if(!$this->user->is_superroot&&$user_id!=$this->user->id&&$id!=$this->user->id){
			throw new \Exception('This user account is not your\'s');
		}
	}
	
	
	protected function updateLogo($pathPattern, $key, $vars=[]){
		$fileKey = str_replace([']','['],['','.'],$key);		
		$imgDir = vsprintf(dirname($pathPattern),(array)$vars);
		$imgFilename = pathinfo($pathPattern,PATHINFO_FILENAME);
		$imgExt = 'png';
		$uploader = $this->di->get(Uploader::class);
		$uploader->image([
			'dir'=>$imgDir,
			'key'=>str_replace('.','_',$fileKey),
			'rename'=>$imgFilename,
			'width'=>false,
			'height'=>false,
			'multi'=>false,
			'conversion'=>$imgExt,
		]);
		if($this->request->dot($fileKey.'_remove')=='1'){
			$imgFile = $imgDir.'/'.$imgFilename.'.'.$imgExt;
			if(is_file($imgFile)){
				unlink($imgFile);
			}
		}
	}

	function getAvatar($pathPattern, $vars=[], $email, $noCache=false, $size=null){
		if(!$size) $size = 180;
		$localAvatar = $this->getAvatarLocal($pathPattern,$vars,$noCache);
		$defaultAvatar = 'http://www.gravatar.com/avatar/'.md5($this->di['default-gravatar']).'?s='.$size;
		$gravatar = 'http://www.gravatar.com/avatar/'.md5($email).'?s='.$size.'&d='.urlencode($defaultAvatar);
		$avatar = $localAvatar?$localAvatar:$gravatar;
		//dd($avatar);
		return $avatar;
	}

	function getAvatarLocal($pathPattern, $vars=[], $noCache=false){
		$localAvatar = vsprintf($pathPattern, $vars);
		return is_file($localAvatar)?$localAvatar.($noCache?'?_t='.time():''):'';
	}
	
}
