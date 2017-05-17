<?php
namespace App\Modules\Action\Promise;

use RedCat\Route\Url;
use RedCat\Route\Request;
use App\Route\User;
use App\AbstractController;
use App\Api\PromiseMail;
use RedCat\Strategy\Di;
use App\Model\Db;

class Create extends AbstractController{

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

        $this->mail = $di->get(PromiseMail::class,[
            'db'=> $this->db,
            'instance_id'=> $this->user->instance_id,
            'user_id'=>$this->user->id,
            'urlBaseHref'=>$this->url->getBaseHref(),
        ]);

    }

    function load($debtor_id){
        return [
            'debtor'        =>  $this->db['debtor'][$debtor_id],
            'contactsList'	=>	$this->db['contact']->where('debtor_primary = ?',[$this->db['debtor']->getPrimary($debtor_id)]),

        ];
    }
	function store($data){
        $data['user_id'] = $this->user->id;
		$row = $this->db['promise']->simpleEntity($data);
		$_many2many_paperwork = [];
		foreach($data['_many2many_paperwork'] as $id){
			$_many2many_paperwork[] = (int)$id;
		}
		$row->_many2many_paperwork = $_many2many_paperwork;
		return $row->store();
	}
	
	function getOnePromesses($id){
        return $this->db['promise']->getAmountSumForDebtor($id,true);
    }

    function send($data){
        return $this->mail->sendEmail($data);
    }
    
}
