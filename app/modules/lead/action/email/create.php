<?php

namespace App\Modules\Action\Email;

use App\Model\Db;

use RedCat\Route\Url;
use RedCat\Route\Request;
use App\Route\User;
use App\AbstractController;
use App\Api\DebtorMail;
use RedCat\Strategy\Di;

class Create extends AbstractController
{
	protected $needAuth = true;

    protected $db;
    protected $url;
    protected $user;
    protected $mailGunApiKey;
    protected $mailGunDomain;
    protected $request;
    protected $mail;

    function __construct(Di $di, Db $db, User $user, Url $url, Request $request)
    {
        $this->db = $db;
        $this->url = $url;
        $this->user = $user;
        $this->request = $request;

        $this->mail = $di->get(DebtorMail::class,[
            'db'=> $this->db,
            'instance_id'=> $this->user->instance_id,
            'user_id'=>$this->user->id,
            'urlBaseHref'=>$this->url->getBaseHref(),
        ]);
    }
    
    function load($debtor_id){
		
		$debtorInfo = $this->db['paperwork']->getOneDebtorInfo($debtor_id);
		
		return [
			'usersList'		=>	$this->db['user'],
			'contactsList'	=>	$this->db['contact']->where('debtor_primary = ?',[$this->db['debtor']->getPrimary($debtor_id)]),
			'templatesList'	=>	$this->db['template']->where('type = ?',['email']),
            'debtor'        =>  $this->db['debtor'][$debtor_id],
            'email'         =>  [
				'debtor_id'       =>$debtor_id,
			 ],
		];
	}
    
    
    function getTemplateRender($message, $datas){
        return $this->mail->getTemplateRender($message, $datas);
    }
    function send($data){
        return $this->mail->sendEmail($data);
    }


}
