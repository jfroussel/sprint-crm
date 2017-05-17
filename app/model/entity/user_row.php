<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
class User_Row extends EntityModel{
	protected $validatePreFilters = [
		'siren_code'=>'removeWhiteSpaces',
	];
	protected $validateRules = [
		'email'=>'email',
		'name'=>'name',
		'first_name'=>'name',
		'last_name'=>'name',
		'birthday'=>'date',
		'company_website'=>'url',
		'cellphone'=>'phone',
		'fax'=>'phone',
		'phone'=>'phone',
		'siren_code'=>'luhn',
	];
	protected $validateFilters = [
		'particular'=>'bool',
		'active'=>'bool',
	];
	protected $validateProperties = [
		'email',
		'password',
		'first_name',
		'last_name',
		'active',
		'presentation',
        'function',
		'cellphone',
		'family_status',
		'gender',
		'id',
		'birthday',
		'phone',
		'skype',
		'social_denomination',
		'twitter',
        'facebook',
        'google',
		
		'id_customer',
		'particular',
		
		'company_name',
		'company_trade_name',
		'company_reference',
		'company_website',
		'company_rate_category',
		'group',

		'address',
		'postal_code',
		'city',
        'country',
		'fax',
		'legal_form',
		'siren_code',
        'siret_code',
		'naf_code',
		'share_capital',
		'rcs',
		'tva_number', //autocalculation from SIREN
		'civility',
		
		'type',
		
		'is_superroot',
		'login',
		'user_id',
	];
	
	function beforeCreate(){
		$this->ctime = $this->now();
		if(!isset($this->user_id)){
			$this->user_id = null;
		}
	}
	function beforePut(){
		//if(isset($this->avatar)) unset($this->avatar);
		
		$this->checkEmailUnicity();
		
		$this->hashPassword();
		
		if($this->siren_code){
			$this->tva_number = $this->getValidate()->filterFR_SirenToTva($this->siren_code);
		}
		
		if($this->particular){
			$removePrefix = 'company_';
			foreach($this as $k=>$v){
				if(substr($k,0,strlen($removePrefix))==$removePrefix){
					$this[$k] = null;
				}
			}
		}
	}
	function beforeDelete(){
		if($this->getValueOf('is_root')){
			throw new ValidationException("You can't delete the root user");
		}
	}
	
	function checkEmailUnicity(){
		$users = $this->db['user'];
		if($this->email&&$users->exists()&&($id=$users->select('id')->where('email = ?',[$this->email])->getCell())&&$id!=$this->id){
			throw new Exception('User with similar email allready exists');
		}
	}
	function hashPassword(){
		if(!empty($this->password)){
			if(strpos($this->password,'$2y$10$')!==0){
				$this->password = password_hash($this->password, PASSWORD_DEFAULT, ['cost' => 10]);
			}
			return true;
		}
		else{
			unset($this->password);
			return false;
		}
	}
	function clearEmptyPassword(){
		if(empty($this->password))
			unset($this->password);
	}
	
	function afterRead(){
		//$this->avatar = ;
	}
	
	
	function getDynamic(){
		
		$avatar = 'content/user/'.$this->id.'/avatar.png';
		if(!file_exists($avatar)){
			$size = 180;
			$email = $this->email;
			$defaultAvatar = 'http://www.gravatar.com/avatar/'.md5($this->_di['default-gravatar']).'?s='.$size;
			$avatar = 'http://www.gravatar.com/avatar/'.md5($email).'?s=180&d='.urlencode($defaultAvatar);
			
		}
		
		return [
			'avatar'=>$avatar,
		];
	}
}
