<?php

namespace App\Modules\Action\Sms;

use App\Model\Db;

use RedCat\Route\Url;
use RedCat\Route\Request;
use App\Route\User;
use App\AbstractController;
use App\Api\DebtorSms;
use RedCat\Strategy\Di;

class Create extends AbstractController
{

    protected $needAuth = true;

    protected $db;
    protected $url;
    protected $user;
    protected $request;
    protected $sms;

    function __construct(Di $di, Db $db, User $user, Url $url, Request $request)
    {
        $this->db = $db;
        $this->url = $url;
        $this->user = $user;
        $this->request = $request;

        $this->sms = $di->get(DebtorSms::class,[
            'db'=> $this->db,
            'instance_id'=> $this->user->instance_id,
            'user_id'=>$this->user->id,
            'urlBaseHref'=>$this->url->getBaseHref(),
        ]);
    }
	
	function load($debtor_id){
		return [
			'usersList'		=>	$this->db['user'],
			'contactsList'	=>	$this->db['contact']->where('debtor_primary = ?',[$this->db['debtor']->getPrimary($debtor_id)]),
			'templatesList'	=>	$this->db['template']->where('type = ?',['sms']),
            'debtor'        =>  $this->db['debtor'][$debtor_id],
		];
	}


    function send($data){
        return $this->sms->sendSms($data);
    }

    protected function curlSender($url, $body, $header){
        return $this->sms->curlSender($url, $body, $header);
    }

}
