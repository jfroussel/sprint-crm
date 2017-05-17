<?php
namespace App\Modules\Lead;

use App\Route\Route;
use RedCat\Route\Request;
use App\Templix\Templix;
use FoxORM\MainDb;
use RedCat\Strategy\CallTrait;
use App\AbstractController;
class Lead extends AbstractController{
	use CallTrait;
	protected $db;
	protected $tokenExpire = 60*60*24*3; //30 jours en secondes
	function __construct(MainDb $db){
		$this->db = $db;
	}
	function addLead(Request $request, MainDb $db){
		$data = [];
		
		$lead = $this->cookie2row($request->email);
		
		if(!$lead){
			$lead = $db->simpleEntity('lead');
		}
		else{
			$this->cleanLead($lead);
		}
		
		$lead->email = $request->email;
        $lead->amount = $request->amount;
        $lead->type = $request->type;
        $lead->seniority = $request->seniority;
        $lead->category = $request->category;
        $lead->frais = 35;
        $lead->fraisIssu = 115;
        //$lead->honorary = $this->honorary($request->amount, $request->type, $request->seniority, $request->category);
        //$lead->costProcedure = $this->costProcedure($request->amount, $request->type, $request->seniority, $request->category);
        //$lead->total = $lead->honorary + $lead->costProcedure;
        $lead->token = bin2hex(random_bytes(32));
        $lead->expire = time()+$this->tokenExpire;
		
		try{
			$db['lead'][] = $lead;
            header('Location:simulator?lead='.$lead->token,302);
            return false;
		}
		catch(ValidationException $e){
			$data['error'] = $e->getMessage();
		}
		$data['lead'] = $lead;
		return $data;
	}

	
	protected function cookie2id($email){
		$token = $this->cookie2token($email);
		if($token){
			$r = $this->token2id($token);
			if(!$r){
				$this->removeCookie($email);
			}
			return $r;
		}
	}
	protected function cookie2row($email){
		$token = $this->cookie2token($email);
		if($token){
			$r = $this->token2row($token);
			if(!$r){
				$this->removeCookie($email);
			}
			return $r;
		}
	}
	protected function cookie2token($email){
		$cookieName = 'lead_'.bin2hex($email);
		return isset($_COOKIE[$cookieName])?$_COOKIE[$cookieName]:false;
	}
	protected function token2row($token){
		$row = $this->db['lead']
			->where('token = ?',[$token])
			->getRow()
		;
		return $row;
	}
	protected function token2id($token){
		$id = $this->db['lead']
			->unSelect()
			->select('id')
			->where('token = ?',[$token])
			->getCell()
		;
		return $id;
	}
	protected function tokenSetCookie($email,$token){
		$cookieName = $this->email2cookieName($email);
		setcookie($cookieName,$token,time()+$this->tokenExpire);
	}
	protected function removeCookie($email){
		$cookieName = $this->email2cookieName($email);
		setcookie($cookieName,null,-1);
		if(isset($_COOKIE[$cookieName])){
			unset($_COOKIE[$cookieName]);
		}
	}
	protected function email2cookieName($email){
		return 'lead_'.bin2hex($email);
	}
	protected function cleanLead($lead){
		$properties = [
			
			//step2
			'deb_type',
			'debit_name',
			'debit_address',
			'debit_zip_code',
			'debit_city',
			'debit_country',
			'debit_siren',
			'debit_tva',
			'debit_last_name',
			'debit_first_name',
			'debit_capacity',
			'debit_email',
			'debit_phone',
			
			//step3
			'invoice_comments',
			'_many_lead_invoice_x_',
			
			//step4
			'cgu_accepted',
		];
		foreach($properties as $k){
			if(substr($k,0,6)=='_many_')
				$lead->$k = [];
			else
				$lead->$k = null;
		}
		$this->db['lead'][] = $lead;
	}
}
